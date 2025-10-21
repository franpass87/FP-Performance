<?php

namespace FP\PerfSuite\Contracts;

/**
 * Optimizer Interface
 * 
 * Definisce il contratto per i servizi di ottimizzazione
 * 
 * ARCHITECTURE: Interfaccia creata nel Turno 6 per standardizzare
 * i servizi di ottimizzazione
 * 
 * @package FP\PerfSuite\Contracts
 * @since 1.6.0
 */
interface OptimizerInterface
{
    /**
     * Registra gli hook WordPress
     *
     * @return void
     */
    public function register(): void;

    /**
     * Verifica se il servizio è abilitato
     *
     * @return bool True se abilitato
     */
    public function isEnabled(): bool;

    /**
     * Ottiene le impostazioni
     *
     * @return array<string,mixed> Impostazioni correnti
     */
    public function getSettings(): array;

    /**
     * Aggiorna le impostazioni
     *
     * @param array<string,mixed> $settings Nuove impostazioni
     * @return bool True se aggiornamento riuscito
     */
    public function updateSettings(array $settings): bool;
}
