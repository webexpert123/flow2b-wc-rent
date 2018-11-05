<?php
/**
 * This is the product content before the cart template.
 * Actual template is loaded before the quantity input field.
 */

//Exit on unecessary access
defined('ABSPATH') or exit;

//Get product meta values
$data = get_post_meta($post->ID, '_rent_prices', true);

?>

<div class="wrp_content_product_rental_prices">

    <h4>Choose Rental Options</h4>
    <?php echo $this->format_rental_price_table($data); ?>

</div>