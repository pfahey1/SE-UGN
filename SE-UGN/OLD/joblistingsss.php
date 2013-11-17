<?php

        /**
         * Plugin Name: Job Listing
         * Author: Me
         * Version: 3.7
         * Description: In depth
         */

function job_listing() {

	$labels = array(
		'name'                => 'Listings',
		'singular_name'       => 'Listing',
		'menu_name'           => 'Listings',
		'parent_item_colon'   => '',
		'all_items'           => 'All Listings',
		'view_item'           => 'View Listing',
		'add_new_item'        => 'Add New Listing',
		'add_new'             => 'New Listing',
		'edit_item'           => 'Edit Listing',
		'update_item'         => 'Update Listing',
		'search_items'        => 'Search Listings',
		'not_found'           => 'No Listings found',
		'not_found_in_trash'  => 'No Listings found in Trash',
	);
	$args = array(
		'label'               => 'listing',
		'description'         => 'Job Listings',
		'labels'              => $labels,
		'supports'            => array( 'title', 'editor', 'revisions', 'custom-fields', 'page-attributes', 'post-formats', ),
		'taxonomies'          => array( 'category', 'post_tag' ),
		'hierarchical'        => true,
		'public'              => true,
		'show_ui'             => true,
		'show_in_menu'        => true,
		'show_in_nav_menus'   => true,
		'show_in_admin_bar'   => true,
		'menu_position'       => 5,
		'menu_icon'           => '',
		'can_export'          => true,
		'has_archive'         => true,
		'exclude_from_search' => false,
		'publicly_queryable'  => true,
		'capability_type'     => 'page',
	);
	register_post_type( 'listing', $args );

}

// Hook into the 'init' action
add_action( 'init', 'job_listing', 0 );

?>
