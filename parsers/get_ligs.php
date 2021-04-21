<?php
/** @noinspection PhpComposerExtensionStubsInspection */

namespace Parsers;

require_once "MyParser.php";
require_once __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "helpers" . DIRECTORY_SEPARATOR . "FileHandler.php";
require_once __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "vendor" . DIRECTORY_SEPARATOR . "autoload.php";

function get_ligs()
{
    $parser = new MyParser(10);
    $ligs = $parser->get_ligs();

    file_put_contents(\Helpers\FileHandler::get_file_path("ligs"), json_encode($ligs));
}