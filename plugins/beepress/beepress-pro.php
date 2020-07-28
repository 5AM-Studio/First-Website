<?php
// 引入 DOM 解析库
if(!class_exists('simple_html_dom_node')){
	require_once("simple_html_dom.php");
}
if(!class_exists('BeePressUtils')){
	require_once("beepress-utils.php");
}

// 添加左边栏入口
if (!function_exists('beepress_pro_request_page')) {
	function beepress_pro_request_page() {
		require_once 'beepress-pro-request-page.php';
	}
}

// 初始化
if (!function_exists('beepress_pro_admin_init')) {
	function beepress_pro_admin_init() {
		$count = get_option('bp_count');
		if (!$count) {
			add_option('bp_count', 5);
		}
		$page = isset($_REQUEST['page']) ? $_REQUEST['page'] : '';
		if (in_array($page, array('beepress_pro_request', 'beepress_pro_option'))) {
			wp_enqueue_style('BOOTSTRAPCSS', plugins_url('/lib/bootstrap.min.css', __FILE__), array(), '3.3.7', 'screen');
			wp_enqueue_script('BOOTSTRAPJS', plugins_url('/lib/bootstrap.min.js', __FILE__), array( 'jquery'), '3.3.7', true);
			wp_enqueue_style('BEEPRESSCSS', plugins_url('/lib/beepress.css', __FILE__), array(), BEEPRESS_VERSION, 'screen');
			wp_enqueue_script('CLIPBOARDJS', plugins_url('/lib/clipboard.min.js', __FILE__), array(), BEEPRESS_VERSION, false);
			wp_enqueue_script('BEEPRESSJS', plugins_url('/lib/beepress-pro.js', __FILE__), array( 'jquery'), BEEPRESS_VERSION, true);
		}
	}
}
add_action('admin_init', 'beepress_pro_admin_init');

if (!function_exists('beepress_pro_the_content_filter')) {
	function beepress_pro_the_content_filter($content) {
		if (is_single()) {
			$postId = get_the_ID();
			$isBP = get_post_meta($postId, 'is_bp', true);
			if ($isBP) {
				$contentBefore = get_option('bp_content_before', '');
				$contentAfter  = get_option('bp_content_after', '');
				$content = $contentBefore . $content . $contentAfter;
			}
		}
		return $content;
	}
}
add_filter('the_content', 'beepress_pro_the_content_filter');

add_filter('manage_posts_columns', 'bp_add_post_type');
add_action('manage_posts_custom_column', 'bp_get_post_type_content', 10, 2);

function bp_add_post_type($defaults) {
	$defaults['bp_is_bp'] = '来自采集';
	return $defaults;
}

function bp_get_post_type_content($colName, $postID) {
	if ($colName === 'bp_is_bp') {
		$isBP = get_post_meta($postID, 'is_bp', true);
		if ($isBP == 1) {
			echo '<div style="color: red">是</div>';
		} else {
			echo '否';
		}
	}
}

if (!function_exists('beepress_pro_init')) {
	function beepress_pro_init() {
		wp_enqueue_script('PLAYERJS', plugins_url('/lib/player.js', __FILE__), array( 'jquery'), BEEPRESS_VERSION, false);
		wp_enqueue_script('BPFRONTJS', plugins_url('/lib/beepress-front.js', __FILE__), array( 'jquery'), BEEPRESS_VERSION, true);
		wp_enqueue_style('BEEPRESSCSS', plugins_url('/lib/beepress.css', __FILE__), array(), BEEPRESS_VERSION, 'screen');

		// 自动同步
		$syncpressPush = isset($_REQUEST['action']) ? $_REQUEST['action'] == 'syncpress_push' : false;
		$syncpressPushToken = isset($_REQUEST['push_token']) ? $_REQUEST['push_token'] : '';
		$syncpressPushStatus  = get_option('syncpress_push_status', 'open');
		if ($syncpressPushStatus == 'open' && $syncpressPush && $syncpressPushToken && ($syncpressPushToken == get_option('syncpress_push_token', null))) {
			$_REQUEST['urls'] = isset($_REQUEST['article_url']) ? $_REQUEST['article_url'] : '';
			$_REQUEST['platform'] = isset($_REQUEST['platform']) ? $_REQUEST['platform'] : null;
			beepress_pro_process_request($syncpressPush);
			exit;
		}

		// 助手同步
		$syncToWP = isset($_REQUEST['action']) ? $_REQUEST['action'] == 'sync_to_wp' : false;
		if ($syncToWP && ($syncpressPushToken == get_option('syncpress_push_token', null))) {
			$_REQUEST['urls'] = isset($_REQUEST['post']) ? $_REQUEST['post'] : '';
			$_REQUEST['platform'] = 'wechat';
			beepress_pro_process_request();
			exit;
		}

		if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'beepress_set_license_code') {
			// 检查是否已经设置成功，避免被修改
			$host                 = parse_url(home_url(), PHP_URL_HOST);
			$homeURL              = preg_replace('/(http:\/\/|https:\/\/)/', '', home_url());
			$licenseCode          = get_option('bp_license_code');
			$host2                = str_replace('www.', '', $host);
			$salt                 = 'FXqqh4gVu27Rd696';
			$requestLicenseCode   = md5($homeURL . $salt);
			$requestLicenseCode2  = md5($host . $salt);
			$requestLicenseCode3  = md5($host2 . $salt);
			$pass                 = $licenseCode && ($licenseCode == $requestLicenseCode2 || $licenseCode == $requestLicenseCode || $licenseCode == $requestLicenseCode3  );
			if (!$pass && isset($_POST['code']) && $_POST['code']) {
				if ($_POST['code'] == $requestLicenseCode || $_POST['code'] == $requestLicenseCode2 || $_POST['code'] == $requestLicenseCode3) {
					update_option('bp_license_code', $_POST['code']);
					echo "1";
				} else {
					echo "0";
				}
			} else {
				echo "2";
			}
			echo "<br>";
			exit;
		}

		if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'beepress_validate') {
			$validateCode = '031591';
			if (isset($_REQUEST['validate_code']) && $_REQUEST['validate_code'] == $validateCode) {
				update_option('is_validate', true);
				echo '验证成功，<a href="' . site_url('wp-admin/admin.php?page=beepress_pro_request') . '">前往查看</a>';
			} else {
				echo '验证失败，<a href="http://xingyue.artizen.me/auth/index.php?site_url=' . site_url() . '">返回</a>';
			}
			exit;
		}
	}
}

