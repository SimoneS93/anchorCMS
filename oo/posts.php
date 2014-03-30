<?php

namespace data;

/**
 * Let's you work with posts
 */

class posts extends base { 
    private static $records;

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
     * Retrieve all posts or just one based on key-value pair
     * @param string $key
     * @param mixed $val
     * @return array of models or a single model
     */
    public static function get($key = '', $val = '') {
        self::load();
        return parent::_filter_keyval(self::$records, $key, $val);
    }

    /**
     * Load once all the posts
     */
    protected static function load() {
        if (!self::$records) {
	    #fetch from database
            $posts = array_pop(\Post::listing(null, 1, 999));
            #make model from each
            foreach ($posts as $post) {
                $post = model::make($post);
                self::extend($post);
                $post->delete('html');
                #push
                self::$records[] = $post;
            } 
        } 
    }
    
    /**
     * Load post's custom fields
     * @param model $post
     */
    private static function extend(&$post) {
        #parse markdwn
        $post->content = parse($post->html);
        #fetch category
        $category = array_shift(categories::get('id', $post->category));
        $post->category_slug = $category->slug;
        #compute url
        $page = \Registry::get('posts_page');
	$post->url = base_url($page->slug . '/' . $post->slug);
        #add custom fields
        $extends = \Extend::fields('post');
        foreach ($extends as $extend) {
            $key = $extend->key;
            $field = \Extend::field('post', $key, $post->id);
            $post->$key = utf8_encode(\Extend::value($field, ''));
        }
    }
}

?>
