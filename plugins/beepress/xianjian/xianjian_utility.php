<?php 

if (!defined('ABSPATH')) exit;

include_once('xianjian_js.php');
include_once('xianjian_consts.php');

function beepress_xianjian_is_rec_shown() {
	global $beepress_xianjian_host;
	$site_id_key="paradigm_site_id";
	$site_id=get_option($site_id_key);
	if ($site_id == "") {
		return false;
	}
	$remote_url = $beepress_xianjian_host.'/business/plug/scene/count?plugSiteId='.$site_id;
	$response = wp_remote_get($remote_url);
	$response_body = wp_remote_retrieve_body($response);
	$response_obj = json_decode($response_body,true);
	try {
		$code = isset($response_obj['code']) ? $response_obj['code'] : 0;
		if ($code == 200) {
			$data = isset($response_obj['data']) ? $response_obj['data'] : null;
			if (!$data) {
				return true;
			}
			$scene_count = isset($data['sceneCount']) ? $data['sceneCount'] : 0;
			if ($scene_count <= 0) {
				return false;
			} else {
				return true;
			}
		} else {
			return true;
		}
	} catch (Exception $e) {
		return true;
	}
}

function beepress_xianjian_random_str($length) {
	$characters = "0123456789abcdefghijklmnopqrstuvwxyz"; 
	$result = "";
	for( $i=0; $i<$length; $i++) {
		$result .= $characters[rand(0,strlen($characters)-1)];
	} 
	return $result;
}

function beepress_xianjian_check_night_time() {
	$timezone_out = date_default_timezone_get();
	date_default_timezone_set('PRC');
	$china_time = date('H');
	date_default_timezone_set($timezone_out);
	if (strlen($china_time) > 1) {
		$tens = $china_time[0];
		$units = $china_time[1];
		if ($tens == 0) {
			if ($units >= 0 && $units < 6) {
				return true;
			} else {
				return false;
			}
		} else {
			return false;
		}
	} else {
		return false;
	}
}

function beepress_xianjian_check_render_config() {
	global $beepress_xianjian_config_key,$beepress_xianjian_config_loaded;
	if ($beepress_xianjian_config_loaded) {
		return;
	}
	$config_str = get_option($beepress_xianjian_config_key);
	$total_config = json_decode($config_str,true);

	global $beepress_xianjian_access_token;
	if (is_array($total_config) && !empty($total_config)) {

		foreach ($total_config as $config) {
			if (strlen($beepress_xianjian_access_token) == 0) {
				$beepress_xianjian_access_token = isset($config['accessToken']) ? $config['accessToken'] : "";
				if (strlen($beepress_xianjian_access_token) == 0) {
					$beepress_xianjian_access_token = isset($config['acessToken']) ? $config['acessToken'] : "";
				}
			}
			$recom_location = isset($config['recomLocation']) ? $config['recomLocation'] : "";
			if (strcmp($recom_location, 'TXT') == 0) {
				beepress_xianjian_content_page($config);
			} elseif (strcmp($recom_location, 'HOME') == 0) {
				beepress_xianjian_home_page($config);
			}
		}
	}
	$beepress_xianjian_config_loaded = true;
}

function beepress_xianjian_content_page($content_config) {
	$content_count = 0;
	global $beepress_xianjian_content_side_id_key;
	$page_location = isset($content_config['pageLocation']) ? $content_config['pageLocation'] : "";
	if (strcmp($page_location, 'A_B') == 0) {
		global $beepress_xianjian_render_content_append_id,$beepress_xianjian_is_theme;
		beepress_xianjian_set_render_js_code($beepress_xianjian_render_content_append_id,$content_config);
		if (!$beepress_xianjian_is_theme) {
			add_filter('the_content','beepress_xianjian_content_append_rec');	
		}
	} elseif (strcmp($page_location, 'C_T') == 0) {
		global $beepress_xianjian_render_content_comment_id;
		beepress_xianjian_set_render_js_code($beepress_xianjian_render_content_comment_id,$content_config);
		add_action('comments_array','beepress_xianjian_content_comment_rec');
	} elseif (strcmp($page_location, 'C_B') == 0) {
		global $beepress_xianjian_render_content_comment_bottom_id;
		beepress_xianjian_set_render_js_code($beepress_xianjian_render_content_comment_bottom_id,$content_config);
		add_action('comment_form_after','beepress_xianjian_content_comment_bottom_rec');
	} elseif (strcmp($page_location, 'S') == 0) {
		global $beepress_xianjian_content_side,$beepress_xianjian_render_content_side_id;
		beepress_xianjian_set_side_render_js_code($beepress_xianjian_render_content_side_id,$content_config);
		$beepress_xianjian_content_side = true;
		$content_count ++;
	}
	update_option($beepress_xianjian_content_side_id_key,$content_count);
}

