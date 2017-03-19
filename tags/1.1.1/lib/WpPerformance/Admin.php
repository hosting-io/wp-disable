<?php
class WpPerformance_Admin
{
    public function __construct()
    {
        add_action('admin_menu', array($this, 'menu'));
        add_action('init', array($this, 'wp_performance_disable_emojis'));
        add_action('init', array($this, 'wp_performance_speed_stop_loading_wp_embed'));
        add_filter('script_loader_src', array($this, 'wp_performance_remove_script_version'), 15, 1);
    }

    public function menu()
    {
        add_menu_page(__('Disabler', 'wpper'), __('Disabler', 'wpper'), 'manage_options', 'wpperformance', array($this, 'addsettings'));
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
            'disable_gravatars'   => ($_POST['disable_gravatars'])?1:0,
            'disable_emoji'       => ($_POST['disable_emoji'])?1:0,
            'disable_embeds'      => ($_POST['disable_embeds'])?1:0,
            'remove_querystrings' => ($_POST['remove_querystrings'])?1:0,
            'lazyload'            => ($_POST['lazyload'])?1:0,
        );
        if ($_POST['disable_gravatars'] == 1) {
            update_option('show_avatars', false);
        } else {
            update_option('show_avatars', true);
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
        }else{
            return $src;
        }
    }

}
