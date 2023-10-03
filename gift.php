<?php

/*
 * Plugin Name:       LEARNDASH GIFT A COURSE + WOOCOMMERCE
 * Description:       Allow customers to buy the learndash courses as a gift for others
 * Version:           1.0
 * Author:            Wisdmlab
 */


class Wdm_Learndash_As_Gift_Woocommerce {
    
    var $access_course = array();
    public function __construct() {
        // hooks 
        // Jquery for front end
        add_action('wp_enqueue_scripts', array($this ,'wdm_purchase_as_gift_field_js'));
        //Custom field on ckeckout page
        add_action('woocommerce_after_checkout_billing_form',array($this , 'wdm_purchase_as_gift_field'));
        // Validation of custom field
        add_action('woocommerce_checkout_process', array($this ,'wdm_purchase_as_gift_field_validate') );
        // Save date of custom field 
        add_action('woocommerce_checkout_update_order_meta', array($this ,'wdm_purchase_as_gift_field_update_order_meta'));
        // Showing custom field data on admin order
        add_action( 'woocommerce_admin_order_data_after_billing_address', array($this ,'wdm_purchase_as_gift_field_on_admin_order_data'), 10, 1 );
        //Gifting courses to recipient 
        add_action( 'woocommerce_order_status_processing',array($this ,'wdm_check_data') ,8, 1 );
        add_action( 'woocommerce_order_status_completed',array($this ,'wdm_gift_add_course_access') ,20, 1 );

        
    }

    /**
     * Adding JS
     * 
     *@return void      
     */
    public function wdm_purchase_as_gift_field_js() {
    
        wp_enqueue_script('wdm_purchase_as_gift_fields_js', plugin_dir_url( __FILE__ ).'wdm_purchase_as_gift_fields.js', array('jquery'));
    
    }

    /**
     * Adding custom field
     * 
     * @param $checkout
     * 
     * @return void
     */

    public function wdm_purchase_as_gift_field($checkout)
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

    }

    /**
     * Validate the checkout field
     * 
     * @return void
     */

    function wdm_purchase_as_gift_field_validate(){
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

    /**
     * Save the data of checkout field 
     * 
     * @param int $order_id
     * 
     * @return void
     */
    function wdm_purchase_as_gift_field_update_order_meta($order_id){
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

    /**
     * Display the data custom field on admin order page 
     * 
     * @param $order
     * 
     * @return void
     */

    function wdm_purchase_as_gift_field_on_admin_order_data($order){
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

    /**
     * Draft of Mail 
     * 
     * @param string $recipient_name
     * 
     * @param string $gift_message
     * 
     * @param string $recipient_email
     * 
     * @param string $recipient_password
     * 
     * @return string $content
     */

    function wdm_mail_body ($recipient_name , $gift_message, $recipient_email, $recipient_password = null){
        $content = '<html>
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
                                <h3>Hello '.$recipient_name.',</h3> ';
        if ($gift_message != null) {
            $content .= '<p>'.$gift_message.'</p>';
        }
        $content .= '<p>Username : '.$recipient_email.'</p>';
        if ($recipient_password != null){
            $content .= '<p>Password ; '.$recipient_password.'</p>';

        }
        $content .= '<p>Start yours learning now <a href="'.site_url().'">Click here.</a></p>
                    </div>
                </div>
            </body>
        </html>';
            
                                
        

        return $content;

        
    }

    /**
     * @param int $order_id 
     * 
     * @param int $customer_id 
     * 
     * @return void 
     * */
    function wdm_check_data ($order_id, $customer_id = null){
        $order = wc_get_order( $order_id );
        $customer_id = $order->get_user_id();
        $products = $order->get_items();
        foreach ($products as $product) {
            $courses_id = get_post_meta( $product['product_id'], '_related_course', true );
            if ( $courses_id && is_array( $courses_id ) ) {
                foreach ( $courses_id as $course_id ) {
                    if (sfwd_lms_has_access($course_id , $customer_id)== null ){
                        array_push($this->access_course,$course_id);
                    }
                }
            }
        }
        
    }

    
    /**
     * Remove access of course
     * @param int $course_id
     * 
     * @param int $customer_id 
     * 
     * @return void 
     */
    function wdm_remove_access ($course_id, $customer_id){
        if (in_array($course_id,$this->access_course )){
            ld_update_course_access( $customer_id, $course_id, $remove = true );
        }
    
    }

    /**
     * Providing access to recipient of courses
     * 
     * @param int $order_id
     * 
     * @param int $customer_id
     * 
     * @return void 
     */        
    function wdm_gift_add_course_access($order_id, $customer_id = null){
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
                            $this->wdm_remove_access($course_id, $customer_id );
                            ld_update_course_access( $recipient_user_exist, $course_id, $remove = true );
                            ld_update_course_access( $recipient_user_exist, $course_id);
                        }
                    }
                }
                $message =  $this->wdm_mail_body($recipient_name , $gift_message, $recipient_email);

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
                            $this->wdm_remove_access($course_id, $customer_id );
                            ld_update_course_access( $user_id, $course_id);
                        }
                    }
                }
                $message =  $this->wdm_mail_body($recipient_name , $gift_message, $recipient_email, $recipient_password);

                $headers = array(
                    'Content-Type: text/html; charset=UTF-8',
                );

                wp_mail( $recipient_email, $order->get_billing_first_name() .' sends you a gift' , $message , $headers);
            }
        }
        
    }

    
    

}

new Wdm_Learndash_As_Gift_Woocommerce();