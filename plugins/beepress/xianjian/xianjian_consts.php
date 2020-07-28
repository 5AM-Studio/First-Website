<?php 

if (!defined('ABSPATH')) exit;

$beepress_xianjian_channel = "plugin_official_beepress";
$beepress_xianjian_version = '1.1.4';
$beepress_xianjian_config_key = "paradigm_render_config";
$beepress_xianjian_is_theme = false;$beepress_xianjian_content_side = false;
$beepress_xianjian_home_side = false;
$beepress_xianjian_home_bottom = false;
$beepress_xianjian_config_loaded = false;
$beepress_xianjian_host = "https://nbrecsys.4paradigm.com";
$beepress_xianjian_sdk_obj = "ParadigmSDKv3";
if (strcmp($beepress_xianjian_host, "https://recsys-free.4paradigm.com") == 0) {
	$beepress_xianjian_sdk_obj = "ParadigmSDKv3Test";
}

$beepress_xianjian_render_content_side_id = 'paradigm_render_content_side_id';
$beepress_xianjian_render_content_append_id = 'paradigm_render_content_append_id';
$beepress_xianjian_render_content_comment_id = 'paradigm_render_content_comment_id';
$beepress_xianjian_render_content_comment_bottom_id = 'paradigm_render_content_comment_bottom_id';
$beepress_xianjian_render_home_side_id = 'paradigm_render_home_side_id';
$beepress_xianjian_render_home_bottom_id = 'paradigm_render_home_bottom_id';

$beepress_xianjian_content_side_id_key = 'paradigm_content_side_id';

$beepress_xianjian_last_check_update_timestamp_key = 'paradigm_last_check_update_timestamp';
$beepress_xianjian_last_upload_timestamp_key = 'paradigm_last_upload_timestamp';
$beepress_xianjian_last_fetch_server_config_key = 'paradigm_last_fetch_server_config';
$beepress_xianjian_server_config_key = 'paradigm_server_config';

$beepress_xianjian_access_token = "";

$beepress_xianjian_js_code_map = array();
$beepress_xianjian_side_js_code_map = array();
$beepress_xianjian_home_side_js_code_map = array();

?>