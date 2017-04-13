<?php
class WpPerformance_Admin
{
    public function __construct()
    {
        add_action('admin_menu', array($this, 'menu'));
        add_action('init', array($this, 'wp_performance_disable_emojis'));
        add_action('init', array($this, 'wp_performance_speed_stop_loading_wp_embed'));
        add_filter('script_loader_src', array($this, 'wp_performance_remove_script_version'), 15, 1);
        add_filter('style_loader_src', array($this, 'wp_performance_remove_script_version'), 15, 1);
        add_action('init', array($this, 'wp_performace_disable_woo_stuffs'));
        add_action('init', array($this, 'wp_performance_optimize_cleanups'));
        add_action("wp_loaded", array($this, 'wp_performance_disable_google_maps'));

    }

    public function menu()
    {
        add_management_page(__('WP Disable', 'wpper'), __('Optimisation.io - Disabler', 'wpper'), 'manage_options', 'wpperformance', array($this, 'addsettings'));
        add_submenu_page('', __('Update Settings', 'wpper'), __('Update Settings', 'wpper'), 'manage_options', 'updatewpperformance-settings', array($this, 'updatesettings'));
    }

    public function addsettings()
    {
        $settings = get_option(WpPerformance::OPTION_KEY . '_settings', array());
        $data     = array('settings' => $settings);
        echo WpPerformance_View::render('admin_settings', $data);
    }

    public function updatesettings()
    {
        $array = array(
            'disable_gravatars'                => ($_POST['disable_gravatars']) ? 1 : 0,
            'disable_emoji'                    => ($_POST['disable_emoji']) ? 1 : 0,
            'disable_embeds'                   => ($_POST['disable_embeds']) ? 1 : 0,
            'remove_querystrings'              => ($_POST['remove_querystrings']) ? 1 : 0,
            'lazyload'                         => ($_POST['lazyload']) ? 1 : 0,
            'default_ping_status'              => ($_POST['default_ping_status']) ? 1 : 0,
            'close_comments'                   => ($_POST['close_comments']) ? 1 : 0,
            'paginate_comments'                => ($_POST['paginate_comments']) ? 1 : 0,
            'disable_woocommerce_non_pages'    => ($_POST['disable_woocommerce_non_pages']) ? 1 : 0,
            'remove_rsd'                       => ($_POST['remove_rsd']) ? 1 : 0,
            'remove_windows_live_writer'       => ($_POST['remove_windows_live_writer']) ? 1 : 0,
            'remove_wordpress_generator_tag'   => ($_POST['remove_wordpress_generator_tag']) ? 1 : 0,
            'remove_shortlink_tag'             => ($_POST['remove_shortlink_tag']) ? 1 : 0,
            'remove_wordpress_api_from_header' => ($_POST['remove_wordpress_api_from_header']) ? 1 : 0,
            'disable_rss'                      => ($_POST['disable_rss']) ? 1 : 0,
            'disable_xmlrpc'                   => ($_POST['disable_xmlrpc']) ? 1 : 0,
            'disable_autosave'                 => ($_POST['disable_autosave']) ? 1 : 0,
            'disable_revisions'                => ($_POST['disable_revisions']) ? 1 : 0,
            'disable_woocommerce_reviews'      => ($_POST['disable_woocommerce_reviews']) ? 1 : 0,
            'disable_google_maps'              => ($_POST['disable_google_maps']) ? 1 : 0,
            'ds_tracking_id'                   => sanitize_text_field($_POST['ds_tracking_id']),
            'ds_adjusted_bounce_rate'          => sanitize_text_field($_POST['ds_adjusted_bounce_rate']),
            'ds_enqueue_order'                 => sanitize_text_field($_POST['ds_enqueue_order']),
            'ds_anonymize_ip'                  => sanitize_text_field($_POST['ds_anonymize_ip']),
            'ds_script_position'               => sanitize_text_field($_POST['ds_script_position']),
            'caos_disable_display_features'    => sanitize_text_field($_POST['caos_disable_display_features']),
            'ds_track_admin'                   => sanitize_text_field($_POST['ds_track_admin']),
            'caos_remove_wp_cron'              => sanitize_text_field($_POST['caos_remove_wp_cron']),

        );
        if ($_POST['disable_gravatars'] == 1) {
            update_option('show_avatars', false);
        } else {
            update_option('show_avatars', true);
        }

        if ($_POST['default_ping_status'] == 1) {
            update_option('default_ping_status', 'close');
        } else {
            update_option('default_ping_status', 'open');
        }

        if ($_POST['close_comments'] == 1) {
            update_option('close_comments_for_old_posts', true);
            update_option('close_comments_days_old', 28);
        } else {
            update_option('close_comments_for_old_posts', false);
            update_option('close_comments_days_old', 14);
        }

        if ($_POST['paginate_comments'] == 1) {
            update_option('page_comments', true);
            update_option('comments_per_page', 20);
        } else {
            update_option('page_comments', false);
            update_option('comments_per_page', 50);
        }

        $options  = $array;
        $settings = update_option(WpPerformance::OPTION_KEY . '_settings', $options);
        $this->addMessage('Settings updated successfully');

        $this->redirectUrl(admin_url('admin.php?page=wpperformance'));
    }

