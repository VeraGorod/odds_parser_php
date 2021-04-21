<?php

namespace Parsers;
use \Helpers\FileHandler;
use \RedBeanPHP\R as R;

require_once __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "helpers" . DIRECTORY_SEPARATOR . "work_with_threads.php";
require_once __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "helpers" . DIRECTORY_SEPARATOR . "FileHandler.php";
require_once __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "helpers" . DIRECTORY_SEPARATOR . "ConfigGetter.php";
require_once __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "helpers" . DIRECTORY_SEPARATOR . "ProcessManager.php";
require_once __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "parsers" . DIRECTORY_SEPARATOR . "MyFParser.php";
require_once __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "db" . DIRECTORY_SEPARATOR . "connect.php";
require_once __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "r_helpers" . DIRECTORY_SEPARATOR . "add_new_record.php";
require_once __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "r_helpers" . DIRECTORY_SEPARATOR . "add_error_to_record.php";

/**
 *
 */
class FParserInThreads
{
    public $thread_number=8;
    public $lock;

    public function parse(){
        //$this->get_lock();
        //if(!$this->thread_number) return;
        $parser = new MyFParser($this->thread_number);
        while (True){
            if (R::count("record", "status=0") == 0) {
                $update = "UPDATE record SET status=0, errors=NULL, thread=0  WHERE status=4 AND (errors LIKE '%he driver server has died%' or errors LIKE '%Curl error thrown for http%')";
                R::exec($update);
                $update = "UPDATE `record` SET `status`=0, `attempts`=`attempts`+1, `thread`=0  WHERE `status`=4 AND `attempts`<10";
                R::exec($update);
                if (R::count("record", "status=0") == 0) break;

            }
            $update = "UPDATE record SET thread={$this->thread_number} WHERE thread=0 and status=0 ORDER BY updated_at ASC LIMIT 1";
            R::exec($update);
            $record = R::findOne("record", "status=0 AND thread={$this->thread_number} ORDER BY updated_at ASC");
            if(empty($record)) {
                sleep(10);
                continue;
            }
            $data = array();
            try{
                switch ($record->type){
                    case "lig":
                        $data = $parser->get_lig_data($record->url, $record->other_years);
                        R::begin();
                        foreach ($data["urls"] as $match_url){
                            if(stripos($match_url, "https://www.oddsportal.com/soccer") === false) {
                                if(stripos($match_url, "soccer") === false) continue;
                                $match_url = "https://www.oddsportal.com" . $match_url;
                            }
                            $new_record = \RHelpers\add_new_record($match_url, "match", false, $record->broker);
                            R::store($new_record);
                        }
                        if(isset($data["next"])){
                            if(stripos($data["next"], "https://www.oddsportal.com/soccer") === false) {
                                if(stripos($data["next"], "soccer") === false) break;
                                $data["next"] = "https://www.oddsportal.com" . $data["next"];
                            }
                            $new_record = \RHelpers\add_new_record($data["next"], "lig", $record->other_years, $record->broker);
                            R::store($new_record);
                        }

                        break;
                    case "match":
                        $data = $parser->get_match_data($record->url, $record->broker);
                        R::begin();
                        break;
                }
                FileHandler::save_array_to_json($data, FileHandler::get_results_dir() . DIRECTORY_SEPARATOR . $record->id . ".json");
                $record->status = 7;
            }
            catch (\Throwable $e){
                $record = \RHelpers\add_error_to_record($record, $e->getMessage());
                $record->status = 4;
            }
            R::store($record);
            R::commit();
        }
        //\Helpers\end_thread($this->lock);
    }

    public function get_lock(){
        foreach (range(1, \Helpers\ConfigGetter::get_number_of_threads()) as $number) {
            $lock = \Helpers\start_thread($number);
            if(!$lock) continue;
            $this->lock = $lock;
            $this->thread_number = $number;
            \Helpers\ProcessManager::write_id($number);
            break;
        }
    }
}

$parser = new FParserInThreads();
$parser->parse();