function beepress_xianjian_content_append_rec($content) {
	if (is_single()) {
		global $beepress_xianjian_js_code_map,$beepress_xianjian_render_content_append_id;
		$content .= isset($beepress_xianjian_js_code_map[$beepress_xianjian_render_content_append_id]) ? $beepress_xianjian_js_code_map[$beepress_xianjian_render_content_append_id] : "";
	}
	return $content;
}

function beepress_xianjian_content_comment_rec($comments) {
	if (is_single()) {
		global $beepress_xianjian_js_code_map,$beepress_xianjian_render_content_comment_id;
		echo '<div style="margin-top:15px">';
		echo isset($beepress_xianjian_js_code_map[$beepress_xianjian_render_content_comment_id]) ? $beepress_xianjian_js_code_map[$beepress_xianjian_render_content_comment_id] : "";
		echo '</div>';
	}
	return $comments;
}

function beepress_xianjian_content_comment_bottom_rec($post_id) {
	if (is_single()) {
		global $beepress_xianjian_js_code_map,$beepress_xianjian_render_content_comment_bottom_id;
		echo isset($beepress_xianjian_js_code_map[$beepress_xianjian_render_content_comment_bottom_id]) ? $beepress_xianjian_js_code_map[$beepress_xianjian_render_content_comment_bottom_id] : "";
	}
}

function beepress_xianjian_home_page($home_config) {
	$page_location = isset($home_config['pageLocation']) ? $home_config['pageLocation'] : "";
	if (strcmp($page_location, 'A_L_B') == 0) {
		global $beepress_xianjian_home_bottom,$beepress_xianjian_render_home_bottom_id;
		beepress_xianjian_set_render_js_code($beepress_xianjian_render_home_bottom_id,$home_config);
		$beepress_xianjian_home_bottom = true;
	}
	if (strcmp($page_location, 'S') == 0) {
		global $beepress_xianjian_home_side,$beepress_xianjian_render_home_side_id;
		beepress_xianjian_set_home_side_render_js_code($beepress_xianjian_render_home_side_id,$home_config);
		$beepress_xianjian_home_side = true;
	}
}

