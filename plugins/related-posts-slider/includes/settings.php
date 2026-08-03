<?php 
// function for adding settings page to wp-admin
function cf5_rps_settings() {
    // Add a new submenu under Options:
    add_options_page('Related Posts Slider', 'Related Posts Slider', 'manage_options', 'rps-settings', 'cf5_rps_settings_page');
}
function cf5_rps_admin_head() {
  if ( isset($_GET['page']) && 'rps-settings' == $_GET['page']  ) {
		//wp_print_scripts( 'farbtastic' );
		//wp_print_styles( 'farbtastic' );
		wp_enqueue_style( 'wp-color-picker' );
   		wp_enqueue_script( 'wp-color-picker' );	
?>
<script type="text/javascript">
jQuery(document).ready(function() { 
	//for WP-color-picker
	jQuery('.wp-color-picker-field').wpColorPicker();
});
</script>

<style type="text/css">
.color-picker-wrap {
		position: absolute;
 		display: none; 
		background: #fff;
		border: 3px solid #ccc;
		padding: 3px;
		z-index: 1000;
	}
</style>

<?php 
  }
}

add_action( 'admin_head', 'cf5_rps_admin_head' );



// This function displays the page content for the Iframe Embed For YouTube Options submenu
function cf5_rps_settings_page() {
?>
<div class="wrap">
<h2>Related Posts Slider</h2>
<form  method="post" action="options.php">
<div id="poststuff" class="metabox-holder has-right-sidebar"> 

<div style="float:left;width:55%;">
<?php
settings_fields('cf5_rps-group');
$cf5_rps = get_option('cf5_rps_options');
// Defensive normalization for PHP 8+
if ( empty( $cf5_rps['img_pick'] ) || ! is_array( $cf5_rps['img_pick'] ) ) {
    $cf5_rps['img_pick'] = array();
}

// Ensure expected indexes exist
$cf5_rps['img_pick'] = array_replace(
    array(
        0 => '0', // Use custom field
        1 => '',  // Custom field name
        2 => '0', // Featured image
        3 => '0', // Attached images
        4 => '1', // Attachment order
        5 => '0', // Scan post content
    ),
    $cf5_rps['img_pick']
);
?>
<h2><?php _e('Overall Slider Settings','cf5_rps'); ?></h2> 
<table class="form-table">

<tr valign="top">
    <th scope="row"><?php _e('Related Posts Plugin to use','cf5_rps'); ?><small><?php _e('(You need to install and activate the selected plugin in order to make RPS run.)','cf5_rps'); ?></small></th>
    <td><select name="cf5_rps_options[plugin]" id="cf5_rps_plugin" >
    <option value="inbuilt" <?php if ($cf5_rps['plugin'] == "inbuilt"){ echo "selected";}?> >Inbuilt (Default)</option>
	<option value="yarpp" <?php if ($cf5_rps['plugin'] == "yarpp"){ echo "selected";}?> >YARPP</option>
    </select>
    </td>
</tr>

<tr valign="top">
    <th scope="row"><?php _e('Related Slider Format','cf5_rps'); ?></th>
    <td>
    <?php _e('Horizontal Carousel','cf5_rps'); ?>
    <input type="hidden" name="cf5_rps_options[format]" value="h_carousel" />
    <small><?php _e('This is currently the only supported slider format.','cf5_rps'); ?></small>
    </td>
</tr>

<tr valign="top">
<th scope="row"><label for="cf5_rps_options[format_style]"><?php _e('Select the style for your Slider','cf5_rps'); ?></label></th> 
<td>
<?php $format_directory = CF5_RPS_FORMAT_DIR; ?>
<select name="cf5_rps_options[format_style]" id="cf5_rps_format_style" >
<?php
if ($handle = opendir($format_directory)) {
    while (false !== ($file = readdir($handle))) { 
     if($file != '.' and $file != '..') { ?>
      <option value="<?php echo $file;?>" <?php if ($cf5_rps['format_style'] == $file){ echo "selected";}?> ><?php echo $file;?></option>
 <?php  } }
    closedir($handle);
}
?>
</select><small><?php _e('The CSS settings below are only applicable and visible in case you select "default" style.','cf5_rps'); ?></small></td></tr>

<tr valign="top">
<th scope="row"><?php _e('No. of Posts in one group of List Section/Visible Posts','cf5_rps'); ?></th>
<td><input type="text" name="cf5_rps_options[per_page]" id="cf5_rps_no_posts" class="small-text" value="<?php echo $cf5_rps['per_page']; ?>" /></td>
</tr>

<tr valign="top">
<th scope="row"><?php _e('Max Number of Related Posts in Slider','cf5_rps'); ?></th>
<td><input type="text" name="cf5_rps_options[num]" id="cf5_rps_num" class="small-text" value="<?php echo $cf5_rps['num']; ?>" /></td>
</tr>

<tr valign="top">
<th scope="row"><?php _e('Posts to Scroll / Slide in one transition','cf5_rps'); ?></th>
<td><input type="text" name="cf5_rps_options[scroll]" id="cf5_rps_scroll" class="small-text" value="<?php echo $cf5_rps['scroll']; ?>" /></td>
</tr>

<tr valign="top" <?php if($cf5_rps['format_style']!='default') echo 'style="display:none;"';?>>
<th scope="row"><?php _e('Slider Height','cf5_rps'); ?></th>
<td><input type="text" name="cf5_rps_options[height]" id="cf5_rps_height" class="small-text" value="<?php echo $cf5_rps['height']; ?>" />&nbsp;px</td>
</tr>

    <tr valign="top" <?php if($cf5_rps['format_style']!='default') echo 'style="display:none;"';?>>
    <th scope="row"><?php _e('Slider Background Color','cf5_rps'); ?></th>
    <td><input type="text" name="cf5_rps_options[bgcolor]" id="color_value_1" value="<?php echo $cf5_rps['bgcolor']; ?>" class="wp-color-picker-field" data-default-color="#ddb6b6" /></td> 
    </tr>
    
    <tr valign="top" <?php if($cf5_rps['format_style']!='default') echo 'style="display:none;"';?>>
    <th scope="row"><?php _e('Slider Foregound Color','cf5_rps'); ?></th>
    <td><input type="text" name="cf5_rps_options[fgcolor]" id="color_value_2" value="<?php echo $cf5_rps['fgcolor']; ?>" class="wp-color-picker-field" data-default-color="#0b0d32" /></td> 
    </tr>
    
    <tr valign="top" <?php if($cf5_rps['format_style']!='default') echo 'style="display:none;"';?>>
    <th scope="row"><?php _e('Background Color for Hover Section','cf5_rps'); ?></th>
    <td><input type="text" name="cf5_rps_options[hvcolor]" id="color_value_3" value="<?php echo $cf5_rps['hvcolor']; ?>" class="wp-color-picker-field" data-default-color="#6d6d6d" /></td> 
    </tr>
    
    <tr valign="top" <?php if($cf5_rps['format_style']!='default') echo 'style="display:none;"';?>>
    <th scope="row"><?php _e('Text Color For Hover Section','cf5_rps'); ?></th>	
    <td><input type="text" name="cf5_rps_options[hvtext_color]" id="color_value_9" value="<?php echo $cf5_rps['hvtext_color']; ?>" class="wp-color-picker-field" data-default-color="#ba4545" /></td> 
    </tr>
    
    <tr valign="top" <?php if($cf5_rps['format_style']!='default') echo 'style="display:none;"';?>>
    <th scope="row"><?php _e('Outer Border Thickness','cf5_rps'); ?></th>
    <td><input type="text" name="cf5_rps_options[obrwidth]" id="cf5_rps_obrwidth" class="small-text" value="<?php echo $cf5_rps['obrwidth']; ?>" />&nbsp;px &nbsp;(put 0 if no border is required)</td> 
	
    </tr>
    
    <tr valign="top" <?php if($cf5_rps['format_style']!='default') echo 'style="display:none;"';?>>
    <th scope="row"><?php _e('Outer Border Color','cf5_rps'); ?></th>
    <td><input type="text" name="cf5_rps_options[obrcolor]" id="color_value_4" value="<?php echo $cf5_rps['obrcolor']; ?>" class="wp-color-picker-field" data-default-color="#161816" /></td> 
    </tr>
    
    <tr valign="top" <?php if($cf5_rps['format_style']!='default') echo 'style="display:none;"';?>>
    <th scope="row"><?php _e('Inner Border Thickness','cf5_rps'); ?></th>
    <td><input type="text" name="cf5_rps_options[ibrwidth]" id="cf5_rps_obrwidth" class="small-text" value="<?php echo $cf5_rps['ibrwidth']; ?>" />&nbsp;px &nbsp;(put 0 if no border is required)</td>
    </tr>
    
    <tr valign="top" <?php if($cf5_rps['format_style']!='default') echo 'style="display:none;"';?>>
    <th scope="row"><?php _e('Inner Border Color','cf5_rps'); ?></th>
    <td><input type="text" name="cf5_rps_options[ibrcolor]" id="color_value_5" value="<?php echo $cf5_rps['ibrcolor']; ?>" class="wp-color-picker-field" data-default-color="#e60000" /></td> 
    </tr>

</table> 

<h2><?php _e('Slider Title','cf5_rps'); ?></h2> 
<table class="form-table">

<tr valign="top">
<th scope="row"><?php _e('Slider Title Text','cf5_rps'); ?></th>
<td><input type="text" name="cf5_rps_options[sldr_title]" class="regular-text code" value="<?php echo $cf5_rps['sldr_title']; ?>" /></td>
</tr>

    <tr valign="top" <?php if($cf5_rps['format_style']!='default') echo 'style="display:none;"';?>>
    <th scope="row"><?php _e('Title Font','cf5_rps'); ?></th>
    <td><select name="cf5_rps_options[stitle_font]" id="cf5_rps_stitle_font" >
    <option value="Arial,Helvetica,sans-serif" <?php if ($cf5_rps['stitle_font'] == "Arial,Helvetica,sans-serif"){ echo "selected";}?> >Arial,Helvetica,sans-serif</option>
    <option value="Calibri,Times,serif" <?php if ($cf5_rps['stitle_font'] == "Calibri,Times,serif"){ echo "selected";}?> >Calibri,Times,serif</option>
    <option value="Century Schoolbook,Times,serif" <?php if ($cf5_rps['stitle_font'] == "Century Schoolbook,Times,serif"){ echo "selected";}?> >Century Schoolbook,Times,serif</option>
    <option value="Courier New,Courier,monospace" <?php if ($cf5_rps['stitle_font'] == "Courier New,Courier,monospace"){ echo "selected";}?> >Courier New,Courier,monospace</option>
    <option value="Geneva,Verdana,sans-serif" <?php if ($cf5_rps['stitle_font'] == "Geneva,Verdana,sans-serif"){ echo "selected";}?> >Geneva,Verdana,sans-serif</option>
    <option value="Georgia,Times New Roman,Times,serif" <?php if ($cf5_rps['stitle_font'] == "Georgia,Times New Roman,Times,serif"){ echo "selected";} ?> >Georgia,Times New Roman,Times,serif</option>
    <option value="Helvetica,Arial,sans-serif" <?php if ($cf5_rps['stitle_font'] == "Helvetica,Arial,sans-serif"){ echo "selected";}?> >Helvetica,Arial,sans-serif</option>
    <option value="Times New Roman,Times,serif" <?php if ($cf5_rps['stitle_font'] == "Times New Roman,Times,serif"){ echo "selected";}?> >Times New Roman,Times,serif</option>
    <option value="Trebuchet MS,Times,serif" <?php if ($cf5_rps['stitle_font'] == "Trebuchet MS,Times,serif"){ echo "selected";}?> >Trebuchet MS,Times,serif</option>
    <option value="Verdana,Geneva,sans-serif" <?php if ($cf5_rps['stitle_font'] == "Verdana,Geneva,sans-serif"){ echo "selected";}?> >Verdana,Geneva,sans-serif</option>
    </select>
    </td>
    </tr>
    
    <tr valign="top" <?php if($cf5_rps['format_style']!='default') echo 'style="display:none;"';?>>
    <th scope="row"><?php _e('Slider Title Font Color','cf5_rps'); ?></th>
    <td><input type="text" name="cf5_rps_options[stitle_color]" id="color_value_10" value="<?php echo $cf5_rps['stitle_color']; ?>" class="wp-color-picker-field" data-default-color="#2e462b" /></td>
    </tr>
    
    <tr valign="top" <?php if($cf5_rps['format_style']!='default') echo 'style="display:none;"';?>>
    <th scope="row"><?php _e('Slider Title Font Size','cf5_rps'); ?></th>
    <td><input type="text" name="cf5_rps_options[stitle_size]" id="cf5_rps_stitle_size" class="small-text" value="<?php echo $cf5_rps['stitle_size']; ?>" />&nbsp;px</td>
    </tr>
    
    <tr valign="top" <?php if($cf5_rps['format_style']!='default') echo 'style="display:none;"';?>>
    <th scope="row"><?php _e('Slider Title Font Weight','cf5_rps'); ?></th>
    <td><select name="cf5_rps_options[stitle_weight]" id="cf5_rps_stitle_weight" >
    <option value="bold" <?php if ($cf5_rps['stitle_weight'] == "bold"){ echo "selected";}?> >Bold</option>
    <option value="normal" <?php if ($cf5_rps['stitle_weight'] == "normal"){ echo "selected";}?> >Normal</option>
    </select>
    </td>
    </tr>
    
    <tr valign="top" <?php if($cf5_rps['format_style']!='default') echo 'style="display:none;"';?>>
    <th scope="row"><?php _e('Slider Title Font Style','cf5_rps'); ?></th>
    <td><select name="cf5_rps_options[stitle_style]" id="cf5_rps_stitle_style" >
    <option value="italic" <?php if ($cf5_rps['stitle_style'] == "italic"){ echo "selected";}?> >Italic</option>
    <option value="normal" <?php if ($cf5_rps['stitle_style'] == "normal"){ echo "selected";}?> >Normal</option>
    </select>
    </td>
    </tr>
</table>

<h2><?php _e('Thumbnail Image','cf5_rps'); ?></h2> 
<p><?php _e('Settings for the thumbnail image in Preview Section','cf5_rps'); ?></p> 
<table class="form-table">

<tr valign="top"> 
<th scope="row"><?php _e('Image Pick Preferences','cf5_rps'); ?> <small><?php _e('(The first one is having priority over second, the second on third and so on. Atleast select one option!)','cf5_rps'); ?></small></th> 
<td><fieldset><legend class="screen-reader-text"><span><?php _e('Image Pick Sequence','cf5_rps'); ?> <small><?php _e('(The first one is having priority over second, the second having priority on third and so on)','cf5_rps'); ?></small> </span></legend> 
<input name="cf5_rps_options[img_pick][0]" type="checkbox" value="1" <?php checked('1', $cf5_rps['img_pick'][0]); ?>  /> <?php _e('Use Custom Field/Key','cf5_rps'); ?> &nbsp; &nbsp; 
<input type="text" name="cf5_rps_options[img_pick][1]" class="text" value="<?php echo $cf5_rps['img_pick'][1]; ?>" /> <?php _e('Name of the Custom Field/Key','cf5_rps'); ?> 
<br />
<input name="cf5_rps_options[img_pick][2]" type="checkbox" value="1" <?php checked('1', $cf5_rps['img_pick'][2]); ?>  /> <?php _e('Use Featured Post/Thumbnail (Wordpress 3.0 +  feature)','cf5_rps'); ?> &nbsp; <br />
<input name="cf5_rps_options[img_pick][3]" type="checkbox" value="1" <?php checked('1', $cf5_rps['img_pick'][3]); ?>  /> <?php _e('Consider Images attached to the post','cf5_rps'); ?>  &nbsp; &nbsp; 
<input type="text" name="cf5_rps_options[img_pick][4]" class="small-text" value="<?php echo $cf5_rps['img_pick'][4]; ?>" /> <?php _e('Order of the Image attachment to pick','cf5_rps'); ?>  &nbsp; <br />
<input name="cf5_rps_options[img_pick][5]" type="checkbox" value="1" <?php checked('1', $cf5_rps['img_pick'][5]); ?>  /> <?php _e('Scan images from the post, in case there is no attached image to the post','cf5_rps'); ?> &nbsp; 
</fieldset></td> 
</tr> 

<tr valign="top">
<th scope="row"><?php _e('Wordpress Image Extract Size','cf5_rps'); ?> </th>
<td><select name="cf5_rps_options[crop]" id="cf5_rps_img_crop" >
<option value="0" <?php if ($cf5_rps['crop'] == "0"){ echo "selected";}?> ><?php _e('Full','cf5_rps'); ?></option>
<option value="1" <?php if ($cf5_rps['crop'] == "1"){ echo "selected";}?> ><?php _e('Large','cf5_rps'); ?></option>
<option value="2" <?php if ($cf5_rps['crop'] == "2"){ echo "selected";}?> ><?php _e('Medium','cf5_rps'); ?></option>
<option value="3" <?php if ($cf5_rps['crop'] == "3"){ echo "selected";}?> ><?php _e('Thumbnail','cf5_rps'); ?></option>
</select>
<small><?php _e('This is because, for every image upload to the media gallery WordPress creates four sizes of the same image. So you can choose which to load in the slider and then specify the actual size.','cf5_rps'); ?></small>
</td>
</tr>

    <tr valign="top" <?php if($cf5_rps['format_style']!='default') echo 'style="display:none;"';?>>
    <th scope="row"><?php _e('Align to','cf5_rps'); ?></th>
    <td><select name="cf5_rps_options[img_align]" id="cf5_rps_img_align" >
    <option value="left" <?php if ($cf5_rps['img_align'] == "left"){ echo "selected";}?> ><?php _e('Left','cf5_rps'); ?></option>
    <option value="right" <?php if ($cf5_rps['img_align'] == "right"){ echo "selected";}?> ><?php _e('Right','cf5_rps'); ?></option>
    <option value="none" <?php if ($cf5_rps['img_align'] == "none"){ echo "selected";}?> ><?php _e('Center','cf5_rps'); ?></option>
    </select>
    </td>
    </tr>
    
    <tr valign="top"> 
    <th scope="row"><label for="cf5_rps_options[img_width]"><?php _e('Image Width','cf5_rps'); ?></label></th> 
    <td><input type="text" name="cf5_rps_options[img_width]" class="small-text" value="<?php echo $cf5_rps['img_width']; ?>" />&nbsp;px&nbsp;&nbsp; </td> 
    </tr> 
    
    <tr valign="top">
    <th scope="row"><?php _e('Maximum Height/Height of the Image','cf5_rps'); ?></th>
    <td><input type="text" name="cf5_rps_options[img_height]" class="small-text" value="<?php echo $cf5_rps['img_height']; ?>" />&nbsp;px &nbsp;&nbsp; <?php _e('(This is necessary in order to keep the maximum image height in control)','cf5_rps'); ?></td>
    </tr>

</table>

<h2><?php _e('List Section','cf5_rps'); ?></h2> 
<table class="form-table">

    <tr valign="top" <?php if($cf5_rps['format_style']!='default') echo 'style="display:none;"';?>>
    <th scope="row"><?php _e('Font','cf5_rps'); ?></th>
    <td><select name="cf5_rps_options[ltitle_font]" id="cf5_rps_ltitle_font" >
    <option value="Arial,Helvetica,sans-serif" <?php if ($cf5_rps['ltitle_font'] == "Arial,Helvetica,sans-serif"){ echo "selected";}?> >Arial,Helvetica,sans-serif</option>
    <option value="Calibri,Times,serif" <?php if ($cf5_rps['ltitle_font'] == "Calibri,Times,serif"){ echo "selected";}?> >Calibri,Times,serif</option>
    <option value="Century Schoolbook,Times,serif" <?php if ($cf5_rps['ltitle_font'] == "Century Schoolbook,Times,serif"){ echo "selected";}?> >Century Schoolbook,Times,serif</option>
    <option value="Courier New,Courier,monospace" <?php if ($cf5_rps['ltitle_font'] == "Courier New,Courier,monospace"){ echo "selected";}?> >Courier New,Courier,monospace</option>
    <option value="Geneva,Verdana,sans-serif" <?php if ($cf5_rps['ltitle_font'] == "Geneva,Verdana,sans-serif"){ echo "selected";}?> >Geneva,Verdana,sans-serif</option>
    <option value="Georgia,Times New Roman,Times,serif" <?php if ($cf5_rps['ltitle_font'] == "Georgia,Times New Roman,Times,serif"){ echo "selected";} ?> >Georgia,Times New Roman,Times,serif</option>
    <option value="Helvetica,Arial,sans-serif" <?php if ($cf5_rps['ltitle_font'] == "Helvetica,Arial,sans-serif"){ echo "selected";}?> >Helvetica,Arial,sans-serif</option>
    <option value="Times New Roman,Times,serif" <?php if ($cf5_rps['ltitle_font'] == "Times New Roman,Times,serif"){ echo "selected";}?> >Times New Roman,Times,serif</option>
    <option value="Trebuchet MS,Times,serif" <?php if ($cf5_rps['ltitle_font'] == "Trebuchet MS,Times,serif"){ echo "selected";}?> >Trebuchet MS,Times,serif</option>
    <option value="Verdana,Geneva,sans-serif" <?php if ($cf5_rps['ltitle_font'] == "Verdana,Geneva,sans-serif"){ echo "selected";}?> >Verdana,Geneva,sans-serif</option>
    </select>
    </td>
    </tr>
    
    <tr valign="top" <?php if($cf5_rps['format_style']!='default') echo 'style="display:none;"';?>>
    <th scope="row"><?php _e('Font Color','cf5_rps'); ?></th>
    <td><input type="text" name="cf5_rps_options[ltitle_color]" id="color_value_6" value="<?php echo $cf5_rps['ltitle_color']; ?>" class="wp-color-picker-field" data-default-color="#c91313" /></td>
    </tr>
    
    <tr valign="top" <?php if($cf5_rps['format_style']!='default') echo 'style="display:none;"';?>>
    <th scope="row"><?php _e('Font Size','cf5_rps'); ?></th>
    <td><input type="text" name="cf5_rps_options[ltitle_size]" id="cf5_rps_ltitle_size" class="small-text" value="<?php echo $cf5_rps['ltitle_size']; ?>" />&nbsp;px</td>
    </tr>

<tr valign="top">
<th scope="row"><?php _e('Max words in List Title','cf5_rps'); ?></th>
<td><input type="text" name="cf5_rps_options[ltitle_words]" id="cf5_rps_ltitle_words" class="small-text" value="<?php echo $cf5_rps['ltitle_words']; ?>" />&nbsp;<?php _e('words','cf5_rps'); ?></td>
</tr>

</table>

<table class="form-table">
    <tr valign="top">
    <th scope="row"><?php _e('Target attribute for the continue reading link/post permalink','cf5_rps'); ?></th>
    <td><select name="cf5_rps_options[target]" id="target" >
    <option value="_self" <?php if ($cf5_rps['target'] == "_self"){ echo "selected";}?> >_self</option>
    <option value="_blank" <?php if ($cf5_rps['target'] == "_blank"){ echo "selected";}?> >_blank</option>
    </select>
    </td>
    </tr>
</table>

<h2><?php _e('Manual/Automatic Insertion','cf5_rps'); ?></h2> 
<small><?php _e('By default the related posts slider is inserted automatically below the content area of the post. But you can select manual insertion (either using templte tag or shortcode or widget) or can select to insert it automatically above the content area of the post.','cf5_rps'); ?></small>
<table class="form-table">
    <tr valign="top">
    <th scope="row"><?php _e('Insert the slider','cf5_rps'); ?></th>
    <td><select name="cf5_rps_options[insert]" id="cf5_rps_insert" >
    <option value="content_down" <?php if ($cf5_rps['insert'] == "content_down"){ echo "selected";}?> ><?php _e('Below the Content','cf5_rps'); ?></option>
    <option value="content_up" <?php if ($cf5_rps['insert'] == "content_up"){ echo "selected";}?> ><?php _e('Above the Content','cf5_rps'); ?></option>
    <option value="manual" <?php if ($cf5_rps['insert'] == "manual"){ echo "selected";}?> ><?php _e('Manually','cf5_rps'); ?></option>
    </select>
    </td>
    </tr>
    
</table>

<p class="submit">
<input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
</p>

<div style="clear:both;"></div>

</div>

   

</div> <!--end of poststuff -->

</form>
</div> <!--end of float wrap -->

<?php	
}
// Hook for adding admin menus
if ( is_admin() ){ // admin actions
  add_action('admin_menu', 'cf5_rps_settings');
  add_action( 'admin_init', 'register_cf5_rps_settings' ); 
} 
function register_cf5_rps_settings() { // whitelist options
  register_setting( 'cf5_rps-group', 'cf5_rps_options' );
}

