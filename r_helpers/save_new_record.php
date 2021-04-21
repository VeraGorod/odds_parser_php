<?php


use \RedBeanPHP\R as R;

function save_new_record($record){
    $exist = R::find("record", "url='" . $record->url . "'");
    if(!empty($exist)) return;
    R::store($record);
}
