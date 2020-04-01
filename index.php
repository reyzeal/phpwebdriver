<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require __DIR__."/vendor/autoload.php";
require __DIR__."/lib/session.php";
require __DIR__."/lib/core.php";
use Carbon\Carbon;
$role = getenv('WA_ROLE');
if($_SERVER['REQUEST_METHOD'] == 'GET'){
    $page = isset($_GET['p'])?$_GET['p']:'index';

    if(!Session() && $page != 'login') {
        header('Location: index.php?p=login');
        exit();
    }elseif($role != 'panel' && Session() && Session()['scan'] && ($page != 'scan' && $page != 'getImg')){
        header('Location: index.php?p=scan');
        exit();
    }
    switch ($page){
        case 'login':
            include "templates/login.php";
            break;
        case 'logout':
            session_destroy();
            Logout();
            header('Location: index.php');
            exit();
            break;
        case "scan":
            include "templates/scan.php";
            break;
        case "getImg":
            $r = getImg();
            if($r)
                echo getImg();
            else{
                $x = Session();
                $x['scan'] = $r;
                Session($x);
            }
            break;
        case 'index':

            switch ($role){
                case 'panel':
                    $dir = array_filter(scandir(__DIR__.'/instances'), function ($x){return $x!='.'&&$x!='..';});
                    include "templates/admin.php";
                    break;
                case 'worker':
                    include "templates/index.php";
                    break;
            }

            break;
        case 'upload':
            include "templates/form.php";
            break;
    }
}
else{
    $page = isset($_GET['p'])?$_GET['p']:'index';
    if(!Session() && $page != 'login'){
        header('Location: index.php?p=login');
        exit();
    }
    switch ($page){
        case 'login':
            try{
                if($_POST['username'] == getenv('WA_USERNAME') && $_POST['password'] == getenv('WA_PASSWORD')){
                    Session([
                        'username' => getenv('WA_USERNAME'),
                        "timeout" => Carbon::now()->addHours(24),
                        "session" => uniqid(),
                        "scan" => null
                    ]);
                    header('Location: index.php');
                    exit();
                }else{
                    header("Location: index.php?p=login");
                }
            }
            catch (Exception $e){
                die($e);
            }
//            header('Location: index.php?p=login');
            exit();
            break;
        case 'index':
            if($role != 'panel'){
                $number = $_POST['number'];
                $message = $_POST['message'];
                send($number,$message);
            }else{
                createNewInstance($_POST['password']);
            }

            header("Location: index.php");
            break;
        case 'destroy':
            if($role == 'panel'){
                destroyInstance($_POST['uuid']);
            }
            header("Location: index.php");
            break;
        case 'upload':
            try{
                $file = $_FILES['upload'];
                $filename = $_POST['filename'];
                $path = __DIR__."/upload/$filename.csv";
                move_uploaded_file($file['tmp_name'], $path);
                $content = file_get_contents($path);
                preg_match_all("([\w\d+.]+)",$content,$match);
                $content = [];
                foreach ($match[0] as $i){
                    $content[] = floatval($i);
                }
                $model = json_decode(file_get_contents(__DIR__.'/lib/model.json'));
                $key = array_keys($model);
                $row = [];
                $row[] = $key;
                $dataset = [];
                foreach ($content as $i){
                    $c = curl_init();
                    curl_setopt($c, CURLOPT_URL, "https://aclmaxgl11ck:SjfRKAx7@rest.tyntec.com/nis/v1/gnv?msisdn=+$i");
                    curl_setopt($c, CURLOPT_HTTPHEADER, ['Accept: application/json']);
                    curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
                    $server_output = curl_exec($c);
                    curl_close ($c);

                    $json = json_decode($server_output);
                    print_r($json);
                    $temp = [];
                    foreach ($key as $j){
                        if(array_key_exists($i, $json)){
                            $temp[] = $json[$i];
                        }else{
                            $temp[] = "";
                        }
                    }
                    $row[] = $temp;
                    $dataset[] = $json;
                }
                $output = json_encode($dataset);
                file_put_contents(__DIR__."/result/$filename.json",$output);
                $csv = new ParseCsv\Csv();
                $csv->save(__DIR__."/result/$filename.csv", $row);
//                echo json_encode($content);
            }catch (Exception $e){
                die($e);
            }
            header("Location: index.php");
            break;
        case 'download':
            break;
    }
}
