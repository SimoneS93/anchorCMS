<?php
namespace ooanchor;

class model {
    private $hash;
    
    /**
     * Create a new instance of self
     * @param object or associative array $o
     * @return model
     */
    public static function make($o = NULL) {
        if ($o instanceof self)
            return $o;
        
        $model = new self();
        #objects created from Anchor store their data in a $data attribute
        if (is_object($o) || is_array($o)) {
            if (is_object($o) && isset($o->data))
                $o = $o->data;
                        
            foreach ($o as $key => $val)
                $model->setAttr($key, $val);
        }
        return $model;   
    }
    
    /**
     * Create a model and set its initial attributes
     * @param array $attributes
     */
    public function __construct(array $attributes = array()) {
        $this->hash = array();
        if (is_array($attributes))
            foreach ($attributes as $key => $val)
                $this->setAttr($key, $val);
    }
    
    /**
     * Check for key existence
     * @param string $key
     * @return boolean
     */
    public function exists($key) {
        return array_key_exists($key, $this->hash);
    }

    /**
     * Delete the keys passed as arguments
     * @param string $key
     * @param varargs $_ other keys to delete 
     */
    public function delete($key, $_ = NULL) {
        foreach (func_get_args() as $_key)
            unset ($this->hash[$_key]);
    }

    /**
     * Getter
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function getAttr($key, $default = '') {
        return $this->exists($key) && $this->hash[$key]? $this->hash[$key] : $default;
    }

    /**
     * Setter
     * @param string $key
     * @param mixed $val
     */
    public function setAttr($key, $val) {
        $this->hash[$key] = $val;
    }
    
    /**
     * Return the attributes as an array
     * @return array
     */
    public function toArray() {
        return $this->hash;
    }
    
    /** magic methods **/
    
    public function __get($key) {
        return $this->getAttr($key);
    }
    
    public function __set($key, $val) {
        $this->setAttr($key, $val);
    }
}

/**
 * A utility class to apply filters on records
 */
class filter {
    /**
     * Non-instantiable class
     */
    private function __construct() {}
    
    /**
     * Filter records on key-value pair (calls self::callback internally)
     * @param array $records
     * @param string $key
     * @param mixed $val
     */
    public static function keyval(array $records, $key = '', $val = NULL) {
        $filter_callback = NULL;
        #filter on key-value match
        if ($key && $val !== NULL)
            $filter_callback = function(model $record) use ($key, $val) {
                return $record->getAttr($key) === $val;
            };
        #filter on key existence
        else if ($key) {
            $filter_callback = function(model $record) use ($key) {
                return $record->exists($key);
            };
        }
        return self::callback($records, $filter_callback);
    }
    
    /**
     * Filter records on callback
     * @param array $records
     * @param callable $callback
     * @return array
     */
    public static function callback(array $records, $callback) {
        if (is_callable($callback))
            return array_filter($records, $callback);
        else return $records;
    }
}

abstract class base {    
    /**
     * Get only the first record
     * @param string $key
     * @param mixed $val
     * @return model
     */
    public static function first($key = '', $val = NULL) {
        $records = static::get($key, $val);
        return $records? array_shift($records) : NULL;
    }

    /**
     * Get records by key-value match, key existence or custom callback
     * @param string $key
     * @param mixed $value
     */
    public static function get($key = '', $val = NULL) {
        static::load();
        return is_callable($key)?
            filter::callback(static::records(), $key) :
            filter::keyval(static::records(), $key, (string)$val);
    }
    
    /**
     * Fetch the records
     */
    protected abstract static function load();
    /**
     * Get the fetched records - avoid clashes between classes records
     */
    protected abstract static function records();
}

class categories extends base {
    /**
     * Store the records
     * @var array
     */
    protected static $records = array();

    /**
     * Get the current category, if on a category page
     * @return NULL or a category model
     */
    public static function current() {
        if ($slug = static::is_category_page())
            return static::get('slug', $slug);
        return NULL;
    }

    /**
     * Check wheter the current page is a category page
     * @return FALSE or the category's slug
     */
    public static function is_category_page() {
        if (strpos(current_url(), 'category/') === 0)
            return array_pop(explode('/', current_url()));
        return FALSE;
    }

    protected static function load() {
        if (empty(static::$records)) {
            #fetch from database
            static::$records = \Category::listing();
            #turn into models
            foreach (static::$records as &$record) {
                $record = model::make($record);
                #extend
                $record->url = base_url('category/' . $record->slug);
            }
        }
    }

    protected static function records() {
        return static::$records;
    }    
}

/**
 * Defines a parent class for posts and pages
 */
abstract class page_post extends base {
    
    /**
     * Load post's or page's custom fields
     * @param model $model
     */
    protected static function extend(&$model, $type) {
        #add custom fields
        $extends = \Extend::fields($type);
        foreach ($extends as $extend) {
            $key = $extend->key;
            $field = \Extend::field($type, $key, $model->id);
            $model->$key = utf8_encode(\Extend::value($field, ''));
        }
    }
}

class posts extends page_post {
    /**
     * Store the records
     * @var array
     */
    protected static $records = array();
    
    protected static function load() {
        if (empty(static::$records)) {
            $posts_page = \Registry::get('posts_page');
            #fetch from database
            static::$records = array_pop(\Post::listing(null, 1, 999));
            #turn into models
            foreach (static::$records as &$post) {
                $post = model::make($post);
            }
            #extend
            foreach (static::$records as &$post) {
                static::extend($post, 'post');
                #parse markdwn
                $post->content = parse($post->html);
                #fetch category
                $category = categories::first('id', $post->category);
                $post->category_slug = $category->slug;
                #compute url
                $post->url = base_url($posts_page->slug . '/' . $post->slug);
                #free some memory
                $post->delete('html');
            } 
        }
    }

    protected static function records() {
        return static::$records;
    }    
}

class pages extends page_post {
    /**
     * Store the records
     * @var array
     */
    protected static $records = array();
    
    /**
     * Get the pages in menu
     * @param boolean $root wether return only top-level pages
     * @return array of models
     */
    public static function menu($root = FALSE) {
        $pages = static::get('show_in_menu', '1');
        #get only pages with no parent
        if ($root)
            $pages = filter::keyval($pages, 'parent', '0');
        return $pages;
    }

    protected static function load() {
        if (empty(static::$records)) {
            #fetch from database
            static::$records = \Page::listing(null, 1, 999);
            #turn into models
            foreach (static::$records as &$page) {
                $page = model::make($page);
            }
            #extend - need to be separated because static::get needs the models are made yet
            foreach (static::$records as &$page) {
                static::extend($page, 'page');
                #fetch parent page, if any
                $page->parent_slug = '';
                if ($page->parent) {
                    $parent = static::first('id', $page->parent);
                    if ($parent)
                        $page->parent_slug = $parent->slug;
                }
                #parse markdown content
                $page->content = parse($page->content);
                #compute url
                $page->url = $page->redirect? $page->redirect : base_url($page->slug);
            } 
        }
    }

    protected static function records() {
        return static::$records;
    }    
}
