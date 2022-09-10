<?php
/*
	This application lets the users send message on flowroute API from portal
	Written By
	Saurabh Singh <saurabh18n@gmail.com>
*/
	require_once "root.php";
	require_once "resources/require.php";
	require_once "resources/check_auth.php";
	require_once "resources/paging.php";

if (permission_exists('sms_view')) {
	//access granted
} else {
	echo "access denied";
	exit;
}

//add multi-lingual support
$language = new text;
$text = $language->get();
require_once "resources/header.php";

$document['title'] = $text['title-sms'];
$user_uuid = $_SESSION['user_uuid'];
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	switch ($_REQUEST["action"]) {
		case "add":{
			$number_name = $_POST["number_name"];
			$api_provider = $_POST["api_provider"];
			$api_user = $_POST["api_user_name"];
			$api_pass = $_POST["api_user_pass"];
			$api_number = $_POST["api_user_number"];

			if(isset($api_user,$api_pass,$api_number,$number_name) && $api_user != "" && $api_pass != "" && $api_number != "" && $number_name != ""){
				$number['number_uuid'] = uuid();
				$number['number_domain'] = $domain_uuid;
				$number['number_user'] = $user_uuid;
				$number['number_name'] = $number_name;
				$number['number_provider'] = $api_provider;
				$number['number_username'] = $api_user;
				$number['number_password'] = $api_pass;
				$number['number_number'] = $api_number;
				$number['number_active'] = true;
				$database = new database;
				$database->app_name = "portal_sms_messages";
				$database->table ="v_sms_numbers";
				$database->fields =$number;
				$database->add();
			}else{
				echo "invalid data";
				exit;
			}
			break;
		}
		case "update":{
			unset($parameters,$sql,$database);
			$uuid = $_POST["hid-uuid"];
			$number_name = $_POST["number_name"];
			$api_user = $_POST["api_user_name"];
			$api_pass = $_POST["api_user_pass"];
			$api_number = $_POST["api_user_number"];			
			if(isset($api_user,$api_pass,$api_number,$number_name,$uuid) && $api_user != "" && $api_pass != "" && $api_number != "" && $number_name != "" && $uuid != ""){
				$sql = "UPDATE v_sms_numbers SET number_name = :nname, number_username = :apiuname, number_password = :apipass, number_number = :apinum WHERE number_uuid = :numuuid";				
				$parameters['numuuid'] = $uuid;
				$parameters['nname'] = $number_name;
				$parameters['apiuname'] =$api_user;
				$parameters['apipass'] =$api_pass;
				$parameters['apinum'] =$api_number;
				$database = new database;
			 	$database->execute($sql, $parameters, 'all');
			}else{
				echo "invalid data";
				exit;
			}			
			break;}
		case "enable":{
			$uuid = $_POST["hid-uuid"];			
			if(isset($uuid) && $uuid != ""){
				$sql = "UPDATE v_sms_numbers SET number_active = true  WHERE number_uuid = :numuuid";
				$parameters['numuuid'] = $uuid;
				$database = new database;
			 	$database->execute($sql, $parameters, 'all');
			}else{
				echo "invalid data";
				exit;
			}	
			break;
		}
			
		case "disable":{
			$uuid = $_POST["hid-uuid"];			
			if(isset($uuid) && $uuid != ""){
				$sql = "UPDATE v_sms_numbers SET number_active = false  WHERE number_uuid = :numuuid";
				$parameters['numuuid'] = $uuid;
				$database = new database;
			 	$database->execute($sql, $parameters, 'all');
			}else{
				echo "invalid data";
				exit;
			}
			break;
		}

			

		case "default":{
			$numuuid = $_POST["hid-uuid"];
			if(isset($numuuid) && $numuuid != ""){
				$sql = "UPDATE v_sms_numbers SET number_default = true WHERE number_uuid = :numuuid";
				$parameters['numuuid'] = $numuuid;
				$database = new database;
			 	$database->execute($sql, $parameters, 'all');
			}else{
				echo "invalid data ".$numuuid;
				exit;
			}
			break;
		}
		case "undefault":{
			$numuuid = $_POST["hid-uuid"];
			if(isset($numuuid) && $numuuid != ""){
				$sql = "UPDATE v_sms_numbers SET number_default = false WHERE number_uuid = :numuuid";
				$parameters['numuuid'] = $numuuid;
				$database = new database;
			 	$out = $database->execute($sql, $parameters, 'all');
			}else{
				echo "invalid data ".$numuuid;
				exit;
			}
			break;
		}




		default:
	}

}

