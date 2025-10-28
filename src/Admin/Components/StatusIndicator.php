<?php

namespace FP\PerfSuite\Admin\Components;

/**
 * Status Indicator Component
 * 
 * Componente unificato per indicatori di stato a semaforo
 * utilizzato in tutte le pagine admin del plugin.
 * 
 * Stati disponibili:
 * - success: Verde 🟢 - Tutto ok
 * - warning: Giallo 🟡 - Attenzione richiesta
 * - error: Rosso 🔴 - Problema critico
 * - info: Blu 🔵 - Informativo
 * - inactive: Grigio ⚫ - Disabilitato/Inattivo
 *
 * @package FP\PerfSuite\Admin\Components
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */
class StatusIndicator
{
    /**
     * Stati disponibili con relative configurazioni
     * 
     * Nota: Utilizza le variabili CSS del plugin per consistenza
     * con il sistema Risk Indicator e il resto dell'interfaccia.
     * 
     * Colori unificati:
     * - success: var(--fp-ok) #1f9d55 (verde scuro)
     * - warning: var(--fp-warn) #f1b814 (amber)
     * - error: var(--fp-danger) #d94452 (rosso)
     * - info: var(--fp-accent) #2d6cdf (blu)
     * - inactive: var(--fp-gray-500) #6b7280 (grigio)
     */
    private const STATES = [
        'success' => [
            'color' => '#1f9d55',        // var(--fp-ok)
            'bg_color' => '#d1fae5',     // var(--fp-success-light)
            'border_color' => '#1f9d55', // var(--fp-ok)
            'emoji' => '🟢',
            'symbol' => '✓',
            'label' => 'Attivo',
        ],
        'warning' => [
            'color' => '#f1b814',        // var(--fp-warn)
            'bg_color' => '#fef3c7',     // var(--fp-warning-light)
            'border_color' => '#f1b814', // var(--fp-warn)
            'emoji' => '🟡',
            'symbol' => '⚠',
            'label' => 'Attenzione',
        ],
        'error' => [
            'color' => '#d94452',        // var(--fp-danger)
            'bg_color' => '#fee2e2',     // var(--fp-danger-light)
            'border_color' => '#d94452', // var(--fp-danger)
            'emoji' => '🔴',
            'symbol' => '✗',
            'label' => 'Errore',
        ],
        'info' => [
            'color' => '#2d6cdf',        // var(--fp-accent)
            'bg_color' => '#dbeafe',     // var(--fp-info-light)
            'border_color' => '#2d6cdf', // var(--fp-accent)
            'emoji' => '🔵',
            'symbol' => 'ℹ',
            'label' => 'Info',
        ],
        'inactive' => [
            'color' => '#6b7280',        // var(--fp-gray-500)
            'bg_color' => '#f3f4f6',     // var(--fp-gray-100)
            'border_color' => '#9ca3af', // var(--fp-gray-300)
            'emoji' => '⚫',
            'symbol' => '○',
            'label' => 'Inattivo',
        ],
    ];

    /**
     * Render inline status indicator
     *
     * @param string $status Lo stato (success, warning, error, info, inactive)
     * @param string $label Etichetta personalizzata (opzionale)
     * @param string $style Stile: 'emoji', 'symbol', 'dot', 'badge'
     * @return string HTML del componente
     */
    public static function render(string $status, string $label = '', string $style = 'emoji'): string
    {
        if (!isset(self::STATES[$status])) {
            $status = 'inactive';
        }

        $config = self::STATES[$status];
        $displayLabel = $label ?: $config['label'];

        switch ($style) {
            case 'emoji':
                return sprintf(
                    '<span class="fp-ps-status-indicator fp-ps-status-%s" style="color: %s;">%s %s</span>',
                    esc_attr($status),
                    esc_attr($config['color']),
                    $config['emoji'],
                    esc_html($displayLabel)
                );

            case 'symbol':
                return sprintf(
                    '<span class="fp-ps-status-indicator fp-ps-status-%s" style="color: %s;">%s %s</span>',
                    esc_attr($status),
                    esc_attr($config['color']),
                    $config['symbol'],
                    esc_html($displayLabel)
                );

            case 'dot':
                return sprintf(
                    '<span class="fp-ps-status-indicator fp-ps-status-%s">
                        <span class="fp-ps-status-dot" style="background: %s;"></span>
                        <span class="fp-ps-status-label">%s</span>
                    </span>',
                    esc_attr($status),
                    esc_attr($config['color']),
                    esc_html($displayLabel)
                );

            case 'badge':
                return sprintf(
                    '<span class="fp-ps-status-badge fp-ps-status-%s" style="background: %s; color: white;">%s</span>',
                    esc_attr($status),
                    esc_attr($config['color']),
                    esc_html($displayLabel)
                );

            default:
                return self::render($status, $label, 'emoji');
        }
    }

