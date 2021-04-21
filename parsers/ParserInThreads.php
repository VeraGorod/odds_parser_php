<?php

namespace Parsers;
use \Helpers\FileHandler;
use \RedBeanPHP\R as R;
use Symfony\Component\Process\PhpProcess;

require_once __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "helpers" . DIRECTORY_SEPARATOR . "work_with_threads.php";
require_once __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "helpers" . DIRECTORY_SEPARATOR . "FileHandler.php";
require_once __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "helpers" . DIRECTORY_SEPARATOR . "ConfigGetter.php";
require_once __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "helpers" . DIRECTORY_SEPARATOR . "ProcessManager.php";
require_once __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "parsers" . DIRECTORY_SEPARATOR . "MyParser.php";
require_once __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "parsers" . DIRECTORY_SEPARATOR . "MyFParser.php";
require_once __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "db" . DIRECTORY_SEPARATOR . "connect.php";
require_once __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "r_helpers" . DIRECTORY_SEPARATOR . "add_new_record.php";
require_once __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "r_helpers" . DIRECTORY_SEPARATOR . "add_error_to_record.php";

/**
 *
 */
class ParserInThreads
{
    public $thread_number;
    public $lock;

    public function parse(){
        $this->get_lock();
        if(!$this->thread_number) return;
        $parser = new MyParser($this->thread_number);
        while (True){
            if (R::count("record", "status=0") == 0) {
                echo "records 0" . "<br>";
                $update = "UPDATE record SET status=0, errors=NULL, thread=0  WHERE status=4 AND (errors LIKE '%he driver server has died%' or errors LIKE '%Curl error thrown for http%')";
                R::exec($update);
                $update = "UPDATE `record` SET `status`=0, `attempts`=`attempts`+1, `thread`=0  WHERE `status`=4 AND `attempts`<10";
                R::exec($update);
                echo "records updated" . "<br>";
                if (R::count("record", "status=0") == 0) {
                    echo "records ended" . "<br>";
                    break;
                }

            }
            $update = "UPDATE record SET thread={$this->thread_number} WHERE thread=0 and status=0 ORDER BY type ASC, updated_at ASC LIMIT 1";
            R::exec($update);
            $record = R::findOne("record", "status=0 AND thread={$this->thread_number} ORDER BY updated_at ASC");
            echo "record found" . "<br>";
            if(empty($record)) {
                sleep(10);
                continue;
            }
            $data = array();
            try{
                switch ($record->type){
                    case "lig":
                        echo "record parse" . "<br>";
                        $data = $parser->get_lig_data($record->url, $record->other_years);
                        echo "record parsed" . "<br>";
                        R::begin();
                        echo "write matches" . "<br>";
                        foreach ($data["urls"] as $match_url){
                            if(stripos($match_url, "https://www.oddsportal.com/soccer") === false) {
                                if(stripos($match_url, "soccer") === false) continue;
                                $match_url = "https://www.oddsportal.com" . $match_url;
                            }
                            $new_record = \RHelpers\add_new_record($match_url, "match", false, $record->broker);
                            R::store($new_record);
                        }
                        echo "write next" . "<br>";
                        if(isset($data["next"])){
                            foreach ($data["next"] as $next_url){
                                $new_record = \RHelpers\add_new_record($next_url, "lig",0, $record->broker);
                                R::store($new_record);
                            }

                        }

                        break;
                    case "match":
                        echo "parse match begin" . "<br>";
                        $data = $parser->get_match_data($record->url, $record->broker);
                        echo "parse match end" . "<br>";
                        R::begin();
                        break;
                }
                echo "save file" . "<br>";
                FileHandler::save_array_to_json($data, FileHandler::get_results_dir() . DIRECTORY_SEPARATOR . $record->id . ".json");
                echo "file saved" . "<br>";
                $record->status = 7;
            }
            catch (\Throwable $e){
                echo $e . "<br>";
                $record = \RHelpers\add_error_to_record($record, $e->getMessage());
                $record->status = 4;
            }
            R::store($record);
            R::commit();
            echo "record written" . "<br>";
        }
        \Helpers\end_thread($this->lock);
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

$parser = new ParserInThreads();
$parser->parse();