<?php

namespace Parsers;

use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Chrome\ChromeDriver;
use Facebook\WebDriver\Remote\WebDriverCapabilityType;
use Facebook\WebDriver\WebDriverBy;


class LigParser
{
    /**
     * @var ChromeDriver
     */
    public $driver;
    public $data = array();
    public $lig_url;
    public $links = array();

    public function __construct($driver)
    {
        $this->driver = $driver;
    }

    public function get_urls($lig_url, $other_years){
        $this->lig_url = $lig_url;
        $this->driver->get($lig_url);
        sleep(2);
        $this->data["urls"] = array();
        $this->get_nexts();
        $no_next = false;
        while (!$no_next){

            $attempts = 0;
            while ($attempts <= 10) {
                try{
                    $this->get_matches();
                    break;
                }
                catch(\Throwable $e){
                    $attempts++;
                }
            }

            $attempts = 0;
            while ($attempts <= 10) {
                try{
                    if(!$this->get_next()) $no_next = true;
                    break;
                }
                catch(\Throwable $e){
                    $attempts++;
                }
            }
            
            
        }
        $attempts = 0;
            while ($attempts <= 10) {
                try{
                    if($other_years) $this->get_other_year();
                    break;
                }
                catch(\Throwable $e){
                    $attempts++;
                }
            }
       

        return $this->data;
    }

    public function get_matches(){

        $as = $this->driver->findElements(WebDriverBy::cssSelector('#tournamentTable td.table-participant a'));
        foreach ($as as $a){
            $link = $a->getAttribute("href");
            if(!in_array($link,$this->data["urls"])) $this->data["urls"][] = $link;
        }
    }

    public function get_next()
    {
        $next = array_shift($this->links);
        if(!is_null($next)) {
            $this->driver->get($next);
            sleep(5);
            return true;
        }
        return false;
    }

    public function get_other_year()
    {
        $this->data["next"] = array();
        $lis = $this->driver->findElements(WebDriverBy::cssSelector(".main-menu2.main-menu-gray ul.main-filter li span.inactive"));
        foreach ($lis as $li){
                try{
                    $link = $li->findElement(WebDriverBy::tagName("a"))->getAttribute("href");
                    if(stripos($link, "https://www.oddsportal.com/soccer") === false) {
                        if(stripos($link, "soccer") === false) break;
                        $full_link = "https://www.oddsportal.com" . $link;
                        if($this->compare_with_original_url($full_link)) $data["next"][] = $full_link;
                    }
                    if($this->compare_with_original_url($link)) $this->data["next"][] = $link;
                }
                catch (\Throwable $e){
                    continue;
                }

        }
    }

    public function compare_with_original_url($url){
        return !($this->lig_url==$url);
    }

    public function get_nexts()
    {
        $as = $this->driver->findElements(WebDriverBy::cssSelector("#pagination a"));
        $links = array();
        foreach ($as as $a){
            $link = $a->getAttribute("href");
            if(stripos($link, "page") === false) continue;
            if(stripos($link, "https://www.oddsportal.com/soccer") === false) {
                if(stripos($link, "soccer") === false) break;
                $data["next"] = "https://www.oddsportal.com" . $link;
            }
            if(!in_array($link, $links)) $links[] = $link;
        }
        var_dump($links);
        $this->links = $links;
    }

}