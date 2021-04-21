<?php
namespace Helpers;
require_once "FileHandler.php";


function start_thread($thread){
    $lock_file_name = FileHandler::path_from_array([FileHandler::get_tmp_dir(), "lock_file_$thread.lock"]);
    if(!is_dir(dirname($lock_file_name))) @mkdir(dirname($lock_file_name), 0777, true);
    if(!file_exists($lock_file_name)) touch($lock_file_name);
    $lock = fopen($lock_file_name, 'w');
    if ( !($lock && flock($lock, LOCK_EX | LOCK_NB)) ) {
        return false;
    }
    return $lock;
}

function end_thread($lock){
    flock($lock, LOCK_UN);
    fclose($lock);
}