<?php
/**
 * WP Advanced Gallery.
 *
 * @package   WP_Advanced_Gallery
 * @author    WPMount
 * @license   GPL-2.0+
 * @link      http://wpmount.com
 * @copyright 2016 WPMount
 */

/**
 * Plugin class. This class should ideally be used to work with the
 * public-facing side of the WordPress site.
 *
 * If you're interested in introducing administrative or dashboard
 * functionality, then refer to 'class-wp-advanced-gallery-admin.php'
 */
class WP_Advanced_Gallery {

	/**
	 * Plugin version name
	 *
	 * @since   1.0.0
	 *
	 * @var     string
	 */
	private static $version_name = 'wp_advanced_gallery_version';

	/**
	 * Plugin version, used for cache-busting of style and script file references.
	 *
	 * @since   1.0.0
	 *
	 * @var     string
	 */
	const VERSION = '1.0.0';

	/**
	 * Unique identifier for your plugin.
	 *
	 * The variable name is used as the text domain when internationalizing strings
	 * of text. Its value should match the Text Domain file header in the main
	 * plugin file.
	 *
	 * @since    1.0.0
	 *
	 * @var      string
	 */
	protected $plugin_slug = 'wp-advanced-gallery';

	/**
	 * Instance of this class.
	 *
	 * @since    1.0.0
	 *
	 * @var      object
	 */
	protected static $instance = null;

	/**
	 * Initialize the plugin by setting localization and loading public scripts
	 * and styles.
	 *
	 * @since     1.0.0
	 */
	private function __construct() {

		// Load plugin text domain
		add_action( 'init', array( $this, 'load_plugin_textdomain' ) );

		// Activate plugin when new blog is added
		add_action( 'wpmu_new_blog', array( $this, 'activate_new_site' ) );
        
        
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_css_js' ), 999 );

        // @TODO: Test wptexturize on live server
    //    remove_filter( 'the_content', 'wptexturize' );
    //    remove_filter( 'the_excerpt', 'wptexturize' );
   //     remove_filter( 'comment_text', 'wptexturize' );
  //      remove_filter( 'the_rss_content', 'wptexturize' );
        // [Gallery] Shortcode
        add_filter( 'post_gallery', array( $this, 'handle_gallery' ), 10, 2 );

        /**
        *----------------------------------------- 
        * Add custom fields to the Gallery Settings
        *-----------------------------------------
        */
        add_action( 'print_media_templates', array( $this, 'admin_gallery_settings') );
		

	}

	/**
	 * Return the plugin slug.
	 *
	 * @since    1.0.0
	 *
	 * @return    Plugin slug variable.
	 */
	public function get_plugin_slug() {
		return $this->plugin_slug;
	}

	/**
	 * Return the plugin version.
	 *
	 * @since    1.0.0
	 *
	 * @return    Plugin version variable.
	 */
	public function get_plugin_version() {
		return self::VERSION;
	}

	/**
	 * Return an instance of this class.
	 *
	 * @since     1.0.0
	 *
	 * @return    object    A single instance of this class.
	 */
	public static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Fired when the plugin is activated.
	 *
	 * @since    1.0.0
	 *
	 * @param    boolean    $network_wide    True if WPMU superadmin uses
	 *                                       "Network Activate" action, false if
	 *                                       WPMU is disabled or plugin is
	 *                                       activated on an individual blog.
	 */
	public static function activate( $network_wide ) {

		if ( function_exists( 'is_multisite' ) && is_multisite() ) {

			if ( $network_wide  ) {

				// Get all blog ids
				$blog_ids = self::get_blog_ids();

				foreach ( $blog_ids as $blog_id ) {

					switch_to_blog( $blog_id );
					self::single_activate();
				}

				restore_current_blog();

			} else {
				self::single_activate();
			}

		} else {
			self::single_activate();
		}

	}

