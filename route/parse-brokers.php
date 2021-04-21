<?php

namespace Route;

use Helpers\FileHandler;

require_once __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "parsers" . DIRECTORY_SEPARATOR . "MyParser.php";
require_once __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "helpers" . DIRECTORY_SEPARATOR . "FileHandler.php";

try{
    if (!isset($_GET["url"])) echo json_encode(["result" => "error"]);
    $url = urldecode($_GET["url"]);
    $parser = new \Parsers\MyParser(10);
    $data = $parser->get_brokers($url);

    $path = FileHandler::get_file_path("brokers");
    file_put_contents($path, json_encode($data));
    echo json_encode(["result"=>"success", "brokers"=>$data]);
}
catch (\Throwable $e){
    echo json_encode(["result"=>"error"]);
}


