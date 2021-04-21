<?php

namespace Parsers;

use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Chrome\ChromeDriver;
use Facebook\WebDriver\Remote\WebDriverCapabilityType;
use Facebook\WebDriver\WebDriverBy;


class MatchParser
{
    /**
     * @var ChromeDriver
     */
    public $driver;
    public $data = [
        "data" => [
            "date" => "",
            "cloubs" => "",
            "result" => "",
            "result_half" => "",
        ],
        "1_x_2" => [
            "1_open" => "",
            "1_close" => "",
            "x_open" => "",
            "x_close" => "",
            "2_open" => "",
            "2_close" => "",
        ],
        "ah" => [
            "Asian handicap -1.5" => [
                "1_open" => "",
                "1_close" => "",
                "2_open" => "",
                "2_close" => "",
            ],
            "Asian handicap -1" => [
                "1_open" => "",
                "1_close" => "",
                "2_open" => "",
                "2_close" => "",
            ],
            "Asian handicap -0.5" => [
                "1_open" => "",
                "1_close" => "",
                "2_open" => "",
                "2_close" => "",
            ],
            "Asian handicap 0" => [
                "1_open" => "",
                "1_close" => "",
                "2_open" => "",
                "2_close" => "",
            ],
            "Asian handicap +0.5" => [
                "1_open" => "",
                "1_close" => "",
                "2_open" => "",
                "2_close" => "",
            ],
            "Asian handicap +1" => [
                "1_open" => "",
                "1_close" => "",
                "2_open" => "",
                "2_close" => "",
            ],
            "Asian handicap +1.5" => [
                "1_open" => "",
                "1_close" => "",
                "2_open" => "",
                "2_close" => "",
            ],
        ],
        "ou" => [
            "Over/Under +1.5" => [
                "over_open" => "",
                "over_close" => "",
                "under_open" => "",
                "under_close" => "",
            ],
            "Over/Under +2.5" => [
                "over_open" => "",
                "over_close" => "",
                "under_open" => "",
                "under_close" => "",
            ],
            "Over/Under +3.5" => [
                "over_open" => "",
                "over_close" => "",
                "under_open" => "",
                "under_close" => "",
            ],
        ],
        "bt" => [
            "yes_open" => "",
            "yes_close" => "",
            "no_open" => "",
            "no_close" => "",
        ],
    ];
    public $match_url;
    public $bookmakers_name;

    public function __construct($driver)
    {
        $this->driver = $driver;
    }

    public function get_match($match_url, $bookmakers_name)
    {
        $this->match_url = $match_url;
        $this->bookmakers_name = json_decode($bookmakers_name, true);
        foreach ($this->bookmakers_name as $bookmaker_name=>$bookmaker_number){
            $this->data[$bookmaker_number] = [
                "1_open" => "",
                "1_close" => "",
                "x_open" => "",
                "x_close" => "",
                "2_open" => "",
                "2_close" => "",
                "ou" => [
                    "Over/Under +1.5" => [
                        "over_open" => "",
                        "over_close" => "",
                        "under_open" => "",
                        "under_close" => "",
                    ],
                    "Over/Under +2.5" => [
                        "over_open" => "",
                        "over_close" => "",
                        "under_open" => "",
                        "under_close" => "",
                    ],
                    "Over/Under +3.5" => [
                        "over_open" => "",
                        "over_close" => "",
                        "under_open" => "",
                        "under_close" => "",
                    ],
                ],
                "bt" => [
                    "yes_open" => "",
                    "yes_close" => "",
                    "no_open" => "",
                    "no_close" => "",
                ],
            ];
        }
        $this->driver->get($match_url);
        sleep(2);
        $this->driver->takeScreenshot("1.png");
        $this->get_data();
        //$this->get_1_x_2();

        try {
            $this->get_ou();
        } catch (\Throwable $e) {
        }
        try {
            $this->get_bt();
        } catch (\Throwable $e) {
        }

        return $this->data;
    }

    public function get_1_x_2()
    {
        $attempts = 0;
        while (True) {
            try {
                $table = $this->driver->findElement(WebDriverBy::cssSelector('div#odds-data-table.bt-1'));
                if (empty($table)) throw new \Exception("таблица пока не загрузилась");
                $trs = $this->driver->findElements(WebDriverBy::cssSelector('table.table-main tr'));
                if (count($trs) == 0) throw new \Exception("строк пока нет");
                break;

            } catch (\Throwable $e) {
                if ($attempts >= 5) throw new \Exception($e);
                $attempts++;
                sleep(1);
            }
        }
        foreach ($trs as $tr) {
            try {
                $as = $tr->findElements(WebDriverBy::cssSelector('a'));
                foreach ($as as $a) {
                    $name = $a->getText();
                    if (!key_exists($name, $this->bookmakers_name)) continue;
                    $tds = $tr->findElements(WebDriverBy::cssSelector('td.odds'));
                    foreach ($tds as $number => $td) {
                        $id = 0;
                        switch ($number) {
                            case 0:
                                $id = 1;
                                break;
                            case 1:
                                $id = "x";
                                break;
                            case 2:
                                $id = 2;
                                break;
                        }
                        $this->data[$this->bookmakers_name[$name]]["{$id}_close"] = $td->getText();
                        $this->data[$this->bookmakers_name[$name]]["{$id}_open"] = $this->get_data_from_tooltip($td);

                    }
                }
            } catch (\Throwable $e) {
                continue;
            }
        }
    }

