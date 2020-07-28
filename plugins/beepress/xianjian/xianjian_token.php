<?php 

if (!defined('ABSPATH')) exit;

include_once('xianjian_consts.php');
include_once('xianjian_utility.php');
include_once('xianjian_item.php');

add_action( 'wp_loaded', 'beepress_xianjian_setup');

function beepress_xianjian_setup() {
	beepress_xianjian_token_verify();
	beepress_xianjian_check_render_config();
	beepress_xianjian_check_item();
}

function beepress_xianjian_token_verify() {
	$site_id_key="paradigm_site_id";
	$site_token_key = "paradigm_site_token";
	$site_token = get_option($site_token_key);
	if ($site_token == "") {

	} else {
		return;
	}
	$site_id=get_option($site_id_key);
	if($site_id=="") {
		$site_id = beepress_xianjian_random_str(16);
		update_option($site_id_key,$site_id); 
	}
	global $beepress_xianjian_channel;
	update_option('paradigm_site_channel',$beepress_xianjian_channel);
	$body_arr = array( 
		'domain' => home_url(), 
		'plugSiteId' => $site_id,
		'terminalType' => 7,
		'plugChannel' => $beepress_xianjian_channel,
	);
	$body = json_encode((object)$body_arr);
	$args = array(
		'body' => $body_arr,
		'timeout' => '8'
	);
	global $beepress_xianjian_host;
	$remote_url = $beepress_xianjian_host.'/business/plug/register/login';
	$response = wp_remote_post($remote_url,$args);
	$response_body = wp_remote_retrieve_body($response);
	$response_obj = json_decode($response_body,true);
	try {
		$code = isset($response_obj['code']) ? $response_obj['code']: 0;
		if ($code == 200) {
			$data = isset($response_obj['data']) ? $response_obj['data'] : null;
			if (!$data) {
				return;
			}
			$beepress_xianjian_config_key = "paradigm_render_config";
			$original_config_str = get_option($beepress_xianjian_config_key);
			$original_config = null;
			if ($original_config_str == '') {
				$original_config = array();
			} else {
				$original_config = json_decode($original_config_str, true);
			}
			$client_token = isset($data['clientToken']) ? $data['clientToken'] : "";
			foreach ($data as $key => $value) {
				if (strcmp('token',$key) == 0) {
					
				} elseif (strcmp('clientToken', $key) == 0) {
					
				} else {
					$new_config_str = $value[$key];
					if (is_string($new_config_str)) {
						$new_config_str = stripslashes($new_config_str);
						$new_config = json_decode($new_config_str, true);
						$new_config['sceneId'] = $key;
						$new_config['clientToken'] = $client_token;
						$new_config['recomTitle'] = isset($new_config["sceneName"]) ? $new_config["sceneName"] : "";
						$new_config['itemSetId'] = isset($value['itemSetId']) ? $value['itemSetId'] : "";
						$new_config['accessToken'] = isset($value['accessToken']) ? $value['accessToken'] : "";
						$original_config[$key] = $new_config;
					}
				}
			}
			
			$total_config_str = json_encode($original_config);
			if (strlen($total_config_str) > 5) {
				update_option($beepress_xianjian_config_key,$total_config_str);
			}

			$token = isset($data['token']) ? $data['token']: null;
			if ($token) {
				update_option($site_token_key,$token);
			} else {
				return;
			}
		}

	} catch (Exception $e) {

	}
}

