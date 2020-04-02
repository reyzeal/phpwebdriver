<?php
/**
 * Rizal Ardhi Rahmadani, April 2020
 *
 * Project WhatsApp Messenger
 */

$ENDPOINT = 'http://localhost';

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
    curl_setopt($c, CURLOPT_HTTPHEADER, ["Accept: application/json","Cookie: PHPSESSID=$session"]);
    curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);

    $feedback = curl_exec($c);
    $feedback = json_decode($feedback);

    return $feedback;
}


echo "=====EXAMPLE LOGIN SUCCESS=====\n";
// after login, just save the session data elsewhere..
//$session = login('admin','123123');
//print_r($session);

echo "=====EXAMPLE LOGIN FAILED=====\n";
//$session2 = login('admin','xx');
//print_r($session2);

echo "=====EXAMPLE LIST INSTANCES=====\n";
//print_r(listInstances('kc4uc9lg8o9ugv30er5kbdjocu'));

echo "=====EXAMPLE LOGIN TO INSTANCES=====\n";
// $session = login('5e8618d8d1f82','123', true);
// print_r($session->data); // this give me instance session -> jcbuvpi73ff0h9776vfo4umepv

echo "=====EXAMPLE SEND MESSAGE=====\n";
// You need to login via dashboard to get QR CODES and scan it first before using this feature. Just make sure this current instance has session logged in.
//print_r(send('6285927475702','ok ok','5e8618d8d1f82', '3iooasclcmaubkaqnj0ucll4kd'));

echo "=====EXAMPLE CREATE INSTANCE=====\n";
// make sure you're using admin panel account
//print_r(createInstance('123','kc4uc9lg8o9ugv30er5kbdjocu'));
echo "=====EXAMPLE DESTROY INSTANCE=====\n";
//$list = listInstances('kc4uc9lg8o9ugv30er5kbdjocu');
//print_r(destroyInstance($list->data[0]->uid,'kc4uc9lg8o9ugv30er5kbdjocu'));