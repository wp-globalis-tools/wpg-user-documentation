<?php
/*
Plugin Name: WPG User Documentation
Plugin URI:  https://github.com/wp-globalis-tools/wpg-user-documentation
Description: Add a mardkown user documentation to your WordPress back-office
Version:     0.1.0
Author:      Pierre Dargham
Author URI:  https://github.com/pierre-dargham/
License:     GPL-2.0+
License URI: http://www.gnu.org/licenses/gpl-2.0.txt
*/

namespace Globalis\WPGUserDocumentation;

use League\CommonMark\CommonMarkConverter;

add_action('admin_bar_menu', __NAMESPACE__ . '\\action_admin_bar_menu', 40);
add_action('admin_head', __NAMESPACE__ . '\\admin_bar_inline_css', 10, 1);
add_action('wp_head', __NAMESPACE__ . '\\admin_bar_inline_css', 10, 1);
add_action('admin_menu', __NAMESPACE__ . '\\add_page', 10);

function action_admin_bar_menu(\WP_Admin_Bar $wp_admin_bar) {
	$wp_admin_bar->add_menu( array(
		'parent' => false,
		'id'     => 'user-documentation',
		'title'  => 'Documentation',
		'href'   => admin_url('/index.php?page=user-documentation'),
		));
}

function admin_bar_inline_css() {
	if(!is_user_logged_in()) {
		return;
	}
  ?>
	<style type="text/css" media="screen">
		#wpadminbar #wp-admin-bar-user-documentation > .ab-item::before {	content: "\f223";	top: 2px;	}
	</style>
  <?php
}

function add_page() {
	add_dashboard_page('Documentation', 'Documentation', 'manage_options', 'user-documentation', __NAMESPACE__ . '\\output_page');
}

function output_page() {
	?>
	<div id="user-documentation-content" class="wrap">
		<?= get_markdown() ?>
	</div>
	<?php
}

function get_markdown_path($filename = 'README.md') {
	$dir      = apply_filters('wpg-user-documentation\mardkown-dir', defined('ROOT_DIR') ? ROOT_DIR : ABSPATH);
	$filename = apply_filters('wpg-user-documentation\mardkown-filename', $filename);
	return trailingslashit($dir) . $filename;
}

function get_markdown() {
	$path = get_markdown_path();
	if(!file_exists($path)) {
		return '';
	}
	$markdown  = file_get_contents($path);
	$converter = new CommonMarkConverter();
	return $converter->convertToHtml($markdown);
}
