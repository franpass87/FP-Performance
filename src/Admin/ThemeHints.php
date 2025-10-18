<?php

namespace FP\PerfSuite\Admin;

use FP\PerfSuite\Services\Compatibility\ThemeDetector;

/**
 * Theme-Specific Hints Helper
 * 
 * Fornisce suggerimenti contestuali basati sul tema attivo
 * Usato per integrare tooltip e badge specifici nelle pagine admin
 * 
 * @package FP\PerfSuite\Admin
 * @author Francesco Passeri
 */
class ThemeHints
{
    private ThemeDetector $detector;
    private array $config;
    
    public function __construct(ThemeDetector $detector)
    {
        $this->detector = $detector;
        $this->config = $detector->getRecommendedConfig();
    }
    
    /**
     * Verifica se il tema è Salient
     */
    public function isSalient(): bool
    {
        return $this->detector->isSalient();
    }
    
    /**
     * Ottiene il nome del tema corrente
     */
    public function getThemeName(): string
    {
        return $this->config['theme']['name'] ?? 'Unknown';
    }
    
    /**
     * Ottiene il nome del page builder corrente
     */
    public function getBuilderName(): string
    {
        return $this->config['page_builder']['name'] ?? 'None';
    }
    
    /**
     * Ottiene un hint specifico per una funzionalità
     * 
     * @param string $feature Nome della funzionalità (es: 'object_cache', 'third_party_scripts')
     * @return array|null Array con 'badge', 'tooltip', 'priority' o null se non ci sono hint
     */
    public function getHint(string $feature): ?array
    {
        if (!isset($this->config['recommendations'][$feature])) {
            return null;
        }
        
        $recommendation = $this->config['recommendations'][$feature];
        
        // Non mostrare hint se non è specifico per il tema
        if (!$this->isSalient() && !$this->isKnownTheme()) {
            return null;
        }
        
        $priority = $recommendation['priority'] ?? 'medium';
        $enabled = $recommendation['enabled'] ?? false;
        $reason = $recommendation['reason'] ?? '';
        
        // Mappa priorità a colori
        $priorityColors = [
            'high' => '#dc2626',     // rosso
            'medium' => '#f59e0b',   // arancione
            'low' => '#3b82f6',      // blu
        ];
        
        $priorityLabels = [
            'high' => '🔴 Alta Priorità',
            'medium' => '🟡 Media Priorità', 
            'low' => '🔵 Bassa Priorità',
        ];
        
        return [
            'badge' => $this->formatBadge($enabled, $priority),
            'tooltip' => $this->formatTooltip($feature, $enabled, $reason, $priority),
            'priority' => $priority,
            'priority_color' => $priorityColors[$priority] ?? '#666',
            'priority_label' => $priorityLabels[$priority] ?? 'Media Priorità',
            'enabled_recommended' => $enabled,
            'reason' => $reason,
        ];
    }
    
    /**
     * Formatta un badge HTML
     */
    private function formatBadge(bool $enabled, string $priority): string
    {
        $icon = $enabled ? '✅' : '⚠️';
        $text = $enabled ? 'Consigliato' : 'Sconsigliato';
        $bgColor = $enabled ? '#d1fae5' : '#fef3c7';
        $textColor = $enabled ? '#065f46' : '#92400e';
        
        if ($priority === 'high') {
            $text .= ' (Alta Priorità)';
        }
        
        return sprintf(
            '<span class="fp-ps-theme-badge" style="background: %s; color: %s; padding: 4px 10px; border-radius: 12px; font-size: 11px; font-weight: 600; margin-left: 8px; white-space: nowrap;">%s %s</span>',
            $bgColor,
            $textColor,
            $icon,
            $text
        );
    }
    
    /**
     * Formatta un tooltip HTML
     */
    private function formatTooltip(string $feature, bool $enabled, string $reason, string $priority): string
    {
        $themeName = $this->getThemeName();
        $builderName = $this->getBuilderName();
        $action = $enabled ? 'attivare' : 'disattivare';
        
        $priorityEmoji = [
            'high' => '🔴',
            'medium' => '🟡',
            'low' => '🔵',
        ];
        
        return sprintf(
            '<div class="fp-ps-theme-tooltip" style="position: absolute; background: #1f2937; color: white; padding: 12px; border-radius: 6px; font-size: 12px; line-height: 1.5; max-width: 300px; z-index: 9999; box-shadow: 0 4px 6px rgba(0,0,0,0.3); display: none;">
                <div style="font-weight: 600; margin-bottom: 8px; color: #60a5fa;">
                    🎨 %s + %s
                </div>
                <div style="margin-bottom: 8px;">
                    <strong>%s Priorità: %s</strong><br>
                    Ti consigliamo di <strong>%s</strong> questa funzionalità.
                </div>
                <div style="color: #d1d5db; font-size: 11px; border-top: 1px solid #374151; padding-top: 8px;">
                    💡 <strong>Motivo:</strong><br>%s
                </div>
            </div>',
            esc_html($themeName),
            esc_html($builderName),
            $priorityEmoji[$priority] ?? '⚪',
            ucfirst($priority),
            $action,
            esc_html($reason)
        );
    }
    
