<?php

namespace FP\PerfSuite\Services\Intelligence\SmartExclusionDetector;

use FP\PerfSuite\Core\Options\OptionsRepositoryInterface;
use function get_option;
use function update_option;
use function time;
use function wp_generate_uuid4;

/**
 * Gestisce l'applicazione e la rimozione delle esclusioni
 * 
 * @package FP\PerfSuite\Services\Intelligence\SmartExclusionDetector
 * @author Francesco Passeri
 */
class ExclusionManager
{
    private const OPTION_KEY = 'fp_ps_smart_exclusions';
    private ?OptionsRepositoryInterface $optionsRepo = null;
    
    /**
     * Costruttore
     * 
     * @param OptionsRepositoryInterface|null $optionsRepo Repository opzionale per gestione opzioni
     */
    public function __construct(?OptionsRepositoryInterface $optionsRepo = null)
    {
        $this->optionsRepo = $optionsRepo;
    }
    
    /**
     * Helper per ottenere opzioni con fallback
     * 
     * @param string $key Chiave opzione
     * @param mixed $default Valore di default
     * @return mixed Valore opzione
     */
    private function getOption(string $key, $default = null)
    {
        if ($this->optionsRepo !== null) {
            return $this->optionsRepo->get($key, $default);
        }
        return get_option($key, $default);
    }
    
    /**
     * Helper per salvare opzioni con fallback
     * 
     * @param string $key Chiave opzione
     * @param mixed $value Valore opzione
     * @param bool $autoload Se autoload
     * @return bool True se salvato con successo
     */
    private function setOption(string $key, $value, bool $autoload = true): bool
    {
        if ($this->optionsRepo !== null) {
            return $this->optionsRepo->set($key, $value, $autoload);
        }
        return update_option($key, $value, $autoload);
    }

    /**
     * Applica automaticamente le esclusioni rilevate
     */
    public function autoApplyExclusions(array $detected, bool $dryRun = true): array
    {
        $applied = [];
        $skipped = [];

        foreach ($detected as $category => $items) {
            if ($category === 'already_protected') {
                continue;
            }

            foreach ($items as $item) {
                $url = $item['url'] ?? '';
                $confidence = $item['confidence'] ?? 0;

                // Applica solo se confidence > 0.7
                if ($confidence > 0.7) {
                    if (!$dryRun) {
                        $success = $this->addExclusion($url, $item);
                        if ($success) {
                            $applied[] = $item;
                        }
                    } else {
                        $applied[] = $item;
                    }
                } else {
                    $skipped[] = $item;
                }
            }
        }

        return [
            'applied' => $applied,
            'skipped' => $skipped,
            'dry_run' => $dryRun,
        ];
    }

    /**
     * Aggiunge un'esclusione
     */
    public function addExclusion(string $url, array $metadata = []): bool
    {
        $exclusions = $this->getAppliedExclusions();

        // Verifica se esiste già
        foreach ($exclusions as $exclusion) {
            if ($exclusion['url'] === $url) {
                return false; // Già presente
            }
        }

        $exclusions[] = [
            'id' => wp_generate_uuid4(),
            'url' => $url,
            'pattern' => $metadata['pattern'] ?? '',
            'reason' => $metadata['reason'] ?? '',
            'confidence' => $metadata['confidence'] ?? 0,
            'type' => $metadata['type'] ?? 'auto',
            'applied_at' => time(),
        ];

        return $this->setOption(self::OPTION_KEY, $exclusions);
    }

    /**
     * Ottiene le esclusioni applicate
     */
    public function getAppliedExclusions(): array
    {
        $exclusions = $this->getOption(self::OPTION_KEY, []);
        return is_array($exclusions) ? $exclusions : [];
    }

    /**
     * Rimuove un'esclusione
     */
    public function removeExclusion(string $exclusionId): bool
    {
        $exclusions = $this->getAppliedExclusions();
        $filtered = [];

        foreach ($exclusions as $exclusion) {
            if ($exclusion['id'] !== $exclusionId) {
                $filtered[] = $exclusion;
            }
        }

        return $this->setOption(self::OPTION_KEY, $filtered);
    }

    /**
     * Aggiunge un'esclusione manuale
     */
    public function addManualExclusion(string $url, string $reason = ''): bool
    {
        return $this->addExclusion($url, [
            'pattern' => $url,
            'reason' => $reason ?: __('Manually added exclusion', 'fp-performance-suite'),
            'confidence' => 1.0,
            'type' => 'manual',
        ]);
    }
}















