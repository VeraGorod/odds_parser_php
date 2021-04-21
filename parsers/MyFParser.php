<?php
/** @noinspection PhpComposerExtensionStubsInspection */

namespace Parsers;

require_once __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "vendor" . DIRECTORY_SEPARATOR . "autoload.php";
require_once __DIR__ . DIRECTORY_SEPARATOR . "LigsParser.php";
require_once __DIR__ . DIRECTORY_SEPARATOR . "LigParser.php";
require_once __DIR__ . DIRECTORY_SEPARATOR . "MatchParser.php";
require_once __DIR__ . DIRECTORY_SEPARATOR . "brokersParser.php";

use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\WebDriverCapabilityType;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverDimension;
use ZipArchive;

class MyFParser
{
    public $driver = null;
    public $config = null;

    function __construct($thread_number)
    {
        $host = '127.0.0.1:891'.$thread_number;
        $capabilities = DesiredCapabilities::phantomjs();
        $capabilities->setCapability('phantomjs.page.settings.userAgent', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/75.0.3770.100 Safari/537.36');
        $this->driver = RemoteWebDriver::create($host, $capabilities);
        $this->config = require 'config.php';

        $window = new WebDriverDimension(1366, 768);
        $this->driver->manage()->window()->setSize($window);
        $this->auth();
        return $this;
    }

    function __destruct()
    {
        $this->end();
    }

    function get_driver(){
        return $this->driver;
    }

    function end(){
        $this->driver->close();
    }

    public function auth()
    {
        $this->driver->get('http://www.oddsportal.com/login');
        echo $this->driver->getCurrentURL();
        $this->driver->takeScreenshot("1.png");
        $input_login = WebDriverBy::id('login-username1');
        $input_pass = WebDriverBy::id('login-password1');
        $login_button = WebDriverBy::cssSelector('.form button.inline-btn-2');

        $this->driver->findElement($input_login)->sendKeys($this->config["user"]);
        $this->driver->findElement($input_pass)->sendKeys($this->config["password"]);
        $this->driver->findElement($login_button)->click();

        sleep(3);
    }
    
    public function get_ligs(){
        $parser = new LigsParser($this->driver);
        return $parser->get_ligs();
    }

    public function get_lig_data($lig_url, $years){
        $parser = new LigParser($this->driver);
        return $parser->get_urls($lig_url, $years);
    }

    public function get_match_data($match_url, $bookmaker_name){
        $parser = new MatchParser($this->driver);
        return $parser->get_match($match_url, $bookmaker_name);
    }

    public function get_brokers($match_url){
        $parser = new brokersParser($this->driver);
        return $parser->get_brokers($match_url);
    }
}