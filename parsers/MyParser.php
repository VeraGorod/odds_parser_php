<?php
/** @noinspection PhpComposerExtensionStubsInspection */

namespace Parsers;

require_once __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "vendor" . DIRECTORY_SEPARATOR . "autoload.php";
require_once __DIR__ . DIRECTORY_SEPARATOR . "LigsParser.php";
require_once __DIR__ . DIRECTORY_SEPARATOR . "LigParser.php";
require_once __DIR__ . DIRECTORY_SEPARATOR . "MatchParser.php";
require_once __DIR__ . DIRECTORY_SEPARATOR . "brokersParser.php";

use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Chrome\ChromeDriver;
use Facebook\WebDriver\Remote\WebDriverCapabilityType;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use ZipArchive;

class MyParser
{
    public $driver = null;
    public $config = null;

    function __construct($thread)
    {
        putenv("webdriver.chrome.driver=" . __DIR__ . DIRECTORY_SEPARATOR ."chromedriver.exe");

        $options = new ChromeOptions();
        $options->addArguments( ['--disable-gpu'] );
        $options->addExtensions([__DIR__ . DIRECTORY_SEPARATOR . "extension_3_22_10_0.crx"]);
        $caps = DesiredCapabilities::chrome();
        $caps->setCapability(ChromeOptions::CAPABILITY, $options);



        $this->driver = ChromeDriver::start($caps);

        $this->config = require 'config.php';
        $this->activate_proxy();
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
        $attempts = 0;
        while ($attempts <=10){
            try{
                $this->driver->get('https://www.oddsportal.com/login/');
                break;
            }
            catch (\Throwable $e){
                echo $e;
                $attempts++;
            }
        }
        $attempts = 0;
		while (true){
			$inner_attempts = 0;
			$done = false;
            $this->driver->wait(60)->until(
                WebDriverExpectedCondition::titleIs('Odds Portal: Login')
            );
			while ($inner_attempts <=10){
				try{
					$input_login = WebDriverBy::id('login-username1');
					$input_pass = WebDriverBy::id('login-password1');
					$login_button = WebDriverBy::cssSelector('.form button.inline-btn-2');

					$this->driver->findElement($input_login)->sendKeys($this->config["user"]);
					$this->driver->findElement($input_pass)->sendKeys($this->config["password"]);
					$this->driver->findElement($login_button)->click();
					$done = true;
					break;
				}
				catch (\Throwable $e){
					echo $e;
				}
				$inner_attempts ++;
				sleep(1);
			}
			if($done) break;
			$this->driver->navigate()->refresh();
            sleep(3);
			$attempts ++;
			if($attempts >=10) exit(0);
		}


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

    public function activate_proxy()
    {
        $this->driver->get("chrome-extension://olabgabaeihfgiagbfeldcaibecoeonn/popup/popup.html");
        sleep(3);
        $attempts = 0;
        while ($attempts <=10) {
            try{
                //$inner = $this->driver->findElement(WebDriverBy::cssSelector(".MainContainer page-switch"));
                $this->driver->getMouse()->mouseMove(null, 200, 340)->click();
                sleep(5);
                break;
            }
            catch (\Throwable $e){
                $attempts ++;
                if($attempts>=10){
                    throw new \Exception("не удалось включить прокси");
                }
                $this->driver->get("chrome-extension://olabgabaeihfgiagbfeldcaibecoeonn/popup/popup.html");
                sleep(5);
            }

        }
    }
}