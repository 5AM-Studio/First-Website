<?php
wp_enqueue_script('jquery');
wp_enqueue_script('bootstrap-script');
wp_enqueue_style('bootstrap-style');
?>
<div class="row">
	<div class="col-lg-8">
		<div class="panel panel-default">
			<div class="panel-heading">
				<h3 class="panel-title">
					基础版(不建议购买) <a class="label label-success" style="color: #FFF;" href="/wp-admin/admin.php?page=beepress_pro_request">查看专业版</a>
				</h3>
			</div>
			<!--	分类列表	-->
			<?php
			$cats = get_categories(array(
					'hide_empty' => false,
					'order' => 'DESC'
			));
			$types = get_post_types(array(
					'public' => true,
			));

			// 获取自动采集配置
			global $wpdb, $table_prefix;
			global $beepress_cron_table, $beepress_profile_table;
			$beepress_cron_table = $table_prefix.'bp_cron_config';
			$beepress_profile_table = $table_prefix . 'bp_profile';
			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
			if ($wpdb->get_var("SHOW TABLES LIKE '$beepress_cron_table'") != $beepress_cron_table) {
				$sql = "CREATE TABLE " . $beepress_cron_table . "(
					id SMALLINT(5) UNSIGNED NOT NULL AUTO_INCREMENT,
					token CHAR(200),
					open TINYINT(1) NOT NULL DEFAULT 1,
					PRIMARY KEY(id)
				) COLLATE='utf8_unicode_ci' ENGINE=MyISAM";
				dbDelta($sql);
			}
			$conf = $wpdb->get_row("SELECT * FROM $beepress_cron_table", ARRAY_A);
			$token = '';
			$open = true;
			if ($conf) {
				$token = $conf['token'];
				$open = intval($conf['open']) == 1;
			}


			if ($wpdb->get_var("SHOW TABLES LIKE '$beepress_profile_table'") != $beepress_profile_table) {
				$sql = "CREATE TABLE " . $beepress_profile_table . "(
					id SMALLINT(5) UNSIGNED NOT NULL AUTO_INCREMENT,
					token CHAR(200),
					count SMALLINT(5) NOT NULL DEFAULT 5,
					PRIMARY KEY(id)
				) COLLATE='utf8_unicode_ci' ENGINE=MyISAM";
				dbDelta($sql);
			}
			$profile = $wpdb->get_row("SELECT * FROM $beepress_profile_table", ARRAY_A);
			$count = 0;
			$profileToken = '';
			if ($profile) {
				$count = $profile['count'];
				$profileToken = trim($profile['token']);
			} else {
				$sql = "INSERT INTO " . $beepress_profile_table . " VALUES(1, '', 5)";
				dbDelta($sql);
				$count = 5;
			}

			$secret = 'wiH5voK0FzAl1DVa';
			$homeUrl = home_url();
			$md5Token = md5($secret . $homeUrl);
			?>
			<div class="panel-body">
				<div>
					<ul class="nav nav-tabs">
						<li class="nav-item active" role="presentation" >
							<a class="nav-link active" data-toggle="tab" href="#wechat" role="tab">微信公众号(手动)</a>
						</li>
<!--						<li class="nav-item">-->
<!--							<a class="nav-link" data-toggle="tab" href="#cron" role="tab">自动同步指定公众号</a>-->
<!--						</li>-->
<!--						<li class="nav-item">-->
<!--							<a class="nav-link" data-toggle="tab" href="#history" role="tab">采集所有文章</a>-->
<!--						</li>-->
						<li class="nav-item">
							<a class="nav-link" data-toggle="tab" href="#custom" role="tab">插件定制</a>
						</li>
