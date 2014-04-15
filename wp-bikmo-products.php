<?php

/* Plugin Name: Bikmo Products
  Plugin URI: http://bikmo.com
  Description: Ability to promote Bikmo products
  Version: 0.0.1
  Author: Gavin Rogers
 */

class WP_Bikmo_Products {
    /*
     * Constants
     */

    const VERSION = '0.0.1';
    const MEDIA_SERVER = 'http://media.bikmo.com/';

    /*
     * WPDB instance
     */

    private $_db;

    /*
     * Initiate the plugin
     */

    public function __construct() {

        global $wpdb;

        $this->_db = $wpdb;
        // activation hook
        register_activation_hook(__FILE__, array($this, 'activatePlugin'));
        //init frontend
        add_action('init', array($this, 'init'));
        //admin init
        add_action('admin_init', array($this, 'adminInit'));
        //init admin menu
        add_action('admin_menu', array($this, 'configureMenu'));
        //add settings link to plugin page
        add_filter("plugin_action_links_" . plugin_basename(__FILE__), array($this, 'pluginSettingsLink'));
    }

    /*
     * Called upon activation of plugin - create database tables
     */

    public function activatePlugin() {

        $productGroupSql = "CREATE TABLE IF NOT EXISTS " . $this->_db->prefix . "bikmo_product_groups"
                . " ("
                . "		id int(11) unsigned NOT NULL AUTO_INCREMENT,"
                . "		name varchar(255) DEFAULT NULL,"
                . "		PRIMARY KEY (id)"
                . "	) ENGINE=InnoDB DEFAULT CHARSET=utf8";

        $productSql = "CREATE TABLE IF NOT EXISTS " . $this->_db->prefix . "bikmo_products"
                . "	("
                . "		id int(11) unsigned NOT NULL AUTO_INCREMENT,"
                . "		bikmo_product_id int(11) DEFAULT NULL,"
                . "		group_id int(11) DEFAULT NULL,"
                . "		view_order int(11) DEFAULT NULL,"
                . "		PRIMARY KEY (id)"
                . "	) ENGINE=InnoDB DEFAULT CHARSET=utf8";

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

        foreach (array($productGroupSql, $productSql) as $sql) {
            //does some checks for column changes - alters table if detects new ones?
            dbDelta($sql);
        }
    }

    /*
     * Hook for frontend init
     */

    public function init() {

        if (is_admin()) {
            return false;
        }

        add_shortcode('bikmo_products', array($this, 'shortcodeAction'));
    }

    /*
     * 	Actions the bikmo_products shortcode action 
     */

    public function shortcodeAction($args) {

        if (empty($args['id'])) {
            return false;
        }

        $results = $this->_db->get_results(
                $this->_db->prepare('SELECT bikmo_product_id FROM ' . $this->_db->prefix . 'bikmo_products WHERE group_id = %d ORDER BY view_order ASC', array($args['id'], $limit)), ARRAY_A
        );

        if (empty($results)) {
            return false;
        }

        $ids = array();

        foreach ($results as $result) {
            $ids[] = $result['bikmo_product_id'];
        }

        require_once __DIR__ . '/classes/BikmoHttp.php';

        $http = new BikmoHttp;

        if (!$products = $http->request(array('ids' => $ids))) {
            return false;
        }

        $limit = !empty($args['limit']) && is_numeric($args['limit']) ? $args['limit'] : 4;

        ob_start();

        //allows the user to over-ride the shortcode template        
        foreach (array(get_template_directory(), __DIR__ . '/shortcodes') as $path) {
            $file = $path . '/bikmo-products.php';
            if (!file_exists($file)) {
                continue;
            }
            include $file;
            break;
        }

        return ob_get_clean();
    }

    /*
     * Hook for admin init
     */

    public function adminInit() {
        //register editable settings
        $this->initSettings();
        //add admin scripts
        add_action('admin_enqueue_scripts', array($this, 'adminPluginScripts'));
    }

    /*
     * Settings link on plugin page
     */

    public function pluginSettingsLink($links) {
        //make sure it's first
        array_unshift($links, '<a href="options-general.php?page=manage-bikmo-products">Settings</a>');
        return $links;
    }

    /*
     * Initialise settings for admin
     */

    public function initSettings() {
        register_setting('wp_plugin_bikmo-products', 'clickref');
        register_setting('wp_plugin_bikmo-products', 'site-url');
    }

    /*
     * Hook for admin menus
     */

