<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>

<style type="text/css">
.cg-panel {
   margin-bottom: 10px;
}
   .form label{width:100%;display: inline-block;}
   .button-primary{margin-top:30px !important;}
   .check_options{font-weight: bold;}


   ul.tabs{
           margin: 0px;
           padding: 0px;
           list-style: none;
       }

ul.tabs li {
   background: hsl(220, 60%, 34%) none repeat scroll 0 0;
   border-radius: 2px 2px 0 0;
   color: hsl(0, 0%, 100%);
   cursor: pointer;
   display: inline-block;
   float: left;
   margin: 0 1px;
   padding: 10px 21px;
}


.wp-core-ui .cg-container .button-primary {
   background: hsl(13, 92%, 56%) none repeat scroll 0 0;
   border-bottom: 5px solid hsl(13, 80%, 49%);
   border-left: medium none !important;
   border-right: medium none !important;
   border-top: medium none !important;
   box-shadow: none;
   color: hsl(0, 0%, 100%);
   height: auto;
   padding: 5px 30px;
   text-decoration: none;
   text-shadow: unset;
}
table tbody tr td:first-child {
   width: 15px !important;
}
table tbody tr td {
   color: hsl(0, 0%, 0%);
   font-size: 12px;
   font-weight: 600;
   padding: 5px;
}
ul.tabs li.current {
   background: hsl(13, 92%, 56%) none repeat scroll 0 0;
   color: hsl(0, 0%, 100%);
}

       .tab-content{
           display: none;
           background: #eee;
           clear: both;
           padding: 15px;
       }

       .tab-content.current{
           display: inherit;
       }
       .wrap {
   margin: 10px 20px 0 2px;
   max-width: 900px;
}
       .wrap header {
   background: hsl(220, 60%, 34%) none repeat scroll 0 0;
   color: hsl(0, 0%, 100%);
   padding: 15px;
}
.wrap header small {
   background: hsl(13, 92%, 56%) none repeat scroll 0 0;
   border-radius: 1px;
   font-size: 13px;
   padding: 2px 13px 3px;
}
.cg-pane-head {
   text-align: right;
}
.wrap header h2 {
     color: hsl(0, 0%, 92%);
   font-size: 20px;
   font-weight: 600;
}
.cg-pane-head h2 {
   color: hsl(0, 0%, 100%);
   line-height: 22px;
   text-align: center;
}
.wp-core-ui .wrap .notice.is-dismissible {
   color: hsl(0, 0%, 6%);

}
.cg-tab-wrap {
   float: left;
   padding: 0 15px;
   width: 57%;
}
.cg-pane-small {
   float: left;
   padding: 0 15px;
   width: 36%;
}
.cg-container {
     background: hsl(0, 0%, 100%) none repeat scroll 0 0;
   border: 1px solid hsl(0, 0%, 85%);
   box-shadow: 0 0 2px 1px hsla(0, 0%, 0%, 0.1);
   padding: 15px 0 30px;
}
.cg-panel.cg-featured-panel {
  background: hsl(13, 92%, 56%) none repeat scroll 0 0;
      margin-bottom: 13px;
   padding: 1px;
}
.cg-col-right {
  float: right;
   margin: 10px auto;
   text-align: right;
   width: 40%;
}
.cg-col-left {
   float: left;
   width: 60%;
}
.cg-col-right a {
   background: hsl(13, 92%, 56%) none repeat scroll 0 0;
   border-radius: 1px;
   color: hsl(0, 0%, 100%);
   font-size: 12px;
   padding: 1px 10px 3px;
   text-decoration: none;
}
.cg-col-right > p {
   margin: 5px auto;
}
.cg-pane-head img {
     border: 2px solid hsl(0, 0%, 98%);
   width: 100%;
}
.cg-pane-head > a {
   text-decoration: none;
}

.cg-panel h3 {
   color: hsl(0, 0%, 100%);
   font-size: 20px;
   text-align: center;
}

.cg-panel.cg-featured-panel.cg-stye-1 {
   background: hsl(220, 60%, 34%) none repeat scroll 0 0;
}
.cg-stars {
   float: right;
   height:19px;
   background: hsla(0, 0%, 0%, 0) url("/wp-content/plugins/wp-disable/images/stars.jpg") repeat scroll 0 0;
   width: 100px;
}
@media(max-width:768px){

.cg-tab-wrap {

   width: 96%;
}

.cg-col-left, .cg-col-right, .cg-pane-small  {

   width: 100%;
}
.cg-col-right a {

   display: block;

}
}
</style>



