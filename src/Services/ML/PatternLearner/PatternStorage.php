<?php

namespace FP\PerfSuite\Services\ML\PatternLearner;

use FP\PerfSuite\Core\Options\OptionsRepositoryInterface;
use function update_option;
use function get_option;

/**
 * Gestisce lo storage dei pattern appresi
 * 
 * @package FP\PerfSuite\Services\ML\PatternLearner
 * @author Francesco Passeri
 */
class PatternStorage
{
    private const OPTION = 'fp_ps_ml_patterns';
    
    /** @var OptionsRepositoryInterface|null Options repository (injected) */
    private ?OptionsRepositoryInterface $optionsRepo = null;

    /**
     * Constructor
     * 
     * @param OptionsRepositoryInterface|null $optionsRepo Options repository (optional for backward compatibility)
     */
    public function __construct(?OptionsRepositoryInterface $optionsRepo = null)
    {
        $this->optionsRepo = $optionsRepo;
    }

    /**
     * Salva pattern appresi
     */
    public function save(array $patterns): void
    {
        if ($this->optionsRepo !== null) {
            $this->optionsRepo->set(self::OPTION, $patterns);
        } else {
            // Fallback to direct option call for backward compatibility
            update_option(self::OPTION, $patterns);
        }
    }

    /**
     * Recupera pattern salvati
     */
    public function get(): array
    {
        if ($this->optionsRepo !== null) {
            return $this->optionsRepo->get(self::OPTION, []);
        }
        
        // Fallback to direct option call for backward compatibility
        return get_option(self::OPTION, []);
    }
}






