<?php
/*
Plugin Name: Job Postings Database Table
Plugin URI: 
Description: A simple database table for job postings
Version: 1.0
Author: SE-UGN
Author URI: 
License: GPL
*/ 
register_activation_hook( __FILE__,'jobpostings_activate' );

function jobpostings_activate()
{

global $wpdb;

$table_name = $wpdb->prefix . "jobpostings";

if ($wpdb->get_var('SHOW TABLES LIKE ' . $table_name) != $table_name)
	{
	
	$sql = "CREATE TABLE $table_name (
	
	Title VARCHAR(255),
	Description VARCHAR(1000) NOT NULL,
	Department VARCHAR(255) NOT NULL,		
	Qualifications VARCHAR(1000) NOT NULL,
	Work_Study_Required VARCHAR(4) NOT NULL,
	PRIMARY KEY Title  (Title)
	);";
	
	require_once ( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql );
	
	add_option('jobpostings_database_version','1.0');
	}

}
?>