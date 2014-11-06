<?php
/**
 * travel Options Page
 * @ Copyright: D5 Creation, All Rights, www.d5creation.com
 */

function optionsframework_option_name() {

	// This gets the theme name from the stylesheet
	$themename = 'travel';
	$optionsframework_settings = get_option( 'optionsframework' );
	$optionsframework_settings['id'] = $themename;
	update_option( 'optionsframework', $optionsframework_settings );
}

/**
 * Defines an array of options that will be used to generate the settings page and be saved in the database.
 * When creating the 'id' fields, make sure to use all lowercase and no spaces.
 *
 * If you are making your theme translatable, you should replace 'travel'
 * with the actual text domain for your theme.  Read more:
 * http://codex.wordpress.org/Function_Reference/load_theme_textdomain
 */


function optionsframework_options() {
	
	add_filter( 'wp_default_editor', create_function('', 'return "html";') );
	
	$options[] = array(
		'name' => 'Travel Options',
		'type' => 'heading');
		
	$options[] = array(
		'desc' => '<div class="infohead"><span class="donation">If you like this FREEE Theme You can consider for a small Donation to us. Your Donation will be spent for the Disadvantaged Children and Students. You can visit our <a href="http://d5creation.com/donate/" target="_blank"><strong>DONATION PAGE</strong></a> and Take your decision.</span><br /><br /><span class="donation"> Need More Features and Options including Exciting Slide and 100+ Advanced Features? Try <a href="http://d5creation.com/theme/travel/" target="_blank"><strong>Travel Extend</strong></a>.</span><br /> <br /><span class="donation"> You can Visit the Travel Extend Demo <a href="http://demo.d5creation.com/wp/themes/travel/" target="_blank"><strong>Here</strong></a>.</span></div>',
		'type' => 'info');
	
	$options[] = array(
		'name' => 'Front Page Heading', 
		'desc' => 'Front Page Heading', 
		'id' => 'fpheading',
		'std' => 'Test Place, Sample Country',
		'type' => 'text');
	
	$options[] = array(
		'name' => 'Post Featured Image Background',
		'desc' => 'Upload an image for the Common Background of Featured/ Thumbnail Image on every Post. 600px X 200px image is recommended. If your post has no featured image attached, this background will be displayed. Otherwise your post featured image will be displayed. You are recommended to attach the Featured Image during new Post Creation or Editing.',
		'id' => 'ft-back',
		'std' => get_template_directory_uri() . '/images/thumb-back.jpg',
		'type' => 'upload');
		
	$options[] = array(
		'name' => 'Use Responsive Layout', 
		'desc' => 'Check the Box if you want the Responsive Layout of your Website', 
		'id' => 'responsive',
		'std' => '1',
		'type' => 'checkbox');	
		
	$fposttype = array( '1' => 'Do nor Show any Post or Page in the Front Page.', '2' => 'Show Posts or Page in the Front Page as Per WordPress Reading Settings.' );
	
	$options[] = array(
		'name' => 'Front Page Post/Page Visibility', 
		'desc' => 'Select Option how you want to show or do not show Posts/Pages in the Front Page', 
		'id' => 'fpostex',
		'std' => '1',
		'type' => 'radio',
		'options' => $fposttype);
		
	// Social Contacts
	
	$options[] = array(
		'desc' => '<span class="featured-area-title">Social Links</span>',
		'type' => 'info');
		
	$options[] = array(
		'name' => 'Google Plus Link',
		'desc' => 'Input your Google Plus URL here.',
		'id' => 'gplus-link',
		'std' => '#',
		'type' => 'text');

	$options[] = array(
		'name' => 'My Contact Link',
		'desc' => 'Input your Contact URL here.',
		'id' => 'con-link',
		'std' => '#',
		'type' => 'text');

// Front Page Fearured Images
	
	$options[] = array(
		'desc' => '<span class="featured-area-title">Featured Boxes</span>',
		'type' => 'info');
		
	foreach (range(1, 3 ) as $fbsinumber) {
	
	$options[] = array(
		'desc' => '<span class="featured-area-title">' . 'Front Page Featured Image: ' . $fbsinumber . '</span>',
		'type' => 'info');
		
	$options[] = array(
		'name' => 'Featured Image',
		'desc' => 'Upload an image for the Featured Box. 270px X 200px image is recommended. If you do not want to show anything here leave the box blank.',
		'id' => 'featured-image' . $fbsinumber,
		'std' => get_template_directory_uri() . '/images/featured-image' . $fbsinumber . '.jpg',
		'type' => 'upload');
	
	$options[] = array(
		'name' => 'Featured Title', 
		'desc' => 'Input the First Part of Featured Title here. Please limit it within 10 Letters. If you do not want to show anything here leave the box blank.', 
		'id' => 'featured01-title' . $fbsinumber,
		'std' => 'Featured',
		'type' => 'text',
		'class' => 'mini');
	
	$options[] = array(
		'desc' => 'Input the Second Part of Featured Title here. Please limit it within 10 Letters. If you do not want to show anything here leave the box blank.', 
		'id' => 'featured02-title' . $fbsinumber,
		'std' => 'Image',
		'type' => 'text',
		'class' => 'mini');
	
	$options[] = array(
		'name' => 'Description', 
		'desc' => 'Input the description of Featured Areas. Please limit the words within 30 so that the layout should be clean and attractive. You can also input any HTML, Videos or iframe here. But Please keep in mind about the limitation of Width of the box.', 
		'id' => 'featured-description' . $fbsinumber,
		'std' => 'A Smart way of Natural Presence. This is a Test Description and you can change it from the Theme Options.',
		'type' => 'textarea' );	
	
	}
	
// Front Page Fearured Contents
	
	$options[] = array(
		'desc' => '<span class="featured-area-title">Featured Contents</span>',
		'type' => 'info');	
		
	foreach (range(1, 3 ) as $fbsinumberd) {
	
	$options[] = array(
		'desc' => '<span class="featured-area-title">' . 'Front Page Featured Content: 0' . $fbsinumberd . '</span>',
		'type' => 'info');
	
	$options[] = array(
		'name' => 'Featured Title',
		'desc' => 'Input the First Part of Featured Title here. Plese limit it within 10 Letters. If you do not want to show anything here leave the box blank.',
		'id' => 'fcontent01-title' . $fbsinumberd,
		'std' => 'Travel',
		'type' => 'text',
		'class' => 'mini');

	$options[] = array(
		'desc' => 'Input the Second Part of Featured Title here. Plese limit it within 10 Letters. If you do not want to show anything here leave the box blank.',
		'id' => 'fcontent02-title' . $fbsinumberd,
		'std' => 'Content',
		'type' => 'text',
		'class' => 'mini');
		
	$options[] = array(
		'name' => 'Featured Image',
		'desc' => 'Upload an image for the Featured Box. 270px X 200px image is recommended. If you do not want to show anything here leave the box blank.',
		'id' => 'fcontent-image' . $fbsinumberd,
		'std' => get_template_directory_uri() . '/images/fcontent-image' . $fbsinumberd . '.jpg',
		'type' => 'upload');

	$options[] = array(
		'name' => 'Featured Description',
		'desc' => 'Input the description of Featured Areas. Please limit the words within 30 so that the layout should be clean and attractive. You can also input any HTML, Videos or iframe here. But Please keep in mind about the limitation of Width of the box.',
		'id' => 'fcontent-description' . $fbsinumberd,
		'std' => '<b>A Smart way of Natural Presence</b><p>A Smart way of Natural Presence. This is a Test Content and you can change it from the Theme Options.</p>',
		'type' => 'textarea' );

	}
	

	return $options;
}

/*
 * This is an example of how to add custom scripts to the options panel.
 * This example shows/hides an option when a checkbox is clicked.
 */

add_action('optionsframework_custom_scripts', 'optionsframework_custom_scripts');

function optionsframework_custom_scripts() { ?>

<?php
}