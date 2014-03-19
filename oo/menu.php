<?php

namespace data;

/**
 * Let's you work with menu items
 */

class menu {
    private static $records = array();
    
    /**
     * Get menu items based on a callback filter
     * @param type $filter
     * @return type
     */
    public static function filter($filter) {
        self::load();
        return parent::_filter_callback(self::$records, $filter);
    }
    
    /**
     * Retrieve all the menu items or just one based on key-value pair
     * @param string $key
     * @param mixed $val
     * @return array of models or a single model
     */
    public static function get($key = '', $val = '') {
        self::load();
        
        if (func_num_args() > 1) {
            list($key, $val) = func_get_args();
            return array_filter(self::$records, function($category) use ($key, $val) {
                return isset($category[$key]) && $category[$key] == $val;
            });
        }
        
        return self::$records;
    }
    
    /**
     * Load once all the menu items
     */
    private static function load() {
        if (!self::$records) {
            while (menu_items()) {
                self::$records[] = array(
                    'id' => menu_id(),
                    'name' => menu_name(),
                    'title' => menu_title(),
                    'active' => menu_active(),
                    'parent' => self::id(menu_parent())->get('title'),
                    'url' => menu_url()
                );
            }
        }
    }
    
    /**
     * Return a single menu item (page) by id
     * @param int $id
     * @return model
     */
    public static function id($id) {
        $page = \Page::id($id);
        return model::make($page);
    }
}

?>