function beepress_xianjian_upload_material($post,$update_last_upload_id,$access_token) {

	global $wpdb;

	$last_upload_id_key = 'last_upload_id_'.$access_token;
	$object_id = isset($post['ID']) ? $post['ID'] : 0;
	$item_id = $object_id;

	$title = isset($post['post_title']) ? $post['post_title'] : "";
	$title = preg_replace("/<.*?>/", '', $title);

	$post_date = isset($post['post_date']) ? $post['post_date'] : "";
	$publish_time = strtotime($post_date) * 1000 - 8 * 3600 * 1000;


	$content = isset($post['post_content']) ? $post['post_content'] : "";
	$picture_url = "";
	$thumbnail = $wpdb->get_row("SELECT meta_value FROM `".$wpdb->prefix."postmeta` WHERE post_id=".$object_id." AND meta_key='_thumbnail_id'",ARRAY_A);
	if ($thumbnail != null) {
		$thumbnail_id = isset($thumbnail['meta_value']) ? $thumbnail['meta_value'] : 0;
		$thumbnail_url = $wpdb->get_row("SELECT guid FROM `".$wpdb->prefix."posts` WHERE ID=".$thumbnail_id,ARRAY_A);
		if ($thumbnail_url != null) {
			$picture_url = isset($thumbnail_url['guid']) ? $thumbnail_url['guid'] : "";
		}
	}
	if (strlen($picture_url) == 0) {
		$match_result = preg_match_all("/<.*?>/", $content, $matches, PREG_SET_ORDER);
		if ($match_result) {
			foreach ($matches as $value) {
				$img_label = $value[0];
				$prefix_str = substr($img_label, 0, 4);
				if (strcmp($prefix_str, "<img") === 0) {
					$pre_operation_img_arr = explode('src="', $img_label);
					if (count($pre_operation_img_arr) > 1) {
						$operation_img_str = $pre_operation_img_arr[1];
						$img_arr = explode('"', $operation_img_str);
						if (count($img_arr) > 0) {
							$img_url = $img_arr[0];
							if (strlen($img_url) > 4) {
								if (strcmp(substr($img_url, 0, 4), "http") === 0) {
									if ($picture_url === "") {
										$picture_url = $img_url;
									} else {
										$picture_url .= ",".$img_url;
									}
								}
							}
						}
					}
				}
			}
		}	
	}
	$content = preg_replace('/[\n\r\t]/', '', $content);
	$content = preg_replace("/<.*?>/", '', $content);
	$content = addslashes($content);


	if (strlen($content) < 5) {
		if ($update_last_upload_id == -2) {
		
		} elseif ($update_last_upload_id == -1) {
			$last_upload_id = $object_id;
			update_option($last_upload_id_key,$last_upload_id);
		} else {
			update_option($last_upload_id_key,$update_last_upload_id);
		}
		return;
	}

	$real_post = get_post($object_id);
	$url = get_permalink($real_post);
	if (!$url) {
		if ($update_last_upload_id == -2) {
		
		} elseif ($update_last_upload_id == -1) {
			$last_upload_id = $object_id;
			update_option($last_upload_id_key,$last_upload_id);
		} else {
			update_option($last_upload_id_key,$update_last_upload_id);
		}
		return;
	}


	$post_author_id = isset($post['post_author']) ? $post['post_author'] : 0;
	$user = $wpdb->get_row("SELECT display_name FROM `".$wpdb->prefix."users` WHERE ID=".$post_author_id, ARRAY_A);
	$publisher_id = isset($user['display_name']) ? $user['display_name'] : "";
	
	$term_relationships = $wpdb->get_results("SELECT term_taxonomy_id FROM `".$wpdb->prefix."term_relationships` WHERE object_id=".$object_id,ARRAY_A);
	$category_id = '';
	$tag = '';
	foreach ($term_relationships as $term_relationship) {
		$term_taxonomy_id = isset($term_relationship['term_taxonomy_id']) ? $term_relationship['term_taxonomy_id'] : 0;
		$term_taxonomies = $wpdb->get_results("SELECT term_id,taxonomy FROM `".$wpdb->prefix."term_taxonomy` WHERE term_taxonomy_id=".$term_taxonomy_id,ARRAY_A);
		$categories = array();
		$tags = array();
		foreach ($term_taxonomies as $term_taxonomy) {
			$term_id = isset($term_taxonomy['term_id']) ? $term_taxonomy['term_id'] : 0;
			$taxonomy = isset($term_taxonomy['taxonomy']) ? $term_taxonomy['taxonomy'] : "";
			$terms = $wpdb->get_results("SELECT name FROM `".$wpdb->prefix."terms` WHERE term_id=".$term_id,ARRAY_A);
			foreach ($terms as $term) {
				$name = isset($term['name']) ? $term['name'] : "";
				if (strcmp($taxonomy, 'category') == 0) {
					array_push($categories, $name);
				} elseif (strcmp($taxonomy, 'post_tag') == 0) {
					array_push($tags, $name);
				}
			}
		}
		foreach ($categories as $category) {
			$category_id .= rtrim($category.',');
		}
		foreach ($tags as $keyword) {
			$tag .= rtrim($keyword.',');
		}
	}
	$category_id =  chop($category_id,',');
	$tag = chop($tag,',');

	if (strlen($category_id) > 0 || strlen($tag) > 0) {
		
	} else {
		if ($update_last_upload_id == -2) {
		
		} elseif ($update_last_upload_id == -1) {
			$last_upload_id = $object_id;
			update_option($last_upload_id_key,$last_upload_id);
		} else {
			update_option($last_upload_id_key,$update_last_upload_id);
		}
		return;
	}

	$body_arr = array( 
		'itemId' => $item_id, 
		'title' => $title,
		'content' => $content,
		'publisherId' => $publisher_id,
		'categoryId' => $category_id,
		'url' => $url,
		'tag' => $tag,
		'publishTime' => $publish_time,
		'coverUrl' => $picture_url
	);
	$body = "[".json_encode((object)$body_arr)."]";
	$request_header = array(
		'Content-Type' => 'application/json',
		'charset' => 'utf-8'
	);
	$args = array(
		'headers' => $request_header,
		'body' => $body,
		'timeout' => '8'
	);

	global $beepress_xianjian_host;
	$remote_url = $beepress_xianjian_host.'/business/items?accessToken='.$access_token.'&source=1';
	$response = wp_remote_post($remote_url,$args);
	$response_body = wp_remote_retrieve_body($response);
	$response_obj = json_decode($response_body,true);
	if ($update_last_upload_id == -2) {
		
	} elseif ($update_last_upload_id == -1) {
		$last_upload_id = $object_id;
		update_option($last_upload_id_key,$last_upload_id);
	} else {
		update_option($last_upload_id_key,$update_last_upload_id);
	}
}
?>