<?php
/**
 * Simple Falang Translation Helper
 * Add this to your theme's functions.php file
 * usage: theme_translate('Your String', 'unique_key_for_string');
 */

class SimpleTranslate {
    private static $context = 'apktemplates';
    
    /**
     * Translate a string with automatic registration
     * 
     * @param string $string The string to translate
     * @param string $key Unique key for the string
     * @return string Translated string
     */
    public static function translate($string, $key) {
        // Register the string
        if (function_exists('icl_register_string')) {
            icl_register_string(self::$context, $key, $string);
        }
        
        // Return translated version
        if (function_exists('falang__')) {
            return falang__($string);
        }
        
        // Fallback
        return $string;
    }
}

/**
 * Convenient wrapper function that ECHOES the translation
 * 
 * @param string $string The string to translate
 * @param string $key Unique key for the string
 */
function theme_translate($string, $key) {
    echo SimpleTranslate::translate($string, $key);
}

/**
 * Convenient wrapper function that RETURNS the translation
 * Use this when you need to store the translation in a variable
 * 
 * @param string $string The string to translate
 * @param string $key Unique key for the string
 * @return string Translated string
 */
function theme_get_translate($string, $key) {
    return SimpleTranslate::translate($string, $key);
}
?>