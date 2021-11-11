<?php

function upper_cube_switch_jqver( $hook ) {
	if ($hook != "toplevel_page_cubeportfolio") return;
	wp_deregister_script( 'sitepress-scripts' );
	wp_deregister_script( 'installer-admin' );
}
add_action( 'admin_enqueue_scripts', 'upper_cube_switch_jqver' );

class CubePortfolioMain {
    // wordpress global db
    public $wpdb;

    // cubeportfolio projects table
    public static $table_cbp = 'cubeportfolio';

    // cubeportfolio items table
    public static $table_cbp_items = 'cubeportfolio_items';

    public $frontendStyle = '';

    public $googleFonts = array();

    public static $settings = array();

    private $loadAssets = false;

    private $request_from_ajax = false;

    function __construct($main_plugin_file) {
        global $wpdb;

        // store global db instance
        $this->wpdb = $wpdb;

        // create table name
        self::$table_cbp = $this->wpdb->prefix . self::$table_cbp;

        self::$table_cbp_items = $this->wpdb->prefix . self::$table_cbp_items;

        // flushing rewrite on activation
        register_activation_hook( $main_plugin_file, array( &$this, 'activate' ) );

        // add action for init
        add_action( 'init', array(&$this, 'init') );

        // add shortcode
        add_shortcode('cubeportfolio', array(&$this, 'add_shortcode'));

        // get options from database
        $this->getOptionsFromDB();

        // add support for visual composer
        add_action('vc_before_init', array(&$this, 'integrate_cbp_to_vc'));

        // popup request
        $data = stripslashes_deep( $_POST );
        if (isset($data['source']) && $data['source'] == 'cubeportfolio') {
            $this->request_from_ajax = true;
            $this->processFrontendPopup($data);
        }

        // custom posts type and taxonomy
        add_action( 'init', array( &$this, 'register_custom_post_type' ) );

        // add template to cubeportfolio post
        add_filter( 'template_include', array( &$this, 'include_template_function'), 1 );

        // internationalization
        add_action('plugins_loaded', array(&$this, 'add_i18n') );
    }

    private function getOptionsFromDB() {
        self::$settings = get_option('cubeportfolio_settings', array());
        
        if (!is_array(self::$settings)) self::$settings = array();

        // set preload option
        if (!isset(self::$settings['preload'])) {
            self::$settings['preload'] = array('onPostsPage', 'onHomePage');
        }

        // set cube posts `post type`
        if (!isset(self::$settings['postType'])) {
            self::$settings['postType'] = 'cubeportfolio';
        }

        // set flush_rewrite_rules setting
        if (!isset(self::$settings['flush_rewrite_rules'])) {
            self::$settings['flush_rewrite_rules'] = false;
        }
    }

    public function activate() {
        // Flush rewrite rules so that users can access custom post types on the front-end right away
        $this->register_custom_post_type();
        flush_rewrite_rules();
    }

    public function processFrontendPopup($data) {
        // get popup values for current cbp id
        $sql = $this->wpdb->prepare('SELECT popup FROM ' . CubePortfolioMain::$table_cbp . ' WHERE id = %d', $data['id']);
        $popup = json_decode($this->wpdb->get_var($sql));
        if (!$popup) return;

        $element = null;

        foreach ($popup as $item) {
            if ($item->link == $data['link'] && $item->type == $data['type']) {
                $element = $item;
            }
        }

        if ($element) {

            if ($element->html) {
                echo $element->html;
                die();
            }

            if ($data['selector'] == 'automatically') {
                add_filter('the_content', array(&$this, 'cbpw_ajax_content_filter'));
            }
        }
    }

    public function cbpw_ajax_content_filter($content) {
        return '<div class="cbpw-ajax-block">' . $content . '</div>';
    }


