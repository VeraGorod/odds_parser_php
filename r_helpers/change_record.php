<?php
/** @noinspection PhpComposerExtensionStubsInspection */


use \RedBeanPHP\R as R;

function change_record($record){
    $record->updated_at = date("Y-m-d H:i:s");
    if(is_array($record->errors)) $record->errors = json_encode($record->errors);
    R::store($record);
}
