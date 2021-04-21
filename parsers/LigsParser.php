<?php

namespace Parsers;

use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Chrome\ChromeDriver;
use Facebook\WebDriver\Remote\WebDriverCapabilityType;
use Facebook\WebDriver\WebDriverBy;


class LigsParser
{
    /**
     * @var ChromeDriver
     */
    public $driver;


    public function __construct($driver)
    {
        $this->driver = $driver;
    }

    public function get_ligs(){
        $this->driver->get("https://www.oddsportal.com/soccer/results/");
        sleep(2);
        $this->driver->takeScreenshot("1.png");
        $trs = $this->driver->findElements(WebDriverBy::cssSelector('table.table-main tr'));
        $country = "";
        $ligs = [];
        foreach ($trs as $tr){
            try{
                $country_tag =  $tr->findElement(WebDriverBy::cssSelector('th a'));
                if (!empty($country_tag)) $country = $country_tag->getText();
            }
            catch (\Throwable $e){
                $ligs_tags = $tr->findElements(WebDriverBy::cssSelector('td a'));
                foreach ($ligs_tags as $lig){
                    $name = $lig->getText();
                    if (empty($name)) continue;
                    $url = $lig->getAttribute("href");
                    $item = ["name" => $name, "country" => $country, "url"=>$url];
                    $ligs[] = $item;
                }
            }
        }

        usort($ligs, function($a,$b){
            return ($a['name']>$b['name']);
        });
        return $ligs;
    }


}