    /**
     * init main app
     */
    public function init () {

        // flush rewrite rules on demand. used when the cube post type is changed
        if (self::$settings['flush_rewrite_rules']) {
            flush_rewrite_rules();

            self::$settings['flush_rewrite_rules'] = false;
            update_option('cubeportfolio_settings', self::$settings);
        }

        if( is_admin() ) {
            // check if db exists
            $this->check_db();

            // load the backend
            require_once( 'CubePortfolioBackend.php' );
            $this->backend = new CubePortfolioBackend();
        } else { // is frontend

            // register assets
            $this->register_assets();

            // load scripts
            add_action('wp_enqueue_scripts', array(&$this, 'load_core_scripts'), 9999);

            // Include the Ajax library on the front end
            add_action( 'wp_head', array( &$this, 'add_ajax_library' ) );
        }
    }


    public function check_db() {
        $current_version = get_option('cubeportfolio_version', false);

        if ($current_version != CUBEPORTFOLIO_VERSION) {

            $charset_collate = ( ( !empty($this->wpdb->charset) )? ' DEFAULT CHARACTER SET ' . $this->wpdb->charset : '' ) .
                               ( ( !empty($this->wpdb->collate) )? ' COLLATE ' . $this->wpdb->collate : '');

            $sql = "CREATE TABLE IF NOT EXISTS " . self::$table_cbp . " (
                        id              INT(10)       UNSIGNED AUTO_INCREMENT NOT NULL,
                        active          TINYINT(1)    UNSIGNED NOT NULL DEFAULT 1,
                        name            VARCHAR(255)  NOT NULL,
                        type            VARCHAR(255)  NOT NULL,
                        customcss       TEXT          NOT NULL,
                        options         TEXT          NOT NULL,
                        loadMorehtml    TEXT,
                        template        TEXT,
                        filtershtml     TEXT,
                        googlefonts     TEXT,
                        popup           MEDIUMTEXT,
                        jsondata        MEDIUMTEXT,
                        PRIMARY KEY (id),
                        INDEX(active)
                    ){$charset_collate};";
            $this->wpdb->query($sql);

            $sql = "CREATE TABLE IF NOT EXISTS " . self::$table_cbp_items . " (
                        id                INT(10)       UNSIGNED AUTO_INCREMENT NOT NULL,
                        cubeportfolio_id  INT(10)       UNSIGNED NOT NULL,
                        sort              TINYINT(1)    UNSIGNED NOT NULL DEFAULT 1,
                        page              TINYINT(2)    UNSIGNED NOT NULL,
                        items             TEXT          NOT NULL,
                        isLoadMore        TEXT,
                        isSinglePage      TEXT,
                        PRIMARY KEY(id),
                        INDEX(cubeportfolio_id)
                    ){$charset_collate};";
            $this->wpdb->query($sql);

            // add popup field in old versions
            if ($current_version && version_compare($current_version, '1.2', '<')) {
                $sql = 'ALTER TABLE ' . self::$table_cbp . ' ADD popup TEXT';
                $this->wpdb->query($sql);

                $sql = 'ALTER TABLE ' . self::$table_cbp . ' DROP COLUMN mainjs';
                $this->wpdb->query($sql);
            }

            // change `cbp-l-loadMore-button-` to `cbp-l-loadMore-`
            if ($current_version && version_compare($current_version, '1.5', '<')) {
                // @deprecated
                require_once CUBEPORTFOLIO_PATH . 'php/deprecated/CubePortfolioVersion150.php';
                new CubePortfolioVersion150();
            }

            // add jsondata field in old versions
            if ($current_version && version_compare($current_version, '1.11.0', '<')) {
                $sql = 'ALTER TABLE ' . self::$table_cbp . ' ADD jsondata TEXT';
                $this->wpdb->query($sql);
            }

            // increase popup & jsondata capacity
            if ($current_version && version_compare($current_version, '1.11.2', '<')) {
                $sql = 'ALTER TABLE ' . self::$table_cbp . ' MODIFY popup MEDIUMTEXT';
                $this->wpdb->query($sql);

                $sql = 'ALTER TABLE ' . self::$table_cbp . ' MODIFY jsondata MEDIUMTEXT';
                $this->wpdb->query($sql);
            }

            // change from '.cbp-filter-counter:before' to '.cbp-filter-counter:after' in customcss
            if ($current_version && version_compare($current_version, '1.13.0', '<')) {
                // @deprecated
                require_once CUBEPORTFOLIO_PATH . 'php/deprecated/CubePortfolioVersion1130.php';
                new CubePortfolioVersion1130();
            }

