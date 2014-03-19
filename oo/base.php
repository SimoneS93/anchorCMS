<?php

namespace data;

abstract class base {
    
    protected static function _filter_keyval(array $records, $key = '', $val = '') {
        if ($key && $val) {
            return array_filter($records, function($category) use ($key, $val) {
                return $category->$key == $val;
            });
        }
        
        return $records;
    }
    
    public static function groupby(array $records, $key) {
        foreach ($records as $record)
            $gr[$record->$key][] = $record;

        return $gr;
    }
}

?>