add_action('init', 'beepress_pro_init');

if (!function_exists('beepress_pro_option_menu')) {
	function beepress_pro_option_menu() {
		$utils = new BeePressUtils();
		add_submenu_page('beepress_pro', '采集导入页面', '采集文章', 'publish_posts', 'beepress_pro_request', 'beepress_pro_request_page');
		if (get_option('bp_close_xianjian', 'no') == 'yes' && $utils->check_license() ) {
		} else {
			add_submenu_page('beepress_pro', __('先荐选项'), '内容推荐', 'manage_options', 'beepress_xianjian_rec_options', 'beepress_xianjian_rec_options');
		}
		add_submenu_page('beepress_pro', '配置页面', '配置&帮助', 'publish_posts', 'beepress_pro_option', 'beepress_pro_option_page');
		remove_submenu_page('beepress_pro', 'beepress_pro');
		add_action('admin_init', 'beepress_pro_register_option');
	}
}

//
if (!function_exists('beepress_pro_anti_protected_link')) {
	function beepress_pro_anti_protected_link() {
		if (get_option('bp_anti_protected_link', 'no') == 'yes') {
			echo '<meta name="referrer" content="no-referrer">';
		}
	}
}
add_action('wp_head', 'beepress_pro_anti_protected_link');

// setting page
if (!function_exists('beepress_pro_option_page')) {
	function beepress_pro_option_page() {
		require_once 'beepress-pro-options-page.php';
	}
}

if (!function_exists('beepress_pro_register_option')) {
	function beepress_pro_register_option() {
		register_setting('beepress-option-group', 'bp_post_time');
		register_setting('beepress-option-group', 'bp_post_status');
		register_setting('beepress-option-group', 'bp_image_dir');
		register_setting('beepress-option-group', 'bp_keep_copyright');
		register_setting('beepress-option-group', 'bp_keep_style');
		register_setting('beepress-option-group', 'bp_sync_token');
		register_setting('beepress-option-group', 'bp_sync_times');
		register_setting('beepress-option-group', 'bp_image_centered');
		register_setting('beepress-option-group', 'bp_featured_image');
		register_setting('beepress-option-group', 'bp_image_name_prefix');
		register_setting('beepress-option-group', 'bp_hide_lite_edition');
		register_setting('beepress-option-group', 'bp_close_xianjian');
		register_setting('beepress-option-group', 'bp_close_sponsor');
		register_setting('beepress-option-group', 'bp_copyright_position');
		register_setting('beepress-option-group', 'bp_image_title_alt');
		register_setting('beepress-option-group', 'bp_image_path');
		register_setting('beepress-option-group', 'bp_remove_outerlink');
		register_setting('beepress-option-group', 'bp_download_image');
		register_setting('beepress-option-group', 'bp_anti_protected_link');
		register_setting('beepress-option-group', 'bp_content_before');
		register_setting('beepress-option-group', 'bp_content_after');
		register_setting('beepress-option-group', 'bp_title_before');
		register_setting('beepress-option-group', 'bp_title_after');
		register_setting('beepress-option-group', 'bp_keywords_lib');
	}
}

// ajax 请求
//include_once('xianjian/xianjian_utility.php');
add_action('wp_ajax_beepress_pro_license_check', 'beepress_pro_license_check');
if (!function_exists('beepress_pro_license_check')) {
	function beepress_pro_license_check() {
		$salt               = 'FXqqh4gVu27Rd696';
		$homeURL            = preg_replace('/(http:\/\/|https:\/\/)/', '', home_url());
		$host               = parse_url(home_url(), PHP_URL_HOST);
		$host2              = str_replace('www.', '', $host);
		$licenseCode        = get_option('bp_license_code');

		$requestLicenseCode  = md5($homeURL . $salt);
		$requestLicenseCode2 = md5($host . $salt);
		$requestLicenseCode3 = md5($host2 . $salt);
		$pass = $licenseCode && ($licenseCode == $requestLicenseCode || $licenseCode == $requestLicenseCode2 || $licenseCode == $requestLicenseCode3);

		$isValidate = get_option('is_validate', false);
		if ($isValidate) {
			$pass = true;
		}
		wp_send_json(array(
			'success' => $pass,
			'data'    => intval(get_option('bp_count')),
		));
	}
}

add_action('wp_ajax_beepress_pro_get_file_content', 'beepress_pro_get_file_content');
if (!function_exists('beepress_pro_get_file_content')) {
	function beepress_pro_get_file_content() {
		$file = isset($_FILES['urlfile']) ? $_FILES['urlfile'] : null;
		$urls = '';
		if (isset($file['tmp_name']) && $file['tmp_name']) {
			$urls = file_get_contents($file['tmp_name']);
		}
		wp_send_json(array(
			'urls' => $urls
		));
	}
}

add_action('wp_ajax_beepress_set_license_code', 'beepress_set_license_code');
if (!function_exists('beepress_set_license_code')) {
	function beepress_set_license_code()
	{
		$type = $_POST['license_type'];
		$code = $_POST['license_code'];
		switch ($type) {
			case 'basic':
			case 'pro':
				update_option('bp_license_code', $code);
				break;
		}
	}
}

add_action('wp_ajax_syncpress_save_setting', 'syncpress_save_setting');
if (!function_exists('syncpress_save_setting')) {
	function syncpress_save_setting()
	{
		$settings = isset($_REQUEST['setting']) ? $_REQUEST['setting'] : array();
		$token = isset($_REQUEST['token']) ? $_REQUEST['token'] : '';
		$pushStatus = isset($_REQUEST['syncpressPushStatus']) ? $_REQUEST['syncpressPushStatus'] : 'open';
		update_option('syncpress_push_token', trim($token));
		update_option('syncpress_push_status', $pushStatus);
		// 去掉重复的设置
		$accountIds = array();
		foreach ($settings as $setting) {
			$accountIds[] = $setting['account_id'];
		}

		$newSettings = array();
		foreach ($accountIds as $accountId) {
			foreach ($settings as $setting) {
				if (isset($setting['account_id']) && $setting['account_id'] == $accountId) {
					$newSettings[$accountId] = $setting;
				}
			}
		}
		// 去掉key
		$newSettings = array_values($newSettings);
		update_option('syncpress_push_settings', $newSettings);
		wp_send_json(array(
			'setting' => get_option('syncpress_push_settings'),
			'push_token' => $token
		), 200);
	}
}

