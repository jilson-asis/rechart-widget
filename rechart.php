<?php
/**
 * Plugin Name: ReChart
 * Description: Displays chart on wp-admin dashboard widget using ReactJS and ReChart.
 * Version: 0.1.0
 * Author: Jilson Asis
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit();
}

// plugin constants
define( 'RECHART_DIR_PATH', plugin_dir_path( __FILE__ ) );
define( 'RECHART_URL', plugins_url( '/', __FILE__ ) );

require_once  RECHART_DIR_PATH . '/class-rechart.php';

$rechart_object = new Rechart();

$rechart_object->init();