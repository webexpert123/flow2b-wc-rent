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
         * 
         * 
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
        
        //Action hook for saving custom meta value for product rental prices
        add_action('save_post', array($this, 'wrp_save_rental_product_clbck'), 10, 1);

        //Action hook for custom cart content
        add_action('woocommerce_cart_contents', array($this, 'wrp_cart_contents'));

        /**
         * List of filter hooks
         * 
         * 
         */
        add_filter('product_type_options', array($this, 'wrp_product_type'), 10, 1);

        //Filter hook to do something about rental products added to the cart with normal products added
        add_filter('woocommerce_cart_item_visible', array($this, 'wrp_cart_item_visible'), 10, 3);

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

        //Add the Wordpress Dashicons
        wp_enqueue_style( 'dashicons' );

        //Add the WRP script js file
        wp_enqueue_script('wrp-script-public', WRP_ABSURL . 'assets/js/script.js', array(), WRP_VERSION, true);

    }

    /**
     * Callback for any wp_head hook codes
     */
    final public function wrp_header_callback(){

        global $post;

        //Do anything for products
        if( $post->post_type = 'product' ){

            /**
             * Check if rental prices are set including the boolean variable
             * Note: This applies to single product page
             */
            if( $this->is_rental_product($post->ID) ){

                //Remove the sale price in the current product page
                remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_price', 10);

            }

        }

        //Include the Date Range Picker lib
        ?>
        
        <script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
        <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
        <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />

        <?php

    }

    /**
     * Callback method for the action hook after the:
     * @hooked woocommerce_template_single_rating - 10
     * @hooked woocommerce_template_single_price - 10
     * See * Hook: woocommerce_single_product_summary in WooCommerce
     */
    final public function wrp_render_rental_prices_single(){
        global $post;

        //Check if rental prices are set including the boolean variable
        if( $this->is_rental_product($post->ID) ){
            $data = get_post_meta($post->ID, '_rent_prices', true);
            include_once WRP_TEMPLATE_DIR . 'content-product-rental-prices.php';
        }
        
    }

    /**
     * Callback method for product loops targetting the action hook: woocommerce_before_shop_loop_item
     */
    final  public function wrp_woocommerce_before_shop_loop_item(){

        global $product;

        /**
         * Check if rental prices are set including the boolean variable
         * Note: This applies to each product loop item
         */
        if( $this->is_rental_product($product->get_id()) ){

            //Remove the sale price in the current product loop item
            remove_action('woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_price', 10);

        }

    }

    /**
     * Callback method for product loops targetting the action hook: woocommerce_after_shop_loop_item
     */
    final public function wrp_woocommerce_after_shop_loop_item(){

        global $product;

        /**
         * Check if rental prices are set including the boolean variable
         * Note: This applies to each product loop item
         */
        if( $this->is_rental_product($product->get_id()) ){
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

        /**
         * Check if rental prices are set including the boolean variable
         * Note: This applies to each product loop item
         */
        if( $this->is_rental_product($id) && $column == 'price' ):
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

    /**
     * Callback method for showing off additional cart contents
     */
    final public function wrp_cart_contents(){

        //Define counter
        $counter = 0;

        //Loop through each product items from the cart
        foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {

            //Check if they are rental products
            if( $this->is_rental_product($cart_item['product_id']) ){

                //Render the date range content
                if( $counter == 0 ){

                    echo '
                    <tr class="wrp_date_range_row">
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td><b>Set Date</b></td>
                        <td>
                            <span class="dashicons dashicons-calendar-alt"></span>
                            <input id="wrp_date_range" type="text" name="wrp_date_range" placeholder="From -- To"/>
                        </td>
                    </tr>
                    ';

                    $counter++;

                }

                //Get the product data
                $_product   = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
				$product_id = apply_filters( 'woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key );

                //Now begin rendering the rental products the Woocommerce way
                if( $_product && $_product->exists() && $cart_item['quantity'] > 0 ){
                    include WRP_TEMPLATE_DIR . 'cart-rental-products.php';
                }
                
            }

        }

    }

    /**
     * Filter method for changing visibility on rental products added to the cart with normal products added
     */
    final public function wrp_cart_item_visible($boolean, $cart_item, $cart_item_key){

        //Check if they are rental products
        if( $this->is_rental_product($cart_item['product_id']) ){
            return false;
        }

       return $boolean;

    }

}

return new WRP_Hooks;