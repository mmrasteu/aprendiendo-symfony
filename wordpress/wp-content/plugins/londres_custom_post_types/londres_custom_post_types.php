<?php
/*
Plugin Name: Londres Custom Post Types
Plugin URI: http://upperthemes.com
Description: [ GUTENBERG READY ] Testimonials, Partners, Team and Projects Posts + Widgets. We do not intended this plugin for distribution. We are only responsible for its usage with Londres theme.
Version: 2.6
Author: Upper
Author URI: http://upperthemes.com
*/


// don't load directly
if ( ! defined( 'ABSPATH' )) {
	die( '-1' );
}

/* defines */
if (!defined('LONDRES_PORTFOLIO_POST_TYPE')){
	if (!defined('LONDRES_SHORTNAME')) define('LONDRES_SHORTNAME', 'londres');
	$portfolio_permalink = get_option(LONDRES_SHORTNAME."_portfolio_permalink");
	if (!get_option(LONDRES_SHORTNAME."_portfolio_permalink")) define("LONDRES_PORTFOLIO_POST_TYPE", "portfolio");
	else define("LONDRES_PORTFOLIO_POST_TYPE", get_option(LONDRES_SHORTNAME."_portfolio_permalink"));
}
if (!defined('LONDRES_TESTIMONIALS_POST_TYPE')){
	define("LONDRES_TESTIMONIALS_POST_TYPE", 'testimonials');
}
if (!defined('LONDRES_PARTNERS_POST_TYPE')){
	define("LONDRES_PARTNERS_POST_TYPE", 'partners');
}
if (!defined('LONDRES_TEAM_POST_TYPE')){
	define("LONDRES_TEAM_POST_TYPE", 'team');	
}
if (!defined('LONDRES_PLG_URL')){
	define('LONDRES_PLG_URL', plugin_dir_url(__FILE__) );
}
if (!defined('LONDRES_PLG_PATH')){
	define('LONDRES_PLG_PATH', plugin_dir_path(__FILE__) );
}

if (!defined('LONDRES_PLG_ACTIVE')){
	define('LONDRES_PLG_ACTIVE', true );
}

/* widgets */
add_action( 'londres_plugin_widgets_init', 'londres_plugin_widgets_init' );
function londres_plugin_widgets_init(){
	global $vc_addons_url;
	$active_widgets = ["contactForm/londres_widget_contactForm.php","contactInfo/londres_widget_contactInfo.php","flickr/londres_widget_flickr.php","instagram/londres_widget_instagram.php","newsletter/londres_widget_newsletter.php","recentComments/londres_widget_recentComments.php","twitter/londres_widget_twitter.php","vcelement/londres_widget_vc_element.php","video/londres_widget_video.php"];
	if (isset($vc_addons_url) && $vc_addons_url != ""){
		$active_widgets_with_addons = ["partners/londres_widget_partners.php","recentPostsSidebar/londres_widget_recentPostsSidebar.php","team/londres_widget_team.php","testimonials/londres_widget_testimonials.php"];
		$active_widgets = array_merge($active_widgets, $active_widgets_with_addons); 
	}
	if (!class_exists('CubePortfolioMain')) unset($active_widgets[ array_search("recentProjects/londres_widget_recentProjects.php", $active_widgets) ]);
	foreach ($active_widgets as $widget) require_once( "lib/widgets/" . $widget );
}

/* gutenberg */ 
add_action( 'init', 'londres_check_for_gutenberg', 1 );
if (!function_exists('londres_check_for_gutenberg')){
	function londres_check_for_gutenberg(){
		global $wp_version;
		
		$_wpb_vc_js_status = isset($_GET['post']) ? get_post_meta( $_GET['post'], '_wpb_vc_js_status', true ) : false;
		if (!$_wpb_vc_js_status){
			$_wpb_vc_js_status = (isset($_GET['post_id']) && isset($_GET['vc_action']) && $_GET['vc_action'] == "vc_inline") ? true : false;
		}
		if ( ( defined('GUTENBERG_VERSION') || intval($wp_version) > 4 ) && !isset($_GET['classic-editor']) && !$_wpb_vc_js_status ){
			add_action('admin_enqueue_scripts', 'upper_admin_block_style');
			require_once( "lib/gutenberg/upper-block.php" );
		}
	}
}

if (!function_exists('upper_admin_block_style')){
	function upper_admin_block_style(){
		wp_enqueue_style( 'upper-block', LONDRES_PLG_URL . "lib/gutenberg/upper-block.css", 1 );
	}
}

/*******+++++**/
/*	projects
/********+++++*/
/**
 * ADD THE ACTIONS
 */
add_action('init', 'londres_register_portfolio_category');  //functions/portfolio.php
add_action('init', 'londres_register_portfolio_post_type');  //functions/portfolio.php
add_action('manage_posts_custom_column',  'portfolio_show_columns'); //functions/portfolio.php
add_filter('manage_edit-portfolio_columns', 'portfolio_columns');

/**
 * Registers the portfolio category taxonomy.
 */
if (!function_exists('londres_register_portfolio_category')){
    function londres_register_portfolio_category(){

        register_taxonomy("portfolio_category",
            array(LONDRES_PORTFOLIO_POST_TYPE),
            array(	"hierarchical" => true,
                "label" => "Categories",
                "singular_label" => "Categories",
                "rewrite" => true,
                "query_var" => true
            ));

        register_taxonomy("portfolio_type",
            array(LONDRES_PORTFOLIO_POST_TYPE),
            array(	"hierarchical" => true,
                "label" => "Portfolios",
                "singular_label" => "Portfolios",
                "rewrite" => true,
                "query_var" => true
            ));
    }
}


/**
 * Registers the portfolio custom type.
 */
if (!function_exists('londres_register_portfolio_post_type')){
    function londres_register_portfolio_post_type() {
        $portfolio_permalink = get_option(LONDRES_SHORTNAME."_portfolio_permalink");
        //the labels that will be used for the portfolio items
        $labels = array(
            'name' => _x('Projects', 'portfolio name','londres'),
            'singular_name' => _x('Project Item', 'portfolio type singular name','londres'),
            'add_new' => __('Add New','londres'),
            'add_new_item' => __('Add New Item','londres'),
            'edit_item' => __('Edit Item','londres'),
            'new_item' => __('New Project Item','londres'),
            'view_item' => __('View Item','londres'),
            'search_items' => __('Search Project Items','londres'),
            'not_found' =>  __('No project items found','londres'),
            'not_found_in_trash' => __('No project items found in Trash','londres'),
            'parent_item_colon' => ''
        );

        //register the custom post type
        register_post_type( LONDRES_PORTFOLIO_POST_TYPE,
            array( 'labels' => $labels,
                'public' => true,
                'show_ui' => true,
                'capability_type' => 'post',
                'menu_icon' => get_template_directory_uri() . '/images/londres_icons/projectsicon.png',
                'hierarchical' => false,
                'rewrite' => array( 'with_front' => 'false', 'slug' => $portfolio_permalink ),
                'taxonomies' => array('portfolio_category'),
                'supports' => array('title', 'editor', 'thumbnail', 'comments', 'page-attributes', 'excerpt') ) );


    }
}



/* ------------------------------------------------------------------------*
 * SET THE DEFAULT IMAGE SIZES FOR THE PORTFOLIO ITEMS REGARDING THE
 * NUMBER OF COLUMNS
 * ------------------------------------------------------------------------*/

if (!function_exists('portfolio_columns')){
    function portfolio_columns($columns) {
        $columns['category'] = 'Category';
        $columns['type'] = 'Portfolio';
        return $columns;
    }
}

/**
 * Add category column to the portfolio items page
 * @param $name
 */
if (!function_exists('portfolio_show_columns')){
    function portfolio_show_columns($name) {
        global $post;
        switch ($name) {
            case 'category':
                $cats = get_the_term_list( $post->ID, 'portfolio_category', '', ', ', '' );
                echo $cats;
                break;
            case 'type':
                $cats = get_the_term_list( $post->ID, 'portfolio_type', '', ', ', '' );
                echo $cats;
                break;
        }
    }
}


/* new order by features helper */
if (!function_exists('upper_orderby_tax_clauses')){
	function upper_orderby_tax_clauses( $clauses, $wp_query ) {
		$orderby_arg = $wp_query->get('orderby');
		if ( ! empty( $orderby_arg ) && substr_count( $orderby_arg, 'taxonomy.' ) ) {
			global $wpdb;
			$bytax = "GROUP_CONCAT({$wpdb->terms}.name ORDER BY name ASC)";
			$array = explode( ' ', $orderby_arg ); 
			if ( ! isset( $array[1] ) ) {
				$array = array( $bytax, "{$wpdb->posts}.post_date" );
				$taxonomy = str_replace( 'taxonomy.', '', $orderby_arg );
			} else {
				foreach ( $array as $i => $t ) {
					if ( substr_count( $t, 'taxonomy.' ) )  {
						$taxonomy = str_replace( 'taxonomy.', '', $t );
						$array[$i] = $bytax;
					} elseif ( $t === 'meta_value' || $t === 'meta_value_num' ) {
						$cast = ( $t === 'meta_value_num' ) ? 'SIGNED' : 'CHAR';
						$array[$i] = "CAST( {$wpdb->postmeta}.meta_value AS {$cast} )";
					} else {
					$array[$i] = "{$wpdb->posts}.{$t}";
					}
				}
			}
			$order = strtoupper( $wp_query->get('order') ) === 'ASC' ? ' ASC' : ' DESC';
			$ot = strtoupper( $wp_query->get('ordertax') );
			$ordertax = $ot === 'DESC' || $ot === 'ASC' ? " $ot" : " $order";
			$clauses['orderby'] = implode(', ',
			array_map( function($a) use ( $ordertax, $order ) {
				return ( strpos($a, 'GROUP_CONCAT') === 0 ) ? $a . $ordertax : $a . $order;
			}, $array ));
			$clauses['join'] .= " LEFT OUTER JOIN {$wpdb->term_relationships} ";
			$clauses['join'] .= "ON {$wpdb->posts}.ID = {$wpdb->term_relationships}.object_id";
			$clauses['join'] .= " LEFT OUTER JOIN {$wpdb->term_taxonomy} ";
			$clauses['join'] .= "USING (term_taxonomy_id)";
			$clauses['join'] .= " LEFT OUTER JOIN {$wpdb->terms} USING (term_id)";
			$clauses['groupby'] = "object_id";
			$clauses['where'] .= " AND (taxonomy = '{$taxonomy}' OR taxonomy IS NULL)";
		}
		return $clauses;
	}	
}

/**
 * Gets a list of custom taxomomies by type
 * @param $type the type of the taxonomy
 */
if (!function_exists('londres_get_taxonomies')){
    function londres_get_taxonomies($type){
        $args = array(
            'type' => 'post',
            'orderby' => 'id',
            'order' => 'ASC',
            'taxonomy' => $type,
            'hide_empty' => 1,
            'pad_counts' => false );

        $categories = get_categories( $args );

        return $categories;
    }
}


/**
 * Gets a list of custom taxomomies by slug
 * @param $term_id the slug
 */
if (!function_exists('londres_get_taxonomy_slug')){
    function londres_get_taxonomy_slug($term_id){
        global $wpdb;

        $res = $wpdb->get_results($wpdb->prepare("SELECT slug FROM $wpdb->terms WHERE term_id=%s LIMIT 1;", $term_id));
        $res=$res[0];
        return $res->slug;
    }
}

/**
 * Gets a list of custom taxomomy's children
 * @param $type the type of the taxonomy
 * @param $parent_id the slug of the parent taxonomy
 */
if (!function_exists('londres_get_taxonomy_children')){
    function londres_get_taxonomy_children($type, $parent_id){
        global $wpdb;

        if($parent_id!='-1'){
            $res = $wpdb->get_results($wpdb->prepare("SELECT t.term_id, t.name, t.slug FROM $wpdb->terms as t LEFT JOIN $wpdb->term_taxonomy tt ON t.term_id=tt.term_id WHERE tt.taxonomy=%s AND tt.parent=%s;", $type, $parent_id));
        }else{
            $res = $wpdb->get_results($wpdb->prepare("SELECT t.term_id, t.name, t.slug FROM $wpdb->terms as t LEFT JOIN $wpdb->term_taxonomy tt ON t.term_id=tt.term_id WHERE tt.taxonomy=%s;", $type));
        }
        return $res;
    }
}

if (!function_exists('londres_get_projects')){
    function londres_get_projects(){
        $proj = array();
        $args= array(
            'posts_per_page' =>-1,
            'post_type' => LONDRES_PORTFOLIO_POST_TYPE
        );
        query_posts($args);

        if(have_posts()) {
            while (have_posts()) {
                the_post();
                $proj[] = array("p_title"=>get_the_title(), "p_id"=>get_the_ID());
                //$ret .= get_the_title() . "|*|";
            }
        }

        return $proj;
    }
}

/*******+++++**/
/*	partners
/********+++++*/
/**
 * ADD THE ACTIONS
 */
add_action('init', 'londres_register_partners_post_type');  //functions/partners.php


/**
 * Registers the portfolio custom type.
 */
if (!function_exists('londres_register_partners_post_type')){
    function londres_register_partners_post_type() {

        register_taxonomy("partners_category",
            array(LONDRES_PARTNERS_POST_TYPE),
            array(	"hierarchical" => true,
                "label" => "Categories",
                "singular_label" => "Categories",
                "rewrite" => true,
                "query_var" => true,
                "show_admin_column" => true
            ));

        //the labels that will be used for the portfolio items
        $labels = array(
            'name' => _x('Partners', 'partners name','londres'),
            'singular_name' => _x('Partners Item', 'partners type singular name','londres'),
            'add_new' => __('Add New','londres'),
            'add_new_item' => __('Add New Item','londres'),
            'edit_item' => __('Edit Item','londres'),
            'new_item' => __('New Partners Item','londres'),
            'view_item' => __('View Item','londres'),
            'search_items' => __('Search Partners Items','londres'),
            'not_found' =>  __('No Partners items found','londres'),
            'not_found_in_trash' => __('No partners items found in Trash','londres'),
            'parent_item_colon' => ''
        );

        //register the custom post type
        register_post_type( LONDRES_PARTNERS_POST_TYPE,
            array( 'labels' => $labels,
                'public' => true,
                'show_ui' => true,
                'exclude_from_search' => true,
                'show_in_nav_menus' => false,
                'menu_icon' => get_template_directory_uri() . '/images/londres_icons/partnersicon.png',
                'capability_type' => 'post',
                'hierarchical' => false,
                'rewrite' => array('slug'=>'partners'),
                'taxonomies' => array('partners_category'),
                'supports' => array('title', 'thumbnail') ) );


    }
}


/*******+++++**/
/*	team
/********+++++*/
/**
 * ADD THE ACTIONS
 */
add_action('init', 'londres_register_team_post_type');  //functions/team.php


/**
 * Registers the portfolio custom type.
 */
if (!function_exists('londres_register_team_post_type')){
    function londres_register_team_post_type() {

        register_taxonomy("team_category",
            array(LONDRES_TEAM_POST_TYPE),
            array(	"hierarchical" => true,
                "label" => "Categories",
                "singular_label" => "Categories",
                "rewrite" => true,
                "query_var" => true,
                "show_admin_column" => true
            ));

        //the labels that will be used for the portfolio items
        $labels = array(
            'name' => _x('Team', 'team name','londres'),
            'singular_name' => _x('Team Item', 'team type singular name','londres'),
            'add_new' => __('Add New','londres'),
            'add_new_item' => __('Add New Item','londres'),
            'edit_item' => __('Edit Item','londres'),
            'new_item' => __('New Team Item','londres'),
            'view_item' => __('View Item','londres'),
            'search_items' => __('Search Team Items','londres'),
            'not_found' =>  __('No Team items found','londres'),
            'not_found_in_trash' => __('No team items found in Trash','londres'),
            'parent_item_colon' => ''
        );

        //register the custom post type
        register_post_type( LONDRES_TEAM_POST_TYPE,
            array( 'labels' => $labels,
                'public' => true,
                'show_ui' => true,
                'exclude_from_search' => true,
                'show_in_nav_menus' => false,
                'menu_icon' => get_template_directory_uri() . '/images/londres_icons/icon71.png',
                'capability_type' => 'post',
                'hierarchical' => false,
                'rewrite' => array('slug'=>'team'),
                'taxonomies' => array('team_category'),
                'supports' => array('title', 'editor', 'thumbnail') ) );


    }
}



