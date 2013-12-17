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
define('JOBLIST_DEFAULT_EXP', (60 * 60 * 24 * 90));
define ('JOBLIST_EXP_WARNING', (60 * 60 * 24 * 3));

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

                    // default expiration date is 3 months from now
                    if ($field['id'] == 'expiration_date' and !strlen($meta)) {
                        $meta = date('Y-m-d', time() + JOBLIST_DEFAULT_EXP);
                    }

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
                        echo '<input type="file" name="job" id="job"><br />';
                        echo  '<script type="text/javascript">
                              jQuery("#post").attr("enctype", "multipart/form-data");
                              </script>';
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

        if(!empty($_FILES['job']['name'])) {
            $supported_types = array(/*'application/pdf'*/);
            $arr_file_type = wp_check_filetype(basename($_FILES['job']['name']));
            $uploaded_type = $arr_file_type['type'];

            // selective functionality is here i added ! to allow everything
            if(!in_array($uploaded_type, $supported_types)) {

                $upload = wp_upload_bits($_FILES['job']['name'], null, file_get_contents($_FILES['job']['tmp_name']));

                if(isset($upload['error']) && $upload['error'] != 0) {
                    wp_die('There was an error uploading your file. The error is: ' . $upload['error']);
                } else {
                    update_post_meta($post_id, 'upload_file', $upload);
                }
            } else {
                wp_die("The file type that you've uploaded is not a PDF.");
            }
        }

        foreach ($joblist_metabox['fields'] as $field) {
            if ($field['id'] == 'upload_file') { continue; }    // we handle uploaded files above
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
            if ($field['id'] != 'upload_file' and !strlen($meta)) { continue; }       # don't display empty values

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
            case 'upload_file':
                if (!isset($meta['url'])) { continue 2; }     // no file
                $value = "<a href=\"{$meta['url']}\" target=\"_blank\" download>" .
                    preg_replace('/^.*\//', '', $meta['url']) .
                    "</a>";
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

/**
 * Handle expiring posts
 */
if ( ! wp_next_scheduled( 'joblist_cron' ) ) {
    wp_schedule_event( time(), 'daily', 'joblist_cron' );
}
add_action( 'joblist_cron',
    function () {
        $job_posts = get_posts(array('post_type' => JOBLIST_POST_TYPE, 'post_status' => 'publish'));
        if (!count($job_posts)) { return; }

        foreach ($job_posts as $p) {
            $expiration = get_post_meta($p->ID, 'expiration_data', true);
            if ($expiration < time()) {
                wp_trash_post($p->ID);
            } else if ($expiration < time() + JOBLIST_EXP_WARNING) {
                $email = get_post_meta($p->ID, 'user_email', true);
                $subject = "Your Job Listing \"{$p->post_title} Will Expire Soon";
                $formatted_exp = date_i18n(get_option('date_format'), strtotime($expiration));
                $edit_link = get_edit_post_link($p->ID, '');
                $text = <<<EOF
Your job listing {$p->post_title} will expire on $formatted_exp.  To prevent it being automatically sent to the trash, update the expiration date at <a href="$edit_link">$edit_link</a>.
EOF;
                wp_mail($email, $subject, $text);
            }
        }
    }
);
