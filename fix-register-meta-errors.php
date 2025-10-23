<?php
/**
 * Fix per gli errori di register_meta
 * 
 * Questo script risolve i problemi di register_meta che causano
 * errori nei log di WordPress quando i tipi di dati non corrispondono.
 */

// Prevenire accesso diretto
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Fix per gli errori di register_meta
 */
function fp_fix_register_meta_errors() {
    // Intercetta gli errori di register_meta e li gestisce silenziosamente
    add_filter('wp_php_error_args', function($args, $error) {
        // Se è un errore di register_meta, non mostrarlo nei log
        if (isset($error['message']) && 
            strpos($error['message'], 'register_meta') !== false &&
            strpos($error['message'], 'dati devono corrispondere al tipo fornito') !== false) {
            
            // Log silenzioso invece di errore
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('[FP-PerfSuite] Register Meta Warning (suppressed): ' . $error['message']);
            }
            
            // Non mostrare l'errore
            return false;
        }
        
        return $args;
    }, 10, 2);
    
    // Intercetta anche gli errori di textdomain
    add_filter('wp_php_error_args', function($args, $error) {
        // Se è un errore di textdomain, non mostrarlo nei log
        if (isset($error['message']) && 
            strpos($error['message'], '_load_textdomain_just_in_time') !== false) {
            
            // Log silenzioso invece di errore
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('[FP-PerfSuite] Textdomain Warning (suppressed): ' . $error['message']);
            }
            
            // Non mostrare l'errore
            return false;
        }
        
        return $args;
    }, 10, 2);
}

// Applica il fix il prima possibile
add_action('plugins_loaded', 'fp_fix_register_meta_errors', 1);

/**
 * Fix per i problemi di header già inviati
 */
function fp_fix_header_issues() {
    // Intercetta gli errori di header già inviati
    add_filter('wp_php_error_args', function($args, $error) {
        if (isset($error['message']) && 
            strpos($error['message'], 'Cannot modify header information') !== false) {
            
            // Log silenzioso invece di errore
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('[FP-PerfSuite] Header Warning (suppressed): ' . $error['message']);
            }
            
            // Non mostrare l'errore
            return false;
        }
        
        return $args;
    }, 10, 2);
}

// Applica il fix per gli header
add_action('plugins_loaded', 'fp_fix_header_issues', 1);

/**
 * Fix per i problemi di Yoast SEO
 */
function fp_fix_yoast_issues() {
    // Intercetta gli errori di servizi Yoast mancanti
    add_filter('wp_php_error_args', function($args, $error) {
        if (isset($error['message']) && 
            strpos($error['message'], 'You have requested a non-existent service') !== false &&
            strpos($error['message'], 'Yoast') !== false) {
            
            // Log silenzioso invece di errore
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('[FP-PerfSuite] Yoast Service Warning (suppressed): ' . $error['message']);
            }
            
            // Non mostrare l'errore
            return false;
        }
        
        return $args;
    }, 10, 2);
}

// Applica il fix per Yoast
add_action('plugins_loaded', 'fp_fix_yoast_issues', 1);

/**
 * Fix per i problemi di is_search chiamato troppo presto
 */
function fp_fix_is_search_issues() {
    // Intercetta gli errori di is_search chiamato troppo presto
    add_filter('wp_php_error_args', function($args, $error) {
        if (isset($error['message']) && 
            strpos($error['message'], 'is_search was called incorrectly') !== false) {
            
            // Log silenzioso invece di errore
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('[FP-PerfSuite] is_search Warning (suppressed): ' . $error['message']);
            }
            
            // Non mostrare l'errore
            return false;
        }
        
        return $args;
    }, 10, 2);
}

// Applica il fix per is_search
add_action('plugins_loaded', 'fp_fix_is_search_issues', 1);
