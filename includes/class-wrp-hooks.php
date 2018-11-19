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
        add_action('admin_enqueue_scripts', array($this, 'wrp_admin_scripts_enqueue_callback'));
        add_action('wp_head', array($this, 'wrp_header_callback'));

        //Add back the ratings template wrapper because it was removed by the 10 priority number from the wp_head callback function above
        add_action('woocommerce_single_product_summary', 'woocommerce_template_single_rating', 11);
        add_action('woocommerce_before_add_to_cart_quantity', array($this, 'wrp_render_rental_prices_single'));
        add_action('woocommerce_product_options_pricing', array($this, 'wrp_product_options_pricing_clbck'));
        add_action('woocommerce_before_shop_loop_item', array($this, 'wrp_woocommerce_before_shop_loop_item'));
        add_action('woocommerce_after_shop_loop_item', array($this, 'wrp_woocommerce_after_shop_loop_item'));
        add_action('manage_product_posts_custom_column', array($this, 'wrp_admin_product_posts_column'), 11, 2);
        
        //Action hook for saving custom meta value for produc rental prices
        add_action('save_post', array($this, 'wrp_save_rental_product_clbck'), 10, 1);

        /**
         * List of filter hooks
         */
        add_filter('product_type_options', array($this, 'wrp_product_type'), 10, 1);

    }

    /**
     * Callback for any script enqueue codes in admin
     */
    final public function wrp_admin_scripts_enqueue_callback(){
        //Add admin css
        wp_enqueue_style('wrp-stylesheet-admin', WRP_ABSURL . 'assets/css/admin.css', array(), WRP_VERSION);
    }

    /**
     * Callback for any script enqueue codes in public
     */
    final public function wrp_scripts_enqueue_callback(){

        //Add the WRP stylesheet
        wp_enqueue_style('wrp-stylesheet-public', WRP_ABSURL . 'assets/css/style.css', array(), WRP_VERSION);

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

            /**
             * Check if rental prices are set including the boolean variable
             * Note: This applies to single product page
             */
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
    final public function wrp_render_rental_prices_single(){
        global $post;

        //Get product meta
        $rental = get_post_meta($post->ID, '_rental', true);
        $rental_prices = get_post_meta($post->ID, '_rent_prices', true);

        //Check if rental prices are set including the boolean variable
        if( wc_string_to_bool($rental) && !empty($rental_prices) ){
            $data = get_post_meta($post->ID, '_rent_prices', true);
            include_once WRP_TEMPLATE_DIR . 'content-product-rental-prices.php';
        }
        
    }

    /**
     * Callback method for product loops targetting the action hook: woocommerce_before_shop_loop_item
     */
    final  public function wrp_woocommerce_before_shop_loop_item(){

        global $product;

        //Get product meta
        $rental = get_post_meta($product->get_id(), '_rental', true);
        $rental_prices = get_post_meta($product->get_id(), '_rent_prices', true);

        /**
         * Check if rental prices are set including the boolean variable
         * Note: This applies to each product loop item
         */
        if( wc_string_to_bool($rental) && !empty($rental_prices) ){

            //Remove the sale price in the current product loop item
            remove_action('woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_price', 10);

        }

    }

    /**
     * Callback method for product loops targetting the action hook: woocommerce_after_shop_loop_item
     */
    final public function wrp_woocommerce_after_shop_loop_item(){
        global $product;

        //Get product meta
        $rental = get_post_meta($product->get_id(), '_rental', true);
        $rental_prices = get_post_meta($product->get_id(), '_rent_prices', true);

        /**
         * Check if rental prices are set including the boolean variable
         * Note: This applies to each product loop item
         */
        if( wc_string_to_bool($rental) && !empty($rental_prices) ){
            $data = get_post_meta($product->get_id(), '_rent_prices', true);
            $first_item = true; //Set this to true to only show the first item of the loop
            include_once WRP_TEMPLATE_DIR . 'content-product-rental-prices.php';
        }

        //Add back the price for non-rental products
        add_action('woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_price', 10);
        
    }

    /**
     * Callback method for adding product type (checkbox) on simple products
     * @param product_type_options
     * see get_product_type_options() method from WooCommerce plugin
     */
    final public function wrp_product_type(array $array): array {
        $array['rental'] = array(
            'id'            => '_rental',
            'wrapper_class' => 'show_if_simple',
            'label'         => __( 'Rental', 'woocommerce' ),
            'description'   => __( 'Rental products can be purchased by period options.', 'woocommerce' ),
            'default'       => 'no',
        );
        return $array;
    }

    /**
     * Callback for filter method on product post type column viewable in WP Dashboard -> All products
     */
    final public function wrp_admin_product_posts_column($column, $id){

        //Get product meta
        $rental = get_post_meta($id, '_rental', true);
        $rental_prices = get_post_meta($id, '_rent_prices', true);

        /**
         * Check if rental prices are set including the boolean variable
         * Note: This applies to each product loop item
         */
        if( wc_string_to_bool($rental) && !empty($rental_prices) && $column == 'price' ):
            $data = get_post_meta($id, '_rent_prices', true);
            $first_item = true; //Set this to true to only show the first item of the loop
            include_once WRP_TEMPLATE_DIR . 'content-product-rental-prices.php';
            ?>
            <style>
                tr#post-<?php echo $id; ?> td span.woocommerce-Price-amount {
                    display: none;
                }
                tr#post-<?php echo $id; ?> td div.wrp_content_product_rental_prices span.woocommerce-Price-amount {
                    display: block;
                }
                tr#post-<?php echo $id; ?> td div.wrp_content_product_rental_prices del span.woocommerce-Price-amount {
                    opacity: 0.5;
                }
            </style>
        <?php endif;

    }

    /**
     * Callback method for adding HTML contents right below the product pricing options for simple products
     */
    final public function wrp_product_options_pricing_clbck(){
        include_once WRP_TEMPLATE_DIR . 'admin/product-options-pricing.php';
    }

    /**
     * Callback method for saving product rental prices from the product editor
     */
    final public function wrp_save_rental_product_clbck($post_id){

        if( isset($_POST['_rental']) ){
            update_post_meta( $post_id, '_rental', 'yes' );
        } else {
            update_post_meta( $post_id, '_rental', 'no' );
        }

        if(isset($_POST['_rent_prices'])){
            update_post_meta( $post_id, '_rent_prices', $this->validated_rental_prices( $this->format_rental_prices_added( $_POST['_rent_prices'] ) ) );
        }
        
    }

}

return new WRP_Hooks;