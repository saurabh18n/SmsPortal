<?php

	//application details
		$apps[$x]['name'] = "SMS Portal";
		$apps[$x]['uuid'] = "98efcc5e-539c-11ec-bf63-0242ac130002";
		$apps[$x]['category'] = "Switch";
		$apps[$x]['subcategory'] = "";
		$apps[$x]['version'] = "";
		$apps[$x]['license'] = "Mozilla Public License 1.1";
		$apps[$x]['url'] = "http://www.fusionpbx.com";
		$apps[$x]['description']['en-us'] = "SMS";
		$apps[$x]['description']['es-cl'] = "";
		$apps[$x]['description']['es-mx'] = "";
		$apps[$x]['description']['de-de'] = "";
		$apps[$x]['description']['de-ch'] = "";
		$apps[$x]['description']['de-at'] = "";
		$apps[$x]['description']['fr-fr'] = "";
		$apps[$x]['description']['fr-ca'] = "";
		$apps[$x]['description']['fr-ch'] = "";
		$apps[$x]['description']['pt-pt'] = "";
		$apps[$x]['description']['pt-br'] = "";

	//permission details
		$y = 0;
		$apps[$x]['permissions'][$y]['name'] = "sms_view";
		$apps[$x]['permissions'][$y]['menu']['uuid'] = "b0ad60e0-539c-11ec-bf63-0242ac130002";
		$apps[$x]['permissions'][$y]['groups'][] = "superadmin";
		$apps[$x]['permissions'][$y]['groups'][] = "admin";
		$y++;
		$apps[$x]['permissions'][$y]['name'] = "sms_send";
		$apps[$x]['permissions'][$y]['groups'][] = "superadmin";
		$y++;
		$apps[$x]['permissions'][$y]['name'] = "sms_delete";
		$apps[$x]['permissions'][$y]['groups'][] = "superadmin";
		$y++;
	//default settings

		$y=0;
		$apps[$x]['default_settings'][$y]['default_setting_uuid'] = "931f9369-9aac-4620-8d4b-7d2bf642b1d2";
		$apps[$x]['default_settings'][$y]['default_setting_category'] = "sms_portal";
		$apps[$x]['default_settings'][$y]['default_setting_subcategory'] = "SMS Portal";
		$apps[$x]['default_settings'][$y]['default_setting_name'] = "text";
		$apps[$x]['default_settings'][$y]['default_setting_value'] = "true";
		$apps[$x]['default_settings'][$y]['default_setting_enabled'] = "false";
		$apps[$x]['default_settings'][$y]['default_setting_description'] = "";
		$y++;
		$apps[$x]['default_settings'][$y]['default_setting_uuid'] = 'b7ad2628-539c-11ec-bf63-0242ac130002';
		$apps[$x]['default_settings'][$y]['default_setting_category'] = 'sms_portal';
		$apps[$x]['default_settings'][$y]['default_setting_subcategory'] = "api";		
		$apps[$x]['default_settings'][$y]['default_setting_name'] = 'api_url';
		$apps[$x]['default_settings'][$y]['default_setting_value'] = 'https://api.flowroute.com/v2/messages';
		$apps[$x]['default_settings'][$y]['default_setting_enabled'] = 'true';
		$apps[$x]['default_settings'][$y]['default_setting_description'] = 'Url For Flowroute API';
		$y++;
		$apps[$x]['default_settings'][$y]['default_setting_uuid'] = 'be98467a-539c-11ec-bf63-0242ac130002';
		$apps[$x]['default_settings'][$y]['default_setting_category'] = 'sms_portal';
		$apps[$x]['default_settings'][$y]['default_setting_subcategory'] = "api";
		$apps[$x]['default_settings'][$y]['default_setting_name'] = 'api_user_name';
		$apps[$x]['default_settings'][$y]['default_setting_value'] = 'xxxx';
		$apps[$x]['default_settings'][$y]['default_setting_enabled'] = 'true';
		$apps[$x]['default_settings'][$y]['default_setting_description'] = 'API User Name';
		$y++;
		$apps[$x]['default_settings'][$y]['default_setting_uuid'] = 'c41c5f64-539c-11ec-bf63-0242ac130002';
		$apps[$x]['default_settings'][$y]['default_setting_category'] = 'sms_portal';
		$apps[$x]['default_settings'][$y]['default_setting_subcategory'] = "api";
		$apps[$x]['default_settings'][$y]['default_setting_name'] = 'api_user_password';
		$apps[$x]['default_settings'][$y]['default_setting_value'] = 'xxxx';
		$apps[$x]['default_settings'][$y]['default_setting_enabled'] = 'true';
		$apps[$x]['default_settings'][$y]['default_setting_description'] = 'API Password';
		$y++;

		$apps[$x]['default_settings'][$y]['default_setting_uuid'] = 'e300e1a8-6291-11ec-90d6-0242ac120003';
		$apps[$x]['default_settings'][$y]['default_setting_category'] = 'sms_portal';
		$apps[$x]['default_settings'][$y]['default_setting_subcategory'] = "api";
		$apps[$x]['default_settings'][$y]['default_setting_name'] = 'api_from_number';
		$apps[$x]['default_settings'][$y]['default_setting_value'] = '';
		$apps[$x]['default_settings'][$y]['default_setting_enabled'] = 'true';
		$apps[$x]['default_settings'][$y]['default_setting_description'] = 'From Number in +12345678901 format';
		$y++;
		
		$apps[$x]['default_settings'][$y]['default_setting_uuid'] = 'f2662644-6291-11ec-90d6-0242ac120003';
		$apps[$x]['default_settings'][$y]['default_setting_category'] = 'sms_portal';
		$apps[$x]['default_settings'][$y]['default_setting_subcategory'] = "api";
		$apps[$x]['default_settings'][$y]['default_setting_name'] = 'api_incomming_user';
		$apps[$x]['default_settings'][$y]['default_setting_value'] = '';
		$apps[$x]['default_settings'][$y]['default_setting_enabled'] = 'true';
		$apps[$x]['default_settings'][$y]['default_setting_description'] = 'User name to which incomming message should be assigned to. This must be super admin account';
		$y++;

	//schema details
		$y=0;
		$apps[$x]['db'][$y]['table']['name'] = "v_sms_messages";
		$apps[$x]['db'][$y]['table']['parent'] = "";
		
		$z=0;
		$apps[$x]['db'][$y]['fields'][$z]['name'] = "message_uuid";
		$apps[$x]['db'][$y]['fields'][$z]['type']['pgsql'] = "uuid";
		$apps[$x]['db'][$y]['fields'][$z]['type']['sqlite'] = "text";
		$apps[$x]['db'][$y]['fields'][$z]['type']['mysql'] = "char(36)";
		$apps[$x]['db'][$y]['fields'][$z]['key']['type'] = "primary";
		$z++;
		
		$apps[$x]['db'][$y]['fields'][$z]['name'] = "message_user";
		$apps[$x]['db'][$y]['fields'][$z]['type']['pgsql'] = "uuid";
		$apps[$x]['db'][$y]['fields'][$z]['type']['sqlite'] = "text";
		$apps[$x]['db'][$y]['fields'][$z]['type']['mysql'] = "char(36)";
		$apps[$x]['db'][$y]['fields'][$z]['key']['reference']['table'] = "v_users";
		$apps[$x]['db'][$y]['fields'][$z]['key']['reference']['field'] = "user_uuid";
		$z++;
	
		$apps[$x]['db'][$y]['fields'][$z]['name'] = "message_domain";
		$apps[$x]['db'][$y]['fields'][$z]['type']['pgsql'] = "uuid";
		$apps[$x]['db'][$y]['fields'][$z]['type']['sqlite'] = "text";
		$apps[$x]['db'][$y]['fields'][$z]['type']['mysql'] = "char(36)";
		$apps[$x]['db'][$y]['fields'][$z]['key']['type'] = "foreign";
		$apps[$x]['db'][$y]['fields'][$z]['key']['reference']['table'] = "v_domains";
		$apps[$x]['db'][$y]['fields'][$z]['key']['reference']['field'] = "domain_uuid";
		$apps[$x]['db'][$y]['fields'][$z]['description']['en-us'] = "";
		$z++;

		$apps[$x]['db'][$y]['fields'][$z]['name'] = "message_start_stamp";
		$apps[$x]['db'][$y]['fields'][$z]['type']['pgsql'] = "timestamp";
		$apps[$x]['db'][$y]['fields'][$z]['type']['sqlite'] = "date";
		$apps[$x]['db'][$y]['fields'][$z]['type']['mysql'] = "timestamp";
		$apps[$x]['db'][$y]['fields'][$z]['description']['en-us'] = "";
		$z++;
		$apps[$x]['db'][$y]['fields'][$z]['name'] = "message_from_number";
		$apps[$x]['db'][$y]['fields'][$z]['type'] = "text";
		$apps[$x]['db'][$y]['fields'][$z]['description']['en-us'] = "";
		$z++;
		$apps[$x]['db'][$y]['fields'][$z]['name'] = "message_to_number";
		$apps[$x]['db'][$y]['fields'][$z]['type'] = "text";
		$apps[$x]['db'][$y]['fields'][$z]['description']['en-us'] = "";
		$z++;
		$apps[$x]['db'][$y]['fields'][$z]['name'] = "message_text";
		$apps[$x]['db'][$y]['fields'][$z]['type'] = "text";
		$apps[$x]['db'][$y]['fields'][$z]['description']['en-us'] = "";
		$z++;
		$apps[$x]['db'][$y]['fields'][$z]['name'] = "message_direction";
		$apps[$x]['db'][$y]['fields'][$z]['type'] = "text";
		$apps[$x]['db'][$y]['fields'][$z]['description']['en-us'] = "";
		$z++;
		$apps[$x]['db'][$y]['fields'][$z]['name'] = "message_response";
		$apps[$x]['db'][$y]['fields'][$z]['type'] = "text";
		$apps[$x]['db'][$y]['fields'][$z]['description']['en-us'] = "";
		$z++;
		$apps[$x]['db'][$y]['fields'][$z]['name'] = "message_carrier";
		$apps[$x]['db'][$y]['fields'][$z]['type'] = "text";
		$apps[$x]['db'][$y]['fields'][$z]['description']['en-us'] = "";
		$z++;
		$apps[$x]['db'][$y]['fields'][$z]['name'] = "message_sent";
		$apps[$x]['db'][$y]['fields'][$z]['type']['pgsql'] = "timestamp";
		$apps[$x]['db'][$y]['fields'][$z]['type']['sqlite'] = "date";
		$apps[$x]['db'][$y]['fields'][$z]['type']['mysql'] = "timestamp";
		$apps[$x]['db'][$y]['fields'][$z]['description']['en-us'] = "Timestamp of acceptence by carrier 200 from API";
		$z++;
		$apps[$x]['db'][$y]['fields'][$z]['name'] = "message_delivered";
		$apps[$x]['db'][$y]['fields'][$z]['type']['pgsql'] = "timestamp";
		$apps[$x]['db'][$y]['fields'][$z]['type']['sqlite'] = "date";
		$apps[$x]['db'][$y]['fields'][$z]['type']['mysql'] = "timestamp";
		$apps[$x]['db'][$y]['fields'][$z]['description']['en-us'] = "Timestamp of delivery notification by api";
		$z++;

