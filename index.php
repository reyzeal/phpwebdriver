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
$accept = getallheaders();
if(key_exists('Accept',$accept) && $accept['Accept'] == 'application/json'){
    $API = true;
}else $API = false;
$addon = $role == 'panel'?'':'/instances/'.getenv('WA_USERNAME').'/';
if($_SERVER['REQUEST_METHOD'] == 'GET'){
    $page = isset($_GET['p'])?$_GET['p']:'index';

    if(!Session() && $page != 'login') {
        if($API){
            header('content-type: application/json');
            die(json_encode([
                'error' => true,
                'message' => 'fail',
                'data' => 'No session'
            ]));
        }
        header("Location: ".$addon."index.php?p=login");
        exit();
    }elseif($role != 'panel' && Session() && Session()['scan'] && ($page != 'scan' && $page != 'getImg')){
        if($API){
            header('content-type: application/json');
            die(json_encode([
                'error' => true,
                'message' => 'fail',
                'data' => 'Instance not logged in WhatsApp yet. Please scan QR Code in panel before using API.'
            ]));
        }
        header("Location: ".$addon."index.php?p=scan");
        exit();
    }
    switch ($page){
        case 'login':
            include "templates/login.php";
            break;
        case 'logout':
            unset($_SESSION[getenv('WA_USERNAME')]);
            Logout();
            if($API){
                header('content-type: application/json');
                die(json_encode([
                    'error' => false,
                    'message' => 'success',
                    'data' => null
                ]));
            }
            header("Location: ".$addon."index.php");
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
                    $dir = array_filter(scandir(__DIR__.'/instances'), function ($x){return $x!='.'&&$x!='..'&&is_dir(__DIR__.'/instances/'.$x);});
                    if($API){
                        $data = [];
                        foreach ($dir as $i){
                            $env = file_get_contents(__DIR__."/instances/$i/.env");
                            preg_match_all("(WA_PASSWORD=[^\n]+)",$env,$pass);
                            $pass = $pass[0][0];
                            $pass = str_replace("'",'',str_replace("WA_PASSWORD=","",$pass));
                            $data[] = [
                                'uid' => $i,
                                'password' => $pass,
                            ];
                        }
                        header('content-type: application/json');
                        die(json_encode([
                            'error' => false,
                            'message' => 'success',
                            'data' => $data
                        ]));
                    }else{
                        include "templates/admin.php";
                    }


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
        header("Location: ".$addon."index.php?p=login");
        exit();
    }
    switch ($page){
        case 'login':
            try{
                if($_POST['username'] == getenv('WA_USERNAME') && $_POST['password'] == getenv('WA_PASSWORD')){
                    if($role == 'panel')
                        Session([
                            'username' => getenv('WA_USERNAME'),
                            "timeout" => Carbon::now()->addHours(24),
                            "session" => uniqid(),
                            "scan" => null
                        ]);
                    else{
                        Session([
                            'username' => getenv('WA_USERNAME'),
                            "timeout" => Carbon::now()->addHours(24),
                            "session" => generate(),
                            "scan" => getImg()
                        ]);
                    }
                    if($API) {
                        header('content-type: application/json');
                        die(json_encode([
                            'error' => false,
                            'message' => 'success',
                            'data' => session_id()
                        ]));
                    }
                    else header("Location: ".$addon."index.php");
                    exit();
                }else{
                    if($API){
                        header('content-type: application/json');
                        die(json_encode([
                            'error' => true,
                            'message' => 'failed',
                            'data' => null
                        ]));
                    }else header("Location: ".$addon."index.php?p=login");
                }
            }
            catch (Exception $e){
                die($e);
            }
            exit();
            break;
        case 'send':
            if($role != 'panel'){
                $number = $_POST['number'];
                $message = $_POST['message'];
                send($number,$message);
                if($API){
                    header('content-type: application/json');
                    die(json_encode([
                        'error' => false,
                        'message' => 'success',
                        'data' => null
                    ]));
                }
            }else{
                if($API){
                    header('content-type: application/json');
                    die(json_encode([
                        'error' => true,
                        'message' => 'fail',
                        'data' => 'not instance'
                    ]));
                }
            }
            break;
        case 'index':
            if($role != 'panel'){
                $number = $_POST['number'];
                $message = $_POST['message'];
                send($number,$message);
            }else{
                createNewInstance($_POST['password']);

            }

            header("Location: ".$addon."index.php");
            break;
        case 'create':
            if($role == 'panel'){
                createNewInstance($_POST['password']);
                if($API){
                    header('content-type: application/json');
                    die(json_encode([
                        'error' => false,
                        'message' => 'success',
                        'data' => null
                    ]));
                }
            }else{
                if($API){
                    header('content-type: application/json');
                    die(json_encode([
                        'error' => true,
                        'message' => 'fail',
                        'data' => 'not admin panel'
                    ]));
                }
            }
            break;
        case 'destroy':
            if($role == 'panel'){
                destroyInstance($_POST['uuid']);
                if($API){
                    header('content-type: application/json');
                    die(json_encode([
                        'error' => false,
                        'message' => 'success',
                        'data' => null
                    ]));
                }
            }else{
                if($API){
                    header('content-type: application/json');
                    die(json_encode([
                        'error' => true,
                        'message' => 'fail',
                        'data' => 'not admin panel'
                    ]));
                }
            }

            header("Location: ".$addon."index.php");
            break;
    }
}
