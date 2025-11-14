<?php
/**
 * Plugin Name: KING OF DYNAMIC BOOKING SYSTEM
 * Plugin URI: https://yourwebsite.com/king-dynamic-booking-system
 * Description: Advanced booking system with membership, subscriptions, waiting lists, and automation workflows.
 * Version: 1.0.0
 * Author: RASEL AHMMED
 * Author URI: https://yourwebsite.com
 * Text Domain: king-dynamic-booking-system
 * Domain Path: /languages
 * Requires at least: 6.0
 * Requires PHP: 8.0
 * License: GPL v2 or later
 * 
 * @package KDBS
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Plugin constants
define('KDBS_VERSION', '1.0.0');
define('KDBS_PLUGIN_URL', plugin_dir_url(__FILE__));
define('KDBS_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('KDBS_PLUGIN_BASENAME', plugin_basename(__FILE__));
define('KDBS_TEXT_DOMAIN', 'king-dynamic-booking-system');

// Auto-loader for KDBS classes
spl_autoload_register(function ($class_name) {
    if (strpos($class_name, 'KDBS_') === 0) {
        $class_file = 'class-' . strtolower(str_replace('_', '-', $class_name)) . '.php';
        
        // Check different directories for the class file
        $directories = [
            'includes/core/',
            'includes/database/',
            'includes/models/',
            'includes/controllers/',
            'includes/utilities/',
            'admin/',
            'public/',
            'modules/booking-engine/',
            'modules/membership/',
            'modules/subscription/',
            'modules/wallet/',
            'modules/automation/',
            'modules/waiting-list/',
            'modules/reporting/',
            'modules/woocommerce/',
            'modules/form-builder/',
            'modules/extensions/',
            'endpoints/api/v1/'
        ];
        
        foreach ($directories as $directory) {
            $file_path = KDBS_PLUGIN_PATH . $directory . $class_file;
            if (file_exists($file_path)) {
                require_once $file_path;
                break;
            }
        }
    }
});

/**
 * Main KDBS Class
 * 
 * Primary controller for the KING OF DYNAMIC BOOKING SYSTEM
 * All code uses unique KDBS_ prefix to prevent conflicts
 */
final class KDBS_Core {
    
    /**
     * The single instance of the class
     */
    private static $instance = null;
    
    /**
     * KDBS loader instance
     */
    public $kdbs_loader;
    
    /**
     * KDBS security instance
     */
    public $kdbs_security;
    
    /**
     * Main KDBS Instance
     */
    public static function kdbs_instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * KDBS Constructor
     */
    public function __construct() {
        $this->kdbs_define_constants();
        $this->kdbs_includes();
        $this->kdbs_init_hooks();
        $this->kdbs_init_components();
        
        do_action('kdbs_loaded');
    }
    
    /**
     * Define plugin constants
     */
    private function kdbs_define_constants() {
        // Database table prefixes
        define('KDBS_DB_PREFIX', 'kdbs_');
        define('KDBS_BOOKING_TABLE', $GLOBALS['wpdb']->prefix . KDBS_DB_PREFIX . 'bookings');
        define('KDBS_SERVICES_TABLE', $GLOBALS['wpdb']->prefix . KDBS_DB_PREFIX . 'services');
        define('KDBS_CUSTOMERS_TABLE', $GLOBALS['wpdb']->prefix . KDBS_DB_PREFIX . 'customers');
        define('KDBS_MEMBERSHIPS_TABLE', $GLOBALS['wpdb']->prefix . KDBS_DB_PREFIX . 'memberships');
        define('KDBS_WAITING_LIST_TABLE', $GLOBALS['wpdb']->prefix . KDBS_DB_PREFIX . 'waiting_list');
        
        // Additional constants
        define('KDBS_MIN_PHP_VERSION', '8.0');
        define('KDBS_MIN_WP_VERSION', '6.0');
        define('KDBS_ASSETS_VERSION', KDBS_VERSION);
    }
    
    /**
     * Include required files
     */
    private function kdbs_includes() {
        // Core files
        require_once KDBS_PLUGIN_PATH . 'includes/core/class-kdbs-loader.php';
        require_once KDBS_PLUGIN_PATH . 'includes/core/class-kdbs-activator.php';
        require_once KDBS_PLUGIN_PATH . 'includes/core/class-kdbs-deactivator.php';
        require_once KDBS_PLUGIN_PATH . 'includes/core/class-kdbs-i18n.php';
        require_once KDBS_PLUGIN_PATH . 'includes/core/class-kdbs-security.php';
        require_once KDBS_PLUGIN_PATH . 'includes/core/class-kdbs-hook-manager.php';
        
        // Database files
        require_once KDBS_PLUGIN_PATH . 'includes/database/class-kdbs-schema.php';
        require_once KDBS_PLUGIN_PATH . 'includes/database/class-kdbs-migrations.php';
        
        // Utility files
        require_once KDBS_PLUGIN_PATH . 'includes/utilities/class-kdbs-helpers.php';
        require_once KDBS_PLUGIN_PATH . 'includes/utilities/class-kdbs-validator.php';
        require_once KDBS_PLUGIN_PATH . 'includes/utilities/class-kdbs-logger.php';
    }
    
