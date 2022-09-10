<?php
include "../../root.php";
require_once "resources/require.php";

date_default_timezone_set('Asia/Kolkata');

ob_end_clean();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $postData = json_decode(file_get_contents('php://input'), true);
    writeError("New message-> from " . get_client_ip() . " " . json_encode($postData));
    //print_r( $postData);
    //if($postData['data']['type']=='')
    header('Content-type: application/json');

    //Putting message in DB
    $now = date('Y-m-d H:i:s');
    $from = $postData["data"]["payload"]["from"]["phone_number"];
    $to = $postData["data"]["payload"]["to"][0]["phone_number"];
    $message = $postData["data"]["payload"]["text"];

    if (isset($from, $to, $message)) {
        http_response_code(200);
        echo "OK";
        //Message data row
        $MessageDataRow['message_uuid'] = uuid();
        $MessageDataRow['message_domain'] = $domain_uuid;
        $MessageDataRow['message_start_stamp'] = $now;
        $MessageDataRow['message_from_number'] =  $from;
        $MessageDataRow['message_to_number'] = $to;
        $MessageDataRow['message_text'] = $message;
        $MessageDataRow['message_direction'] = "IN";
        $MessageDataRow['message_response'] = "Incomming Message";
        $MessageDataRow['message_carrier'] = "Default";
        $database = new database;
        $database->app_name = "portal_sms_messages";
        $database->table = "v_sms_messages";
        $database->fields = $MessageDataRow;
        $database->add();
    } else {
        echo 'Error';
        writeError("Receive-> Invalid Request from " . get_client_ip() . " " . json_encode($postData));
    }
} else {
    http_response_code(400);
    echo "bad request";
    writeError("Receive-> GET Request from " . get_client_ip());
}
exit;


function writeError($text)
{
    $ErrorRow['error_uuid'] = uuid();
    $ErrorRow['error_time'] = date('Y-m-d H:i:s');
    $ErrorRow['error_text'] = $text;
    $database = new database;
    $database->app_name = "portal_sms_messages";
    $database->table = "v_sms_errors";
    $database->fields = $ErrorRow;
    $database->add();
}

function get_client_ip()
{
    $ipaddress = '';
    if (isset($_SERVER['HTTP_CLIENT_IP']))
        $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
    else if (isset($_SERVER['HTTP_X_FORWARDED_FOR']))
        $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
    else if (isset($_SERVER['HTTP_X_FORWARDED']))
        $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
    else if (isset($_SERVER['HTTP_FORWARDED_FOR']))
        $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
    else if (isset($_SERVER['HTTP_FORWARDED']))
        $ipaddress = $_SERVER['HTTP_FORWARDED'];
    else if (isset($_SERVER['REMOTE_ADDR']))
        $ipaddress = $_SERVER['REMOTE_ADDR'];
    else
        $ipaddress = 'UNKNOWN';
    return $ipaddress;
}
