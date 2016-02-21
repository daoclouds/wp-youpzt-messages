<?php
add_action( 'admin_init', 'youpzt_messages_init' );
add_action( 'admin_menu', 'youpzt_messages_add_menu' );
/**
 * Register plugin option
 *
 * @return void
 */
function youpzt_messages_init()
{
	register_setting( 'youpzt_messages_option_group', 'youpzt_messages_option' );
}

/**
 * Add Option page and PM Menu
 *
 * @return void
 */
function youpzt_messages_add_menu()
{
	global $wpdb, $current_user;

	// Get number of unread messages
	$num_unread = $wpdb->get_var( 'SELECT COUNT(`id`) FROM ' . $wpdb->youpzt_messages.' WHERE `recipient` = "' . $current_user->user_login . '" AND `read` = 0 AND `deleted` != "2"' );

	if ( empty( $num_unread ) )
		$num_unread = 0;

	// Option page
	add_options_page( __( '站内信设置', 'youpzt' ), __( '站内信', 'youpzt' ), 'manage_options', 'youpzt_messages_option', 'youpzt_messages_option_page' );

	// Add Private Messages Menu
	$icon_url = YPM_URL . 'icon.png';
	add_menu_page( __( '站内信', 'youpzt' ), __( '站内信', 'youpzt' ) . "<span class='update-plugins count-$num_unread'><span class='plugin-count'>$num_unread</span></span>", 'read', 'youpzt_messages_inbox', 'youpzt_messages_inbox', $icon_url );

	// 收件箱 page
	$inbox_page = add_submenu_page( 'youpzt_messages_inbox', __( '收件箱', 'youpzt' ), __( '收件箱', 'youpzt' ), 'read', 'youpzt_messages_inbox', 'youpzt_messages_inbox' );
	add_action( "admin_print_styles-{$inbox_page}", 'youpzt_messages_admin_print_styles_inbox' );

	// 已发信息 page
	$outbox_page = add_submenu_page( 'youpzt_messages_inbox', __( '已发信息', 'youpzt' ), __( '已发信息', 'youpzt' ), 'read', 'youpzt_messages_outbox', 'youpzt_messages_outbox' );
	add_action( "admin_print_styles-{$outbox_page}", 'youpzt_messages_admin_print_styles_outbox' );

	// Send page
	$send_page = add_submenu_page( 'youpzt_messages_inbox', __( '发送站内信', 'youpzt' ), __( '发送', 'youpzt' ), 'read', 'youpzt_messages_send', 'youpzt_messages_send' );
	add_action( "admin_print_styles-{$send_page}", 'youpzt_messages_admin_print_styles_send' );
}

/**
 * Enqueue scripts and styles for inbox page
 *
 * @return void
 */
function youpzt_messages_admin_print_styles_inbox()
{
	do_action( 'youpzt_messages_print_styles', 'inbox' );
}

/**
 * Enqueue scripts and styles for outbox page
 *
 * @return void
 */
function youpzt_messages_admin_print_styles_outbox()
{
	do_action( 'youpzt_messages_print_styles', 'outbox' );
}

/**
 * Enqueue scripts and styles for send page
 *
 * @return void
 */
function youpzt_messages_admin_print_styles_send()
{
    //wp_enqueue_style( 'jquery-ui', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery-ui.css' );
	wp_enqueue_style( 'youpzt_messages_css', YPM_CSS_URL . 'style.css' );
	wp_enqueue_script( 'youpzt_messages_js', YPM_JS_URL . 'script.js', array( 'jquery-ui-autocomplete' ) );

	do_action( 'youpzt_messages_print_styles', 'send' );
}

/**
 * Option page
 * Change number of PMs for each group
 */