<div class="wrap">

   <div id="icon-options-general" class="icon32"><br /></div>
   <header>
   <div class="cg-col-left">
       <h2>WordPress Disable - Improve Performances</h2>
       Improve performance and reduce HTTP requests.
</div>

<div class="cg-col-right"><strong>Help us build a better product</strong>
<p><a target="blank" href="https://wordpress.org/plugins/wp-disable/">Rate us on WordPress.org</a></p>
<div class="cg-stars">

</div>
</div>
<div style="clear:both"></div>
   </header>
<div class="cg-container">
<div class="cg-tab-wrap">
  <ul class="tabs">
       <li class="tab-link current" data-tab="tab-1">Requests</li>
       <li class="tab-link" data-tab="tab-2">WooCommerce </li>
       <li class="tab-link" data-tab="tab-3">Tags</li>
       <li class="tab-link" data-tab="tab-4">Admin</li>
       <li class="tab-link" data-tab="tab-5">Others</li>
   </ul>
<form method="post" action="<?php echo admin_url('admin.php?page=updatewpperformance-settings'); ?>">
   <div id="tab-1" class="tab-content current">


<div class="form">
<table class="disable_settings">
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
           <td><input name="remove_querystrings" <?php if ($settings['remove_querystrings'] == 1) {
       echo 'checked="checked"';
   }
   ?> type="checkbox" id="remove_querystrings" value="1"/></td>
           <td><label for="remove_querystrings">Remove Querystrings</label></td>
       </tr>

</table>

</div>
   </div>
   <div id="tab-2" class="tab-content">

<div class="form">
<table class="disable_settings">

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

</table>

</div>


   </div>
   <div id="tab-3" class="tab-content">

<div class="form">
<table class="disable_settings">


   <tr>
       <td><input name="remove_rsd" <?php if ($settings['remove_rsd'] == 1) {
   echo 'checked="checked"';
}
?> type="checkbox" id="remove_rsd" value="1"/></td>
       <td><label for="remove_rsd">Remove RSD (Really Simple Discovery) tag</label></td>
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

</table>

</div>
   </div>
   <div id="tab-4" class="tab-content">

<div class="form">
<table class="disable_settings">

       <tr>
           <td><input name="disable_revisions" <?php if ($settings['disable_revisions'] == 1) {
       echo 'checked="checked"';
   }
   ?> type="checkbox" id="disable_revisions" value="1"/></td>
           <td><label for="disable_revisions">Disable Revisions</label></td>
       </tr>


</table>

</div>

   </div>
    <div id="tab-5" class="tab-content">

<div class="form">
<table class="disable_settings">

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



</table>

</div>

   </div>
   <div>
       <?php echo submit_button('Update', 'button button-primary button-large', 'submit', false); ?>
   </div>

   </form>
   </div>
   <div class="cg-pane-small">
       <div class="cg-panel">
       <div class="cg-pane-head">
        <a target="blank" href="https://wordpress.org/plugins/wp-image-compression/">  <img src="https://res.cloudinary.com/dhnesdsyd/image/upload/q_auto/v1490964304/wp-image-compression_xaucfv.jpg" alt="" /> </a>
       </div>
       </div>
         <div class="cg-panel cg-featured-panel">
       <div class="cg-pane-head">
           <a target="blank" href="#"><h2>WordPress Cache<br>Coming Soon</h2></a>
       </div>
       </div>
         <div class="cg-panel">
       <div class="cg-pane-head">
          <a target="blank" href="https://optimisation.io/"><img src="http://res.cloudinary.com/dhnesdsyd/image/upload/q_auto/v1490964304/optimisation_noj4ri.jpg" alt="" /></a>
          <a target="blank" style="text-decoration: none;" href="https://optimisation.io">Still Need Help ? We also do manual optimisations.</a>
       </div>
       </div>


   </div>
   <div style="clear:both"></div>
   </div>

<script type="text/javascript">
   function checkall()
   {
       jQuery('.disable_settings').find('input[type="checkbox"]:visible').prop('checked', true);
   }

   function uncheckall()
   {
       jQuery('.disable_settings').find('input[type="checkbox"]:visible').prop('checked', false);
   }



$(document).ready(function(){

   $('ul.tabs li').click(function(){
       var tab_id = $(this).attr('data-tab');

       $('ul.tabs li').removeClass('current');
       $('.tab-content').removeClass('current');

       $(this).addClass('current');
       $("#"+tab_id).addClass('current');
   })

})

</script>