            update_option('cubeportfolio_version', CUBEPORTFOLIO_VERSION);
        }
    }


    function add_shortcode($atts, $content = null) {
        $shortcode_atts = shortcode_atts(array(
            'id' => -1
        ), $atts);

        return $this->generate_cbp( (int)$shortcode_atts['id'] );
    }


    public function generate_cbp($id) {
        $db_data = $this->get_data_from_db($id);

        if ($db_data === NULL) {
            return CubePortfolioMain::frontend_error('Incorrect cubeportfolio ID in shortcode or problem with query. 1001');
        }

        // generate frontend html and scripts based on $db_data
        require_once('CubePortfolioFrontend.php');
        $portfolio = new CubePortfolioFrontend($db_data, $id);

        // add google fonts to googleFonts array
        $this->populateGoogleFonts($portfolio);

        return $portfolio->style . $portfolio->html . $portfolio->script;
    }

    // add more google fonts based on what came from db
    public function populateGoogleFonts($portfolio) {
        $fontsArray = $portfolio->googleFonts;

        foreach ($fontsArray as $font) {
            $add = true;

            foreach ($this->googleFonts as $localFont) {
                if ($localFont->name == $font->name && $localFont->weightStyle == $font->weightStyle) {
                    $add = false;
                    break;
                }
            }

            if ($add) {
                array_push($this->googleFonts, $font);
                $portfolio->style .= '<link rel="stylesheet" href="//fonts.googleapis.com/css?family=' . $font->slug . ':'. $font->weightStyle . '" type="text/css" media="all" property="stylesheet">';
            }
        }
    }


    public function get_data_from_db($id) {
        $table_cbp = self::$table_cbp;
        $table_cbp_items = self::$table_cbp_items;

        $sql = $this->wpdb->prepare("SELECT * FROM  $table_cbp WHERE id = %d", $id);
        $grid = $this->wpdb->get_row($sql, ARRAY_A);

        if ($grid === NULL) {
            return NULL;
        }

        $sql = $this->wpdb->prepare("SELECT * FROM  $table_cbp_items WHERE cubeportfolio_id = %d AND isLoadMore = 0 ORDER BY sort", $id);
        $grid['items'] = $this->wpdb->get_results($sql, ARRAY_A);

        return $grid;
    }


    public static function frontend_error($message) {
        return '<p><strong>' . $message . '</strong></p>';
    }

    public function integrate_cbp_to_vc() {
        vc_map( array(
            'name' => 'Cube Portfolio',
            'base' => 'cubeportfolio',
            'class' => '',
            'category' => 'Content',
            'description' => 'Responsive WordPress Grid Plugin',
            'params' => array(
                    array(
                        'type' => 'dropdown',
                        'heading' => 'Cube Portfolio',
                        'param_name' => 'id',
                        'value' => $this->get_cbp_items_for_vc(),
                        'admin_label' => true,
                        'description' => 'Select your Cube Portfolio'
                    )
            )
        ) );
    }

    public function get_cbp_items_for_vc() {
        // $cbp = self::$table_cbp;
        //         $result = array('Select a portfolio' => '-1');
        // 
        //         $sql = $this->wpdb->prepare("SELECT id, name FROM  $cbp WHERE active = %d", 1);
        //         $cbps = $this->wpdb->get_results($sql, OBJECT);

		$result = array('Select a portfolio' => '-1');
		global $wpdb, $table_prefix;
		$table_name = $table_prefix."cubeportfolio";
		if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
			// do nothing.
		} else {
			$sql = "SELECT id, name FROM ".$table_prefix."cubeportfolio WHERE active=1";
	        $cbps = $wpdb->get_results($sql,OBJECT);
        foreach($cbps as $cbp){
            $value = $cbp->id;
            $text = $cbp->name . ' (id=' . $value . ')';

            $result[$text] = $value;
        }
		}
        return $result;
    }


    public function load_core_scripts() {
        global $posts;
		
		if ( !is_array(self::$settings) ) self::$settings = array();

        if ( in_array('onAllPages', self::$settings['preload']) ) {
            $this->loadAssets = true;
        }

        if ( in_array('onHomePage', self::$settings['preload']) ) {
            if ( is_front_page() ) {
                $this->loadAssets = true;
            }
        }

        if ( in_array('onPostsPage', self::$settings['preload']) ) {

            // find shortcode in current post
            if (isset($posts) && !empty($posts)) {

                foreach($posts as $post) {
                    if ( preg_match_all("/cubeportfolio/s", $post ->post_content, $matches) ) {
                        $this->loadAssets = true;
                    }
                }

            }

        }

        if ($this->loadAssets) {
            $this->enqueue_assets();
        }
    }

    public function register_assets() {
        // css
        
         wp_register_style('cubeportfolio-jquery-css', CUBEPORTFOLIO_URL . 'public/css/main.min-1.13.2.css', false, CUBEPORTFOLIO_VERSION, 'all');

        // js
        
         wp_register_script('cubeportfolio-jquery-js', CUBEPORTFOLIO_URL . 'public/js/main.min-1.13.2.js', array('jquery'), CUBEPORTFOLIO_VERSION, true);
    }

    public function enqueue_assets() {
        // CUBEPORTFOLIO main plugin
        wp_enqueue_style('cubeportfolio-jquery-css');
        wp_enqueue_script('cubeportfolio-jquery-js');

        // visual composer workaround
        wp_enqueue_script('wpb_composer_front_js');
    }


    /**
     * Add the WordPress Ajax Library to the frontend.
     */
    public function add_ajax_library() {
        echo '<script type="text/javascript">if (typeof ajaxurl === "undefined") {var ajaxurl = "' . admin_url( 'admin-ajax.php' ) . '"}</script>';
    }

    /**
     * Register Custom Post Type & Taxonomy
     */
    public function register_custom_post_type() {
        $taxonomy = self::$settings['postType'] . '_category';

        $tax_args = array(
            'hierarchical' => true,
            'label' => 'Custom Categories',
            'singular_label' => 'Custom Categorie',
            'rewrite' => true,
            'public' => true,
            'show_admin_column' => true,
        );

        $args = array(
            'label' => 'Cube Posts',
            'singular_label' => 'Cube Post',
            'public' => true,
            'capability_type' => 'post',
            'hierarchical' => false,
            'show_ui' => true,
            'show_in_menu' => true,
            'supports' => array('title', 'editor', 'custom-fields'),
            'show_in_admin_bar' => false,
            'taxonomies' => array($taxonomy),
            'rewrite' => array('slug' => self::$settings['postType'], 'with_front' => true),
        );

        register_taxonomy($taxonomy, array(self::$settings['postType']), $tax_args);

        register_post_type(self::$settings['postType'], $args);
    }

    public function include_template_function($template_path) {
        if ( get_post_type() == self::$settings['postType'] && is_single()) {
            // trigger the slider only when you are not from ajax
            if (!$this->request_from_ajax) {
                // init standalone cbp post
                
                 wp_register_script('cubeportfolio-jquery-js-standalone', CUBEPORTFOLIO_URL . 'public/js/init-cbp-standalone.min-1.13.2.js', array('cubeportfolio-jquery-js'), CUBEPORTFOLIO_VERSION, true);

                // load assest for
                wp_enqueue_style('cubeportfolio-jquery-css');
                wp_enqueue_script('cubeportfolio-jquery-js');
                wp_enqueue_script('cubeportfolio-jquery-js-standalone');
            }

            $template = get_metadata( 'post', get_the_ID(), 'cbp_project_page_attr', true);

            // checks if the file exists in the theme first, otherwise serve the file from the plugin
            if ( $theme_file = locate_template( array ( $template . '.php' ) ) ) {
                $template_path = $theme_file;
            } else {
                $template_path = CUBEPORTFOLIO_PATH . 'public/partials/' . $template . '.php';
            }
        }

        return $template_path;
    }

    public function add_i18n() {
        load_plugin_textdomain(CUBEPORTFOLIO_TEXTDOMAIN, false, CUBEPORTFOLIO_DIRNAME . '/languages/');
    }

}
