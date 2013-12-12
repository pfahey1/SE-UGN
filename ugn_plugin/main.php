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

/**
 * Copyright 2013 "Unimaginative Group Name", contact via Plymouth State University
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2, as
 * published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

define('JOBLIST_POST_TYPE', 'joblisting');

/**
 * Register our custom post type
 */
add_action( 'init', function () {
    joblist_register_post_type();
});
function joblist_register_post_type() {
    register_post_type( JOBLIST_POST_TYPE,
        array(
            'labels' => array(
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
                ),
            'description'   => 'This is the custom post type for creating job listings.',
            'public'        => true,
            'menu_position' => 5,
            'menu_icon'     => plugins_url( 'icon.png', __FILE__ ),
            'supports'      => array( 'title', 'editor', 'thumbnail', 'excerpts', 'comments' ),
            'has_archive'   => true,
        )
    );
}

/**
 * Add job postings to the main query
 */
add_action( 'pre_get_posts',
    function ($query) {
        if ( is_home() and $query->is_main_query() ) {
            $post_types = $query->get('post_type');     # don't trample on what's already there
            $post_types[] = JOBLIST_POST_TYPE;
            $query->set('post_type', $post_types);
        }
    }
);

/**
 * Flush rewrite rules on activiation so permalinks work
 */
register_activation_hook( __FILE__,
    function () {
        joblist_register_post_type();
        flush_rewrite_rules();
    }
);

/**
 * Define our custom fields
 */
$joblist_metabox = array(
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
            'name' => 'End Date:',
            'desc' => '',
            'id' => 'end_date',
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
        ),
        array(
            'name' => 'Expiration Date:',
            'desc' => '',
            'id' => 'expiration_date',
            'type' => 'date',
            'default' => ''
        )
    )
);

/**
 * Add our custom fields
 */
add_action( 'add_meta_boxes_' . JOBLIST_POST_TYPE,
    function () use ($joblist_metabox) {
        add_meta_box($joblist_metabox['id'], $joblist_metabox['title'],
            function () use ($joblist_metabox) {
                global $post;

                if ( $post->post_type !== JOBLIST_POST_TYPE ) { return; }   # if this isn't our party, leave

                // Use once for verification
                wp_nonce_field( 'joblist_metabox', 'joblist_metabox_nonce' );

                echo '<table class="form-table">';

                foreach ($joblist_metabox['fields'] as $field) {
                    // get current post meta data
                    $meta = get_post_meta($post->ID, $field['id'], true);

                    echo '<tr><th style="width:20%"><label for="'. $field['id'] .'">'. $field['name']. '</label></th><td>';

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

                    echo '<td></tr>';
                }

                echo '</table>';
            },
            JOBLIST_POST_TYPE, $joblist_metabox['context'], $joblist_metabox['priority']
        );
    }
);

/**
 * handle our custom fields on save
 */
add_action('save_post',
    function ($post_id) use ($joblist_metabox) {
        global $post;

        if ( $post->post_type !== JOBLIST_POST_TYPE ) { return; }   # if this isn't our party, leave

        //Verify nonce
        if (!wp_verify_nonce($_POST['joblist_metabox_nonce'], 'joblist_metabox')) {
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

        foreach ($joblist_metabox['fields'] as $field) {
            $old = get_post_meta($post_id, $field['id'], true);
            $new = sanitize_text_field( $_POST[$field['id']] );

            if ($new && $new != $old) {
                update_post_meta($post_id, $field['id'], $new);
            } elseif ('' == $new && $old) {
                delete_post_meta($post_id, $field['id'], $old);
            }
        }
    }
);

/**
 * hook into the_content and tack on our formatted extra content
 */
add_action('the_content',
    function ($content) use ($joblist_metabox) {
        global $post;   # we need the post_type so we can tell if this is a post we care about, and the post id to get the metadata
        if ( $post->post_type !== JOBLIST_POST_TYPE ) { return $content; }   # if this isn't our party, leave

        $joblist_extras = array();
        foreach ($joblist_metabox['fields'] as $field) {                # loop through our custom fields
            $meta = get_post_meta($post->ID, $field['id'], true);       # get the value for each field for this post
            if (!strlen($meta)) { continue; }       # don't display empty values

            $heading = $field['name'];
            $value = $meta;

            switch ($field['id']) {
            case 'expiration_date':
            case 'start_date':
            case 'end_date':
                $value = date_i18n(get_option('date_format'), strtotime($meta));
                break;
            case 'work_study':
                if ($meta === "Value 1") {
                    $value = "Yes";
                } else {
                    $value = "No";
                }
                break;
            case 'user_email':
                $value = "<a href=\"mailto:$meta\">$meta</a>";
                break;
            }

            if ($field['id'] == 'expiration_date') {    # we don't show the expiration to everybody, so we'll handle it later
                $expiration = $value;
                continue;
            }

            $joblist_extras[] = "<b>$heading</b> $value";
        }
        $content .= "<p>" . implode('<br />', $joblist_extras) . "</p>";     # add a line break between each item and add it to the end of the content

        if (current_user_can('edit_post', $post->ID)) {      # show users that can edit posts the expiration date
            $content .= "<p style='font-style:italic;'>This post will expire on $expiration</p>";
        }

        return $content;    # return the filtered content
    }
);

# todo: What does this do? We're not using it anywhere...
function wp_custom_attachment() {
    wp_nonce_field(plugin_basename(__FILE__), 'wp_custom_attachment_nonce');

    $html = '<p class="description">';
    $html .= 'Upload your PDF here.';
    $html .= '</p>';
    $html .= '<input type="file" id="wp_custom_attachment" name="wp_custom_attachment" value="" size="25">';

    echo $html;
}

