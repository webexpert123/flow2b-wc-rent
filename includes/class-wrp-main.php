<?php
/**
 * This is our main WRP_Main class
 */

//Exit on unecessary access
defined('ABSPATH') or exit;

//Our main class that is extendable but methods cannot be overriden
class WRP_Main {
	
	//A single instance of the class WRP_Main
	protected static $wrp_instance = null;
	
	//JDS Instance ensuring that only 1 instance of the class is loaded
	final public static function instance($version){
		if(is_null(self::$wrp_instance)){
			self::$wrp_instance = new self($version);
		}
		return self::$wrp_instance;
	}
	
	//Cloning is forbidden
	public function __clone() {
		$error = new WP_Error('forbidden', 'Cloning is forbidden.');
		return $error->get_error_message();
	}
	
	//Unserializing instances of this class is forbidden.
	public function __wakeup() {
		$error = new WP_Error('forbidden', 'Unserializing instances of this class is forbidden.');
		return $error->get_error_message();
    }

    //Main construct
    public function __construct($version){
        register_activation_hook( WRP_PLUGIN_FILE, array( $this , 'activate' ) );
        $this->version = $version;
        $this->wrp_constants();
        $this->wrp_includes();
    }

    //Method to check if PHP is version 7.2.0 and above
    final public function activate() {
	
		//Check PHP Version and deactivate & die if it doesn't meet minimum requirements.
		if ( version_compare(PHP_VERSION, '7.2.0', '<=') ) {
			deactivate_plugins( plugin_basename( WRP_PLUGIN_FILE ) );
			wp_die( 'This plugin requires <b>PHP Version 7.2 and up</b>. <a href="' . admin_url('plugins.php') . '">Go Back</a>' );
        }
        
        //Check if WooCommerce is activated. Otherwise, do not activate the plugin.
        if( !is_plugin_active('woocommerce/woocommerce.php') ) {
            deactivate_plugins( plugin_basename( WRP_PLUGIN_FILE ) );
			wp_die( 'This plugin requires <b>WooCommerce<b>. <a href="' . admin_url('plugins.php') . '">Go Back</a>' );
        }
		
        //Do activate Stuff now...
        
	}

    //Define the constants
    final public function wrp_constants(){
		$this->define('WRP_ABSPATH', plugin_dir_path(WRP_PLUGIN_FILE));
		$this->define('WRP_ABSURL', plugin_dir_url(WRP_PLUGIN_FILE));
		$this->define('WRP_VERSION', $this->version);
    }

    //Method to defining constants if it's not set
	final public function define( $name, $value ) {
		if ( ! defined( $name ) ) {
			define( $name, $value );
		}
    }

    //Include the files to be used
    public function wrp_includes(){

        //Our test class
		//include_once WRP_ABSPATH . 'includes/class-wrp-test.php';
		
		//REST API extension class
		include_once WRP_ABSPATH . 'includes/class-wrp-rest.php';

	}
	
	/**
	 * Begin public methods
	 */

	/**
	 * Method for checking formatted multi-dimensional array
	 * Format:
		array(
			'regular_price' => '$10',
			'sale_price' => '0',
			'period_code' => 'daily',
			'period_name' => 'Daily'
		)
	 * Return type: bool
	*/
	final public function is_rental_prices_formatted(array $array): bool {
		if( empty($array['regular_price']) && empty($array['sale_price']) ){
			return false;
		}
		if( empty($array['period_code']) || empty($array['period_name']) ){
			return false;
		}
		return true;
	}

}
