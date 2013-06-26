<?php
/*
Plugin Name: HTML Mode Locker
Plugin URI: http://wordpress.org/plugins/html-mode-locker/
Description: Adds and option to lock post editor in HTML Mode on selected post types on per-item basis.
Version: 0.5-dev
Author: Max Chirkov
Author URI: http://simplerealtytheme.com
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Text Domain: html-mode-locker
Domain Path: /languages
*/

if ( !is_admin() )
  return;

include_once 'Class_Pointers.php';

load_plugin_textdomain( 'html-mode-locker', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

add_action('admin_init', 'html_mode_lock_settings_api_init');
function html_mode_lock_settings_api_init() {
	add_settings_section('html_mode_lock_settings', __( 'HTML Mode Locker', 'html-mode-locker'), 'html_mode_lock_empty_content', 'writing');
	add_settings_field('html_mode_lock_post_types',
		__( 'Activate on Post Types', 'html-mode-locker'),
		'html_mode_lock_post_types',
		'writing',
		'html_mode_lock_settings');
	register_setting( 'writing', 'html_mode_lock_post_types' );
}


//this call back is required by the add_settings_section()
//but our content is created by add_settings_field()
//so we return nothing
function html_mode_lock_empty_content() {

}


function html_mode_lock_post_types() {
	$post_types = get_post_types(array('show_ui' => 1));

	$options = get_option('html_mode_lock_post_types');

  $output = '';
	foreach($post_types as $name) {
    $value = ( isset($options[$name]) ) ?  $options[$name] : false;

		$output .= '<input post_type="' . $name . '" type="checkbox" value="1" name="html_mode_lock_post_types[' . $name . ']" ' . checked( 1, $value, false ) .' class="code" /> ' . $name .'<br/>';
	}
	echo '<div id="html-mode-locker-settings">';
  echo $output;
	echo '<p>' . __( 'Allows you to lock post editor in HTML Mode on selected post types on per-item basis.', 'html-mode-locker') . '</p>';
  echo '</div>';
}

add_action('add_meta_boxes', 'html_mode_lock_meta_box');
/* Do something with the data entered */
add_action( 'save_post', 'html_mode_lock_save_postdata' );


function html_mode_lock_meta_box() {
	$options = get_option('html_mode_lock_post_types');

  if ( !$options )
    return;

	foreach($options as $k => $v) {
		if ($v == 1) {
		   add_meta_box(
		        'html_mode_lock',
		        __( 'HTML Mode Locker', 'html_mode_lock' ),
		        'html_mode_lock_callback',
		        $k,
			'side',
			'high'
		    );
		}
	}

}


function html_mode_lock_callback($post)
{
  // Use nonce for verification
  wp_nonce_field( 'html_mode_lock', 'html_mode_lock_nonce', false );

  $html_mode_lock = get_post_meta($post->ID,'html_mode_lock',true);

  // The actual fields for data entry
  echo '<label for="html_mode_lock" class="selectit">';
  echo '<input type="checkbox" id="html_mode_lock" name="html_mode_lock" ' . checked($html_mode_lock, "on", false ) . '/> ';
  _e( 'Lock HTML View', 'html_mode_lock' );
  echo '</label> ';
}


/* When the post is saved, saves our custom data */
function html_mode_lock_save_postdata( $post_id ) {

  // verify if this is an auto save routine.
  // If it is our form has not been submitted, so we dont want to do anything
  if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
      return;

  // verify this came from the our screen and with proper authorization,
  // because save_post can be triggered at other times

  if ( !wp_verify_nonce( $_POST['html_mode_lock_nonce'], 'html_mode_lock' ) )
      return;

  // Check permissions
  if ( 'page' == $_POST['post_type'] )
  {
    if ( !current_user_can( 'edit_page', $post_id ) )
        return;
  }
  else
  {
    if ( !current_user_can( 'edit_post', $post_id ) )
        return;
  }

  // OK, we're authenticated: we need to find and save the data

  $html_mode_lock = $_POST['html_mode_lock'];


  // Do something with $mydata
  // probably using add_post_meta(), update_post_meta(), or
  // a custom table (see Further Reading section below)
  update_post_meta($post_id, 'html_mode_lock', $html_mode_lock);
}


add_filter('user_can_richedit', 'html_mode_lock_on');
function html_mode_lock_on($wp_rich_edit) {
  global $post;

  $html_mode_lock = get_post_meta($post->ID,'html_mode_lock',true);

  if ($html_mode_lock)
    return false;

  return $wp_rich_edit;
}


function html_mode_lock_set_ignore() {
  if ( ! current_user_can('manage_options') )
    die('-1');
  check_ajax_referer('html_mode_lock-ignore');

  $options = get_option('html_mode_lock');
  $options['ignore_'.$_POST['option']] = 'ignore';
  update_option('html_mode_lock', $options);
  die('1');
}
add_action('wp_ajax_html_mode_lock_set_ignore', 'html_mode_lock_set_ignore');