/*******+++++**/
/*	testimonials
/********+++++*/
/**
 * ADD THE ACTIONS
 */
add_action('init', 'londres_register_testimonials_post_type');  //functions/testimonials.php


/**
 * Registers the portfolio custom type.
 */
if (!function_exists('londres_register_testimonials_post_type')){
    function londres_register_testimonials_post_type() {

        register_taxonomy("testimonials_category",
            array(LONDRES_TESTIMONIALS_POST_TYPE),
            array(	"hierarchical" => true,
                "label" => "Categories",
                "singular_label" => "Categories",
                "rewrite" => true,
                "query_var" => true,
                "show_admin_column" => true,
            ));

        //the labels that will be used for the portfolio items
        $labels = array(
            'name' => _x('Testimonials', 'testimonials name','londres'),
            'singular_name' => _x('Testimonials Item', 'testimonials type singular name','londres'),
            'add_new' => __('Add New','londres'),
            'add_new_item' => __('Add New Item','londres'),
            'edit_item' => __('Edit Item','londres'),
            'new_item' => __('New Testimonials Item','londres'),
            'view_item' => __('View Item','londres'),
            'search_items' => __('Search Testimonials Items','londres'),
            'not_found' =>  __('No testimonials items found','londres'),
            'not_found_in_trash' => __('No testimonials items found in Trash','londres'),
            'parent_item_colon' => ''
        );

        //register the custom post type
        register_post_type( LONDRES_TESTIMONIALS_POST_TYPE,
            array( 'labels' => $labels,
                'public' => true,
                'show_ui' => true,
                'exclude_from_search' => true,
                'show_in_nav_menus' => false,
                'menu_icon' => get_template_directory_uri() . '/images/londres_icons/testicon.png',
                'capability_type' => 'post',
                'hierarchical' => false,
                'rewrite' => array('slug'=>'testimonials'),
                'taxonomies' => array('testimonials_category'),
                'supports' => array('title', 'editor', 'thumbnail', 'comments', 'page-attributes') ) );


    }
}

if (!function_exists('londres_init_cpt_plugin')){
    function londres_init_cpt_plugin(){
        londres_register_partners_post_type();
        londres_register_team_post_type();
        londres_register_testimonials_post_type();
    }
}


/*  EXTEND VC SHORTCODES  */
if (!function_exists('londres_team_categories_settings_field')){
	function londres_team_categories_settings_field($settings, $value){
		//$dependency = vc_generate_dependencies_attributes($settings);
		$taxonomy = 'team_category';
		$tax_terms = get_terms($taxonomy);
		$output = "";
		if (!count($tax_terms)){
			$output .= "No categories defined.";
			$output .= '<input name="'.esc_attr($settings['param_name']).'" class="hidden wpb_vc_param_value wpb-textinput '.esc_attr($settings['param_name']).' '.esc_attr($settings['type']).'_field" type="text" value="0" />';
		} else {
			if (count($tax_terms) > 1) $output .= "<label class='team_categories'><input class='selectall' type='checkbox' name='categories[]' value='0' onchange=\"if(jQuery(this).is(':checked')){ jQuery(this).parent().siblings().children('input').attr('checked',true);jQuery(this).parent().siblings('input.".esc_js($settings['param_name'])."').val('-1');} else { jQuery(this).parent().siblings().children('input').attr('checked',false);jQuery(this).parent().siblings('input.".esc_js($settings['param_name'])."').val('0');}\" />".esc_html__('All','londres')."</label>";
			$value = explode(",",$value);
			foreach ($tax_terms as $tax_term) {
				$output .= "<label class='team_categories'><input ";
				if (in_array($tax_term->slug, $value)) $output .= " checked='checked' ";
				$output .= "class='categories_checks' type='checkbox' name='categories[]' value='".esc_attr($tax_term->slug)."' onchange=\"var output = '';jQuery('.edit_form_line input:checked').not('.selectall').each(function(e){ if(e!=0){output += ',';} output += jQuery(this).val(); }); jQuery(this).parent().siblings('.team_cats_field').val(output); if (jQuery('.edit_form_line input').not('.selectall').not(':checked').length) jQuery('.edit_form_line input.selectall').attr('checked',false); if (jQuery('.edit_form_line input.categories_checks:checked').not('.selectall').length == jQuery('.edit_form_line input.categories_checks').not('.selectall').length) jQuery('.edit_form_line input.selectall').attr('checked',true); \" />".esc_html($tax_term->name)."</label>";
			}
			$output .= '<input name="'.esc_attr($settings['param_name']).'" class="hidden wpb_vc_param_value wpb-textinput '.esc_attr($settings['param_name']).' '.esc_attr($settings['type']).'_field" type="text" value="'.esc_attr(implode(",",$value)).'" />';
		}
		return $output;
	}
}

if (!function_exists('londres_partners_categories_settings_field')){
	function londres_partners_categories_settings_field($settings, $value){
		//$dependency = vc_generate_dependencies_attributes($settings);
		$taxonomy = 'partners_category';
		$tax_terms = get_terms($taxonomy);
		$output = "";
		if (!count($tax_terms)){
			$output .= "No categories defined.";
			$output .= '<input name="'.esc_attr($settings['param_name']).'" class="hidden wpb_vc_param_value wpb-textinput '.esc_attr($settings['param_name']).' '.esc_attr($settings['type']).'_field" type="text" value="0" />';
		} else {
			if (count($tax_terms) > 1) $output .= "<label class='partners_categories'><input class='selectall' type='checkbox' name='categories[]' value='0' onchange=\"if(jQuery(this).is(':checked')){ jQuery(this).parent().siblings().children('input').attr('checked',true);jQuery(this).parent().siblings('input.".esc_js($settings['param_name'])."').val('-1');} else { jQuery(this).parent().siblings().children('input').attr('checked',false);jQuery(this).parent().siblings('input.".esc_js($settings['param_name'])."').val('0');}\" />".esc_html__('All','londres')."</label>";
			$value = explode(",",$value);
			foreach ($tax_terms as $tax_term) {
				$output .= "<label class='partners_categories'><input "; 
				if (in_array($tax_term->slug, $value)) $output .= " checked='checked' ";
				$output .= "class='categories_checks' type='checkbox' name='categories[]' value='".esc_attr($tax_term->slug)."' onchange=\"var output = '';jQuery('.edit_form_line input:checked').not('.selectall').each(function(e){ if(e!=0){output += ',';} output += jQuery(this).val(); }); jQuery(this).parent().siblings('.partners_cats_field').val(output); if (jQuery('.edit_form_line input').not('.selectall').not(':checked').length) jQuery('.edit_form_line input.selectall').attr('checked',false); if (jQuery('.edit_form_line input.categories_checks:checked').not('.selectall').length == jQuery('.edit_form_line input.categories_checks').not('.selectall').length) jQuery('.edit_form_line input.selectall').attr('checked',true); \" />".esc_html($tax_term->name)."</label>";
			}
			$output .= '<input name="'.esc_attr($settings['param_name']).'" class="hidden wpb_vc_param_value wpb-textinput '.esc_attr($settings['param_name']).' '.esc_attr($settings['type']).'_field" type="text" value="'.esc_attr(implode(",",$value)).'" />';
		}
		return $output;
	}
}

if (!function_exists('londres_testimonials_categories_settings_field')){
	function londres_testimonials_categories_settings_field($settings, $value){
		//$dependency = vc_generate_dependencies_attributes($settings);
		$taxonomy = 'testimonials_category';
		$tax_terms = get_terms($taxonomy);
		$output = "";
		if (!count($tax_terms)){
			$output .= "No categories defined.";
			$output .= '<input name="'.esc_attr($settings['param_name']).'" class="hidden wpb_vc_param_value wpb-textinput '.esc_attr($settings['param_name']).' '.esc_attr($settings['type']).'_field" type="text" value="0" />';
		} else {
			if (count($tax_terms) > 1) $output .= "<label class='testimonial_categories'><input class='selectall' type='checkbox' name='categories[]' value='0' onchange=\"if(jQuery(this).is(':checked')){ jQuery(this).parent().siblings().children('input').attr('checked',true);jQuery(this).parent().siblings('input.".esc_js($settings['param_name'])."').val('-1');} else { jQuery(this).parent().siblings().children('input').attr('checked',false);jQuery(this).parent().siblings('input.".esc_js($settings['param_name'])."').val('0');}\" />".esc_html__('All','londres')."</label>";
			$value = explode(",",$value);
			foreach ($tax_terms as $tax_term) {
				$output .= "<label class='testimonial_categories'><input ";
				if (in_array($tax_term->slug, $value)) $output .= " checked='checked' ";
				$output .= "class='categories_checks' type='checkbox' name='categories[]' value='".esc_attr($tax_term->slug)."' onchange=\"var output = '';jQuery('.edit_form_line input:checked').not('.selectall').each(function(e){ if(e!=0){output += ',';} output += jQuery(this).val(); }); jQuery(this).parent().siblings('.testimonials_cats_field').val(output); if (jQuery('.edit_form_line input').not('.selectall').not(':checked').length) jQuery('.edit_form_line input.selectall').attr('checked',false); if (jQuery('.edit_form_line input.categories_checks:checked').not('.selectall').length == jQuery('.edit_form_line input.categories_checks').not('.selectall').length) jQuery('.edit_form_line input.selectall').attr('checked',true); \" />".esc_html($tax_term->name)."</label>";
			}
			$output .= '<input name="'.esc_attr($settings['param_name']).'" class="hidden wpb_vc_param_value wpb-textinput '.esc_attr($settings['param_name']).' '.esc_attr($settings['type']).'_field" type="text" value="'.esc_attr(implode(",",$value)).'" />';
		}
		return $output;
	}
}


if (!function_exists('londres_fa_settings_field')){
	function londres_fa_settings_field($settings, $value) {
	   //$dependency = vc_generate_dependencies_attributes($settings);
		$icons = array('fa-adjust','fa-adn','fa-align-center','fa-align-justify','fa-align-left','fa-align-right','fa-ambulance','fa-anchor','fa-android','fa-angle-double-down','fa-angle-double-left','fa-angle-double-right','fa-angle-double-up','fa-angle-down','fa-angle-left','fa-angle-right','fa-angle-up','fa-apple','fa-archive','fa-arrow-circle-down','fa-arrow-circle-left','fa-arrow-circle-o-down','fa-arrow-circle-o-left','fa-arrow-circle-o-right','fa-arrow-circle-o-up','fa-arrow-circle-right','fa-arrow-circle-up','fa-arrow-down','fa-arrow-left','fa-arrow-right','fa-arrow-up','fa-arrows','fa-arrows-alt','fa-arrows-h','fa-arrows-v','fa-asterisk','fa-automobile','fa-backward','fa-ban','fa-bank','fa-bar-chart-o','fa-barcode','fa-bars','fa-beer','fa-behance','fa-behance-square','fa-bell','fa-bell-o','fa-bitbucket','fa-bitbucket-square','fa-bitcoin','fa-bold','fa-bolt','fa-bomb','fa-book','fa-bookmark','fa-bookmark-o','fa-briefcase','fa-btc','fa-bug','fa-building','fa-building-o','fa-bullhorn','fa-bullseye','fa-cab','fa-calendar','fa-calendar-o','fa-camera','fa-camera-retro','fa-car','fa-caret-down','fa-caret-left','fa-caret-right','fa-caret-square-o-down','fa-caret-square-o-left','fa-caret-square-o-right','fa-caret-square-o-up','fa-caret-up','fa-certificate','fa-chain','fa-chain-broken','fa-check','fa-check-circle','fa-check-circle-o','fa-check-square','fa-check-square-o','fa-chevron-circle-down','fa-chevron-circle-left','fa-chevron-circle-right','fa-chevron-circle-up','fa-chevron-down','fa-chevron-left','fa-chevron-right','fa-chevron-up','fa-child','fa-circle','fa-circle-o','fa-circle-o-notch','fa-circle-thin','fa-clipboard','fa-clock-o','fa-cloud','fa-cloud-download','fa-cloud-upload','fa-cny','fa-code','fa-code-fork','fa-codepen','fa-coffee','fa-cog','fa-cogs','fa-columns','fa-comment','fa-comment-o','fa-comments','fa-comments-o','fa-compass','fa-compress','fa-copy','fa-credit-card','fa-crop','fa-crosshairs','fa-css3','fa-cube','fa-cubes','fa-cut','fa-cutlery','fa-dashboard','fa-database','fa-dedent','fa-delicious','fa-desktop','fa-deviantart','fa-digg','fa-dollar','fa-dot-circle-o','fa-download','fa-dribbble','fa-dropbox','fa-drupal','fa-edit','fa-eject','fa-ellipsis-h','fa-ellipsis-v','fa-empire','fa-envelope','fa-envelope-o','fa-envelope-square','fa-eraser','fa-eur','fa-euro','fa-exchange','fa-exclamation','fa-exclamation-circle','fa-exclamation-triangle','fa-expand','fa-external-link','fa-external-link-square','fa-eye','fa-eye-slash','fa-facebook','fa-facebook-square','fa-fast-backward','fa-fast-forward','fa-fax','fa-female','fa-fighter-jet','fa-file','fa-file-archive-o','fa-file-audio-o','fa-file-code-o','fa-file-excel-o','fa-file-image-o','fa-file-movie-o','fa-file-o','fa-file-pdf-o','fa-file-photo-o','fa-file-picture-o','fa-file-powerpoint-o','fa-file-sound-o','fa-file-text','fa-file-text-o','fa-file-video-o','fa-file-word-o','fa-file-zip-o','fa-files-o','fa-film','fa-filter','fa-fire','fa-fire-extinguisher','fa-flag','fa-flag-checkered','fa-flag-o','fa-flash','fa-flask','fa-flickr','fa-floppy-o','fa-folder','fa-folder-o','fa-folder-open','fa-folder-open-o','fa-font','fa-forward','fa-foursquare','fa-frown-o','fa-gamepad','fa-gavel','fa-gbp','fa-ge','fa-gear','fa-gears','fa-gift','fa-git','fa-git-square','fa-github','fa-github-alt','fa-github-square','fa-gittip','fa-glass','fa-globe','fa-google','fa-google-plus','fa-google-plus-square','fa-graduation-cap','fa-group','fa-h-square','fa-hacker-news','fa-hand-o-down','fa-hand-o-left','fa-hand-o-right','fa-hand-o-up','fa-hdd-o','fa-header','fa-headphones','fa-heart','fa-heart-o','fa-history','fa-home','fa-hospital-o','fa-html5','fa-image','fa-inbox','fa-indent','fa-info','fa-info-circle','fa-inr','fa-instagram','fa-institution','fa-italic','fa-joomla','fa-jpy','fa-jsfiddle','fa-key','fa-keyboard-o','fa-krw','fa-language','fa-laptop','fa-leaf','fa-legal','fa-lemon-o','fa-level-down','fa-level-up','fa-life-bouy','fa-life-ring','fa-life-saver','fa-lightbulb-o','fa-link','fa-linkedin','fa-linkedin-square','fa-linux','fa-list','fa-list-alt','fa-list-ol','fa-list-ul','fa-location-arrow','fa-lock','fa-long-arrow-down','fa-long-arrow-left','fa-long-arrow-right','fa-long-arrow-up','fa-magic','fa-magnet','fa-mail-forward','fa-mail-reply','fa-mail-reply-all','fa-male','fa-map-marker','fa-maxcdn','fa-medkit','fa-meh-o','fa-microphone','fa-microphone-slash','fa-minus','fa-minus-circle','fa-minus-square','fa-minus-square-o','fa-mobile','fa-mobile-phone','fa-money','fa-moon-o','fa-mortar-board','fa-music','fa-navicon','fa-openid','fa-outdent','fa-pagelines','fa-paper-plane','fa-paper-plane-o','fa-paperclip','fa-paragraph','fa-paste','fa-pause','fa-paw','fa-pencil','fa-pencil-square','fa-pencil-square-o','fa-phone','fa-phone-square','fa-photo','fa-picture-o','fa-pied-piper','fa-pied-piper-alt','fa-pinterest','fa-pinterest-square','fa-plane','fa-play','fa-play-circle','fa-play-circle-o','fa-plus','fa-plus-circle','fa-plus-square','fa-plus-square-o','fa-power-off','fa-print','fa-puzzle-piece','fa-qq','fa-qrcode','fa-question','fa-question-circle','fa-quote-left','fa-quote-right','fa-ra','fa-random','fa-rebel','fa-recycle','fa-reddit','fa-reddit-square','fa-refresh','fa-renren','fa-reorder','fa-repeat','fa-reply','fa-reply-all','fa-retweet','fa-rmb','fa-road','fa-rocket','fa-rotate-left','fa-rotate-right','fa-rouble','fa-rss','fa-rss-square','fa-rub','fa-ruble','fa-rupee','fa-save','fa-scissors','fa-search','fa-search-minus','fa-search-plus','fa-send','fa-send-o','fa-share','fa-share-alt','fa-share-alt-square','fa-share-square','fa-share-square-o','fa-shield','fa-shopping-cart','fa-sign-in','fa-sign-out','fa-signal','fa-sitemap','fa-skype','fa-slack','fa-sliders','fa-smile-o','fa-sort','fa-sort-alpha-asc','fa-sort-alpha-desc','fa-sort-amount-asc','fa-sort-amount-desc','fa-sort-asc','fa-sort-desc','fa-sort-down','fa-sort-numeric-asc','fa-sort-numeric-desc','fa-sort-up','fa-soundcloud','fa-space-shuttle','fa-spinner','fa-spoon','fa-spotify','fa-square','fa-square-o','fa-stack-exchange','fa-stack-overflow','fa-star','fa-star-half','fa-star-half-empty','fa-star-half-full','fa-star-half-o','fa-star-o','fa-steam','fa-steam-square','fa-step-backward','fa-step-forward','fa-stethoscope','fa-stop','fa-strikethrough','fa-stumbleupon','fa-stumbleupon-circle','fa-subscript','fa-suitcase','fa-sun-o','fa-superscript','fa-support','fa-table','fa-tablet','fa-tachometer','fa-tag','fa-tags','fa-tasks','fa-taxi','fa-tencent-weibo','fa-terminal','fa-text-height','fa-text-width','fa-th','fa-th-large','fa-th-list','fa-thumb-tack','fa-thumbs-down','fa-thumbs-o-down','fa-thumbs-o-up','fa-thumbs-up','fa-ticket','fa-times','fa-times-circle','fa-times-circle-o','fa-tint','fa-toggle-down','fa-toggle-left','fa-toggle-right','fa-toggle-up','fa-trash-o','fa-tree','fa-trello','fa-trophy','fa-truck','fa-try','fa-tumblr','fa-tumblr-square','fa-turkish-lira','fa-twitter','fa-twitter-square','fa-umbrella','fa-underline','fa-undo','fa-university','fa-unlink','fa-unlock','fa-unlock-alt','fa-unsorted','fa-upload','fa-usd','fa-user','fa-user-md','fa-users','fa-video-camera','fa-vimeo-square','fa-vine','fa-vk','fa-volume-down','fa-volume-off','fa-volume-up','fa-warning','fa-wechat','fa-weibo','fa-weixin','fa-wheelchair','fa-windows','fa-won','fa-wordpress','fa-wrench','fa-xing','fa-xing-square','fa-yahoo','fa-yen','fa-youtube','fa-youtube-play','fa-youtube-square');
		$output = '<div class="londres_fa_block">'
		             .'<input name="'.esc_attr($settings['param_name'])
		             .'" class="wpb_vc_param_value wpb-textinput '
		             .esc_attr($settings['param_name']).' '.esc_attr($settings['type']).'_field" type="text" value="'
		             .esc_attr($value).'" />'
		         .'</div><div class="icons-container">';
		foreach($icons as $i){
			$output .= '<i class="fa '.esc_attr($i);
			if ($i == $value) $output .= ' selected';
			$output .= '" onclick="jQuery(this).closest(\'.edit_form_line\').find(\'input.londres_fa_field\').val(\''.esc_js($i).'\');jQuery(this).addClass(\'selected\').siblings().removeClass(\'selected\');"/>';
		}
		$output .= '</div>';
	   return $output;
	}
}

