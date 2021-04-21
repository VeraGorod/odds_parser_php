<?php

namespace Export;
require_once __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "vendor" . DIRECTORY_SEPARATOR . "autoload.php";
require_once __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "helpers" . DIRECTORY_SEPARATOR . "FileHandler.php";
require_once __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "db" . DIRECTORY_SEPARATOR . "connect.php";

use Helpers\FileHandler;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use \RedBeanPHP\R as R;


/**
 *
 */
class Exporter
{
    public $data = array();

    public function export($force = false)
    {
        $export_path = FileHandler::get_output_dir() . DIRECTORY_SEPARATOR . "export.xlsx";
        if (!$force && file_exists($export_path)) return;
        FileHandler::create_path_to_file($export_path);
        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        foreach ($this->get_names() as $key => $name) {
            $sheet->setCellValue($this->get_cell_letter($key) . "1", $name);
        }
        $row = 2;
        foreach (R::find("record", "status=7 AND type='match'") as $record) {
            $path = FileHandler::get_results_dir() . DIRECTORY_SEPARATOR . $record->id . ".json";
            if (!file_exists($path)) continue;
            $this->data = json_decode(file_get_contents($path), true);

            foreach ($this->get_names() as $key => $name) {
                if ($name == "id") $cell_data = $record->id;
                else {
                    $cell_data = $this->get_data_item($name);
                }

                $cell = $this->get_cell_letter($key) . $row;
                $sheet->setCellValue($cell, $cell_data);

            }
            $row++;
        }
        $writer = new Xlsx($spreadsheet);
        $writer->save($export_path);
    }

    public function get_names()
    {
        $names = [
            //"id",
            "Дата",
            "Клуб1",
            "Клуб2",
            "Счет1",
            "Счет2",
            "Счет3",
            "Счет4"
        ];
        for ($i = 1; $i <= 5; $i++) {
            $numbers = ["+1.5", "+2.5", "+3.5"];
            foreach ($numbers as $number) {
                $ous = ['over_open', 'under_open', 'over_close', 'under_close'];
                foreach ($ous as $ou) {
                    $names[] = "ou " . $number . " " . $ou . $i;
                }
            }
            $bts = ['yes_open', 'yes_close', 'no_open', 'no_close'];
            foreach ($bts as $bt) {
                $names[] = "bt_" . $bt . $i;
            }

        }
        return $names;
    }

    public function get_letters()
    {
        return [
            "A",
            "B",
            "C",
            "D",
            "E",
            "F",
            "G",
            "H",
            "I",
            "J",
            "K",
            "L",
            "M",
            "N",
            "O",
            "P",
            "Q",
            "R",
            "S",
            "T",
            "U",
            "V",
            "W",
            "X",
            "Y",
            "Z",
        ];
    }

    public function get_cell_letter($number)
    {
        $letters = $this->get_letters();
        if ($number < 26) return $letters[$number];
        $first  = intdiv($number, 26) - 1;
        $second = $number % 26;
        return $letters[$first] . $letters[$second];
    }

    public function get_data_item($name)
    {
        switch ($name) {
            case "Дата":
                $date = $this->data["data"]["date"];
                $ar   = explode(", ", $date);
                return implode(", ", [$ar[1], $ar[2]]);
            case "Клуб1":
                $data = $this->data["data"]["cloubs"];
                return explode(" - ", $data)[0];
            case "Клуб2":
                $data = $this->data["data"]["cloubs"];
                return explode(" - ", $data)[1];
            case "Счет1":
                if (empty($this->data["data"]["result_half"])) return "";
                return explode(":", $this->data["data"]["result_half"])[0];
            case "Счет2":
                if (empty($this->data["data"]["result_half"])) return "";
                return explode(":", $this->data["data"]["result_half"])[1];
            case "Счет3":
                if (empty($this->data["data"]["result"])) return "";
                return explode(":", $this->data["data"]["result"])[0];
            case "Счет4":
                if (empty($this->data["data"]["result"])) return "";
                return explode(":", $this->data["data"]["result"])[1];
            default:
                return $this->analyse_name($name);
        }


    }

    public function analyse_name($name)
    {
        $number = substr($name, -1);
        if(substr($name, 0, 2) == "ou"){
            $rate = substr($name, 4, 3);
            $type = substr($name, 8, -1);
            if(!empty($this->data[$number])) {
                return $this->data[$number]["ou"]["Over/Under +".$rate][$type];
            }
        }

        if(substr($name, 0, 2) == "bt"){
            $type = substr($name, 3, -1);
            if(!empty($this->data[$number])) {
                return $this->data[$number]["bt"][$type];
            }
        }

        return "undefined";
    }
}