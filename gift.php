<?php

/*
 * Plugin Name:       LEARNDASH GIFT A COURSE + WOOCOMMERCE
 * Description:       Allow customers to buy the learndash courses as a gift for others
 * Version:           1.0
 * Author:            Luv Mistry
 */



add_action('woocommerce_after_checkout_billing_form', 'custom_checkout_field');

function custom_checkout_field($checkout)
{
    $chekbox_for_purchase_as_gift = array(
        'type' => 'checkbox',
        'class' => array('input-checkbox') ,
        'label' => __('Purchase as a gift?'),
        'values' => false,

    );

    $field_for_recipients_name = array(
        'type' => 'text',
        'input_class' => array('course_as_gift') ,
        'label' => __('Recipients Name') ,
        'required'  => true,
    );
    $field_for_recipients_email =array(
        'type' => 'text',
        'input_class' => array('course_as_gift') ,
        'label' => __('Recipients Email?') ,
        'required'  => true,

    );
    $field_for_gift_message = array(
        'type' => 'textarea',
        'input_class' => array('course_as_gift') ,
        'label' => __('Gift Message') ,
        'placeholder' => __('Personalize your gift with little note.') ,


    );

    echo '<div id="course_as_gift">';

    woocommerce_form_field( 'purchase_as_gift',$chekbox_for_purchase_as_gift );
    echo '<div id="recipient_info_fields">';
    woocommerce_form_field( 'recipients_name',$field_for_recipients_name  );
    woocommerce_form_field( 'recipients_email',$field_for_recipients_email  );
    woocommerce_form_field( 'recipients_message',$field_for_gift_message  );
    echo '</div>';
    echo '</div>';

    ?>
<script>
    jQuery(document).ready(function($) {
        $('#recipient_info_fields').hide();
        $('#purchase_as_gift').on('change', function() {
            if ($('#purchase_as_gift').is(":checked"))
            {
                $('#recipient_info_fields').show();
            }
            else {
                $('#recipient_info_fields').hide();
            }
        });
    })
</script>
<?php

}


add_action('woocommerce_checkout_process', 'customised_checkout_field_process', 99 );

function customised_checkout_field_process(){
    if (!empty($_POST['purchase_as_gift'])) 
    {
        if (empty($_POST['recipients_name'])){
            wc_add_notice(__('Please Recipients Name') , 'error');
        }   
        if (empty($_POST['recipients_email'])){
            wc_add_notice(__('Please Recipients Email ID') , 'error');
        }
    }
    
}

add_action('woocommerce_checkout_update_order_meta', 'custom_checkout_field_update_order_meta');

function custom_checkout_field_update_order_meta($order_id){
    if (!empty($_POST['recipients_name'])) {
        update_post_meta($order_id, '_recipients_name',sanitize_text_field($_POST['recipients_name']));
    }
    if (!empty($_POST['recipients_email'])) {
        update_post_meta($order_id, '_recipients_email',sanitize_text_field($_POST['recipients_email']));
    }
    if (!empty($_POST['recipients_message'])) {
        update_post_meta($order_id, '_recipients_message',sanitize_text_field($_POST['recipients_message']));
    }
    

}

function your_custom_field_function_name($order){
    $order_id = $order->id;
    $recipient_name =  get_post_meta($order_id , '_recipients_name' ,true );
    $recipient_email = get_post_meta($order_id , '_recipients_email' ,true );
    $gift_message = get_post_meta($order_id , '_recipients_message' ,true );
    if (!empty($recipient_name) && !empty($recipient_email) ){
        echo "<p><strong>Recipients Name : </strong> " .$recipient_name. "</p>";
        echo "<p><strong>Recipients E-mail : </strong> " .$recipient_email. "</p>";
        echo "<p><strong>Gift Message : </strong> " .$gift_message. "</p>";
    }
    
}

add_action( 'woocommerce_admin_order_data_after_billing_address', 'your_custom_field_function_name', 10, 1 );

