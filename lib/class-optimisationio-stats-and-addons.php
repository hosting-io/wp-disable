<?php
class Optimisationio_Stats_And_Addons {

	private static $instance = null;

	public static $addons_slug = array( 'wp-disable', 'cache-performance', 'wp-image-compression' );

	public static $addons = null;

	function __construct() {
		add_action( 'admin_menu', array( $this, 'statistics_menu' ), 8 );
		add_action( 'admin_menu', array( $this, 'import_export_menu' ), 20 );
		add_action( 'admin_enqueue_scripts', array( $this, 'addons_pages_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'addons_pages_scripts' ) );
		add_action( 'wp_ajax_optimisationio_install_addon', array( $this, 'ajax_install_addon' ) );
		add_action( 'wp_ajax_optimisationio_deactivate_addon', array( $this, 'ajax_deactivate_addon' ) );
		add_action( 'wp_ajax_optimisationio_activate_addon', array( $this, 'ajax_activate_addon' ) );
		add_action( 'wp_ajax_optimisationio_import_addons_settings', array( $this, 'ajax_import_addons_settings' ) );
		add_action( 'wp_ajax_optimisationio_export_addons_settings', array( $this, 'ajax_export_addons_settings' ) );
	}

	public static function init(){
		if( null === self::$instance ){
			self::$instance = new self();
		}
	}

	public function statistics_menu() {
		
		add_menu_page( __( 'Optimisation.io', 'optimisationio' ), __( 'Optimisation.io', 'optimisationio' ), 'manage_options', 'optimisationio-statistics-and-addons', array( $this, 'statistics_page' ), 'dashicons-dashboard' );

		add_submenu_page( 'optimisationio-statistics-and-addons', __( 'Statistics', 'optimisationio' ), __( 'Statistics', 'optimisationio' ), 'manage_options', 'optimisationio-statistics-and-addons' );
	}

	public function import_export_menu(){
		add_submenu_page( 'optimisationio-statistics-and-addons', __( 'Import/Export', 'optimisationio' ), __( 'Import/Export', 'optimisationio' ), 'manage_options', 'optimisationio-import-export', array($this, 'import_export_page') );
	}

	public function statistics_page() {
		if( null === self::$addons ){
			self::init_addons();
		}
		require_once( plugin_dir_path( dirname( __FILE__ ) ) . 'views/optimisationio-statistics-addons.php' );
	}

	public function import_export_page(){
		if( null === self::$addons ){
			self::init_addons();
		}
		require_once( plugin_dir_path( dirname( __FILE__ ) ) . 'views/optimisationio-import-export.php' );
	}

	public function addons_pages_styles($hook){
		
		wp_enqueue_style( 'optimisationio-all', plugin_dir_url( dirname( __FILE__ ) ) . 'css/optimisationio-all.css' );


		if ( 'toplevel_page_optimisationio-statistics-and-addons' === $hook ) {
			wp_enqueue_style( 'optimisationio-stats-addons-page', plugin_dir_url( dirname( __FILE__ ) ) . 'css/optimisationio-statistics-addons.css' );
		}
		if ( 'optimisation-io_page_optimisationio-import-export' == $hook ) {
			wp_enqueue_style( 'optimisationio-import-export', plugin_dir_url( dirname( __FILE__ ) ) . 'css/optimisationio-import-export.css' );
		}
	}

	public function addons_pages_scripts($hook){

		if ( 'optimisation-io_page_optimisationio-import-export' == $hook ) {
			wp_enqueue_script( 'optimisationio-import-export', plugin_dir_url( dirname( __FILE__ ) ) . 'js/clipboard.min.js' );
		}
	}

	private function wp_verify_nonce($nonce, $type){
		return wp_verify_nonce( $nonce, $type );
	}

	public function ajax_install_addon(){

		$post_req = $_POST;	// Input var okay.

		$ret = array( 'error'	=> 1 );

		if( $this->wp_verify_nonce( $post_req['nonce'], 'optimisationio-addons-nonce' ) ){

			if( isset( $post_req['link'] ) && $post_req['link'] ){

				global $wp_filesystem;

				if ( ! $wp_filesystem ) {
					if ( ! function_exists( 'WP_Filesystem' ) ) {
						require_once( ABSPATH . 'wp-admin/includes/file.php' );
					}
					WP_Filesystem();
				}

				// @note: Check if plugins root folder is writable.
				if ( ! WP_Filesystem( false, WP_PLUGIN_DIR ) || 'direct' !== $wp_filesystem->method ) {
					$ret['msg'] = 'You are not allowed to edt folders/files on this site';
				}
				else {

					ob_start();
					require_once( ABSPATH . 'wp-admin/includes/file.php' );
					require_once( ABSPATH . 'wp-admin/includes/misc.php' );
					require_once( ABSPATH . 'wp-admin/includes/class-wp-upgrader.php' );
					require_once( 'class-optimisationio-upgrader-skin.php' );
					$upgrader = new Plugin_Upgrader( new Optimisationio_Upgrader_Skin() );
					$install = $upgrader->install( $post_req['link'] );
					ob_end_clean();

					if( null === $install ){
						$ret['msg'] = 'Could not complete add-on installation';
					}
					else{
						$ret['error'] = 0;
					}

				}

			}
			else{
				$ret['msg'] = "Invalid addon";
			}
		}
		else{
			$ret['msg'] = "Invalid user";
		}

		wp_send_json( $ret );
	}

	public function ajax_activate_addon(){

		$post_req = $_POST;	// Input var okay.

		$ret = array( 'error'	=> 1 );

		if( $this->wp_verify_nonce( $post_req['nonce'], 'optimisationio-addons-nonce' ) ){

			if( isset( $post_req['file'] ) && $post_req['file'] ){

				self::$addons = null;

				$ret['addons_number'] = self::active_addons_number();

				$result = activate_plugin( $post_req['file'] );

				if ( ! is_wp_error( $result ) ) {
					$ret['error'] = 0;
					$ret['msg'] = "Successful activation";
				}
				else{
					$ret['msg'] = "Activation error";
				}
			}
			else{
				$ret['msg'] = "Invalid addon";
			}
		}
		else{
			$ret['msg'] = "Invalid user";
		}

		wp_send_json( $ret );
	}

	public function ajax_deactivate_addon(){

		$post_req = $_POST;	// Input var okay.

		$ret = array( 'error' => 1 );

		if( $this->wp_verify_nonce( $post_req['nonce'], 'optimisationio-addons-nonce' ) ){

			if( isset( $post_req['file'] ) && $post_req['file'] ){

				self::$addons = null;

				$ret['addons_number'] = self::active_addons_number();

				if( 1 < self::active_addons_number() ){

					$result = deactivate_plugins( $post_req['file'] );

					if ( ! is_wp_error( $result ) ) {
						$ret['error'] = 0;
						$ret['msg'] = "Successful deactivation";
					}
					else{
						$ret['msg'] = "Dectivation error";
					}
				}
				else{
					$ret['type'] = "deny-disable";
					$ret['msg'] = "Can not disable all addons.";
				}
			}
			else{
				$ret['msg'] = "Invalid addon";
			}
		}
		else{
			$ret['msg'] = "Invalid user";
		}

		wp_send_json( $ret );
	}

	public function ajax_import_addons_settings(){
		
		$post_req = $_POST;	// Input var okay.

		$ret = array( 'error' => 1 );

		if( $this->wp_verify_nonce( $post_req['nonce'], 'optimisationio-import-export-nonce' ) ){

			if( ! isset( $post_req['data'] ) || ! is_string( $post_req['data'] ) ){
				$ret['msg'] = __("Invalid import arguments", "optimisationio");
				$ret['type'] = 'invalid_arguments';
			}
			else{
				
				$decoded_data = json_decode( base64_decode( $post_req['data'] ), true );

				if( $decoded_data ){
					foreach( self::$addons_slug as $key => $slug ){
						if( isset( $decoded_data[$slug] ) ){
							switch( $slug ){
								case self::$addons_slug[0]:	// 'wp-disable'.
									update_option( 'wpperformance_rev3a_settings', $decoded_data[$slug] );
									if( self::addon_activated($slug) ){
										WpPerformance::synchronize_discussion_data( $decoded_data[$slug] );
									}
									break;
								case self::$addons_slug[1]:	// 'cache-performance'.

									if( isset( $decoded_data[$slug]['cdn_sett'] ) ){
										update_option( 'Optimisationio_rev3a_cdnsettings', $decoded_data[$slug]['cdn_sett'] );
									}
							
									if( isset( $decoded_data[$slug]['general_sett'] ) ){
										update_option( 'Optimisationio_rev3a_settings', $decoded_data[$slug]['general_sett'] );
									}
							
									if( isset( $decoded_data[$slug]['db_opt_sett'] ) ){
										update_option( 'Optimisationio_rev3a_dboptimisesetting', $decoded_data[$slug]['db_opt_sett'] );
									}

									if( isset( $decoded_data[$slug]['gravatar_cache_sett'] ) ){
										update_option( 'Optimisationio_rev3a_gravatar_cache_settings', $decoded_data[$slug]['gravatar_cache_sett'] );
									}
									break;
								case self::$addons_slug[2]:	// 'wp-image-compression'.
							
									if( isset( $decoded_data[$slug]['general_sett'] ) ){
										update_option( '_wpimage_options', $decoded_data[$slug]['general_sett'] );
									}

									if( isset( $decoded_data[$slug]['cloudinary_sett'] ) ){
										update_option( '_wpimage_options_cloudinary', $decoded_data[$slug]['cloudinary_sett'] );
									}
							
									if( isset( $decoded_data[$slug]['lazy_load_sett'] ) ){
										update_option( '_wpimage_lazyload_options', $decoded_data[$slug]['lazy_load_sett'] );
									}

									if( isset( $decoded_data[$slug]['max_width'] ) ){
										update_option( 'wpimages_max_width', $decoded_data[$slug]['max_width'] );
									}

									if( isset( $decoded_data[$slug]['max_height'] ) ){
										update_option( 'wpimages_max_height', $decoded_data[$slug]['max_height'] );
									}

									if( isset( $decoded_data[$slug]['quality'] ) ){
										update_option( 'wpimages_quality', $decoded_data[$slug]['quality'] );
									}

									if( isset( $decoded_data[$slug]['quality_auto'] ) ){
										update_option( 'wpimages_quality_auto', $decoded_data[$slug]['quality_auto'] );
									}

									if( isset( $decoded_data[$slug]['max_width_library'] ) ){
										update_option( 'wpimages_max_width_library', $decoded_data[$slug]['max_width_library'] );
									}

									if( isset( $decoded_data[$slug]['max_height_library'] ) ){
										update_option( 'wpimages_max_height_library', $decoded_data[$slug]['max_height_library'] );
									}

									if( isset( $decoded_data[$slug]['max_width_other'] ) ){
										update_option( 'wpimages_max_width_other', $decoded_data[$slug]['max_width_other'] );
									}

									if( isset( $decoded_data[$slug]['max_height_other'] ) ){
										update_option( 'wpimages_max_height_other', $decoded_data[$slug]['max_height_other'] );
									}

									if( isset( $decoded_data[$slug]['bmp_to_jpg'] ) ){
										update_option( 'wpimages_bmp_to_jpg', $decoded_data[$slug]['bmp_to_jpg'] );
									}

									if( isset( $decoded_data[$slug]['png_to_jpg'] ) ){
										update_option( 'wpimages_png_to_jpg', $decoded_data[$slug]['png_to_jpg'] );
									}
									break;
							}
						}
					}

					$ret['msg'] = __("Settings imported successfully", "optimisationio");
					$ret['error'] = 0;
				}
				else{
					$ret['msg'] = __("Imported invalid data", "optimisationio");
					$ret['type'] = 'invalid_data';
				}
			}
		}

		wp_send_json($ret);
	}

	public function ajax_export_addons_settings(){
		
		$post_req = $_POST;	// Input var okay.
		
		$ret = array( 'error' => null );

		if( $this->wp_verify_nonce( $post_req['nonce'], 'optimisationio-import-export-nonce' ) ){

			if( ! isset( $post_req['data'] ) || ! is_array( $post_req['data'] ) ){
				$ret['msg'] = __("Invalid export arguments", "optimisationio");
				$ret['type'] = 'invalid_arguments';
			}
			else{

				$export = array();

				foreach( $post_req['data'] as $key => $val ){
					if( self::addon_activated($val) ){
						switch( $val ){
							case self::$addons_slug[0]:	// 'wp-disable'.
								$export[$val] = get_option( 'wpperformance_rev3a_settings' );
								break;
							case self::$addons_slug[1]:	// 'cache-performance'.
								$export[$val] = array(
									'cdn_sett' => get_option( 'Optimisationio_rev3a_cdnsettings' ),
									'general_sett' => get_option( 'Optimisationio_rev3a_settings' ),
									'db_opt_sett' => get_option( 'Optimisationio_rev3a_dboptimisesetting' ),
									'gravatar_cache_sett' => get_option( 'Optimisationio_rev3a_gravatar_cache_settings' ),
								);				
								break;
							case self::$addons_slug[2]:	// 'wp-image-compression'.
								$export[$val] = array(
									'general_sett' => get_option( '_wpimage_options' ),
									'cloudinary_sett' => get_option( '_wpimage_options_cloudinary' ),
									'lazy_load_sett' => get_option( '_wpimage_lazyload_options' ),
									'max_width' => get_option( 'wpimages_max_width' ),
									'max_height' => get_option( 'wpimages_max_height' ),
									'quality' => get_option( 'wpimages_quality' ),
									'quality_auto' => get_option( 'wpimages_quality_auto' ),
									'max_width_library' => get_option( 'wpimages_max_width_library' ),
									'max_height_library' => get_option( 'wpimages_max_height_library' ),
									'max_width_other' => get_option( 'wpimages_max_width_other' ),
									'max_height_other' => get_option( 'wpimages_max_height_other' ),
									'bmp_to_jpg' => get_option( 'wpimages_bmp_to_jpg' ),
									'png_to_jpg' => get_option( 'wpimages_png_to_jpg' ),
								);
								break;
						}
					}
				}

				$ret['decoded_export'] = $export;

				if( count( $export) ){
					$ret['export'] = base64_encode( json_encode( $export ) );
					$ret['error'] = 0;
				}
				else{
					$ret['msg'] = __("Can't find saved data to export", "optimisationio");
					$ret['type'] = 'not_saved_data';
				}
			}
		}
		else{
			$ret['msg'] = __("Failed data verification", "optimisationio");
			$ret['type'] = 'verification_fail';
		}

		wp_send_json($ret);
	}

	public static function init_addons(){

		$addon_homepage = array(
			self::$addons_slug[0] => 'https://wordpress.org/plugins/wp-disable/',
			self::$addons_slug[1] => 'https://wordpress.org/plugins/cache-performance/',
			self::$addons_slug[2] => 'https://wordpress.org/plugins/wp-image-compression/',
		);

		$addon_title = array(
			self::$addons_slug[0] => 'WP Disable',
			self::$addons_slug[1] => 'Cache for WordPress Performance',
			self::$addons_slug[2] => 'JPG, PNG Compression and Optimization',
		);

		$addon_file = array(
			self::$addons_slug[0] => 'wp-disable/wpperformance.php',
			self::$addons_slug[1] => 'cache-performance/optimisationio.php',
			self::$addons_slug[2] => 'wp-image-compression/wp-image-compression.php',
		);

		$addon_description = array(
			self::$addons_slug[0] => __( 'Improve WordPress performance by disabling unused items.', 'optimisationio' ),
			self::$addons_slug[1] => __( 'Simple efficient WordPress caching.', 'optimisationio' ),
			self::$addons_slug[2] => __( 'Image Compression and resizing - Setup under the Tools menu', 'optimisationio' ),
		);

		$addon_image = array(
			self::$addons_slug[0] => plugin_dir_url( dirname( __FILE__ ) ) . 'images/wp-disable.jpg',
			self::$addons_slug[1] => plugin_dir_url( dirname( __FILE__ ) ) . 'images/optimisation-1.jpg',
			self::$addons_slug[2] => plugin_dir_url( dirname( __FILE__ ) ) . 'images/wp-image-compression.jpg',
		);

		foreach (self::$addons_slug as $slug) {
			self::$addons[$slug] = array(
				'slug' => $slug,
				'title' => $addon_title[$slug],
				'file' => $addon_file[$slug],
				'thumb'	=> $addon_image[$slug],
				'homepage'	=> $addon_homepage[$slug],
				'download_link'	=> self::addon_download_link($slug),
				'installed' => self::addon_installed($slug, $addon_file[$slug]),
				'activated' => self::addon_activated($slug, $addon_file[$slug]),
				'description' => $addon_description[$slug],
			);
		}
	}

	public static function addon_installed( $slug, $file = false ){
		if($file){
			return file_exists( WP_PLUGIN_DIR . '/' . $file );
		}
		if( null === self::$addons ){
			self::init_addons();
		}
		return file_exists( WP_PLUGIN_DIR . '/' . self::$addons[$slug]['file'] );
	}

	public static function addon_activated( $slug, $file = false ){
		if($file){
			return self::addon_installed( $slug, $file ) && in_array( $file, apply_filters( 'active_plugins', get_option( 'active_plugins' ) ), true );
		}
		if( null === self::$addons ){
			self::init_addons();
		}

		return self::addon_installed( $slug, $file ) && in_array( self::$addons[$slug]['file'], apply_filters( 'active_plugins', get_option( 'active_plugins' ) ), true );
	}

	public static function active_addons_number(){
		if( null === self::$addons ){
			self::init_addons();
		}
		return ( defined('OPTIMISATIONIO_WP_DISABLE_ADDON') && (int) self::$addons['wp-disable']['activated'] ? 1 : 0 ) +
			   ( defined('OPTIMISATIONIO_CACHE_ADDON') && (int) self::$addons['cache-performance']['activated'] ? 1 : 0 ) +
			   ( defined('OPTIMISATIONIO_IMAGE_COMPRESSION_ADDON') && self::$addons['wp-image-compression']['activated'] ? 1 : 0 );
	}

	public static function addon_download_link($plugin_slug){

		$transient_id = 'optimisaitionio_addon_download_link[' . $plugin_slug . ']';

		$link = get_transient( $transient_id );

		if( false === $link ){

			if( ! function_exists('plugins_api') ){
				include_once( ABSPATH . 'wp-admin/includes/plugin-install.php' );
			}

			$plugin_info = plugins_api( 'plugin_information', array(
				'slug' => $plugin_slug,
				'fields' => array(
					'short_description' => false,
					'sections' => false,
					'requires' => false,
					'rating' => false,
					'ratings' => false,
					'downloaded' => false,
					'last_updated' => false,
					'added' => false,
					'tags' => false,
					'compatibility' => false,
					'homepage' => false,
					'donate_link' => false,
				),
			) );

			if ( ! is_wp_error( $plugin_info ) ) {
				$link = isset( $plugin_info->download_link ) ? $plugin_info->download_link : false;
			}

			if( $link ){
				set_transient( $transient_id, $link, DAY_IN_SECONDS );
			}
		}

		return $link;
	}

	public static function echo_stats_size( $valid, $size ){
		$e = __( 'n/a', 'optimisationio' );
		if( $valid ){
			$size = size_format( $size );
			$e = $size ? $size : '0 B';
		}
		echo $e;
	}

	public static function echo_addon_state_color( $activated, $installed ){
		echo $installed ? ( $activated ? 'green' : 'orange' ) : 'red';
	}

	public static function delete_transients(){
		foreach (self::$addons_slug as $slug) {
			delete_transient('optimisaitionio_addon_download_link[' . $slug . ']');
		}
	}
}
