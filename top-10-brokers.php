<?php
/*
Plugin Name: Top 10 Brokers Table
Description: A plugin to display a customizable table of top-rated brokers.
Version: 1.0
Author: David Balian
Text Domain: top10-brokers
Domain Path: /languages
License: GPL v2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Requires at least: 5.0
Requires PHP: 7.0
*/

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Define plugin constants
define('TOP10_BROKERS_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('TOP10_BROKERS_PLUGIN_URL', plugin_dir_url(__FILE__));
define('TOP10_BROKERS_VERSION', '1.0');
define('TOP10_BROKERS_MIN_WP_VERSION', '5.0');
define('TOP10_BROKERS_MIN_PHP_VERSION', '7.0');

class Top10_Brokers_Core {
    private static $instance = null;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        // Check requirements before proceeding
        if (!$this->check_requirements()) {
            return;
        }

        $this->init_hooks();
    }

    private function check_requirements() {
        global $wp_version;

        if (version_compare(PHP_VERSION, TOP10_BROKERS_MIN_PHP_VERSION, '<')) {
            add_action('admin_notices', function() {
                $this->display_error_notice(sprintf(
                    __('Top 10 Brokers plugin requires PHP version %s or higher. You are running version %s', 'top10-brokers'),
                    TOP10_BROKERS_MIN_PHP_VERSION,
                    PHP_VERSION
                ));
            });
            return false;
        }

        if (version_compare($wp_version, TOP10_BROKERS_MIN_WP_VERSION, '<')) {
            add_action('admin_notices', function() {
                global $wp_version;
                $this->display_error_notice(sprintf(
                    __('Top 10 Brokers plugin requires WordPress version %s or higher. You are running version %s', 'top10-brokers'),
                    TOP10_BROKERS_MIN_WP_VERSION,
                    $wp_version
                ));
            });
            return false;
        }

        return true;
    }

    private function display_error_notice($message) {
        echo '<div class="error"><p>' . esc_html($message) . '</p></div>';
    }

    public static function log($message, $type = 'info') {
        if (defined('WP_DEBUG') && WP_DEBUG === true) {
            error_log(sprintf('[Top 10 Brokers] [%s] %s', strtoupper($type), $message));
        }
    }

    private function init_hooks() {
        // Initialize plugin functionality
        add_action('init', array($this, 'init'));
        add_action('plugins_loaded', array($this, 'load_textdomain'));
        
        // Register activation and deactivation hooks
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
    }

    public function init() {
        $this->register_post_types();
        $this->register_shortcode();
    }

    public function load_textdomain() {
        load_plugin_textdomain('top10-brokers', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }

    public function activate() {
        // Activation tasks
        $this->register_post_types();
        flush_rewrite_rules();
        
        // Set default options
        if (!get_option('top10_brokers_options')) {
            $default_options = array(
                'top10_brokers_post_type' => 'broker',
                'top10_brokers_taxonomy' => 'broker-category',
                'top10_brokers_limit' => 10,
                'top10_brokers_rating_field' => 'rating',
                'top10_brokers_button_text' => __('Learn More', 'top10-brokers')
            );
            update_option('top10_brokers_options', $default_options);
        }

        self::log('Plugin activated successfully', 'info');
    }

    public function deactivate() {
        flush_rewrite_rules();
        self::log('Plugin deactivated', 'info');
    }

    private function register_post_types() {
        // Register Broker post type
        register_post_type('broker', array(
            'labels' => array(
                'name' => __('Brokers', 'top10-brokers'),
                'singular_name' => __('Broker', 'top10-brokers'),
            ),
            'public' => true,
            'has_archive' => true,
            'menu_icon' => 'dashicons-businessman',
            'supports' => array('title', 'editor', 'thumbnail', 'custom-fields'),
            'show_in_rest' => true,
        ));

        // Register Broker Category taxonomy
        register_taxonomy('broker-category', 'broker', array(
            'labels' => array(
                'name' => __('Broker Categories', 'top10-brokers'),
                'singular_name' => __('Broker Category', 'top10-brokers'),
            ),
            'public' => true,
            'hierarchical' => true,
            'show_in_rest' => true,
        ));
    }

    private function register_shortcode() {
        add_shortcode('top10_brokers_table', 'top10_brokers_render_table');
    }
}

// Initialize the plugin
Top10_Brokers_Core::get_instance();

// Include necessary files
include_once TOP10_BROKERS_PLUGIN_DIR . 'admin-settings.php';
include_once TOP10_BROKERS_PLUGIN_DIR . 'shortcode-render.php';
include_once TOP10_BROKERS_PLUGIN_DIR . 'ajax-handler.php';

// Enqueue scripts and styles - only when needed
function top10_brokers_enqueue_assets() {
    global $post;
    
    // Only load assets if shortcode is present in content or we're in admin
    if (!is_admin() && (!is_a($post, 'WP_Post') || !has_shortcode($post->post_content, 'top10_brokers_table'))) {
        return;
    }

    // Get file modification time for cache busting
    $css_file = TOP10_BROKERS_PLUGIN_DIR . 'assets/css/style.css';
    $js_file = TOP10_BROKERS_PLUGIN_DIR . 'assets/js/script.js';
    $star_css_file = TOP10_BROKERS_PLUGIN_DIR . 'assets/css/star-rating.css';
    $star_js_file = TOP10_BROKERS_PLUGIN_DIR . 'assets/js/star-rating.js';
    
    $css_version = file_exists($css_file) ? filemtime($css_file) : TOP10_BROKERS_VERSION;
    $js_version = file_exists($js_file) ? filemtime($js_file) : TOP10_BROKERS_VERSION;
    $star_css_version = file_exists($star_css_file) ? filemtime($star_css_file) : TOP10_BROKERS_VERSION;
    $star_js_version = file_exists($star_js_file) ? filemtime($star_js_file) : TOP10_BROKERS_VERSION;

    // Register styles first
    wp_register_style(
        'top10-brokers-star-rating',
        TOP10_BROKERS_PLUGIN_URL . 'assets/css/star-rating.css',
        array(),
        $star_css_version
    );

    wp_register_style(
        'top10-brokers-style',
        TOP10_BROKERS_PLUGIN_URL . 'assets/css/style.css',
        array('top10-brokers-star-rating'),
        $css_version
    );

    // Register scripts
    wp_register_script(
        'top10-brokers-star-rating',
        TOP10_BROKERS_PLUGIN_URL . 'assets/js/star-rating.js',
        array(),
        $star_js_version,
        true
    );

    wp_register_script(
        'top10-brokers-script',
        TOP10_BROKERS_PLUGIN_URL . 'assets/js/script.js',
        array('jquery', 'top10-brokers-star-rating'),
        $js_version,
        true
    );

    // Enqueue the registered assets
    wp_enqueue_style('top10-brokers-style');
    wp_enqueue_style('top10-brokers-star-rating');
    wp_enqueue_script('top10-brokers-star-rating');
    wp_enqueue_script('top10-brokers-script');

    // Localize script with dynamic data
    wp_localize_script('top10-brokers-script', 'top10BrokersAjax', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('top10_brokers_nonce'),
        'i18n' => array(
            'error' => __('An error occurred. Please try again.', 'top10-brokers'),
            'loading' => __('Loading...', 'top10-brokers'),
            'no_results' => __('No results found.', 'top10-brokers')
        )
    ));

    // Add RTL support
    if (is_rtl()) {
        wp_enqueue_style(
            'top10-brokers-rtl',
            TOP10_BROKERS_PLUGIN_URL . 'assets/css/rtl.css',
            array('top10-brokers-style'),
            $css_version
        );
    }

    // Log asset loading in debug mode
    Top10_Brokers_Core::log('Assets loaded successfully');
}
add_action('wp_enqueue_scripts', 'top10_brokers_enqueue_assets');
