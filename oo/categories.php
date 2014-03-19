<?php

namespace data;

class categories extends base {
    private static $records;
    
    public static function current() {
        if ($slug = self::is_category())
            return array_pop(self::get('slug', $slug));
    }

    public static function get($key = '', $val = '') {
        self::load();
        return parent::_filter_keyval(self::$records, $key, $val);
    }
    
    public static function is_category() {
        if (strpos(current_url(), 'category/') === 0)
            return array_pop(explode('/', current_url()));
        return FALSE;
    }

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
