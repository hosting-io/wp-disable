<style type="text/css">
    .form label{width:100%;display: inline-block;}
    .button-primary{margin-top:30px !important;}
    .check_options{font-weight: bold;}
</style>
<div class="wrap">
    <div id="icon-options-general" class="icon32"><br /></div>
    <h2>WordPress Disable - Performance Settings</h2>
<div class="check_options"><a href="javascript:void(0);" onclick="checkall();">Check All</a> &nbsp; <a href="javascript:void(0);" onclick="uncheckall();">Uncheck All</a></div>
    <form method="post" action="<?php echo admin_url('admin.php?page=updatewpperformance-settings'); ?>">
<div class="form">
<table id="disable_settings">
    <tr>

        <td><input name="disable_emoji" type="checkbox" id="disable_emoji" <?php if ($settings['disable_emoji'] == 1) {
    echo 'checked="checked"';
}
?> value="1"/></td>
        <td><label for="disable_emoji">Disable Emojis</label></td>
    </tr>

    <tr>

        <td style="width:300px;"><input name="disable_embeds" type="checkbox" id="disable_embeds" <?php if ($settings['disable_embeds'] == 1) {
    echo 'checked="checked"';
}
?> value="1"/></td>
        <td><label for="disable_embeds">Disable Embeds</label></td>
    </tr>

     <tr>
        <td><input name="disable_gravatars" <?php if ($settings['disable_gravatars'] == 1) {
    echo 'checked="checked"';
}
?> type="checkbox" id="disable_gravatars" value="1"/></td>
        <td><label for="disable_gravatars">Disable Gravatars</label></td>
    </tr>

    <tr>
        <td><input name="default_ping_status" <?php if ($settings['default_ping_status'] == 1) {
    echo 'checked="checked"';
}
?> type="checkbox" id="default_ping_status" value="1"/></td>
        <td><label for="default_ping_status">Disable pingbacks and trackbacks</label></td>
    </tr>

        <tr>
            <td><input name="disable_revisions" <?php if ($settings['disable_revisions'] == 1) {
        echo 'checked="checked"';
    }
    ?> type="checkbox" id="disable_revisions" value="1"/></td>
            <td><label for="disable_revisions">Disable Revisions</label></td>
        </tr>

        <tr>
            <td><input name="disable_rss" <?php if ($settings['disable_rss'] == 1) {
        echo 'checked="checked"';
    }
    ?> type="checkbox" id="disable_rss" value="1"/></td>
            <td><label for="disable_rss">Disable RSS</label></td>
        </tr>

        <tr>
            <td><input name="disable_xmlrpc" <?php if ($settings['disable_xmlrpc'] == 1) {
        echo 'checked="checked"';
    }
    ?> type="checkbox" id="disable_xmlrpc" value="1"/></td>
            <td><label for="disable_xmlrpc">Disable XML-RPC</label></td>
        </tr>

        <tr>
            <td><input name="disable_autosave" <?php if ($settings['disable_autosave'] == 1) {
        echo 'checked="checked"';
    }
    ?> type="checkbox" id="disable_autosave" value="1"/></td>
            <td><label for="disable_autosave">Disable Autosave</label></td>
        </tr>

            <tr>
                <td><input name="disable_woocommerce_non_pages" <?php if ($settings['disable_woocommerce_non_pages'] == 1) {
            echo 'checked="checked"';
        }
        ?> type="checkbox" id="disable_woocommerce_non_pages" value="1"/></td>
                <td><label for="disable_woocommerce_non_pages">Disable WooCommerce scripts and CSS on non WooCommerce pages</label></td>
            </tr>

         <tr>
            <td><input name="disable_woocommerce_reviews" <?php if ($settings['disable_woocommerce_reviews'] == 1) {
        echo 'checked="checked"';
    }
    ?> type="checkbox" id="disable_woocommerce_reviews" value="1"/></td>
            <td><label for="disable_woocommerce_reviews">Disable WooCommerce Reviews</label></td>
        </tr>

    <tr>
        <td><input name="close_comments" <?php if ($settings['close_comments'] == 1) {
    echo 'checked="checked"';
}
?> type="checkbox" id="close_comments" value="1"/></td>
        <td><label for="close_comments">Close comments after 28 days</label></td>
    </tr>


    <tr>
        <td><input name="paginate_comments" <?php if ($settings['paginate_comments'] == 1) {
    echo 'checked="checked"';
}
?> type="checkbox" id="paginate_comments" value="1"/></td>
        <td><label for="paginate_comments">Paginate comments at 20</label></td>
    </tr>

  

        <tr>
            <td><input name="remove_querystrings" <?php if ($settings['remove_querystrings'] == 1) {
        echo 'checked="checked"';
    }
    ?> type="checkbox" id="remove_querystrings" value="1"/></td>
            <td><label for="remove_querystrings">Remove Querystrings</label></td>
        </tr>

    <tr>
        <td><input name="remove_rsd" <?php if ($settings['remove_rsd'] == 1) {
    echo 'checked="checked"';
}
?> type="checkbox" id="remove_rsd" value="1"/></td>
        <td><label for="remove_rsd">Remove RSD (Really Simple Discovery) tag</label></td>
    </tr>

    <tr>
        <td><input name="remove_windows_live_writer" <?php if ($settings['remove_windows_live_writer'] == 1) {
    echo 'checked="checked"';
}
?> type="checkbox" id="remove_windows_live_writer" value="1"/></td>
        <td><label for="remove_windows_live_writer">Remove Windows Live Writer tag</label></td>
    </tr>

    <tr>
        <td><input name="remove_wordpress_generator_tag" <?php if ($settings['remove_wordpress_generator_tag'] == 1) {
    echo 'checked="checked"';
}
?> type="checkbox" id="remove_wordpress_generator_tag" value="1"/></td>
        <td><label for="remove_wordpress_generator_tag">Remove Wordpress Generator Tag</label></td>
    </tr>

    <tr>
        <td><input name="remove_shortlink_tag" <?php if ($settings['remove_shortlink_tag'] == 1) {
    echo 'checked="checked"';
}
?> type="checkbox" id="remove_shortlink_tag" value="1"/></td>
        <td><label for="remove_shortlink_tag">Remove Shortlink Tag</label></td>
    </tr>

    <tr>
        <td><input name="remove_wordpress_api_from_header" <?php if ($settings['remove_wordpress_api_from_header'] == 1) {
    echo 'checked="checked"';
}
?> type="checkbox" id="remove_wordpress_api_from_header" value="1"/></td>
        <td><label for="remove_wordpress_api_from_header">Remove Wordpress API from header</label></td>
    </tr>

 

        <tr >
        <td></td>
        <td><?php
submit_button('Update', 'button button-primary button-large', 'submit', false);
?></td>
    </tr>
</table>

</div>
    </form>
<script type="text/javascript">
    function checkall()
    {
        jQuery('#disable_settings').find('input[type="checkbox"]').prop('checked', true);
    }

    function uncheckall()
    {
        jQuery('#disable_settings').find('input[type="checkbox"]').prop('checked', false);
    }

</script>