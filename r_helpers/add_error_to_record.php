<?php
/** @noinspection PhpComposerExtensionStubsInspection */

namespace RHelpers;

function add_error_to_record($record, $error){
    $errors = json_decode($record->errors, true);
    if ($errors == null) $errors = [];
    $errors[] = $error;
    $record->errors = json_encode($errors);
    return $record;
}