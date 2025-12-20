<?php

namespace FP\PerfSuite\Admin\Pages\AIConfig\Components;

use function json_encode;
use function sprintf;
use function implode;

/**
 * Genera comandi WP-CLI per applicare la configurazione
 * 
 * @package FP\PerfSuite\Admin\Pages\AIConfig\Components
 * @author Francesco Passeri
 */
class WPCLICommandsGenerator
{
    /**
     * Genera i comandi WP-CLI
     */
    public function generate(array $config): string
    {
        $commands = [];
        
        // Page Cache
        if (isset($config['page_cache'])) {
            $commands[] = sprintf(
                'wp option update fp_ps_page_cache \'%s\'',
                json_encode($config['page_cache'])
            );
        }
        
        
        // Heartbeat
        if (isset($config['heartbeat'])) {
            $commands[] = sprintf(
                'wp option update fp_ps_heartbeat_interval %d',
                $config['heartbeat']
            );
        }
        
        return implode("\n", $commands);
    }
}
















