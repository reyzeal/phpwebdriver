<?php
use Carbon\Carbon;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Dotenv\Dotenv;
$dotenv = Dotenv::createImmutable(__DIR__.DIRECTORY_SEPARATOR.'..','.env');
$dotenv->load();
function Session($data = null){
    if ($data == null){
        $x = isset($_SESSION[getenv('WA_USERNAME')])?unserialize($_SESSION[getenv('WA_USERNAME')]):null;
        if ($x && $x['timeout'] < Carbon::now()) return null;
        return $x;
    }
    $_SESSION[getenv('WA_USERNAME')] = serialize($data);
    return true;
}

/**
 * @return RemoteWebDriver
 */
function SessionWeb($target = null){
    $host = 'http://localhost:4444'; // this is the default
    $sessions = RemoteWebDriver::getAllSessions($host);

    $session = null;
    try{
        if(!$session)
            $session = file_get_contents("session");
        else
            $session = $target;
    }
    catch (Exception $e){

    }
    foreach ($sessions as $i){
        if($i['id'] == $session) $sid = $i;
    }
    if($sid){
        $driver = RemoteWebDriver::createBySessionID($session, $host);
    }else{
        $userdir = getenv('WA_USERDIR');
        $options = new ChromeOptions();
        $options->addArguments(["--user-data-dir=$userdir"]);
        $caps = DesiredCapabilities::chrome();
        $caps->setCapability(ChromeOptions::CAPABILITY,$options);
        $driver = RemoteWebDriver::create($host, $caps);
    }
    file_put_contents("session",$driver->getSessionID());
    try{
        $driver->executeScript("document.querySelector('._1WZqU.PNlAR').click();");
    }
    catch (Exception $e){
    }
    return $driver;
}