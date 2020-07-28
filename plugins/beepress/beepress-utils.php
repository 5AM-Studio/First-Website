<?php
/**
 * Class BeePressUtils
 * 作者：Bee
 * 邮箱：bsn.huang@gmail.com
 * 版权：本代码所有权归作者所有，未经授权，不得使用
 */

class BeePressUtils
{
	// 过滤空白字符，包括空行
	public function content_filter_blank($content = '')
	{
		$content = preg_replace('/<br[^>|.]*>/', '', $content);
		$content = preg_replace('/<p>[\s]*<\/p>/', '', $content);
		$content = preg_replace('/<section>[\s]*<\/section>/', '', $content);
		$content = preg_replace('/&nbsp;/','', $content);
		return $content;
	}

	// 去除一些无效属性
	public function remove_useless_attrs($content = '')
	{
		$content = preg_replace('/data\-([a-zA-Z0-9\-])+\=\"[^\"]*\"/i', '', $content);
//		$content = preg_replace('/class\=\"[^\"]*\"/i', '', $content);
//		$content = preg_replace('/id\=\"[^\"]*\"/i', '', $content);
		$content = preg_replace('/powered\-by\=\"[^\"]*\"/', '', $content);
		return $content;
	}

	// 过滤脚本
	public function remove_script($content = '') {
		$content = preg_replace('/<script[\s\S]*?<\/script>/i', '', $content);
		$content = preg_replace('/<noscript[\s\S]*?<\/noscript>/i', '', $content);
		return $content;
	}

	// 过滤链接
	public function remove_link($content = '', $keepText = false)
	{
		if ($keepText) {
			$content = preg_replace('/<a[^>]*>(.*?)<\/a>/i', '$1', $content);
			return $content;
		} else {
			$content = preg_replace('/<a[^>]*>(.*?)<\/a>/i', '', $content);
			return $content;
		}
	}

	// 关键词替换
	public function keywords_replace($content = '', $rules)
	{
		$content = str_replace(array_keys($rules), array_values($rules), $content);
		return $content;
	}

	public function format_url($baseurl, $srcurl)
	{
		$srcinfo = parse_url($srcurl);
		if(isset($srcinfo['scheme'])) {
			return $srcurl;
		}
		$baseinfo = parse_url($baseurl);
		$url = $baseinfo['scheme'].'://'.$baseinfo['host'];
		if(substr($srcinfo['path'], 0, 1) == '/') {
			$path = $srcinfo['path'];
		}else{
			$path = dirname($baseinfo['path']).'/'.$srcinfo['path'];
		}
		$rst = array();
		$path_array = explode('/', $path);
		if(!$path_array[0]) {
			$rst[] = '';
		}
		foreach ($path_array AS $key => $dir) {
			if ($dir == '..') {
				if (end($rst) == '..') {
					$rst[] = '..';
				}elseif(!array_pop($rst)) {
					$rst[] = '..';
				}
			}elseif($dir && $dir != '.') {
				$rst[] = $dir;
			}
		}
		if(!end($path_array)) {
			$rst[] = '';
		}
		$url .= implode('/', $rst);
		return str_replace('\\', '/', $url);
	}

	public function remove_specified_tags($content = '', $tag = null, $delContent = false)
	{
		if ($delContent) {
			$content = preg_replace("/(<{$tag}.*?>[\s\S]*?<\/{$tag}>)/",'',$content);
		} else {
			$content = preg_replace("/<{$tag}[^>]*>/is", '', $content);
			$content = preg_replace("/<\/{$tag}>/is", '', $content);
		}
		return $content;
	}

	public function check_license()
	{
		$host                 = parse_url(home_url(), PHP_URL_HOST);
		$host2                = str_replace('www.', '', $host);
		$homeURL              = preg_replace('/(http:\/\/|https:\/\/)/', '', home_url());
		$licenseCode          = get_option('bp_license_code');
		$salt                 = 'FXqqh4gVu27Rd696';
		$requestLicenseCode   = md5($homeURL . $salt);
		$requestLicenseCode2  = md5($host . $salt);
		$requestLicenseCode3  = md5($host2 . $salt);
		$pass                 = $licenseCode && ($licenseCode == $requestLicenseCode3 || $licenseCode == $requestLicenseCode2 || $licenseCode == $requestLicenseCode);
		return $pass;
	}

	public function is_validate()
	{
		return get_option('is_validate', false) == true;
	}
}