	/**
	 * Fired when the plugin is deactivated.
	 *
	 * @since    1.0.0
	 *
	 * @param    boolean    $network_wide    True if WPMU superadmin uses
	 *                                       "Network Deactivate" action, false if
	 *                                       WPMU is disabled or plugin is
	 *                                       deactivated on an individual blog.
	 */
	public static function deactivate( $network_wide ) {

		if ( function_exists( 'is_multisite' ) && is_multisite() ) {

			if ( $network_wide ) {

				// Get all blog ids
				$blog_ids = self::get_blog_ids();

				foreach ( $blog_ids as $blog_id ) {

					switch_to_blog( $blog_id );
					self::single_deactivate();

				}

				restore_current_blog();

			} else {
				self::single_deactivate();
			}

		} else {
			self::single_deactivate();
		}

	}

	/**
	 * Fired when a new site is activated with a WPMU environment.
	 *
	 * @since    1.0.0
	 *
	 * @param    int    $blog_id    ID of the new blog.
	 */
	public function activate_new_site( $blog_id ) {

		if ( 1 !== did_action( 'wpmu_new_blog' ) ) {
			return;
		}

		switch_to_blog( $blog_id );
		self::single_activate();
		restore_current_blog();

	}

	/**
	 * Get all blog ids of blogs in the current network that are:
	 * - not archived
	 * - not spam
	 * - not deleted
	 *
	 * @since    1.0.0
	 *
	 * @return   array|false    The blog ids, false if no matches.
	 */
	private static function get_blog_ids() {

		global $wpdb;

		// get an array of blog ids
		$sql = "SELECT blog_id FROM $wpdb->blogs
			WHERE archived = '0' AND spam = '0'
			AND deleted = '0'";

		return $wpdb->get_col( $sql );

	}

	/**
	 * Fired for each blog when the plugin is activated.
	 *
	 * @since    1.0.0
	 */
	private static function single_activate() {
		update_option( self::$version_name, self::VERSION );
		// @TODO: Define activation functionality here
	}

	/**
	 * Fired for each blog when the plugin is deactivated.
	 *
	 * @since    1.0.0
	 */
	private static function single_deactivate() {
		// @TODO: Define deactivation functionality here
	}

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		$domain = $this->plugin_slug;
		$locale = apply_filters( 'plugin_locale', get_locale(), $domain );

