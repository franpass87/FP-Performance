<?php

namespace FP\PerfSuite\Contracts;

/**
 * Cache Interface
 * 
 * Definisce il contratto per i servizi di cache
 * 
 * ARCHITECTURE: Interfaccia creata nel Turno 6 per migliorare
 * la dependency injection e il decoupling
 * 
 * @package FP\PerfSuite\Contracts
 * @since 1.6.0
 */
interface CacheInterface
{
    /**
     * Verifica se il servizio è abilitato
     *
     * @return bool True se abilitato
     */
    public function isEnabled(): bool;

    /**
     * Pulisce la cache
     *
     * @return void
     */
    public function clear(): void;

    /**
     * Ottiene lo stato del servizio
     *
     * @return array{enabled:bool,...} Stato corrente
     */
    public function status(): array;

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
