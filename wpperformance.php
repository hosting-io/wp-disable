<?php
/*
Plugin Name: WP Disable
Plugin URI: https://optimisation.io
Description: Improve WordPress performance by disabling unused items.
Author: pigeonhut, Jody Nesbitt, optimisation.io
Author URI:https://optimisation.io
Version: 1.2.22

Copyright (C) 2017 Optimisation.io

/** Load all of the necessary class files for the plugin */
spl_autoload_register('WpPerformance::autoload');
/**
 * Init Singleton Class.
 */
class WpPerformance
{
    private static $instance = false;

    const MIN_PHP_VERSION = '5.2.4';
    const MIN_WP_VERSION  = '4.3';
    const TEXT_DOMAIN     = 'wpperformance';
    const OPTION_KEY      = 'wpperformance_rev3a';
    const FILE            = __FILE__;

    /**
     * Singleton class
     */
    public static function getInstance()
    {
        session_start();
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor.
     * Initializes the plugin by setting localization, filters, and
     * administration functions.
     */
    private function __construct()
    {

        if (!$this->testHost()) {
            return;
        }
        add_action('init', array($this, 'textDomain'));
        register_uninstall_hook(__FILE__, array(__CLASS__, 'uninstall'));
        new WpPerformance_Admin;
    }

    /**
     * PSR-0 compliant autoloader to load classes as needed.
     *
     * @since  2.1
     *
     * @param  string  $classname  The name of the class
     * @return null    Return early if the class name does not start with the
     *                 correct prefix
     */
    public static function autoload($className)
    {
        if (__CLASS__ !== mb_substr($className, 0, strlen(__CLASS__))) {
            return;
        }

        $className = ltrim($className, '\\');
        $fileName  = '';
        $namespace = '';
        if ($lastNsPos = strrpos($className, '\\')) {
            $namespace = substr($className, 0, $lastNsPos);
            $className = substr($className, $lastNsPos + 1);
            $fileName  = str_replace('\\', DIRECTORY_SEPARATOR, $namespace);
            $fileName .= DIRECTORY_SEPARATOR;
        }
        $fileName .= str_replace('_', DIRECTORY_SEPARATOR, 'lib_' . $className);

        $fileName .= '.php';
        require $fileName;
    }

    /**
     * Loads the plugin text domain for translation
     */
    public function textDomain()
    {
        $domain = self::TEXT_DOMAIN;
        $locale = apply_filters('plugin_locale', get_locale(), $domain);
        load_textdomain(
            $domain,
            WP_LANG_DIR . '/' . $domain . '/' . $domain . '-' . $locale . '.mo'
        );
        load_plugin_textdomain(
            $domain,
            false,
            dirname(plugin_basename(__FILE__)) . '/lang/'
        );
    }

    /**
     * Fired when the plugin is uninstalled.
     */
    public function uninstall()
    {
        delete_option(self::OPTION_KEY);
    }

    // -------------------------------------------------------------------------
    // Environment Checks
    // -------------------------------------------------------------------------

    /**
     * Checks PHP and WordPress versions.
     */
    private function testHost()
    {
        // Check if PHP is too old
        if (version_compare(PHP_VERSION, self::MIN_PHP_VERSION, '<')) {
            // Display notice
            add_action('admin_notices', array(&$this, 'phpVersionError'));
            return false;
        }

        // Check if WordPress is too old
        global $wp_version;
        if (version_compare($wp_version, self::MIN_WP_VERSION, '<')) {
            add_action('admin_notices', array(&$this, 'wpVersionError'));
            return false;
        }
        return true;
    }

    /**
     * Displays a warning when installed on an old PHP version.
     */
    public function phpVersionError()
    {
        echo '<div class="error"><p><strong>';
        printf(
            'Error: %3$s requires PHP version %1$s or greater.<br/>' .
            'Your installed PHP version: %2$s',
            self::MIN_PHP_VERSION,
            PHP_VERSION,
            $this->getPluginName()
        );
        echo '</strong></p></div>';
    }

    /**
     * Displays a warning when installed in an old Wordpress version.
     */
    public function wpVersionError()
    {
        echo '<div class="error"><p><strong>';
        printf(
            'Error: %2$s requires WordPress version %1$s or greater.',
            self::MIN_WP_VERSION,
            $this->getPluginName()
        );
        echo '</strong></p></div>';
    }

    /**
     * Get the name of this plugin.
     *
     * @return string The plugin name.
     */
    private function getPluginName()
    {
        $data = get_plugin_data(self::FILE);
        return $data['Name'];
    }
}

add_action('plugins_loaded', array('WpPerformance', 'getInstance'));

// Register hook to schedule script in wp_cron()
register_activation_hook(__FILE__, 'activate_update_local_ga');

function activate_update_local_ga() {
    wp_clear_scheduled_hook( 'update_local_ga');
    if  (!wp_next_scheduled('update_local_ga')) {
        wp_schedule_event(time(), 'daily', 'update_local_ga');
    }
}


// Load update script to schedule in wp_cron()
add_action('update_local_ga', 'update_local_ga_script');
function update_local_ga_script() {
    include('includes/update_local_ga.php');
}

// Remove script from wp_cron upon plugin deactivation
register_deactivation_hook(__FILE__, 'deactivate_update_local_ga');

function deactivate_update_local_ga() {
    if  (wp_next_scheduled('update_local_ga')) {
        wp_clear_scheduled_hook('update_local_ga');
    }
}

// Remove script from wp_cron if option is selected
$settings = get_option('wpperformance_rev3a_settings', array());
$caos_remove_wp_cron = esc_attr($settings['caos_remove_wp_cron']);

switch ($caos_remove_wp_cron) {
    case "on":
        if (wp_next_scheduled('update_local_ga')) {
            wp_clear_scheduled_hook('update_local_ga');
        }
        break;
    default:
        if (!wp_next_scheduled('update_local_ga')) {
            wp_schedule_event(time(), 'daily', 'update_local_ga');
        }
        break;
}

// Generate tracking code and add to header/footer (default is header)
function add_ga_header_script() {
    $settings = get_option('wpperformance_rev3a_settings', array());
    $ds_track_admin = esc_attr($settings['ds_track_admin']);
    // If user is admin we don't want to render the tracking code, when option is disabled.
    if (current_user_can('manage_options') && (!$ds_track_admin)) return;

    $ds_tracking_id = esc_attr($settings['ds_tracking_id']);

    $ds_adjusted_bounce_rate = esc_attr($settings['ds_adjusted_bounce_rate']);
    $ds_anonymize_ip = esc_attr($settings['ds_anonymize_ip']);
    $caos_disable_display_features = esc_attr($settings['caos_disable_display_features']);

    echo "<!-- This site is running CAOS: Complete Analytics Optimization Suite for Wordpress -->";

    echo "<script>
            (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
            (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
            m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
            })(window,document,'script','" . plugin_dir_url(__FILE__) . "cache/local-ga.js','ga');";

    echo "ga('create', '" . $ds_tracking_id . "', 'auto');";

    echo $caos_disable_display_features_code = ($caos_disable_display_features == "on") ? "ga('set', 'displayFeaturesTask', null);
" : "";

    echo $ds_anonymize_ip_code = ($ds_anonymize_ip == "on") ? "ga('set', 'anonymizeIp', true);" : "";

    echo "ga('send', 'pageview');";

    echo $ds_abr_code = ($ds_adjusted_bounce_rate) ? 'setTimeout("ga(' . "'send','event','adjusted bounce rate','" . $ds_adjusted_bounce_rate . " seconds')" . '"' . "," . $ds_adjusted_bounce_rate * 1000 . ");" : "";

    echo "</script>";
}

$ds_script_position = esc_attr($settings['ds_script_position']);
$ds_enqueue_order = (esc_attr($settings['ds_enqueue_order'])) ? esc_attr($settings['ds_enqueue_order']) : 0;

switch ($ds_script_position) {
    case "footer":
        add_action('wp_footer', 'add_ga_header_script', $ds_enqueue_order);
        break;
    default:
        add_action('wp_head', 'add_ga_header_script', $ds_enqueue_order);
        break;
}