		load_textdomain( $domain, trailingslashit( WP_LANG_DIR ) . $domain . '/' . $domain . '-' . $locale . '.mo' );
		load_plugin_textdomain( $domain, FALSE, basename( plugin_dir_path( dirname( __FILE__ ) ) ) . '/languages/' );

	}
    
    
    
    /**
     *----------------------------------------- 
     * Handle default WordPress Gallery [gallery] shortcode output
     *
     * http://wordpress.stackexchange.com/questions/4343/how-to-customise-the-output-of-the-wp-image-gallery-shortcode-from-a-plugin
     * http://robido.com/wordpress/wordpress-gallery-filter-to-modify-the-html-output-of-the-default-gallery-shortcode-and-style/
     *
     * Modified function gallery_shortcode(), search for word 'MODIFIED'
     * https://core.trac.wordpress.org/browser/trunk/src/wp-includes/media.php
     * or
     * https://github.com/WordPress/WordPress/blob/master/wp-includes/media.php
     *-----------------------------------------
     */
	public function handle_gallery($output, $attr){

	$post = get_post();
	static $instance = 0;
	$instance++;

	$html5 = current_theme_supports( 'html5', 'gallery' );
	$atts = shortcode_atts( array(
		'order'      => 'ASC',
		'orderby'    => 'menu_order ID',
		'id'         => $post ? $post->ID : 0,
		'itemtag'    => $html5 ? 'figure'     : 'dl',
		'icontag'    => $html5 ? 'div'        : 'dt',
		'captiontag' => $html5 ? 'figcaption' : 'dd',
		'columns'    => 3,
		'size'       => 'thumbnail',
		'include'    => '',
		'exclude'    => '',
		'link'       => '',
		'type'       => 'default', // MODIFIED: Custom Type added
		'captions'       => 'false', // MODIFIED: Display caption
		'caption_info'       => '{{TITLE}}', // MODIFIED: Custom Caption Info
		'gallery_thumb_style'       => 'none', // MODIFIED: Thumbnail Style
		'gallery_animation_effect'       => 'no-effect', // MODIFIED: Animation Effect
		'enable_mp'       => 'false', // MODIFIED: Magnific Popup for default and isotope galleries
		'display_filters'       => 'false', // MODIFIED: Isotope Filters
		'autoplay'       => 'false', // MODIFIED: Owl option
		'navigation'       => 'false', // MODIFIED: Owl option
		'pagination'       => 'false', // MODIFIED: Owl option
		'hoverstop'       => 'false', // MODIFIED: Owl option
		'delay'       => '5000' // MODIFIED: BG Slider (Vegas) option
	), $attr, 'gallery' );
	$id = intval( $atts['id'] );
	if ( ! empty( $atts['include'] ) ) {
		$_attachments = get_posts( array( 'include' => $atts['include'], 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => $atts['order'], 'orderby' => $atts['orderby'] ) );
		$attachments = array();
		foreach ( $_attachments as $key => $val ) {
			$attachments[$val->ID] = $_attachments[$key];
		}
	} elseif ( ! empty( $atts['exclude'] ) ) {
		$attachments = get_children( array( 'post_parent' => $id, 'exclude' => $atts['exclude'], 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => $atts['order'], 'orderby' => $atts['orderby'] ) );
	} else {
		$attachments = get_children( array( 'post_parent' => $id, 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => $atts['order'], 'orderby' => $atts['orderby'] ) );
	}
	if ( empty( $attachments ) ) {
		return '';
	}
	if ( is_feed() ) {
		$output = "\n";
		foreach ( $attachments as $att_id => $attachment ) {
			$output .= wp_get_attachment_link( $att_id, $atts['size'], true ) . "\n";
		}
		return $output;
	}
	$itemtag = tag_escape( $atts['itemtag'] );
	$captiontag = tag_escape( $atts['captiontag'] );
	$icontag = tag_escape( $atts['icontag'] );
	$valid_tags = wp_kses_allowed_html( 'post' );
	if ( ! isset( $valid_tags[ $itemtag ] ) ) {
		$itemtag = 'dl';
	}
	if ( ! isset( $valid_tags[ $captiontag ] ) ) {
		$captiontag = 'dd';
	}
	if ( ! isset( $valid_tags[ $icontag ] ) ) {
		$icontag = 'dt';
	}
	$columns = intval( $atts['columns'] );
	$itemwidth = $columns > 0 ? floor(100/$columns) : 100;
	$float = is_rtl() ? 'right' : 'left';
	$selector = "gallery-{$instance}";
	$gallery_style = '';
	
	$type=$atts['type']; // MODIFIED: Added $type
	/**
	 * Filter whether to print default gallery styles.
	 *
	 * @since 3.1.0
	 *
	 * @param bool $print Whether to print default gallery styles.
	 *                    Defaults to false if the theme supports HTML5 galleries.
	 *                    Otherwise, defaults to true.
	 */
	if ( apply_filters( 'use_default_gallery_style', ! $html5 ) ) {
		$gallery_style = "
		<style type='text/css'>
			#{$selector} {
				margin: auto;
			}
			#{$selector} .gallery-item {
				float: {$float};
				margin-top: 10px;
				text-align: center;
				width: {$itemwidth}%;
			}
			#{$selector} img {
				border: 2px solid #cfcfcf;
			}
			#{$selector} .gallery-caption {
				margin-left: 0;
			}
			/* see gallery_shortcode() in wp-includes/media.php */
		</style>\n\t\t";
	}
	$size_class = sanitize_html_class( $atts['size'] );
	$gallery_extra_class = '';// MODIFIED: Additional classes based on new options
	$gallery_extra_class .= ( isset( $atts['enable_mp'] ) && ( $atts['enable_mp'] == 'true' ) ) ? 'gallery-mfp-enabled ' : '';
	if( ( $type == 'default' ) || ( $type == 'isotope' ) ){
        $thumb_style = isset( $atts['gallery_thumb_style'] ) ? $atts['gallery_thumb_style'] : 'none ';
        $animation_effect = isset( $atts['gallery_animation_effect'] ) ? $atts['gallery_animation_effect'] : 'no-effect ';
		$gallery_extra_class .= 'hover-theme-' . $thumb_style . ' ';
        $gallery_extra_class .= 'animation-effect-' . $animation_effect . ' ';
	}
	$gallery_div = "<div id='$selector' class='gallery row galleryid-{$id} gallery-columns-{$columns} gallery-size-{$size_class} gallery-type-{$type} {$gallery_extra_class}' style='margin:0px;'>";
	/**
	 * Filter the default gallery shortcode CSS styles.
	 *
	 * @since 2.5.0
	 *
	 * @param string $gallery_style Default gallery shortcode CSS styles.
	 * @param string $gallery_div   Opening HTML div container for the gallery shortcode output.
	 */
	$output = apply_filters( 'gallery_style', $gallery_style . $gallery_div );
		
		/**
		*----------------------------------------- 
		* MODIFIED: Add filter functions
		*-----------------------------------------
		*/
		$gallery_terms=array();
		foreach( $attachments as $id => $attachment){
			$attachment_term=wp_get_post_terms($id,'media_tags',array('fields'=>'ids'));
			if(is_array($attachment_term)){
				$gallery_terms=array_merge($gallery_terms,$attachment_term);
			}
		}
		
		// Isotope Filters
		if((count($gallery_terms)>0)&&($type=='isotope')&&($atts['display_filters']=='true')){
		
			$output.='<div class="gallery-controls">';
			
			$gallery_terms = get_terms( 'media_tags', array(
				'hide_empty' => 0,
				'include'		 => array_values($gallery_terms)
			 ) );


			$output.="<button data-filter='all'>".__('all','wp-advanced-gallery')."</button>";
			foreach($gallery_terms as $gallery_term){
				$output.="<button data-filter='{$gallery_term->term_id}'>{$gallery_term->name}</button>";
			}
			$output.='</div>'; //end .gallery-controls
		}
		// END MODIFIED
		/**
		*----------------------------------------- 
		* MODIFIED: Gallery Options
		*-----------------------------------------
		*/
		$gallery_options='';
		$gallery_options.='data-captions="'.(($atts['captions']=='true')?'true':'false').'" ';
		if($type=='owl'){
			// Owl options
			$gallery_options.='data-autoplay="'.(($atts['autoplay']=='true')?'true':'false').'" ';
			$gallery_options.='data-navigation="'.(($atts['navigation']=='true')?'true':'false').'" ';
			$gallery_options.='data-pagination="'.(($atts['pagination']=='true')?'true':'false').'" ';
			$gallery_options.='data-hoverstop="'.(($atts['hoverstop']=='true')?'true':'false').'" ';
		}
			

		$output.='<div class="gallery-content" '.$gallery_options.'>';
		
	$i = 0;
	foreach ( $attachments as $id => $attachment ) {
		$attr = ( trim( $attachment->post_excerpt ) ) ? array( 'aria-describedby' => "$selector-$id" ) : '';
		if ( ! empty( $atts['link'] ) && 'file' === $atts['link'] ) {
			$image_output = wp_get_attachment_link( $id, $atts['size'], false, false, false, $attr );
		} elseif ( ! empty( $atts['link'] ) && 'none' === $atts['link'] ) {
			$image_output = wp_get_attachment_image( $id, $atts['size'], false, $attr );
		} else {
			$image_output = wp_get_attachment_link( $id, $atts['size'], true, false, false, $attr );
		}
		$image_meta  = wp_get_attachment_metadata( $id );
		$orientation = '';
		if ( isset( $image_meta['height'], $image_meta['width'] ) ) {
			$orientation = ( $image_meta['height'] > $image_meta['width'] ) ? 'portrait' : 'landscape';
		}
		
		/**
		*----------------------------------------- 
		* MODIFIED: 
		* - Add media_tags
		* - CaptionTag option
		*-----------------------------------------
		*/
		$item_tags='';
		$item_tags_arr=wp_get_post_terms($id, 'media_tags', array("fields" => "ids")); 
		if(is_array($item_tags_arr)){
			$item_tags=join(' ',$item_tags_arr);
		}
		//Caption Tags
        $gallery_caption_info = isset( $atts['caption_info'] ) ? $atts['caption_info'] : '{{TITLE}}';
		$gallery_caption_info = apply_filters( 'advanced_gallery_caption', $gallery_caption_info );
		$item_tags_names=wp_get_post_terms($id, 'media_tags', array("fields" => "names")); 
		$gallery_caption_tags=array(); // Array of allowed tags
		//Assign values to tags
		$gallery_caption_tags['{{TITLE}}']=$attachment->post_title;
		if(is_array($item_tags_names)){
		$gallery_caption_tags['{{TAGS}}']=join(', ',$item_tags_names);
		}
		$gallery_caption_tags['{{CONTENT}}']=$attachment->post_content;
		$gallery_caption_tags['{{EXCERPT}}']=$attachment->post_excerpt;
		$gallery_caption_tags['{{SEP}}']='<div class="line-sep"></div>';
		// If tag-value isn't empty - wrap it into <p>-tag
		$gallery_caption_tags['{{TITLE}}']=$gallery_caption_tags['{{TITLE}}']?'<p class="gallery-caption-title">'.$gallery_caption_tags['{{TITLE}}'].'</p>':'';
		$gallery_caption_tags['{{TAGS}}']=isset($gallery_caption_tags['{{TAGS}}'])?'<p class="gallery-caption-tags">'.$gallery_caption_tags['{{TAGS}}'].'</p>':'';
		$gallery_caption_tags['{{CONTENT}}']=$gallery_caption_tags['{{CONTENT}}']?'<p class="gallery-caption-content">'.$gallery_caption_tags['{{CONTENT}}'].'</p>':'';
		$gallery_caption_tags['{{EXCERPT}}']=$gallery_caption_tags['{{EXCERPT}}']?'<p class="gallery-caption-description">'.$gallery_caption_tags['{{EXCERPT}}'].'</p>':'';
		// Replace tags in option with tag-values
		$gallery_caption_info=str_replace(array_keys($gallery_caption_tags),array_values($gallery_caption_tags),$gallery_caption_info);
		// END MODIFIED
		
		$output .= "<{$itemtag} class='gallery-item' data-media-tags='{$item_tags}'>";
		$output .= "
			<{$icontag} class='gallery-icon {$orientation}'>
				$image_output
			</{$icontag}>";
		if ( $captiontag && ($gallery_caption_info!='') ) {
			$output .= "
				<{$captiontag} class='wp-caption-text gallery-caption' id='$selector-$id'>
				" . wptexturize($gallery_caption_info) . "
				</{$captiontag}>";
		}
		$output .= "</{$itemtag}>";
		if ( ! $html5 && $columns > 0 && ++$i % $columns == 0 ) {
			$output .= '<br style="clear: both" />';
		}
	} 
	$output.='</div>'; // end .gallery-content
	if ( ! $html5 && $columns > 0 && $i % $columns !== 0 ) {
		$output .= "
			<br style='clear: both' />";
	}
	$output .= "
		</div>\n"; // end .gallery ($gallery_div)

 
	return $output;
	}
    


	/**
	*----------------------------------------- 
	* Define your backbone template;
	* The "tmpl-" prefix is required, and your input field should have a data-setting attribute matching the shortcode name
	* http://wordpress.stackexchange.com/questions/90114/enhance-media-manager-for-gallery
	*-----------------------------------------
	*/
	public function admin_gallery_settings(){	?>
		<script type="text/template" id="tmpl-theme-custom-gallery-settings">
			<label class="setting">
				<span><?php _e('Gallery Type','wp-advanced-gallery'); ?></span>
				<select data-setting="type">
					<option value="default"><?php _e('Default','wp-advanced-gallery'); ?></option>
					<option value="isotope"><?php _e('Isotope','wp-advanced-gallery'); ?></option>
					<option value="owl"><?php _e('Owl Carousel','wp-advanced-gallery'); ?></option>
				</select>
			</label>
			<label class="setting">
				<span><?php _e('Display Captions','wp-advanced-gallery'); ?></span>
				<input type="checkbox" data-setting="captions">
			</label>
			<label class="setting">
				<span><?php _e('Caption Info','wp-advanced-gallery'); ?></span>
				<input type="text" data-setting="caption_info"><br>
                <span>Available tags: &#123;&#123;TITLE&#125;&#125; &#123;&#123;TAGS&#125;&#125; &#123;&#123;CONTENT&#125;&#125; &#123;&#123;EXCERPT&#125;&#125; &#123;&#123;SEP&#125;&#125;</span>
			</label>
			<span><strong><?php _e('Extra Options','wp-advanced-gallery'); ?></strong></span>
		</script>
		<script type="text/template" id="tmpl-theme-custom-gallery-settings-group-default">
   			<label class="setting">
				<span><?php _e('Hover Style','wp-advanced-gallery'); ?></span>
				<select data-setting="gallery_thumb_style">
                    <option value='none'>None</option>
                    <option value='light'>Light</option>
                    <option value='dark'>Dark</option>
                </select>
			</label>   
   			<label class="setting">
				<span><?php _e('Animation Effect','wp-advanced-gallery'); ?></span>
				<select data-setting="gallery_animation_effect">
                    <option value='no-effect'>None</option>
                    <option value='zoom-in'>Zoom In</option>
                    <option value='zoom-out'>Zoom Out</option>
                </select>
			</label>   
			<label class="setting">
				<span><?php _e('Enable Magnific Popup','wp-advanced-gallery'); ?></span>
				<input type="checkbox" data-setting="enable_mp">
			</label>
		</script>
		<script type="text/template" id="tmpl-theme-custom-gallery-settings-group-isotope">		
   			<label class="setting">
				<span><?php _e('Hover Style','wp-advanced-gallery'); ?></span>
				<select data-setting="gallery_thumb_style">
                    <option value='none'>None</option>
                    <option value='light'>Light</option>
                    <option value='dark'>Dark</option>
                </select>
			</label>   
   			<label class="setting">
				<span><?php _e('Animation Effect','wp-advanced-gallery'); ?></span>
				<select data-setting="gallery_animation_effect">
                    <option value='no-effect'>None</option>
                    <option value='zoom-in'>Zoom In</option>
                    <option value='zoom-out'>Zoom Out</option>
                </select>
			</label> 
			<label class="setting">
				<span><?php _e('Enable Magnific Popup','wp-advanced-gallery'); ?></span>
				<input type="checkbox" data-setting="enable_mp">
			</label>
			<label class="setting">
				<span><?php _e('Display Isotope Filters','wp-advanced-gallery'); ?></span>
				<input type="checkbox" data-setting="display_filters">
			</label>
		</script>
		<script type="text/template" id="tmpl-theme-custom-gallery-settings-group-owl">		
			<label class="setting">
				<span><?php _e('Enable Autoplay','wp-advanced-gallery'); ?></span>
				<input type="checkbox" data-setting="autoplay">
			</label>
			<label class="setting">
				<span><?php _e('Stop on hover','wp-advanced-gallery'); ?></span>
				<input type="checkbox" data-setting="hoverstop">
			</label>
			<label class="setting">
				<span><?php _e('Display Navigation','wp-advanced-gallery'); ?></span>
				<input type="checkbox" data-setting="navigation">
			</label>
			<label class="setting">
				<span><?php _e('Display Pagination','wp-advanced-gallery'); ?></span>
				<input type="checkbox" data-setting="pagination">
			</label>
		</script>

		<script>

			jQuery(document).ready(function(){

				// add your shortcode attribute and its default value to the
				// gallery settings list; $.extend should work as well...
				_.extend(wp.media.gallery.defaults, {
					type: 'default',
					enable_mp: ''
				});

				// merge default gallery settings template with yours
				wp.media.view.Settings.Gallery = wp.media.view.Settings.Gallery.extend({
					extraView: null,
					initialize: function(){
						 this.model.on('change:type', this.changeExtraOptionsGroup, this);
						 this.listenTo(this.model, 'change:type', this.render);
					},
					template: function(view){
						this.extraView=view;	
						return wp.media.template('gallery-settings')(view)
								 + wp.media.template('theme-custom-gallery-settings')(view)
								 + wp.media.template('theme-custom-gallery-settings-group-'+view.model.type)(view);
					},
					render: function(){
						wp.media.view.Settings.prototype.render.apply( this, arguments );
						return this;
					},
					changeExtraOptionsGroup: function(object){
						this.extraView.model.type=object.attributes.type;
						this.template(this.extraView);
					}
				});

			});

		</script>
		<?php

	}
    
    
    public function enqueue_css_js(){
        /*
        *----------------------------------------- 
        * Styles 
        *-----------------------------------------
        */
        
        /* Register Styles */
        wp_register_style( $this->plugin_slug.'_css', plugins_url( 'assets/css/style.css', __FILE__ ), array() );
        
        // Magnific Popup
        wp_register_style( $this->plugin_slug.'_magnific_popup_css', plugins_url( 'assets/js/magnific-popup/magnific-popup.css', __FILE__ ), array() );
        // Owl Carousel
        wp_register_style( $this->plugin_slug.'_owl_css', plugins_url( 'assets/js/owl-carousel/owl.carousel.css', __FILE__ ), array() );
        // Owl Theme
        wp_register_style( $this->plugin_slug.'_owl_theme_css', plugins_url( 'assets/js/owl-carousel/owl.theme.css', __FILE__ ), array(  $this->plugin_slug.'_owl_css') );

        /* Enqueue Styles */

        wp_enqueue_style($this->plugin_slug.'_css');
        wp_enqueue_style($this->plugin_slug.'_magnific_popup_css');
        wp_enqueue_style($this->plugin_slug.'_owl_css');
        wp_enqueue_style($this->plugin_slug.'_owl_theme_css');

        
        
        /*
        *----------------------------------------- 
        * JavaScript 
        *-----------------------------------------
        */
        
        /* Register JS */
        wp_register_script( $this->plugin_slug.'_js', plugins_url( 'assets/js/script.js', __FILE__), array('jquery'), '', true );

        // Images Loaded
        wp_register_script( $this->plugin_slug.'_imagesloaded_js', plugins_url( 'assets/js/imagesloaded.pkgd.min.js', __FILE__ ), array('jquery'), '', true );
        // Isotope
        wp_register_script( $this->plugin_slug.'_isotope_js', plugins_url( 'assets/js/isotope.pkgd.min.js', __FILE__ ), array('jquery'), '', true );
        // Magnific Popup
        wp_register_script( $this->plugin_slug.'_magnific_popup_js', plugins_url( 'assets/js/magnific-popup/jquery.magnific-popup.min.js', __FILE__ ), array('jquery'), '', true );
        // Owl Carousel
        wp_register_script( $this->plugin_slug.'_owl_js', plugins_url( 'assets/js/owl-carousel/owl.carousel.min.js', __FILE__ ), array('jquery'), '', true );


        /* Enqueue JS */
        wp_enqueue_script( $this->plugin_slug.'_js');
        wp_enqueue_script( $this->plugin_slug.'_imagesloaded_js');
        wp_enqueue_script( $this->plugin_slug.'_isotope_js');
        wp_enqueue_script( $this->plugin_slug.'_magnific_popup_js');
        wp_enqueue_script( $this->plugin_slug.'_owl_js');
    }



}
