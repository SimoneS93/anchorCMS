<?php

namespace data;

class model {
    private $hash;
    
    
    public static function make($o = NULL) {
        if ($o instanceof self)
            return $o;
        
        $attr = new self();
        
        if (is_object($o) || is_array($o)) {
            if (is_object($o) && isset($o->data))
                $o = $o->data;
            
            foreach ($o as $key => $val)
                $attr->set ($key, $val);
        }
        return $attr;   
    }
    
    
    public function __construct(array $attributes = array()) {
        $this->hash = array();
        if (is_array($attributes))
            foreach ($attributes as $key => $val)
                $this->set($key, $val);
    }
    
    public function delete($key, $_ = NULL) {
        foreach (func_get_args() as $_key)
            unset ($this->hash[$_key]);
    }

    public function get($key, $default = '') {
        return array_key_exists($key, $this->hash)? $this->hash[$key] : $default;
    }
    
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
    
    public function reset() {
        foreach ($this->hash as $key => $val)
            $this->delete($key);
        return $this;
    }

    public function set($key, $val) {
        $this->hash[$key] = $val;
    }
    
    public function toArray() {
        return $this->hash;
    }
    
    public function __get($key) {
        return $this->get($key);
    }
    
    public function __set($key, $val) {
        $this->set($key, $val);
    }
}
?>
