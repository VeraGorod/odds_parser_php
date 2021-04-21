<?php

use \Parsers\MainParser;

require_once __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "parsers" . DIRECTORY_SEPARATOR . "MainParser.php";

try {


    $parser = new MainParser(true, true);
    $parser->set_data($url, $broker, $all_years);


    $parser->parse();
    echo json_encode(["result" => "success"]);
} catch (\Throwable $e) {
    echo json_encode(["result" => "error"]);
}