<!--						<li class="nav-item">-->
<!--							<a class="nav-link" data-toggle="tab" href="#about" role="tab">关于</a>-->
<!--						</li>-->
					</ul>
					<!-- Tab panes -->
					<div class="tab-content">
						<div class="tab-pane active" id="wechat" role="tabpanel">
							<div class="panel panel-default">
								<div class="panel-heading">
									<?php if ($profileToken == $md5Token):?>
										<h4 class="panel-title" style="color: #a94442;">文章导入(数量不宜过多，最好不超过10条)｜已授权</h4>
									<?php elseif ($count > 0):?>
										<h4 class="panel-title" style="color: #a94442;">文章导入(数量不宜过多，最好不超过10条)｜剩余免费使用次数：<?php echo $count;?></h4>
									<?php else:?>
										<h4 class="panel-title" style="color: #a94442;">批量导入(数量不宜过多，最好不超过10条)｜免费使用次数已用完，请购买授权码</h4>
									<?php endif;?>
								</div>
								<div class="panel-body">
									<form method="post" enctype="multipart/form-data">
										<input type="hidden" name="media" value="wx">
										<div class="form-group">
											<label for="formGroupExampleInput">购买永久授权码，仅需 29 元，请联系微信：always-bee 购买，注明 授权</label>
											<input type="text" class="form-control" id="formGroupExampleInput" name="license_code" placeholder="授权码，购买后可永久使用" value="<?php echo $profileToken;?>">
										</div>
										<div class="form-group">
											<label for="formGroupExampleInput">从指定URL中导入(默认)</label>
											<textarea class="form-control" name="post_urls" rows="10" placeholder="每行一条文章地址"></textarea>
										</div>
										<div class="form-group">
											<label for="formGroupExampleInput">从文件导入(文本形式，每行一条文章地址)</label>
											<input type="file" class="form-control" name="post_file">
										</div>
										<div class="form-group">
											<label for="formGroupExampleInput2">发布时间</label>
											<select class="custom-select" name="change_post_time">
												<option value="false" selected>原文时间</option>
												<option value="true">当前时间</option>
											</select>
										</div>
										<div class="form-group">
											<label for="formGroupExampleInput2">文章状态</label>
											<select class="custom-select" name="post_status">
												<option value="publish">发布</option>
												<option value="pending">等待复审</option>
												<option value="draft">草稿</option>
											</select>
										</div>
										<div class="form-group">
											<label for="formGroupExampleInput2">文章分类</label>
											<select class="custom-select" name="post_cate">
												<option value="1" selected>默认分类</option>
												<?php if(count($cats)):?>
													<?php foreach($cats as $cat):?>
														<?php if($cat->cat_ID == 1) continue; ?>
														<option value="<?php echo $cat->cat_ID;?>"><?php echo $cat->cat_name;?></option>
													<?php endforeach;?>
												<?php endif;?>
											</select>
										</div>
										<div class="form-group">
											<label for="formGroupExampleInput2">文章类型</label>
											<select class="custom-select" name="post_type">
												<?php if(count($types)):?>
													<?php foreach($types as $type):?>
														<option value="<?php echo $type;?>"><?php echo $type;?></option>
													<?php endforeach;?>
												<?php else:?>
													<option value="post" selected>默认类型(post)</option>
												<?php endif;?>
											</select>
										</div>
<!--										<div class="form-group">-->
<!--											<label for="formGroupExampleInput2">图片保存路径</label>-->
<!--											<select class="custom-select" name="image_url_mode">-->
<!--												<option value="default" selected>使用相对路径</option>-->
<!--												<option value="relative">使用绝对路径</option>-->
<!--											</select>-->
<!--										</div>-->
										<div class="form-group">
											<label for="formGroupExampleInput2">保留版权信息</label>
											<select class="custom-select" name="keep_source">
												<option value="keep" selected>保留</option>
												<option value="remove">移除</option>
											</select>
										</div>
										<div class="form-group">
											<label for="formGroupExampleInput2">保留原文样式</label>
											<select class="custom-select" name="keep_style">
												<option value="keep" selected>保留</option>
												<option value="remove">移除</option>
											</select>
										</div>
