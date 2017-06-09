<?php
/**
 * Plugin Name:         WPG User Documentation
 * Plugin URI:          https://github.com/wp-globalis-tools/wpg-user-documentation
 * Description:         Add a mardkown user documentation to your WordPress back-office
 * Author:              Pierre Dargham, Globalis Media Systems
 * Author URI:          https://www.globalis-ms.com/
 * License:             GPL2
 *
 * Version:             0.3.3
 * Requires at least:   4.0.0
 * Tested up to:        4.7.8
 */

namespace Globalis\WP\UserDocumentation;

use League\CommonMark\CommonMarkConverter;

add_action('after_setup_theme', __NAMESPACE__ . '\\setup_plugin', 10, 1);

function setup_plugin() {
    $capability = apply_filters('wpg-user-documentation\capability', 'manage_options');

    if(!current_user_can($capability)) {
        return;
    }

    add_action('admin_bar_menu', __NAMESPACE__ . '\\add_admin_bar_menu', 40);
    add_action('admin_menu', __NAMESPACE__ . '\\add_page', 10);
    add_action('admin_head', __NAMESPACE__ . '\\admin_bar_inline_css', 10, 1);
    add_action('wp_head', __NAMESPACE__ . '\\admin_bar_inline_css', 10, 1);
}

function add_admin_bar_menu(\WP_Admin_Bar $wp_admin_bar) {
    $wp_admin_bar->add_menu( array(
        'parent' => false,
        'id'     => 'user-documentation',
        'title'  => 'Documentation',
        'href'   => admin_url('/index.php?page=user-documentation'),
        ));
}

function admin_bar_inline_css() {
  if(!is_user_logged_in() || !is_admin_bar_showing()) {
    return;
  }
  ?>
    <style type="text/css" media="screen">
        #wpadminbar #wp-admin-bar-user-documentation > .ab-item::before { content: "\f223"; top: 2px; }
    </style>
  <?php
}

function add_page() {
    add_dashboard_page('Documentation', 'Documentation', 'manage_options', 'user-documentation', __NAMESPACE__ . '\\output_page');
}

function output_page() {
    if($css = get_inline_css()) :
        ?>
        <style type="text/css">
            <?= $css ?>
        </style>;
        <?php
    endif;
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

function get_inline_css() {
    return apply_filters('wpg-user-documentation\inline-css', '');
}
