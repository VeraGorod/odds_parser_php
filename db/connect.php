<?php
require_once __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "vendor" . DIRECTORY_SEPARATOR . "autoload.php";
use \RedBeanPHP\R as R;

R::setup( 'mysql:host=localhost;dbname=odds', 'odds', 'odds' );

if ( !R::testConnection() )
{
    exit ('Нет соединения с базой данных');
}