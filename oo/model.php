<?php

namespace data;

class model {
    private $hash;
    
    /**
     * Create a new instance of self
     * @param type $o
     * @return model
     */
    public static function make($o = NULL) {
        if ($o instanceof \html\attributes)
            return $o;
        
        $attr = new \html\attributes();
        
        if (is_object($o) || is_array($o)) {
            if (is_object($o) && isset($o->data))
                $o = $o->data;
            
            foreach ($o as $key => $val)
                $attr->set ($key, $val);
        }
        return $attr;   
    }
    
    /**
     * Create a model and set its initial attributes
     * @param array $attributes
     */
    public function __construct(array $attributes = array()) {
        $this->hash = array();
        if (is_array($attributes))
            foreach ($attributes as $key => $val)
                $this->set($key, $val);
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
    public function get($key, $default = '') {
        return array_key_exists($key, $this->hash)? $this->hash[$key] : $default;
    }
    
    /**
     * Set a bunch of attributes at once or returns a new model with a merge 
     * of the current one and those provided (used mainly for backup purposes)
     * @param array $attributes
     * @param boolean $temp wheter it should merge into the current one
     * @return model
     */
    public function merge(array $attributes, $temp = FALSE) {
        if ($temp) {
            $merged = array();
            foreach (array_merge($this->hash, $attributes) as $key => $value)
                $merged[$key] = $value;

            return new self($merged);
        }
        
        foreach ($attributes as $key => $val)
            $this->set($key, $val);
        
        return $this;
    }
    
    /**
     * Delete all the attributes
     * @return model
     */
    public function reset() {
        foreach ($this->hash as $key => $val)
            $this->delete($key);
        return $this;
    }

    /**
     * Setter
     * @param string $key
     * @param mixed $val
     */
    public function set($key, $val) {
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
        return $this->get($key);
    }
    
    public function __set($key, $val) {
        $this->set($key, $val);
    }
}
?>
