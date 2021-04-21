<?php
namespace Route;
use \RedBeanPHP\R as R;
require_once __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "vendor" . DIRECTORY_SEPARATOR . "autoload.php";
require_once __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "db" . DIRECTORY_SEPARATOR . "connect.php";
require_once __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "helpers" . DIRECTORY_SEPARATOR . "ProcessManager.php";


$data = array();
$data["processCount"] = \Helpers\ProcessManager::get_count_of_running_processes();
$data["all"] = R::count("record");
$data["now"] = R::count("record", "status=7");
$data["error"] = R::count("record", "status=4");
if($data["all"] == 0){
    $data["process"] = false;
}
else $data["process"] = true;

echo json_encode($data);