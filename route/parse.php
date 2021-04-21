<?php

use \Parsers\MainParser;

require_once __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "parsers" . DIRECTORY_SEPARATOR . "MainParser.php";

try {

    if (isset($_GET["old"]) && $_GET["old"] == 1) {
        $parser = new MainParser(true);
    } else {
        if (!isset($_GET["url"])) return json_encode(["result" => "error"]);
        $url = urldecode($_GET["url"]);
        if(stripos($url, "results") === false) $url .= "results/";
        if (!isset($_GET["brokers"])) return json_encode(["result" => "error"]);
        $brokers    = urldecode($_GET["brokers"]);
        $all_years = false;
        if (isset($_GET["years"])) $all_years = boolval($_GET["years"]);

        $parser = new MainParser(false);
        $parser->set_data($url, $brokers, $all_years);
    }


    $parser->parse();
    echo json_encode(["result" => "success"]);
} catch (\Throwable $e) {
    echo json_encode(["result" => "error"]);
}



