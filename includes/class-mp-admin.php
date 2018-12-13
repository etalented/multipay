<?php
/**
 * MultiPay Admin
 *
 * @since   1.5
 * @class   MP_Admin
 */

defined( 'ABSPATH' ) || exit;

class MP_Admin {
    
    public function __construct() {
		add_action( 'init', array( $this, 'includes' ) );
        add_action( 'admin_menu', array( $this, 'admin_menu' ), 9 );
        add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );
    }
    
    public function admin_menu() {
        add_menu_page( 'MultiPay', 'MultiPay', 'manage_options', 'multipay-transactions', null, 'dashicons-cart' );
        add_submenu_page( 'multipay-transactions', __( 'Transactions', 'multipay' ),  __( 'Transactions', 'multipay' ), 'manage_options', 'multipay-transactions', array( $this, 'transactions_page' ) );
        add_submenu_page( 'multipay-transactions', __( 'Settings', 'multipay' ),  __( 'Settings', 'multipay' ), 'manage_options', 'multipay-settings', array( $this, 'settings_page' ) );
    }

    public function admin_scripts() {
        wp_register_script( 'multipay-media', MP()->get_asset_url( 'assets/js/media.js' ), array( 'jquery', 'wp-color-picker', 'jquery-ui-sortable', 'jquery-ui-datepicker' ), false, true );

        wp_register_style( 'jquery-ui-style', 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css' );
        wp_register_style( 'multipay-admin-style', MP()->get_asset_url( 'assets/css/settings.css' ), array( 'jquery-ui-style', 'wp-color-picker' ) );
    }
    
    public function includes() {
        include_once dirname( __FILE__ ) . '/class-mp-admin-settings.php';
        include_once dirname( __FILE__ ) . '/class-mp-admin-transactions.php';
    }
    
	public function settings_page() {
        wp_enqueue_style( 'multipay-admin-style' );
        wp_enqueue_script( 'multipay-media' );
        wp_enqueue_media();
        
		MP_Admin_Settings::output();
	}
    
	public function transactions_page() {
        wp_enqueue_style( 'multipay-admin-style' );
        
		MP_Admin_Transactions::output();
	}
}

return new MP_Admin();