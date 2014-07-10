<?php

use System\Database\Query as Query;

function dump($o) {
    print '<pre>';
    var_dump($o);
    print '</pre>';
}

abstract class oo_base implements Countable, Iterator {
    //singleton
    protected static $instances;
    
    //Iterator interface
    protected $position;
    
    //database records
    protected $records;


    public function __construct() {
        $this->position = 0;
        $this->records = $this->all($this->tableName());
    }
    
    /**
     * Singleton
     * @return oo_base
     */
    public static function getInstance() {
        $id = get_called_class();
        if (!isset(static::$instances[$id]))
            static::$instances[$id] = new static();
        
        return static::$instances[$id];
    }

    /**
     * Get the 'current' object (page, post, category, user)
     */
    public abstract function target();

    /**
     * Get records based on filter
     * @param string|callback $key the key in a key-value pair or a filter callback
     * @param mixed $value the value in a key-value pair
     * @params bool $strict compare using '===' or '=='
     * @return array of stdClass
     */
    public function filter($key, $value = null, $strict = true) {
        if ($value !== null) {
            return $this->filter(function($record) use ($key, $value, $strict) {
                return $strict? $record->{$key} === $value : $record->{$key} == $value;
            });
        }
        else if (is_callable($key))
            return array_filter($this->records, $key);
        else return null;
    }
    
    /**
     * Get first record based on filter
     * @param string|callback $key the key in a key-value pair or a filter callback
     * @param mixed $value the value in a key-value pair
     * @params bool $strict compare using '===' or '=='
     * @return stcClass
     */
    public function first($key, $value = null, $strict = true) {
        if ($match = $this->filter($key, $value, $strict))
            return array_shift($match);
        else return null;
    }

    /**
     * Get record by ID
     * @param int $id
     * @return stdClass
     */
    public function id($id) {
        return $this->first('id', $id, false);
    }
    
    /**
     * Get record by slug
     * @param string $slug
     * @return stdClass
     */
    public function slug($slug) {
        return $this->first('slug', $slug);
    }
    
    /**
     * Sort records on key or by custom sort callback
     * @param string|callback $key the key to sort by or a sort callback
     * @return oo_base
     */
    public function sort($key) {
        if (!is_callable($key))
            $this->sort(function($a, $b) use ($key) {
                $a = $a->{$key};
                $b = $b->{$key};
                return is_numeric($a)? $a > $b : strcmp(strval($a), strval($b));
            });
        else
            usort($this->records, $key);
        
        return $this;
    }

    //Countable interface
    public function count() {
        return count($this->records);
    }

    //Iterator interface
    public function current() {
        return $this->records[$this->position];
    }
    public function key() {
        return $this->position;
    }
    public function next() {
        ++$this->position;
    }
    public function rewind() {
        $this->position = 0;
    }
    public function valid() {
        return isset($this->records[$this->position]);
    }
    
    /**
     * Get all table records
     * @param string $table
     * @return array of stdClass
     */
    protected function all($table) {
        return $this->table($table)->where('id', '>', 0)->get();
    }
    
    /**
     * Merge custom fields in object
     * @param stdClass &$object either a post or page object
     */
    protected function extend(&$object) {
        //type is singular of table name -> remove ending 's'
        $type = preg_replace('!s$!', '', $this->tableName());
        $fields = Extend::fields($type, $object->id);
        foreach ($fields as $field) {
            //custom field value is in $field->value->{$field->field}, if is set
            $v = $field->value;
            $f = $field->field;
            $object->{$field->key} = isset($v->$f)? $v->$f : '';
        }
    }

    /**
     * Get a Query object connected to a table
     * @param string $name
     * @return Query
     */
    protected function table($name) {
        $prefix = \System\Config::db('prefix', '');
        return Query::table($prefix . $name);
    }

    /**
     * Get table name from calling class
     * @return string
     */
    protected function tableName() {
        return str_replace('oo_', '', get_called_class());
    }
}

/**
 * Work with posts
 */
class oo_posts extends oo_base {
    
    public function __construct() {
        parent::__construct();
        
        foreach ($this->records as $post) {
            //add category and author objects
            $post->category = &oo_categories::getInstance()->id($post->category);
            $post->author = &oo_users::getInstance()->id($post->author);
            //parse content
            $post->content = parse($post->html);
            unset($post->html);
            //fetch custom fields
            $this->extend($post);
        }
    }

    public function target() {
        $posts_page = Registry::prop('posts_page', 'slug') . '/';
        $url = current_url();
        
        if (strpos($url, $posts_page) === 0) {
            $slug = str_replace($posts_page, '', $url);
            return $this->slug($slug);
        }
    }
    
}

/**
 * Work with pages
 */
class oo_pages extends oo_base {
    
    public function __construct() {
        parent::__construct();
        
        foreach ($this->records as $page) {
            //add parent page object
            $page->parent = &$this->id($page->parent);
            //parse cotnent
            $page->content = parse($page->content);
            //fetch custom fields
            $this->extend($page);
        }
    }

    public function target() {
        $url = current_url();
        
        if (strpos($url, '/') === false)
            return $this->slug($url);
    }
    
    /**
     * Check if on home page
     * @return bool
     */
    public function isHomepage() {
        $target = $this->target();
        return $target && $target->id == Config::meta('home_page');
    }
    
    /**
     * Check if on posts page
     * @return bool
     */
    public function isPostspage() {
        $target = $this->target();
        return $target && $target->id == Config::meta('posts_page');
    }
}

/**
 * Work with categories
 */
class oo_categories extends oo_base {
    
    public function target() {
        $url = current_url();
        
        if (strpos($url, 'category/') === 0) {
            $slug = str_replace('category/', '', $url);
            return $this->slug($slug);
        }
    }
}


/**
 * Work with users
 */
class oo_users extends oo_base {
    
    public function __construct() {
        parent::__construct();
        foreach ($this->records as $user) {
            //delete user's password
            unset($user->password);
        }
    }

    public function target() {}
}


//shortcuts to constructors
function A() { return oo_posts::getInstance(); }
function C() { return oo_categories::getInstance(); }
function P() { return oo_pages::getInstance(); }
function U() { return oo_users::getInstance(); }
