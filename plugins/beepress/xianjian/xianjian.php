<?php

if (!defined('ABSPATH')) {
    exit;
}

include_once('xianjian_consts.php');
include_once('xianjian_utility.php');
include_once('xianjian_item.php');
include_once('xianjian_js.php');
include_once('xianjian_token.php');

add_action('plugins_loaded', 'beepress_xianjian_plugin_setup');
add_action('admin_menu', 'beepress_xianjian_rec_menu');

add_filter('wp_enqueue_script', 'beepress_xianjian_script_loader_src', 20, 2);
function beepress_xianjian_script_loader_src($src, $handle)
{
    return preg_replace('/^(http|https):/', '', $src);
}

add_filter('wp_enqueue_style', 'beepress_xianjian_style_loader_src', 20, 2);
function beepress_xianjian_style_loader_src($src, $handle)
{
    return preg_replace('/^(http|https):/', '', $src);
}
add_action('admin_enqueue_scripts', 'paradigm_custom_scripts');
add_filter('plugin_action_links_xianjian/xianjian.php', 'beepress_xianjianLinks');

function beepress_xianjian_plugin_setup()
{
    beepress_xianjian_check_render_config(false);
    wp_register_sidebar_widget('beepress_xianjian_home_bottom', '先荐推荐栏-首页底部栏', 'widget_beepress_xianjian_home_bottom');
    wp_register_sidebar_widget('beepress_xianjian_home_side_1', '先荐推荐栏-首页侧边栏1', 'widget_beepress_xianjian_home_side_1');
    wp_register_sidebar_widget('beepress_xianjian_home_side_2', '先荐推荐栏-首页侧边栏2', 'widget_beepress_xianjian_home_side_2');
    wp_register_sidebar_widget('beepress_xianjian_content_side_1', '先荐推荐栏-正文页侧边栏1', 'widget_beepress_xianjian_content_side_1');
    wp_register_sidebar_widget('beepress_xianjian_content_side_2', '先荐推荐栏-正文页侧边栏2', 'widget_beepress_xianjian_content_side_2');
    wp_register_sidebar_widget('beepress_xianjian_content_side_3', '先荐推荐栏-正文页侧边栏3', 'widget_beepress_xianjian_content_side_3');
}

function beepress_xianjian_rec_menu()
{
    add_submenu_page('plugins.php', __('先荐选项'), __('先荐-猜你喜欢'), 'manage_options', 'beepress_xianjian_rec_options', 'beepress_xianjian_rec_options');
    add_submenu_page('plugins.php', __('先荐选项1'), __('先荐-猜你喜欢1'), 'manage_options', 'beepress_xianjian_rec_options1', 'beepress_xianjian_rec_options1');
    add_submenu_page('plugins.php', __('先荐选项2'), __('先荐-猜你喜欢2'), 'manage_options', 'beepress_xianjian_rec_options2', 'beepress_xianjian_rec_options2');
    add_submenu_page('plugins.php', __('先荐选项3'), __('先荐-猜你喜欢3'), 'manage_options', 'beepress_xianjian_rec_options3', 'beepress_xianjian_rec_options3');
    add_submenu_page('plugins.php', __('先荐选项4'), __('先荐-猜你喜欢4'), 'manage_options', 'beepress_xianjian_rec_options4', 'beepress_xianjian_rec_options4');
}

function beepress_xianjianLinks($links)
{
    $mylinks = array('<a href="plugins.php?page=beepress_xianjian_rec_options">设置</a>');
    return array_merge($links, $mylinks);
}

function beepress_xianjian_rec_options()
{
    include 'config/sceneList.php';
}
function beepress_xianjian_rec_options1()
{
    include 'config/styleConfig.php';
}
function beepress_xianjian_rec_options2()
{
    include 'config/report.php';
}
function beepress_xianjian_rec_options3()
{
    include 'config/mateManege.php';
}
function beepress_xianjian_rec_options4()
{
    include 'config/brower.php';
}

function widget_beepress_xianjian_home_bottom($args)
{
    if (is_front_page()) {
        global $beepress_xianjian_home_bottom,$beepress_xianjian_render_home_bottom_id;
        if ($beepress_xianjian_home_bottom) {
            insert_beepress_xianjian_js($args, $beepress_xianjian_render_home_bottom_id);
        }
    }
}

function widget_beepress_xianjian_home_side_1($args)
{
    if (is_front_page()) {
        global $beepress_xianjian_home_side,$beepress_xianjian_render_home_side_id;
        if ($beepress_xianjian_home_side) {
            insert_beepress_xianjian_home_side_js($args, $beepress_xianjian_render_home_side_id);
        }
    }
}

function widget_beepress_xianjian_home_side_2($args)
{
    if (is_front_page()) {
        global $beepress_xianjian_home_side,$beepress_xianjian_render_home_side_id;
        if ($beepress_xianjian_home_side) {
            insert_beepress_xianjian_home_side_js($args, $beepress_xianjian_render_home_side_id);
        }
    }
}

function widget_beepress_xianjian_content_side_1($args)
{
    if (is_single()) {
        global $beepress_xianjian_content_side,$beepress_xianjian_render_content_side_id;
        if ($beepress_xianjian_content_side) {
            insert_beepress_xianjian_side_js($args, $beepress_xianjian_render_content_side_id);
        }
    }
}

function widget_beepress_xianjian_content_side_2($args)
{
    if (is_single()) {
        global $beepress_xianjian_content_side,$beepress_xianjian_render_content_side_id;
        if ($beepress_xianjian_content_side) {
            insert_beepress_xianjian_side_js($args, $beepress_xianjian_render_content_side_id);
        }
    }
}

function widget_beepress_xianjian_content_side_3($args)
{
    if (is_single()) {
        global $beepress_xianjian_content_side,$beepress_xianjian_render_content_side_id;
        if ($beepress_xianjian_content_side) {
            insert_beepress_xianjian_side_js($args, $beepress_xianjian_render_content_side_id);
        }
    }
}

function paradigm_custom_scripts()
{
    global $beepress_xianjian_version,$beepress_xianjian_channel;
    if ($beepress_xianjian_channel=='plugin_official_beepress') {
        wp_enqueue_style('paradigm_plugin_beer', plugins_url('config/static/beerPress.css', __FILE__), array(), $beepress_xianjian_version);
    }
    wp_enqueue_style('paradigm_element', plugins_url('config/static/element-theme/index.css', __FILE__), array(), $beepress_xianjian_version);
    wp_enqueue_style('paradigm_plugin', plugins_url('config/static/plugin.css', __FILE__), array(), $beepress_xianjian_version);
    wp_enqueue_script('paradigm_vue', plugins_url('config/static/vue.js', __FILE__), array(), $beepress_xianjian_version);
    wp_enqueue_script('paradigm_elementjs', plugins_url('config/static/element-ui/index.js', __FILE__), array(), $beepress_xianjian_version);
    wp_enqueue_script('paradigm_axios', plugins_url('config/api/axios.js', __FILE__), array(), $beepress_xianjian_version);
    wp_enqueue_script('paradigm_api', plugins_url('config/api/api.js', __FILE__), array(), $beepress_xianjian_version);
    wp_enqueue_script('paradigm_util', plugins_url('config/util/util.js', __FILE__), array(), $beepress_xianjian_version);
    wp_enqueue_script('paradigm_echarts', plugins_url('config/static/echarts.js', __FILE__), array(), $beepress_xianjian_version);
}
