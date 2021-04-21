<?php

namespace Route;

require_once __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "parsers" . DIRECTORY_SEPARATOR . "MyParser.php";

$parser = new \Parsers\MyParser();
$data = $parser->get_match_data("https://www.oddsportal.com/soccer/england/premier-league/liverpool-norwich-4IMoMG3q/", "10Bet");
var_dump($data);
