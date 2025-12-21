<?php

namespace FP\PerfSuite\Services\Cache;

use FP\PerfSuite\Core\Logging\LoggerInterface;
use FP\PerfSuite\Core\Options\OptionsRepositoryInterface;
use FP\PerfSuite\Utils\Logger as StaticLogger;
use FP\PerfSuite\Utils\HostingDetector;
use FP\PerfSuite\Services\Cache\ObjectCacheManager\BackendDetector;
use FP\PerfSuite\Services\Cache\ObjectCacheManager\ConnectionTester;
use FP\PerfSuite\Services\Cache\ObjectCacheManager\DropInGenerator;
use FP\PerfSuite\Services\Cache\ObjectCacheManager\DropInManager;

/**
 * Object Cache Manager
 * 
 * Gestisce l'object caching con supporto per Redis, Memcached e APCu
 * 
 * @package FP\PerfSuite\Services\Cache
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */
class ObjectCacheManager
{
    private const OPTION_KEY = 'fp_ps_object_cache';
    
    private ?string $availableBackend = null;
    private BackendDetector $detector;
    private ConnectionTester $tester;
    private DropInGenerator $generator;
    private DropInManager $dropInManager;
    
    /** @var OptionsRepositoryInterface|null Options repository (injected) */
    private ?OptionsRepositoryInterface $optionsRepo = null;
    
    /** @var LoggerInterface|null Logger (injected) */
    private ?LoggerInterface $logger = null;
    
    public function __construct(?OptionsRepositoryInterface $optionsRepo = null, ?LoggerInterface $logger = null)
    {
        $this->optionsRepo = $optionsRepo;
        $this->logger = $logger;
        $this->detector = new BackendDetector();
        $this->tester = new ConnectionTester();
        $this->generator = new DropInGenerator();
        $this->dropInManager = new DropInManager($this->generator);
        
        $this->availableBackend = $this->detector->detectAvailableBackend();
    }
    
    /**
     * Helper per logging con fallback
     * 
     * @param string $level Log level
     * @param string $message Message
     * @param array $context Context
     */
    private function log(string $level, string $message, array $context = []): void
    {
        if ($this->logger !== null) {
            $this->logger->$level($message, $context);
        } else {
            StaticLogger::$level($message, $context);
        }
    }
    
    /**
     * Helper method per ottenere opzioni con fallback
     * 
     * @param string $key Option key
     * @param mixed $default Default value
     * @return mixed
     */
    private function getOption(string $key, $default = [])
    {
        if ($this->optionsRepo !== null) {
            return $this->optionsRepo->get($key, $default);
        }
        
        // Fallback to direct option call for backward compatibility
        return get_option($key, $default);
    }
    
    /**
     * Helper method per salvare opzioni con fallback
     * 
     * @param string $key Option key
     * @param mixed $value Value to save
     * @return bool
     */
    private function setOption(string $key, $value): bool
    {
        if ($this->optionsRepo !== null) {
            $this->optionsRepo->set($key, $value);
            return true;
        }
        
        // Fallback to direct option call for backward compatibility
        return update_option($key, $value, false);
    }
    
    /**
     * Registra il servizio
     */
    public function register(): void
    {
        add_action('admin_notices', [$this, 'showAdminNotices']);
    }
    
    // Metodo detectAvailableBackend() rimosso - ora gestito da BackendDetector
    
    private function detectAvailableBackend_OLD(): void
    {
        // Controlla Redis
        if (class_exists('Redis') || extension_loaded('redis')) {
            $this->availableBackend = 'redis';
            return;
        }
        
        // Controlla Memcached
        if (class_exists('Memcached') || extension_loaded('memcached')) {
            $this->availableBackend = 'memcached';
            return;
        }
        
        // Controlla APCu
        if (function_exists('apcu_fetch') && ini_get('apc.enabled')) {
            $this->availableBackend = 'apcu';
            return;
        }
        
        $this->availableBackend = null;
    }
    
    /**
     * Verifica se l'object cache è disponibile
     */
    public function isAvailable(): bool
    {
        return $this->availableBackend !== null;
    }
    
    /**
     * Verifica se l'object cache è attivo
     */
    public function isEnabled(): bool
    {
        $result = wp_using_ext_object_cache();
        return $result === true;
    }
    
    /**
     * Ottiene il backend disponibile
     */
    public function getAvailableBackend(): ?string
    {
        return $this->availableBackend;
    }
    