add_action('wp_ajax_beepress_pro_rule_save_setting', 'beepress_pro_rule_save_setting');
if (!function_exists('beepress_pro_rule_save_setting')) {
	function beepress_pro_rule_save_setting()
	{
		$settings = isset($_REQUEST['settings']) ? $_REQUEST['settings'] : array();
		update_option('beepress_rule_settings', $settings);
		wp_send_json(array(
			'setting' => get_option('beepress_rule_settings')
		), 200);
	}
}

add_action('wp_ajax_beepress_pro_process_request', 'beepress_pro_process_request');
if (!function_exists('beepress_pro_process_request')) {
	function beepress_pro_process_request($syncPressPush = false) {
		$syncToWP = isset($_REQUEST['action']) ? $_REQUEST['action'] == 'sync_to_wp' : false;
		if (!is_admin() && (!$syncPressPush && !$syncToWP)) {
			wp_send_json(array(
				'success' => false,
				'message' => '您没有权限使用该接口'
			));
		}

		// 参数获取
		// 媒体平台
		$platform = isset($_REQUEST['platform']) ? $_REQUEST['platform'] : null;
		// 文章链接
		$urls = isset($_REQUEST['urls']) ? $_REQUEST['urls'] : '';
		if (!($urls)) {
			wp_send_json(array(
				'success' => false,
				'message' => '没有符合要求的文章链接'
			));
		}

		$finalUrls = explode("\n", $urls);
		// url为空
		if (count($finalUrls) == 0) {
			wp_send_json(array(
				'success' => false,
				'message' => 'URL为空'
			));
		}
		$postId = null;
		switch ($platform) {
			case 'wechat':
				$postId = beepress_pro_for_platform($finalUrls, $platform);
				break;
			case 'zhihu':
				$postId = beepress_pro_for_platform($finalUrls , $platform);
				break;
			case 'jianshu':
				$postId = beepress_pro_for_platform($finalUrls, $platform);
				break;
			case 'toutiao':
				$postId = beepress_pro_for_platform($finalUrls, $platform);
				break;
			case 'default':
				$postId = beepress_pro_for_platform($finalUrls, $platform);
				break;
			default:
				wp_send_json(array(
					'success' => false,
					'message' => '暂不支持该平台'
				));
				break;
		}

		if ($syncPressPush) {
			wp_send_json(array(
				'home_url'      => home_url(),
				'push_settings' => get_option('syncpress_push_settings', array()),
				'push_token'    => get_option('syncpress_push_token', ''),
				'post_id'       => $postId
			));
		}

		if ($syncToWP) {
			wp_send_json(array(
				'success' => true,
				'post_id' => $postId
			));
		}

		if (!is_int($postId)) {
			wp_send_json(array(
					'success' => false,
					'data'    => $postId,
					'message' => $postId
			));
		} else {
			wp_send_json(array(
					'success'     => true,
					'data'        => $postId,
					'message'     => '导入成功'
			));
		}
	}
}


