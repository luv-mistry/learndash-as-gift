<?php

/*
 * Plugin Name:       LEARNDASH GIFT A COURSE + WOOCOMMERCE
 * Description:       Allow customers to buy the learndash courses as a gift for others
 * Version:           1.0
 * Author:            Wisdmlab
 */


if(!defined('ABSPATH')){
    die("");
}

if ( !defined( 'WDM_GIFT_PLUGIN_DIR' ) ) {
    define( 'WDM_GIFT_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
}

if ( !defined( 'WDM_GIFT_PLUGIN_URL')) {
    define( 'WDM_GIFT_PLUGIN_URL', plugin_dir_url(__FILE__) );
}

include_once( WDM_GIFT_PLUGIN_DIR . 'admin/main.php' );