//Show Get Content
echo '<link rel="stylesheet" href="static/css/navigation.css">';
echo '<div class="wrapper">
        <!-- Sidebar  -->
        <nav id="sidebar">
            <div class="sidebar-header">
                <h3>SMS</h3>
            </div>

            <ul class="list-unstyled components">
                <li class="">
                    <a class="sidebar-a" href="inbox.php">
                        <i class="fas fa-inbox"></i>
                        Inbox
                    </a>
                </li> 
                <li class="active">
                    <a class="sidebar-a" href="settings.php">
                        <i class="fas fa-cog"></i>
                        Settings
                    </a>
                </li>            
            </ul>
        </nav>
		<div id="content">
			<div id="numlist" class="">
				<div class="card col-sm-12 col-md-12 col-xl-12 mx-auto">
					<table class="table table-sm" style="font-size:1rem">
						<thead>
							<tr>
								<th class="d-none">numberid</th>
								<th class="">Name</th>
								<th class="">Provider</th>
								<th class="">Number</th>
								<th class="text-center">Edit</th>
								<th class="text-center">Active</th>
								<th class="text-center">Default</th>
							</tr>
						</thead>
						<tbody>';
	unset($sql,$parameters,$database);
	$sql = "SELECT * FROM v_sms_numbers WHERE number_user = :user ORDER BY number_default DESC";
	$parameters['user'] = $user_uuid;
	$database = new database;
	$number_list = $database->select($sql, $parameters, 'all');
	if($number_list){
		foreach ($number_list as $number) {
			echo '<tr><td class="d-none">'.$number['number_uuid'].'</td>';
			echo '<td>'.$number['number_name'].'</td>';
			echo '<td>'.$number['number_provider'].'</td>';
			echo '<td>'.$number['number_number'].'</td>';
			echo '<td class="text-center"><button data-uuid="'.$number['number_uuid'].'" data-name="'.$number['number_name'].'" data-number="'.$number['number_number'].'" class="btn btn-sm btn-primary number-edit-btn" type="button">Edit</button></td>';

			//Active
			echo '<td class="text-center"><div class="form-check form-check-inline">';
			if($number['number_active'] == true){
				echo '<input data-uuid="'.$number['number_uuid'].'" class="form-check-input number-enable-check" type="checkbox" checked="checked" ></div></td>';
			}else{
				echo '<input data-uuid="'.$number['number_uuid'].'" class="form-check-input number-enable-check" type="checkbox" ></div></td>';
			}
			//Default
			echo '<td class="text-center"><div class="form-check form-check-inline">';
			if($number['number_default'] == true){
				echo '<input data-uuid="'.$number['number_uuid'].'" class="form-check-input number-default-check" type="checkbox" checked="checked" ></div></td></tr>';
			}else{
				echo '<input data-uuid="'.$number['number_uuid'].'" class="form-check-input number-default-check" type="checkbox" ></div></td></tr>';
			}
		}
	}else{
		echo '<tr><td colspan="5">No Number Registered</td></tr>';
	}
echo 					'</tbody>
					</table>
					<div class="form-group">
						<button type="button" id="btn_add_new" class="btn btn-primary float-right">Add New</button>
					</div>
				</div>	
			</div>

			<div id="addnum" class="mx-auto col-xxl-3 col-xl-4 col-md-6 d-none">
				<form id="add-form" method="post" action="settings.php?action=add">
					<input type="hidden" value="" name="hid-uuid" id="hid-uuid"/>
					<div class="form-group">
						<label for="api_provider">Provider</label>
						<select class="form-control form-select" id="api_provider" name="api_provider" >
							<option selected value="FLOWROUTE">Flowroute</option>
							<option value="TELNYX">Telnyx</option>
						</select>					
					</div>
					<div class="form-group">
						<label for="api_user_name">Number Name</label>
						<input type="text" class="form-control" id="number_name" name="number_name" autocomplete="off">						
					</div>
					<div class="form-group">
						<label for="api_user_name">API Username Or API Key</label>
						<input type="text" class="form-control" id="api_user_name" name="api_user_name" autocomplete="off">						
					</div>
					<div class="form-group">
						<label for="api_user_pass">API Password OR API Key</label>
						<input type="text" class="form-control" id="api_user_pass" name="api_user_pass" autocomplete="off">						
					</div>

					<div class="form-group">
						<label for="api_user_number">API Number</label>
						<input type="text" class="form-control" id="api_user_number" name="api_user_number" autocomplete="off">						
					</div>
					<button type="button" id="btn_add_new_back" class="btn btn-secondary">Back</button>
					<button id="add-new-submit" type="submit" class="btn btn-primary float-right">Submit</button>
				</form>
			</div>
	</div>
</div></div>';

require_once "resources/footer.php";
echo '<script type="text/javascript" src="static/js/settings.js"></script>';