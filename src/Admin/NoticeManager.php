<?php

namespace FP\PerfSuite\Admin;

/**
 * Helper class per gestire admin notices in modo consistente
 * 
 * @package FP\PerfSuite\Admin
 * @author Francesco Passeri
 */
class NoticeManager
{
    /**
     * Aggiunge un admin notice
     * 
     * @param string $message Messaggio da mostrare
     * @param string $type Tipo di notice ('info', 'success', 'warning', 'error')
     * @param bool $dismissible Se il notice è dismissible
     * @return void
     */
    public static function add(string $message, string $type = 'info', bool $dismissible = true): void
    {
        if (!is_admin() || !current_user_can('manage_options')) {
            return;
        }
        
        add_action('admin_notices', function() use ($message, $type, $dismissible) {
            $dismissibleClass = $dismissible ? ' is-dismissible' : '';
            printf(
                '<div class="notice notice-%s%s">
                    <p><strong>FP Performance Suite:</strong> %s</p>
                </div>',
                esc_attr($type),
                $dismissibleClass,
                esc_html($message)
            );
        });
    }
    
    /**
     * Aggiunge un notice di tipo warning
     * 
     * @param string $message Messaggio
     * @return void
     */
    public static function warning(string $message): void
    {
        self::add($message, 'warning');
    }
    
    /**
     * Aggiunge un notice di tipo error
     * 
     * @param string $message Messaggio
     * @return void
     */
    public static function error(string $message): void
    {
        self::add($message, 'error');
    }
    
    /**
     * Aggiunge un notice di tipo success
     * 
     * @param string $message Messaggio
     * @return void
     */
    public static function success(string $message): void
    {
        self::add($message, 'success');
    }
    
    /**
     * Aggiunge un notice di tipo info
     * 
     * @param string $message Messaggio
     * @return void
     */
    public static function info(string $message): void
    {
        self::add($message, 'info');
    }
}
















