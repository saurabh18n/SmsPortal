

<?php
/*
	This application lets the users send message on flowroute API from portal
	Written By
	Saurabh Singh <saurabh18n@gmail.com>
*/
include "root.php";
require_once "resources/require.php";
require_once "resources/check_auth.php";
if (permission_exists('sms_view')) { // Shooud be changed to sms send 
	//access granted
} else {
	echo "access denied";
	exit;
}

//add multi-lingual support
$language = new text;
$text = $language->get();

//get the http values and set them as variables
$user_uuid = $_SESSION['user_uuid'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	ob_end_clean();  // cleaning Fusionpbx import outputs.
	$url = "https://api.flowroute.com/v2.2/messages";
	//Checking send 
	if($_REQUEST["action"] === "send"){
		// Setting up Options
		$fromnumbername = $_POST["fromnumber"];
		$tonumber = $_POST["tonumber"];
		$message = $_POST["message"];		
		//Getting the API Details from DB
		$sql = "SELECT number_username,number_password,number_number FROM v_sms_numbers WHERE number_name = :numbername AND number_user = :user LIMIT 1";
		$parameters['numbername'] = $fromnumbername;
		$parameters['user'] = $user_uuid;
		$database = new database;
		$row = $database->select($sql, $parameters, 'all');
		if(!$row){
			echo json_encode([success=>false,message=>'Invalid From Number']);
			exit;
		}
		$apiusername =trim($row[0]['number_username']);
		$apipassword =trim($row[0]['number_password']);
		$apinumber = trim($row[0]['number_number']);

		
		$now = date('Y-m-d H:i:s');
		$MessageDataRow['message_uuid'] = uuid();
		$MessageDataRow['message_domain'] = $domain_uuid;
		$MessageDataRow['message_start_stamp'] = $now;
		$MessageDataRow['message_from_number'] = $apinumber;
		$MessageDataRow['message_to_number'] = $tonumber;
		$MessageDataRow['message_text'] = $message;
		$MessageDataRow['message_direction'] = "OUT";
		$MessageDataRow['message_response'] = "";
		$MessageDataRow['message_carrier'] = "Default";
		$MessageDataRow['message_user'] = $user_uuid; 
		
			// $_SERVER['HTTP_HOST'] host
		$dlr_callback = 'https://'.$_SERVER['HTTP_HOST'].'/app/smsportal/hook/notify.php?messid='.$MessageDataRow['message_uuid'];
		// Sending Message
		$data = array(
			"data" => array (
				"type" => "message",
				"attributes" => array(
					"to" => $tonumber, // To Number
					"from" => $apinumber, // From number
					"body" => $message, // Message Text
					"dlr_callback" => $dlr_callback
				)
			)
		);
		$dataJson = json_encode($data);
		//unset($data);
		$ch = curl_init();
		curl_setopt($ch,CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_USERPWD, $apiusername . ":" .$apipassword);
		curl_setopt( $ch, CURLOPT_POSTFIELDS, $dataJson );
		curl_setopt( $ch, CURLOPT_HTTPHEADER, array('Content-Type:application/vnd.api+json')); // Content Type
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$result = curl_exec($ch);
		$Responce = json_decode($result);
		$returnData = [];
		$now = date('Y-m-d H:i:s');
		if(isset($Responce->errors)){
			// Set db perameters.
			//$MessageDataRow['message_response'] = $now.": Message Sent Error ".$Responce->errors[0]->detail;;
			// Set responce perameters.
			$returnData["success"] = false;
			$returnData["message"] = "Message Sent Error. ".$Responce->errors[0]->detail;
			$returnData["data"] = [messid=>$MessageDataRow['message_uuid'],d=>$data];
			$returnData["error"] = [$Responce->errors[0]];
			$returnData["success"] = false;				
		}else if(isset($Responce->data)){
			// Set db perameters.
			$MessageDataRow['message_response'] = $now.": Message Sent Successfully id ".$Responce->data->id;
			$MessageDataRow['message_sent'] = date('Y-m-d H:i:s');
			// Set responce perameters.
			$returnData["success"] = true;
			$returnData["message"] = "Sent Successfully";
			$returnData["data"] = [id=>$Responce->data->id,messid=>$MessageDataRow['message_uuid']];
			$database = new database;
			$database->app_name = "portal_sms_messages";
			$database->table ="v_sms_messages";
			$database->fields =$MessageDataRow;
			$database->add();					
		}else{
		 	$returnData["success"] = false;
		 	$returnData["message"] = "Some thing went wrong Please try again";
		 	$returnData["error"] = $Responce;
		 	$returnData["data"] = [messid=>$MessageDataRow['message_uuid']];
		 }
		header('Content-type: application/json');
		echo json_encode( $returnData );
		//Updating fields after API Call
		// Saving details to db
		
		
	}else if($_REQUEST["action"] === "resend"){ 
		$messid = $_POST["messid"];
		//get the message
		$sql = "select	message_from_number,
						message_to_number,
						message_text,
						message_direction,
						message_sent
				from v_sms_messages where message_uuid = '".$messid."'";
		$database = new database;
		$row = $database->select($sql, $parameters, 'all');
		if($row){			
			$returnData = [];
			//If Message was already sent.
			if(isset($row["message_sent"])){
				$returnData['success'] = true;
				$returnData["message"] = "Message Already Sent Message";
				$returnData["data"] = [messid=> $messid];
			}else if($row["message_direction"] === "IN"){
				$returnData['success'] = false;
				$returnData["message"] = "Can not resend Incoming message";
				$returnData["data"] = [messid=> $messid];
			}else{
			//try sending message
			$dlr_callback = 'https://'.$_SERVER['HTTP_HOST'].'/app/smsportal/hook/notify.php?messid='.$messid;
			$data = array(
				"data" => array (
					"type" => "message",
					"attributes" => array(
						"to" => $row["message_to_number"], // To Number
						"from" => $row["message_from_number"], // From number
						"body" => $row["message_text"], // Message Text
						"dlr_callback" => $dlr_callback
					)
				)
			);
			$dataJson = json_encode($data);
			unset($data);
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_USERPWD, $username . ":" . $password);
			curl_setopt( $ch, CURLOPT_POSTFIELDS, $dataJson );
			curl_setopt( $ch, CURLOPT_HTTPHEADER, array('Content-Type:application/vnd.api+json')); // Content Type
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			$result = curl_exec($ch);
			$Responce = json_decode($result);
			$now = date('Y-m-d H:i:s');
			$sql = "";
			if(isset($Responce->errors)){
				// Set db perameters.
				$sql = "UPDATE v_sms_messages SET message_response = message_response || chr(10) || '"
				.$now.": Message Sent Error ".$Responce->errors[0]->detail."' WHERE message_uuid = '".$messid."'";
				// Set responce perameters.
				$returnData["success"] = false;
				$returnData["message"] = "Message Sent Error. ".$Responce->errors[0]->detail;
				$returnData["error"] = $Responce->errors[0] ;
				$returnData["data"] = [messid => $messid];				
			}else if(isset($Responce->data)){
				// Set db perameters.
				$sql = "UPDATE v_sms_messages SET message_response = message_response || chr(10) || '"
				.$now.": Message Sent Successfully id ".$Responce->data->id."', message_sent = '".$now."' WHERE message_uuid = '".$messid."'";
				// Set responce perameters.
				$returnData["success"] = true;
				$returnData["message"] = "Sent Successfully";
				$returnData["data"] =[id=>$Responce->data->id,messid=>$messid];				
			}else{
			 	$returnData["success"] = false;
			 	$returnData["message"] = "Some thing went wrong Please try again";
			 	$returnData["error"] = $Responce;
			 	$returnData["data"] = [messid=>$messid];
			 }
			};
			$returnData["data"] = [messid=>$messid,sql=>$sql];
			$database = new database;
			$row = $database->execute($sql, $parameters, 'all');

			header('Content-type: application/json');
			echo json_encode( $returnData );
		}else{
			$returnData = [
				success => false,
				message => "Invalid Message",
				data => [messid=> $messid]
			];
			header('Content-type: application/json');
			echo json_encode( $returnData );
		}
	}else{
		$data = [
			success => false,
			message => "Invalid Action",
			data => null
		];
		header('Content-type: application/json');
		echo json_encode( $data );
	}
	exit;
}