    public function get_data()
    {
        $attempts = 0;
        while (True) {
            try {
                $this->data["data"]["cloubs"] = $this->driver->findElement(WebDriverBy::tagName("h1"))->getText();
                if ($this->data["data"]["cloubs"] == "" || stripos($this->data["data"]["cloubs"], "-") === false) throw new \Exception();
                $this->data["data"]["date"] = $this->driver->findElement(WebDriverBy::cssSelector("p.date"))->getText();
                try {
                    $this->data["data"]["result"] = $this->get_result_from_string($this->driver->findElement(WebDriverBy::cssSelector("#event-status strong"))->getText());
                    $text = $this->driver->findElement(WebDriverBy::cssSelector("#event-status"))->getText();
                    preg_match("#\([0-9]{1,}\:[0-9]{1,}#", $text, $matches);
                    if(!empty($matches)){
                        $this->data["data"]["result_half"] = str_replace("(", "", $matches[0]);
                    }

                } catch (\Throwable $e) {
                    $this->data["data"]["result"] = "";
                }
                break;
            } catch (\Throwable $e) {

                if ($attempts >= 5) {
                    throw new \Exception($e);
                }
                sleep(1);
                $attempts++;
            }
        }


    }

    public function get_result_from_string($string){
        preg_match("#[0-9]{1,}\:[0-9]{1,}#", $string, $matches);
        if(!empty($matches)){
            return $matches[0];
        }
        return "";
    }

    public function get_data_from_tooltip(\Facebook\WebDriver\Remote\RemoteWebElement $td)
    {
        $text = "";
        $attempts = 0;
        while (true){
            if($attempts >=10) break;
            $this->driver->getMouse()->mouseMove($td->getCoordinates());
            sleep(2);
            $tooltip = $this->driver->findElement(WebDriverBy::id("tooltipdiv"));
            try {
                $strongs = $tooltip->findElements(WebDriverBy::cssSelector("strong"));
                foreach ($strongs as $strong_number => $strong) {
                    if ($strong->getText() == "Click to BET NOW") continue;
                    $text = $strong->getText();
                }
            } catch (\Throwable $e) {
                $attempts ++;
                $this->driver->getMouse()->mouseMove(null, 100, 100);
                sleep(1);
            }
            if ($text == "") {
                $this->driver->getMouse()->mouseMove(null, 100, 100);
                sleep(1);
                $attempts ++;
            }
            else break;

        }

        return $text;
    }

    public function get_ou()
    {
        $attempts = 0;
        $this->change_tab("O/U");
        while (True) {
            $inner_attempts = 0;
            $done = false;
            while (True) {
                try{
                    $table = $this->driver->findElement(WebDriverBy::cssSelector('div#odds-data-table.bt-2'));
                    if (empty($table)) throw new \Exception("таблица пока не загрузилась");
                    $trs = $table->findElements(WebDriverBy::cssSelector("div.table-container"));
                    if (count($trs) > 0) {
                        $done = true;
                        break;
                    }
                    else{
                        if ($inner_attempts >= 5) break;
                        sleep(2);
                        $inner_attempts++;
                    }
                }
                catch (\Throwable $e){
                    if ($inner_attempts >= 5) break;
                    sleep(2);
                    $inner_attempts++;
                }


            }
            if ($done) break;
            $attempts ++;
            if($attempts>=5){
                throw new \Exception("can't get bt");
            }
            $this->driver->navigate()->refresh();
            sleep(3);
            $this->change_tab("O/U");
        }
        $need = array();
        foreach ($trs as $tr) {
            try {
                $as = $tr->findElements(WebDriverBy::cssSelector("a"));
                foreach ($as as $a) {
                    $a_text = $a->getText();
                    if (!in_array(trim($a_text), ["Over/Under +2.5", "Over/Under +1.5", "Over/Under +3.5"])) continue;

                    $need[] = $tr;
                }
            } catch (\Throwable $e) {
                continue;
            }
        }

        foreach ($need as $tr) {
            try {
                $as = $tr->findElements(WebDriverBy::cssSelector("a"));
                foreach ($as as $a) {
                    $a_text = $a->getText();
                    if ($a_text == "Compare odds") $a->click();
                }
            } catch (\Throwable $e) {
                continue;
            }
        }
        sleep(1);
        foreach ($need as $tr) {
            try {
                $as = $tr->findElements(WebDriverBy::cssSelector("a"));
                foreach ($as as $a) {
                    $a_text = $a->getText();
                    if (in_array(trim($a_text), ["Over/Under +2.5", "Over/Under +1.5", "Over/Under +3.5"])) break;
                }
                if (empty($a_text)) continue;
                $brokers = $tr->findElements(WebDriverBy::cssSelector("table tr"));
                foreach ($brokers as $broker) {
                    try {
                        $as = $broker->findElements(WebDriverBy::cssSelector('a'));
                        foreach ($as as $a) {
                            $name = $a->getText();
                            if (!key_exists($name, $this->bookmakers_name)) continue;
                            $tds = $broker->findElements(WebDriverBy::cssSelector('td.odds'));
                            foreach ($tds as $number => $td) {
                                $id = 0;
                                switch ($number) {
                                    case 0:
                                        $id = "over";
                                        break;
                                    case 1:
                                        $id = "under";
                                        break;
                                }
                                $this->data[$this->bookmakers_name[$name]]["ou"][$a_text]["{$id}_close"] = $td->getText();
                                $this->data[$this->bookmakers_name[$name]]["ou"][$a_text]["{$id}_open"] = $this->get_data_from_tooltip($td);
                            }
                        }
                    } catch (\Throwable $e) {
                        continue;
                    }
                }
            } catch (\Throwable $e) {
                continue;
            }
        }

    }

