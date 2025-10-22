<?php

namespace FP\PerfSuite\Services\Monitoring;

use FP\PerfSuite\Services\Assets\Optimizer;
use FP\PerfSuite\Services\Cache\Headers;
use FP\PerfSuite\Services\Cache\PageCache;
use FP\PerfSuite\Services\DB\Cleaner;

use function __;
use function update_option;

/**
 * Applicatore automatico di raccomandazioni per la performance
 * 
 * Permette di applicare con un click le raccomandazioni suggerite dall'analizzatore
 *
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */
class RecommendationApplicator
{
    private PageCache $pageCache;
    private Headers $headers;
    private Optimizer $optimizer;
    private Cleaner $cleaner;

    public function __construct(
        PageCache $pageCache,
        Headers $headers,
        Optimizer $optimizer,
        Cleaner $cleaner
    ) {
        $this->pageCache = $pageCache;
        $this->headers = $headers;
        $this->optimizer = $optimizer;
        $this->cleaner = $cleaner;
    }

    /**
     * Applica una raccomandazione specifica
     *
     * @param string $actionId ID dell'azione da applicare
     * @return array{success: bool, message: string}
     */
    public function apply(string $actionId): array
    {
        switch ($actionId) {
            case 'enable_page_cache':
                return $this->enablePageCache();
            
            case 'enable_browser_cache':
                return $this->enableBrowserCache();
            
            case 'enable_minify_html':
                return $this->enableMinifyHtml();
            
            case 'enable_defer_js':
                return $this->enableDeferJs();
            
            case 'remove_emojis':
                return $this->removeEmojis();
            
            case 'optimize_heartbeat':
                return $this->optimizeHeartbeat();
            
            case 'optimize_database':
                return $this->optimizeDatabase();
            
            default:
                return [
                    'success' => false,
                    'message' => __('Azione non riconosciuta', 'fp-performance-suite'),
                ];
        }
    }

    private function enablePageCache(): array
    {
        try {
            update_option('fp_ps_page_cache', '1');
            $this->pageCache->flush();
            
            return [
                'success' => true,
                'message' => __('✅ Cache delle pagine abilitata con successo! Il tuo sito sarà molto più veloce.', 'fp-performance-suite'),
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => sprintf(
                    __('Errore nell\'abilitazione della cache: %s', 'fp-performance-suite'),
                    $e->getMessage()
                ),
            ];
        }
    }

    private function enableBrowserCache(): array
    {
        try {
            update_option('fp_ps_cache_headers', '1');
            
            return [
                'success' => true,
                'message' => __('✅ Headers di cache del browser abilitati! I visitatori caricheranno il sito più velocemente nelle visite successive.', 'fp-performance-suite'),
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => sprintf(
                    __('Errore nell\'abilitazione degli headers di cache: %s', 'fp-performance-suite'),
                    $e->getMessage()
                ),
            ];
        }
    }

    private function enableMinifyHtml(): array
    {
        try {
            update_option('fp_ps_minify_html', '1');
            
            return [
                'success' => true,
                'message' => __('✅ Minificazione HTML attivata! Le tue pagine saranno più leggere del 10-20%.', 'fp-performance-suite'),
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => sprintf(
                    __('Errore nell\'attivazione della minificazione HTML: %s', 'fp-performance-suite'),
                    $e->getMessage()
                ),
            ];
        }
    }

    private function enableDeferJs(): array
    {
        try {
            update_option('fp_ps_defer_js', '1');
            
            return [
                'success' => true,
                'message' => __('✅ JavaScript differito attivato! Il contenuto apparirà più velocemente agli utenti.', 'fp-performance-suite'),
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => sprintf(
                    __('Errore nell\'attivazione del defer JS: %s', 'fp-performance-suite'),
                    $e->getMessage()
                ),
            ];
        }
    }

    private function removeEmojis(): array
    {
        try {
            update_option('fp_ps_remove_emojis', '1');
            update_option('fp_ps_remove_embeds', '1');
            
            return [
                'success' => true,
                'message' => __('✅ Script emoji e embeds rimossi! Eliminate richieste HTTP non necessarie.', 'fp-performance-suite'),
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => sprintf(
                    __('Errore nella rimozione degli script emoji: %s', 'fp-performance-suite'),
                    $e->getMessage()
                ),
            ];
        }
    }

    private function optimizeHeartbeat(): array
    {
        try {
            // Imposta heartbeat a 120 secondi (consigliato per hosting condiviso)
            update_option('fp_ps_heartbeat_admin', '120');
            update_option('fp_ps_heartbeat_editor', '120');
            update_option('fp_ps_heartbeat_frontend', '0'); // Disabilita sul frontend
            
            return [
                'success' => true,
                'message' => __('✅ Heartbeat API ottimizzata! Ridotto il consumo di risorse del server.', 'fp-performance-suite'),
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => sprintf(
                    __('Errore nell\'ottimizzazione del heartbeat: %s', 'fp-performance-suite'),
                    $e->getMessage()
                ),
            ];
        }
    }

    private function optimizeDatabase(): array
    {
        try {
            // Ottimizza solo le tabelle (operazione sicura)
            $result = $this->cleaner->optimizeTables();
            
            if ($result['success']) {
                return [
                    'success' => true,
                    'message' => sprintf(
                        __('✅ Database ottimizzato! Recuperati %.2f MB di spazio. Le query saranno più veloci.', 'fp-performance-suite'),
                        $result['freed_mb'] ?? 0
                    ),
                ];
            } else {
                return [
                    'success' => false,
                    'message' => __('Si è verificato un errore durante l\'ottimizzazione del database.', 'fp-performance-suite'),
                ];
            }
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => sprintf(
                    __('Errore nell\'ottimizzazione del database: %s', 'fp-performance-suite'),
                    $e->getMessage()
                ),
            ];
        }
    }
}