    /**
     * Ottiene informazioni sul backend attivo
     */
    public function getBackendInfo(): array
    {
        $backend = $this->availableBackend;
        $info = $this->detector->getBackendInfo($backend);
        
        if (!$backend) {
            return $info;
        }
        
        $info['enabled'] = $this->isEnabled();
        $info['status'] = $this->isEnabled() ? 'active' : 'available';
        
        // Aggiungi informazioni di connessione
        switch ($backend) {
            case 'redis':
                $info['connection'] = $this->tester->testRedisConnection();
                break;
                
            case 'memcached':
                $info['connection'] = $this->tester->testMemcachedConnection();
                break;
                
            case 'apcu':
                $info['connection'] = ['status' => 'ok'];
                break;
        }
        
        return $info;
    }
    
    // Metodo testRedisConnection() rimosso - ora gestito da ConnectionTester
    
    private function testRedisConnection_OLD(): array
    {
        try {
            $redis = new \Redis();
            
            $host = defined('WP_REDIS_HOST') ? WP_REDIS_HOST : '127.0.0.1';
            $port = defined('WP_REDIS_PORT') ? WP_REDIS_PORT : 6379;
            $timeout = defined('WP_REDIS_TIMEOUT') ? WP_REDIS_TIMEOUT : 1;
            
            if ($redis->connect($host, $port, $timeout)) {
                // Test autenticazione se configurata
                if (defined('WP_REDIS_PASSWORD') && WP_REDIS_PASSWORD) {
                    $redis->auth(WP_REDIS_PASSWORD);
                }
                
                // Test set/get
                $testKey = 'fp_perfsuite_test';
                $redis->set($testKey, 'test', 10);
                $result = $redis->get($testKey);
                $redis->del($testKey);
                
                return [
                    'status' => 'ok',
                    'host' => $host,
                    'port' => $port,
                    'info' => $redis->info('Server'),
                ];
            }
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage(),
            ];
        }
        