if (!class_exists('VCExtendAddonClass')){
	class VCExtendAddonClass {
	
	    function __construct() {
	        // We safely integrate with VC with this hook
	        add_action( 'vc_before_init', array( $this, 'londres_integrateWithVC' ) );
 
	        // Use this when creating a shortcode addon
	        // the code has been moved for gutenberg compatibility. its down there
	        
	        
	        add_action( 'admin_enqueue_scripts', array( $this, 'upper_cpt_admin_scripts' ) );
	        
	        //ererere
	        global $londres_team_index, $londres_team_index_aux, $londres_partners_index, $londres_twitter_index, $londres_testimonials_index, $londres_single_testimonial_index, $londres_vertical_tabs, $londres_vt_index;
	        	$londres_team_index = $londres_team_index_aux = $londres_partners_index = $londres_twitter_index = $londres_testimonials_index = $londres_single_testimonial_index = $londres_vertical_tabs = $londres_vt_index = 1;
	    }
		
		function upper_cpt_admin_scripts(){
			wp_enqueue_style("upper-cpt-admin",plugins_url("lib/css/upper-cpt-admin.css",__FILE__));
		}
		
	    public function londres_integrateWithVC() {
		    
		    // this code here for gutenberg compat
		    if (function_exists('add_shortcode')){
				add_shortcode( 'verticaltabs', array( $this, 'londres_renderVerticalTabs' ) );
				add_shortcode( 'verticaltab', array( $this, 'londres_renderVerticalTab' ) );
				add_shortcode( 'testimonials', array( $this, 'londres_renderTestimonials' ) );
				add_shortcode( 'twitter_scroller', array( $this, 'londres_renderTwitterScroller' ) );
				add_shortcode( 'partners', array( $this, 'londres_renderPartners' ) );
				add_shortcode( 'team', array( $this, 'londres_renderTeam' ) );
				add_shortcode( 'newsletter', array($this, 'londres_renderNewsletter') );
	        }
		    
	        $vs_posttypes = get_option('wpb_js_content_types');
	        if (!isset($vs_posttypes)) update_option('wpb_js_content_types', array('post','page','portfolio','team'), true );
	        else {
		        if (!isset($vs_posttypes) || !$vs_posttypes) {
			        $vs_posttypes = array('post','page','portfolio','team');
		        }
			    if (is_array($vs_posttypes) && !in_array('team',$vs_posttypes)){ 
					array_push($vs_posttypes, 'team');
				}
				if (is_array($vs_posttypes) && !in_array('page',$vs_posttypes)){ 
					array_push($vs_posttypes, 'page');
				}
				if (is_array($vs_posttypes) && !in_array('portfolio',$vs_posttypes)){ 
					array_push($vs_posttypes, 'portfolio');
				} 
				update_option('wpb_js_content_types', $vs_posttypes, true);
	        }
        
	        vc_map( array(
	            "name" => esc_html__("[UPPER] Newsletter", 'londres'),
				"category" => 'UPPER Shortcodes',
	            "description" => esc_html__("Newsletter", 'londres'),
	            "base" => "newsletter",
	            "icon" => "vc_extend_newsletter_icon", // or css class name which you can reffer in your css file later. Example: "vc_extend_my_class"
	            "category" => esc_html__('UPPER', 'londres'),
				'show_settings_on_create' => false,
				'category' => array('UPPER Shortcodes',esc_html__('Content','londres')),
				'params' => array()
		        )
			);
        
	        $tab_id_1 = time() . '-1-' . rand( 0, 100 );
	        vc_map( array(
	            "name" => esc_html__("[UPPER] Vertical Tabs", 'londres'),
	            "description" => esc_html__("Awesome Vertical Tabs", 'londres'),
	            "base" => "verticaltabs",
	            "icon" => "vc_extend_vertical_tabs_icon", // or css class name which you can reffer in your css file later. Example: "vc_extend_my_class"
	            "category" => 'UPPER Shortcodes',
				'show_settings_on_create' => false,
				"as_parent" => array('only' => 'verticaltab'),
	            'admin_enqueue_js' => LONDRES_PLG_URL . 'lib/vc_shortcodes/vertical_tabs.js', // This will load js file in the VC backend editor
	            'front_enqueue_js' => LONDRES_PLG_URL . 'lib/vc_shortcodes/vertical_tabs.js', // This will load js file in the VC backend editor
	            'admin_enqueue_css' => LONDRES_PLG_URL . 'lib/vc_shortcodes/vertical_tabs.css', // This will load css file in the VC 
	            'params' => array(
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Widget title', 'londres' ),
						'param_name' => 'tab_title',
						'description' => esc_html__( 'Enter text which will be used as widget title. Leave blank if no title is needed.', 'londres' )
					),
					array(
						'type' => 'dropdown',
						'heading' => esc_html__( 'Style', 'londres' ),
						'param_name' => 'style_vt',
						'value' => array( 'Icon' => 'icon', 'Text' => 'text', 'Icon + Text' => 'icontext' ),
						'std' => 'icon',
						'description' => esc_html__( 'Choose between just display an icon or icon and text.', 'londres' )
					),
					array(
						'type' => 'dropdown',
						'heading' => esc_html__( 'Orientation', 'londres' ),
						'param_name' => 'orientation',
						'value' => array( 'Vertical' => 'vertical', 'Horizontal' => 'horizontal' ),
						'std' => 'vertical',
						'description' => esc_html__( 'Choose between Vertical and Horizontal orientation (it also affects the effect).', 'londres' )
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Extra class name', 'londres' ),
						'param_name' => 'el_class',
						'description' => esc_html__( 'If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.', 'londres' )
					)
				),
				'custom_markup' => '<div class="wpb_tabs_holder wpb_holder vc_container_for_children"><ul class="tabs_controls"></ul>%content%</div>',
				'default_content' => '[verticaltab title="' . esc_html__( 'Tab 1', 'londres' ) . '" tab_id="' . $tab_id_1 . '" icon="fa-adjust"][/verticaltab]',
				'js_view' => 'VcVerticalTabsView',
				'category' => array('UPPER Shortcodes',esc_html__('Content','londres')),
		        )
			);
			
			$upper_icons_set_type = class_exists('Ultimate_VC_Addons') ? "icon_manager" : "londres_fa";
		
			vc_map( array(
				'name' => esc_html__( '[UPPER] Vertical Tab', 'londres' ),
				"category" => 'UPPER Shortcodes',
				'base' => 'verticaltab',
				"as_child" => array('only' => 'verticaltabs'),
				"as_parent" => array('only' => 'vc_row'),
				"allowed_container_element" => true,
				"is_container" => true,
				'params' => array(
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Title', 'londres' ),
						'param_name' => 'title',
						'description' => esc_html__( 'Tab title.', 'londres' )
					),
					array(
						"type" => $upper_icons_set_type,
						"class" => "",
						"heading" => __("Select Icon ","londres"),
						"param_name" => "icon",
						"value" => "",
						'description' => esc_html__( 'Choose an Icon', 'londres' )
					),
/*
					array(
						'type' => 'textarea_html',
						'heading' => 'This tab\'s content.',
						'param_name' => 'content'
					),
*/
					array(
						'type' => 'tab_id',
						'heading' => esc_html__( 'Tab ID', 'londres' ),
						'param_name' => "tab_id"
					)
				),
				'default_content' => '[vc_row_inner][vc_column_inner][/vc_column_inner][/vc_row_inner]',
				'js_view' => 'VcVerticalTabView',
			) );
		
			vc_map( array(
				'name' => esc_html__( '[UPPER] Testimonials', 'londres' ),
				"category" => 'UPPER Shortcodes',
				'base' => 'testimonials',
				'is_container' => false,
				"icon" => "vc_extend_testimonials_icon", // or css class name which you can reffer in your css file later. Example: "vc_extend_my_class"
				'admin_enqueue_js' => LONDRES_PLG_URL . 'lib/vc_shortcodes/testimonials.js',
				'params' => array(
					array(
						'type' => 'dropdown',
						'heading' => esc_html__('Style', 'londres'),
						'param_name' => 'style_testimonials',
						'description' => esc_html__('3 Different Styles to choose from.','londres'),
						'value' => array(
							esc_html__( 'Style 1', 'londres' ) => 'style1',
							esc_html__( 'Style 2 (with scroller)', 'londres' ) => 'style2',
							esc_html__( 'Style 3 (single wide)', 'londres' ) => 'style3'
						),
					),
				
					/*flexoptions*/
					array(
						'type' => 'dropdown',
						'heading' => esc_html__('Animation Type','londres'),
						'param_name' => 'des_testimonials_flex_animation',
						'description' => esc_html__('Choose between Slide and Fade effects.','londres'),
						'value' => array(
							esc_html__( 'Slide', 'londres' ) => 'slide',
							esc_html__( 'Fade', 'londres' ) => 'fade'
						),
					),
					array(
						'type' => 'dropdown',
						'heading' => esc_html__( 'Slideshow?', 'londres' ),
						'param_name' => 'des_testimonials_flex_slideshow',
						'description' => esc_html__( 'Animate slider automatically.', 'londres' ),
						'value' => array( esc_html__( 'Yes, please', 'londres' ) => 'yes', esc_html__( 'No, thanks', 'londres' ) => 'no' )
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Slideshow Speed', 'londres' ),
						'param_name' => 'des_testimonials_flex_slideshow_speed',
						'description' => esc_html__( 'Set the speed of the slideshow cycling, in milliseconds.', 'londres' ),
						'value' => '3500'
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Animation Duration', 'londres' ),
						'param_name' => 'des_testimonials_flex_animation_duration',
						'description' => esc_html__( 'Set the speed of animations, in milliseconds.', 'londres' ),
						'value' => '1000'
					),
					
					/* new londres options */
					array(
						'type' => 'dropdown',
						'heading' => esc_html__( 'Number of Items in Desktop', 'londres' ),
						'param_name' => 'des_testimonials_flex_items_desktop',
						'description' => esc_html__( 'The number of visible items per slide in a desktop.', 'londres' ),
						'value' => array('1'=>1,'2'=>2,'3'=>3),
					),
					array(
						'type' => 'dropdown',
						'heading' => esc_html__( 'Number of Items in a Small Desktop', 'londres' ),
						'param_name' => 'des_testimonials_flex_items_small_desktop',
						'description' => esc_html__( 'The number of visible items per slide in a small desktop.', 'londres' ),
						'value' => array('1'=>1,'2'=>2,'3'=>3),
					),
					array(
						'type' => 'dropdown',
						'heading' => esc_html__( 'Number of Items in a Tablet', 'londres' ),
						'param_name' => 'des_testimonials_flex_items_tablet',
						'description' => esc_html__( 'The number of visible items per slide in a tablet.', 'londres' ),
						'value' => array('1'=>1,'2'=>2,'3'=>3),
					),
					array(
						'type' => 'dropdown',
						'heading' => esc_html__( 'Number of Items in Mobile', 'londres' ),
						'param_name' => 'des_testimonials_flex_items_mobile',
						'description' => esc_html__( 'The number of visible items per slide in a mobile.', 'londres' ),
						'value' => array('1'=>1,'2'=>2,'3'=>3),
					),
					/* endof new londres options */
					
					
					array(
						'type' => 'dropdown',
						'heading' => esc_html__( 'Direction Navigation', 'londres' ),
						'param_name' => 'des_testimonials_flex_direction_nav',
						'description' => esc_html__( 'Create navigation for previous/next navigation.', 'londres' ),
						'value' => array( esc_html__( 'Yes, please', 'londres' ) => 'yes', esc_html__( 'No, thanks', 'londres' ) => 'no' )
					),
					array(
						'type' => 'dropdown',
						'heading' => esc_html__( 'Navigation Style', 'londres' ),
						'param_name' => 'des_testimonials_flex_nav_style',
						'description' => esc_html__( 'Choose between Dark and Light style.', 'londres' ),
						'value' => array( esc_html__( 'Dark', 'londres' ) => 'dark', esc_html__( 'Light', 'londres' ) => 'light' )
					),
					array(
						'type' => 'dropdown',
						'heading' => esc_html__( 'Control Navigation', 'londres' ),
						'param_name' => 'des_testimonials_flex_control_nav',
						'description' => esc_html__( 'Create navigation for paging control of each slide.', 'londres' ),
						'value' => array( esc_html__( 'Yes, please', 'londres' ) => 'yes', esc_html__( 'No, thanks', 'londres' ) => 'no' )
					),
					array(
						'type' => 'dropdown',
						'heading' => esc_html__( 'Pause on Hover', 'londres' ),
						'param_name' => 'des_testimonials_flex_pause_on_hover',
						'description' => esc_html__( 'Pause the slideshow when hovering over slider, then resume when no longer hovering.', 'londres' ),
						'value' => array( esc_html__( 'Yes, please', 'londres' ) => 'yes', esc_html__( 'No, thanks', 'londres' ) => 'no' )
					),
					
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Testimonials Scroller Height', 'londres' ),
						'param_name' => 'des_testimonials_flex_height',
						'description' => esc_html__( 'The height of the testimonials scroller in pixels.', 'londres' ),
						'std' => '650'
					),
					/*endofflexoptions*/
				
					array(
						'type' => 'testimonials_cats',
						'heading' => esc_html__( 'Categories', 'londres' ),
						'param_name' => 'testimonials_cats',
						'description' => esc_html__( 'Choose one or more Categories', 'londres' )
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Number of Testimonials', 'londres' ),
						'param_name' => 'number',
						'description' => esc_html__( 'The number of testimonials. If set to 0 it will display all.', 'londres' )
					),
					/* new order options */
					array(
						'type' => 'dropdown',
						'heading' => esc_html__( 'Order By', 'londres' ),
						'param_name' => 'londres_testimonials_orderby',
						'value' => array( esc_html__( 'Date', 'londres' ) => 'date', esc_html__( 'Author', 'londres' ) => 'author', esc_html__( 'Company', 'londres' ) => 'company' )
					),
					array(
						'type' => 'dropdown',
						'heading' => esc_html__( 'Order', 'londres' ),
						'param_name' => 'londres_testimonials_order',
						'value' => array( esc_html__( 'DESC', 'londres' ) => 'DESC', esc_html__( 'ASC', 'londres' ) => 'ASC' )
					),
					/* endof new order options */
					array(
						'type' => 'checkbox',
						'heading' => esc_html__( 'Hide author?', 'londres' ),
						'param_name' => 'hide_author',
						'description' => esc_html__( 'If selected, the author will not be displayed.', 'londres' ),
						'value' => array( esc_html__( 'Yes, please', 'londres' ) => 'yes' )
					),
					array(
						'type' => 'checkbox',
						'heading' => esc_html__( 'Hide company?', 'londres' ),
						'param_name' => 'hide_company',
						'description' => esc_html__( 'If selected, the company will not be displayed.', 'londres' ),
						'value' => array( esc_html__( 'Yes, please', 'londres' ) => 'yes' )
					)
				),
				'js_view' => 'VcTestimonialsView',
				'front_enqueue_js' => array(LONDRES_PLG_URL . "lib/vc_shortcodes/testimonials.js")
			) );
		
			vc_map( array(
				'name' => esc_html__('[UPPER] Twitter Scroller', 'londres'),
				"category" => 'UPPER Shortcodes',
				'base' => 'twitter_scroller',
				'is_container' => false,
				'icon' => 'vc_extend_twitter_scroller_icon',
				'admin_enqueue_js' => LONDRES_PLG_URL . 'lib/vc_shortcodes/twitter_scroller.js',
				'params' => array(
					/*flexoptions*/
					array(
						'type' => 'dropdown',
						'heading' => esc_html__('Animation Type','londres'),
						'param_name' => 'des_twitter_animation',
						'description' => esc_html__('Choose between Slide and Fade effects.','londres'),
						'value' => array(
							esc_html__( 'Slide', 'londres' ) => 'slide',
							esc_html__( 'Fade', 'londres' ) => 'fade'
						),
					),
					array(
						'type' => 'dropdown',
						'heading' => esc_html__( 'Slideshow?', 'londres' ),
						'param_name' => 'des_twitter_slideshow',
						'description' => esc_html__( 'Animate slider automatically.', 'londres' ),
						'value' => array( esc_html__( 'Yes, please', 'londres' ) => 'yes', esc_html__( 'No, thanks', 'londres' ) => 'no' )
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Slideshow Speed', 'londres' ),
						'param_name' => 'des_twitter_slideshow_speed',
						'description' => esc_html__( 'Set the speed of the slideshow cycling, in milliseconds.', 'londres' ),
						'value' => '3500'
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Animation Duration', 'londres' ),
						'param_name' => 'des_twitter_animation_duration',
						'description' => esc_html__( 'Set the speed of animations, in milliseconds.', 'londres' ),
						'value' => '1000'
					),
					array(
						'type' => 'dropdown',
						'heading' => esc_html__( 'Direction Navigation', 'londres' ),
						'param_name' => 'des_twitter_direction_nav',
						'description' => esc_html__( 'Create navigation for previous/next navigation.', 'londres' ),
						'value' => array( esc_html__( 'Yes, please', 'londres' ) => 'yes', esc_html__( 'No, thanks', 'londres' ) => 'no' )
					),
					array(
						'type' => 'dropdown',
						'heading' => esc_html__( 'Navigation Style', 'londres' ),
						'param_name' => 'des_twitter_direction_nav_style',
						'description' => esc_html__( 'Choose between Dark and Light style.', 'londres' ),
						'value' => array( esc_html__( 'Dark', 'londres' ) => 'dark', esc_html__( 'Light', 'londres' ) => 'light' )
					),
					array(
						'type' => 'dropdown',
						'heading' => esc_html__( 'Control Navigation', 'londres' ),
						'param_name' => 'des_twitter_control_nav',
						'description' => esc_html__( 'Create navigation for paging control of each slide.', 'londres' ),
						'value' => array( esc_html__( 'Yes, please', 'londres' ) => 'yes', esc_html__( 'No, thanks', 'londres' ) => 'no' )
					),
					array(
						'type' => 'dropdown',
						'heading' => esc_html__( 'Pause on Hover', 'londres' ),
						'param_name' => 'des_twitter_pause_on_hover',
						'description' => esc_html__( 'Pause the slideshow when hovering over slider, then resume when no longer hovering.', 'londres' ),
						'value' => array( esc_html__( 'Yes, please', 'londres' ) => 'yes', esc_html__( 'No, thanks', 'londres' ) => 'no' )
					),
					/*endofflexoptions*/
				),
				'js_view' => 'VcTwitterScrollerView',
				'front_enqueue_js' => array(LONDRES_PLG_URL . "lib/vc_shortcodes/twitter_scroller.js")
			) );
		
			vc_map( array(
				'name' => esc_html__( '[UPPER] Partners', 'londres' ),
				'category' => 'UPPER Shortcodes',
				'base' => 'partners',
				'is_container' => false,
				'icon' => 'vc_extend_partners_icon', // or css class name which you can reffer in your css file later. Example: "vc_extend_my_class"
				'admin_enqueue_js' => LONDRES_PLG_URL . 'lib/vc_shortcodes/partners.js',
				'params' => array(
					array(
						'type' => 'partners_cats',
						'heading' => esc_html__( 'Categories', 'londres' ),
						'param_name' => 'partners_cats',
						'description' => esc_html__( 'Choose one or more Categories', 'londres' )
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Number of Partners', 'londres' ),
						'param_name' => 'number',
						'description' => esc_html__( 'The number of Partners. If set to 0 it will display all.', 'londres' )
					),
					/* new order options */
					array(
						'type' => 'dropdown',
						'heading' => esc_html__( 'Order By', 'londres' ),
						'param_name' => 'londres_partners_orderby',
						'value' => array( esc_html__( 'Date', 'londres' ) => 'date', esc_html__( 'Title', 'londres' ) => 'title' )
					),
					array(
						'type' => 'dropdown',
						'heading' => esc_html__( 'Order', 'londres' ),
						'param_name' => 'londres_partners_order',
						'value' => array( esc_html__( 'DESC', 'londres' ) => 'DESC', esc_html__( 'ASC', 'londres' ) => 'ASC' )
					),
					/* endof new order options */
					array(
						'type' => 'dropdown',
						'heading' => esc_html__( 'Display tooltip?', 'londres' ),
						'param_name' => 'tooltip',
						'description' => esc_html__( 'If selected, a tooltip with the Partner\'s name will be displayed on hover.', 'londres' ),
						'value' => array( esc_html__( 'Yes, please', 'londres' ) => 'yes', esc_html__( 'No, thanks', 'londres' ) => 'no' )
					),
					array(
						'type' => 'dropdown',
						'heading' => esc_html__( 'Display scroller?', 'londres' ),
						'param_name' => 'scroller',
						'description' => esc_html__( 'If selected, the Partner\'s will be displayed with a scroller.', 'londres' ),
						'value' => array( esc_html__( 'Yes, please', 'londres' ) => 'yes', esc_html__( 'No, thanks', 'londres' ) => 'no' )
					),
				
					/*owlcarousel*/
					array(
						'type' => 'dropdown',
						'heading' => esc_html__( 'Auto Play?', 'londres' ),
						'param_name' => 'des_partners_owl_autoplay',
						'description' => esc_html__( 'Animate slider automatically.', 'londres' ),
						'value' => array( esc_html__( 'Yes, please', 'londres' ) => 'yes', esc_html__( 'No, thanks', 'londres' ) => 'no' )
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Animation Speed', 'londres' ),
						'param_name' => 'des_partners_owl_animation_speed',
						'description' => esc_html__( 'Set the speed of the slideshow cycling, in milliseconds.', 'londres' ),
						'value' => '3000'
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Number of Items in Desktop', 'londres' ),
						'param_name' => 'des_partners_owl_items_desktop',
						'description' => esc_html__( 'The number of visible items per slide in a desktop.', 'londres' ),
						'value' => '6'
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Number of Items in a Small Desktop', 'londres' ),
						'param_name' => 'des_partners_owl_items_small_desktop',
						'description' => esc_html__( 'The number of visible items per slide in a small desktop.', 'londres' ),
						'value' => '4'
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Number of Items in a Tablet', 'londres' ),
						'param_name' => 'des_partners_owl_items_tablet',
						'description' => esc_html__( 'The number of visible items per slide in a tablet.', 'londres' ),
						'value' => '2'
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Number of Items in Mobile', 'londres' ),
						'param_name' => 'des_partners_owl_items_mobile',
						'description' => esc_html__( 'The number of visible items per slide in a mobile.', 'londres' ),
						'value' => '1'
					),
					array(
						'type' => 'dropdown',
						'heading' => esc_html__( 'Navigation?', 'londres' ),
						'param_name' => 'des_partners_owl_navigation',
						'description' => esc_html__( 'Display "next" and "prev" buttons.', 'londres' ),
						'value' => array( esc_html__( 'Yes, please', 'londres' ) => 'yes', esc_html__( 'No, thanks', 'londres' ) => 'no' )
					),
					array(
						'type' => 'dropdown',
						'heading' => esc_html__( 'Navigation Style', 'londres' ),
						'param_name' => 'des_partners_owl_nav_style',
						'description' => esc_html__( 'Choose between Dark and Light style.', 'londres' ),
						'value' => array( esc_html__( 'Dark', 'londres' ) => 'dark', esc_html__( 'Light', 'londres' ) => 'light' )
					),
					array(
						'type' => 'dropdown',
						'heading' => esc_html__( 'Pagination?', 'londres' ),
						'param_name' => 'des_partners_owl_pagination',
						'description' => esc_html__( 'Show pagination.', 'londres' ),
						'value' => array( esc_html__( 'Yes, please', 'londres' ) => 'yes', esc_html__( 'No, thanks', 'londres' ) => 'no' )
					),
					array(
						'type' => 'dropdown',
						'heading' => esc_html__( 'Pagination Style', 'londres' ),
						'param_name' => 'des_partners_owl_pag_style',
						'description' => esc_html__( 'Choose between Dark and Light style.', 'londres' ),
						'value' => array( esc_html__( 'Dark', 'londres' ) => 'dark', esc_html__( 'Light', 'londres' ) => 'light' )
					),
					array(
						'type' => 'dropdown',
						'heading' => esc_html__( 'Pause on Hover', 'londres' ),
						'param_name' => 'des_partners_owl_pause_on_hover',
						'description' => esc_html__( 'Pause the slideshow when hovering over slider, then resume when no longer hovering.', 'londres' ),
						'value' => array( esc_html__( 'Yes, please', 'londres' ) => 'yes', esc_html__( 'No, thanks', 'londres' ) => 'no' )
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Slider Height', 'londres' ),
						'param_name' => 'des_partners_owl_height',
						'description' => esc_html__( 'The height of the slider in pixels.', 'londres' ),
						'value' => '130'
					),
					/*endofowlcarousel*/
				
					array(
						'type' => 'dropdown',
						'heading' => esc_html__( 'Number of Partners per row', 'londres' ),
						'param_name' => 'number_per_row',
						'description' => esc_html__( 'The number of Partners per row.', 'londres' ),
						'value' => array_reverse(array( '6' => '6', '4' => '4', '3' => '3', '2' => '2', '1' => '1' ))
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Row Height', 'londres' ),
						'param_name' => 'row_height',
						'description' => esc_html__( 'The height of each row of partners in pixels.', 'londres' ),
						'value' => '130'
					),
					array(
						'type' => 'dropdown',
						'heading' => esc_html__( 'Display Inner Border?', 'londres' ),
						'param_name' => 'innerborder',
						'value' => array( esc_html__( 'Yes, please', 'londres' ) => 'yes', esc_html__( 'No, thanks', 'londres' ) => 'no' ),
						'description' => esc_html__( 'Displays a border between the partners.', 'londres' ),
					),
					array(
						'type' => 'colorpicker',
						'heading' => esc_html__( 'Inner Border Color', 'londres' ),
						'param_name' => 'inner_border_color',
						'description' => esc_html__( 'Select a color for the border.', 'londres' )
					)

				),
				'js_view' => 'VcPartnersView',
				'front_enqueue_js' => array(LONDRES_PLG_URL . "lib/vc_shortcodes/partners.js")
			) );
		
			vc_map( array(
				'name' => esc_html__( '[UPPER] Team', 'londres' ),
				"category" => 'UPPER Shortcodes',
				'base' => 'team',
				'is_container' => false,
				"icon" => "vc_extend_team_icon", // or css class name which you can reffer in your css file later. Example: "vc_extend_my_class"
				'admin_enqueue_js' => LONDRES_PLG_URL . 'lib/vc_shortcodes/team.js',
				'params' => array(
					array(
						'type' => 'team_cats',
						'heading' => esc_html__( 'Categories', 'londres' ),
						'param_name' => 'team_cats',
						'description' => esc_html__( 'Choose one or more Categories', 'londres' )
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Number of Team Members', 'londres' ),
						'param_name' => 'number',
						'description' => esc_html__( 'The number of Team Members. If set to 0 it will display all.', 'londres' )
					),
					/* new order options */
					array(
						'type' => 'dropdown',
						'heading' => esc_html__( 'Order By', 'londres' ),
						'param_name' => 'londres_team_orderby',
						'value' => array( esc_html__( 'Date', 'londres' ) => 'date', esc_html__( 'Title', 'londres' ) => 'title' )
					),
					array(
						'type' => 'dropdown',
						'heading' => esc_html__( 'Order', 'londres' ),
						'param_name' => 'londres_team_order',
						'value' => array( esc_html__( 'DESC', 'londres' ) => 'DESC', esc_html__( 'ASC', 'londres' ) => 'ASC' )
					),
					/* endof new order options */
					array(
						'type' => 'dropdown',
						'heading' => esc_html__( 'Display tooltip?', 'londres' ),
						'param_name' => 'tooltip',
						'description' => esc_html__( 'If selected, a tooltip with the Team Member\'s name will be displayed on hover.', 'londres' ),
						'value' => array( esc_html__( 'Yes, please', 'londres' ) => 'yes', esc_html__( 'No, thanks', 'londres' ) => 'no' )
					),
					array(
						'type' => 'dropdown',
						'heading' => esc_html__( 'Display scroller?', 'londres' ),
						'param_name' => 'scroller',
						'description' => esc_html__( 'If selected, the Team Member\'s will be displayed with a scroller.', 'londres' ),
						'value' => array( esc_html__( 'Yes, please', 'londres' ) => 'yes', esc_html__( 'No, thanks', 'londres' ) => 'no' )
					),
				
					/*owlcarousel*/
					array(
						'type' => 'dropdown',
						'heading' => esc_html__( 'Auto Play?', 'londres' ),
						'param_name' => 'des_team_owl_autoplay',
						'description' => esc_html__( 'Animate slider automatically.', 'londres' ),
						'value' => array( esc_html__( 'Yes, please', 'londres' ) => 'yes', esc_html__( 'No, thanks', 'londres' ) => 'no' )
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Animation Speed', 'londres' ),
						'param_name' => 'des_team_owl_animation_speed',
						'description' => esc_html__( 'Set the speed of the slideshow cycling, in milliseconds.', 'londres' ),
						'value' => '3000'
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Number of Items in Desktop', 'londres' ),
						'param_name' => 'des_team_owl_items_desktop',
						'description' => esc_html__( 'The number of visible items per slide in a desktop.', 'londres' ),
						'value' => '6'
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Number of Items in a Small Desktop', 'londres' ),
						'param_name' => 'des_team_owl_items_small_desktop',
						'description' => esc_html__( 'The number of visible items per slide in a small desktop.', 'londres' ),
						'value' => '4'
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Number of Items in a Tablet', 'londres' ),
						'param_name' => 'des_team_owl_items_tablet',
						'description' => esc_html__( 'The number of visible items per slide in a tablet.', 'londres' ),
						'value' => '2'
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Number of Items in Mobile', 'londres' ),
						'param_name' => 'des_team_owl_items_mobile',
						'description' => esc_html__( 'The number of visible items per slide in a mobile.', 'londres' ),
						'value' => '1'
					),
					array(
						'type' => 'dropdown',
						'heading' => esc_html__( 'Navigation?', 'londres' ),
						'param_name' => 'des_team_owl_navigation',
						'description' => esc_html__( 'Display "next" and "prev" buttons.', 'londres' ),
						'value' => array( esc_html__( 'Yes, please', 'londres' ) => 'yes', esc_html__( 'No, thanks', 'londres' ) => 'no' )
					),
					array(
						'type' => 'dropdown',
						'heading' => esc_html__( 'Navigation Style', 'londres' ),
						'param_name' => 'des_team_owl_nav_style',
						'description' => esc_html__( 'Choose between Dark and Light style.', 'londres' ),
						'value' => array( esc_html__( 'Dark', 'londres' ) => 'dark', esc_html__( 'Light', 'londres' ) => 'light' )
					),
					array(
						'type' => 'dropdown',
						'heading' => esc_html__( 'Pagination?', 'londres' ),
						'param_name' => 'des_team_owl_pagination',
						'description' => esc_html__( 'Show pagination.', 'londres' ),
						'value' => array( esc_html__( 'Yes, please', 'londres' ) => 'yes', esc_html__( 'No, thanks', 'londres' ) => 'no' )
					),
					array(
						'type' => 'dropdown',
						'heading' => esc_html__( 'Pagination Style', 'londres' ),
						'param_name' => 'des_team_owl_pag_style',
						'description' => esc_html__( 'Choose between Dark and Light style.', 'londres' ),
						'value' => array( esc_html__( 'Dark', 'londres' ) => 'dark', esc_html__( 'Light', 'londres' ) => 'light' )
					),
					array(
						'type' => 'dropdown',
						'heading' => esc_html__( 'Pause on Hover', 'londres' ),
						'param_name' => 'des_team_owl_pause_on_hover',
						'description' => esc_html__( 'Pause the slideshow when hovering over slider, then resume when no longer hovering.', 'londres' ),
						'value' => array( esc_html__( 'Yes, please', 'londres' ) => 'yes', esc_html__( 'No, thanks', 'londres' ) => 'no' )
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Slider Height', 'londres' ),
						'param_name' => 'des_team_owl_height',
						'description' => esc_html__( 'The height of the slider in pixels.', 'londres' ),
						'value' => '350'
					),
					/*endofowlcarousel*/
				
					array(
						'type' => 'dropdown',
						'heading' => esc_html__( 'Number of Team Members per row', 'londres' ),
						'param_name' => 'number_per_row',
						'description' => esc_html__( 'The number of Partners per row.', 'londres' ),
						'value' => array( '6' => '6', '4' => '4', '3' => '3', '2' => '2', '1' => '1' )
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Row Height on Desktops', 'londres' ),
						'param_name' => 'row_height',
						'description' => esc_html__( 'The height of each row of partners in pixels.', 'londres' ),
						'value' => '350'
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Row Height on Tablets', 'londres' ),
						'param_name' => 'row_height_tablets',
						'description' => esc_html__( 'The height of each row of partners in pixels.', 'londres' ),
						'value' => '230'
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Row Height on Mobiles', 'londres' ),
						'param_name' => 'row_height_mobiles',
						'description' => esc_html__( 'The height of each row of partners in pixels.', 'londres' ),
						'value' => '170'
					)
				),
				'js_view' => 'VcTeamView',
				'front_enqueue_js' => array(LONDRES_PLG_URL . "lib/vc_shortcodes/team.js")
			) );
	    }
    
	    /*
	    Shortcode logic how it should be rendered
	    */
    
	    public function londres_renderNewsletter( $atts, $content = null ){
			$code = str_replace('&', '&amp;', get_option("londres_mailchimp_code"));
			if (!empty($code)){
			    $output = '<div class="newsletter_shortcode"><div class="mail-box"><div class="mail-news"><div class="news-l"><div class="banner"><h3>'.wp_kses_post(get_option("londres_newsletter_text")).'</h3><p>'.wp_kses_post(get_option("londres_newsletter_stext")).'</p></div><div class="form">';
			    if (function_exists('icl_t')){
				    $output = '<div class="newsletter_shortcode"><div class="mail-box"><div class="mail-news"><div class="news-l"><div class="banner"><h3>'.wp_kses_post(icl_t( 'londres', 'Subscribe our <span class=text_color>Newsletter</span>', get_option('londres_newsletter_text'))).'</h3><p>'.wp_kses_post(icl_t( 'londres', 'Subscribe to our newsletter to receive news, cool free stuff updates and new released products (no spam!)', get_option('londres_newsletter_stext'))).'</p></div><div class="form">';
			    }
				$output .= stripslashes($code);
				$output .= '</div></div></div></div></div>';
			} else {
				$output = '<div class="newsletter_shortcode">'.esc_html__('You need to fill the inputs on the Appearance > Londres Options > Newsletter panel in order to work.','londres').'</div>';
			}
		    return $output;
	    }
    
		public function londres_renderTeam( $atts, $content = null ) {	
  		  extract( shortcode_atts( array(
  			 'team_cats' => -1,
  		  	 'number' => -1,
  			 'tooltip' => 'yes',
  			 'scroller' => 'yes',
  			 'londres_team_orderby' => 'date',
  			 'londres_team_order' => 'DESC',
  			 'des_team_owl_autoplay' => 'yes',
  			 'des_team_owl_animation_speed' => 3000,
  			 'des_team_owl_items_desktop' => 6,
  			 'des_team_owl_items_small_desktop' => 4,
  			 'des_team_owl_items_tablet' => 2,
  			 'des_team_owl_items_mobile' => 1,
  			 'des_team_owl_navigation' => 'yes',
  			 'des_team_owl_pagination' => 'yes',
  			 'des_team_owl_pause_on_hover' => 'yes',
  			 'number_per_row' => 6,
  			 'row_height' => 350,
  			 'row_height_tablets' => 230,
  			 'row_height_mobiles' => 170,
  			 'des_team_owl_height' => 350,
  			 'des_team_owl_nav_style' => 'dark',
  			 'des_team_owl_pag_style' => 'dark'
  		  ), $atts ) );
		
  	      $content = wpb_js_remove_wpautop($content, true); // fix unclosed/unwanted paragraph tags in $content
	
  		  global $londres_team_index, $londres_team_index_aux;
			$londres_team_index = vc_is_inline() ? uniqid() : $londres_team_index;
			$londres_team_index_aux = vc_is_inline() ? uniqid() : 1;
	
  		  $output = "";
  		  if ($team_cats == "0" || $team_cats == -1 || $team_cats == "") {
  			  $args = array(
  				'posts_per_page' => $number,
  			  	'post_type' => 'team'
  			  );
  			  //$team = get_posts($args);
  		  } else {
  			  $team_cats = explode(",",$team_cats);
  			  $aux_cats = array();
  			  foreach($team_cats as $t){
  			  	  $term = get_term_by( 'slug', $t, 'team_category', ARRAY_A );
  			  	  if (!empty($term)){
	  			  	  $aux_cats[] = $term['term_taxonomy_id'];
  			  	  }
  			  }
  			  $team_cats = $aux_cats;
  			  
  			  $args = array(
  				'posts_per_page' => $number,
  			  	'post_type' => 'team',
  			  	'tax_query' => array(
  			        array(
  			            'taxonomy' => 'team_category',
  			            'field'    => 'term_id',
  			            'terms'    => $team_cats
  			        ),
  			    )
  		   	  );
  		   	  //$team = get_posts($args);
  		  }
  		  
  		  $args['orderby'] = $londres_team_orderby;
  		  $args['order'] = $londres_team_order;
  		  $team = get_posts($args);
		
  		  if ($scroller == "no"){
  				$output .= '<div id="des-team-'.$londres_team_index.'" class="des-team-shortcode row team text-center noscroller '; 
  				if ($tooltip == 'yes') $output .= "withtooltip";
  				$output .= '">';
  				foreach ($team as $t){
  					$output .= '<div class="col-xs-'.(12/intval($number_per_row,10)).' col-sm-'.(12/intval($number_per_row,10)).'" style="height: '.intval($row_height,10).'px;"><a data-toggle="modal" href="#member'.$londres_team_index.'-'.$londres_team_index_aux.'" class="modal-popup-link team-profile profile-id-'.$t->ID.'"><img src="'; 
  					$image = wp_get_attachment_image_src( get_post_thumbnail_id( $t->ID ), 'single-post-thumbnail' );
  					$output .= esc_url($image[0]);
  					$output .= '" alt="'.esc_attr($t->post_title).'" /><div class="tooltip-desc"><div class="tooltip-content"><p><b>'.wp_kses_post($t->post_title).'</b></p></div></div><div class="overlay-thumb-tem"></div></a></div>';
  					
  					$shortcodes_custom_css = get_post_meta( $t->ID, '_wpb_shortcodes_custom_css', true );
					if ( ! empty( $shortcodes_custom_css ) ) {
						londres_set_custom_inline_css($shortcodes_custom_css);
					}
					$post_custom_css = get_post_meta( $t->ID, '_wpb_post_custom_css', true );
					if ( ! empty( $post_custom_css ) ) {
						$post_custom_css = strip_tags( $post_custom_css );
						londres_set_custom_inline_css($post_custom_css);
					}
  					
  					$londres_team_index_aux++;
  				}
  				$output .= '</div>';
  				$team_profile_contents = '';
  				$londres_team_index_aux = 1;
  				foreach ($team as $t){
  					$team_profile_contents .= '<div id="member'.$londres_team_index.'-'.$londres_team_index_aux.'" class="modal team_member_profile_content"><div class="container">'.do_shortcode($t->post_content).'</div><a href="#" class="close" data-dismiss="modal">x</a></div>';
  					$londres_team_index_aux++;
  					
  					$shortcodes_custom_css = get_post_meta( $t->ID, '_wpb_shortcodes_custom_css', true );
					if ( ! empty( $shortcodes_custom_css ) ) {
						londres_set_custom_inline_css($shortcodes_custom_css);
					}
					$post_custom_css = get_post_meta( $t->ID, '_wpb_post_custom_css', true );
					if ( ! empty( $post_custom_css ) ) {
						$post_custom_css = strip_tags( $post_custom_css );
						londres_set_custom_inline_css($post_custom_css);
					}
					
  				}
  				londres_set_team_profiles_content($team_profile_contents);
  				$output .= '<style>@media only screen and (min-width: 768px) and (max-width: 959px) {#des-team-'.$londres_team_index.' > div{height:'.intval($row_height_tablets,10).'px !important;}}@media only screen and (max-width: 767px) {#des-team-'.$londres_team_index.' > div{height:'.intval($row_height_mobiles,10).'px !important;}}</style>';
  		  } else {
  				wp_enqueue_script('ult-slick');
  				wp_enqueue_script('ultimate-appear');
  				wp_enqueue_script('ult-slick-custom');
  				$output .= '<div id="des-team-'.$londres_team_index.'" class="des-team-shortcode row team text-center withscroller'; 
  				if ($tooltip == 'yes') $output .= " withtooltip";
  				$output .= '" style="max-height:'.intval($des_team_owl_height).'px;">';
  				foreach ($team as $t){
  					$output .= '<div style="display:inline-block;"><a data-toggle="modal" href="#member'.$londres_team_index.'-'.$londres_team_index_aux.'" class="modal-popup-link team-profile profile-id-'.esc_attr($t->ID).'"><img src="'; 
  					$image = wp_get_attachment_image_src( get_post_thumbnail_id( $t->ID ), 'single-post-thumbnail' );
  					$output .= esc_url($image[0]);
  					$output .= '" alt="'.esc_attr($t->post_title).'" /><div class="tooltip-desc"><div class="tooltip-content"><p><b>'.wp_kses_post($t->post_title).'</b></p></div></div></a><div class="overlay-thumb-tem"></div></div>';
  					$londres_team_index_aux++;
  				}
  				$output .= '</div>';
  				$output .= "<div class='des_shortcode_hidden'>".esc_html($des_team_owl_autoplay.'|'.$des_team_owl_animation_speed.'|'.$des_team_owl_items_desktop.'|'.$des_team_owl_items_small_desktop.'|'.$des_team_owl_items_tablet.'|'.$des_team_owl_items_mobile.'|'.$des_team_owl_navigation.'|'.$des_team_owl_pagination.'|'.$des_team_owl_pause_on_hover.'|'.$des_team_owl_nav_style.'|'.$des_team_owl_pag_style)."</div>";
  				
  				$team_profile_contents = '';
  				$londres_team_index_aux = 1;
  				foreach ($team as $t){
  					$team_profile_contents .= '<div id="member'.$londres_team_index.'-'.$londres_team_index_aux.'" class="modal team_member_profile_content"><div class="container">'.wpb_js_remove_wpautop($t->post_content).'</div><a href="#" class="close" data-dismiss="modal">x</a></div>';
  					$londres_team_index_aux++;
  				}
  				londres_set_team_profiles_content($team_profile_contents);
  		  }
	
  		  $londres_team_index++;
  		  
  		  $output .= "<script type=\"text/javascript\">
					jQuery(document).ready(function(){
						
						if (window.self===window.top){
							if (jQuery('body').is('.wp-admin.compose-mode')){
								return;
							}
						}
						
						jQuery('.des-team-shortcode').each(function(){
							var who = jQuery(this);
							if (who.hasClass('withscroller')){
								var owlopts = who.next('.des_shortcode_hidden').html().split('|');
								if (owlopts[0] == 'yes') owlopts[0] = parseInt(owlopts[1],10); else owlopts[0] = false;
								if (owlopts[6] == 'yes') {
									owlopts[6] = true;
									who.addClass('nav-'+owlopts[9]);
								} else owlopts[6] = false;
								if (owlopts[7] == 'yes') {
									owlopts[7] = true; 
									who.addClass('controlnav-'+owlopts[10]);
								} else owlopts[7] = false;
								if (owlopts[8] == 'yes') owlopts[8] = true; else owlopts[8] = false;
								who.slick({
									dots: owlopts[7], 
									autoplay: owlopts[0], 
									autoplaySpeed:5000, speed:600, infinite: true,
									arrows: owlopts[6],
									adaptiveHeight:true,
									nextArrow:'<button type=\"button\" class=\"slick-next default\"><i class=\"ultsl-arrow-right6\"></i></button>',
									prevArrow:'<button type=\"button\" class=\"slick-prev default\"><i class=\"ultsl-arrow-left6\"></i></button>',
									swipe:true,
									draggable:true,
									touchMove:true,
									slidesToScroll: parseInt(owlopts[2],10),
									slidesToShow: parseInt(owlopts[2],10),
									responsive:[{
										breakpoint: 1024,
										settings:{
											slidesToShow: parseInt(owlopts[3],10),
											slidesToScroll: parseInt(owlopts[3],10)
										}
									},{
										breakpoint: 768,
										settings:{
											slidesToShow: parseInt(owlopts[4],10),
											slidesToScroll: parseInt(owlopts[4],10)
										}
									},{
										breakpoint: 480,
										settings:{
											slidesToShow: parseInt(owlopts[5],10),
											slidesToScroll: parseInt(owlopts[5],10)
										}
									}],
									pauseOnHover:true,
									pauseOnDotsHover:true,
									customPaging:function(slider,i){
										return'<i type=\"button\" style=\"class=\"ultsl-record\" data-role=\"none\"></i>';
									}
								});
							}
							who.removeClass('des-team-shortcode');
						});
						
					});
				</script>";
  	      return $output;
		}

		public function londres_renderPartners( $atts, $content = null ) {
		
		  extract( shortcode_atts( array(
			 'partners_cats' => -1,
		  	 'number' => -1,
		  	 'londres_partners_orderby' => 'date',
		  	 'londres_partners_order' => 'DESC',
			 'tooltip' => 'yes',
			 'scroller' => 'yes',
			 'des_partners_owl_autoplay' => 'yes',
			 'des_partners_owl_animation_speed' => 3000,
			 'des_partners_owl_items_desktop' => 6,
			 'des_partners_owl_items_small_desktop' => 4,
			 'des_partners_owl_items_tablet' => 2,
			 'des_partners_owl_items_mobile' => 1,
			 'des_partners_owl_navigation' => 'yes',
			 'des_partners_owl_nav_style' => 'dark',
			 'des_partners_owl_pagination' => 'yes',
			 'des_partners_owl_pag_style' => 'dark',
			 'des_partners_owl_pause_on_hover' => 'yes',
			 'number_per_row' => 6,
			 'row_height' => 130,
			 'des_partners_owl_height' => 130,
			 'innerborder' => 'yes',
			 'inner_border_color' => '#EDEDED'
		  ), $atts ) );
	
	      $content = wpb_js_remove_wpautop($content, true); // fix unclosed/unwanted paragraph tags in $content
		  
		  global $londres_partners_index;
			  $londres_partners_index = vc_is_inline() ? uniqid() : $londres_partners_index;

		  $output = "";  
	  
		  if ($partners_cats == "0" || $partners_cats == -1 || $partners_cats == "") {
			  $args = array(
				'posts_per_page' => $number,
			  	'post_type' => 'partners'
			  );
			  //$partners = get_posts($args);
		  } else {
			  $partners_cats = explode(",",$partners_cats);
			  $aux_cats = array();
  			  foreach($partners_cats as $t){
  			  	  $term = get_term_by( 'slug', $t, 'partners_category', ARRAY_A );
  			  	  if (!empty($term)){
	  			  	  $aux_cats[] = $term['term_taxonomy_id'];
  			  	  }
  			  }
  			  $partners_cats = $aux_cats;
			  $args = array(
				'posts_per_page' => $number,
			  	'post_type' => 'partners',
			  	'tax_query' => array(
			        array(
			            'taxonomy' => 'partners_category',
			            'field'    => 'term_id',
			            'terms'    => $partners_cats
			        ),
			    )
		   	  );
		   	  //$partners = get_posts($args);
		  }
		  
		  $args['orderby'] = $londres_partners_orderby;
  		  $args['order'] = $londres_partners_order;
		  $partners = get_posts($args);

		  if ($scroller == "no"){
				$output .= '<div id="partners-'.esc_attr($londres_partners_index).'" class="partners-container noscroller'; 
				if ($tooltip == 'yes') $output .= " withtooltip";
				if ($innerborder == 'yes') $output .= " innerborder innerbordercolor-{$inner_border_color}";
				$output .= '">';
				foreach ($partners as $p){
					$output .= '<div class="partner-item col-md-'.esc_attr((12/intval($number_per_row,10))).'" style="height: '.esc_attr(intval($row_height,10)).'px;line-height: '.esc_attr(intval($row_height,10)).'px;"><a target="_blank" href="'.esc_url(get_post_meta($p->ID,'link_value', true)).'" ';
					if ($tooltip == "yes") $output .= ' data-toggle="tooltip" data-placement="top" title="'.esc_attr($p->post_title).'"';
					$output .= '><img title="'.esc_attr($p->post_title).'" src="';
					$image = wp_get_attachment_image_src( get_post_thumbnail_id( $p->ID ), 'single-post-thumbnail' );
					$output .= esc_url($image[0]).'" />';
					$output .= '</a></div>';
				}
				$output .= '</div>';
				$output .= "<script type=\"text/javascript\">
						jQuery(document).ready(function(){ jQuery('#partners-".esc_js($londres_partners_index).".withtooltip .partner-item a, #partners-".esc_js($londres_partners_index).".withtooltip .carousel-item a').tooltip(); });
						</script>";
			} else {
				wp_enqueue_script('ult-slick');
	  			wp_enqueue_script('ultimate-appear');
	  			wp_enqueue_script('ult-slick-custom');
				$output .= '<div id="logos-carousel-'.esc_attr($londres_partners_index).'" class="partners-container owl-carousel light-text'; 
				if ($tooltip == 'yes') $output .= ' withtooltip"'; else $output .= '"';
				$output .= ' style="max-height:'.intval($des_partners_owl_height).'px;" ';
				$output .= 'rel="'.esc_attr($des_partners_owl_autoplay.'|'.$des_partners_owl_animation_speed.'|'.$des_partners_owl_items_desktop.'|'.$des_partners_owl_items_small_desktop.'|'.$des_partners_owl_items_tablet.'|'.$des_partners_owl_items_mobile.'|'.$des_partners_owl_navigation.'|'.$des_partners_owl_pagination.'|'.$des_partners_owl_pause_on_hover.'|'.$des_partners_owl_nav_style.'|'.$des_partners_owl_pag_style).'">';
				foreach ($partners as $p){
					$output .= '<div class="carousel-item" style="height:'.esc_attr(intval($des_partners_owl_height,10)).'px;line-height:'.esc_attr(intval($des_partners_owl_height,10)).'px;"><a target="_blank" href="'.esc_url(get_post_meta($p->ID, 'link_value', true)).'" class="c-adj"'; 
					if ($tooltip == "yes") $output .= ' data-toggle="tooltip" data-placement="top" title="'.esc_attr($p->post_title).'"';
					$output .= '><img alt="'.esc_attr($p->post_title).'" src="';
					$image = wp_get_attachment_image_src( get_post_thumbnail_id( $p->ID ), 'single-post-thumbnail' );
					$output .= esc_url($image[0]);
					$output .= '"></a></div>';
				}
				$output .= '</div>';
			
				$output .= "
				<script type=\"text/javascript\">
					jQuery(document).ready(function(){ 
						
						if (window.self===window.top){
							if (jQuery('body').is('.wp-admin.compose-mode')){
								return;
							}
						}

						var who = jQuery('#logos-carousel-".esc_js($londres_partners_index)."');
						var owlopts = who.attr('rel').split('|');
	
						if (owlopts[0] == 'yes') owlopts[1] = parseInt(owlopts[1],10); else owlopts[0] = false;
						if (owlopts[0] == 'yes') owlopts[0] = true; else owlopts[0] = false;
						if (owlopts[6] == 'yes') {
							owlopts[6] = true; 
							who.addClass('nav-'+owlopts[9]);
						} else owlopts[6] = false;
						if (owlopts[7] == 'yes') {
							owlopts[7] = true;
							who.addClass('controlnav-'+owlopts[10]);
						} else owlopts[7] = false;
						if (owlopts[8] == 'yes') owlopts[8] = true; else owlopts[8] = false;
						var this_autoplay = false;
						if (owlopts[0]!=false) this_autoplay = true;

						//if (who.is('.slick-initialized')) who.slick('destroy');
						who.slick({
							dots: owlopts[7], 
							autoplay: this_autoplay,
							autoplaySpeed: owlopts[1], speed:600, infinite: true,
							arrows: owlopts[6],
							adaptiveHeight:true,
							nextArrow:'<button type=\"button\" class=\"slick-next default\"><i class=\"ultsl-arrow-right6\"></i></button>',
							prevArrow:'<button type=\"button\" class=\"slick-prev default\"><i class=\"ultsl-arrow-left6\"></i></button>',
							swipe:true,
							draggable:true,
							touchMove:true,
							slidesToScroll: parseInt(owlopts[2],10),
							slidesToShow: parseInt(owlopts[2],10),
							responsive:[{
								breakpoint: 1024,
								settings:{
									slidesToShow: parseInt(owlopts[3],10),
									slidesToScroll: parseInt(owlopts[3],10)
								}
							},{
								breakpoint: 768,
								settings:{
									slidesToShow: parseInt(owlopts[4],10),
									slidesToScroll: parseInt(owlopts[4],10)
								}
							},{
								breakpoint: 480,
								settings:{
									slidesToShow: parseInt(owlopts[5],10),
									slidesToScroll: parseInt(owlopts[5],10)
								}
							}],
							pauseOnHover:true,
							pauseOnDotsHover:true,
							customPaging:function(slider,i){
								return'<i type=\"button\" class=\"ultsl-record\" data-role=\"none\"></i>';
							}
						});
						
							";
						if ($tooltip == "yes") $output .= "who.find('.partner-item, .carousel-item').css('margin-top','10px').find('a').tooltip(); ";
						$output .= "
						
					});
				</script>";
			
		  }
	  
	      wp_reset_postdata();

		  $londres_partners_index++;
	      return $output;
		}

		public function londres_renderTwitterScroller ( $atts, $content = null ){
			extract( shortcode_atts( array(
				 'des_twitter_animation' => 'slide',
				 'des_twitter_direction' => 'horizontal',
				 'des_twitter_slideshow' => 'yes',
				 'des_twitter_slideshow_speed' => '3500',
				 'des_twitter_animation_duration' => '1000',
				 'des_twitter_direction_nav' => 'yes',
				 'des_twitter_control_nav' => 'yes',
				 'des_twitter_pause_on_hover' => 'yes',
				 'des_twitter_direction_nav_style' => 'dark',
			), $atts ) );
		    $content = wpb_js_remove_wpautop($content, true); // fix unclosed/unwanted paragraph tags in $content

			global $londres_twitter_index;
				$londres_twitter_index = vc_is_inline() ? uniqid() : $londres_twitter_index;
			$output = "";
		
			$errors = false;
			$username = get_option("londres_twitter_username");
			if (empty($username)) $errors .= 'Twitter Username';
			$consumerkey = trim(get_option('twitter_consumer_key'));
			if (empty($consumerkey)){
				if ($errors) $errors .= ', ';
				$errors .= 'Twitter App Consumer Key';
			}
			$consumersecret = trim(get_option('twitter_consumer_secret'));
			if (empty($consumersecret)){
				if ($errors) $errors .= ', ';
				$errors .= 'Twitter App Consumer Secret';
			}
			$usertoken = trim(get_option('twitter_user_token'));
			if (empty($usertoken)){
				if ($errors) $errors .= ', ';
				$errors .= 'Twitter App User Token';
			}
			$usersecret = trim(get_option('twitter_user_secret'));
			if (empty($usersecret)){
				if ($errors) $errors .= ', ';
				$errors .= 'Twitter App Access Token Secret';
			}
			$tweetcount = get_option('londres_twitter_number_tweets');
			if (empty($tweetcount)) $tweetcount = 0;
		
			if ($errors){
				$output = '<div class="twitter_warning">'.esc_html__('You need to fill the following Twitter related fields in the Admin Panel > Londres Options > Twitter and Social Icons > Twitter. ','londres').'<br/>'.wp_kses_post($errors).'</div>';
			} else {
				wp_enqueue_script('ult-slick');
  				wp_enqueue_script('ultimate-appear');
  				wp_enqueue_script('ult-slick-custom');
				$output = '<div id="des-twitter-'.esc_attr($londres_twitter_index).'" class="twitter-container style-'.esc_attr($des_twitter_direction_nav_style).'" rel="'.esc_attr($username.'|'.$tweetcount).'"><div class="icon-author animated flipInX"><div class="bird"><i class="fa fa-twitter"></i></div><p class="twitter-author"><a href="http://twitter.com/'.esc_attr($username).'" target="_blank"><b>'.sprintf(esc_html__("%s",'londres'), get_option("londres_twitter_follow_us")).'</b></a></p></div><div class="twitter-slider"><div id="twitter-feed"></div></div></div>';
				$upper_twitter_pre_tweet = sprintf(esc_html__("%s", "londres"), get_option("londres_twitter_pre_tweet"));
				if (function_exists('icl_t')){
					$output = '<div id="des-twitter-'.esc_attr($londres_twitter_index).'" class="twitter-container style-'.esc_attr($des_twitter_direction_nav_style).'" rel="'.esc_attr($username.'|'.$tweetcount).'"><div class="icon-author animated flipInX"><div class="bird"><i class="fa fa-twitter"></i></div><p class="twitter-author"><a href="http://twitter.com/'.esc_attr($username).'" target="_blank"><b>'.sprintf(esc_html__("%s",'londres'), icl_t( 'londres', 'Follow us on Twitter', get_option('londres_twitter_follow_us'))).'</b></a></p></div><div class="twitter-slider"><div id="twitter-feed"></div></div></div>';
					$upper_twitter_pre_tweet = sprintf(esc_html__("%s", "londres"), icl_t( 'londres', 'I was looking at ', get_option('londres_twitter_pre_tweet')));
				}
				
				$output .= "<script type=\"text/javascript\">
					jQuery(document).ready(function(){
						
						if (window.self===window.top){
							if (jQuery('body').is('.wp-admin.compose-mode')){
								return;
							}
						}
						
						var who = jQuery('#des-twitter-".esc_js($londres_twitter_index)."');
						if (typeof vc !== 'undefined') who = jQuery(vc.".'$frame_body'.").find('#des-twitter-".esc_js($londres_twitter_index)."');
		
						if (!jQuery('body').is('.wp-admin.vc_inline-shortcode-edit-form')){
							who.find('#twitter-feed').destweet({
								username: \"".esc_js($username)."\",
								join_text: '".$upper_twitter_pre_tweet."',
								avatar_size: 0,
								count: ".esc_js($tweetcount)."
							});
						}
					
						who.find('#twitter-feed').find('ul').addClass('slides');
						who.find('#twitter-feed').find('ul li').addClass('slide');
						//who.find('#twitter-feed').contents().wrapAll('<div class=\"tweets-feed\">');
					
						var flexopts = [\"".esc_js($des_twitter_animation)."\",\"".esc_js($des_twitter_direction)."\",\"".esc_js($des_twitter_slideshow)."\",\"".esc_js($des_twitter_slideshow_speed)."\",\"".esc_js($des_twitter_animation_duration)."\",\"".esc_js($des_twitter_direction_nav)."\",\"".esc_js($des_twitter_control_nav)."\",\"".esc_js($des_twitter_pause_on_hover)."\",\"".esc_js($des_twitter_direction_nav_style)."\"];
		
						if (flexopts[2] == 'yes') flexopts[2] = true; else flexopts[2] = false;
						if (flexopts[5] == 'yes'){ 
							flexopts[5] = true; 
						} else flexopts[5] = false;
						if (flexopts[6] == 'yes') {
							flexopts[6] = true;
						} else flexopts[6] = false;
						if (flexopts[7] == 'yes') flexopts[7] = true; else flexopts[7] = false;
						if (flexopts[0] == 'fade') flexopts[0] = true; else flexopts[0] = false;
						
						who.on('loaded', function(){
							who.find('.tweet_list').slick({
								fade: flexopts[0],
								dots: flexopts[6], 
								autoplay: flexopts[2], 
								autoplaySpeed:flexopts[3], speed:flexopts[4], infinite: true,
								arrows: flexopts[5],
								adaptiveHeight:true,
								nextArrow:'<button type=\"button\" class=\"slick-next default\"><i class=\"ultsl-arrow-right6\"></i></button>',
								prevArrow:'<button type=\"button\" class=\"slick-prev default\"><i class=\"ultsl-arrow-left6\"></i></button>',
								swipe:true,
								draggable:true,
								touchMove:true,
								slidesToScroll: 1,
								slidesToShow: 1,
								pauseOnHover:flexopts[7],
								pauseOnDotsHover:flexopts[7],
								customPaging:function(slider,i){
									return'<i type=\"button\" class=\"ultsl-record\" data-role=\"none\"></i>';
								}
							});
						});
						
					});
					
					</script>";
			}
		
			$londres_twitter_index++;
			return $output;
		}

	    public function londres_renderTestimonials( $atts, $content = null ) {	
	    
		  extract( shortcode_atts( array(
			 'style_testimonials' => 'style1',
			 'testimonials_cats' => -1,
		  	 'number' => -1,
			 'hide_author' => 'no',
			 'hide_company' => 'no',
			 'des_testimonials_flex_animation' => 'slide',
			 'des_testimonials_flex_direction' => 'horizontal',
			 'des_testimonials_flex_items_desktop' => 1,
			 'des_testimonials_flex_items_small_desktop' => 1,
			 'des_testimonials_flex_items_tablet' => 1,
			 'des_testimonials_flex_items_mobile' => 1,
			 'des_testimonials_flex_slideshow' => 'yes',
			 'des_testimonials_flex_slideshow_speed' => '3500',
			 'des_testimonials_flex_animation_duration' => '1000',
			 'des_testimonials_flex_direction_nav' => 'yes',
			 'des_testimonials_flex_control_nav' => 'yes',
			 'des_testimonials_flex_pause_on_hover' => 'yes',
			 'des_testimonials_flex_nav_style' => 'dark',
			 'des_testimonials_flex_height' => '650'
		  ), $atts ) );
	      $content = wpb_js_remove_wpautop($content, true); // fix unclosed/unwanted paragraph tags in $content

		  global $londres_testimonials_index, $londres_single_testimonial_index;
			  $londres_testimonials_index = vc_is_inline() ? uniqid() : $londres_testimonials_index;
			  $londres_single_testimonial_index = vc_is_inline() ? uniqid() : $londres_single_testimonial_index;
		  $output = "";
	  
		  if ($testimonials_cats == "0" || $testimonials_cats == -1 || $testimonials_cats == "") {
			  $args = array(
				'posts_per_page' => $number,
			  	'post_type' => 'testimonials'
			  );
			  $testimonials = get_posts($args);
		  } else {
			  $testimonials_cats = explode(",",$testimonials_cats);
			  $aux_cats = array();
  			  foreach($testimonials_cats as $t){
  			  	  $term = get_term_by( 'slug', $t, 'testimonials_category', ARRAY_A );
  			  	  if (!empty($term)){
	  			  	  $aux_cats[] = $term['term_taxonomy_id'];
  			  	  }
  			  }
  			  $testimonials_cats = $aux_cats;
			  $args = array(
				'posts_per_page' => $number,
			  	'post_type' => 'testimonials',
			  	'tax_query' => array(
			        array(
			            'taxonomy' => 'testimonials_category',
			            'field'    => 'term_id',
			            'terms'    => $testimonials_cats
			        ),
			    )
		   	  );
		   	  $testimonials = get_posts($args);
		  }
	  
		  if ($style_testimonials === "style1"){
			  $output .= '<div id="testimonials-container-'.esc_attr($londres_testimonials_index).'" class="container testimonials '.esc_attr($style_testimonials).'"><div class="testimonial-box">'; 
			  
			  $first = true;
			  $londres_single_testimonial_index = 1;
			  $output .= '<ul class="testimonial-nav">';
			  foreach ($testimonials as $t){
				$output .= '<li><a href="#testimonial-'.esc_attr($londres_single_testimonial_index).'" ';
				if ($first){
					$first = false;
					$output .= ' class="active" ';
				}
				$output .= '><img alt="'.esc_attr(get_the_title( $t->ID )).'" src="'; 
				$image = wp_get_attachment_image_src( get_post_thumbnail_id( $t->ID ), 'single-post-thumbnail' );
				$output .= esc_url($image[0]);
				$output .= '" /><div class="cover-test-img"></div></a></li>';
				$londres_single_testimonial_index++;
			  }

			  $output .= '</ul>';
			  
			  
			  
			  
			  $londres_single_testimonial_index = 1;

			  $output .= '<div class="testimonials-content">';
			  foreach ($testimonials as $t){
				  	$active = ($londres_single_testimonial_index == 1) ? " active" : "";
					$output .= '<div class="testimonial'.esc_attr($active).'" id="testimonial-'.esc_attr($londres_single_testimonial_index).'">'.wp_kses_post($t->post_content);
					if ($hide_author === "no" && $hide_company === "no" || get_post_meta($t->ID,'author_value', true) != '' && get_post_meta($t->ID,'company_value', true) != '') { $output .= '<span>'; }
						if (get_post_meta($t->ID,'author_value', true) != ''){
							if (get_post_meta($t->ID,'author_link_value', true) != ''){ $output .= '<a href="'.esc_url(get_post_meta($t->ID,'author_link_value', true)).'">'; }
							$output .= esc_html(get_post_meta($t->ID,'author_value', true));
							if (get_post_meta($t->ID,'author_link_value', true) != ''){ $output .= '</a>'; }
						}
						if (get_post_meta($t->ID,'author_value', true) != '' && get_post_meta($t->ID,'company_value', true) != '') $output .= ' <br/> ';
						if (get_post_meta($t->ID,'company_value', true) != ''){
							if (get_post_meta($t->ID,'company_link_value', true) != ''){ $output .= '<a href="'.esc_url(get_post_meta($t->ID,'company_link_value', true)).'">'; }
							$output .= esc_html(get_post_meta($t->ID,'company_value', true));
							if (get_post_meta($t->ID,'company_link_value', true) != ''){ $output .= '</a>'; }
						}
					if ($hide_author === "no" && $hide_company === "no" || get_post_meta($t->ID,'author_value', true) != '' && get_post_meta($t->ID,'company_value', true) != '') { $output .= '</span>'; }
					$output .= '</div>';
				    $londres_single_testimonial_index++;
			  }
			  $output .= '</div>'; // end of testimonials-content

			  $output .= '</div></div>'; //end of #testimonials
		  
			  $output .= "
			  <script type=\"text/javascript\">
			  jQuery(document).ready(function(){
					var who = jQuery('#testimonials-container-".esc_js($londres_testimonials_index)."');
					who.find('.testimonials-content').height( who.find('.testimonials-content .testimonial.active > p').height() + who.find('.testimonials-content .testimonial.active > span').height() +40 );
					who.find('.testimonial-nav a').on('click', function(e){
						e.preventDefault();
					});
					who.find('.testimonial-nav a').on('mouseenter', function(){
						who.find('.testimonial-nav a').removeClass('active');
						jQuery(this).addClass('active');
						who.find('.testimonial.active').removeClass('active');
						who.find(jQuery(this).attr('href')).addClass('active');
						who.find('.testimonials-content').height( who.find('.testimonials-content .testimonial.active > p').height() + who.find('.testimonials-content .testimonial.active > span').height() +40 );
					});
			  });
			  </script>";		  
		  } else {
		  	wp_enqueue_script('ult-slick');
			wp_enqueue_script('ultimate-appear');
			wp_enqueue_script('ult-slick-custom');
			if ($style_testimonials == "style2"){
				$output .= '<div id="testimonials-slider-'.esc_attr($londres_testimonials_index).'" class="testimonials-style2 style-'.esc_attr($des_testimonials_flex_nav_style).'"><ul class="slides styled-list"'; 
			} else {
				$output .= '<div id="testimonials-slider-'.esc_attr($londres_testimonials_index).'" class="single-wide-testimonials testimonials-style2 style-'.esc_attr($des_testimonials_flex_nav_style).'"><ul class="slides styled-list"'; 
			}
			
			$output .= ' style="max-height:'.intval($des_testimonials_flex_height).'px;" ';
			$output .= '>';
					
			foreach ($testimonials as $t){
				$output .= '<li class="testimonials-slide"><div class="testimonials-slide-content container">';
			
				
				$output .= '<div class="text-container">';
				
				$output .= '<span class="testimonials_text_content">';
				$output .= wp_kses_post($t->post_content);
				$output .= '</span>';
				$output .= '</div>';
				
				$image = wp_get_attachment_image_src( get_post_thumbnail_id( $t->ID ), 'single-post-thumbnail' );
				if ($image[0] != ""){
					$output .= '<div class="img-container"><img title="'.esc_attr(get_post_meta($t->ID,'author_value', true)).'" src="'.esc_url($image[0]).'" alt="'.esc_attr(get_post_meta($t->ID,'author_value', true)).'">';
					if ($hide_author === "no" && $hide_company === "no" || get_post_meta($t->ID,'author_value', true) != '' && get_post_meta($t->ID,'company_value', true) != '') { $output .= '<span class="t-author">'; }
					if (get_post_meta($t->ID,'author_value', true) != ''){
						if (get_post_meta($t->ID,'author_link_value', true) != ''){ $output .= '<a href="'.esc_url(get_post_meta($t->ID,'author_link_value', true)).'">'; }
						$output .= esc_html(get_post_meta($t->ID,'author_value', true));
						if (get_post_meta($t->ID,'author_link_value', true) != ''){ $output .= '</a>'; }
					}
					if (get_post_meta($t->ID,'author_value', true) != '' && get_post_meta($t->ID,'company_value', true) != '') $output .= '';
					if (get_post_meta($t->ID,'company_value', true) != ''){
						if (get_post_meta($t->ID,'company_link_value', true) != ''){ $output .= '<a href="'.esc_url(get_post_meta($t->ID,'company_link_value', true)).'">'; }
						$output .= esc_html(get_post_meta($t->ID,'company_value', true));
						if (get_post_meta($t->ID,'company_link_value', true) != ''){ $output .= '</a>'; }
					}
					if ($hide_author === "no" && $hide_company === "no" || get_post_meta($t->ID,'author_value', true) != '' && get_post_meta($t->ID,'company_value', true) != '') { $output .= '</span>'; }
				
					$output .= '</div>';
				}
				
				$output .= '</div></li>';
			}
			$output .= '</ul></div>';
			
			
			if ($style_testimonials == "style3"){
				$des_testimonials_flex_items_desktop = $des_testimonials_flex_items_small_desktop = $des_testimonials_flex_items_tablet = $des_testimonials_flex_items_mobile = 1;
			}
			
					
			$output .= "
			<script type=\"text/javascript\">
				jQuery(document).ready(function(){
					
					if (window.self===window.top){
						if (jQuery('body').is('.wp-admin.compose-mode')){
							return;
						}
					}
					
					var who = jQuery('#testimonials-slider-".esc_js($londres_testimonials_index)." ul.slides');
				
					var opts = ['".esc_js($des_testimonials_flex_animation)."','".esc_js($des_testimonials_flex_direction)."','".esc_js($des_testimonials_flex_slideshow)."','".esc_js($des_testimonials_flex_slideshow_speed)."','".esc_js($des_testimonials_flex_animation_duration)."','".esc_js($des_testimonials_flex_direction_nav)."','".esc_js($des_testimonials_flex_control_nav)."','".esc_js($des_testimonials_flex_pause_on_hover)."','".esc_js($des_testimonials_flex_nav_style)."','".esc_js($des_testimonials_flex_items_desktop)."','".esc_js($des_testimonials_flex_items_small_desktop)."','".esc_js($des_testimonials_flex_items_tablet)."','".esc_js($des_testimonials_flex_items_mobile)."'];
					if (opts[2] == 'yes') opts[2] = true; else opts[2] = false;
					if (opts[5] == 'yes') {
						opts[5] = true;
					} else opts[5] = false;
					if (opts[6] == 'yes') {
						opts[6] = true;
					} else opts[6] = false;
					if (opts[7] == 'yes') opts[7] = true; else opts[7] = false;
					if (opts[0] == 'fade') opts[0] = true; else opts[0] = false;
				
					who.slick({
						fade: opts[0],
						dots: opts[6], 
						autoplay: opts[2], 
						autoplaySpeed:opts[3], speed:opts[4], infinite: true,
						arrows: opts[5],
						adaptiveHeight:true,
						nextArrow:'<button type=\"button\" class=\"slick-next default\"><i class=\"ultsl-arrow-right6\"></i></button>',
						prevArrow:'<button type=\"button\" class=\"slick-prev default\"><i class=\"ultsl-arrow-left6\"></i></button>',
						swipe:true,
						draggable:true,
						touchMove:true,
						slidesToScroll: parseInt(opts[9],10),
						slidesToShow: parseInt(opts[9],10),
						responsive:[{
							breakpoint: 1024,
							settings:{
								slidesToShow: parseInt(opts[10],10),
								slidesToScroll: parseInt(opts[10],10)
							}
						},{
							breakpoint: 768,
							settings:{
								slidesToShow: parseInt(opts[11],10),
								slidesToScroll: parseInt(opts[11],10)
							}
						},{
							breakpoint: 480,
							settings:{
								slidesToShow: parseInt(opts[12],10),
								slidesToScroll: parseInt(opts[12],10)
							}
						}],
						pauseOnHover:opts[7],
						pauseOnDotsHover:opts[7],
						customPaging:function(slider,i){
							return'<i type=\"button\" class=\"ultsl-record\" data-role=\"none\"></i>';
						}
					});
				});
			</script>";
		  }
	
	      wp_reset_postdata();

		  $londres_testimonials_index++;
	      return $output;
		}

	    public function londres_renderVerticalTabs( $atts, $content = null ) {
	      extract( shortcode_atts( array(
	        'title' => '',
	        'style_vt' => 'icon',
	        'orientation' => 'vertical'
	      ), $atts ) );
		  
		  global $londres_vertical_tabs;
			  $londres_vertical_tabs = vc_is_inline() ? uniqid() : $londres_vertical_tabs;
	     
	      $content = wpb_js_remove_wpautop($content, true); // fix unclosed/unwanted paragraph tags in $content
 
		  if ($orientation === 'horizontal'){
				$tabslayout = 'col-xs-12 col-sm-12';
		  } else {
				$tabslayout = ($style_vt === 'icon') ? 'col-xs-12 col-sm-1' : 'col-xs-12 col-sm-3';  
		  }
	  
		  if ($orientation === 'horizontal'){
				$contentlayout = 'col-xs-12 col-sm-12';
		  } else {
				$contentlayout = ($style_vt === 'icon') ? 'col-xs-12 col-sm-11' : 'col-xs-12 col-sm-9';
		  }
		
	      $output = "<section class='special_tabs {$style_vt} {$orientation}'>";
		  if ($title) $output .= "<h2>".esc_html($title)."</h2>";
		  $output .= "<div class='tab-selector {$tabslayout}'></div><div class='tab-container {$contentlayout}' style='margin-left:0px;'></div>";
		  $output .= "{$content}</section>";
  
		  $londres_vertical_tabs++;
	      return $output;
	    }

		public function londres_renderVerticalTab( $atts, $content = '[vc_row_inner][vc_column_inner][/vc_column_inner][/vc_row_inner]' ) {
	      extract( shortcode_atts( array(
	        'title' => 'Tab',
			'icon' => 'fa-adjust',
	        'tab_id' => ''
	      ), $atts ) );
	      $content = wpb_js_remove_wpautop($content, true); // fix unclosed/unwanted paragraph tags in $content
		  
		  global $londres_vt_index;
			  $londres_vt_index = vc_is_inline() ? uniqid() : $londres_vt_index;
	        
		  $output = "<div class='label {$londres_vt_index}'><div class='londres_icon_special_tabs'><i class='fa {$icon}'></i></div><div class='title'><a>{$title}</a></div><div class='divider-vertical-tabs'></div></div><div class='content {$londres_vt_index}'>{$content}</div>";

		  $londres_vt_index++;
	      return $output;
	    }
	} 
}

