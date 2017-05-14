<!-- <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script> -->


<div class="wrap wp-disable">
<h2><!-- Wordpress messages will display here automatically --></h2>
   <div id="icon-options-general" class="icon32"><br /></div>
   <header>
   <div class="col-left">
       <h2>WordPress Disable - Improve Performances</h2>
       <small>Improve performance and reduce HTTP requests.</small>
</div>

<div class="col-right"><strong>Help us build a better product</strong>
<p><a target="blank" href="https://wordpress.org/plugins/wp-disable/">Rate us on WordPress.org</a></p>
<!-- <div class="stars">

</div> -->
</div>
</header>
<div class="container">
<div class="tab-wrap">
  <ul class="tabs">
       <li class="tab-link current" data-tab="tab-1">Requests</li>
       <li class="tab-link" data-tab="tab-2">WooCommerce </li>
       <li class="tab-link" data-tab="tab-3">Tags</li>
       <li class="tab-link" data-tab="tab-4">Admin</li>
       <li class="tab-link" data-tab="tab-5">Others</li>
   </ul>
<form method="post" action="<?php echo admin_url('tools.php?page=updatewpperformance-settings'); ?>">
  <div id="tab-1" class="tab-content current">
    <div class="form">
      <div class="disable-form disable_settings">
        <div class="form-group">
          <span>Disable Emojis</span>
          <label class="switch">
            <input name="disable_emoji" type="checkbox" id="disable_emoji" <?php if (isset($settings['disable_emoji']) && $settings['disable_emoji'] == 1) {
               echo 'checked="checked"';
            }
            ?> value="1"/>
            <div class="slider round"></div>
          </label>
        </div>

        <div class="form-group">
          <span>Remove Querystrings</span>
          <label class="switch">
            <input name="remove_querystrings" <?php if (isset($settings['remove_querystrings']) && $settings['remove_querystrings'] == 1) {
               echo 'checked="checked"';
               }
               ?> type="checkbox" id="remove_querystrings" value="1"/>
            <div class="slider round"></div>
          </label>
        </div>

        <div class="form-group">
          <span>Disable Embeds</span>
          <label class="switch">
            <td style="width:300px; text-align:left;"><input name="disable_embeds" type="checkbox" id="disable_embeds" <?php if (isset($settings['disable_embeds']) && $settings['disable_embeds'] == 1) {
               echo 'checked="checked"';
            }
            ?> value="1"/>
            <div class="slider round"></div>
          </label>
        </div>

        <div class="form-group">
          <span>Disable Google Maps</span>
          <label class="switch">
            <input name="disable_google_maps" <?php if (isset($settings['disable_google_maps']) && $settings['disable_google_maps'] == 1) {
                   echo 'checked="checked"';
               }
               ?> type="checkbox" id="disable_google_maps" value="1"/>
            <div class="slider round"></div>
          </label>
        </div>
      </div>
    </div>
  </div>
  <div id="tab-2" class="tab-content">
    <div class="form">
      <div class="disable-form disable_settings">
        <div class="form-group">
          <span>Disable WooCommerce scripts and CSS on non WooCommerce pages</span>
          <label class="switch">
            <input name="disable_woocommerce_non_pages" <?php if (isset($settings['disable_woocommerce_non_pages']) && $settings['disable_woocommerce_non_pages'] == 1) {
                     echo 'checked="checked"';
                 }
                 ?> type="checkbox" id="disable_woocommerce_non_pages" value="1"/>
            <div class="slider round"></div>
          </label>
        </div>

        <div class="form-group">
          <span>Disable WooCommerce Reviews</span>
          <label class="switch">
             <input name="disable_woocommerce_reviews" <?php if (isset($settings['disable_woocommerce_reviews']) && $settings['disable_woocommerce_reviews'] == 1) {
                 echo 'checked="checked"';
             }
             ?> type="checkbox" id="disable_woocommerce_reviews" value="1"/>
            <div class="slider round"></div>
          </label>
         
        </div>

        <div class="form-group">
          <span>Defer Woocommerce Cart Fragments</span>
          <label class="switch">
             <input name="disable_woocommerce_cart_fragments" <?php if (isset($settings['disable_woocommerce_cart_fragments']) && $settings['disable_woocommerce_cart_fragments'] == 1) {
                 echo 'checked="checked"';
             }
             ?>  type="checkbox" id="disable_woocommerce_cart_fragments" value="1"/>
            <div class="slider round"></div>
          </label>
          
        </div>
      </div>
    </div>
  </div>
  <div id="tab-3" class="tab-content">
    <div class="form">
      <div class="disable-form disable_settings">
        
        <div class="form-group">
          <span>Remove RSD (Really Simple Discovery) tag</span>
          <label class="switch">
             ><input name="remove_rsd" <?php if (isset($settings['remove_rsd']) && $settings['remove_rsd'] == 1) {
                echo 'checked="checked"';
             }
             ?> type="checkbox" id="remove_rsd" value="1"/>
            <div class="slider round"></div>
          </label>
          
        </div>

        <div class="form-group">
          <span>Remove Shortlink Tag</span>
          <label class="switch">
             <input name="remove_shortlink_tag" <?php if (isset($settings['remove_shortlink_tag']) && $settings['remove_shortlink_tag'] == 1) {
                echo 'checked="checked"';
             }
             ?> type="checkbox" id="remove_shortlink_tag" value="1"/>
            <div class="slider round"></div>
          </label>
          
        </div>

        <div class="form-group">
          <span>Remove Wordpress API from header</span>
          <label class="switch">
             <input name="remove_wordpress_api_from_header" <?php if (isset($settings['remove_wordpress_api_from_header']) && $settings['remove_wordpress_api_from_header'] == 1) {
                echo 'checked="checked"';
             }
             ?> type="checkbox" id="remove_wordpress_api_from_header" value="1"/>
            <div class="slider round"></div>
          </label>
          
        </div>

        <div class="form-group">
          <span>Remove Windows Live Writer tag</span>
          <label class="switch">
             <input name="remove_windows_live_writer" <?php if (isset($settings['remove_windows_live_writer']) && $settings['remove_windows_live_writer'] == 1) {
             echo 'checked="checked"';
             }
             ?> type="checkbox" id="remove_windows_live_writer" value="1"/>
            <div class="slider round"></div>
          </label>
          
        </div>

        <div class="form-group">
          <span>Remove Wordpress Generator Tag</span>
          <label class="switch">
             <input name="remove_wordpress_generator_tag" <?php if (isset($settings['remove_wordpress_generator_tag']) && $settings['remove_wordpress_generator_tag'] == 1) {
             echo 'checked="checked"';
             }
             ?> type="checkbox" id="remove_wordpress_generator_tag" value="1"/>
            <div class="slider round"></div>
          </label>
          
        </div>
      </div>
    </div>
  </div>
  <div id="tab-4" class="tab-content">
    <div class="form">
      <div class="disable-form disable_settings">
        <div class="form-group">
          <span>Disable Revisions</span>
          <label class="switch">
             <input name="disable_revisions" <?php if (isset($settings['disable_revisions']) && $settings['disable_revisions'] == 1) {
                    echo 'checked="checked"';
                }
                ?> type="checkbox" id="disable_revisions" value="1"/>
            <div class="slider round"></div>
          </label>
          
        </div>
        <div class="form-group">
          <span>Disable Autosave</span>
          <label class="switch">
             <input name="disable_autosave" <?php if (isset($settings['disable_autosave']) && $settings['disable_autosave'] == 1) {
                       echo 'checked="checked"';
                   }
                   ?> type="checkbox" id="disable_autosave" value="1"/>
            <div class="slider round"></div>
          </label>
          
        </div>

        <div class="form-group">
          <span>Close comments after 28 days</span>
          <label class="switch">
             <input name="close_comments" <?php if (isset($settings['close_comments']) && $settings['close_comments'] == 1) {
                   echo 'checked="checked"';
                }
                ?> type="checkbox" id="close_comments" value="1"/>
            <div class="slider round"></div>
          </label>
          
        </div>

        <div class="form-group">
          <span>Paginate comments at 20</span>
          <label class="switch">
             <input name="paginate_comments" <?php if (isset($settings['paginate_comments']) && $settings['paginate_comments'] == 1) {
                   echo 'checked="checked"';
                }
                ?> type="checkbox" id="paginate_comments" value="1"/>
            <div class="slider round"></div>
          </label>
        </div>
      </div>
    </div>
  </div>
  <div id="tab-5" class="tab-content">
    <div class="form">
      <div class="disable-form disable_settings">
        <div class="form-group">
          <span>Disable Gravatars</span>
          <label class="switch">
             <input name="disable_gravatars" <?php if (isset($settings['disable_gravatars']) && $settings['disable_gravatars'] == 1) {
                echo 'checked="checked"';
             }
             ?> type="checkbox" id="disable_gravatars" value="1"/>
            <div class="slider round"></div>
          </label>
        </div>

        <div class="form-group">
          <span>Disable pingbacks and trackbacks</span>
          <label class="switch">
             <input name="default_ping_status" <?php if (isset($settings['default_ping_status']) && $settings['default_ping_status'] == 1) {
                echo 'checked="checked"';
             }
             ?> type="checkbox" id="default_ping_status" value="1"/>
            <div class="slider round"></div>
          </label>
        </div>

        <div class="form-group">
          <span>Disable RSS</span>
          <label class="switch">
             <input name="disable_rss" <?php if (isset($settings['disable_rss']) && $settings['disable_rss'] == 1) {
                    echo 'checked="checked"';
                }
                ?> type="checkbox" id="disable_rss" value="1"/>
            <div class="slider round"></div>
          </label>
        </div>

        <div class="form-group">
          <span>Disable XML-RPC</span>
          <label class="switch">
             <input name="disable_xmlrpc" <?php if (isset($settings['disable_xmlrpc']) && $settings['disable_xmlrpc'] == 1) {
                    echo 'checked="checked"';
                }
                ?> type="checkbox" id="disable_xmlrpc" value="1"/>
            <div class="slider round"></div>
          </label>
        </div>

      </div>
    </div>
  </div>
  <div>
       <?php echo submit_button('Update', 'btn-submit', 'submit', false); ?>
