<?php
/** @noinspection PhpUndefinedFieldInspection */
namespace RHelpers;
use \RedBeanPHP\R as R;

function add_new_record($url, $type, $other_years=False, $broker=""){
    $record = R::dispense("record");
    $record->type = $type;
    $record->url = $url;
    $record->status = 0;
    $record->attempts = 0;
    $record->added_at = date("Y-m-d H:i:s");
    $record->updated_at = date("Y-m-d H:i:s");
    $record->thread = 0;
    $record->other_years = $other_years;
    $record->broker = $broker;
    return $record;
}
