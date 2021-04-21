<?php
/** @noinspection PhpComposerExtensionStubsInspection */

namespace DataHelpers;

require_once __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "helpers" . DIRECTORY_SEPARATOR . "FileHandler.php";

/**
 *
 */
class DataGetter
{
    public static function get_ligs(){
        if (!file_exists(\Helpers\FileHandler::get_file_path("ligs"))) return [];
        $json = file_get_contents(\Helpers\FileHandler::get_file_path("ligs"));
        $ligs = json_decode($json, true);
        if ($ligs == false) return [];
        return $ligs;
    }

    public static function get_brokers(){
        if (!file_exists(\Helpers\FileHandler::get_file_path("brokers"))) return [];
        $json = file_get_contents(\Helpers\FileHandler::get_file_path("brokers"));
        $brokers = json_decode($json, true);
        if ($brokers == false) return [];
        return $brokers;
    }
}