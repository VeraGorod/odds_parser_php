<?php
require_once __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "export" . DIRECTORY_SEPARATOR . "Exporter.php";

try{
    $exporter = new \Export\Exporter();
    $exporter->export();
    echo json_encode(["result"=>"success"]);
}
catch (\Throwable $e){
    echo json_encode(["result"=>"error"]);
}