    public function configureMenu() {
        add_menu_page('WP Bikmo Products', 'Bikmo Products', 'manage_options', 'manage-bikmo-products', array($this, 'pluginMenuPage'));
        add_submenu_page('manage-bikmo-products', 'Product Groups', 'Product Groups', 'manage_options', 'bikmo-product-groups', array($this, 'pluginGroupPage'));
        add_submenu_page(null, 'Add Product', 'Add Product', 'manage_options', 'add-bikmo-product', array($this, 'addProductPage'));
    }

    /*
     * Get's called on view of plugin page
     */

    public function pluginMenuPage() {

        if (!current_user_can('manage_options')) {
            wp_die('You do not have sufficient permissions to access this page.');
        }

        //include the settings.php file

        include_once(__DIR__ . '/pages/settings.php');
    }

    public function addProductPage() {

        $groupId = !empty($_GET['group-id']) && is_numeric($_GET['group-id']) ? $_GET['group-id'] : false;

        if ($groupId) {
            $group = $this->_db->get_row(
                    $this->_db->prepare('SELECT * FROM ' . $this->_db->prefix . 'bikmo_product_groups WHERE id = %d', $groupId), ARRAY_A
            );
        }

        include_once __DIR__ . '/pages/add-product.php';
    }

    /*
     * Gets called on view of add product group page
     */

    public function pluginGroupPage() {

        if (!current_user_can('manage_options')) {
            wp_die('You do not have sufficient permissions to access this page.');
        }

        if (!empty($_GET['action']) && !empty($_GET['id']) && empty($_GET['complete'])) {

            $id = is_numeric($_GET['id']) ? $_GET['id'] : false;
            $action = strtolower($_GET['action']);

            //editing the product group

            if ($action == 'edit') {

                //get the product group
                $group = $this->_db->get_row(
                        $this->_db->prepare('SELECT id, name FROM ' . $this->_db->prefix . 'bikmo_product_groups bgp WHERE bgp.id = %d', $id), ARRAY_A
                );


                if (!empty($group)) {


                    //sub action has been selected

                    if (!empty($_GET['sub-action']) && !empty($_GET['sub-id'])) {

                        $subAction = $_GET['sub-action'];

                        $productId = is_numeric($_GET['sub-id']) ? $_GET['sub-id'] : false;

                        //delete product from group 

                        if ($subAction == 'delete' && $productId) {

                            $row = $this->_db->get_row(
                                    $this->_db->prepare('SELECT * FROM ' . $this->_db->prefix . 'bikmo_products WHERE bikmo_product_id = %d', array($productId)), ARRAY_A
                            );

                            if ($row) {

                                // reduce the ones with a higher order by 1

                                $this->_db->query(
                                        $this->_db->prepare('UPDATE ' . $this->_db->prefix . 'bikmo_products SET view_order = view_order -1 WHERE group_id = %d AND view_order > %d', array($row['group_id'], $row['view_order']))
                                );

                                //delete it

                                $this->_db->delete($this->_db->prefix . 'bikmo_products', array(
                                    'bikmo_product_id' => $productId,
                                    'group_id' => $group['id']
                                ));
                            }
                        }
                    }

                    //post request to add more?

                    if (!empty($_POST['products'])) {
                        foreach ($_POST['products'] as $product) {

                            //don't add a duplicate

                            $check = $this->_db->get_var(
                                    $this->_db->prepare('SELECT COUNT(id) FROM ' . $this->_db->prefix . 'bikmo_products WHERE bikmo_product_id = %d AND group_id = %d', array($product, $group['id']))
                            );
                            if (!$check) {

                                $maxOrder = $this->_db->get_var(
                                        $this->_db->prepare('SELECT MAX(view_order) FROM ' . $this->_db->prefix . 'bikmo_products WHERE group_id = %d', array($group['id']))
                                );

                                $this->_db->insert($this->_db->prefix . 'bikmo_products', array(
                                    'bikmo_product_id' => $product,
                                    'group_id' => $group['id'],
                                    'view_order' => $maxOrder ? $maxOrder + 1 : 1
                                ));
                            }
                        }
                    }

                    //get products

                    $productResults = $this->_db->get_results(
                            $this->_db->prepare('SELECT bikmo_product_id FROM ' . $this->_db->prefix . 'bikmo_products bp WHERE bp.group_id = %d', $id), ARRAY_A
                    );

                    $products = array();

                    if (!empty($productResults)) {


                        foreach ($productResults as $result) {
                            $ids[] = $result['bikmo_product_id'];
                        }

                        //require and instantiate the class
                        require_once __DIR__ . '/classes/BikmoHttp.php';

                        $http = new BikmoHttp;

                        if ($results = $http->request(array('ids' => $ids))) {
                            $products = $results;
                        }
                    }

                    //include singular view file

                    include_once(__DIR__ . '/pages/product-group.php');

                    //no need to include the rest

                    return;
                }
            }
        }

        //deleting the product group

        if ($action == 'delete' && $id) {

            //if deleted a group then delete all its products

            if ($this->_db->delete($this->_db->prefix . 'bikmo_product_groups', array('id' => $id))) {
                $this->_db->delete($this->_db->prefix . 'bikmo_products', array(
                    'group_id' => $id
                ));
            }
        }

        //insert new group

        if (!empty($_POST['name'])) {
            $this->_db->insert($this->_db->prefix . 'bikmo_product_groups', array(
                'name' => $_POST['name']
            ));
        }


        $limit = 15;

        $count = $this->_db->get_var('SELECT count(id) FROM ' . $this->_db->prefix . 'bikmo_product_groups');

        $pageNum = !empty($_GET['pagenum']) ? $_GET['pagenum'] : 1;

        //get product groups

        $productGroups = $this->_db->get_results('SELECT'
                . ' bpg.id, bpg.name, COUNT(bp.id) as product_count'
                . '		FROM ' . $this->_db->prefix . 'bikmo_product_groups bpg'
                . '			LEFT JOIN ' . $this->_db->prefix . 'bikmo_products bp ON bpg.id = bp.group_id'
                . '	GROUP BY bpg.id'
                . ' ORDER BY bpg.id DESC'
                . '	LIMIT ' . (($pageNum - 1) * $limit) . ',' . $limit, ARRAY_A);

        //include view file

        include_once(__DIR__ . '/pages/product-groups.php');
    }

