<?php
/**
 * Plugin Name: Reset Thumbnails
 * Version: 1.0
 * Plugin URI: http://wptrack.net
 * Description: Allows you to set the default thumbnails for your all image attachments or apply for your all post thumbnail.
 * Author: Kai
 * Author URI: http://wptrack.net
 * Requires at least: 4.0
 * Tested up to: 4.0
 *
 * Text Domain: reset-thumbnails
 * Domain Path: /lang/
 *
 * @package WordPress
 * @author Kai
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// Load plugin class files
require_once('includes/class-reset-thumbnails.php');
require_once('includes/class-reset-thumbnails-settings.php');


// Load plugin libraries
require_once('includes/lib/class-reset-thumbnails-api.php');
require_once('includes/lib/class-reset-thumbnails-helper.php');

/**
 * Returns the main instance of Reset_Thumbnails to prevent the need to use globals.
 *
 * @since  1.0.0
 * @return object Reset_Thumbnails
 */
function Kai_Reset_Thumbnails() {
	$instance = Reset_Thumbnails::instance( __FILE__, '1.0.0' );

	if ( is_null( $instance->settings ) ) {
		$instance->settings = ResetThumbnails::instance( $instance );
	}

	return $instance;
}

Kai_Reset_Thumbnails();