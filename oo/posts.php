<?php

namespace data;

class posts extends base { 
    private static $records;

    public static function get($key = '', $val = '') {
        self::load();
        return parent::_filter_keyval(self::$records, $key, $val);
    }

    protected static function load() {
        if (!self::$records) {
            $posts = array_pop(\Post::listing(null, 1, 999));
            
            foreach ($posts as $post) {
                $post = new \html\attributes($post->data);
                
                self::extend($post);
                $post->delete('html');
                
                self::$records[] = $post;
            } 
        } 
    }
    
    private static function extend(&$post) {
        $post->content = parse($post->html);
        $page = \Registry::get('posts_page');
	      $post->url = base_url($page->slug . '/' . $post->slug);
        
        //add custom fields
        $extends = \Extend::fields('post');
        foreach ($extends as $extend) {
            $key = $extend->key;
            $field = \Extend::field('post', $key, $post->id);
            $post->$key = utf8_encode(\Extend::value($field, ''));
        }
    }
}

?>
