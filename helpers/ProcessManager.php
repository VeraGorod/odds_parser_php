<?php


namespace Helpers;

require_once "FileHandler.php";
require_once "ConfigGetter.php";
require_once "work_with_threads.php";

class ProcessManager
{
    public static function write_id($process){
        $path = ProcessManager::get_id_path($process);
        $processId = getmypid();
        file_put_contents($path, $processId);
    }

    public static function kill_process($process){
        $path = ProcessManager::get_id_path($process);
        $lock = \Helpers\start_thread($process);
        if($lock) {
            end_thread($lock);
            if(!file_exists($path)) return;
            if(file_exists($path)) unlink($path);
            return;
        }

        if(!file_exists($path)) return;
        $processid = file_get_contents($path);
        ProcessManager::kill($processid);
        if(file_exists($path)) unlink($path);

    }

    public static function kill($processId){
        if (function_exists('posix_kill')) {
            posix_kill($processId, SIGTERM);
        } elseif (function_exists('exec') && strstr(PHP_OS, 'WIN')) {
            exec("taskkill /F /PID $processId") ? TRUE : FALSE;
        }
    }

    public static function kill_all(){
        foreach (range(1, ConfigGetter::get_number_of_threads()) as $process){
            ProcessManager::kill_process($process);
        }
    }

    public static function get_id_path($number){
        return FileHandler::get_tmp_dir() . DIRECTORY_SEPARATOR . $number . ".txt";
    }

    public static function get_count_of_running_processes(){
        $count = 0;
        foreach (range(1, ConfigGetter::get_number_of_threads()) as $process){
            $lock = \Helpers\start_thread($process);
            if(!$lock) {
                $count ++;
                continue;
            }
            end_thread($lock);
        }
        return $count;
    }
}