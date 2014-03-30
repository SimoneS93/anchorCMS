<?php

namespace data;

/**
 * Let's you work with pages
 */

class pages extends base { 
    private static $records = array();

    /**
     * Get psts based on a callback filter
     * @param type $filter
     * @return type
     */
    public static function filter($filter) {
        self::load();
        return parent::_filter_callback(self::$records, $filter);
    }

    /**
     * Retrieve all pages or just one based on key-value pair
     * @param string $key
     * @param mixed $val
     * @return array of models or a single model
     */
    public static function get($key = '', $val = '') {
        self::load();
        return parent::_filter_keyval(self::$records, $key, $val);
    }

    /**
     * Load once all the pages
     */
    protected static function load() {
        if (!self::$records) {
            self::$records = \Page::listing(null, 1, 999);
            
            foreach (self::$records as &$page) {
                $page = model::make($page);
                self::extend($page);
            } 
        }
    }
    
    /**
     * Load page's custom fields
     * @param model $page
     */
    private static function extend(&$page) {
        #fetch parent page, if any
        $page->parent_slug = '';
        if ($page->parent) {
            $parent = array_shift(self::get('id', $page->parent));
            if ($parent)
                $page->parent_slug = $parent->slug;
        }
        #parse markdown content
        $page->content = parse($page->content);
        #compute url
	$page->url = base_url($page->slug);
        
        #add custom fields
        $extends = \Extend::fields('page');
        foreach ($extends as $extend) {
            $key = $extend->key;
            $field = \Extend::field('page', $key, $page->id);
            $page->$key = utf8_encode(\Extend::value($field, ''));
        }
    }
}

?>