    /*
     * List of scripts need
     */

    public function adminPluginScripts($hook) {
        $check = strtolower($hook);

        if ($check == 'admin_page_add-bikmo-product') {
            wp_enqueue_script('product-search', plugin_dir_url(__FILE__) . 'js/product-search.js', array('jquery'), self::VERSION, true);
        }

        if (strpos($check, 'bikmo') !== false) {
            wp_enqueue_style('bikmo-admin', plugin_dir_url(__FILE__) . 'css/admin-bikmo.css', array(), self::VERSION);
        }
    }

    /*
     * Convenience method for calculating discount
     */

    public static function discount($price, $rrp) {
        $discount = 100 - (($price / $rrp) * 100);
        return round($discount);
    }

    /*
     * Convenience function for adding clickref to links
     */

    public static function deepLink($productUrl) {
        return (get_option('site-url') ? rtrim(get_option('site-url'), '/') : 'http://search.bikmo.com') . '/buy?' . http_build_query(array('link' => $productUrl));
    }

    /*
     * Convenience function for linking to rcuk product page
     */

    public static function linkify($manufacturer, $name) {
        $siteUrl = get_option('site-url') ? get_option('site-url') : 'http://search.bikmo.com';
        return $siteUrl . '/' . self::link($manufacturer) . '/' . self::link($name);
    }

    /*
     * Strips out html and any non alpha-numeric chars
     */

    private static function link($string) {
        return preg_replace(array('/\<[^\>]+\>/', '/([^\w_]+|[\-]{2})/'), array('', '-'), strtolower(trim($string)));
    }

    public static function image($url, $options = 'med') {

        if (!is_array($options)) {
            switch ($options) {
                case 'original':
                    $options = array();
                    break;
                case 'large':
                    $options = array('w' => 1024);
                    break;
                case 'med':
                    $options = array('w' => 600);
                    break;
                case 'small':
                    $options = array('w' => 280);
                    break;
                case 'thumb':
                    $options = array('w' => 150);
                    break;
            }
        }

        //merge defaults
        $filters = array_merge(array(
            'extension' => 'jpg',
            'mode' => 'fit'
                ), $options
        );

        if ($url) {
            $name = pathinfo($source, PATHINFO_FILENAME);
            $name .= '-' . substr(md5($source), 0, 4);
        } else {
            $name = 'placeholder';
        }

        $target = preg_replace("/[^a-zA-Z0-9-]+/", "", $name);
        $target .=!empty($filters['w']) ? '_w' . $filters['w'] : '';
        $target .=!empty($filters['h']) ? '_h' . $filters['h'] : '';
        $target .=!empty($filters['mode']) ? '_' . $filters['mode'] : '';
        $target .= '.' . $filters['extension'];

        $params = array();
        if (!empty($filters['force']) && $filters['force']) {
            $params['force'] = true;
        }
        $params['source'] = $url;

        return $url = self::MEDIA_SERVER . urlencode($target) . '?' . http_build_query($params);
    }

}

/*
 * Initialise the plugin
 */
$bikmoProducts = new WP_Bikmo_Products();
