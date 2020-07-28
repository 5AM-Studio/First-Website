<?php $plug_url = plugins_url(); ?>
<?php $site_id_key="paradigm_site_id";$site_id=get_option($site_id_key); if ($site_id=="") {
    $characters = "0123456789abcdefghijklmnopqrstuvwxyz";
    for ($i=0;$i<16;$i++) {
        $site_id .= $characters[rand(0, strlen($characters)-1)];
    }
    update_option($site_id_key, $site_id);
}?>
<script type="text/javascript">
	var _paradigm_is_detail_config = false;
	// 配置完成更新后的回调
	function paradigmPostCallBack(plugConfig) {
		if (plugConfig.type === 'modify') {
			if (plugConfig.showDetailConfig === 1) {
				// 打开详细配置画面
				_paradigm_is_detail_config = true;
				_paradigm_is_detail_config_sceneId = plugConfig.sceneId;
				var baseUrl =
					'<?php global $beepress_xianjian_host;echo $beepress_xianjian_host?>' ||
					'https://nbrecsys.4paradigm.com'
				var siteId = '<?php echo $site_id?>' || ''
				const paradigm_detail_url = baseUrl +
					'/#/plugInBk/feDetailconfig?id=' + plugConfig.sceneId +
					'&type=wordpress&siteId=' + siteId + '&isOfficial=true'
				window.open(paradigm_detail_url, '_blank')
			} else {
				var phpUrl = window.location.origin + window.location.pathname;
				window.location.href = phpUrl +
					'?page=beepress_xianjian_rec_options' + (plugConfig.showCodeDialog ? '&sceneId=' + plugConfig.sceneId : "")
			}

		}
	}

	function paradigmPostPlugConfigToWordPress(dic, callBack) {
		if (dic == "") {
			return;
		}
		var form = document.getElementById("beepress_xianjian-form");

		var input = document.getElementById('beepress_xianjian-input');
		input.value = "true";

		var input_config = document.getElementById('beepress_xianjian-config');
		input_config.value = JSON.stringify(dic);
		form.appendChild(input_config);

		form.submit();
	}
</script>
<?php
if (!defined('ABSPATH')) {
    exit;
}
if (!empty($_POST) && strcmp($_POST['beepress_xianjian-input'], "true") == 0) {
    if (check_admin_referer('beepress_xianjian-nonce') && current_user_can('manage_options')) {
        $paradimg_config_key = "paradigm_render_config";
        $new_config_str = $_POST['beepress_xianjian-config'];
        $new_config_str = sanitize_text_field($new_config_str);
        if (is_string($new_config_str)) {
            $new_config_str = stripslashes($new_config_str);
            $new_config = json_decode($new_config_str, true);
            $original_config_str = get_option($paradimg_config_key);
            $original_config = null;
            if ($original_config_str == '') {
                $original_config = array();
            } else {
                $original_config = json_decode($original_config_str, true);
            }
            $scene_id = $new_config['sceneId'];
            $type = $new_config['type'];
            if (strcmp('delete', $type) == 0) {
                $delete_arr = array($scene_id => "1" );
                $original_config = array_diff_key($original_config, $delete_arr);
            } elseif (strcmp('modify', $type) == 0) {
                $original_config[$scene_id] = $new_config;
            }
            $total_config_str = json_encode($original_config);
            if (strlen($total_config_str) > 5) {
                update_option($paradimg_config_key, $total_config_str);
            }
            echo "<script>paradigmPostCallBack(".$new_config_str.")</script>";
        }
    }
}
?>

<head>

	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<input hidden id='paradigm_sitId' value='<?php  echo $site_id; ?>'>
	<input hidden id='paradigm_wpVersion' value='<?php global $wp_version;echo $wp_version ?>'>
	<input hidden id='paradigm_plugChannel' value='<?php global $beepress_xianjian_channel;echo $beepress_xianjian_channel?>'>
	<input hidden id='paradigm_baseUrl' value='<?php global $beepress_xianjian_host;echo $beepress_xianjian_host?>'>
</head>

<body>
	<form id="beepress_xianjian-form" action="#" method="post" name="setting" target="">
		<input type="hidden" name="beepress_xianjian-input" id="beepress_xianjian-input">
		<input type="hidden" name="beepress_xianjian-config" id="beepress_xianjian-config">
		<?php wp_nonce_field("beepress_xianjian-nonce"); ?>
	</form>
</body>