    private function sendemail($to, $subject, $message, $from)
    {
        $headers = "From: " . strip_tags($from) . "\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        @mail($to, $subject, $message, $headers);
    }

    private function addMessage($msg, $type = 'success')
    {
        if ($type == 'success') {
            printf(
                "<div class='updated'><p><strong>%s</strong></p></div>",
                $msg
            );
        } else {
            printf(
                "<div class='error'><p><strong>%s</strong></p></div>",
                $msg
            );
        }
    }
    private function redirectUrl($url)
    {
        //header('Location:'.$url);
        echo '<script>';
        echo 'window.location.href="' . $url . '"';
        echo '</script>';
    }

    public function disable_emojis_tinymce($plugins)
    {
        if (is_array($plugins)) {
            return array_diff($plugins, array('wpemoji'));
        } else {
            return array();
        }
    }

    public function wp_performance_disable_emojis()
    {

        $settings = get_option(WpPerformance::OPTION_KEY . '_settings', array());
        if (isset($settings['disable_emoji']) && $settings['disable_emoji'] == 1) {
            remove_action('wp_head', 'print_emoji_detection_script', 7);
            remove_action('admin_print_scripts', 'print_emoji_detection_script');
            remove_action('wp_print_styles', 'print_emoji_styles');
            remove_action('admin_print_styles', 'print_emoji_styles');
            remove_filter('the_content_feed', 'wp_staticize_emoji');
            remove_filter('comment_text_rss', 'wp_staticize_emoji');
            remove_filter('wp_mail', 'wp_staticize_emoji_for_email');
            add_filter('tiny_mce_plugins', array($this, 'disable_emojis_tinymce'));
        }
    }

    public function wp_performance_speed_stop_loading_wp_embed()
    {
        $settings = get_option(WpPerformance::OPTION_KEY . '_settings', array());
        if (isset($settings['disable_embeds']) && $settings['disable_embeds'] == 1) {
            if (!is_admin()) {
                wp_deregister_script('wp-embed');
            }
        }

    }

    public function wp_performance_remove_script_version($src)
    {
        $settings = get_option(WpPerformance::OPTION_KEY . '_settings', array());
        if (isset($settings['remove_querystrings']) && $settings['remove_querystrings'] == 1) {
            $parts = explode('?ver', $src);
            return $parts[0];
        } else {
            return $src;
        }
    }

    public function wp_performace_disable_woo_stuffs()
    {
        $settings = get_option(WpPerformance::OPTION_KEY . '_settings', array());
        if (isset($settings['disable_woocommerce_non_pages']) && $settings['disable_woocommerce_non_pages'] == 1) {
            add_action('wp_print_scripts', array($this, 'wp_performance_woocommerce_de_script'), 100);
            add_action('wp_enqueue_scripts', array($this, 'wp_performance_remove_woocommerce_generator'), 99);
            add_action('wp_enqueue_scripts', array($this, 'child_manage_woocommerce_css'));
        }
    }

