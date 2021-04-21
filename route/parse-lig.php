<?php

namespace Route;

require_once __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "parsers" . DIRECTORY_SEPARATOR . "MyParser.php";

$parser = new \Parsers\MyParser();
$data = $parser->get_lig_data("https://www.oddsportal.com/soccer/spain/tercera-division-promotion-play-offs/results/", true);
var_dump($data);

