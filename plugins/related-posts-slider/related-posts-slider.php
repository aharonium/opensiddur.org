<?php
/* error_log( 'RPS main plugin file loaded' );
/*
Plugin Name: Related Posts Slider
Plugin URI: https://github.com/aharonium/related-posts-slider
Description: Related posts slider creates a very attractive slider of the related posts or/and pages for a WordPress post or page. The slider is a lightweight jQuery implementation of the related post functionality. 
Version: 2.3	
Author: SliderVilla, edited by Aharon Varady
Author URI: https://github.com/aharonium
WordPress version supported: 3.0 and above
*/

/*  Copyright 2011-2015  SliderVilla.com  (email : tedeshpa@gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
if ( ! defined( 'CF5_RPS_PLUGIN_BASENAME' ) )
	define( 'CF5_RPS_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
define("CF5_RPS_VER","2.3",false);
define('CF5_RPS_URLPATH', trailingslashit( WP_PLUGIN_URL . '/' . plugin_basename( dirname(__FILE__) ) ) );
if ( ! defined( 'CF5_RPS_FORMAT_DIR' ) )
	define( 'CF5_RPS_FORMAT_DIR', WP_PLUGIN_DIR.'/'.str_replace(basename( __FILE__),"",plugin_basename(__FILE__)).'/formats/h_carousel/styles/' );

function cf5_rps_url( $path = '' ) {
	return plugins_url( $path, __FILE__ );
}
// Create Text Domain For Translations
load_plugin_textdomain('cf5_rps', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/');
//on activation, your Related Posts Slider options will be populated. Here a single option is used which is actually an array of multiple options
function activate_cf5_rps() {
	$cf5_rps_opts1 = get_option('cf5_rps_options');
	$cf5_rps_opts2 =array('per_page' => '4',
					   'num'=>'10',
	                   'height'=>'250',
					   'scroll'=>'1',
					   'bgcolor'=>'#ffffff',
					   'fgcolor'=>'#f1f1f1',
					   'hvcolor'=>'#6d6d6d',
					   'hvtext_color'=>'#ffffff',
					   'obrwidth'=>'1',
					   'obrcolor'=>'#F1F1F1',
					   'ibrwidth'=>'1',
					   'ibrcolor'=>'#DFDFDF',
					   'img_align'=>'none',
					   'img_width'=>'100',
					   'img_pick'=>array('1','preview_thumb','1','1','1','1'), //use custom field/key, name of the key, use post featured image, pick the image attachment, attachment order,scan images
					   'img_height'=>'100',
					   'crop'=>'0',
					   'sldr_title'=>'Related Posts',
					   'stitle_font'=>'Georgia,Times New Roman,Times,serif',
					   'stitle_color'=>'#333333',
					   'stitle_size'=>'14',
					   'stitle_weight'=>'bold',
					   'stitle_style'=>'normal',
					   'ltitle_font'=>'Verdana,Geneva,sans-serif',
					   'ltitle_size'=>'12',
					   'ltitle_color'=>'#444444',
					   'ltitle_words'=>'8',
					   'target'=>'_self',
					   'insert'=>'content_down',
					   'format' => 'h_carousel', 
					   'plugin' => 'inbuilt',
					   'format_style' => 'plain');
	if ($cf5_rps_opts1) {
	    $cf5_rps = $cf5_rps_opts1 + $cf5_rps_opts2;
		update_option('cf5_rps_options',$cf5_rps);
	}
	else {
		$cf5_rps_opts1 = array();	
		$cf5_rps = $cf5_rps_opts1 + $cf5_rps_opts2;
		add_option('cf5_rps_options',$cf5_rps);		
	}
}

register_activation_hook( __FILE__, 'activate_cf5_rps' );
global $cf5_rps,$rps_slider_shown;
$cf5_rps = get_option('cf5_rps_options');
require_once (dirname (__FILE__) . '/includes/cf5-rps-get-the-image.php');
require_once (dirname (__FILE__) . '/includes/cf5-rps-slider-formats.php');


function cf5_rps_wp_init() {
	cf5_rps_wp_init_h_carousel();
}

add_action( 'wp', 'cf5_rps_wp_init' );

function cf5_rps_wp_head() {
	cf5_rps_wp_head_h_carousel();
}

add_action( 'wp_head', 'cf5_rps_wp_head' );

/*
 * No wp_footer hook anymore. The old cf5_rps_wp_footer() dispatcher existed
 * to support multiple slider formats, some of which (the vertical 'default'
 * format) used wp_footer to print a small inline script. Now that h_carousel
 * (Swiper-based) is the only supported format, and h_carousel has no
 * wp_footer function of its own, this hook has nothing left to do.
 */

