<?php

namespace Parsers;

use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Chrome\ChromeDriver;
use Facebook\WebDriver\Remote\WebDriverCapabilityType;
use Facebook\WebDriver\WebDriverBy;


class BrokersParser
{
    /**
     * @var ChromeDriver
     */
    public $driver;
    public $data = array();
    public $match_url;

    public function __construct($driver)
    {
        $this->driver = $driver;
    }

    public function get_brokers($match_url){
        $this->driver->get($match_url);
        sleep(2);
        $this->parse_brokers();
        return $this->data;
    }

    public function parse_brokers()
    {
        $trs = $this->driver->findElements(WebDriverBy::cssSelector('table.table-main tr'));
        foreach ($trs as $tr){
            try{
                $td = $tr->findElement(WebDriverBy::tagName("td"));
                $a = $td->findElement(WebDriverBy::cssSelector("div.l a.name"));
            }
            catch (\Throwable $e){
                continue;
            }

            $this->data[] = $a->getText();
        }
    }


}