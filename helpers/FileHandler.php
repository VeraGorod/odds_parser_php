<?php

namespace Helpers;


class FileHandler
{
    public static function get_file_path($name){
        switch ($name){
            case "ligs":
                return FileHandler::get_data_path() . DIRECTORY_SEPARATOR . "ligs.json";
            case "brokers":
                return FileHandler::get_data_path() . DIRECTORY_SEPARATOR . "brokers.json";
            default:
                throw new Exception($name . " for get filepath didn't exist");
        }
    }

    public static function get_data_path(){
        return __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "data";
    }

    public static function path_from_array($array){
        if(!is_array($array)) throw new Exception("должен быть передан массив");
        if(count($array) == 0) throw new Exception("в массиве для создания пути должен быть хотя бы один пункт");
        return implode(DIRECTORY_SEPARATOR, $array);
    }

    public static function save_text_file($data, $file_path){
        FileHandler::create_path_to_file($file_path);
        file_put_contents($file_path, $data);
    }

    public static function save_array_to_json($data, $file_path){
        FileHandler::create_path_to_file($file_path);
        file_put_contents($file_path, json_encode($data));
    }

    public static function read_json_to_array($file_path){
        return json_decode(file_get_contents($file_path), true);
    }

    public static function get_results_dir(){
        return FileHandler::path_from_array([__DIR__, "..", "results"]);
    }

    public static function get_output_dir(){
        return FileHandler::path_from_array([__DIR__, "..", "output"]);
    }

    public static function get_tmp_dir(){
        return FileHandler::path_from_array([__DIR__, "..", "mytmp"]);
    }

    public static function create_path_to_file($file_path){
        if(!file_exists(dirname($file_path)))
            mkdir(dirname($file_path), 0777, true);
    }

    public static function removeDirectory($dir) {
        if(!file_exists($dir)) return;
        $objs = scandir($dir);
        foreach ($objs as $obj){
            if($obj=="."||$obj=="..") continue;
            is_dir($dir . DIRECTORY_SEPARATOR . $obj) ? FileHandler::removeDirectory($dir . DIRECTORY_SEPARATOR . $obj) : unlink($dir . DIRECTORY_SEPARATOR . $obj);
        }
        rmdir($dir);
    }
}