    /**
     * Render status card
     *
     * @param string $status Lo stato
     * @param string $title Titolo della card
     * @param string $description Descrizione (opzionale)
     * @param string $value Valore da mostrare (opzionale)
     * @return string HTML della card
     */
    public static function renderCard(string $status, string $title, string $description = '', string $value = ''): string
    {
        if (!isset(self::STATES[$status])) {
            $status = 'inactive';
        }

        $config = self::STATES[$status];

        ob_start();
        ?>
        <div class="fp-ps-status-card fp-ps-status-<?php echo esc_attr($status); ?>" 
             style="background: <?php echo esc_attr($config['bg_color']); ?>; border-left: 4px solid <?php echo esc_attr($config['border_color']); ?>;">
            <div class="fp-ps-status-card-header">
                <span class="fp-ps-status-icon" style="color: <?php echo esc_attr($config['color']); ?>;">
                    <?php echo $config['emoji']; ?>
                </span>
                <h3 class="fp-ps-status-card-title" style="color: <?php echo esc_attr($config['color']); ?>;">
                    <?php echo esc_html($title); ?>
                </h3>
            </div>
            <?php if ($value): ?>
                <div class="fp-ps-status-card-value" style="color: <?php echo esc_attr($config['color']); ?>;">
                    <?php echo esc_html($value); ?>
                </div>
            <?php endif; ?>
            <?php if ($description): ?>
                <div class="fp-ps-status-card-description">
                    <?php echo esc_html($description); ?>
                </div>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Render progress bar with status
     *
     * @param int $percentage Percentuale (0-100)
     * @param string $status Stato che determina il colore
     * @param string $label Etichetta (opzionale)
     * @return string HTML della progress bar
     */
    public static function renderProgressBar(int $percentage, string $status = null, string $label = ''): string
    {
        // Auto-determina lo stato in base alla percentuale se non specificato
        if ($status === null) {
            if ($percentage >= 80) {
                $status = 'success';
            } elseif ($percentage >= 50) {
                $status = 'warning';
            } else {
                $status = 'error';
            }
        }

        if (!isset(self::STATES[$status])) {
            $status = 'inactive';
        }

        $config = self::STATES[$status];
        $percentage = max(0, min(100, $percentage));

        ob_start();
        ?>
        <div class="fp-ps-progress-wrapper">
            <?php if ($label): ?>
                <div class="fp-ps-progress-label">
                    <?php echo esc_html($label); ?>
                    <span class="fp-ps-progress-percentage" style="color: <?php echo esc_attr($config['color']); ?>;">
                        <?php echo esc_html($percentage); ?>%
                    </span>
                </div>
            <?php endif; ?>
            <div class="fp-ps-progress-bar">
                <div class="fp-ps-progress-fill fp-ps-status-<?php echo esc_attr($status); ?>" 
                     style="width: <?php echo esc_attr($percentage); ?>%; background: <?php echo esc_attr($config['color']); ?>;">
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Render status list item
     *
     * @param string $status Stato
     * @param string $label Etichetta
     * @param string $description Descrizione opzionale
     * @return string HTML del list item
     */
    public static function renderListItem(string $status, string $label, string $description = ''): string
    {
        if (!isset(self::STATES[$status])) {
            $status = 'inactive';
        }

        $config = self::STATES[$status];

        ob_start();
        ?>
        <li class="fp-ps-status-list-item">
            <span style="color: <?php echo esc_attr($config['color']); ?>;">
                <?php echo $config['symbol']; ?>
            </span>
            <span class="fp-ps-status-list-label"><?php echo esc_html($label); ?></span>
            <?php if ($description): ?>
                <span class="fp-ps-status-list-description"><?php echo esc_html($description); ?></span>
            <?php endif; ?>
        </li>
        <?php
        return ob_get_clean();
    }

    /**
     * Get status configuration
     *
     * @param string $status Stato
     * @return array Configurazione dello stato
     */
    public static function getConfig(string $status): array
    {
        return self::STATES[$status] ?? self::STATES['inactive'];
    }

    /**
     * Get color for status
     *
     * @param string $status Stato
     * @return string Colore esadecimale
     */
    public static function getColor(string $status): string
    {
        return self::STATES[$status]['color'] ?? self::STATES['inactive']['color'];
    }

    /**
     * Auto-determine status from percentage or value
     *
     * @param float $value Valore (es. percentuale, punteggio)
     * @param float $goodThreshold Soglia per "success" (default: 80)
     * @param float $warningThreshold Soglia per "warning" (default: 50)
     * @return string Stato determinato
     */
    public static function autoStatus(float $value, float $goodThreshold = 80, float $warningThreshold = 50): string
    {
        if ($value >= $goodThreshold) {
            return 'success';
        } elseif ($value >= $warningThreshold) {
            return 'warning';
        } else {
            return 'error';
        }
    }

    /**
     * Render comparison indicator (up/down arrows)
     *
     * @param float $value Valore attuale
     * @param float $previous Valore precedente
     * @param bool $higherIsBetter Se true, valori alti sono positivi
     * @return string HTML dell'indicatore
     */
    public static function renderComparison(float $value, float $previous, bool $higherIsBetter = true): string
    {
        $diff = $value - $previous;
        $percentage = $previous != 0 ? abs(($diff / $previous) * 100) : 0;

        if ($diff > 0) {
            $status = $higherIsBetter ? 'success' : 'error';
            $arrow = '↑';
        } elseif ($diff < 0) {
            $status = $higherIsBetter ? 'error' : 'success';
            $arrow = '↓';
        } else {
            $status = 'info';
            $arrow = '→';
        }

        $config = self::STATES[$status];

        return sprintf(
            '<span class="fp-ps-comparison-indicator" style="color: %s;">%s %s%%</span>',
            esc_attr($config['color']),
            $arrow,
            number_format($percentage, 1)
        );
    }
}