    public function change_tab($name)
    {
        if($name == "bt"){
            $exist = false;
            $more = $this->driver->findElement(WebDriverBy::id("tab-sport-others"));
            $this->driver->getMouse()->mouseMove($more->getCoordinates());
            sleep(1);
            foreach ($this->driver->findElements(WebDriverBy::cssSelector("li.more")) as $li){
                foreach ($li->findElements(WebDriverBy::cssSelector("a")) as $a){
                    if($a->getText() == "Both Teams to Score") {
                        $a->click();
                        $exist = true;
                        sleep(1);
                        break;
                        break;
                    }
                }
            }
            if (!$exist) throw new \Exception("tab {$name} not found");
        }
        else {
            $nav = $this->driver->findElement(WebDriverBy::id("tab-nav-main"));
            $tabs = $nav->findElements(WebDriverBy::cssSelector("li"));
            $exist = false;
            foreach ($tabs as $tab) {
                $tab_text = $tab->getText();
                if ($tab_text == $name) {
                    $this->driver->getMouse()->mouseMove($tab->getCoordinates())->mouseDown();
                    //$tab->click();
                    $exist = true;
                    sleep(3);
                    $this->driver->takeScreenshot("1.png");
                }
            }
            if (!$exist) throw new \Exception("tab {$name} not found");
        }

    }

    public function get_bt()
    {
        $attempts = 0;
        $this->change_tab("bt");
        while (True) {
            $inner_attempts = 0;
            $done = false;
            while (True) {
                try{
                    $table = $this->driver->findElement(WebDriverBy::cssSelector('div#odds-data-table.bt-13'));
                    if (empty($table)) throw new \Exception("таблица пока не загрузилась");
                    $trs = $table->findElements(WebDriverBy::cssSelector("table.table-main tr"));
                    if (count($trs) > 0) {
                        $done = true;
                        break;
                    }
                    else{
                        if ($inner_attempts >= 5) break;
                        sleep(2);
                        $inner_attempts++;
                    }
                }
                catch (\Throwable $e){
                    if ($inner_attempts >= 5) break;
                    sleep(2);
                    $inner_attempts++;
                }

            }
            if ($done) break;
            $attempts ++;
            if($attempts>=5){
                throw new \Exception("can't get bt");
            }
            $this->driver->navigate()->refresh();
            sleep(3);
            $this->change_tab("bt");
        }

        foreach ($trs as $tr) {
            try {
                $as = $tr->findElements(WebDriverBy::cssSelector('a'));
                foreach ($as as $a) {
                    $name = $a->getText();
                    if (!key_exists($name, $this->bookmakers_name)) continue;
                    $tds = $tr->findElements(WebDriverBy::cssSelector('td.odds'));
                    foreach ($tds as $number => $td) {
                        $id = 0;
                        switch ($number) {
                            case 0:
                                $id = "yes";
                                break;
                            case 1:
                                $id = "no";
                                break;
                        }
                        $this->data[$this->bookmakers_name[$name]]["bt"]["{$id}_close"] = $td->getText();
                        $this->data[$this->bookmakers_name[$name]]["bt"]["{$id}_open"] = $this->get_data_from_tooltip($td);
                    }
                }
            } catch (\Throwable $e) {
                continue;
            }

        }

    }
}