        return [
            'status' => 'error',
            'message' => 'Impossibile connettersi a Redis',
        ];
    }
    
    // Metodo testMemcachedConnection() rimosso - ora gestito da ConnectionTester
    
    private function testMemcachedConnection_OLD(): array
    {
        try {
            $memcached = new \Memcached();
            
            $host = defined('WP_CACHE_HOST') ? WP_CACHE_HOST : '127.0.0.1';
            $port = defined('WP_CACHE_PORT') ? WP_CACHE_PORT : 11211;
            
            $memcached->addServer($host, $port);
            
            // Test set/get
            $testKey = 'fp_perfsuite_test';
            $memcached->set($testKey, 'test', 10);
            $result = $memcached->get($testKey);
            $memcached->delete($testKey);
            
            if ($result === 'test') {
                return [
                    'status' => 'ok',
                    'host' => $host,
                    'port' => $port,
                    'version' => $memcached->getVersion(),
                ];
            }
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage(),
            ];
        }
        
        return [
            'status' => 'error',
            'message' => 'Impossibile connettersi a Memcached',
        ];
    }
    
    /**
     * Installa il drop-in per object cache
     */
    public function install(): array
    {
        if (!$this->isAvailable()) {
            return [
                'success' => false,
                'message' => 'Nessun backend di object caching disponibile sul server. È necessario installare Redis, Memcached o APCu.',
            ];
        }
        
        // Verifica permessi scrittura
        if (!is_writable(WP_CONTENT_DIR)) {
            return [
                'success' => false,
                'message' => 'La directory wp-content non è scrivibile.',
            ];
        }
        
        // CRITICAL: Verifica che esista un plugin di object cache dedicato
        // Il nostro drop-in non può implementare direttamente Redis/Memcached
        $hasPluginImplementation = $this->hasPluginImplementation();
        
        if (!$hasPluginImplementation) {
            $this->log('warning', 'No dedicated object cache plugin found', [
                'backend' => $this->availableBackend
            ]);
            
            return [
                'success' => false,
                'message' => sprintf(
                    'Per utilizzare %s è necessario installare un plugin dedicato come "Redis Object Cache" di Till Krüss. Il drop-in generato da FP Performance Suite non può gestire direttamente la connessione %s.',
                    strtoupper($this->availableBackend),
                    strtoupper($this->availableBackend)
                ),
            ];
        }
        
        // Genera il contenuto del drop-in in base al backend
        $dropInContent = $this->generateDropInContent($this->availableBackend);
        
        // Backup del vecchio file se esiste
        if (file_exists($this->dropInPath)) {
            $backupPath = $this->dropInPath . '.backup.' . time();
            copy($this->dropInPath, $backupPath);
        }
        
        // Scrivi il nuovo drop-in con file lock
        $result = $this->safeDropInWrite($this->dropInPath, $dropInContent);
        
        if ($result === false) {
            return [
                'success' => false,
                'message' => 'Impossibile scrivere il file object-cache.php (file locked)',
            ];
        }
        
        $this->log('info', 'Object cache enabled', ['backend' => $this->availableBackend]);
        
        return [
            'success' => true,
            'message' => sprintf('Object cache attivato con successo (%s).', strtoupper($this->availableBackend)),
            'backend' => $this->availableBackend,
        ];
    }

    /**
     * Scrive il drop-in in modo sicuro con file lock
     * 
     * @param string $filePath Path del file drop-in
     * @param string $content Contenuto da scrivere
     * @return bool True se scrittura riuscita
     */
    private function safeDropInWrite(string $filePath, string $content): bool
    {
        $lockFile = $filePath . '.lock';
        $lock = fopen($lockFile, 'c+');
        
        if (!$lock) {
            $this->log('error', 'Failed to create object cache lock file', ['file' => $lockFile]);
            return false;
        }
        
        // Acquire exclusive lock (non-blocking)
        if (!flock($lock, LOCK_EX | LOCK_NB)) {
            fclose($lock);
            $this->log('debug', 'Object cache drop-in locked by another process');
            return false; // Another process is writing
        }
        
        try {
            // Write drop-in file safely
            $result = file_put_contents($filePath, $content, LOCK_EX);
            
            if ($result === false) {
                $this->log('error', 'Failed to write object cache drop-in');
                return false;
            }
            
            $this->log('debug', 'Object cache drop-in written safely');
            return true;
        } finally {
            // Always release lock
            flock($lock, LOCK_UN);
            fclose($lock);
            @unlink($lockFile);
        }
    }
    
    /**
     * Verifica se esiste un'implementazione di plugin per object cache
     */
    private function hasPluginImplementation(): bool
    {
        // Redis Object Cache plugin
        if (file_exists(WP_PLUGIN_DIR . '/redis-cache/includes/object-cache.php')) {
            return true;
        }
        
        // Memcached drop-in
        if (file_exists(WP_CONTENT_DIR . '/object-cache-memcached.php')) {
            return true;
        }
        
        // Altri plugin comuni
        if (file_exists(WP_PLUGIN_DIR . '/memcached/object-cache.php')) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Disinstalla il drop-in
     */
    public function uninstall(): array
    {
        if (!file_exists($this->dropInPath)) {
            return [
                'success' => true,
                'message' => 'Object cache non è installato.',
            ];
        }
        
        // Verifica che sia il nostro drop-in
        $content = file_get_contents($this->dropInPath);
        // BUGFIX PHP 7.4 COMPATIBILITY: str_contains() è PHP 8.0+, usa strpos()
        if (strpos($content, 'FP Performance Suite') === false) {
            return [
                'success' => false,
                'message' => 'Il file object-cache.php non appartiene a FP Performance Suite.',
            ];
        }
        
        // Backup prima di rimuovere
        $backupPath = $this->dropInPath . '.removed.' . time();
        copy($this->dropInPath, $backupPath);
        
        if (unlink($this->dropInPath)) {
            wp_cache_flush();
            $this->log('info', 'Object cache disabled');
            
            return [
                'success' => true,
                'message' => 'Object cache disattivato con successo.',
            ];
        }
        
        return [
            'success' => false,
            'message' => 'Impossibile rimuovere il file object-cache.php',
        ];
    }
    
    /**
     * Genera il contenuto del drop-in
     */
    // Metodi generateDropInContent(), getBackendImplementation(), getRedisImplementation(), getMemcachedImplementation(), getApcuImplementation() rimossi - ora gestiti da DropInGenerator
    
    /**
     * Ottiene statistiche sull'object cache
     */
    public function getStatistics(): array
    {
        global $wp_object_cache;
        
        if (!$this->isEnabled()) {
            return [
                'enabled' => false,
                'hits' => 0,
                'misses' => 0,
                'ratio' => 0,
            ];
        }
        
        $stats = [
            'enabled' => true,
            'backend' => $this->availableBackend,
        ];
        
        // Statistiche da wp_object_cache
        if (isset($wp_object_cache->cache_hits)) {
            $stats['hits'] = $wp_object_cache->cache_hits;
        }
        
        if (isset($wp_object_cache->cache_misses)) {
            $stats['misses'] = $wp_object_cache->cache_misses;
        }
        
        if (isset($stats['hits']) && isset($stats['misses'])) {
            $total = $stats['hits'] + $stats['misses'];
            $stats['ratio'] = $total > 0 ? ($stats['hits'] / $total) * 100 : 0;
        }
        
        return $stats;
    }
    
    /**
     * Svuota la cache
     */
    public function flush(): bool
    {
        $result = wp_cache_flush();
        return $result === true;
    }
    
    /**
     * Mostra avvisi admin
     */
    public function showAdminNotices(): void
    {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        // Mostra notice solo una volta a settimana per non essere invasivi
        $notice_key = 'fp_ps_object_cache_notice_shown';
        if (get_transient($notice_key)) {
            return;
        }
        
        $isShared = HostingDetector::isSharedHosting();
        
        // Caso 1: Object cache disponibile ma non attivo
        if ($this->isAvailable() && !$this->isEnabled()) {
            $backendName = strtoupper($this->availableBackend);
            
            printf(
                '<div class="notice notice-success is-dismissible">
                    <p><strong>FP Performance Suite - Ottima Notizia!</strong></p>
                    <p>✅ <strong>%s</strong> è disponibile sul tuo server e può accelerare drasticamente il sito riducendo le query database.</p>
                    <p><a href="%s" class="button button-primary">Attiva Object Caching</a> <a href="https://developer.wordpress.org/reference/classes/wp_object_cache/" target="_blank" class="button">Scopri di più</a></p>
                </div>',
                esc_html($backendName),
                esc_url(admin_url('admin.php?page=fp-performance-suite&tab=database'))
            );
            
            set_transient($notice_key, true, WEEK_IN_SECONDS);
            
        } 
        // Caso 2: Object cache NON disponibile su shared hosting (normale)
        elseif (!$this->isAvailable() && $isShared) {
            printf(
                '<div class="notice notice-info is-dismissible">
                    <p><strong>FP Performance Suite - Info Hosting:</strong></p>
                    <p>ℹ️ Object Cache (Redis/Memcached/APCu) non disponibile su questo shared hosting.</p>
                    <p>Questo è <strong>normale</strong> per hosting condiviso. Il plugin utilizzerà ottimizzazioni alternative per massimizzare le performance.</p>
                    <p><small>💡 Per object caching considera un upgrade a VPS o hosting dedicato.</small></p>
                </div>'
            );
            
            set_transient($notice_key, true, WEEK_IN_SECONDS);
        }
        // Caso 3: Object cache NON disponibile su VPS/Dedicated (problema)
        elseif (!$this->isAvailable() && !$isShared) {
            printf(
                '<div class="notice notice-warning is-dismissible">
                    <p><strong>FP Performance Suite - Raccomandazione:</strong></p>
                    <p>⚠️ Object Cache non rilevato ma il tuo hosting sembra supportarlo (VPS/Dedicated).</p>
                    <p>Installa Redis, Memcached o APCu per prestazioni ottimali:</p>
                    <ul style="list-style: disc; margin-left: 20px;">
                        <li><strong>Redis</strong> (raccomandato): <code>sudo apt install redis php-redis</code></li>
                        <li><strong>Memcached</strong>: <code>sudo apt install memcached php-memcached</code></li>
                        <li><strong>APCu</strong>: <code>sudo apt install php-apcu</code></li>
                    </ul>
                    <p><a href="https://developer.wordpress.org/advanced-administration/performance/cache/" target="_blank" class="button">Guida Installazione</a></p>
                </div>'
            );
            
            set_transient($notice_key, true, WEEK_IN_SECONDS);
        }
    }
    
    /**
     * Ottiene le impostazioni
     */
    public function getSettings(): array
    {
        $defaults = [
            'enabled' => false,
            'auto_enable' => false,
        ];
        
        $options = $this->getOption(self::OPTION_KEY, []);
        return wp_parse_args($options, $defaults);
    }
    
    /**
     * Aggiorna le impostazioni
     */
    public function updateSettings(array $settings): bool
    {
        $current = $this->getSettings();
        $updated = wp_parse_args($settings, $current);
        
        $result = $this->setOption(self::OPTION_KEY, $updated);
        
        if ($result) {
            // FIX: Reinizializza il servizio per applicare immediatamente le modifiche
            $this->forceInit();
        }
        
        return $result;
    }
    
    /**
     * Forza l'inizializzazione del servizio
     * FIX: Ricarica le impostazioni e reinizializza il servizio
     */
    public function forceInit(): void
    {
        // Rimuovi hook esistenti
        remove_action('admin_notices', [$this, 'showAdminNotices']);
        
        // Reinizializza
        $this->register();
    }
}

