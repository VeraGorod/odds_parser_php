<?php

try{
    require_once __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "helpers" . DIRECTORY_SEPARATOR . "ProcessManager.php";
    \Helpers\ProcessManager::kill_all();
    echo json_encode(["result"=>"success"]);
}
catch (\Throwable $e){
    echo json_encode(["result"=>"error"]);
}