// Second Schema
		$y++;
		$apps[$x]['db'][$y]['table']['name'] = "v_sms_errors";
		$apps[$x]['db'][$y]['table']['parent'] = "";
		
		$z=0;
		$apps[$x]['db'][$y]['fields'][$z]['name'] = "error_uuid";
		$apps[$x]['db'][$y]['fields'][$z]['type']['pgsql'] = "uuid";
		$apps[$x]['db'][$y]['fields'][$z]['type']['sqlite'] = "text";
		$apps[$x]['db'][$y]['fields'][$z]['type']['mysql'] = "char(36)";
		$apps[$x]['db'][$y]['fields'][$z]['key']['type'] = "primary";
		$z++;
		$apps[$x]['db'][$y]['fields'][$z]['name'] = "error_time";
		$apps[$x]['db'][$y]['fields'][$z]['type']['pgsql'] = "timestamp";
		$apps[$x]['db'][$y]['fields'][$z]['type']['sqlite'] = "date";
		$apps[$x]['db'][$y]['fields'][$z]['type']['mysql'] = "timestamp";
		$apps[$x]['db'][$y]['fields'][$z]['description']['en-us'] = "Timestamp of delivery notification by api";
		$z++;
		$apps[$x]['db'][$y]['fields'][$z]['name'] = "error_text";
		$apps[$x]['db'][$y]['fields'][$z]['type'] = "text";
		$apps[$x]['db'][$y]['fields'][$z]['description']['en-us'] = "Error Text";
		$z++;

?>
