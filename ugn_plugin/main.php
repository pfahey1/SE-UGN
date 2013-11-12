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
		'supports'      => array( 'title', 'editor', 'thumbnail', 'excerpts', 'comments' ),
		'has_archive'   => true,
	);
	register_post_type( 'job', $args );	
}


add_action( 'init', 'my_custom_post_product' );

?>
