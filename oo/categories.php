<?php

namespace data;

/**
 * Let's you work with categories 
 */

class categories extends base {
    private static $records;
    
    /**
     * Get the current category if is set
     * @return model
     */
    public static function current() {
        if ($slug = self::is_category())
            return array_pop(self::get('slug', $slug));
    }
    
    /**
     * Get categories based on a callback filter
     * @param type $filter
     * @return type
     */
    public static function filter($filter) {
        self::load();
        return parent::_filter_callback(self::$records, $filter);
    }

    /**
     * Retrieve all the categories or just one based on key-value pair
     * @param string $key
     * @param mixed $val
     * @return array of models or a single model
     */
    public static function get($key = '', $val = '') {
        self::load();
        return parent::_filter_keyval(self::$records, $key, $val);
    }
    
    /**
     * Check is the current page is a category page: if it is, returns its slug
     * @return FALSE or category's slug
     */
    public static function is_category() {
        if (strpos(current_url(), 'category/') === 0)
            return array_pop(explode('/', current_url()));
        return FALSE;
    }

    /**
     * Load once all the categories
     */
    protected static function load() {
        if (!self::$records) {
            categories();
            $categories = \Registry::get('categories');
            
            self::$records = array_map(function($c) {
                $c = model::make($c);
                $c->set('class', $c->get('slug'));
                $c->set('url', base_url('category/' . $c->get('slug')));
                
                return $c;
                
            }, $categories->toArray());
            
            self::$records = array_filter(self::$records, function($c) { 
                return $c->get('slug') !== 'uncategorised';
            });
        }
    }
}

?>
