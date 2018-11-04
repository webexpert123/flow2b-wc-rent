<?php
/**
 * This is our test class
 */

//Exit on unecessary access
defined('ABSPATH') or exit;

class Test extends WRP_Main {

    public function __construct(){

        add_action( 'admin_menu', function(){
            add_menu_page(
                'WRP Test',
                'WRP Test',
                'administrator',
                'wrp-test',
                function(){
                    ?>
                    <div class="wrap">
                        <pre>
                        <?php
                        $data = get_post_meta( 29, '_rent_prices', true );

                        //Check if the value is an array
                        if( is_array($data) ){

                            //Loop through the elements
                            foreach($data as $index => $value){
                                if( $this->is_rental_prices_formatted($value) ){
                                    $data[$index] =  $value;
                                }
                            }

                        }
                        //unset($data[0]);
                        $data = array_values($data);
                        var_dump($data);
                        var_dump( WRP() );

                        //var_dump( version_compare(PHP_VERSION, '7.2.0', '>=') );
                        
                        ?>
                        </pre>
                    </div>
                    <?php
                }
            );
        });

    }

}

return new Test;