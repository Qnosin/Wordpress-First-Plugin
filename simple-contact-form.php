<?php 
/**
 * Plugin Name: Simple Contact Form
 * Description: Simple contact form tutorial
 * Author: Jakub Putaj
 * Version: 1.0.0
 * Text Domain: simple-contact-form
*/
if(!defined('ABSPATH')){
    exit;
}

class SimpleContactForm{
    public function __construct(){
        //Create Custom Post Type
        add_action('init', array($this,'create_custom_post_type'));
        //Add assets (js,css,etc)
        add_action('wp_enqueue_scripts', array($this,'load_assets'));

        //Add shortcode
        add_shortcode('contact-form', array($this,'load_shortcode'));
        // Load Js
        add_action('wp_footer',array($this,'load_scripts'));

        add_action('rest_api_init',array($this,'register_rest_api'));
    }
    public function create_custom_post_type(){
        $args = array(
            'public'=> true,
            'has_archive' => true,
            'supports'=> array('title'),
            'exclude_from_search' => true,
            'publicly_queryable' => false,
            'capability' => 'manage_options',
            'labels' => array(
                'name' => 'Contact Form',
                'singular_name' => 'Contact Form Entry',
            ),
            'menu_icon' => 'dashicons-media-text',
        );
        register_post_type('simple_contact_form',$args);

    }
    public function load_assets(){
        wp_enqueue_style('simple-contact-form', plugin_dir_url(__FILE__) . 'css/simple-contact-form.css',array(), 1 ,'all');
        wp_enqueue_script('simple-contact-form',  plugin_dir_url(__FILE__) . 'js/simple-contact-form.js', array('jquery') , 1 ,true);
    }

    public function load_shortcode()
    { 
        return "
                <div class='simple-contact-form'>
                <h1>Send us an email</h1>
                <p>Please fill the below Form</p>
                <form id='simple-contact-form__form' class='simple-contact-form__form'>
                    <input name='name' type='text' placeholder='Name'> 
                    <input name='email' type='email' placeholder='email'>
                    <input name='tel' type='tel' placeholder='Phone'>
                    <textarea name='desc' placeholder='Type your message'></textarea>
                    <button type='submit'>Send</button>
                </form>
                </div>
                "  ;
    }
    public function load_scripts()
    {
        echo  "
        <script>
        var nonce = '<?php echo wp_create_nonce('wp_rest') ?>'
        jQuery('#simple-contact-form__form').submit(function(event){
            event.preventDefault();
            var form = jQuery(this).serialize();
            jQuery.ajax({
                method:'post',
                url: '<?php echo get_rest_url(null,'simple-contact-form/v1/send-email'); ?> ',
                headers: {'X-WP-Nonce': nonce},
                data: form
            })
        });
        </script>
        ";
    }

    public function register_rest_api(){
        register_rest_route('simple-contact-form/v1','send-email',array(
            'methods' => 'POST',
            'callback' => array($this,'handle_contact_form'),
        ));
    }
    public function handle_contact_form($data){
        echo "Hello this endpoint is working";
    }
}

new SimpleContactForm;