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

$user_uuid = $_SESSION['user_uuid'];
//Post Action Handlers

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	switch ($_REQUEST["action"]) {
		case "getmessage":{
			$number = $_POST["number"];
			$offset = $_POST["offset"];
			$till = $_POST["till"];
			$sql = "SELECT message_uuid, message_start_stamp,
					 message_from_number,message_to_number, message_text, message_direction, message_delivered,message_sent 
					FROM v_sms_messages
					WHERE (message_from_number = :number OR message_to_number = :number) AND message_start_stamp < :till
					ORDER BY message_start_stamp DESC LIMIT 20 OFFSET :offset";
			$parameters['number'] = $number;
			$parameters['offset'] = $offset;
			$parameters['till'] = $till;
			$database = new database;
			$mess_list = $database->select($sql, $parameters, 'all');
			$data = [success=>true, data=> $mess_list];
			echo json_encode($data);
			unset($database,$sql,$mess_list,$data);
			exit;
		}
		case "getallmessage":{
			$fetched = $_POST["fetched"];
			$sql = "SELECT message_uuid, message_start_stamp, message_from_number,message_to_number, message_text, message_direction, message_delivered, message_sent
			FROM v_sms_messages
			WHERE message_start_stamp > :fetched
			AND (message_from_number IN (SELECT number_number FROM v_sms_numbers WHERE number_user = :user_uuid) 
			OR message_to_number IN (SELECT number_number FROM v_sms_numbers WHERE number_user = :user_uuid))
			ORDER BY message_start_stamp DESC
			LIMIT 200";
			$parameters['user_uuid'] = $user_uuid;
			$parameters['fetched'] = $fetched;
			$database = new database;
			$mess_list = $database->select($sql, $parameters, 'all');
			$data = [success=>true,data=> $mess_list];
			echo json_encode($data);
			unset($database,$sql,$mess_list,$data);
			exit;
		}
		case "markread":{
			$now = date('Y-m-d H:i:s');
			$number = $_POST["number"];
			$sql = "UPDATE v_sms_messages set message_delivered = :readtime ,message_user = :user
					WHERE message_direction = 'IN' AND
					message_from_number = :num AND
					message_delivered IS NULL";
			$parameters['user'] = $user_uuid;
			$parameters['num'] = $number;
			$parameters['readtime'] = $now;
			$database = new database;
			$database->execute($sql, $parameters, 'all');
			echo 'OK';
			unset($database,$sql);
			exit;
		}

		default:
	}
}
//add multi-lingual support
$language = new text;
$text = $language->get();

require_once "resources/header.php";
$document['title'] = $text['title-sms'];

//Navigaion
echo '<link rel="stylesheet" href="static/css/navigation.css">';

echo '<div class="wrapper">
        <!-- Sidebar  -->
        <nav id="sidebar" class="d-flex flex-column">
            <div class="sidebar-header">
                <h3>SMS</h3>
            </div>
			<div>
				<ul class="list-unstyled components">
					<li class="active">
						<a class="sidebar-a" href="inbox.php">
							<i class="fas fa-inbox"></i>
							Inbox
						</a>
					</li> 
					<li class="">
						<a class="sidebar-a" href="settings.php">
							<i class="fas fa-cog"></i>
							Settings
						</a>
					</li>            
				</ul>
			</div>
			<div class="px-2 d-flex flex-grow-1 justify-content-end	flex-column">
				<label class="m-0">Refresh</label>
				<select id="refreshtime" class="form-control my-2" >
					<option value="1000">1 Sec</option>
					<option value="2000">2 Sec</option>
					<option value="3000" selected>3 Sec</option>
					<option value="4000">4 Sec</option>
					<option value="5000">5 Sec</option>
					<option value="OFF">OFF</option>
				</select>
			</div>
        </nav>
		<div id="content">';
//Navigation Ends
// Get List Of Numbers

$sql = "
SELECT number, message_start_stamp mtime,message_text message FROM (SELECT DISTINCT ON(number) number, message_start_stamp, message_text FROM (SELECT DISTINCT message_to_number number,message_start_stamp,message_text FROM v_sms_messages WHERE 
message_from_number IN (SELECT number_number FROM v_sms_numbers WHERE number_user = :userid)
UNION ALL
SELECT DISTINCT message_from_number number,message_start_stamp,message_text FROM v_sms_messages WHERE 
message_to_number IN (SELECT number_number FROM v_sms_numbers WHERE number_user = :userid)
) k ORDER BY number, message_start_stamp DESC  LIMIT 100) g  ORDER BY message_start_stamp DESC";

$parameters['userid'] = $user_uuid;
$database = new database;
$number_list = $database->select($sql, $parameters, 'all');

$sql = 'SELECT number_name
FROM "v_sms_numbers"
WHERE number_user = :userid AND number_active = true
ORDER BY number_default DESC';
$parameters['userid'] = $user_uuid;

