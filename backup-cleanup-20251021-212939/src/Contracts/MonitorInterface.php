<?php

namespace FP\PerfSuite\Contracts;

/**
 * Monitor Interface
 * 
 * Definisce il contratto per i servizi di monitoraggio
 * 
 * ARCHITECTURE: Interfaccia creata nel Turno 6 per standardizzare
 * i servizi di monitoraggio
 * 
 * @package FP\PerfSuite\Contracts
 * @since 1.6.0
 */
interface MonitorInterface
{
    /**
     * Registra gli hook WordPress per il monitoraggio
     *
     * @return void
     */
    public function register(): void;

    /**
     * Verifica se il monitoraggio è abilitato
     *
     * @return bool True se abilitato
     */
    public function isEnabled(): bool;

    /**
     * Ottiene i dati di monitoraggio
     *
     * @param int $days Numero di giorni da analizzare
     * @return array<string,mixed> Dati di monitoraggio
     */
    public function getData(int $days = 7): array;

    /**
     * Pulisce i dati vecchi
     *
     * @param int $days Mantieni solo ultimi N giorni
     * @return int Numero di record rimossi
     */
    public function cleanup(int $days = 30): int;
}

