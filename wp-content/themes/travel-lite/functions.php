<?php
/* 	Travel Theme's Functions
	Copyright: 2013, D5 Creation, www.d5creation.com
	Based on the Simplest D5 Framework for WordPress
	Since travel 1.0
*/

//	Set the content width based on the theme's travel and stylesheet.
	if ( ! isset( $content_width ) ) $content_width = 584;

// Load the D5 Framework Optios Page
	if ( !function_exists( 'optionsframework_init' ) ) {
	define( 'OPTIONS_FRAMEWORK_DIRECTORY', get_template_directory_uri() . '/inc/' );
	require_once dirname( __FILE__ ) . '/inc/options-framework.php';
	}


// 	Tell WordPress for wp_title in order to modify document title content
	function travel_filter_wp_title( $title ) {
    $site_name = get_bloginfo( 'name' );
    $filtered_title = $site_name . $title;
    return $filtered_title;
	}
	add_filter( 'wp_title', 'travel_filter_wp_title' );
	
	function travel_setup() {
	register_nav_menus( array( 'main-menu' => "Main Menu" ) );
// 	Tell WordPress for the Feed Link
	add_editor_style();
	add_theme_support( 'automatic-feed-links' );
	
// 	This theme uses Featured Images (also known as post thumbnails) for per-post/per-page Custom Header images
	add_theme_support( 'post-thumbnails' );
	set_post_thumbnail_size( 600, 200, true );
	add_image_size( 'fpage-thumb', 150, 100 );
	
		
// 	WordPress 3.4 Custom Background Support	
	$travel_custom_background = array(
	'default-color'          => 'e4e8e9',
	'default-image'          => '',
	);
	add_theme_support( 'custom-background', $travel_custom_background );
	
// 	WordPress 3.4 Custom Header Support				
	$travel_custom_header = array(
	'default-image'          => get_template_directory_uri() . '/images/logo.png',
	'random-default'         => false,
	'width'                  => 300,
	'height'                 => 80,
	'flex-height'            => false,
	'flex-width'             => false,
	'default-text-color'     => '000000',
	'header-text'            => false,
	'uploads'                => true,
	'wp-head-callback' 		 => '',
	'admin-head-callback'    => '',
	'admin-preview-callback' => '',
	);
	add_theme_support( 'custom-header', $travel_custom_header );
	}
	add_action( 'after_setup_theme', 'travel_setup' );

// 	Functions for adding script
	function travel_enqueue_scripts() {
	wp_enqueue_style('travel-style', get_stylesheet_uri());
	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) { 
		wp_enqueue_script( 'comment-reply' ); 
	}
	
	wp_enqueue_script( 'travel-menu-style', get_template_directory_uri(). '/js/menu.js', array('jquery'), false );
	wp_register_style('travel-gfonts1', '//fonts.googleapis.com/css?family=Oswald', false );
	wp_register_style('travel-gfonts2', '//fonts.googleapis.com/css?family=Lato:300', false );
	wp_enqueue_style('travel-gfonts1');
	wp_enqueue_style('travel-gfonts2');
	if ( of_get_option('responsive', '0') == '1' ) : wp_enqueue_style('travel-responsive', get_template_directory_uri(). '/style-responsive.css'); endif;
	}
	add_action( 'wp_enqueue_scripts', 'travel_enqueue_scripts' );



// 	Functions for adding some custom code within the head tag of site
	function travel_custom_code() {
	
	?>
	
	<style type="text/css">
	.site-title a, 
	.site-title a:active, 
	.site-title a:hover {
	
	color: #<?php echo get_header_textcolor(); ?>;
	}
	#container .thumb {background: url("<?php if (of_get_option('ft-back', get_template_directory_uri() . '/images/thumb-back.jpg') != '') : echo of_get_option('ft-back', get_template_directory_uri() . '/images/thumb-back.jpg'); else:  echo  get_template_directory_uri() . '/images/thumb-back.jpg'; endif; ?>") no-repeat scroll 0 0 #CCCCCC;}
	</style>
	
	<?php 
	
	}
	
	add_action('wp_head', 'travel_custom_code');
	
