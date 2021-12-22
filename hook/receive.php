<?php
include "../root.php";
require_once "resources/require.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $postData = json_decode(file_get_contents('php://input'), true);
    http_response_code(200);
    echo "OK";
    header('Content-type: application/json');
        $now = date('Y-m-d H:i:s');
        $from = $postData["from"];
        $to = $postData["to"];
        $message = $postData["body"];
        if(isset($from,$to,$message)){       
            //Message data row
            $MessageDataRow['message_uuid'] = uuid();
            $MessageDataRow['message_domain'] = $domain_uuid;
            $MessageDataRow['message_start_stamp'] = $now;
            $MessageDataRow['message_sent'] = $now;
            $MessageDataRow['message_delivered'] = $now;
            $MessageDataRow['message_from_number'] = "+".$from;
            $MessageDataRow['message_to_number'] = "+".$to;
            $MessageDataRow['message_text'] = $message;
            $MessageDataRow['message_direction'] = "IN";
            $MessageDataRow['message_response'] = "Incomming Message";
            $MessageDataRow['message_carrier'] = "Default";
            $database = new database;
            $database->app_name = "portal_sms_messages";
            $database->table ="v_sms_messages";
            $database->fields =$MessageDataRow;
            $database->add();
        }else{
            writeError("Receive-> Invalid Request from ".get_client_ip()." ".json_encode( $postData));
        }
}else{
    http_response_code(400);
    echo "bad request";
    writeError("Receive-> GET Request from ".get_client_ip());
}

function writeError($text){
    $ErrorRow['error_uuid'] = uuid();
	$ErrorRow['error_time'] = date('Y-m-d H:i:s');
	$ErrorRow['error_text'] = $text;
    $database = new database;
	$database->app_name = "portal_sms_messages";
	$database->table ="v_sms_errors";
	$database->fields =$ErrorRow;
	$database->add();
}

function get_client_ip() {
    $ipaddress = '';
    if (isset($_SERVER['HTTP_CLIENT_IP']))
        $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
    else if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
        $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
    else if(isset($_SERVER['HTTP_X_FORWARDED']))
        $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
    else if(isset($_SERVER['HTTP_FORWARDED_FOR']))
        $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
    else if(isset($_SERVER['HTTP_FORWARDED']))
        $ipaddress = $_SERVER['HTTP_FORWARDED'];
    else if(isset($_SERVER['REMOTE_ADDR']))
        $ipaddress = $_SERVER['REMOTE_ADDR'];
    else
        $ipaddress = 'UNKNOWN';
    return $ipaddress;
}


