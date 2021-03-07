<?php
/*
Plugin Name: Weather Echeverri
Plugin URI: https://github.com/echeverrisantiago
Description: Is in honour to me develop this plugin
Author: Santiago Echeverri
Version: 1.0
Author URI: https://echeverriwebservice.com/
*/

defined('ABSPATH') or die("You are not allowed to be here.");

class Weather
{

    public $data;

    function __construct()
    {
        add_action('init', array($this, 'historical_cpt'));
        add_action('admin_menu', array($this, 'dbi_add_settings_page'), 1); /* 1 */
        add_action('admin_init', array($this, 'dbi_register_settings'));
        add_action('rest_api_init', array($this, 'rest_routes') );

    }

    function my_theme_redirect() {
        global $wp;
        $plugindir = dirname( __FILE__ );

        //A Specific Custom Post Type
            $templatefilename = 'page-historical.php';
            if (file_exists(TEMPLATEPATH . '/resources/' . $templatefilename)) {
                $return_template = TEMPLATEPATH . '/resources/' . $templatefilename;
            } else {
                $return_template = $plugindir . '/resources/' . $templatefilename;
            }
            $this->do_theme_redirect($return_template);

        //A Custom Taxonomy Page
    }

    function do_theme_redirect($url) {
        global $post, $wp_query;
        if (have_posts()) {
            include($url);
            die();
        } else {
            $wp_query->is_404 = true;
        }
    }

    function activate()
    {
        $this->historical_cpt();
        $this->rest_routes();
        $this->insert_page();
        flush_rewrite_rules();
    }

    function register()
    {
        add_action( 'template_redirect', array($this,'my_theme_redirect' ));
        add_action('init', array($this, 'enqueue'));
    }

    function insert_page()
    {
        $page = array(
            'post_title'    => 'historical',
            'post_content'  => 'This is my post.',
            'post_status'   => 'publish',
            'post_author'   => 1,
            'post_category' => array( 8,39 ),
            'post_type' => 'page',
            'page_template' => 'page-historical.php'
        );

            wp_insert_post($page);

    }

    function rest_routes()
    {
        register_rest_route('ht/v1', 'historical', array(
            'methods' => WP_REST_SERVER::CREATABLE,
            'args' => array(),
            'callback' => array($this, 'register_routes_add'),
            'permission_callback' => function () {
                return true;
              }
        ));

        register_rest_route('ht/v1', 'historicalGet', array(
            'methods' => WP_REST_SERVER::READABLE,
            'callback' => array($this, 'get_historical_paginated')
        ));
    }

    function get_historical_paginated(){
        $from = $_GET['from'];

        $arr = [];

        $args = array(
            'post_type' => 'historical',
            'posts_per_page' => 5,
            'post_status' => 'publish',
            'paged' => $from
        );

        $data = new WP_Query($args);
        foreach($data->posts as $i => $pag):
            $arrT = [];

            $temp = get_post_meta($pag->ID,'temp')[0];
            $temp_min = get_post_meta($pag->ID,'temp_min')[0];
            $temp_max = get_post_meta($pag->ID,'temp_max')[0];
            $pressure = get_post_meta($pag->ID,'pressure')[0];
            $name = get_post_meta($pag->ID,'name')[0];
            $humidity = get_post_meta($pag->ID,'humidity')[0];
            $city_id = get_post_meta($pag->ID,'city_id')[0];
            $main = get_post_meta($pag->ID,'main')[0];
            $description = get_post_meta($pag->ID,'description')[0];

            $arrT = [
                'temp' => $temp,
                'name' => $name,
                'temp_min' => $temp_min,
                'temp_max' => $temp_max,
                'pressure' => $pressure,
                'humidity' => $humidity,
                'city_id' => $city_id,
                'main' => $main,
                'description' => $description,
            ];

            array_push($arr,$arrT);
        endforeach;
        return $arr ? $arr : false;
    }

    function register_routes_add(WP_REST_Request $request)
    {
        if (isset($request['id'])) :
            $historicalID = $request['id'];
            $name = $request['name'];
            $temp_max = $request['temp_max'];
            $temp_min = $request['temp_min'];
            $temp = $request['temp'];
            $pressure = $request['pressure'];
            $humidity = $request['humidity'];
            $city_id = $request['city_id'];
            $main = $request['main'];
            $description = $request['description'];

            $verifyHistorical = array(
                'post_type' => 'historical',
                'post_status' => 'publish',
                'p' => $historicalID
            );

            $historicalData = new WP_Query($verifyHistorical);

            if($historicalData->posts):
                update_post_meta($historicalID,'temp',$temp);
            update_post_meta($historicalID,'temp_min',$temp_min);
            update_post_meta($historicalID,'temp_max',$temp_max);
            update_post_meta($historicalID,'pressure',$pressure);
            update_post_meta($historicalID,'humidity',$humidity);
            update_post_meta($historicalID,'city_id',$city_id);
            update_post_meta($historicalID,'main',$main);
            update_post_meta($historicalID,'description',$description);
            update_post_meta($historicalID,'name',$name);
        endif;
        return true;
        endif;
    }

    function deactivate()
    {
        flush_rewrite_rules();
    }

    function uninstall()
    {
        $mycustomposts = get_posts( array( 'post_type' => 'historical', 'numberposts' => -1));
        foreach( $mycustomposts as $mypost ) {
            // Delete's each post.
           wp_delete_post( $mypost->ID, true);
           // Set to False if you want to send them to Trash.
          }
        flush_rewrite_rules();
    }