</div>


    <div class="panel">
       <div class="pane-head">
          <a target="blank" href="https://optimisation.io"><img src="<?php echo plugins_url('images/optimisation-4.jpg', __FILE__.'/../../../') ?>" alt="" /></a>
          <a target="blank" href="https://optimisation.io" class="just-link">Still Need Help ? We also do manual optimisations.</a>
       </div>
       </div>
   </div>
   <div class="side-bar">
    <h3>Offload Google Analytics to local</h3>
    <div class="offload-form">
      <div class="form-group">
        <label>GA Code</label>
        <input type="text" name="ds_tracking_id" value="<?php echo (isset($settings['ds_tracking_id']))?$settings['ds_tracking_id']:''; ?>" />
      </div>

      <div class="form-group">
        <label>Save GA in (please ensure you remove any other GA tracking)</label>
        <?php
          $sgal_script_position = array('<span>Header</span>', '<span>Footer</span>');

          foreach ($sgal_script_position as $option) {
              echo "<input type='radio' name='ds_script_position' value='" . $option . "' ";
              echo $sgal_checked = ($option == $settings['ds_script_position']) ? ' checked="checked"' : '';
              echo " />";
              echo ucfirst($option);
              echo $sgal_script_default = ($option == 'header') ? '' : '';

          }
        ?>
      </div>

      <div class="form-group">
        <label>Use adjusted bounce rate?</label>
        <input type="number" name="ds_adjusted_bounce_rate" min="0" max="60" value="<?php echo isset($settings['ds_adjusted_bounce_rate'])?$settings['ds_adjusted_bounce_rate']:0; ?>" />
      </div>

      <div class="form-group">
        <label>Change enqueue order? (Default = 0)</label>
        <input type="number" name="ds_enqueue_order" min="0" value="<?php echo isset($settings['ds_enqueue_order'])?$settings['ds_enqueue_order']:0; ?>" />
      </div>

      <div class="form-group">
        <input type="checkbox" name="caos_disable_display_features" <?php if (isset($settings['caos_disable_display_features']) && $settings['caos_disable_display_features'] == "on") echo 'checked = "checked"'; ?> />  Disable all <a href="https://developers.google.com/analytics/devguides/collection/analyticsjs/display-features" target="_blank">display features functionality</a>?
      </div>

      <div class="form-group">
        <input type="checkbox" name="ds_anonymize_ip" <?php if (isset($settings['ds_anonymize_ip']) && $settings['ds_anonymize_ip'] == "on") echo 'checked = "checked"'; ?> />  Use <a href="https://support.google.com/analytics/answer/2763052?hl=en" target="_blank">Anomymize IP</a>? (Required by law for some countries)
      </div>

      <div class="form-group">
        <input type="checkbox" name="ds_track_admin" <?php if(isset($settings['ds_track_admin']) && $settings['ds_track_admin'] == "on") echo 'checked = "checked"'; ?> />  Track logged in Administrators?
      </div>

      <div class="form-group">
        <input type="checkbox" name="caos_remove_wp_cron" <?php if(isset($settings['caos_remove_wp_cron']) && $settings['caos_remove_wp_cron'] == "on") echo 'checked = "checked"'; ?> />  Remove script from wp-cron?
      </div>



    </div>
</form>
      <div class="panel">
        <div class="pane-head">
          <a target="blank" href="https://wordpress.org/plugins/wp-image-compression/">  <img src="<?php echo plugins_url('images/wp-image-compression.jpg', __FILE__.'/../../../') ?>" alt="" /> </a>
        </div>
      </div>
    </div>
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



jQuery(document).ready(function($){

   $('ul.tabs li').click(function(){
       var tab_id = $(this).attr('data-tab');

       $('ul.tabs li').removeClass('current');
       $('.tab-content').removeClass('current');

       $(this).addClass('current');
       $("#"+tab_id).addClass('current');
   })

})

</script>
