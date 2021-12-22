

<?php
/*
	This application lets the users send message on flowroute API from portal
	Written By
	Saurabh Singh <saurabh18n@gmail.com>
*/
include "root.php";
require_once "resources/require.php";
require_once "resources/check_auth.php";
if (permission_exists('sms_send')) {
	//access granted
} else {
	echo "access denied";
	exit;
}

//add multi-lingual support
$language = new text;
$text = $language->get();

//get the http values and set them as variables


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	ob_end_clean();  // cleaning Fusionpbx imports outputs.
	//Getting setting values
	$username = $_SESSION["sms_portal"]["api"]["api_user_name"];
	$password = $_SESSION["sms_portal"]["api"]["api_user_password"];
	$fromnumber =  $_SESSION["sms_portal"]["api"]["api_from_number"];
	$url = $_SESSION["sms_portal"]["api"]["api_url"];
	// $username = "sjsjdfsdfsdfa";
	// $password = "kkhdsfhkdshkdsgdfg";
	// $fromnumber =  "+17975943534";
	// $url = "https://api.flowroute.com/v2.2/messages";
	//Checking send 
	if($_REQUEST["action"] === "send"){
		// Setting up Options
		$number = $_POST["number"];
		$message = $_POST["message"];
		$user_uuid = $_SESSION['user_uuid'];
		$now = date('Y-m-d H:i:s');
		$MessageDataRow['message_uuid'] = uuid();
		$MessageDataRow['message_domain'] = $domain_uuid;
		$MessageDataRow['message_start_stamp'] = $now;
		$MessageDataRow['message_from_number'] = $fromnumber;
		$MessageDataRow['message_to_number'] = $number;
		$MessageDataRow['message_text'] = $message;
		$MessageDataRow['message_direction'] = "OUT";
		$MessageDataRow['message_response'] = "";
		$MessageDataRow['message_carrier'] = "Default";
		$MessageDataRow['message_user'] = $user_uuid; 
		if($username == null || $password == null || $fromnumber == null){
			$data = [
				success => false,
				message => "API Credendials not configured. Kindly check options.",
				data => [messid => $MessageDataRow['message_uuid']]
			];
			header('Content-type: application/json');
			echo json_encode( $data );
			$MessageDataRow['message_response'] = "API Credendials not configured. Kindly check options.";
		}else{
			// $_SERVER['HTTP_HOST'] host
			$dlr_callback = 'https://'.$_SERVER['HTTP_HOST'].'/app/smsportal/hook/notify.php?messid='.$MessageDataRow['message_uuid'];
			// Sending Message
			$data = array(
				"data" => array (
					"type" => "message",
					"attributes" => array(
						"to" => $number, // To Number
						"from" => $fromnumber, // From number
						"body" => $message, // Message Text
						"dlr_callback" => $dlr_callback
					)
				)
			);

			$dataJson = json_encode($data);
			unset($data);
			$ch = curl_init();
			curl_setopt($ch,CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_USERPWD, $username . ":" . $password);
			curl_setopt( $ch, CURLOPT_POSTFIELDS, $dataJson );
			curl_setopt( $ch, CURLOPT_HTTPHEADER, array('Content-Type:application/vnd.api+json')); // Content Type
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			$result = curl_exec($ch);
			$Responce = json_decode($result);
			$returnData = [];
			$now = date('Y-m-d H:i:s');
			if(isset($Responce->errors)){
				// Set db perameters.
				$MessageDataRow['message_response'] = $now.": Message Sent Error ".$Responce->errors[0]->detail;;
				// Set responce perameters.
				$returnData["success"] = false;
				$returnData["message"] = "Message Sent Error. ".$Responce->errors[0]->detail;
				$returnData["data"] = [messid=>$MessageDataRow['message_uuid']];
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
			$database = new database;
			$database->app_name = "portal_sms_messages";
			$database->table ="v_sms_messages";
			$database->fields =$MessageDataRow;
			$database->add();
		}
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
} else {
require_once "resources/header.php";
$document['title'] = $text['title-sms-send'];

require_once "resources/paging.php";
//Contemt

echo '<h4 width="100%" border="0" cellpadding="0" cellspacing="0">' . $text['title-sms-send'] . '</h4>';
//echo "		<form method='post' action='send.php'>";
echo '			<div id="sms_pre" style="display:block">';
echo '			<table width="100%" border="0" cellpadding="0" cellspacing="0">';
echo '				<tbody>';
echo '					<tr>';
echo '						<td width="30%" class="vncellreq" valign="middle" align="left" nowrap="nowrap">';
echo '							To Number';
echo '						</td>';
echo '						<td class="vtable" valign="top" align="left" nowrap="nowrap">';
echo '							<input class="formfld w-100" type="text" id="tonumber" name="tonumber" required >';
echo '						</td>';
echo '					</tr>';
echo '					<tr>';
echo '						<td width="30%" class="vncellreq" valign="middle" align="left" nowrap="nowrap">';
echo '							Message';
echo '						</td>';
echo '						<td class="vtable" valign="top" align="left" nowrap="nowrap">';
echo '							<textarea class="formfld w-100" id="messagetext" name="messagetext" style="height:200px;" rows="30" value="" required></textarea>';
echo '						</td>';
echo '					</tr>';
echo '					<tr>';
echo '						<td width="30%" class="" valign="middle" align="left" nowrap="nowrap">';
echo '					</td>';
echo '					<td class="">';
echo '						<input class="btn my-1" style="width:100px" id="back_btn" type="button" value="' . $text['button-back'] .'" onclick="location.replace('."'sms.php')".'">';
echo '						<input class="btn my-1 ml-4" style="width:100px" id="preview_btn" type="button" value="' . $text['button-preview'] . '">';
echo '					</td>';
echo '					</tr></tbody></table></div>';

// Preview
echo '<div id="sms_post" style="display:none" class="col-md-6 mx-auto text-right">';
echo '			<table id="sms_post_priview" class="table" border="0" cellpadding="0" cellspacing="0">';
echo '				<thead>';
echo '					<tr>';
echo '						<th class="vtable" valign="top" align="left" nowrap="nowrap" style="width:30%">';
echo '							Number';
echo '						</th>';
echo '						<th class="vtable text-center" valign="top" align="middle" nowrap="nowrap" style="width:auto">' ;
echo '							Status';
echo '						</th>';
echo '						<th class="vtable text-center" valign="top" align="left" nowrap="nowrap" style="width:10%">';
echo '							Action';
echo '						</th>';
echo '					</tr>';
echo '				</thead>';
echo '				<tbody>';
echo '				</tbody>';
echo '				</table>';
echo '							<textarea class="formfld w-100" id="sms_post_priview_messagetext" name="messagetext" style="height:50px;max-height:auto;width:100%;max-width:100%" value="" required></textarea>';
echo '							<input class="btn my-1" style="width:100px" id="back_btn" type="button" value="' . $text['button-back'] .'" onclick="location.replace('."'sms.php')".'">';
echo '							<input class="btn my-1" style="width:100px" id="send_btn" type="button" value="' . $text['button-send'] . '">';
echo '</div>';
//echo "</form>";
}
//show the footer
echo '<script type="text/javascript" src="resources/js/sms.js"></script>';
require_once "resources/footer.php";