<?php
/**
 * Form Helper Class
 * Utilities for handling form data
 */

class FormHelper {
    /**
     * Convert checkbox value to proper boolean/integer
     * 
     * @param mixed $value The checkbox value from form
     * @return int 1 if checked, 0 if not
     */
    public static function checkboxToInt($value) {
        if ($value === '1' || $value === 1 || $value === true || $value === 'on') {
            return 1;
        }
        return 0;
    }
    
    /**
     * Sanitize and validate common form fields
     * 
     * @param array $data Form data
     * @param array $booleanFields List of fields that are checkboxes
     * @return array Sanitized data
     */
    public static function sanitizeFormData($data, $booleanFields = []) {
        foreach ($booleanFields as $field) {
            if (isset($data[$field])) {
                $data[$field] = self::checkboxToInt($data[$field]);
            } else {
                $data[$field] = 0; // Unchecked checkboxes don't send any value
            }
        }
        
        return $data;
    }
    
    /**
     * Convert empty strings to null for database
     * 
     * @param mixed $value The value to check
     * @return mixed Null if empty string, original value otherwise
     */
    public static function emptyToNull($value) {
        return ($value === '' || $value === null) ? null : $value;
    }
    
    /**
     * Handle numeric field - convert empty to null, validate number
     * 
     * @param mixed $value The value to process
     * @return float|null Null if empty, float if valid number
     */
    public static function sanitizeNumeric($value) {
        if ($value === '' || $value === null) {
            return null;
        }
        return floatval($value);
    }
    
    /**
     * Sanitize all form data at once
     * 
     * @param array $data The form data
     * @param array $config Configuration array with field types
     * @return array Sanitized data
     */
    public static function sanitize($data, $config) {
        $sanitized = $data;
        
        foreach ($config as $field => $type) {
            if (!isset($sanitized[$field])) {
                continue;
            }
            
            switch ($type) {
                case 'boolean':
                    $sanitized[$field] = self::checkboxToInt($sanitized[$field]);
                    break;
                case 'numeric':
                    $sanitized[$field] = self::sanitizeNumeric($sanitized[$field]);
                    break;
                case 'nullable':
                    $sanitized[$field] = self::emptyToNull($sanitized[$field]);
                    break;
            }
        }
        
        return $sanitized;
    }
}