function beepress_xianjian_check_item() {
	global $beepress_xianjian_last_upload_timestamp_key,$beepress_xianjian_last_fetch_server_config_key,$beepress_xianjian_server_config_key,$beepress_xianjian_host,$beepress_xianjian_access_token;

	$last_fetch_config_timestamp = get_option($beepress_xianjian_last_fetch_server_config_key);
	$current_time = time();
	$beepress_xianjian_server_config = get_option($beepress_xianjian_server_config_key);
	$beepress_xianjian_fetch_interval = 60 * 60;
	$beepress_xianjian_day_minute_max = 30;
	$beepress_xianjian_night_minute_max = 300;
	$beepress_xianjian_day_limit = 1;
	$beepress_xianjian_night_limit = 20;
	if ($beepress_xianjian_server_config == "") {

	} else {
		$server_config = json_decode($beepress_xianjian_server_config,true);
		$beepress_xianjian_fetch_interval = isset($server_config['interval']) ? $server_config['interval'] : 60 *60;
		$beepress_xianjian_day_minute_max = isset($server_config['dayMinuteMax']) ? $server_config['dayMinuteMax'] : 30;
		$beepress_xianjian_night_minute_max = isset($server_config['nightMinuteMax']) ? $server_config['nightMinuteMax'] : 300;
		$beepress_xianjian_day_limit = isset($server_config['dayLimit']) ? $server_config['dayLimit'] : 1;
		$beepress_xianjian_night_limit = isset($server_config['nightLimit']) ? $server_config['nightLimit'] : 20;
	}
	if ($current_time - $last_fetch_config_timestamp > $beepress_xianjian_fetch_interval) {
		$args = array(
			'timeout' => '5'
		);
		$response = wp_remote_get($beepress_xianjian_host.'/business/cms/plug/post/config?token=ai4paradigm&accessToken='.$beepress_xianjian_access_token,$args);
		$response_body = wp_remote_retrieve_body($response);
		$response_obj = json_decode($response_body,true);
		$data = isset($response_obj['data']) ? $response_obj['data'] : null;
		if (!$data) {
			$current_time = time();
			update_option($beepress_xianjian_last_fetch_server_config_key,$current_time);
			return;
		}
		$config_str = isset($data['plugSitePostConfig']) ? $data['plugSitePostConfig'] : "";
		$config_str = stripslashes($config_str);
		if ($config_str == "" || strcmp($config_str, 'null')==0 || $config_str == null) {
		 	
		} else {
			update_option($beepress_xianjian_server_config_key,$config_str);
		}
		$current_time = time();
		update_option($beepress_xianjian_last_fetch_server_config_key,$current_time);
		return;
	}

	$post_limit = 5;
	$update_interval = 5;
	if (beepress_xianjian_check_night_time()) {
		$post_limit = $beepress_xianjian_night_limit;
		$update_interval = 60 / ($beepress_xianjian_night_minute_max / $beepress_xianjian_night_limit);
	} else {
		$post_limit = $beepress_xianjian_day_limit;
		$update_interval = 60 / ($beepress_xianjian_day_minute_max / $beepress_xianjian_day_limit);
	}

	$last_upload_tiemstamp = get_option($beepress_xianjian_last_upload_timestamp_key);
	$current_time = time();
	if ($current_time - $last_upload_tiemstamp < $update_interval) {
		return;
	}
	global $wpdb,$beepress_xianjian_access_token;
	if ($beepress_xianjian_access_token == "") {
		return;
	}

	$last_upload_id_key = 'last_upload_id_'.$beepress_xianjian_access_token;
	$last_upload_id = get_option($last_upload_id_key);

	if (!$last_upload_id) {
		$last_upload_id = 0;
	}

	# 获取文章信息
	$posts = $wpdb->get_results("SELECT ID,post_author,post_date,post_content,post_title,post_status,post_parent FROM `".$wpdb->prefix."posts` WHERE ID>".$last_upload_id." AND post_status='publish' ORDER BY ID ASC LIMIT ".$post_limit,ARRAY_A);
	foreach ($posts as $post) {
		$post_status = isset($post['post_status']) ? $post['post_status'] : "";
		$post_id = isset($post['ID']) ? $post['ID'] : 0;
		if (strcmp('publish', $post_status) == 0) {
			beepress_xianjian_upload_material($post,-1,$beepress_xianjian_access_token);
		} elseif (strcmp('inherit', $post_status) == 0) {
			$post_parent = isset($post['post_parent']) ? $post['post_parent'] : 0;
			if ((int)$post_parent != 0) {
				$inherit_id = $post_parent;
				$update_posts = $wpdb->get_results("SELECT ID,post_author,post_date,post_content,post_title,post_status FROM `".$wpdb->prefix."posts` WHERE ID=".$inherit_id,ARRAY_A);
				foreach ($update_posts as $update_post) {
					$update_post_status = isset($update_post['post_status']) ? $update_post['post_status'] : "";
					if (strcmp('publish',$update_post_status ) == 0) {
						beepress_xianjian_upload_material($update_post,$post_id,$beepress_xianjian_access_token);
					} else {
						update_option($last_upload_id_key,$post_id);
					}
				}
			} else {
				update_option($last_upload_id_key,$post_id);
			}
		} else {
			update_option($last_upload_id_key,$post_id);
		}
	}
	$new_time = time();
	update_option($beepress_xianjian_last_upload_timestamp_key, $new_time);
}

?>