$numbers = $database->select($sql, $parameters, 'all');

unset($sql,$parameters);

// The New Implementation
echo '<link rel="stylesheet" href="static/css/inbox.css?<?php echo rand(10,1000)?>">
<div class="row m-0 p-0">
		<div class="card w-100">
			<div class="row g-0">
				<div class="col-12 col-lg-6 col-xl-4 border-right">
					<div class="px-4 d-md-block">
						<div class="d-flex align-items-center">
							<div class="flex-grow-1">
								<input id="searchip" type="text" class="form-control my-3" placeholder="Search or enter number to send message">
							</div>
							<div>
								<button id="newnumberbtn" class="btn btn-block btn-primary p-1">
									<svg aria-hidden="true" height="30px" width="30px" focusable="false" data-prefix="far" data-icon="square-plus" class="svg-inline--fa fa-square-plus" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path fill="currentColor" d="M319.1 232h-72V160c0-13.25-10.74-24-23.1-24S199.1 146.7 199.1 160v72H127.1C114.7 232 103.1 242.7 103.1 256S114.7 280 127.1 280h71.1V352c0 13.25 10.75 24 24 24s23.1-10.75 23.1-24V280h72c13.25 0 23.1-10.75 23.1-24S333.3 232 319.1 232zM384 32H64C28.65 32 0 60.65 0 96v320c0 35.35 28.65 64 64 64h320c35.35 0 64-28.65 64-64V96C448 60.65 419.3 32 384 32zM400 416c0 8.822-7.178 16-16 16H64c-8.822 0-16-7.178-16-16V96c0-8.822 7.178-16 16-16h320c8.822 0 16 7.178 16 16V416z"></path>
									</svg>
								</button>
							</div>
						</div>
					</div>
					<div id="contact-list-container">
						<div id="contact-list">';
							foreach ($number_list as  $index=>$number) {
								if($index== 0){
									$firstNumber = $number['number'];
								}
								echo '<div class="number-list-item list-group-item border-0 my-1 pl-2 '.($index==0?'active':'').'" data-number="'.$number['number'].'">
										<div class="d-flex flex-column align-items-start" style="pointer-events: none;">
											<div class="d-flex flex-row w-100 justify-content-between" style="pointer-events: none;">
												<span class="list-number">'.$number['number'].'</span>
												<span class="badge badge-pill badge-danger pill-text"></span>											
											</div>
											<div class="d-flex flex-row w-100 justify-content-between" style="pointer-events: none;">							
												<span class="list-text">'.substr($number['message'], 0, 50).'</span>
												<span class="list-time my-auto">'.$number['mtime'].'</span>
											</div>															
										</div></div>';
							}
	echo '				</div>
					</div>
					<!-- <hr class="d-block d-lg-none mt-1 mb-0"> -->
				</div>
				<div class="col-12 col-lg-6 col-xl-8">
					<div class="py-2 px-4 border-bottom d-none d-lg-block">
						<div class="d-flex align-items-center py-1">
							<div class="flex-grow-1 pl-3">
								<span id="header-number" class="list-number">'.$firstNumber.'</span>
							</div>
						</div>
					</div>

					<div class="position-relative">
						<div id="messages" class="chat-messages p-4">
							<div id="start-of-chat" class="w-100 text-center mb-2"><span id="load-more" class="badge badge-pill badge-primary">Load More</span></div>
						</div>
					</div>

					<div class="flex-grow-0 py-3 px-4 border-top row">
					<div class="input-group col-2">';					
							if(count($numbers) == 1){
echo '							<select id="from-number" class="form-control" disabled>
									<option value='.$numbers[0]['number_name'].' selected >'.$numbers[0]['number_name'].'</option>';
							}else{
echo '							<select id="from-number" class="form-control">';
								foreach ($numbers as  $index=>$number) {
									if($index == 0){
echo '									<option value="'.$numbers[0]['number_name'].'" selected >'.$numbers[0]['number_name'].'</option>';
									}else{
echo '									<option value="'.$numbers[$index]['number_name'].'" >'.$numbers[$index]['number_name'].'</option>';
									}
								}
							}
echo '						</select>							
						</div>
						<div class="input-group col-10">
							<textarea id="mess-text" class="form-control" rows="2" cols="50" ></textarea>
							<button id="sendbutton" class="btn btn-primary">Send</button>
						</div>
					</div>

				</div>
			</div>
		</div>
</div>
</div>
</div>';
require_once "resources/footer.php";
echo '<script type="text/javascript" src="static/js/inbox.js?v='.rand(111,999).'"></script>';

function formatStatus($str){
	$texts = (explode("\n",$str));
	end($texts);
	$lastString = "<p class='m-0 p-0'>". $texts[key($texts)].'</p>';
	return $lastString;
}
