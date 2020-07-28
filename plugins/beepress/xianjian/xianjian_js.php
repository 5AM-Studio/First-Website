<?php
if (!defined('ABSPATH')) {
    exit;
}

include_once('xianjian_consts.php');
function beepress_xianjian_get_js_code($render_div_id, $config)
{
    global $beepress_xianjian_js_code_map,$beepress_xianjian_host,$beepress_xianjian_sdk_obj;
    $beepress_xianjian_js_code ="<div id='".$render_div_id."_".$config['sceneId']."'><script>
 function load4pScript(a){if(window._paradigm_plug_sdkV3_loaded){a();return}else{if(!window._paradigm_plug_loadFinishCallBack){window._paradigm_plug_loadFinishCallBack=[];window._paradigm_plug_loadFinishCallBack.push(a)}else{window._paradigm_plug_loadFinishCallBack.push(a);return}}var b=document.createElement('script');b.charset='utf-8';
 b.src='".$beepress_xianjian_host."/sdk/js/ParadigmSDK_v3.js';document.getElementsByTagName('head')[0].appendChild(b);b.onload=function(){for(var c=0;c<window._paradigm_plug_loadFinishCallBack.length;c++){window._paradigm_plug_loadFinishCallBack[c]()}}};load4pScript(function(){window._paradigm_plug_sdkV3_loaded=true;".$beepress_xianjian_sdk_obj.".init('".$config['clientToken']."',{isDisableArticleFetch:true});".$beepress_xianjian_sdk_obj.".renderArticle('".$render_div_id."_".$config['sceneId']."',".$config['itemSetId'].",".$config['sceneId'].")});
   </script></div>";
    return  $beepress_xianjian_js_code;
}
function beepress_xianjian_set_render_js_code($render_div_id, $config)
{
    global $beepress_xianjian_js_code_map,$beepress_xianjian_host,$beepress_xianjian_sdk_obj;
    $scene_id = isset($config['sceneId']) ? $config['sceneId'] : "";
    $item_set_id = isset($config['itemSetId']) ? $config['itemSetId'] : "";
    $client_token = isset($config['clientToken']) ? $config['clientToken'] : "";
    $beepress_xianjian_js_code = beepress_xianjian_get_js_code($render_div_id, $config);
    $pre_js_code = isset($beepress_xianjian_js_code_map[$render_div_id]) ? $beepress_xianjian_js_code_map[$render_div_id] : "";
    $total_js_code = $pre_js_code.$beepress_xianjian_js_code;
    $beepress_xianjian_js_code_map[$render_div_id] = $total_js_code;
}

function beepress_xianjian_set_side_render_js_code($render_div_id, $config)
{
    global $beepress_xianjian_side_js_code_map,$beepress_xianjian_host,$beepress_xianjian_sdk_obj;
    $scene_id = isset($config['sceneId']) ? $config['sceneId'] : "";
    $item_set_id = isset($config['itemSetId']) ? $config['itemSetId'] : "";
    $client_token = isset($config['clientToken']) ? $config['clientToken'] : "";
    $beepress_xianjian_js_code = beepress_xianjian_get_js_code($render_div_id, $config);
    $code_dic = array();
    $code_dic['used'] = false;
    $code_dic['code'] = $beepress_xianjian_js_code;
    $code_dic['title'] = isset($config['recomTitle']) ? $config['recomTitle'] : "";
    $beepress_xianjian_side_js_code_map[$scene_id] = $code_dic;
}

function beepress_xianjian_set_home_side_render_js_code($render_div_id, $config)
{
    global $beepress_xianjian_home_side_js_code_map,$beepress_xianjian_host,$beepress_xianjian_sdk_obj;
    $scene_id = isset($config['sceneId']) ? $config['sceneId'] : "";
    $item_set_id = isset($config['itemSetId']) ? $config['itemSetId'] : "";
    $client_token = isset($config['clientToken']) ? $config['clientToken'] : "";
    $beepress_xianjian_js_code = beepress_xianjian_get_js_code($render_div_id, $config);
    $code_dic = array();
    $code_dic['used'] = false;
    $code_dic['code'] = $beepress_xianjian_js_code;
    $code_dic['title'] = isset($config['recomTitle']) ? $config['recomTitle'] : "";
    $beepress_xianjian_home_side_js_code_map[$scene_id] = $code_dic;
}

function insert_beepress_xianjian_js($args, $render_div_id)
{
    global $beepress_xianjian_js_code_map;
    extract($args);
    echo $before_widget;
    echo $before_title . __('先荐', 'beepress_xianjian') . $after_title;
    echo isset($beepress_xianjian_js_code_map[$render_div_id]) ? $beepress_xianjian_js_code_map[$render_div_id] : "";
    echo $after_widget;
}

function insert_beepress_xianjian_side_js($args, $render_div_id)
{
    global $beepress_xianjian_side_js_code_map;
    $current_key = null;
    $current_arr = array();
    if (is_array($beepress_xianjian_side_js_code_map) && !empty($beepress_xianjian_side_js_code_map)) {
        foreach ($beepress_xianjian_side_js_code_map as $key => $code_dic) {
            $used = isset($code_dic['used']) ? $code_dic['used'] : false;
            if ($used) {
            } else {
                $current_key = $key;
                $current_arr = $code_dic;
                break;
            }
        }
    } else {
        return;
    }
    if (empty($current_key)) {
        return;
    }
    $title = isset($current_arr['title']) ? $current_arr['title']:"";
    $code = isset($current_arr['code']) ? $current_arr['code']:"";
    $current_arr['used'] = true;
    $beepress_xianjian_side_js_code_map[$current_key] = $current_arr;
    extract($args);
    echo $before_widget;
    echo $before_title . __($title, 'beepress_xianjian') . $after_title;
    echo $code;
    echo $after_widget;
}

function insert_beepress_xianjian_home_side_js($args, $render_div_id)
{
    global $beepress_xianjian_home_side_js_code_map;
    $current_key = null;
    $current_arr = array();
    if (is_array($beepress_xianjian_home_side_js_code_map) && !empty($beepress_xianjian_home_side_js_code_map)) {
        foreach ($beepress_xianjian_home_side_js_code_map as $key => $code_dic) {
            $used = isset($code_dic['used']) ? $code_dic['used'] : false;
            if ($used) {
            } else {
                $current_key = $key;
                $current_arr = $code_dic;
                break;
            }
        }
    } else {
        return;
    }
    if (empty($current_key)) {
        return;
    }
    $title = isset($current_arr['title']) ? $current_arr['title']:"";
    $code = isset($current_arr['code']) ? $current_arr['code'] : "";
    $current_arr['used'] = true;
    $beepress_xianjian_home_side_js_code_map[$current_key] = $current_arr;
    extract($args);
    echo $before_widget;
    echo $before_title . __($title, 'beepress_xianjian') . $after_title;
    echo $code;
    echo $after_widget;
}
?>