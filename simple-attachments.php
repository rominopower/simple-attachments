<?php
/*
 Plugin Name: Simple Attachments
 Plugin URI: http://www.programmattatore.blogspot.com
 Description: Attach images and files to a post using wordpress core.
 Version: 1.0.0
 Author: Fabrizio Giannone
 Author URI: http://www.myprojects.it
 */
if (!defined('ABSPATH')) die("Aren't you supposed to come here via WP-Admin?");

function get_plugin_url($path = '',$file = __FILE__) {
       global $wp_version;

       if (version_compare($wp_version, '2.8', '<')) { // Using WordPress 2.7
          $folder = dirname(plugin_basename($file));
          if ('.' != $folder)
         $path = path_join(ltrim($folder, '/'), $path);
          return plugins_url($path);
       }
       return plugins_url($path, $file);
    }

/* Prints the box content */
function simple_attachments_inner_custom_box($post) {
	
	  echo '<a href="#" id="attach-media" class="button">Attach media</a>';
	
	$args = array(
		'post_type' => 'attachment',
		'numberposts' => -1,
		'post_status' => null,
		'post_parent' => $post->ID
	);
	$attachments = get_posts($args);
	if ($attachments) {
		$image_dir = get_plugin_url(NULL,__FILE__).'/images/';
		$i = 0;
		foreach ($attachments as $attachment) {
			$i++;
			echo "\n";
			echo '<div style="float: left; border: 1px solid #dfdfdf; margin-bottom: 5px; margin-right: 5px;"><div style="border: 1px solid #fff; padding: 0 5px 0 5px; min-width: 120px; height: 170px; ">';
			
			echo '<p style="margin-bottom: 0;"><a class="dashicons dashicons-no-alt" alt="remove the attachment (not the file)" href="'.esc_url( admin_url('tools.php?page=unattach&noheader=true&id=' . $attachment->ID) ).'">&nbsp; </a><small>Attach '.$i.'</small></p>';

			$icon = wp_mime_type_icon($attachment->post_mime_type);
			$temp = end(explode('/',$icon));
			if($temp == 'default.png')
			{
				$end = end(explode('.',$attachment->guid));
				$file = WP_PLUGIN_DIR.'/simple-attachments/images/'.$end.'.png';
				// Wait! We have a better one!
				if(file_exists($file))
				{
					$icon = $image_dir.$end.'.png';
				}
			}

			// Show the real file? Even better!
			if(in_array($end, array('png','jpeg','jpg','gif','bmp')))
			{
				$icon = $attachment->guid;
			}

			$title = apply_filters('the_title', $attachment->post_title);
			$icon_html = '<img class="sa-img" id="'.$attachment->ID.'" src="'.$icon.'" style="max-width: 80px; max-height: 80px;">';
			$href_human = end(explode('uploads/',$attachment->guid));
			
			echo '<p style="margin: 0;"><strong>'.$title.'</strong></p>';
			
		//	echo '<p style="line-height: 12px;"><small>Link: <br/><a href="'.$attachment->guid.'" target="_blank">'.$href_human.'</a></small></p>';
			echo '<p style="text-align: center; padding-top: 5px;">'.$icon_html.'</p>';
			
			echo '</div></div><!-- end div for attachment -->';
		}
		echo '<div style="clear: both;"></div>';
	}
	else
	{
		echo '<div style="clear: both;">'.__('Not attached files').'</div>';
	}
}

/* Adds a box to the main column on all post_type edit screens */
function simple_attachments_add_custom_box() {
	$post_types=get_post_types('','names'); 
	foreach ($post_types as $post_type ) {
		add_meta_box( 'plugin_see_attachments_sectionid', __( 'Attached Media', 'plugin_see_attachments' ), 'simple_attachments_inner_custom_box', $post_type );
	}  
}

//action to set post_parent to 0 on attachment
function unattach_do_it() {
	global $wpdb;
	
	if (!empty($_REQUEST['id'])) {
		$wpdb->update($wpdb->posts, array('post_parent'=>0), array('id'=>$_REQUEST['id'], 'post_type'=>'attachment'));
	}
	
	wp_redirect($_SERVER[HTTP_REFERER]);
	exit;
}





//set it up
add_action( 'admin_menu', 'unattach_init' );
function unattach_init() {
	if ( current_user_can( 'upload_files' ) ) {
		//this is hacky but couldn't find the right hook
		add_submenu_page('tools.php', 'Unattach Media', 'Unattach', 'upload_files', 'unattach', 'unattach_do_it');
		remove_submenu_page('tools.php', 'unattach');
	}
}

/* Define the custom boxes */
add_action('add_meta_boxes', 'simple_attachments_add_custom_box');


add_action('wp_enqueue_media', 'include_media_button_js_file');


function include_media_button_js_file() {
       
       wp_register_script('simple-attachment-js', get_plugin_url(NULL,__FILE__).'/js/simple-attachments.js', array('jquery'), '1.0', true);
     $valori=array('urlajax'=> admin_url().'/admin-ajax.php');

       wp_localize_script( 'simple-attachment-js', 'variabili', $valori );
       wp_enqueue_script( 'simple-attachment-js' );
     
}

function myAjax(){

global $wpdb;

$listaid=$_POST["variabile"];
$postid=$_POST["postid"];
//print_r($listaid);
//echo "oi";
//echo $postid;
foreach ($listaid as $id) {
	
	
/* $invia=$wpdb->update($wpdb->posts, array('post_parent'=>$postid, array('id'=>$id, 'post_type'=>'attachment')));

  $my_post = array(
     'ID'           => $id,
      'post_parent'   => $postid,
     
  );

// Update the post into the database
  wp_update_post( $my_post, true );
exit( var_dump( $wpdb->last_query ) );*/

$attached = $wpdb->query( $wpdb->prepare( "UPDATE $wpdb->posts SET post_parent = ".$postid." WHERE post_type = 'attachment' AND ID IN ( $id )", $postid ) );
echo( var_dump( $wpdb->last_query ) );

echo $attached;



}
exit();
}

add_action( 'wp_ajax_nopriv_myAjax', 'myAjax' );
add_action( 'wp_ajax_myAjax', 'myAjax' );

?>