    function historical_cpt()
    {

        $labels = array(
            'name' => __('Historical', 'santiago-echeverri'),
            'singular_name' => __('Book', 'santiago-echeverri'),
            'all_items' => __('All', 'santiago-echeverri'),
            'add_new' => __('Add new', 'santiago-echeverri'),
            'add_new_item' => __('Add new', 'santiago-echeverri'),
            'edit' => __('Edit', 'santiago-echeverri'),
            'edit_item' => __('Edit', 'santiago-echeverri'),
            'new_item' => __('New', 'santiago-echeverri'),
            'view_item' => __('See', 'santiago-echeverri'),
            'search_items' => __('Search', 'santiago-echeverri'),
            'not_found' => __('Not found results', 'santiago-echeverri'),
            'not_found_in_trash' => __('Not found results in bin', 'santiago-echeverri'),
        );

        $args = array(
            'labels' => $labels,
            'description' => __('Historical description', 'santiago-echeverri'),
            'public' => true,
            'publicly_queryable' => true,
            'exclude_from_search' => false,
            'show_ui' => true,
            'query_var' => true,
            'menu_position' => 5,
            'menu_icon' => 'dashicons-clock',
            'rewrite' => array('slug' => 'historical', 'with_front' => false),
            'has_archive' => false,
            'capability_type' => 'post',
            'hierarchical' => false,
            'show_in_rest' => true,
            'rest_controller_class' => 'WP_REST_Posts_Controller',
            'rest_base'             => 'historical',
            'supports' => array('title', 'author', 'custom-fields', 'revisions')
        );

        register_post_type('historical', $args);
    }

    function enqueue()
    {
        wp_enqueue_style('weather-style', plugins_url('/assets/css/style.css', __FILE__));
        wp_enqueue_style('font-awesome','https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css');
        wp_enqueue_script('jquery-sc','https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js');
        wp_enqueue_script('weather-script', plugins_url('/assets/js/script.js   ', __FILE__));
        wp_localize_script('weather-script', 'pluginData', array(
            'nonce' => wp_create_nonce('wp_rest'),
            'site_url' => site_url(),
            'pagination' => 1
        ));
    }
    /* 2 */
    function dbi_add_settings_page()
    {
        add_options_page('weather configuration', 'Weather configuration', 'manage_options', 'weather', array($this, 'dbi_render_plugin_settings_page'));
    }
    /* 3 */
    function dbi_render_plugin_settings_page()
    {
?>
        <h2>Weather Settings</h2>
        <form action="<?php ?>" method="post" id="form-settings">
            <?php
            settings_fields('dbi_example_plugin_options');
            do_settings_sections('dbi_example_plugin');
            wp_nonce_field('nonce-historic-generate', 'nonce'); ?>
            <input name="submit" id="store-historical" class="hidden button button-primary" type="submit" value="<?php esc_attr_e('Conectar'); ?>" />
        </form>
<?php
    }
    /* 4 */
    /*
API FORM
*/
    function dbi_register_settings()
    {
        register_setting('dbi_example_plugin_options', 'dbi_example_plugin_options', 'dbi_example_plugin_options_validate');
        add_settings_section('api_settings', false, array($this, 'dbi_plugin_section_text'), 'dbi_example_plugin');

        add_settings_field('weather_plugin_api', 'API', array($this, 'weather_plugin_api'), 'dbi_example_plugin', 'api_settings');
        add_settings_field('weather_plugin_api_key', 'API Key', array($this, 'weather_plugin_api_key'), 'dbi_example_plugin', 'api_settings');
        add_settings_field('weather_plugin_city_id', 'City id', array($this, 'weather_plugin_city_id'), 'dbi_example_plugin', 'api_settings');
    }

    function dbi_example_plugin_options_validate($input)
    {
        $newinput['api_key'] = trim($input['api_key']);
        if (!preg_match('/^[a-z0-9]{32}$/i', $newinput['api_key'])) {
            $newinput['api_key'] = '';
        }

        return $newinput;
    }

    function dbi_plugin_section_text()
    {
        echo '<p>Here you can set all the options for using the API</p>';
    }

    function weather_plugin_api()
    {
        $options = get_option('dbi_example_plugin_options');
        echo "<input id='weather_plugin_api' name='dbi_example_plugin_options[api]' type='text' value='" . esc_attr($options['api']) . "' />";
    }

    function weather_plugin_api_key()
    {
        $options = get_option('dbi_example_plugin_options');
        echo "<input id='weather_plugin_api_key' name='dbi_example_plugin_options[api_key]' type='text' value='" . esc_attr($options['api_key']) . "' />";
    }

    function weather_plugin_city_id()
    {
        $options = get_option('dbi_example_plugin_options');
        echo "<input id='weather_plugin_city_id' name='dbi_example_plugin_options[city_id]' type='text' value='" . esc_attr($options['city_id']) . "' />";
    }
}

if (class_exists('Weather')) {
    $weather =  new Weather();
    $weather->register();
}

register_activation_hook(__FILE__, array($weather, 'activate'));
register_deactivation_hook(__FILE__, array($weather, 'deactivate'));
register_uninstall_hook(__FILE__, array($weather, 'uninstall'));