function youpzt_messages_option_page() {
	?>
<div class="wrap">
	<h2><?php _e( '站内信设置', 'youpzt' ); ?></h2>

	<div style="width:600px;float:left">
		<form method="post" action="options.php">

			<?php
			settings_fields( 'youpzt_messages_option_group' );
			$option = get_option( 'youpzt_messages_option' );

			if ( empty( $option['hide_update'] ) ) {
				echo '<div class="updated">',
				'<p><strong>', __( '1、本插件带有一个用作前台使用的页面模板。', 'youpzt' ), '</strong></p>',
				'<p>', __( '复制文件 <code>youpztMessages-template.php </code>到您的主题文件夹，使用叫做 <code>消息模板</code> 的模板创建页面。', 'youpzt' ), '</p>',
				'<p>', __( '这个模板只有基本结构。您应该修改它以让它和您的主题相协调。', 'youpzt' ), '</p>',
				'<p></p><p><strong>', __( '2、现在您能够把站内信同时发送给多个收件人。', 'youpzt' ), '</strong></p>',
				'</div>';
				echo '<input type="checkbox" name="youpzt_messages_option[hide_update]"> ', __( '下次不要显示这条信息。', 'youpzt' );
			}

			echo '<h3>', __( '请设定每种用户的站内信数量限制:', 'youpzt' ), '</h3>';
			echo '<p>', __( '<b><i>0</i></b> 表示 <b><i>无限制</i></b>', 'youpzt' ), '</p>';
			echo '<p>', __( '<b><i>-1</i></b> 表示 <b><i>不允许</i></b> 发送站内信', 'youpzt' ), '</p>';


			?>
			<table class="form-table">
				<tr>
					<th><?php _e( 'Administrator（管理员）', 'youpzt' ); ?></th>
					<td>
						<input type="text" name="youpzt_messages_option[administrator]" value="<?php echo $option['administrator']; ?>"/>
					</td>
				</tr>
				<tr>
					<th><?php _e( 'Editor（编辑）', 'youpzt' ); ?></th>
					<td><input type="text" name="youpzt_messages_option[editor]" value="<?php echo $option['editor']; ?>"/></td>
				</tr>
				<tr>
					<th><?php _e( 'Author（作者）', 'youpzt' ); ?></th>
					<td><input type="text" name="youpzt_messages_option[author]" value="<?php echo $option['author']; ?>"/></td>
				</tr>
				<tr>
					<th><?php _e( 'Contributor（投稿者）', 'youpzt' ); ?></th>
					<td>
						<input type="text" name="youpzt_messages_option[contributor]" value="<?php echo $option['contributor']; ?>"/>
					</td>
				</tr>
				<tr>
					<th><?php _e( 'Subscriber（订阅者）', 'youpzt' ); ?></th>
					<td><input type="text" name="youpzt_messages_option[subscriber]" value="<?php echo $option['subscriber']; ?>"/>
					</td>
				</tr>
				<tr>
					<th><?php _e( '发件时，您想怎么选择收件人？', 'youpzt' ); ?></th>
					<td>
						<input type="radio" name="youpzt_messages_option[type]" value="dropdown" <?php if ( $option['type'] == 'dropdown' )
							echo 'checked="checked"'; ?> /><?php _e( '下拉列表', 'youpzt' ); ?>
						<input type="radio" name="youpzt_messages_option[type]" value="autosuggest" <?php if ( $option['type'] == 'autosuggest' )
							echo 'checked="checked"'; ?> /><?php _e( '根据用户输入自动给出建议', 'youpzt' ); ?>
					</td>
				</tr>
			</table>

			<h3><?php _e( 'Email 模板:', 'youpzt' ); ?></h3>

			<table class="form-table">
				<tr>
					<th><?php _e( '当用户收到站内信时通过Email通知？', 'youpzt' ); ?></th>
					<td>
						<input type="radio" name="youpzt_messages_option[email_enable]" value="1" <?php if ( $option['email_enable'] )
							echo 'checked="checked"'; ?> /> <?php _e( '是', 'youpzt' ); ?>
						<input type="radio" name="youpzt_messages_option[email_enable]" value="0" <?php if ( !$option['email_enable'] )
							echo 'checked="checked"'; ?> /> <?php _e( '否', 'youpzt' ); ?>
					</td>
				</tr>
				<tr>
					<th><?php _e( '来自 [姓名] （可选）', 'youpzt' ); ?></th>
					<td><input type="text" name="youpzt_messages_option[email_name]" value="<?php echo $option['email_name']; ?>"/>
					</td>
				</tr>
				<tr>
					<th><?php _e( '来自 [Email] （可选）', 'youpzt' ); ?></th>
					<td>
						<input type="text" name="youpzt_messages_option[email_address]" value="<?php echo $option['email_address']; ?>"/>
					</td>
				</tr>
				<tr>
					<th><?php _e( '主题', 'youpzt' ); ?></th>
					<td>
						<input type="text" name="youpzt_messages_option[email_subject]" value="<?php echo $option['email_subject']; ?>"/>
					</td>
				</tr>
				<tr>
					<th><?php _e( '内容', 'youpzt' ); ?></th>
					<td>
						<textarea name="youpzt_messages_option[email_body]" rows="10" cols="50"><?php echo $option['email_body']; ?></textarea><br/>
						<?php _e( '允许使用的HTML标签：', 'youpzt' ); ?> a, br, b, i, u, img, ul, ol, li, hr
					</td>
				</tr>
				<tr>
					<th><strong><?php _e( '可用标签', 'youpzt' ); ?></strong></th>
					<td>&nbsp;</td>
				</tr>
				<tr>
					<th>%BLOG_NAME%</th>
					<td><?php _e( '博客名称', 'youpzt' ) ?></td>
				</tr>
				<tr>
					<th>%BLOG_ADDRESS%</th>
					<td><?php _e( '安装博客的Email地址', 'youpzt' ) ?></td>
				</tr>
				<tr>
					<th>%SENDER%</th>
					<td><?php _e( '发送人名称', 'youpzt' ) ?></td>
				</tr>
				<tr>
					<th>%INBOX_URL%</th>
					<td><?php _e( '收件箱URL', 'youpzt' ) ?></td>
				</tr>
			</table>

			<p class="submit">
				<input type="submit" name="submit" class="button-primary" value="<?php _e( '保存修改', 'youpzt' ) ?>"/>
			</p>

		</form>

	</div>
	<div style="width:200px;float:right;border:1px solid #ccc;padding:10px">
		<h3><?php _e( '微信公众号', 'youpzt' ); ?></h3>

		<p>关注我们</p>

		<a href="http://www.youpzt.com/" target="_blank"><img src="https://www.paypal.com/en_US/i/btn/btn_donate_LG.gif" /></a>
	</div>
</div>
	<?php
}