//	function tied to the excerpt_more filter hook.
	function travel_excerpt_length( $length ) {
	global $blExcerptLength;
	if ($blExcerptLength) {
    return $blExcerptLength;
	} else {
    return 50; //default value
    } }
	add_filter( 'excerpt_length', 'travel_excerpt_length', 999 );
	
	function travel_excerpt_more($more) {
       global $post;
	return '<a href="'. get_permalink($post->ID) . '" class="read-more">Read More...</a>';
	}
	add_filter('excerpt_more', 'travel_excerpt_more');

// Content Type Showing
	function travel_content() { the_content('<span class="read-more">Read More...</span>'); }
	function travel_creditline() { echo '<span class="credit">| Travel Theme by: <a href="http://d5creation.com" target="_blank"><img  src="' . get_template_directory_uri() . '/images/d5logofooter.png" /> D5 Creation</a> | Powered by: <a href="http://wordpress.org" target="_blank">WordPress</a></span>'; }

//	Get our wp_nav_menu() fallback, wp_page_menu(), to show a home link
	function travel_page_menu_args( $travel_args ) {
	$travel_args['show_home'] = true;
	return $travel_args;
	}
	add_filter( 'wp_page_menu_args', 'travel_page_menu_args' );


//	Registers the Widgets and Sidebars for the site
	function travel_widgets_init() {

	register_sidebar( array(
		'name' => 'Front Page Right Sidebar',
		'id' => 'sidebar-1',
		'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		'after_widget' => "</aside>",
		'before_title' => '<h3 class="widget-title">',
		'after_title' => '</h3>',
	) );

	register_sidebar( array(
		'name' => 'Other Pages Right Sidebar', 
		'id' => 'sidebar-2',
		'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		'after_widget' => "</aside>",
		'before_title' => '<h3 class="widget-title">',
		'after_title' => '</h3>',
	) );

	register_sidebar( array(
		'name' => 'Footer Area One', 
		'id' => 'sidebar-3',
		'description' => 'An optional widget area for your site footer', 
		'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		'after_widget' => "</aside>",
		'before_title' => '<h3 class="widget-title">',
		'after_title' => '</h3>',
	) );

	register_sidebar( array(
		'name' => 'Footer Area Two', 
		'id' => 'sidebar-4',
		'description' => 'An optional widget area for your site footer', 
		'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		'after_widget' => "</aside>",
		'before_title' => '<h3 class="widget-title">',
		'after_title' => '</h3>',
	) );

	register_sidebar( array(
		'name' => 'Footer Area Three', 
		'id' => 'sidebar-5',
		'description' => 'An optional widget area for your site footer', 
		'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		'after_widget' => "</aside>",
		'before_title' => '<h3 class="widget-title">',
		'after_title' => '</h3>',
	) );
	
	register_sidebar( array(
		'name' => 'Footer Area Four', 
		'id' => 'sidebar-6',
		'description' => 'An optional widget area for your site footer', 
		'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		'after_widget' => "</aside>",
		'before_title' => '<h3 class="widget-title">',
		'after_title' => '</h3>',
	) );
	}
	add_action( 'widgets_init', 'travel_widgets_init' );

	add_filter('the_title', 'travel_title');
	function travel_title($title) {
        if ( '' == $title ) {
            return '(Untitled)';
        } else {
            return $title;
        }
    }
	
	add_filter( 'wp_nav_menu_objects', 'travel_add_menu_parent_class' );
	function travel_add_menu_parent_class( $travelitems ) {
	$travelparents = array();
	foreach ( $travelitems as $travelitem ) {
	if ( $travelitem->menu_item_parent && $travelitem->menu_item_parent > 0 ) {
	$travelparents[] = $travelitem->menu_item_parent;
		}
	}
		
	foreach ( $travelitems as $travelitem ) {
	if ( in_array( $travelitem->ID, $travelparents ) ) {
	$travelitem->classes[] = 'menu-parent-item'; 
			}
		}
		
		return $travelitems;    
	}

//	Remove WordPress Custom Header Support for the theme travel
//	remove_theme_support('custom-header');

