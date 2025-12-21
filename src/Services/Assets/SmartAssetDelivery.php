<?php

namespace FP\PerfSuite\Services\Assets;

use FP\PerfSuite\Core\Options\OptionsRepositoryInterface;
use FP\PerfSuite\Utils\Logger;

/**
 * Smart Asset Delivery
 * 
 * Consegna asset ottimizzati basandosi su connessione e dispositivo
 *
 * @package FP\PerfSuite\Services\Assets
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */
class SmartAssetDelivery
{
    private const OPTION_KEY = 'fp_ps_smart_delivery';
    
    /**
     * @var OptionsRepositoryInterface|null
     */
    private $optionsRepo;

    /**
     * Constructor
     * 
     * @param OptionsRepositoryInterface|null $optionsRepo Options repository instance
     */
    public function __construct(?OptionsRepositoryInterface $optionsRepo = null)
    {
        $this->optionsRepo = $optionsRepo;
    }

    /**
     * Registra il servizio
     */
    public function register(): void
    {
        $settings = $this->getSettings();

        if (empty($settings['enabled'])) {
            return;
        }

        // Adatta immagini per connessione
        if (!empty($settings['adaptive_images'])) {
            add_filter('wp_get_attachment_image_src', [$this, 'adaptImageQuality'], 10, 4);
        }

        // Rileva e salva info connessione - solo nel frontend
        if (!is_admin()) {
            add_action('wp_footer', [$this, 'detectConnectionType'], 35);
        }

        Logger::debug('Smart Asset Delivery registered');
    }

    /**
     * Ottiene le impostazioni (alias per compatibilità)
     */
    public function settings(): array
    {
        return $this->getSettings();
    }

    /**
     * Ottiene le impostazioni
     */
    public function getSettings(): array
    {
        $defaults = [
            'enabled' => false,
            'detect_connection' => true,
            'save_data_mode' => true,
            'adaptive_images' => true,
            'adaptive_videos' => false,
            'quality_slow' => 60,
            'quality_moderate' => 75,
            'quality_fast' => 85,
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
        remove_filter('wp_get_attachment_image_src', [$this, 'adaptImageQuality'], 10);
        remove_action('wp_footer', [$this, 'detectConnectionType'], 35);
        
        // Reinizializza
        $this->register();
    }

    /**
     * Get option value (with fallback)
     * 
     * @param string $key Option key
     * @param mixed $default Default value
     * @return mixed
     */
    private function getOption(string $key, $default = null)
    {
        if ($this->optionsRepo !== null) {
            return $this->optionsRepo->get($key, $default);
        }
        return get_option($key, $default);
    }

    /**
     * Set option value (with fallback)
     * 
     * @param string $key Option key
     * @param mixed $value Value to set
     * @return bool
     */
    private function setOption(string $key, $value): bool
    {
        if ($this->optionsRepo !== null) {
            return $this->optionsRepo->set($key, $value);
        }
        return update_option($key, $value);
    }

    /**
     * Rileva tipo connessione
     */
    public function detectConnectionType(): void
    {
        ?>
        <script>
        (function() {
            if ('connection' in navigator) {
                const conn = navigator.connection || navigator.mozConnection || navigator.webkitConnection;
                const connectionType = conn.effectiveType; // slow-2g, 2g, 3g, 4g
                const saveData = conn.saveData || false;
                
                // Salva in sessionStorage
                sessionStorage.setItem('fp_connection_type', connectionType);
                sessionStorage.setItem('fp_save_data', saveData);
                
                // Invia al server via beacon
                if (navigator.sendBeacon) {
                    const data = new FormData();
                    data.append('connection_type', connectionType);
                    data.append('save_data', saveData);
                    navigator.sendBeacon('<?php echo admin_url('admin-ajax.php?action=fp_save_connection'); ?>', data);
                }
            }
        })();
        </script>
        <?php
    }

    /**
     * Adatta qualità immagine
     */
    public function adaptImageQuality($image, $attachment_id, $size, $icon)
    {
        // Questo è un placeholder - in produzione richiederebbe
        // logica più complessa per rigenerare immagini con qualità diversa

        return $image;
    }

    /**
     * Ottiene tipo connessione utente corrente
     * SICUREZZA: Sanitizzazione input per prevenire XSS
     */
    private function getConnectionType(): string
    {
        // SICUREZZA: Sanitizziamo sempre gli input per prevenire XSS
        if (isset($_COOKIE['fp_connection_type'])) {
            $connection_type = sanitize_text_field($_COOKIE['fp_connection_type']);
            // Validiamo che sia un valore consentito
            $allowed_types = ['slow-2g', '2g', '3g', '4g'];
            if (in_array($connection_type, $allowed_types, true)) {
                return $connection_type;
            }
        }

        // Fallback sicuro
        return '4g';
    }

    /**
     * Verifica se è connessione lenta
     */
    public function isSlowConnection(): bool
    {
        $type = $this->getConnectionType();
        return in_array($type, ['slow-2g', '2g', '3g'], true);
    }

    /**
     * Ottiene qualità raccomandata
     */
    public function getRecommendedQuality(): int
    {
        $settings = $this->getSettings();
        
        return $this->isSlowConnection() ? 
            $settings['quality_slow'] : 
            $settings['quality_fast'];
    }

    /**
     * Status
     */
    public function status(): array
    {
        return [
            'enabled' => !empty($this->getSettings()['enabled']),
            'settings' => $this->getSettings(),
            'current_connection' => $this->getConnectionType(),
            'is_slow' => $this->isSlowConnection(),
            'recommended_quality' => $this->getRecommendedQuality(),
        ];
    }
}