function get_related_posts_slider($echo=true,$type=array('post')){
    global $cf5_rps;
	$rps_posts='';
	$related_plugin = $cf5_rps['plugin'];
	if(empty($related_plugin) or !$related_plugin) {
	  $related_plugin = 'inbuilt';
	}
//Inbuilt Related Posts Pull
	if($related_plugin == 'inbuilt') {
		$rps_posts=get_cf5_inbuilt_related_posts();
	}
	
//if using YARPP
    if (
        $related_plugin === 'yarpp' &&
        function_exists('yarpp_related') &&
        function_exists('get_cf5_yarpp_related_posts')
    ) {
        $rps_posts = get_cf5_yarpp_related_posts($type, array(), false);
    }

	return cf5_rps_h_carousel($echo,$rps_posts);
}

//Pull Related Posts
require_once (dirname (__FILE__) . '/includes/cf5-rps-pull-related-posts.php');

function cf5_rps_automatic_insertion($content){
 global $cf5_rps,$post,$wp_query;
	 if(is_singular()) {
		if($cf5_rps['insert']=='content_down'){
		   $content=$content.'&nbsp;[rps]';
		}
		if($cf5_rps['insert']=='content_up'){
		   $content='[rps]&nbsp;'.$content;
		}
	 }
	return $content;
}
if($cf5_rps['insert']=='content_down' or $cf5_rps['insert'] == 'content_up') {
   add_filter( 'the_content', 'cf5_rps_automatic_insertion', 5 );
}

function cf5_rps_shortcode($atts) {
	extract(shortcode_atts(array(
	), $atts));

	if(is_singular()){
	   return get_related_posts_slider($echo=false);
	}
	else{return '';}
}
add_shortcode('rps', 'cf5_rps_shortcode');

/* class CF5_RPS_Widget extends WP_Widget {
	function CF5_RPS_Widget() {
		$widget_options = array('classname' => 'cf5_rps_wclass', 'description' => 'Insert Related Posts Slider' );
		parent::__construct('cf5_rps_wid', 'Related Posts Slider', $widget_options);
	}

	function widget($args, $instance) {
		extract($args, EXTR_SKIP);
	    extract( $args );
		
		echo $before_widget;

		if ( $title ) 
		   echo $before_title . $title . $after_title;
		get_related_posts_slider();
		echo $after_widget;
	}

	function update($new_instance, $old_instance) {
	    $instance = $old_instance;
        return $instance;
	}

	function form($instance) {
	}
} */ 
/* add_action( 'widgets_init', create_function('', 'return register_widget("CF5_RPS_Widget");') ); */


class CF5_RPS_Widget extends WP_Widget {

	function __construct() {
		$widget_options = array(
			'classname'   => 'cf5_rps_wclass',
			'description' => 'Insert Related Posts Slider'
		);
		parent::__construct(
			'cf5_rps_wid',
			'Related Posts Slider',
			$widget_options
		);
	}

	function widget($args, $instance) {
		extract($args, EXTR_SKIP);

		echo $before_widget;

		if (!empty($instance['title'])) {
			echo $before_title . esc_html($instance['title']) . $after_title;
		}

		get_related_posts_slider();

		echo $after_widget;
	}

	function update($new_instance, $old_instance) {
		$instance = $old_instance;
		$instance['title'] = sanitize_text_field($new_instance['title'] ?? '');
		return $instance;
	}

	function form($instance) {
		$title = $instance['title'] ?? '';
		?>
		<p>
			<label for="<?php echo esc_attr($this->get_field_id('title')); ?>">
				<?php esc_html_e('Title:'); ?>
			</label>
			<input class="widefat"
			       id="<?php echo esc_attr($this->get_field_id('title')); ?>"
			       name="<?php echo esc_attr($this->get_field_name('title')); ?>"
			       type="text"
			       value="<?php echo esc_attr($title); ?>" />
		</p>
		<?php
	}
}

add_action('widgets_init', function () {
	register_widget('CF5_RPS_Widget');
});


function rps_slider_plugin_url( $path = '' ) {
	global $wp_version;
	return plugins_url( $path, __FILE__ );
}

function cf5_rps_word_limiter( $text, $limit = 40 , $display_dots = true) {
    $text = str_replace(']]>', ']]&gt;', $text);
	//Not using strip_tags as to accomodate the 'retain html tags' feature
	//$text = strip_tags($text);
	
    $explode = explode(' ',$text);
    $string  = '';

    $dots = '...';
    if(count($explode) <= $limit){
        $dots = '';
    }
    for($i=0;$i<$limit;$i++){
    	if(!isset($explode[$i])) break;
        else $string .= $explode[$i]." ";
    }
    if ($dots) {
        $string = substr($string, 0, strlen($string));
    }
	if($display_dots)
      return $string.$dots;
	else
	  return $string;
}
require_once (dirname (__FILE__) . '/includes/settings.php');
?>