/*
function cf5_rps_admin_url( $query = array() ) {
	global $plugin_page;
	if ( ! isset( $query['page'] ) )
		$query['page'] = $plugin_page;
	$path = 'admin.php';
	if ( $query = build_query( $query ) )
		$path .= '?' . $query;
	$url = admin_url( $path );
	return esc_url_raw( $url );
} */

function cf5_rps_admin_url( $query = array() ) {
	global $plugin_page;

	if ( ! isset( $query['page'] ) && ! empty( $plugin_page ) ) {
		$query['page'] = $plugin_page;
	}

	$path = 'admin.php';

	$query_string = http_build_query( $query );
	if ( $query_string ) {
		$path .= '?' . $query_string;
	}

	return esc_url_raw( admin_url( $path ) );
}


function cf5_rps_plugin_action_links( $links, $file ) {
	if ( $file != CF5_RPS_PLUGIN_BASENAME )
		return $links;
	$url = cf5_rps_admin_url( array( 'page' => 'rps-settings' ) );
	$settings_link = '<a href="' . esc_attr( $url ) . '">'
		. esc_html( __( 'Settings') ) . '</a>';

	array_unshift( $links, $settings_link );
	return $links;
}
add_filter( 'plugin_action_links', 'cf5_rps_plugin_action_links', 10, 2 );//adds the link to the settings page on main Plugins admin page
?>
