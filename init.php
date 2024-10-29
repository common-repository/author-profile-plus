<?php
/*
Plugin Name: Author Profile Plus
Plugin URI: http://www.birdbrain.com.au/plugin/author-profile-plus
Description: Supercharge your WordPress user profiles with Author Profile Plus
Author: BirdBrain Logic
Version: 0.8.2
Author URI: http://www.birdbrain.com.au
*/

// Ensure WordPress has been bootstrapped
if( !defined( 'ABSPATH' ) )
	exit;

$path = trailingslashit( dirname( __FILE__ ) );

// Ensure our class dependencies class has been defined
require_once( $path . 'class.author-profile-plus.php' );

new Author_Profile_Plus();

?>