if (!function_exists('londres_vc_before_init')){
	function londres_vc_before_init(){

		if (function_exists('vc_add_shortcode_param')){
			vc_add_shortcode_param('team_cats', 'londres_team_categories_settings_field');
			vc_add_shortcode_param('partners_cats', 'londres_partners_categories_settings_field');
			vc_add_shortcode_param('testimonials_cats', 'londres_testimonials_categories_settings_field');
			vc_add_shortcode_param('londres_fa', 'londres_fa_settings_field');	
		}
		if (function_exists('vc_remove_element')){
			vc_remove_element('vc_carousel');
			vc_remove_element('vc_posts_slider');
			vc_remove_element('vc_gallery');
			vc_remove_element('vc_images_carousel');
			vc_remove_element('vc_button');
			vc_remove_element('vc_cta_button');
		}

	
		if ( class_exists( 'WPBakeryShortCodesContainer' ) && !class_exists('WPBakeryShortCode_Verticaltabs')) {
			class WPBakeryShortCode_des_info_list extends WPBakeryShortCodesContainer {}
		    class WPBakeryShortCode_Verticaltabs extends WPBakeryShortCodesContainer {
				static $filter_added = false;
				public function __construct( $settings ) {
					parent::__construct( $settings );
					if ( ! self::$filter_added ) {
						$this->addFilter( 'vc_inline_template_content', 'setCustomTabId' );
						self::$filter_added = true;
					}
				}
				public function contentAdmin( $atts, $content = null ) {
					if (!isset($output)) $output = "";
					$width = $custom_markup = '';
					$shortcode_attributes = array( 'width' => '1/1' );
					foreach ( $this->settings['params'] as $param ) {
						if ( $param['param_name'] != 'content' ) {
							if ( isset( $param['value'] ) && is_string( $param['value'] ) ) {
								$shortcode_attributes[$param['param_name']] = esc_html__( $param['value'], "londres" );
							} elseif ( isset( $param['value'] ) ) {
								$shortcode_attributes[$param['param_name']] = $param['value'];
							}
						} else if ( $param['param_name'] == 'content' && $content == NULL ) {
							$content = esc_html__( $param['value'], "londres" );
						}
					}
					extract( shortcode_atts(
						$shortcode_attributes
						, $atts ) );
					preg_match_all( '/verticaltab title="([^\"]+)"(\stab_id\=\"([^\"]+)\"){0,1}/i', $content, $matches, PREG_OFFSET_CAPTURE );			
					$tab_titles = array();
					if ( isset( $matches[0] ) ) {
						$tab_titles = $matches[0];
					}
					$tmp = '';
					if ( count( $tab_titles ) ) {
						$tmp .= '<ul class="clearfix tabs_controls">';
						foreach ( $tab_titles as $tab ) {
							preg_match( '/title="([^\"]+)"(\stab_id\=\"([^\"]+)\"){0,1}/i', $tab[0], $tab_matches, PREG_OFFSET_CAPTURE );
							if ( isset( $tab_matches[1][0] ) ) {
								$tmp .= '<li><a href="#tab-' . ( isset( $tab_matches[3][0] ) ? $tab_matches[3][0] : sanitize_title( $tab_matches[1][0] ) ) . '">' . $tab_matches[1][0] . '</a></li>';
	
							}
						}
						$tmp .= '</ul>' . "\n";
					} else {
						$output .= do_shortcode( $content );
					}
					$elem = $this->getElementHolder( $width );
					$iner = '';
					foreach ( $this->settings['params'] as $param ) {
						$custom_markup = '';
						$param_value = isset($param) && isset( $param['param_name'] ) ? $param['param_name'] : '';
						if ( is_array( $param_value ) ) {
							reset( $param_value );
							$first_key = key( $param_value );
							$param_value = $param_value[$first_key];
						}
						$iner .= $this->singleParamHtmlHolder( $param, $param_value );
					}
					if ( isset( $this->settings["custom_markup"] ) && $this->settings["custom_markup"] != '' ) {
						if ( $content != '' ) {
							$custom_markup = str_ireplace( "%content%", $tmp . $content, $this->settings["custom_markup"] );
						} else if ( $content == '' && isset( $this->settings["default_content_in_template"] ) && $this->settings["default_content_in_template"] != '' ) {
							$custom_markup = str_ireplace( "%content%", $this->settings["default_content_in_template"], $this->settings["custom_markup"] );
						} else {
							$custom_markup = str_ireplace( "%content%", '', $this->settings["custom_markup"] );
						}
						$iner .= do_shortcode( $custom_markup );
					}
					$elem = str_ireplace( '%wpb_element_content%', $iner, $elem );
					$output = $elem;
					return $output;
				}
				public function getTabTemplate() {
					return '<div class="wpb_template">' . do_shortcode( '[verticaltab title="" tab_id="" icon=""][/verticaltab]' ) . '</div>';
				}
				public function setCustomTabId( $content ) {
					return preg_replace( '/tab\_id\=\"([^\"]+)\"/', 'tab_id="$1-' . time() . '"', $content );
				}
	
		    }
		}
	
	
		if ( class_exists('WPBakeryShortCodesContainer') && !class_exists('WPBakeryShortCode_Verticaltab')){
			class WPBakeryShortCode_Verticaltab extends WPBakeryShortCodesContainer {
				protected $predefined_atts = array(
					'tab_id' => '',
					'icon' => 'fa-adjust',
					'title' => ''
				);
	
				public function __construct( $settings ) {
					parent::__construct( $settings );
				}
	
				public function customAdminBlockParams() {
					return ' id="tab-' . esc_attr($this->atts['tab_id']) . '"';
				}
	
				public function mainHtmlBlockParams( $width, $i ) {
					return 'data-element_type="' . esc_attr($this->settings["base"]) . '" class="wpb_' . esc_attr($this->settings['base']) . ' wpb_sortable wpb_content_holder"' . $this->customAdminBlockParams();
				}
	
				public function containerHtmlBlockParams( $width, $i ) {
					return 'class="wpb_column_container vc_container_for_children"';
				}
			}
		}
		
		if (class_exists('WPBakeryVisualComposerAbstract')){
			if (!function_exists('upper_vc_library_cat_list')){
				function upper_vc_library_cat_list() {
					return array( __('All','londres') => 'all', 
						__('About','londres') => 'about', 
						__('FAQ','londres') => 'faq',
						__('Counters','londres') => 'counters',  
						__('Contact & Maps','londres') => 'contactforms',
						__('General','londres') => 'general',  
						__('Portfolio','londres') => 'portfolio',
						__('Pricing','londres') => 'pricing',
						__('Services','londres') => 'services',
						__('Stylish Tabs','londres') => 'verticalstabsicon', 
						__('Testimonials','londres') => 'testimonials',
						__('Team','londres') => 'team');
				}
			}
		}
		
		
	}
}