    public function wp_performance_remove_woocommerce_generator()
    {
        if (function_exists('is_woocommerce')) {
            if (!is_woocommerce()) {
                // if we're not on a woo page, remove the generator tag
                remove_action('wp_head', array($GLOBALS['woocommerce'], 'generator'));
            }
        }
    }

    public function child_manage_woocommerce_css()
    {
        if (function_exists('is_woocommerce')) {
            if (!is_woocommerce()) {
                // this adds the styles back on woocommerce pages. If you're using a custom script, you could remove these and enter in the path to your own CSS file (if different from your basic style.css file)
                wp_dequeue_style('woocommerce-layout');
                wp_dequeue_style('woocommerce-smallscreen');
                wp_dequeue_style('woocommerce-general');
            }
        }
    }

    public function wp_performance_woocommerce_de_script()
    {
        if (function_exists('is_woocommerce')) {
            if (!is_woocommerce() && !is_cart() && !is_checkout() && !is_account_page()) {
                // if we're not on a Woocommerce page, dequeue all of these scripts
                wp_dequeue_script('wc-add-to-cart');
                wp_dequeue_script('jquery-blockui');
                wp_dequeue_script('jquery-placeholder');
                wp_dequeue_script('woocommerce');
                wp_dequeue_script('jquery-cookie');
                wp_dequeue_script('wc-cart-fragments');
            }
        }
    }
    public function disabler_kill_rss()
    {
        wp_die(_e("No feeds available.", 'ippy_dis'));
    }

    public function disabler_kill_autosave()
    {
        wp_deregister_script('autosave');
    }

    public function wcs_woo_remove_reviews_tab($tabs)
    {
        unset($tabs['reviews']);
        return $tabs;
    }

    public function wp_performance_optimize_cleanups()
    {
        $settings = get_option(WpPerformance::OPTION_KEY . '_settings', array());
        if (isset($settings['rsd_clean']) && $settings['rsd_clean']) {
            remove_action('wp_head', 'rsd_link');
        }
        if (isset($settings['remove_windows_live_writer']) && $settings['remove_windows_live_writer']) {
            remove_action('wp_head', 'wlwmanifest_link');
        }
        if (isset($settings['remove_wordpress_generator_tag']) && $settings['remove_wordpress_generator_tag']) {
            remove_action('wp_head', 'wp_generator');
        }
        if (isset($settings['remove_shortlink_tag']) && $settings['remove_shortlink_tag']) {
            remove_action('wp_head', 'wp_shortlink_wp_head');
        }
        if (isset($settings['remove_wordpress_api_from_header']) && $settings['remove_wordpress_api_from_header']) {
            remove_action('wp_head', 'rest_output_link_wp_head');
        }

        if (isset($settings['disable_revisions']) && $settings['disable_revisions']) {
            remove_action('pre_post_update', 'wp_save_post_revision');
        }

        if (isset($settings['disable_rss']) && $settings['disable_rss']) {
            add_action('do_feed', array($this, 'disabler_kill_rss'), 1);
            add_action('do_feed_rdf', array($this, 'disabler_kill_rss'), 1);
            add_action('do_feed_rss', array($this, 'disabler_kill_rss'), 1);
            add_action('do_feed_rss2', array($this, 'disabler_kill_rss'), 1);
            add_action('do_feed_atom', array($this, 'disabler_kill_rss'), 1);
        }
        if (isset($settings['disable_xmlrpc']) && $settings['disable_xmlrpc']) {
            add_filter('xmlrpc_enabled', '__return_false');
        }
        if (isset($settings['disable_autosave']) && $settings['disable_autosave']) {
            add_action('wp_print_scripts', array($this, 'disabler_kill_autosave'));
        }
        if (isset($settings['disable_woocommerce_reviews']) && $settings['disable_woocommerce_reviews']) {
            add_filter('woocommerce_product_tabs', array($this, 'wcs_woo_remove_reviews_tab'), 98);
        }

    }

    public function wp_performance_disable_google_maps()
    {
        ob_start('disable_google_maps_ob_end');
    }

}

function disable_google_maps_ob_end($html)
{
    $html = preg_replace('/<script[^<>]*\/\/maps.(googleapis|google|gstatic).com\/[^<>]*><\/script>/i', '', $html);
    return $html;
}
