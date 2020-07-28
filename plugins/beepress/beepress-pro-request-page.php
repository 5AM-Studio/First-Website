<?php include_once('beepress-utils.php');?>
<div class="container-fluid">
	<?php
	$utils = new BeePressUtils();
	$pass = $utils->check_license();
	$isValidate = $utils->is_validate();
	?>
	<div class="page-header">
		<h3>采集 <small id="auth" class=""></small></h3>
		<a href="<?php echo admin_url();?>admin.php?page=beepress_pro_option">常规配置</a> ｜
		<?php if(!($pass || $isValidate)):?><a style="color: red;font-weight: bolder" href="<?php echo admin_url();?>admin.php?page=beepress_pro_option">免费验证</a> ｜<?php endif;?>
		<a href="<?php echo admin_url();?>admin.php?page=beepress_xianjian_rec_options">内容推荐设置</a> ｜
		<a href="<?php echo admin_url();?>admin.php?page=beepress_pro_option">自动同步</a> ｜
		<a target="_blank" href="http://xingyue.artizen.me/?p=1959" style="color: red;font-weight: bolder">公众号文章助手</a> ｜
		<a href="<?php echo admin_url();?>admin.php?page=beepress_pro_option">关于&帮助&用户协议</a> ｜
		<a target="_blank" href="http://xingyue.artizen.me/beepresspro?m=bpp&s=<?php echo home_url();?>">官网</a> ｜
		<a href="<?php echo admin_url();?>plugins.php">当前版本：<?php echo BEEPRESS_VERSION;?></a>
	</div>
	<input type="text" hidden id="request_url" value="<?php echo admin_url( 'admin-ajax.php' );?>">
	<input type="text" hidden id="home_url" value="<?php echo home_url();?>">
	<table class="form-table">
		<tr valign="top">
			<th scope="row">采集规则</th>
			<td>
				<p>现支持指定站点采集规则配置，可满足大多数网站的采集需求，请前往<a href="<?php echo admin_url();?>admin.php?page=beepress_pro_option">配置页面</a>进行配置</p>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row">文章链接(可批量)</th>
			<td>
				<textarea cols="100" rows="10" id="post-urls" name="urls" placeholder="在此处输入文章链接，每行一条链接，目前支持微信公众号、知乎专栏文章、简书文章、今日头条文章导入，其他平台请到配置面板进行配置规则"></textarea>
				<br>通过文本文件上传，每行一条链接 <input type="file" name="urlfile">
			</td>
		</tr>
		<tr valign="top">
			<th scope="row">自定标题</th>
			<td>
				<input style="width:450px" placeholder="默认为原文标题" type="text" name="custom_title" />
			</td>
		</tr>
		<tr valign="top">
			<th scope="row">选择作者</th>
			<?php
			// 获取所有用户
			$users = get_users(array(
				'fields' => array('ID', 'user_nicename', 'display_name')
			));
			// 当前登录用户
			$currentUserId = get_current_user_id();
			?>
			<td>
				<select class="custom-select post-user" name="post-user">
					<?php foreach ($users as $user):?>
						<option value="<?php echo $user->ID;?>" <?php if($user->ID == $currentUserId) echo 'selected'; ?> ><?php echo $user->user_nicename . '(' . $user->display_name . ')';?></option>
					<?php endforeach;?>
				</select>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row">文章状态</th>
			<td>
				<?php
				$bp_post_status = get_option('bp_post_status', 'publish');
				?>
				<input type="radio" <?php if($bp_post_status == 'publish') echo 'checked';?> value="publish" name="post_status"> 直接发布
				<input type="radio" <?php if($bp_post_status == 'pending') echo 'checked';?> value="pending" name="post_status"> 待审核
				<input type="radio" <?php if($bp_post_status == 'draft') echo 'checked';?> value="draft" name="post_status"> 草稿
			</td>
		</tr>
		<tr valign="top">
			<th scope="row">跳过重复的文章</th>
			<td>
				<input type="radio" value="yes" name="skip_duplicate"> 是
				<input type="radio" checked value="no" name="skip_duplicate"> 否
			</td>
		</tr>
		<tr valign="top">
			<th scope="row">去除所有 HTML 标签(除多媒体标签外)</th>
			<td>
				<input type="radio" value="yes" name="remove_tags"> 是
				<input type="radio" checked value="no" name="remove_tags"> 否
			</td>
		</tr>
		<tr valign="top">
			<th scope="row">去除指定 HTML 标签</th>
			<td>
				<input style="width: 100%" name="remove_html_tags" type="text" placeholder="多个标签用#号隔开">
				<p>如需去除 <?php echo htmlspecialchars('<a>');?> 标签，则输入 a 或 A 即可，不区分大小写</p>
				<p>去除多个标签，如 <?php echo htmlspecialchars('<a><div><h1>');?> 标签，则输入 a#div#h1</p> 即可，不区分大小写
			</td>
		</tr>
		<tr valign="top">
			<th scope="row">去除原文样式</th>
			<td>
				<input type="radio" value="yes" name="remove_style"> 是
				<input type="radio" checked value="no" name="remove_style"> 否
			</td>
		</tr>
		<tr valign="top">
			<th scope="row">自定义图片 Title 和 Alt 属性值</th>
			<td>
			<input style="width:450px" placeholder="默认为文章标题，若填写，则覆盖配置中设置，否则以配置中的为准" type="text" name="image_title_alt" value="<?php echo esc_attr( get_option('bp_image_title_alt') );?>" />
			</td>
		</tr>
		<tr valign="top">
			<th scope="row">移除文中的链接</th>
			<td>
				<input <?php echo get_option('bp_remove_outerlink', 'no') == 'no' ? 'checked' : '';?> class="form-check-input" type="radio" name="remove_outerlink" value="no" > 否
				<input <?php echo get_option('bp_remove_outerlink', 'no') == 'keepcontent' ? 'checked' : '';?> class="form-check-input" type="radio" name="remove_outerlink" value="keepcontent"> 移除链接，保留内容
				<input <?php echo get_option('bp_remove_outerlink', 'no') == 'all' ? 'checked' : '';?> class="form-check-input" type="radio" name="remove_outerlink" value="all"> 移除链接和内容
			</td>
		</tr>
		<tr valign="top">
			<th scope="row">移除所有图片</th>
			<td>
				<input type="radio" value="yes" name="remove_image"> 移除
				<input type="radio" checked value="no" name="remove_image"> 保留
			</td>
		</tr>
		<tr valign="top">
			<th scope="row">去除指定位置图片</th>
			<td>
				<input type="checkbox" value="1" name="remove_specified_image[]"> 第1
				<input type="checkbox" value="2" name="remove_specified_image[]"> 第2
				<input type="checkbox" value="3" name="remove_specified_image[]"> 第3
				<input type="checkbox" value="4" name="remove_specified_image[]"> 第4<br><br>
				<input type="checkbox" value="-1" name="remove_specified_image[]"> 倒数第1
				<input type="checkbox" value="-2" name="remove_specified_image[]"> 倒数第2
				<input type="checkbox" value="-3" name="remove_specified_image[]"> 倒数第3
				<input type="checkbox" value="-4" name="remove_specified_image[]"> 倒数第4<br>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row">移除空白字符(包括空行)</th>
			<td>
				<input type="radio" value="yes" name="remove_blank"> 移除
				<input type="radio" checked value="no" name="remove_blank"> 保留
			</td>
		</tr>
		<tr valign="top">
			<th scope="row">发布类型</th>
			<td>
				<?php
				$types = get_post_types(array(
					'public' => true,
				));
				$typeMap = array(
					'post' => '文章',
					'page' => '页面',
				);
				?>
				<?php foreach ($types as $type):?>
					<?php
						if (in_array($type, array('attachment'))) continue;
						$typeName = isset($typeMap[$type]) ? $typeMap[$type] : $type;
					?>
					<?php if ($type == 'post'):?>
						<input type="radio" name="post_type" value="<?php echo $type;?>" checked><?php echo $typeName;?>&nbsp;&nbsp;
					<?php else: ?>
						<input type="radio" name="post_type" value="<?php echo $type;?>"><?php echo $typeName;?>&nbsp;&nbsp;
					<?php endif;?>
				<?php endforeach;?>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row">文章标签</th>
			<td>
				<input style="width: 100%" name="post_tags" type="text" placeholder="多个标签用#号隔开">
				如：科技#体育#阅读
			</td>
		</tr>
		<tr valign="top">
			<th scope="row">文章分类</th>
			<td>
				<?php
				$cats = get_categories(array(
						'hide_empty' => false,
						'order' => 'ASC',
						'orderby' => 'id'
				));
				?>
				<?php foreach ($cats as $cat):?>
					<input type="checkbox" name="post_cate[]" value="<?php echo $cat->cat_ID;?>"><?php echo $cat->cat_name;?>&nbsp;&nbsp;
				<?php endforeach;?>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row">关键词替换</th>
			<td>
				使用关键词替换库(可前往<a href="<?php echo admin_url();?>admin.php?page=beepress_pro_option">配置</a>中添加)
				<input type="radio" name="use_keywords_lib" value="yes" checked>是&nbsp;&nbsp;
				<input type="radio" name="use_keywords_lib" value="no" >否&nbsp;&nbsp;
				<textarea name="keywords_replace_rule" cols="100" rows="8" placeholder="在此输入关键词替换规则，每行一条规则，规则格式：关键词=替换后的关键词"></textarea><br>
				如：<br>
				windows=mac<br>
				乔布斯=盖茨<br>
			</td>
		</tr>
	</table>
	<?php if($isValidate || $pass):?>
		<input type="submit" value="开始采集" class="button button-primary" id="bp-submit"><p></p>
	<?php else:?>
		<p>
			请确保已经<a target="_blank" href="http://xingyue.artizen.me/auth/index.php?site_url=<?php echo site_url();?>">验证网站</a>
		</p>
		<input type="submit" value="开始采集" class="button button-primary" id="bp-submit" disabled="disabled"><p></p>
	<?php endif;?>
	<div class="progress">
		<div id="progress-status" class="progress-bar active progress-bar-striped" role="progressbar" aria-valuenow="0" aria-valuemin="0" style="width: 0%;">
		</div>
	</div>
	<div class="result">
		<h4>采集结果</h4>
		<div class="table-responsive">
			<table class="table">
				<thead class="thead-inverse">
				<tr>
					<th>#</th>
					<th>结果</th>
					<th>操作</th>
					<th>链接</th>
				</tr>
				</thead>
				<tbody>
				</tbody>
			</table>
			<p style="color: red">*失败原因可能是：文章重复、采集超时、没有图片上传权限、采集规则不正确等</p>
			<p style="color: red">*显示失败实际上也有可能采集成功，可到文章列表进行查看</p>
		</div>
	</div>
	<?php if(!$pass || ($pass && (get_option('bp_close_sponsor', 'no') == 'no'))):?>
		<h4>赞助商</h4>
		<div class="row">
			<div class="col-xs-3" data-end="2019.03.01">
				<a href="https://www.huzhan.com/code/goods311972.html" target="_blank"><img style="border-radius: 5px" src="https://i.loli.net/2019/01/28/5c4ec9fca46ff.gif" width="100%"></a>
			</div>
			<div class="col-xs-3" data-end="2019.01.23">
				<a href="https://www.nuoapp.com/?from=beepress" target="_blank"><img style="border-radius: 5px" src="https://i.loli.net/2018/12/21/5c1c6718aeeb7.jpg
" width="100%"></a>
			</div>
			<div class="col-xs-3" data-end="2019.01.23">
				<a href="https://item.taobao.com/item.htm?id=580526293702" target="_blank"><img style="border-radius: 5px" src="https://i.loli.net/2018/12/18/5c184e4586169.jpg" width="100%"></a>
			</div>
			<div class="col-xs-3" data-end="2019.02.07">
				<a href="https://3ez.cn/script" target="_blank"><img style="border-radius: 5px" src="https://i.loli.net/2019/01/04/5c2f2ae34ce47.gif" width="100%"></a>
			</div>
		</div>
		<p>成为【蜜蜂采集】的赞助商即可在此处展示您的Logo及跳转链接，300元/月，目前仅开放4个位置，插件下载量已达3万+，<a target="_blank" href="http://xingyue.artizen.me/auth/index.php?site_url=<?php echo site_url();?>" style="color: red;">联系我</a></p>
		<p>2号、3号广告位已过期，欢迎联系</p>
	<?php endif;?>
</div>