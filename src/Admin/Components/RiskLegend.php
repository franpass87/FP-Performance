<?php

namespace FP\PerfSuite\Admin\Components;

/**
 * Risk Legend Component
 * 
 * Componente riutilizzabile per mostrare la legenda del sistema di semafori
 * 
 * @package FP\PerfSuite\Admin\Components
 */
class RiskLegend
{
    /**
     * Renderizza la legenda completa del sistema di rischi
     * 
     * @param bool $show_warning Mostra il banner di avviso per opzioni ad alto rischio
     * @return string HTML della legenda
     */
    public static function render(bool $show_warning = false): string
    {
        ob_start();
        ?>
        
        <?php if ($show_warning) : ?>
        <!-- Warning Banner per Opzioni ad Alto Rischio -->
        <div class="fp-ps-risk-warning-banner">
            <div class="fp-ps-risk-warning-content">
                <h3><?php esc_html_e('⚠️ Attenzione: Questa sezione contiene opzioni ad ALTO RISCHIO', 'fp-performance-suite'); ?></h3>
                <p><?php esc_html_e('Alcune opzioni in questa pagina possono causare problemi visivi o funzionali al tuo sito se attivate senza la configurazione corretta.', 'fp-performance-suite'); ?></p>
                <ul>
                    <li><strong><?php esc_html_e('🔴 Rischio MOLTO Alto:', 'fp-performance-suite'); ?></strong> <?php esc_html_e('NON attivare senza testare su staging. Può rompere completamente il layout.', 'fp-performance-suite'); ?></li>
                    <li><strong><?php esc_html_e('🟡 Rischio Medio:', 'fp-performance-suite'); ?></strong> <?php esc_html_e('Testa accuratamente su tutte le pagine prima di usare in produzione.', 'fp-performance-suite'); ?></li>
                    <li><strong><?php esc_html_e('🟢 Rischio Basso:', 'fp-performance-suite'); ?></strong> <?php esc_html_e('Sicuro da attivare. Raramente causa problemi.', 'fp-performance-suite'); ?></li>
                </ul>
                <p style="margin-top: 12px; font-weight: 600;">
                    💡 <?php esc_html_e('Passa il mouse sopra il semaforo colorato accanto a ogni opzione per vedere i dettagli sui rischi specifici.', 'fp-performance-suite'); ?>
                </p>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Legenda Sistema Semafori -->
        <div class="fp-ps-risk-legend">
            <h3 class="fp-ps-risk-legend-title">
                <?php esc_html_e('Guida ai Livelli di Rischio', 'fp-performance-suite'); ?>
            </h3>
            
            <div class="fp-ps-risk-legend-items">
                <!-- Verde: Sicuro -->
                <div class="fp-ps-risk-legend-item">
                    <div class="fp-ps-risk-legend-icon green"></div>
                    <div class="fp-ps-risk-legend-content">
                        <p class="fp-ps-risk-legend-label">
                            <span class="emoji">🟢</span>
                            <strong><?php esc_html_e('Rischio Basso / Sicuro', 'fp-performance-suite'); ?></strong>
                        </p>
                        <p class="fp-ps-risk-legend-desc">
                            <?php esc_html_e('Opzioni sicure da attivare. Standard web consolidati che raramente causano problemi. Attiva con fiducia!', 'fp-performance-suite'); ?>
                        </p>
                        <p class="fp-ps-risk-legend-examples">
                            <?php esc_html_e('Es: Compressione Gzip, Minify CSS/JS, Remove Emoji Script, Browser Caching', 'fp-performance-suite'); ?>
                        </p>
                    </div>
                </div>
                
                <!-- Ambra: Medio -->
                <div class="fp-ps-risk-legend-item">
                    <div class="fp-ps-risk-legend-icon amber"></div>
                    <div class="fp-ps-risk-legend-content">
                        <p class="fp-ps-risk-legend-label">
                            <span class="emoji">🟡</span>
                            <strong><?php esc_html_e('Rischio Medio / Testa Prima', 'fp-performance-suite'); ?></strong>
                        </p>
                        <p class="fp-ps-risk-legend-desc">
                            <?php esc_html_e('Opzioni che possono causare problemi su alcuni siti. Testa accuratamente su staging e verifica tutte le pagine prima di usare in produzione.', 'fp-performance-suite'); ?>
                        </p>
                        <p class="fp-ps-risk-legend-examples">
                            <?php esc_html_e('Es: Defer JavaScript, Async CSS, Combine Files, Lazy Loading', 'fp-performance-suite'); ?>
                        </p>
                    </div>
                </div>
                
                <!-- Rosso: Alto Rischio -->
                <div class="fp-ps-risk-legend-item">
                    <div class="fp-ps-risk-legend-icon red"></div>
                    <div class="fp-ps-risk-legend-content">
                        <p class="fp-ps-risk-legend-label">
                            <span class="emoji">🔴</span>
                            <strong><?php esc_html_e('Rischio MOLTO Alto / NON Consigliato', 'fp-performance-suite'); ?></strong>
                        </p>
                        <p class="fp-ps-risk-legend-desc">
                            <?php esc_html_e('Opzioni aggressive che probabilmente romperanno il tuo sito. Richiedono configurazione esperta e esclusioni manuali. NON attivare senza esperienza avanzata.', 'fp-performance-suite'); ?>
                        </p>
                        <p class="fp-ps-risk-legend-examples">
                            <?php esc_html_e('Es: Remove Unused CSS, Defer Non-Critical CSS, Tree Shaking automatico', 'fp-performance-suite'); ?>
                        </p>
                    </div>
                </div>
            </div>
            
            <div style="margin-top: 16px; padding-top: 16px; border-top: 1px solid #cbd5e1; font-size: 12px; color: #64748b;">
                <strong>💡 <?php esc_html_e('Suggerimento Professionale:', 'fp-performance-suite'); ?></strong>
                <?php esc_html_e('Passa il mouse sopra ogni semaforo colorato per vedere i rischi specifici, esempi concreti di problemi, e consigli personalizzati per quella opzione.', 'fp-performance-suite'); ?>
            </div>
        </div>
        
        <?php
        return ob_get_clean();
    }
    
    /**
     * Renderizza solo il warning banner
     * 
     * @return string HTML del warning banner
     */
    public static function renderWarning(): string
    {
        return self::render(true);
    }
    
    /**
     * Renderizza solo la legenda senza warning
     * 
     * @return string HTML della legenda
     */
    public static function renderLegend(): string
    {
        return self::render(false);
    }
}

