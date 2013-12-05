<?php

/**
 * Plugin Name: UGN Job Posting Plugin
 * Plugin URI: http://URI_Of_Page_Describing_Plugin_and_Updates
 * Description: A plugin allowing users to post open job positions to be filled by students.
 * Version: 0.1
 * Author: Chris Call, David Zaharee, Danielle Gannon, Dirk Entoven, Paul Fahey
 * Author URI: http://plymouth.edu
 * License: GPL2 License
 */


/*  Copyright 2013 "Unimaginative Group Name", contact via Plymouth State University
    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

function my_custom_post_product() {
    $labels = array(
        'name'               => _x( 'Job Posting', 'post type general name' ),
        'singular_name'      => _x( 'Job Posting', 'post type singular name' ),
        'add_new'            => _x( 'Add New', 'book' ),
        'add_new_item'       => __( 'Post a New Job' ),
        'edit_item'          => __( 'Edit a Job Posting' ),
        'new_item'           => __( 'New Job Posting' ),
        'all_items'          => __( 'All Job Postings' ),
        'view_item'          => __( 'View Job Posting' ),
        'search_items'       => __( 'Search Job Postings' ),
        'not_found'          => __( 'No Matching Job Postings' ),
        'not_found_in_trash' => __( 'No Job Postings Found In The Trash' ),
        'parent_item_colon'  => '',
        'menu_name'          => 'Job Posting'
    );
    $args = array(
        'labels'        => $labels,
        'description'   => 'This is the custom post type for the SoftEng class with Porter, Job Posting',
        'public'        => true,
        'menu_position' => 5,
				'menu_icon'			=> plugins_url( 'icon.png', __FILE__ ),
        'supports'      => array( 'title', 'editor', 'thumbnail', 'excerpts', 'comments' ),
        'has_archive'   => true,
    );

    register_post_type( 'job', $args );
}

add_action( 'init', 'my_custom_post_product' );

$meta_box['job'] = array(
    'id' => 'jobs-meta-details',
    'title' => 'Job Details',
    'context' => 'normal',
    'priority' => 'high',
    'fields' => array(
        array(
            'name' => 'Start Date:',
            'desc' => '',
            'id' => 'start_date',
            'type' => 'date',
            'default' => ''
        ),

        array(
            'name' => 'Expiration Date:',
            'desc' => '',
            'id' => 'expiration_date',
            'type' => 'date',
            'default' => ''
        ),

        array(
            'name' => 'Department:',
            'desc' => '',
            'id' => 'user_dept',
            'type' => 'textarea2',
            'default' => ''
        ),

        array(
            'name' => 'Qualifications:',
            'desc' => '',
            'id' => 'qualif',
            'type' => 'textarea3',
            'default' => ''
        ),

        array(
            'name' => 'Work Study Required:',
            'desc' => '',
            'id' => 'work_study',
            'type' => 'radio',
            'options' => array(
                array('name' => 'Yes', 'value' => 'Value 1'),
                array('name' => 'No', 'value' => 'Value 2')),
            'default' => ''
        ),

        array(
            'name' => 'Pay Information (Optional):',
            'desc' => '',
            'id' => 'pay_info',
            'type' => 'textarea3',
            'default' => ''
        ),

        array(
            'name' => 'Contact Email:',
            'desc' => '',
            'id' => 'user_email',
            'type' => 'textarea2',
            'default' => ''
        ),

        array(
            'name' => 'Upload File:',
            'desc' => '',
            'id' => 'upload_file',
            'type' => 'upload',
            'default' => ''
        )
    )
);

add_action('admin_menu', 'plib_add_box');

function wp_custom_attachment() {
    wp_nonce_field(plugin_basename(__FILE__), 'wp_custom_attachment_nonce');

    $html = '<p class="description">';
    $html .= 'Upload your PDF here.';
    $html .= '</p>';
    $html .= '<input type="file" id="wp_custom_attachment" name="wp_custom_attachment" value="" size="25">';

    echo $html;
}

//Add meta boxes to post types
function plib_add_box() {
    global $meta_box;

    foreach($meta_box as $post_type => $value) {
        add_meta_box($value['id'], $value['title'], 'plib_format_box', $post_type, $value['context'], $value['priority']);
    }
}

//Format meta boxes
function plib_format_box() {
    global $meta_box, $post;

    // Use once for verification
    echo '<input type="hidden" name="plib_meta_box_nonce" value="', wp_create_nonce(basename(__FILE__)), '" />';

    echo '<table class="form-table">';

    foreach ($meta_box[$post->post_type]['fields'] as $field) {
        // get current post meta data
        $meta = get_post_meta($post->ID, $field['id'], true);

        echo '<tr>'.
            '<th style="width:20%"><label for="'. $field['id'] .'">'. $field['name']. '</label></th>'.
            '<td>';
        switch ($field['type']) {
        case 'text':
            echo '<input type="text" name="'. $field['id']. '" id="'. $field['id'] .'" value="'. ($meta ? $meta : $field['default']) . '" size="30" style="width:97%" />'. '<br />'. $field['desc'];
            break;
        case 'textarea':
            echo '<textarea name="'. $field['id']. '" id="'. $field['id']. '" cols="60" rows="4" style="width:97%">'. ($meta ? $meta : $field['default']) . '</textarea>'. '<br />'. $field['desc'];
            break;
        case 'select':
            echo '<select name="'. $field['id'] . '" id="'. $field['id'] . '">';
            foreach ($field['options'] as $option) {
                echo '<option '. ( $meta == $option ? ' selected="selected"' : '' ) . '>'. $option . '</option>';
            }
            echo '</select>';
            break;
        case 'radio':
            foreach ($field['options'] as $option) {
                echo '<input type="radio" name="' . $field['id'] . '" value="' . $option['value'] . '"' . ( $meta == $option['value'] ? ' checked="checked"' : '' ) . ' />' . $option['name'];
            }
            break;
        case 'checkbox':
            echo '<input type="checkbox" name="' . $field['id'] . '" id="' . $field['id'] . '"' . ( $meta ? ' checked="checked"' : '' ) . ' />';
            break;
        case 'date':
            echo '<input type="date" name="'. $field['id']. '" id="'. $field['id'] .'" value="'. ($meta ? $meta : $field['default']) . '" size="30" style="width:97%" />'. '<br />'. $field['desc'];
            break;
        case 'textarea2':
            echo '<textarea name="'. $field['id']. '" id="'. $field['id']. '" cols="60" rows="1" style="width:97%">'. ($meta ? $meta : $field['default']) . '</textarea>'. '<br />'. $field['desc'];
            break;
        case 'textarea3':
            echo '<textarea name="'. $field['id']. '" id="'. $field['id']. '" cols="60" rows="4" style="width:97%">'. ($meta ? $meta : $field['default']) . '</textarea>'. '<br />'. $field['desc'];
            break;
        case 'upload':
            echo '<label for="file">Filename:</label><input type="file" name="file" id="file"> <br> ';
        }
        echo     '<td>'.'</tr>';
    }
    echo '</table>';
}

// Save data from meta box
function plib_save_data($post_id) {
    global $meta_box,  $post;

    //Verify nonce
    if (!wp_verify_nonce($_POST['plib_meta_box_nonce'], basename(__FILE__))) {
        return $post_id;
    }

    //Check autosave
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return $post_id;
    }

    //Check permissions
    if ('page' == $_POST['post_type']) {
        if (!current_user_can('edit_page', $post_id)) {
            return $post_id;
        }
    } elseif (!current_user_can('edit_post', $post_id)) {
        return $post_id;
    }

    foreach ($meta_box[$post->post_type]['fields'] as $field) {
        $old = get_post_meta($post_id, $field['id'], true);
        $new = $_POST[$field['id']];

        if ($new && $new != $old) {
            update_post_meta($post_id, $field['id'], $new);
        } elseif ('' == $new && $old) {
            delete_post_meta($post_id, $field['id'], $old);
        }
    }
}
/*
add_action('save_post', 'plib_save_data');
function job_page(){
	echo ("hello world");
	 $my_post = array(
  'post_title'    => 'cookiepolicy',
  'post_content'  => 'this is my content',
  'post_type'     => 'page',
  'post_status'   => 'publish',
  'post_author'   => 1,
  'post_category' => array( 3,4 )
  );

  // Insert the post into the database
  wp_insert_post( $my_post );
}


if ( have_posts() ) {
		while ( have_posts() ) {
			the_post(); 

				query_posts( 'post_type=labels');

		} // end while
	} // end if
*/

?>
