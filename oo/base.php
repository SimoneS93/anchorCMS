<?php

namespace data;

/**
 * Defines an abstract class for concrete classes with reusable methods
 */

abstract class base {
    
    /**
     * Filter based on a key-value pair
     * @param array $records
     * @param string $key
     * @param string $val
     * @return array 
     */
    protected static function _filter_keyval(array $records, $key = '', $val = '') {
        if ($key && $val) {
            return array_filter($records, function($record) use ($key, $val) {
                return $record->$key == $val;
            });
        }
        
        return $records;
    }
    
    /**
     * Filter based on a callback
     * @param array $records
     * @param callable or Closure $filter
     * @return array
     * @throws Exception
     */
    protected static function _filter_callback(array $records, $filter) {
        if (is_callable($filter))
            return array_filter ($records, $filter);
        else throw new Exception('filter must be callbale');
    }

    /**
     * Group records on key
     * @param array $records
     * @param string $key
     * @return array
     */
    public static function groupby(array $records, $key) {
        foreach ($records as $record)
            $gr[$record->$key][] = $record;

        return $gr;
    }
}

?>
