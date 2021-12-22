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

//get the http values and set them as variables
$search = check_str($_GET["search"]);
$order_by = check_str($_GET["order_by"]);
$order = check_str($_GET["order"]);

require_once "resources/header.php";
$document['title'] = $text['title-sms'];
//settings
//print_r($_SESSION['sms_portal']['api']['api_url']);
//add the search string
	if (isset($_GET["search"])) {
		$search =  strtolower($_GET["search"]);
		$sql_search .= if_group("superadmin") ? "where" : " and ( ";
		$sql_search .= "	from_number like :search ";
		$sql_search .= "	or to_number like :search ";
		$sql_search .= "	or lower(message) like :search ";
		$sql_search .= "	or lower(response) like :search ";
		$sql_search .= ") ";
		$parameters['search'] = '%'.$search.'%';
	}

//get the count
	$sql = "select count(message_uuid) from v_sms_messages ";
	if(!if_group("superadmin")){
		$sql .= "where domain_uuid = :domain_uuid ";
		$parameters['domain_uuid'] = $_SESSION['domain_uuid'];
	}
	
	if (isset($sql_search)) {
		$sql .= $sql_search;
	}
	
	$database = new database;
	$num_rows = $database->select($sql, $parameters, 'column');
	//prepare to page the results
	$rows_per_page = ($_SESSION['domain']['paging']['numeric'] != '') ? $_SESSION['domain']['paging']['numeric'] : 50;
	$param = $search ? "&search=".$search : null;
	$param = ($_GET['show'] == 'all' && permission_exists('sms_view')) ? "&show=all" : null;
	$page = is_numeric($_GET['page']) ? $_GET['page'] : 0;
	list($paging_controls, $rows_per_page) = paging($num_rows, $param, $rows_per_page);
	list($paging_controls_mini, $rows_per_page) = paging($num_rows, $param, $rows_per_page, true);
	$offset = $rows_per_page * $page;

	unset($num_rows);

	if(!isset($_GET['order_by'])){
		$order_text = "message_start_stamp desc";
	}else{
		$order_text = $_GET['order_by'];
	}
//get the SMS List 

	$sql = 'SELECT 	t1.message_domain,
					t1.message_user,
					message_uuid,
					message_start_stamp,
					message_from_number,
					message_to_number,
					message_text,
					message_direction,
					message_response,
					message_sent,
					message_delivered,
					t2.username,
					t3.domain_name
			FROM v_sms_messages t1
			INNER JOIN v_domains t3 ON t1.message_domain = t3.domain_uuid 
			LEFT OUTER JOIN v_users t2 ON t1.message_user = t2.user_uuid ';
	if(!if_group('superadmin')){
		$sql .= "where domain_uuid = :domain_uuid ";
	}
	if (isset($sql_search)) {
		$sql .= $sql_search;
	}
	$sql .= "order by $order_text ";
	$sql .= "limit $rows_per_page offset $offset ";
	$database = new database;
	$sms_list = $database->select($sql, $parameters, 'all');

//  echo "<pre>";
//  echo $sql;
// // print_r($parameters);
//  print_r($sms_list);
//  echo "</pre>";
unset($parameters, $sql);
//show the content
echo "<table width=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\">\n";
echo "  <tr>\n";
echo "	<td align='left' width='100%'><b>" . $text['header-sms'] . "</b><br>\n";
echo "		" . $text['description-sms'] . "\n";
echo "	</td>\n";
echo "		<form method='get' action=''>\n";
echo "			<td style='vertical-align: top; text-align: right; white-space: nowrap;'>\n";
//Rander SMS Send Button
if (permission_exists('sms_send')) {
	echo "				<input type='button' class='btn' style='margin-right: 15px;' value='" . $text['button-mdr'] . "' onclick=\"window.location.href='send.php'\">\n";
}
echo "				<input type='text' class='txt' style='width: 150px' name='search' id='search' value='" . $search . "'>";
echo "				<input type='submit' class='btn' name='submit' value='" . $text['button-search'] . "'>";
if ($paging_controls_mini != '') {
	echo 			"<span style='margin-left: 15px;'>" . $paging_controls_mini . "</span>\n";
}
echo "			</td>\n";
echo "		</form>\n";
echo "  </tr>\n";
echo "</table>\n";
echo "<br />";

$c = 0;
$row_style["0"] = "row_style0";
$row_style["1"] = "row_style1";

echo "<form name='frm' method='post' action='sms_delete.php'>\n";
echo "<table class='tr_hover' width='100%' border='0' cellpadding='0' cellspacing='0'>\n";
echo "<tr>\n";
if(if_group('superadmin')){
	echo th_order_by("message_domain", $text['label-domain'], $order_by, $order);	
}
if(if_group('admin') || if_group('superadmin')){
	echo th_order_by("message_user", $text['label-user'], $order_by, $order);
}
echo th_order_by('message_from_number', $text['label-from'], $order_by, $order);
echo th_order_by('message_to_number', $text['label-to'], $order_by, $order);
echo th_order_by('message_start_stamp', $text['label-scheduled'], $order_by, $order);
echo th_order_by('message_sent', $text['label-sent'], $order_by, $order);
echo th_order_by('message_delivered', $text['label-delivered'], $order_by, $order);
echo th_order_by('message_text', $text['label-message'], $order_by, $order);
echo '<th style="width:20%;word-break: break-all">'.$text['label-status']."</th>";
echo "<td class='list_control_icon'>\n";
echo "</td>\n";
echo "</tr>\n";

if (is_array($sms_list)) {
	foreach ($sms_list as $row) {
		if(if_group('superadmin')){
			echo "	<td valign='top' class='" . $row_style[$c] . "'>" . $row['domain_name'] . "</td>\n";
		}
		if(if_group('admin') || if_group('superadmin')){
			echo "	<td valign='top' class='" . $row_style[$c] . "'>" . $row['username'] . "</td>\n";
		}
		echo "	<td valign='top' class='" . $row_style[$c] . "'>" . $row['message_from_number'] . "</td>\n";
		echo "	<td valign='top' class='" . $row_style[$c] . "'>" . $row['message_to_number'] . "</td>\n";
		echo "	<td valign='top' class='" . $row_style[$c] . "'>" . $row['message_start_stamp'] . "</td>\n";
		echo "	<td valign='top' class='" . $row_style[$c] . "'>" . ($row['message_sent']==null?'Not Sent':$row['message_sent']) . "</td>\n";
		echo "	<td valign='top' class='" . $row_style[$c] . "'>" .($row['message_delivered']==null?'Unknown':$row['message_delivered']) . "</td>\n";
		echo "	<td valign='top' class=' " . $row_style[$c] . "'>" .$row['message_text'] . "</td>\n";
		echo "	<td valign='top' style='line-break:auto' class='" . $row_style[$c] . "'>" .formatStatus($row['message_response']) . "</td>\n";	
		echo "</td>";
		echo "</tr>";
		$c = ($c) ? 0 : 1;
	}
	unset($sms_list,$row);
}
echo "</table>\n";
echo "<div align='center'>".$paging_controls."</div>\n";
echo "</form>";
//show the footer
require_once "resources/footer.php";

function formatStatus($str){
	$texts = (explode("\n",$str));
	end($texts);
	$lastString = "<p class='m-0 p-0'>". $texts[key($texts)].'</p>';
	return $lastString;
}
