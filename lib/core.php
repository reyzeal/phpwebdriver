<?php

require_once(__DIR__ . '/../vendor/autoload.php');
require_once(__DIR__ . '/session.php');
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
function generate(){
    $driver = SessionWeb();
    if (strpos( $driver->getTitle(),"WhatsApp")!=-1 && strpos( $driver->getTitle(),"Share on WhatsApp")==-1){
        return $driver->getSessionID();
    }
    $driver->get("https://web.whatsapp.com/");
    return $driver->getSessionID();
}

function getImg()
{
    $driver = SessionWeb();
    try{
        $msg = $driver->findElement(WebDriverBy::xpath('//div[@id="side"]//div[@role="button" and @title="Menu"]'));
        $error = false;
    }
    catch (Exception $e){
        $error = true;
    }
    if ($driver->getTitle() == "WhatsApp" && $error) {
        try {
            $img = $driver->findElement(WebDriverBy::xpath('//canvas/ancestor::div[@data-ref]//span/div'));
            $img->click();
        } catch (Exception $e) {

        }
        try{
            $driver->wait(5, 1000)->until(
                WebDriverExpectedCondition::visibilityOfElementLocated(WebDriverBy::tagName('canvas'))
            );
        }catch (Exception $e){
            return getImg();
        }

        return $driver->executeScript("return document.querySelector('canvas').toDataURL()");
    }
    return null;
}
function send($n,$m){
    ini_set("max_execution_time",0);
    $m = urlencode($m);
    $driver = SessionWeb();
    $driver->get("https://api.whatsapp.com/send?phone=$n&text=$m&source=&data=");
    $driver->wait(10, 1000)->until(
        WebDriverExpectedCondition::visibilityOfElementLocated(WebDriverBy::xpath('//*[contains(@class,"_whatsapp_www__block_action")]'))
    );
    $driver->executeScript("document.querySelector('a[title]').click();");

    $driver->wait(10, 1000)->until(
        WebDriverExpectedCondition::visibilityOfElementLocated(WebDriverBy::xpath('//a[contains(text(),"use WhatsApp Web")]'))
    );
//    $driver->executeScript("document.querySelector('a[title]').click();");
    $driver->executeScript("document.querySelector('#fallback_block a._36or:not(._2z07)').click();");

    $driver->wait(40, 1000)->until(
        WebDriverExpectedCondition::visibilityOfElementLocated(WebDriverBy::cssSelector('[data-icon=send]'))
    );
    try{
        $msg = $driver->findElement(WebDriverBy::xpath('//*[contains(text(),"Phone number shared via url is invalid.")]'));
        $error = true;
    }
    catch (Exception $e){}
    $driver->executeScript("document.querySelector('[data-icon=send]').click();");
    $error = false;
    return !$error;
}
function Logout(){
    ini_set("max_execution_time",0);
    $driver = SessionWeb();
    $driver->executeScript("localStorage.clear()");
    $driver->get("https://web.whatsapp.com/");
}
function cpy($source, $dest){
    if(is_dir($source)) {
        $dir_handle=opendir($source);
        while($file=readdir($dir_handle)){
            if($file!="." && $file!=".."){
                if(is_dir($source."/".$file)){
                    if(!is_dir($dest."/".$file)){
                        mkdir($dest."/".$file);
                    }
                    cpy($source."/".$file, $dest."/".$file);
                } else {
                    copy($source."/".$file, $dest."/".$file);
                }
            }
        }
        closedir($dir_handle);
    } else {
        copy($source, $dest);
    }
}
function createNewInstance($password){
    $uuid = uniqid();
    $DIR = explode(DIRECTORY_SEPARATOR,__DIR__);
    array_pop($DIR);
    $DIR = implode(DIRECTORY_SEPARATOR,$DIR);
    $path = $DIR."/instances/$uuid";
    $path_usr = ($DIR."/instances/$uuid/tmp_files");
    mkdir($path);
    mkdir($path."/lib");
    mkdir($path."/tmp_files");
    mkdir($path."/templates");
    mkdir($path."/vendor");
    copy($DIR."/index.php",$path."/index.php");
    copy($DIR."/dcsp.zip",$path."/dcsp.zip");
    cpy($DIR."/vendor",$path."/vendor");
    cpy($DIR."/lib",$path."/lib");
    cpy($DIR."/templates",$path."/templates");\
    file_put_contents($path."/.env","WA_USERNAME=$uuid\nWA_PASSWORD='$password'\nWA_ROLE=worker\nWA_USERDIR='$path_usr'");
    file_put_contents($path."/session","");
}
function rmdir_recursive($directory, $delete_parent = null)
{
    $files = glob($directory . '/{,.}[!.,!..]*',GLOB_MARK|GLOB_BRACE);
    foreach ($files as $file) {
        if (is_dir($file)) {
            rmdir_recursive($file, 1);
        } else {
            unlink($file);
        }
    }
    if ($delete_parent) {
        rmdir($directory);
    }
}
function destroyInstance($uuid){
    $DIR = __DIR__.'/..';
    $path = $DIR."/instances/$uuid";
    try{
        $env = file_get_contents($path."/.env");
        preg_match_all("(WA_USERDIR=[^\n]+)",$env,$dir);
        $dir = $dir[0][0];
        $dir = str_replace("'",'',str_replace("WA_USERDIR=","",$dir));
        $session = SessionWeb(file_get_contents($path."/session"), $dir);
        $session->close();
    }catch (Exception $e){

    }
    rmdir_recursive(realpath($path),1);
}