add_action('vc_before_init', 'londres_vc_before_init');

if (!function_exists('londres_add_increase_time')){
	function londres_add_increase_time(){
		@ini_set( 'max_execution_time', '300' );
	}
}

add_filter('widget_text', 'do_shortcode');

if (!function_exists('upper_scrape_instagram')){
	function upper_scrape_instagram() {
		
		$title = get_option('londres_instagram_title', '');
		$limit = get_option('londres_instagram_limit', 9);
		$uid = get_option('londres_insta_uid', false);
		$target = get_option('londres_instagram_target', '_self');
		$access_token = get_option('londres_insta_token', false);
		
		if (!$access_token || !$uid){
			echo 'Please authorize Instagram on Appearance > Londres Options > Social Networks > Instagram and clicking on Authorize Instagram.';
			return;
		}
		
		$url = 'https://g' . 'raph.ins' . 'tag' . 'ram.com/' . $uid . '/media?fields=media_url,thumbnail_url,caption,id,media_type,timestamp,username,comments_count,like_count,permalink,children{media_url,id,media_type,timestamp,permalink,thumbnail_url}&limit=' . $limit . '&acce' . 'ss_to' . 'ken=' . $access_token;
		
		$args = array(
			'timeout' => 60,
			'sslverify' => false
		);
		$response = londres_remote_get( $url, $args, "_scrapper", $limit );

		if (isset($response['data'])){

			$posts = $response['data'];
			
			$ulclass = apply_filters( 'upper_insta_list_class', 'instagram-pics' );
			$liclass = apply_filters( 'upper_insta_item_class', '' );
			$aclass = apply_filters( 'upper_insta_a_class', '' );
			$imgclass = apply_filters( 'upper_insta_img_class', '' );
			$username = '';
			if (get_option('londres_enable_instagram_grayscale') == "on") $imgclass .= ' londres_grayscale ';
			echo '<ul class="'.esc_attr( $ulclass ).'">';
			foreach ($posts as $post){
				$caption = isset($post['caption']) ? $post['caption'] : '';
				$thumb = strtolower($post['media_type']) == "video" ? $post['thumbnail_url'] : $post['media_url'];
				echo '<li style="width:'. (100/$limit) .'%;" class="'. esc_attr( $liclass ) .'"><a href="'. esc_url( $post['permalink'] ) .'" target="'. esc_attr( $target ) .'"  class="'. esc_attr( $aclass ) .'"><img src="'. esc_url( $thumb ) .'"  alt="'. esc_attr( $caption ) .'" title="'. esc_attr( $caption ).'"  class="'. esc_attr( $imgclass ) .'"/></a></li>';
				$username = $post['username'];
			}
			echo '</ul>';
			
			$linkclass = apply_filters( 'upper_insta_link_class', 'clear' );
			if ( get_option('londres_instagram_link') != '' ) {
				?><p class="<?php echo esc_attr( $linkclass ); ?>"><a href="<?php echo trailingslashit( '//ins'.'tag'.'ram.com/' . $username ); ?>" rel="me" target="<?php echo esc_attr( get_option('londres_instagram_target') ); ?>"><?php echo esc_html( get_option('londres_instagram_link') ); ?></a></p><?php
			}
			
		} else {
			echo 'Something went wrong. Please re-authorize Instagram on Appearance > Londres Options > Social Networks > Instagram and clicking on Authorize Instagram.';
			return;
		}
	}
}