if (!function_exists('beepress_pro_for_platform')) {
	function beepress_pro_for_platform($urls, $platform) {
		if (count($urls) == 0) {
			wp_send_json(array(
				'success' => false,
				'message' => '链接为空'
			));
			return null;
		}
		$syncToWP             = isset($_REQUEST['action']) ? $_REQUEST['action'] == 'sync_to_wp' : false;
		$syncpressPush        = isset($_REQUEST['action']) ? $_REQUEST['action'] == 'syncpress_push' : false;
		// 作者，目前是以当前登录用户作为作者
		$userId               = get_current_user_id();
		$postTags             = isset($_REQUEST['post_tags']) ? $_REQUEST['post_tags'] : '';
		$postTags             = array_map('trim', explode('#', $postTags));
		$MSG                  = '';
		$userId               = isset($_REQUEST['post_user']) ? $_REQUEST['post_user'] : $userId;
		$ruleSettings         = get_option('beepress_rule_settings', array());
		$hosts                = array();
		foreach ($ruleSettings as $setting) {
			$hosts[] = str_replace(array('http://', 'https://'), array('', ''), $setting['site']);
		}

		$host                 = parse_url(home_url(), PHP_URL_HOST);
		$host2                = str_replace('www.', '', $host);
		$homeURL              = preg_replace('/(http:\/\/|https:\/\/)/', '', home_url());
		$licenseCode          = get_option('bp_license_code');
		$salt                 = 'FXqqh4gVu27Rd696';
		$requestLicenseCode   = md5($homeURL . $salt);
		$requestLicenseCode2  = md5($host . $salt);
		$requestLicenseCode3  = md5($host2 . $salt);
		$pass                 = $licenseCode && ($licenseCode == $requestLicenseCode3 || $licenseCode == $requestLicenseCode2 || $licenseCode == $requestLicenseCode);

		$isValidate = get_option('is_validate', false);
		if ($isValidate) {
			$pass = true;
		}
		if (!$syncpressPush && !$syncToWP) {
			// 文章分类
			$postCate         = isset($_REQUEST['post_cate']) ? $_REQUEST['post_cate'] : array();
			$postCate         = array_map('intval', $postCate);
		} else {
			// 同步采用同步设置中的规则
			$accountId = null;
			if ($syncToWP) {
				$accountId = isset($_REQUEST['biz']) ? $_REQUEST['biz'] : null;
				$_REQUEST['accountId'] = $accountId;
			}
			if ($syncpressPush) {
				$pass = true;
				$accountId = isset($_REQUEST['accountId']) ? $_REQUEST['accountId'] : null;
			}
			$syncpressPushSettings = get_option('syncpress_push_settings', array());
			$accountSetting = array();
			foreach ($syncpressPushSettings as $setting) {
				if (strtolower(trim($setting['account_id'])) == strtolower($accountId)) {
					$accountSetting = $setting;
					break;
				}
			}
			if (empty($accountSetting)) {
				wp_send_json(array(
					'success' => false,
					'message' => '没有找到该公众号的设置信息'
				));
			}
			if (isset($accountSetting['post_author']) && get_user_by('ID', $accountSetting['post_author'])) {
				$userId = $accountSetting['post_author'];
			}
			$postCate = array_map('intval', $accountSetting['cat_ids']);
			$_REQUEST['post_status'] = isset($accountSetting['post_status']) ? $accountSetting['post_status'] : 'publish';
		}
		if (isset($_SERVER['SERVER_ADDR']) && $_SERVER['SERVER_ADDR'] == '127.0.0.1') {
			$pass = true;
		}

		foreach ($urls as $url) {
			// 过滤文章链接
			switch ($platform) {
				case 'default':
					$urlHost = parse_url($url, PHP_URL_HOST);
					if (!in_array($urlHost, $hosts)) {
						continue;
					}
					break;
				case 'wechat':
					$url = str_replace('https', 'http', $url);
					if (strpos($url, 'http://mp.weixin.qq.com') !== false || strpos($url, 'https://mp.weixin.qq.com') !== false) {
						$url = trim($url);
						if (!$url) {
							$MSG .= '|URL不能为空';
							continue;
						}
					} else {
						continue;
					}
					break;
				case 'zhihu':
					if (strpos($url, 'https://zhuanlan.zhihu.com') !== false || strpos($url, 'http://zhuanlan.zhihu.com') !== false) {
						$url = trim($url);
						if (!$url) {
							$MSG .= '|URL不能为空';
							continue;
						}
					} else {
						continue;
					}
					break;
				case 'jianshu':
					if (strpos($url, 'jianshu.com')) {
						$url = trim($url);
						if (!$url) {
							$MSG .= '|URL不能为空';
							continue;
						}
					} else {
						continue;
					}
					break;
				case 'toutiao':
					if (strpos($url, '.toutiao.com') !== false) {
						$url = trim($url);
						if (!$url) {
							$MSG .= '|URL不能为空';
							continue;
						}
					} else {
						continue;
					}
					break;
				default:
					continue;
			}
			if (!$pass) {
				if ($count = intval(get_option('bp_count'))) {
					$count--;
					update_option('bp_count', $count);
				} else {
					return '免费试用次数已经用完，请联系开发者购买授权码(微信：always-bee，注明BeePress)';
				}
				if ($count <= -1 && $count > 5) {
					return '免费试用次数已经用完，请联系开发者购买授权码(微信：always-bee，注明BeePress)';
				}
			}
			$content = '';
			if (function_exists('file_get_contents')) {
				$content = @file_get_contents($url);
			}

			$urlHost = parse_url($url, PHP_URL_HOST);
			$rule = beepress_get_rule_setting($urlHost);
			if (isset($rule['encodeRule']) && strtolower($rule['encodeRule']) != 'utf8') {
				$content = @iconv(strtolower($rule['encodeRule']), 'utf-8//IGNORE', $content);
			}

			$dom = str_get_html($content);
			// 文章标题
			if ($platform == 'default') {
				$title = getPostTitle($dom, $platform, parse_url($url, PHP_URL_HOST), $content);
			} else {
				$title = getPostTitle($dom, $platform, null, $content);
			}
			// 没有标题
			if (!$title) {
				$ch      = curl_init();
				$timeout = 60;
				curl_setopt($ch, CURLOPT_URL, $url);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
				switch ($platform) {
					case 'wechat':
						curl_setopt($ch, CURLOPT_USERAGENT,'Mozilla/5.0 (Linux; Android 6.0; 1503-M02 Build/MRA58K) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/37.0.0.0 Mobile MQQBrowser/6.2 TBS/036558 Safari/537.36 MicroMessenger/6.3.25.861 NetType/WIFI Language/zh_CN');
						break;
					case 'toutiao':
						curl_setopt($ch, CURLOPT_USERAGENT,'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/70.0.3538.110 Safari/537.36');
						curl_setopt($ch, CURLOPT_REFERER, 'https://www.toutiao.com/');
						break;
					default:
						curl_setopt($ch, CURLOPT_USERAGENT,'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/66.0.3359.139 Safari/537.36');
						break;
				}
				$content = curl_exec($ch);
				curl_close($ch);

				if (!$content) {
					$MSG .= '|无法获取改链接内容';
					wp_send_json(array(
						'success' => false,
						'message' => '无法获取改链接内容'
					));
					continue;
				}
				if (isset($rule['encodeRule']) && strtolower($rule['encodeRule']) != 'utf8') {
					$content = @iconv(strtolower($rule['encodeRule']), 'utf8', $content);
				}
				$dom = str_get_html($content);
				// 文章标题
				if ($platform == 'default') {
					$title = getPostTitle($dom, $platform, parse_url($url, PHP_URL_HOST), $content);
				} else {
					$title = getPostTitle($dom, $platform, null, $content);
				}
				if (!$title) {
					$MSG .= '|无法获取文章标题';
					wp_send_json(array(
						'success' => false,
						'message' => '无法获取文章标题'
					));
					continue;
				}
			}
			// 文章重复
			$skipDuplicate = isset($_REQUEST['skip_duplicate']) && ($_REQUEST['skip_duplicate'] == 'yes');
			if ($id = post_exists($title) && ($skipDuplicate || $syncpressPush || $syncToWP)) {
				wp_send_json(array(
					'success' => false,
					'message' => '文章重复'
				));
				continue;
			}

			$title = isset($_REQUEST['custom_title']) && $_REQUEST['custom_title'] ? $_REQUEST['custom_title'] : $title;

			// 处理图片及视频、音频
			$desc = null;
			if ($platform == 'wechat') {
				$imageDom = $dom->find('img');
				$videoDom = $dom->find('.video_iframe');
				$accountName = esc_html($dom->find('#post-user', 0)->plaintext);
				$audioDom = $dom->find('mpvoice');
				$audioDomain = 'http://res.wx.qq.com/voice/getvoice?mediaid=';
				$audioCounter = 0;
				foreach ($audioDom as $audio) {
					$audioCounter++;
					$audioSrc = $audioDomain . $audio->getAttribute('voice_encode_fileid');
					$auioName = $audio->getAttribute('name');
					$parent = $audio->parent();
					$parent->innertext = '<div class="aplayer" id="audio-' . $audioCounter . '"></div>'.'<script>var audio' . $audioCounter .' = new BeePlayer({element: document.getElementById("audio-' . $audioCounter . '"),music:{title:  "' . $auioName .'", author: "' . $accountName . '",pic: "' . plugins_url('/lib/player.png', __FILE__) . '", url: "' . $audioSrc . '"}}); audio' . $audioCounter . '.init();' . '</script>';
				}

				foreach ($imageDom as $image) {
					$dataSrc = $image->getAttribute('data-src');
					if (!$dataSrc) {
						continue;
					}
					$src = $dataSrc;
					$image->setAttribute('src', $src);
				}

				foreach ($videoDom as $video) {
					$dataSrc = $video->getAttribute('data-src');
					// 处理视频链接
					$dataSrc = preg_replace('/(width|height)=([^&]*)/i', '', $dataSrc);
					$dataSrc = str_replace('&&', '&', $dataSrc);
					$video->setAttribute('src', $dataSrc);
				}
			}
			if ($platform == 'jianshu') {
				$imageDom = $dom->find('.image-view img');
				foreach ($imageDom as $image){
					$dataSrc = $image->getAttribute('data-original-src');
					if (!$dataSrc) {
						continue;
					}
					$image->setAttribute('src', $dataSrc);
				}
				// 处理图片的格式
				$imageContainer = $dom->find('.image-container');
				foreach ($imageContainer as $container) {
					$container->setAttribute('style', '');
				}
				$imageContainerFill = $dom->find('.image-container-fill');
				foreach ($imageContainerFill as $fill) {
					$fill->setAttribute('style', '');
				}
			}

			// 文章发布日期
			$postDate = current_time('mysql');
			// 文章发布时间
			$keepOriginalPostTime = get_option('bp_post_time', 'original_time') == 'original_time';
			if ($keepOriginalPostTime) {
				switch ($platform) {
					case 'wechat':
						preg_match('/(publish_time = ")([^\"]+)"/', $content, $matches);
						$postDate = isset($matches[2]) ? $matches[2] : $postDate;
						break;
					case 'jianshu':
						$timeStr = $dom->find('.publish-time', 0)->innertext;
						$postDate = str_replace('.', '-', $timeStr);
						$postDate = explode(' ', $postDate);
						$postDate = $postDate[0];
						break;
				}
				$postDate = date('Y-m-d H:i:s', strtotime($postDate) + 50);
			}

			if (count($postCate) == 0) {
				$postCate = array(1);
			}
			$postType = isset($_REQUEST['post_type']) ? $_REQUEST['post_type'] : 'post';
			$_REQUEST['post_title'] = htmlspecialchars_decode($title);
			$titleBefore = get_option('bp_title_before', '');
			$titleAfter = get_option('bp_title_after', '');
			$_REQUEST['post_title'] = $titleBefore . $_REQUEST['post_title'] . $titleAfter;

			$post = array(
				'post_title'    => $_REQUEST['post_title'],
				'post_name'     => substr(md5($_REQUEST['post_title'] . time()), 0, 10),
				'post_content'  => '',
				'post_status'   => 'pendding',
				'post_author'   => $userId,
				'post_category' => $postCate,
				'tags_input'    => $postTags,
				'post_type'	    => $postType,
			);
			$_REQUEST['post_date'] = $postDate;

			$postId = wp_insert_post($post);
			add_post_meta($postId, 'is_bp', 1);
			// 公众号设置featured image
			// 是否设置特色图片
			$setFeaturedImage  = get_option('bp_featured_image', 'yes') == 'yes';
			$removeImage       = isset($_REQUEST['remove_image']) ? $_REQUEST['remove_image'] == 'yes' : false;
			if ($platform == 'wechat' && $setFeaturedImage && !$removeImage) {
				preg_match('/(msg_cdn_url = ")([^\"]+)"/', $content, $matches);
				$redirectUrl   = 'http://read.html5.qq.com/image?src=forum&q=4&r=0&imgflag=7&imageUrl=';
				switch ($platform) {
					case 'wechat':
						$coverImageSrc = $matches[2];
						$coverImageSrc = $redirectUrl . $coverImageSrc;
						$tmpFile       = beepress_pro_download_url($coverImageSrc, $url);
						break;
					default:
						$coverImageSrc = $redirectUrl . $matches[2];
						$tmpFile       = download_url($coverImageSrc);
						break;
				}
				if (is_string($tmpFile)) {
					$prefixName = get_option('bp_image_name_prefix', 'beepress');
					$randomInt  = rand(0, 10);
					$fileName   = $prefixName . $randomInt .  '-' . time() . '.jpeg';
					$fileArr    = array(
						'name'     => $fileName,
						'tmp_name' => $tmpFile
					);
					$id = @media_handle_sideload($fileArr, $postId);
					if (!is_wp_error($id)) {
						@set_post_thumbnail($postId, $id);
					}
				}
			}
			// 下载图片
			$postId = beepress_pro_download_image($postId, $dom, $platform, $url, $content);
			unset($content);
			if (count($urls) == 1) {
				return $postId;
			}
		}
		return null;
	}
}

