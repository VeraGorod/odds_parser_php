<?php

namespace Route;

require_once __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "parsers" . DIRECTORY_SEPARATOR . "get_ligs.php";

\Parsers\get_ligs();

try{
    require_once __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "data_helpers" . DIRECTORY_SEPARATOR . "DataGetter.php";
    $ligs = \DataHelpers\DataGetter::get_ligs();
    echo json_encode(["result"=>"success", "ligs"=>$ligs]);
}
catch (\Throwable $e){
    echo json_encode(["result"=>"error"]);
}
