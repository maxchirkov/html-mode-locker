<?php
/*
Plugin Name: HTML Mode Locker
Plugin URI: http://simplerealtytheme.com
Description: Adds and option to lock post editor in HTML Mode on selected post types on per-item basis.
Version: 0.1
Author: Max Chirkov
Author URI: http://simplerealtytheme.com
*/

add_action('admin_init', 'html_mode_lock_settings_api_init');
function html_mode_lock_settings_api_init(){
	add_settings_section('html_mode_lock_settings', 'HTML Mode Locker', 'html_mode_lock_settings', 'writing');		
	add_settings_field('html_mode_lock_post_types',
		'Activate on Post Types',
		'html_mode_lock_post_types',
		'writing',
		'html_mode_lock_settings');
	register_setting( 'writing', 'html_mode_lock_post_types' );
}

function html_mode_lock_post_types(){
	$post_types = get_post_types(array('show_ui' => 1));	

	$options = get_option('html_mode_lock_post_types');
	
	foreach($post_types as $name){
		$output .= '<input type="checkbox" value="1" name="html_mode_lock_post_types[' . $name . ']" ' . checked( 1, $options[$name], false ) .' class="code" /> ' . $name .'<br/>';
	}	
	echo $output;	
	echo '<p>Allows you to lock post editor in HTML Mode on selected post types on per-item basis.</p>';
}

add_action('add_meta_boxes', 'html_mode_lock_meta_box');
/* Do something with the data entered */
add_action( 'save_post', 'html_mode_lock_save_postdata' );

function html_mode_lock_meta_box(){
	$options = get_option('html_mode_lock_post_types');
	
	foreach($options as $k => $v){
		if($v == 1){
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
  if($html_mode_lock){
  	add_action('user_can_richedit', 'html_mode_lock_on');
  }

  // The actual fields for data entry
  echo '<label for="html_mode_lock" class="selectit">';
  echo '<input type="checkbox" id="html_mode_lock" name="html_mode_lock" ' . checked($html_mode_lock, "on", false ) . '/> ';
  _e("Lock HTML View", 'html_mode_lock' );
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

function html_mode_lock_on(){
	return false;
}

?>