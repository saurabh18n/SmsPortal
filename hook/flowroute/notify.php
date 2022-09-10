<?php
include "../../root.php";
require_once "resources/require.php";
require_once "resources/check_auth.php";
require_once "resources/paging.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $messid =  $_REQUEST["messid"];
    $postData = json_decode(file_get_contents('php://input'), true);
    writeError("Notify-> Request from ".get_client_ip()." ".$messid ." ".json_encode( $postData));
    if($messid == "" ){
        http_response_code(400);
        echo "bad request";
        writeError("Notify-> Request without messid ".get_client_ip()." ".json_encode( $postData));
    }else{
        http_response_code(200);
        header('Content-type: application/json');
        echo "OK";
        $now = date('Y-m-d H:i:s');
        if($postData["type"]=="delivery_receipt"){
            $statusCode = $postData["attributes"]["status_code"];
            $statusDescription = $postData["attributes"]["status"];
            $timestmp = isset($postData["attributes"]["timestamp"])? isset($postData["attributes"]["timestamp"]) : $now;
            $sql = "UPDATE v_sms_messages SET message_response = message_response || chr(10) || '";
            if($statusDescription == 'delivered'){
                $sql = $sql.$now.": Message Delivered',message_delivered='". $timestmp."' WHERE message_uuid = '".$messid."'";
            }else{
                $sql = $sql.$now.": * ".$statusDescription."' WHERE message_uuid = '".$messid."'";
            }
            $database = new database;
			$database->execute($sql, $parameters, 'all');
        }else{
             writeError("Notify-> Invalid Request from ".get_client_ip()." ".json_encode( $postData));
        }
        unset($postData);
    }
}else{
    http_response_code(400);
    echo "bad request ";
    writeError("Notify-> Invalid Request from ".get_client_ip());
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