if (!function_exists('beepress_pro_download_image')) {
	function beepress_pro_download_image($postId, $dom, $platform, $postSrc = '', $content = '') {
		switch ($platform) {
			case 'toutiao':
				preg_match("/(content: ')([^\']+)'/", $content, $matches);
				$content = mb_convert_encoding($matches[2], 'HTML-ENTITIES' , 'utf8');
				$content = mb_convert_encoding($content, 'utf8', 'HTML-ENTITIES' );
				$content = '<div id="tt-content">' . $content . '</div>';
				$dom = str_get_html($content);
				break;
		}
		if (!$postId || !$dom) {
			return null;
		}
		$beepressUtils = new BeePressUtils();
		$host = parse_url($postSrc, PHP_URL_HOST);
		$scheme = parse_url($postSrc, PHP_URL_SCHEME);
		$syncpressPush        = isset($_REQUEST['action']) ? $_REQUEST['action'] == 'syncpress_push' : false;
		$syncToWP             = isset($_REQUEST['action']) ? $_REQUEST['action'] == 'sync_to_wp' : false;
		if ($platform != 'jianshu') {
			if ($platform == 'default') {
				$rule = beepress_get_rule_setting($host);
				$contentRule = $rule['contentRule'];
				$imageRule = explode('|', $rule['imgRule']);
				$images = $dom->find($contentRule . ' ' . $imageRule[0]);
			} else {
				$images            = $dom->find('img');
			}
		} else {
			$images            = $dom->find('.show-content img');
		}
		// 是否设置特色图片
		$setFeaturedImage  = get_option('bp_featured_image', 'yes') == 'yes';
		$title = $_REQUEST['post_title'];
		$removeOuterlink   = 'no';
		// 版权
		$keepCopyRight     = get_option('bp_keep_copyright', 'yes') == 'yes';
		$hasSetFeaturedImg = false;
		// 移除图片
		$removeImage       = isset($_REQUEST['remove_image']) ? $_REQUEST['remove_image'] == 'yes' : false;
		// 移除指定位置图片
		if ($syncpressPush || $syncToWP) {
			$syncpressPushSettings = get_option('syncpress_push_settings', array());
			if ($syncToWP) {
				$accountId = isset($_REQUEST['biz']) ? $_REQUEST['biz'] : null;
				$_REQUEST['accountId'] = $accountId;
			}
			$accountId = isset($_REQUEST['accountId']) ? $_REQUEST['accountId'] : null;
			$accountSetting = array();
			foreach ($syncpressPushSettings as $setting) {
				if (strtolower(trim($setting['account_id'])) == strtolower($accountId)) {
					$accountSetting = $setting;
					break;
				}
			}
			$removeSpecifiedImages = isset($accountSetting['remove_images']) ? $accountSetting['remove_images'] : array();
			// 移除外链
			$removeOuterlink = isset($accountSetting['remove_outerlink']) ? $accountSetting['remove_outerlink'] : 'no';
		} else {
			$removeSpecifiedImages = isset($_REQUEST['remove_specified_iamges']) ? $_REQUEST['remove_specified_iamges']: array();
		}

		$removeSpecifiedImages = array_map('intval', $removeSpecifiedImages);
		// 图片居中
		$centeredImage     = get_option('bp_image_centered', 'no') == 'yes';
		$imageTitleAlt     = isset($_REQUEST['image_title_alt']) && $_REQUEST['image_title_alt'] ? $_REQUEST['image_title_alt'] : get_option('bp_image_title_alt', '');

		if ($imageTitleAlt) {
			$title = $imageTitleAlt;
		}
		$imageCounter = 0;
		$imageTotal = count($images);
		// 过滤无效的图片
		foreach ($images as $image) {
			$src = $image->getAttribute('src');
			if (!$src || strstr($src, 'res.wx.qq.com') || strstr($src, 'wx.qlogo.cn') || $src == 'http://mp.weixin.qq.com') {
				$imageTotal--;
			}
		}
		foreach ($images as $image) {
			$rule = beepress_get_rule_setting($host);
			$imageRule = explode('|', $rule['imgRule']);
			if (count($imageRule) == 2) {
				$srcVal = $image->getAttribute($imageRule[1]);
				if (strstr($srcVal, './')) {
					$srcVal = $beepressUtils->format_url($postSrc, $srcVal);
					$image->setAttribute('src', $srcVal);
				}
				if ($srcVal) {
					if (!strstr($srcVal, $host) && !strstr($srcVal, '//')) {
						$srcVal = $scheme . '//' . $host . $srcVal;
					}
					$image->setAttribute('src', $srcVal);
				}
			} else {
				$srcVal = $image->getAttribute('src');
				if (strstr($srcVal, './')) {
					$srcVal = $beepressUtils->format_url($postSrc, $srcVal);
					$image->setAttribute('src', $srcVal);
				}
				if (!strstr($srcVal, $host) && !strstr($srcVal, '//')) {
					$srcVal = $scheme . '://' . $host . $srcVal;
					$image->setAttribute('src', $srcVal);
				}
			}
			$className = $image->getAttribute('class');
			if (strstr($className, 'logo')) {
				continue;
			}
			$src = $image->getAttribute('src');
			if (!$src || strstr($src, 'res.wx.qq.com') || strstr($src, 'wx.qlogo.cn') || $src == 'http://mp.weixin.qq.com') {
				$image->outertext = '';
				continue;
			}
			$imageCounter++;
			if ($removeImage || in_array($imageCounter, $removeSpecifiedImages) || in_array($imageCounter - $imageTotal - 1, $removeSpecifiedImages)) {
				$image->outertext = '';
				continue;
			}
			switch ($platform) {
				case 'wechat':
					break;
				case 'zhihu':
					if (strstr($src, 'data:image')) {
						$image->outertext = '';
					}
					break;
				case 'toutiao':
					break;
			}
			$class = $image->getAttribute('class');
			if ($centeredImage) {
				$class .= ' aligncenter';
				$image->setAttribute('class', $class);
			}
			$type  = $image->getAttribute('data-type');
			$src = preg_replace('/^\/\//', 'http://', $src, 1);
			if (!$type || $type == 'other') {
				$type = 'jpeg';
			}
			switch ($platform) {
				case 'default':
					$tmpFile = beepress_pro_download_url($src, $postSrc);
					break;
				case 'wechat':
					$redirectUrl   = 'http://read.html5.qq.com/image?src=forum&q=4&r=0&imgflag=7&imageUrl=';
					$src = $redirectUrl . $src;
					$tmpFile = beepress_pro_download_url($src, $postSrc);
					break;
				default:
					$tmpFile = download_url($src);
					break;
			}
			if (!is_string($tmpFile)) {
				continue;
			}
			$prefixName = get_option('bp_image_name_prefix', 'beepress');
			$randomInt  = rand(0, 10);
			$fileName = $prefixName . $randomInt . '-' . time() . '.' . $type;
			$fileArr  = array(
				'name'     => $fileName,
				'tmp_name' => $tmpFile
			);

			// 下载图片
			if (get_option('bp_download_image', 'yes') == 'yes') {
				$id = @media_handle_sideload($fileArr, $postId);
				if (is_wp_error($id)) {
					continue;
				} else {
					$imageInfo = wp_get_attachment_image_src($id, 'full');
					if (!$imageInfo) {
						continue;
					}
					$imageSrc = $imageInfo[0];
					// 设置特色图片
					if ($setFeaturedImage && !$hasSetFeaturedImg) {
						switch ($platform) {
							case 'zhihu':
								$class = $image->getAttribute('class');
								if (strstr($class, 'TitleImage')) {
									@set_post_thumbnail($postId, $id);
									$hasSetFeaturedImg = true;
								}
								break;
							case 'toutiao':
								$className = $image->getAttribute('class');
								if (strstr($className, 'logo')) {
//									break;
								} else {
									@set_post_thumbnail($postId, $id);
									$hasSetFeaturedImg = true;
								}
								break;
							case 'jianshu':
								@set_post_thumbnail($postId, $id);
								$hasSetFeaturedImg = true;
								break;
						}
					}
					// 图片保存路径
					if (get_option('bp_image_path', 'abs') == 'rel') {
						$imageSrc = substr_replace($imageSrc, '', 0, strlen(site_url()));
					}
					$image->setAttribute('src', $imageSrc);
				}
			}
			$image->setAttribute('alt', $title);
			$image->setAttribute('title', $title);
		}
		$accountName = '原文始发于：<a target="_blank" href="' . $postSrc. '">' . $title . '</a>';
		switch ($platform) {
			case 'wechat':
				$accountName = '原文始发于微信公众号（' . esc_html($dom->find('#profileBt a', 0)->plaintext) .'）：<a target="_blank" href="' . $postSrc. '">' . $title . '</a>';
				$content     = $dom->find('#js_content', 0)->innertext;
				break;
			case 'zhihu':
				$content     = $dom->find('.Post-RichText', 0)->innertext;
				break;
			case 'jianshu':
				$content     = $dom->find('.show-content', 0)->innertext;
				break;
			case 'toutiao':
				$content     = $dom->find('#tt-content', 0)->innertext;
				break;
			case 'default':
				$rule = beepress_get_rule_setting($host);
				if ($rule) {
					$content     = $dom->find($rule['contentRule'], 0)->innertext;
				}
				break;
		}
		// 背景图片处理
		// 匹配背景图片地址
		preg_match_all("/background-image: url\(([^\)]*)\)/", $content, $backgroundImages);
		if (count($backgroundImages[1])) {
			$backgroundImages = array_unique($backgroundImages[1]);
		} else {
			$backgroundImages = array();
		}
		// 下载对应的图片
		$redirectUrl   = 'http://read.html5.qq.com/image?src=forum&q=4&r=0&imgflag=7&imageUrl=';
		foreach ($backgroundImages as $backgroundImage) {
			if (get_option('bp_download_image', 'yes') == 'yes') {
				switch ($platform) {
					case 'wechat':
						$backgroundImageSrc = str_replace('&quot;', '', $backgroundImage);
						$backgroundImageSrc = $redirectUrl . $backgroundImageSrc;
						$tmpFile       = beepress_pro_download_url($backgroundImageSrc, $postSrc);
						break;
					default:
						$backgroundImageSrc = $redirectUrl . str_replace('&quot;', '', $backgroundImage);
						$tmpFile       = download_url($backgroundImageSrc);
						break;
				}
				$backgroundImageSrc2 = $backgroundImageSrc;
				if (is_string($tmpFile)) {
					$prefixName = get_option('bp_image_name_prefix', 'beepress');
					$randomInt  = rand(0, 10);
					$fileName   = $prefixName . $randomInt .  '-' . time() . '.jpeg';
					$fileArr    = array(
						'name'     => $fileName,
						'tmp_name' => $tmpFile
					);
					$id = @media_handle_sideload($fileArr, $postId);
					if (is_wp_error($id)) {
						continue;
					} else {
						$backgroundImageInfo = wp_get_attachment_image_src($id, 'full');
						$backgroundImageSrc2 = $backgroundImageInfo[0];
					}
				}
			} else {
				$backgroundImageSrc2 = '';
			}
			if ($removeImage) {
				$backgroundImageSrc2 = '';
			}
			$content = str_replace($backgroundImage, "'{$backgroundImageSrc2}'", $content);
		}

		$removeHTMLTags = isset($_REQUEST['remove_html_tags']) ? $_REQUEST['remove_html_tags'] : '';
		$removeHTMLTags = array_map('trim', explode('#', $removeHTMLTags));
		foreach ($removeHTMLTags as $removeHTMLTag) {
			if ($removeHTMLTag) {
				$content = $beepressUtils->remove_specified_tags($content, $removeHTMLTag, false);
			}
		}

		$content = $beepressUtils->remove_useless_attrs($content);
		if ($keepCopyRight && $accountName) {
			$source = '<blockquote class="keep-source">' .
					  '<p>' . $accountName . '</p>' .
					  '</blockquote>';
			if (get_option('bp_copyright_position') == 'top') {
				$content = $source . $content;
			} else {
				$content .= $source;
			}
		}
		$content = '<div class="bpp-post-content">'.$content.'</div>';

		// 处理视屏和图片
		switch ($platform) {
			case 'wechat':
				$iframe =
						'<div class="bp-video">
                <div class="player">
                    <iframe class="bp-iframe" width="100%" src="$1" frameborder="0" allowfullscreen="true"></iframe>
                </div>';
				$iframe .= '</div>';

				$oldContent = $content;
				$content = preg_replace('/<iframe\s+.*?\s+src="(.*?)".*?<\/iframe>/', $iframe, $content);
				if (!$content) {
					$content = $oldContent;
				}
				$content = preg_replace('/src=\"(http:\/\/read\.html5\.qq\.com)([^\"])*\"/', '', $content);
				break;
			case 'zhihu':
				$content = preg_replace('/<noscript>(.*?)<\/noscript>/', "$1", $content);
				break;
		}
		$removeTags  = isset($_REQUEST['remove_tags']) ? $_REQUEST['remove_tags'] == 'yes' : false;
		if ($removeTags) {
			$content = str_replace('</p>', '<br>', $content);
			$content = strip_tags($content, '<br><img><video><iframe><code><a><audio><canvas><input>');
		}
		$removeStyle = isset($_REQUEST['remove_style']) ? $_REQUEST['remove_style'] == 'yes' : false;
		if ($removeStyle) {
			$content = preg_replace('/style=\"([^\"])*\"/', ' ', $content);
		}
		if ($platform != 'wechat') {
			$content = $beepressUtils->remove_script($content);
		}
		if ($syncpressPush) {
		} else {
			$removeOuterlink = isset($_REQUEST['remove_outerlink']) ? $_REQUEST['remove_outerlink'] : 'no';
		}
		if ($removeOuterlink != 'no') {
			switch ($removeOuterlink) {
				case 'all':
					$content = $beepressUtils->remove_link($content, false);
					break;
				case 'keepcontent':
					$content = $beepressUtils->remove_link($content, true);
					break;
			}
		}

		$removeBlank = isset($_REQUEST['remove_blank']) ? $_REQUEST['remove_blank'] == 'yes' : true;
		if ($removeBlank) {
			$content = $beepressUtils->content_filter_blank($content);
		}

		$syncpressPushSettings = get_option('syncpress_push_settings', array());
		$accountId = isset($_REQUEST['accountId']) ? $_REQUEST['accountId'] : null;

		$accountSetting = array();
		foreach ($syncpressPushSettings as $setting) {
			if (strtolower(trim($setting['account_id'])) == strtolower($accountId)) {
				$accountSetting = $setting;
				break;
			}
		}

		if ($syncpressPush) {
			$keywordsReplaceRule = isset($accountSetting['keywords_replace_rule']) ? $accountSetting['keywords_replace_rule'] : '';
		} else {
			$keywordsReplaceRule = isset($_REQUEST['keywords_replace_rule']) ? $_REQUEST['keywords_replace_rule'] : '';
		}
		$useKeywordsLib = isset($_REQUEST['use_keywords_lib']) ? $_REQUEST['use_keywords_lib'] == 'yes' : false;
		if ($useKeywordsLib) {
			$keywordsLib = get_option('bp_keywords_lib', '');
			$keywordsReplaceRule .= $keywordsLib;
		}

		if ($keywordsReplaceRule) {
			$ruleArr = explode("\n", $keywordsReplaceRule);
			$rules = array();
			foreach ($ruleArr as $rule) {
				$rule = explode('=', $rule);
				if (count($rule) == 2) {
					$rules[trim($rule[0])] = trim($rule[1]);
				}
			}
			$content = $beepressUtils->keywords_replace($content, $rules);
		}
		// 文章状态
		$postStatus = isset($_REQUEST['post_status']) && in_array($_REQUEST['post_status'], array('publish', 'pending', 'draft')) ? $_REQUEST['post_status'] : 'publish';
		// 文章发布时间
		$keepOriginalPostTime = get_option('bp_post_time', 'original_time') == 'original_time';
		$updatePost = array(
			'ID'           => $postId,
			'post_content' => trim($content),
			'post_status'  => $postStatus
		);
		if ($keepOriginalPostTime) {
			$updatePost['post_date'] = $_REQUEST['post_date'];
		}

		return @wp_update_post($updatePost);
	}

	function getPostTitle($dom = null, $platform = '', $host = null,  $content = '') {
		$title = '';
		if ($dom) {
			switch ($platform) {
				case 'wechat':
					preg_match('/(msg_title = ")([^\"]+)"/', $content, $matches);
					$title = $matches[2];
					break;
				case 'zhihu':
					$title = trim($dom->find('.Post-Title', 0)->plaintext);
					break;
				case 'jianshu':
					$title = trim($dom->find('.title', 0)->plaintext);
					break;
				case 'toutiao':
					preg_match("/(title: ')([^\']+)'/", $content, $matches);
					$title = $matches[2];
					break;
				case 'default':
					$rule = beepress_get_rule_setting($host);
					if ($rule) {
						$titleRule = $rule['titleRule'];
						$title = trim($dom->find($titleRule, 0)->plaintext);
					}
					break;
				default:
					$title = '';
			}

		}
		return $title;
	}

	function beepress_pro_download_url($url, $postSrc) {
		$url = str_replace('&amp;', '&', $url);
		$urlFileName = basename(parse_url($url, PHP_URL_PATH));
		$tmpfname = wp_tempnam($urlFileName);
		$response = wp_safe_remote_get($url, array(
			'timeout' => 300,
			'stream' => true,
			'filename' => $tmpfname,
			'user-agent' => 'Mozilla/5.0 (Linux; Android 6.0; 1503-M02 Build/MRA58K) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/37.0.0.0 Mobile MQQBrowser/6.2 TBS/036558 Safari/537.36 MicroMessenger/6.3.25.861 NetType/WIFI Language/zh_CN',
			'headers' => array(
				'referer' => $postSrc
			),
		));
		if ( is_wp_error( $response ) ) {
			unlink( $tmpfname );
			return $response;
		}

		if ( 200 != wp_remote_retrieve_response_code( $response ) ){
			unlink( $tmpfname );
			return new WP_Error( 'http_404', trim( wp_remote_retrieve_response_message( $response ) ) );
		}

		$content_md5 = wp_remote_retrieve_header( $response, 'content-md5' );
		if ( $content_md5 ) {
			$md5_check = verify_file_md5( $tmpfname, $content_md5 );
			if ( is_wp_error( $md5_check ) ) {
				unlink( $tmpfname );
				return $md5_check;
			}
		}

		return $tmpfname;
	}

	function beepress_get_rule_setting($host)
	{
		$ruleSettings = get_option('beepress_rule_settings', array());
		foreach ($ruleSettings as $setting) {
			if ($host == parse_url($setting['site'], PHP_URL_HOST)) {
				return $setting;
			}
		}
		return null;
	}
}
