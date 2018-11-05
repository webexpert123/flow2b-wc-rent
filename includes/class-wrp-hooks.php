<?php
/**
 * This is our main WRP_Hooks class
 */

//Exit on unecessary access
defined('ABSPATH') or exit;

class WRP_Hooks extends WRP_Main {

    //Main construct
    public function __construct(){

        /**
         * List of action hooks
         */
        add_action('wp_enqueue_scripts', array($this, 'wrp_scripts_enqueue_callback'));
        add_action('wp_head', array($this, 'wrp_header_callback'));

        //Add back the ratings template wrapper because it was removed by the 10 priority number from the wp_head callback function above
        add_action('woocommerce_single_product_summary', 'woocommerce_template_single_rating', 11);
        add_action('woocommerce_before_add_to_cart_quantity', array($this, 'wrp_render_rental_prices'));

    }

    /**
     * Callback for any script enqueue codes
     */
    final public function wrp_scripts_enqueue_callback(){

        //Add the WRP stylesheet
        wp_enqueue_style('wrp-stylesheet', WRP_ABSURL . 'assets/css/style.css', array(), WRP_VERSION);

    }

    /**
     * Callback for any wp_head hook codes
     */
    final public function wrp_header_callback(){

        global $post;

        //Do anything for products
        if( $post->post_type = 'product' ){

            //Get product meta
            $rental = get_post_meta($post->ID, '_rental', true);
            $rental_prices = get_post_meta($post->ID, '_rent_prices', true);

            //Check if rental prices are set including the boolean variable
            if( wc_string_to_bool($rental) && !empty($rental_prices) ){

                //Remove the sale price in the current product page
                remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_price', 10);

            }

        }

    }

    /**
     * Callback method for the action hook after the:
     * @hooked woocommerce_template_single_rating - 10
     * @hooked woocommerce_template_single_price - 10
     * See * Hook: woocommerce_single_product_summary in WooCommerce
     */
    final public function wrp_render_rental_prices(){
        global $post;
        include_once WRP_TEMPLATE_DIR . 'content-product-rental-prices.php';
    }

}

return new WRP_Hooks;