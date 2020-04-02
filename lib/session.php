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
function SessionWeb($target = null, $userdir = 'tmp_files'){
    $host = 'http://localhost:4444'; // this is the default
    $sessions = RemoteWebDriver::getAllSessions($host);

    $session = null;
    try{
        if(!$session)
            $session = file_get_contents(__DIR__."/../session");
        else
            $session = $target;
    }
    catch (Exception $e){

    }
    $sid = null;
    foreach ($sessions as $i){
        if($i['id'] == $session) $sid = $i;
    }
    if($sid && strlen($session) > 0){
        $driver = RemoteWebDriver::createBySessionID($session, $host);
    }
    else{
        if(getenv('WA_ROLE') != 'panel')
            $userdir = getenv('WA_USERDIR');
        $options = new ChromeOptions();
        $options->addExtensions([__DIR__.'/../dcsp.zip']);
        $options->addArguments(["--user-data-dir=$userdir"]);
        $caps = DesiredCapabilities::chrome();
        $caps->setCapability(ChromeOptions::CAPABILITY,$options);
        $driver = RemoteWebDriver::create($host, $caps);
    }
    file_put_contents(__DIR__."/../session",$driver->getSessionID());
    try{
        if(strpos($driver->getTitle(),"WhatsApp"))
            $driver->executeScript("document.querySelector('._1WZqU.PNlAR').click();");
    }
    catch (Exception $e){
    }
    return $driver;
}