<?php

namespace Parsers;
use COM;
use \Helpers\FileHandler;
use \RedBeanPHP\R as R;
require_once __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "vendor" . DIRECTORY_SEPARATOR . "autoload.php";
require_once __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "helpers" . DIRECTORY_SEPARATOR . "FileHandler.php";
require_once __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "helpers" . DIRECTORY_SEPARATOR . "ConfigGetter.php";
require_once __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "helpers" . DIRECTORY_SEPARATOR . "ProcessManager.php";
require_once __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "db" . DIRECTORY_SEPARATOR . "connect.php";
require_once __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "r_helpers" . DIRECTORY_SEPARATOR . "add_new_record.php";
require_once __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "r_helpers" . DIRECTORY_SEPARATOR . "add_error_to_record.php";
require_once __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "parsers" . DIRECTORY_SEPARATOR . "MyParser.php";
/**
 *
 */
class MainParser
{
    public $url;
    public $all_years;
    public $brokers;
    public $old;


    function __construct($old, $add=false)
    {
        $this->old = $old;
        if(!$add) \Helpers\ProcessManager::kill_all();

    }

    public function set_data($url, $brokers, $all_years){
        $this->url = $url;
        $this->all_years = $all_years;
        $this->brokers = $brokers;
    }

    public function parse(){
        if(!$this->old) $this->start_round();
        R::exec("UPDATE record SET thread=0");
        R::exec("UPDATE record SET status=0, errors=NULL, thread=0 WHERE errors LIKE '%Curl error thrown for http%'");
        foreach (range(1, \Helpers\ConfigGetter::get_number_of_threads()) as $number){
            // если chrome
            $path = __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "parsers" . DIRECTORY_SEPARATOR . "ParserInThreads.php";
            // если phantomjs
            //$path = __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "parsers" . DIRECTORY_SEPARATOR . "ParserInThreadsPh.php";
            //exec("php {$path}  > NUL");
            $handle = new COM('WScript.Shell');
            $handle->Run("php {$path}", 0, false);
        }
    }

    public function start_round(){
        FileHandler::removeDirectory(FileHandler::get_results_dir());
        FileHandler::removeDirectory(FileHandler::get_output_dir());
        $log_path = FileHandler::path_from_array([__DIR__, "log.log"]);
        $xml_log_path = FileHandler::path_from_array([__DIR__, "create_xml.log"]);
        if(file_exists($log_path)) unlink($log_path);
        if(file_exists($xml_log_path)) unlink($xml_log_path);

        R::wipe('record');
        $record = \RHelpers\add_new_record($this->url, "lig", $this->all_years, $this->brokers);
        R::store($record);
    }

}