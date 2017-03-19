<style type="text/css">
    .form label{width:100%;display: inline-block;}
    .button-primary{margin-top:30px !important;}
</style>
<div class="wrap">
    <div id="icon-options-general" class="icon32"><br /></div>
    <h2>WordPress Disable - Performance Settings</h2>
<br><br>
    <form method="post" action="<?php echo admin_url( 'admin.php?page=updatewpperformance-settings' ); ?>">
<div class="form">
<table>
    <tr>

        <td><input name="disable_emoji" type="checkbox" id="disable_emoji" <?php if($settings['disable_emoji'] == 1)echo 'checked="checked"'; ?> value="1"/></td>
        <td><label for="disable_emoji">Disable Emojis</label></td>
    </tr>
    <!-- <tr>

        <td><input name="lazyload" type="checkbox" id="lazyload" value="1" <?php if($settings['lazyload'] == 1)echo 'checked="checked"'; ?>/></td>
        <td><label for="lazyload">Lazy load images and iframe/videos</label></td>
    </tr> -->
    <tr>

        <td style="width:300px;"><input name="disable_embeds" type="checkbox" id="disable_embeds" <?php if($settings['disable_embeds'] == 1)echo 'checked="checked"'; ?> value="1"/></td>
        <td><label for="disable_embeds">Disable Embeds</label></td>
    </tr>

    <tr>
        <td><input name="remove_querystrings" <?php if($settings['remove_querystrings'] == 1)echo 'checked="checked"'; ?> type="checkbox" id="remove_querystrings" value="1"/></td>
        <td><label for="remove_querystrings">Remove Querystrings</label></td>
    </tr>

     <tr>
        <td><input name="disable_gravatars" <?php if($settings['disable_gravatars'] == 1)echo 'checked="checked"'; ?> type="checkbox" id="disable_gravatars" value="1"/></td>
        <td><label for="disable_gravatars">Disable Gravatars</label></td>
    </tr>

    <tr>
        <td><input name="default_ping_status" <?php if($settings['default_ping_status'] == 1)echo 'checked="checked"'; ?> type="checkbox" id="default_ping_status" value="1"/></td>
        <td><label for="default_ping_status">Disable pingbacks and trackbacks</label></td>
    </tr>

    <tr>
        <td><input name="close_comments" <?php if($settings['close_comments'] == 1)echo 'checked="checked"'; ?> type="checkbox" id="close_comments" value="1"/></td>
        <td><label for="close_comments">Close comments after 28 days</label></td>
    </tr>


    <tr>
        <td><input name="paginate_comments" <?php if($settings['paginate_comments'] == 1)echo 'checked="checked"'; ?> type="checkbox" id="paginate_comments" value="1"/></td>
        <td><label for="paginate_comments">Paginate comments at 20</label></td>
    </tr>

    <tr>
        <td><input name="disable_woocommerce_non_pages" <?php if($settings['disable_woocommerce_non_pages'] == 1)echo 'checked="checked"'; ?> type="checkbox" id="disable_woocommerce_non_pages" value="1"/></td>
        <td><label for="disable_woocommerce_non_pages">Disable WooCommerce scripts and CSS on non WooCommerce pages</label></td>
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