    /**
     * Initialize hooks
     */
    private function kdbs_init_hooks() {
        register_activation_hook(__FILE__, array('KDBS_Activator', 'kdbs_activate'));
        register_deactivation_hook(__FILE__, array('KDBS_Deactivator', 'kdbs_deactivate'));
        
        add_action('init', array($this, 'kdbs_init'));
        add_action('wp_enqueue_scripts', array($this, 'kdbs_enqueue_scripts'));
        add_action('admin_enqueue_scripts', array($this, 'kdbs_admin_enqueue_scripts'));
    }
    
    /**
     * Initialize core components
     */
    private function kdbs_init_components() {
        $this->kdbs_loader = new KDBS_Loader();
        $this->kdbs_security = new KDBS_Security();
        $this->kdbs_i18n = new KDBS_i18n();
        
        // Initialize internationalization
        $this->kdbs_i18n->kdbs_load_textdomain();
    }
    
    /**
     * Initialize plugin
     */
    public function kdbs_init() {
        // Check system requirements
        $this->kdbs_check_requirements();
        
        // Initialize hook manager
        $kdbs_hook_manager = new KDBS_Hook_Manager();
        $kdbs_hook_manager->kdbs_register_core_hooks();
        
        do_action('kdbs_init');
    }
    
    /**
     * Check system requirements
     */
    private function kdbs_check_requirements() {
        // Check PHP version
        if (version_compare(PHP_VERSION, KDBS_MIN_PHP_VERSION, '<')) {
            add_action('admin_notices', array($this, 'kdbs_php_version_notice'));
            return;
        }
        
        // Check WordPress version
        if (version_compare(get_bloginfo('version'), KDBS_MIN_WP_VERSION, '<')) {
            add_action('admin_notices', array($this, 'kdbs_wp_version_notice'));
            return;
        }
    }
    
    /**
     * PHP version notice
     */
    public function kdbs_php_version_notice() {
        ?>
        <div class="error">
            <p>
                <?php 
                printf(
                    esc_html__('KING OF DYNAMIC BOOKING SYSTEM requires PHP version %s or higher. Your current version is %s.', KDBS_TEXT_DOMAIN),
                    KDBS_MIN_PHP_VERSION,
                    PHP_VERSION
                );
                ?>
            </p>
        </div>
        <?php
    }
    
    /**
     * WordPress version notice
     */
    public function kdbs_wp_version_notice() {
        ?>
        <div class="error">
            <p>
                <?php 
                printf(
                    esc_html__('KING OF DYNAMIC BOOKING SYSTEM requires WordPress version %s or higher. Your current version is %s.', KDBS_TEXT_DOMAIN),
                    KDBS_MIN_WP_VERSION,
                    get_bloginfo('version')
                );
                ?>
            </p>
        </div>
        <?php
    }
    
    /**
     * Enqueue frontend scripts and styles
     */
    public function kdbs_enqueue_scripts() {
        wp_enqueue_style(
            'kdbs-frontend',
            KDBS_PLUGIN_URL . 'public/css/kdbs-frontend.css',
            array(),
            KDBS_ASSETS_VERSION
        );
        
        wp_enqueue_script(
            'kdbs-frontend',
            KDBS_PLUGIN_URL . 'public/js/kdbs-frontend.js',
            array('jquery'),
            KDBS_ASSETS_VERSION,
            true
        );
        
        // Localize script for AJAX
        wp_localize_script('kdbs-frontend', 'kdbs_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('kdbs_nonce'),
            'text_domain' => KDBS_TEXT_DOMAIN
        ));
    }
    
    /**
     * Enqueue admin scripts and styles
     */
    public function kdbs_admin_enqueue_scripts($hook) {
        // Only load on KDBS admin pages
        if (strpos($hook, 'kdbs') === false) {
            return;
        }
        
        wp_enqueue_style(
            'kdbs-admin',
            KDBS_PLUGIN_URL . 'admin/css/kdbs-admin.css',
            array(),
            KDBS_ASSETS_VERSION
        );
        
        wp_enqueue_script(
            'kdbs-admin',
            KDBS_PLUGIN_URL . 'admin/js/kdbs-admin.js',
            array('jquery'),
            KDBS_ASSETS_VERSION,
            true
        );
    }
    
    /**
     * Get loader instance
     */
    public function kdbs_get_loader() {
        return $this->kdbs_loader;
    }
}

/**
 * Main function to get KDBS instance
 */
function KDBS() {
    return KDBS_Core::kdbs_instance();
}

// Initialize KDBS
$GLOBALS['kdbs'] = KDBS();