if (!function_exists('londres_remote_get')){
	function londres_remote_get($url = null, $args = array(), $culprit = "", $limit = 12) {
		if (!get_transient( 'londres_insta_transient'.$culprit )){
			$url = add_query_arg($args, trailingslashit($url));
			$response = londres_validate_response(wp_remote_get($url, array('timeout' => 29)));
			if (!isset($reponse['error'])) set_transient( 'londres_insta_transient'.$culprit, $response, 3600 );
		} else {
			$response = get_transient( 'londres_insta_transient'.$culprit );
			if (isset($response['data']) && count($response['data']) != $limit){
				$url = add_query_arg($args, trailingslashit($url));
				$response = londres_validate_response(wp_remote_get($url, array('timeout' => 29)));
				if (!isset($reponse['error'])) set_transient( 'londres_insta_transient'.$culprit, $response, 3600 );
			}
		}
		return $response;
	}
}

if (!function_exists('londres_validate_response')){
	function londres_validate_response($json = null) {
		if (!($response = json_decode(wp_remote_retrieve_body($json), true)) || 200 !== wp_remote_retrieve_response_code($json)) {
			if (isset($response['error'])) {
				$message = isset($response['message']) ? $response['message'] : $response['error'];
				return array(
					'error' => 1,
					'message' => $message
				);
			}
			if (is_wp_error($json)) {
				$response = array(
					'error' => 1,
					'message' => $json->get_error_message()
				);
			} else {
				$response = array(
					'error' => 1,
					'message' => esc_html__('Unknow error occurred, please try again', 'londres')
				);
			}
		}
		return $response;
	}
}

add_action( 'upper_insta_cron_hook', 'upper_insta_cron_exec' );
add_action( 'wp_login', 'upper_insta_cron_exec' );
if (!function_exists('upper_insta_cron_exec')){
	function upper_insta_cron_exec(){
		$at = get_option( 'londres_insta_token', false );
		if ($at != false){
			$args = array(
				'grant_type' => 'ig_refresh_token',
				'access_token' => $at
			);
			$url = "https://grap" . "h.ins" . "tagr" . "am.com/ref" . "resh_" . "acc" . "ess_t" . "oken";
			$url_with_args = add_query_arg( $args, trailingslashit($url) );
			$response = londres_validate_response( wp_remote_get( $url_with_args, array('timeout' => 29) ) );
			if (isset($response['access_token'])) update_option('londres_insta_token', $response['access_token']);
		}
	}
}

register_deactivation_hook( __FILE__, 'upper_cpt_deactivate' ); 
function upper_cpt_deactivate() {
	$timestamp = wp_next_scheduled( 'upper_insta_cron_hook' );
	if ($timestamp) wp_unschedule_event( $timestamp, 'upper_insta_cron_hook' );
}


?>