    /**
     * Verifica se il tema è uno conosciuto con configurazioni specifiche
     */
    private function isKnownTheme(): bool
    {
        $knownThemes = ['salient', 'avada', 'divi', 'astra'];
        $themeSlug = strtolower($this->config['theme']['slug'] ?? '');
        
        foreach ($knownThemes as $known) {
            if (strpos($themeSlug, $known) !== false) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Renderizza un hint inline accanto a un elemento
     * 
     * @param string $feature Nome della funzionalità
     * @return string HTML del hint
     */
    public function renderInlineHint(string $feature): string
    {
        $hint = $this->getHint($feature);
        
        if (!$hint) {
            return '';
        }
        
        ob_start();
        ?>
        <span class="fp-ps-theme-hint-wrapper" style="position: relative; display: inline-block; vertical-align: middle; margin-left: 8px;">
            <?php echo $hint['badge']; ?>
            <span class="fp-ps-theme-hint-icon" style="cursor: help; display: inline-block; width: 18px; height: 18px; line-height: 18px; text-align: center; background: <?php echo esc_attr($hint['priority_color']); ?>; color: white; border-radius: 50%; font-size: 11px; font-weight: bold; margin-left: 6px;" data-feature="<?php echo esc_attr($feature); ?>">?</span>
            <?php echo $hint['tooltip']; ?>
        </span>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Ottiene un notice generale per Salient
     */
    public function getSalientNotice(): ?array
    {
        if (!$this->isSalient()) {
            return null;
        }
        
        $recommendations = $this->config['recommendations'] ?? [];
        $highPriorityCount = 0;
        $recommendedServices = [];
        
        foreach ($recommendations as $service => $rec) {
            if (($rec['priority'] ?? '') === 'high' && ($rec['enabled'] ?? false)) {
                $highPriorityCount++;
                $recommendedServices[] = $this->getServiceLabel($service);
            }
        }
        
        return [
            'theme' => $this->getThemeName(),
            'builder' => $this->getBuilderName(),
            'high_priority_count' => $highPriorityCount,
            'recommended_services' => array_slice($recommendedServices, 0, 3),
            'message' => sprintf(
                'Abbiamo rilevato che stai usando <strong>%s</strong> con <strong>%s</strong>. Ci sono %d ottimizzazioni ad alta priorità consigliate per massimizzare le performance.',
                $this->getThemeName(),
                $this->getBuilderName(),
                $highPriorityCount
            ),
        ];
    }
    
    /**
     * Ottiene etichetta leggibile per un servizio
     */
    private function getServiceLabel(string $service): string
    {
        $labels = [
            'object_cache' => '🗄️ Object Cache',
            'core_web_vitals' => '📊 Core Web Vitals Monitor',
            'third_party_scripts' => '🔌 Third-Party Scripts',
            'avif_converter' => '🖼️ AVIF Converter',
            'service_worker' => '📱 Service Worker',
            'edge_cache' => '🌐 Edge Cache',
            'http2_push' => '⚡ HTTP/2 Push',
            'smart_delivery' => '📱 Smart Delivery',
        ];
        
        return $labels[$service] ?? ucwords(str_replace('_', ' ', $service));
    }
    
    /**
     * Renderizza script JS per gestire tooltip interattivi
     */
    public static function renderTooltipScript(): string
    {
        return <<<'JS'
<script>
jQuery(document).ready(function($) {
    // Mostra/nascondi tooltip al hover
    $('.fp-ps-theme-hint-icon').on('mouseenter', function() {
        $(this).siblings('.fp-ps-theme-tooltip').fadeIn(200);
    }).on('mouseleave', function() {
        $(this).siblings('.fp-ps-theme-tooltip').fadeOut(200);
    });
    
    // Posiziona il tooltip sopra l'icona
    $('.fp-ps-theme-hint-icon').each(function() {
        var $icon = $(this);
        var $tooltip = $icon.siblings('.fp-ps-theme-tooltip');
        
        $icon.on('mouseenter', function() {
            var iconPos = $icon.offset();
            var iconWidth = $icon.outerWidth();
            var tooltipWidth = $tooltip.outerWidth();
            
            $tooltip.css({
                'left': -((tooltipWidth / 2) - (iconWidth / 2)) + 'px',
                'bottom': '100%',
                'margin-bottom': '8px',
                'position': 'absolute'
            });
        });
    });
});
</script>
JS;
    }
}
