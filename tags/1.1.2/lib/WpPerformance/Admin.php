<?php
class WpPerformance_Admin
{
    public function __construct()
    {
        add_action('admin_menu', array($this, 'menu'));
        add_action('init', array($this, 'wp_performance_disable_emojis'));
        add_action('init', array($this, 'wp_performance_speed_stop_loading_wp_embed'));
        add_filter('script_loader_src', array($this, 'wp_performance_remove_script_version'), 15, 1);
        add_filter('init', array($this, 'wp_performace_disable_woo_stuffs'));
    }

    public function menu()
    {
        add_menu_page(__('WP Disable', 'wpper'), __('WP Disable', 'wpper'), 'manage_options', 'wpperformance', array($this, 'addsettings'));
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
            'disable_gravatars'             => ($_POST['disable_gravatars']) ? 1 : 0,
            'disable_emoji'                 => ($_POST['disable_emoji']) ? 1 : 0,
            'disable_embeds'                => ($_POST['disable_embeds']) ? 1 : 0,
            'remove_querystrings'           => ($_POST['remove_querystrings']) ? 1 : 0,
            'lazyload'                      => ($_POST['lazyload']) ? 1 : 0,
            'default_ping_status'           => ($_POST['default_ping_status']) ? 1 : 0,
            'close_comments'                => ($_POST['close_comments']) ? 1 : 0,
            'paginate_comments'             => ($_POST['paginate_comments']) ? 1 : 0,
            'disable_woocommerce_non_pages' => ($_POST['disable_woocommerce_non_pages']) ? 1 : 0,
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
        if ($settings['disable_emoji'] == 1) {
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
        if ($settings['disable_embeds'] == 1) {
            if (!is_admin()) {
                wp_deregister_script('wp-embed');
            }
        }

    }

    public function wp_performance_remove_script_version($src)
    {
        $settings = get_option(WpPerformance::OPTION_KEY . '_settings', array());
        if ($settings['remove_querystrings'] == 1) {
            $parts = explode('?ver', $src);
            return $parts[0];
        } else {
            return $src;
        }
    }

    public function wp_performace_disable_woo_stuffs()
    {
        $settings = get_option(WpPerformance::OPTION_KEY . '_settings', array());
        if ($settings['disable_woocommerce_non_pages'] == 1) {
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

}
