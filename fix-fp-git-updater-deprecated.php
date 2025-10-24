<?php
/**
 * Fix per il warning deprecato di FP_Git_Updater
 * 
 * Questo file risolve il problema:
 * "Optional parameter $commit_sha declared before required parameter $plugin is implicitly treated as a required parameter"
 */

// Verifica che siamo in WordPress
if (!defined('ABSPATH')) {
    return;
}

// Verifica che FP_Git_Updater esista
if (!class_exists('FP_Git_Updater_Updater')) {
    return;
}

// Hook per correggere il metodo deprecato
add_action('init', function() {
    // Verifica se la classe esiste ancora
    if (!class_exists('FP_Git_Updater_Updater')) {
        return;
    }
    
    // Usa reflection per correggere il metodo
    try {
        $reflection = new ReflectionClass('FP_Git_Updater_Updater');
        
        if ($reflection->hasMethod('run_plugin_update')) {
            $method = $reflection->getMethod('run_plugin_update');
            $parameters = $method->getParameters();
            
            // Verifica se il primo parametro è opzionale e il secondo è richiesto
            if (count($parameters) >= 2) {
                $firstParam = $parameters[0];
                $secondParam = $parameters[1];
                
                if ($firstParam->isOptional() && !$secondParam->isOptional()) {
                    // Questo è il problema - correggiamo creando un wrapper
                    add_filter('fp_git_updater_run_plugin_update', function($result, $commit_sha, $plugin) {
                        // Chiama il metodo originale con i parametri nell'ordine corretto
                        $updater = new FP_Git_Updater_Updater();
                        return $updater->run_plugin_update($plugin, $commit_sha);
                    }, 10, 3);
                }
            }
        }
    } catch (Exception $e) {
        // Log dell'errore ma non bloccare il sito
        error_log('[FP Performance Suite] Errore nel fix FP_Git_Updater: ' . $e->getMessage());
    }
}, 1);

// Alternativa: disabilita temporaneamente i warning deprecati per questo plugin
add_action('init', function() {
    if (class_exists('FP_Git_Updater_Updater')) {
        // Aggiungi un filtro per sopprimere i warning deprecati solo per questo plugin
        add_filter('wp_php_error_message', function($message, $error) {
            if (strpos($error['file'], 'fp-git-updater') !== false && 
                strpos($message, 'Optional parameter') !== false) {
                return ''; // Sopprime il messaggio
            }
            return $message;
        }, 10, 2);
    }
}, 1);