add_action( 'woocommerce_order_status_completed','gift_add_course_access' , 20, 1 );


function gift_add_course_access($order_id, $customer_id = null){
    $order = wc_get_order( $order_id );
    $recipient_name =  get_post_meta($order_id , '_recipients_name' ,true );
    $recipient_email = get_post_meta($order_id , '_recipients_email' ,true );
    $gift_message = get_post_meta($order_id , '_recipients_message' ,true );
    $customer_id = $order->get_user_id();
    $products = $order->get_items();
    if (!empty($recipient_name) && !empty($recipient_email) ){ 
        update_user_meta( $customer_id, 'name',$products  );
        $recipient_user_exist = email_exists($recipient_email);
        if ($recipient_user_exist){
            foreach ($products as $product) {
                $courses_id = get_post_meta( $product['product_id'], '_related_course', true );
                if ( $courses_id && is_array( $courses_id ) ) {
                    foreach ( $courses_id as $course_id ) {
                        ld_update_course_access( $recipient_user_exist, $course_id, $remove = true );
                        ld_update_course_access( $recipient_user_exist, $course_id);
                    }
                }
            }
            $message = '<html>
                            <head>
                                <title>HTML Email Template</title>
                                <style>
                                h1 {
                                    text-align: center;
                                    background-color : #98ed95;
                                    padding : 20px;
                                    
                                    
                                }
                                .message {
                                    margin: auto;
                                    background-color : #ededed;
                                    width : 50%;
                                    
                                }
                                .content {
                                    padding : 5px 30px ; 
                                }
                                
                                </style>
                            </head>
                            <body>
                                <div class="message">
                                    <h1>Learndash as gift + Woocommerce</h1>
                                    <div class="content" >
                                        <h3>Hello '.$recipient_name.',</h3>
                                        <p>'.$gift_message.'.</p>
                                        <p>Username : '.$recipient_email.'</p>
                                        <p>Start yours learning now <a href="http://learndash-as-gift.local/">Click here </a></p>
                                    </div>
                                </div>
                            </body>
                        </html>';

            $headers = array(
                'Content-Type: text/html; charset=UTF-8',
            );

            wp_mail( $recipient_email, $order->get_billing_first_name() .' sends you a gift' , $message , $headers);
            
        }
        else {
            $recipient_password = wp_generate_password( $length = 12, $special_chars = true,  $extra_special_chars = false );
            $user_id = wp_create_user( $recipient_email , $recipient_password , $recipient_email);
            foreach ($products as $product) {
                $courses_id = get_post_meta( $product['product_id'], '_related_course', true );
                if ( $courses_id && is_array( $courses_id ) ) {
                    foreach ( $courses_id as $course_id ) {
                        ld_update_course_access( $user_id, $course_id);
                    }
                }
            }
            $message = '<html>
                            <head>
                                <title>HTML Email Template</title>
                                <style>
                                h1 {
                                    text-align: center;
                                    background-color : #98ed95;
                                    padding : 20px;
                                    
                                    
                                }
                                .message {
                                    margin: auto;
                                    background-color : #ededed;
                                    width : 50%;
                                    
                                }
                                .content {
                                    padding : 5px 30px ; 
                                }
                                
                                </style>
                            </head>
                            <body>
                                <div class="message">
                                    <h1>Learndash as gift + Woocommerce</h1>
                                    <div class="content" >
                                        <h3>Hello '.$recipient_name.',</h3>
                                        <p>'.$gift_message.'</p>
                                        <p>Username : '.$recipient_email.'</p>
                                        <p>Password ; '.$recipient_password.'</p>
                                        <p>Start yours learning now <a href="http://learndash-as-gift.local/">Click here.</a></p>
                                    </div>
                                </div>
                            </body>
                        </html>';

            $headers = array(
                'Content-Type: text/html; charset=UTF-8',
            );

            wp_mail( $recipient_email, $order->get_billing_first_name() .' sends you a gift' , $message , $headers);
        }
    }
    
}