<!--										<div class="form-group">-->
<!--											<label for="force">强制导入（正常情况下，标题重复是无法导入的，勾选后将强制导入）</label>-->
<!--											<input hidden name="force" id="force" type="checkbox" value="force">-->
<!--										</div>-->
										<div class="form-group">
											<label for="debug">调试（如果出现出错的情况，勾选可查看调试出错信息）</label>
											<input name="debug" id="debug" type="checkbox" value="debug">
										</div>
										<button type="submit" class="btn btn-primary">确定</button>
									</form>
								</div>
							</div>
						</div>
						<div class="tab-pane" id="cron" role="tabpanel">
							<div class="panel panel-default">
								<div class="panel-heading">
									<h4 class="panel-title" style="color: #a94442;">自动同步配置，免去你每天需要手动导入的烦恼</h4>
								</div>
								<div class="panel-body">
									<h4 class="panel-title" style="color: #a94442;">注意:基础版和专业版均须额外购买Token，请加微信:always-bee，注明:token</h4>
									<form method="post">
										<input hidden name="setting" value="cron">
										<div class="form-group">
											<label for="formGroupExampleInput">输入Token(密钥，请勿泄漏)</label>
											<input type="text" class="form-control" id="token" name="token" placeholder="请联系微信：always-bee 购买" value="<?php echo $token;?>">
										</div>
										<div class="form-group">
											<label for="debug">是否开启</label>
											<input name="open" id="open" type="checkbox" <?php if($open) echo "checked";else echo "";?>>
										</div>
										<button type="submit" class="btn btn-primary">确定</button>
									</form>
								</div>
								<a href="http://artizen.me/beepress?utm_source=beepress&utm_medium=token" target="_blank">相关说明</a>｜<a href="http://kongbei.io/reading?utm_source=beepress&utm_medium=token" target="_blank">DEMO</a>
							</div>
						</div>
						<div class="tab-pane" id="history" role="tabpanel">
							付费服务：采集指定的公众号的所有历史文章
							<h4>如有需要请添加微信：always-bee 注明 历史文章 </h4>
						</div>
						<div class="tab-pane" id="custom" role="tabpanel">
							如果您需要基于此插件进行定制，可以联系我
							<h4>微信：always-bee 注明 插件定制 </h4>
						</div>
						<div class="tab-pane" id="about" role="tabpanel">
							<h4>使用帮助</h4>
							<ul>
								<li>
									1.批量导入请注意URL条数不宜过多，导致请求超时情况，出现部分文章导入失败
								</li>
								<li>
									2.图片保存到本地，速度会比较慢，请根据自身网络情况选择
								</li>
								<li>
									3.为什么提示导入成功后仍旧看不到文章？可能文章排在后面，按日期排序
								</li>
								<li>
									4.不宜导入过于频繁，避免被微信屏蔽
								</li>
								<li>
									更多问题请访问 <a href="http://xingyue.artizen.me/archives/563"> FAQs 页面</a>
								</li>
							</ul>
							<h4>免责声明</h4>
							<p>本插件仅负责导入文章的工作，给用户提供方便的途径，请确保您拥有文章的所有权或文章原作者的授权</p>
						</div>
					</div>
				</div>
			</div>
			<div class="panel-footer">Built with ❤️ by Bee｜<a href="http://xingyue.artizen.me/beepresspro?utm_source=<?php echo $homeUrl;?>&utm_medium=footer" target="_blank">官方网站</a></div>
		</div>
	</div>
	<div class="col-lg-4 col-md-4 col-sm-8">
		<div class="col-lg-12">
			<div class="panel panel-primary" style="border-color: #000">
				<div class="panel-heading" style="background-color: #000">
					<h3 class="panel-title">
						小蜜蜂公众号助手
					</h3>
				</div>
				<div class="panel-body">
					<a href="http://xingyue.artizen.me/?p=1959" target="_blank">
						<img src="https://i.loli.net/2018/11/30/5c0094d09f450.jpeg" width="100%">
					</a>
				</div>
				<div class="panel-footer">
					公众号历史文章采集，支持导出HTML、PDF、表格、链接格式、点赞数、阅读数、评论采集<a style="color: red;" href="http://xingyue.artizen.me/?p=1959" target="_blank"><strong>了解详情</strong></a>
				</div>
			</div>
		</div>
		<div class="col-lg-12">
			<div class="panel panel-default">
				<div class="panel-heading">
					<h3 class="panel-title">
						关注我的公众号
					</h3>
				</div>
				<div class="panel-body">
					<img width="100%" src="https://i.loli.net/2018/11/30/5c00943dcba24.png">
				</div>
				<div class="panel-footer">
					第一时间获取插件相关消息
				</div>
			</div>
		</div>
	</div
</div>

