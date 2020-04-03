<?php
/**
 * Rizal Ardhi Rahmadani, April 2020
 *
 * Project WhatsApp Messenger
 */
ini_set('max_execution_time',0);
$ENDPOINT = 'http://localhost/';

/**
 * @param $page
 * @param null $instance
 * @return string
 */
function buildURL($page, $instance=null){
    global $ENDPOINT;
    if(!$instance) return "$ENDPOINT?p=$page";
    return "$ENDPOINT/instances/$instance?p=$page";
}

/**
 * @param $uid
 * @param $password
 * @param string $instance
 * @return bool|mixed|string
 */
function login($uid, $password, $instance = false){
    if(!$instance)
        $c = curl_init(buildURL('login'));
    else
        $c = curl_init(buildURL('login', $uid));

    curl_setopt($c, CURLOPT_POST, 1); // POST
    curl_setopt($c, CURLOPT_POSTFIELDS, "username=$uid&password=$password");
    curl_setopt($c, CURLOPT_HTTPHEADER, ["Accept: application/json"]);
    curl_setopt($c, CURLOPT_TIMEOUT, 0);
    curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);

    $feedback = curl_exec($c);
    $feedback = json_decode($feedback);

    return $feedback;
}

/**
 * @param $session
 * @param bool $instance
 * @return bool|mixed|string
 */
function logout($session, $instance = false){
    if(!$instance)
        $c = curl_init(buildURL('logout'));
    else
        $c = curl_init(buildURL('logout', $instance));

    curl_setopt($c, CURLOPT_POST, 0); // POST
    curl_setopt($c, CURLOPT_HTTPHEADER, ["Accept: application/json","Cookie: PHPSESSID=$session"]);
    curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);

    $feedback = curl_exec($c);
    $feedback = json_decode($feedback);

    return $feedback;
}

/**
 * @param $session
 * @return bool|string
 */
function listInstances($session){
    $c = curl_init(buildURL('index'));
    curl_setopt($c, CURLOPT_POST, 0); // GET
    curl_setopt($c, CURLOPT_HTTPHEADER, ["Accept: application/json","Cookie: PHPSESSID=$session"]);
    curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);

    $feedback = curl_exec($c);
    $feedback = json_decode($feedback);
    return $feedback;
}

/**
 * @param $password
 * @param $session
 * @return bool|mixed|string
 */
function createInstance($password, $session){
    $c = curl_init(buildURL('create'));

    curl_setopt($c, CURLOPT_POST, 1); // POST
    curl_setopt($c, CURLOPT_POSTFIELDS, "password=$password");
    curl_setopt($c, CURLOPT_TIMEOUT, 0);
    curl_setopt($c, CURLOPT_HTTPHEADER, ["Accept: application/json","Cookie: PHPSESSID=$session"]);
    curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);

    $feedback = curl_exec($c);
    $feedback = json_decode($feedback);

    return $feedback;
}

/**
 * @param $uid
 * @param $session
 * @return bool|mixed|string
 */
function destroyInstance($uid, $session){
    $c = curl_init(buildURL('destroy'));

    curl_setopt($c, CURLOPT_POST, 1); // POST
    curl_setopt($c, CURLOPT_POSTFIELDS, "uuid=$uid");
    curl_setopt($c, CURLOPT_TIMEOUT, 0);
    curl_setopt($c, CURLOPT_HTTPHEADER, ["Accept: application/json","Cookie: PHPSESSID=$session"]);
    curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);

    $feedback = curl_exec($c);
    $feedback = json_decode($feedback);

    return $feedback;
}

/**
 * function to send message
 * @param $number
 * @param $message
 * @param $session
 * @return bool|mixed|string
 */
function send($number, $message, $instance, $session){
    $c = curl_init(buildURL('send', $instance));
    curl_setopt($c, CURLOPT_POST, 1); // POST
    curl_setopt($c, CURLOPT_POSTFIELDS, "number=$number&message=$message");
    curl_setopt($c, CURLOPT_TIMEOUT, 0);
    curl_setopt($c, CURLOPT_HTTPHEADER, ["Accept: application/json","Cookie: PHPSESSID=$session"]);
    curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);

    $feedback = curl_exec($c);
    $feedback = json_decode($feedback);

    return $feedback;
}

print_r(send('6285927475702',"ok ok\nHere line breaker",'5e8734495cdfb', 'ko1iqcon04llhs088em234n0go'));

