<?php
/*
Plugin Name: 蜜蜂采集
Plugin URI: http://xingyue.artizen.me?source=wp
Description: 蜜蜂采集(BeePress) 是一款能够帮助你导入微信公众号文章、知乎专栏文章、简书文章、今日头条文章的插件(支持规则配置采集其他网站文章)，可以实现单篇或者批量导入、自动同步文章、采集指定公众号所有历史文章，支持将图片资源保存到本地，同时集成了强大的内容推荐功能。
Version: 6.1.9
Author: Bee
Author URI: http://xingyue.artizen.me?source=wp
License: GPL
*/

/**
 * 初始化
 */
define('BEEPRESS_VERSION', '6.1.9');
if(!class_exists('simple_html_dom_node')){
	require_once("simple_html_dom.php");
}
if(!class_exists('BeePressUtils')){
	require_once("beepress-utils.php");
}
$utils = new BeePressUtils();
$pass = $utils->check_license();

if (get_option('bp_close_xianjian', 'no') == 'yes' && $pass) {
} else {
	require_once "xianjian/xianjian.php";
}

/**
 * 后台入口
 */
if (is_admin()) {
	add_action('admin_menu', 'beepress_pro_admin_menu');
	add_action('admin_menu', 'beepress_pro_option_menu');
}
if (!function_exists('beepress_pro_admin_menu')) {
	function beepress_pro_admin_menu() {
		add_menu_page('BeePress Pro', '蜜蜂工具箱', 'publish_posts', 'beepress_pro', 'beepress_pro_request_page', '');
	}
}
require_once( ABSPATH . 'wp-admin/includes/file.php' );
require_once( ABSPATH . 'wp-admin/includes/image.php' );
require_once( ABSPATH . 'wp-admin/includes/media.php' );
require_once( ABSPATH . 'wp-admin/includes/post.php' );


require_once 'beepress-pro.php';
