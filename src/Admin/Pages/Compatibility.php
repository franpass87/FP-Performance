<?php

namespace FP\PerfSuite\Admin\Pages;

use FP\PerfSuite\ServiceContainer;
use FP\PerfSuite\Services\Compatibility\ThemeCompatibility;
use FP\PerfSuite\Services\Compatibility\ThemeDetector;

/**
 * Compatibility Admin Page
 *
 * @package FP\PerfSuite\Admin\Pages
 * @author Francesco Passeri
 */
class Compatibility extends AbstractPage
{
    private ThemeCompatibility $compat;
    private ThemeDetector $detector;
    
    public function __construct(ServiceContainer $container)
    {
        parent::__construct($container);
        $this->compat = $container->get(ThemeCompatibility::class);
        $this->detector = $container->get(ThemeDetector::class);
    }
    
    public function render(): void
    {
        $config = $this->detector->getRecommendedConfig();
        $status = $this->compat->status();
        $theme = $config['theme'];
        $builder = $config['page_builder'];
        
        ?>
        <div class="wrap fp-ps-compatibility">
            <h1>🎨 Compatibilità Tema & Page Builder</h1>
            
            <?php if (!empty($_GET['applied'])): ?>
            <div class="notice notice-success is-dismissible">
                <p><strong>✅ Configurazione applicata con successo!</strong></p>
            </div>
            <?php endif; ?>
            
            <div class="fp-ps-card">
                <h2>📋 Ambiente Rilevato</h2>
                <table class="widefat">
                    <tr>
                        <th width="30%">Tema Attivo</th>
                        <td>
                            <strong><?php echo esc_html($theme['name']); ?></strong>
                            <?php if (isset($theme['parent'])): ?>
                                <br><small>Parent: <?php echo esc_html($theme['parent']); ?></small>
                            <?php endif; ?>
                            <br><small>Versione: <?php echo esc_html($theme['version']); ?></small>
                        </td>
                    </tr>
                    <tr>
                        <th>Page Builder</th>
                        <td>
                            <strong><?php echo esc_html($builder['name']); ?></strong>
                            <?php if ($builder['detected']): ?>
                                <span class="fp-ps-badge fp-ps-badge-success">Rilevato</span>
                            <?php else: ?>
                                <span class="fp-ps-badge fp-ps-badge-info">Non rilevato</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <th>Configurazione Auto-Apply</th>
                        <td>
                            <?php if ($status['auto_apply']): ?>
                                <span class="fp-ps-badge fp-ps-badge-success">✅ Attivo</span>
                                <?php if ($status['applied']): ?>
                                    <span class="fp-ps-badge fp-ps-badge-info">Già applicata</span>
                                <?php endif; ?>
                            <?php else: ?>
                                <span class="fp-ps-badge fp-ps-badge-warning">⚠️ Disattivo</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                </table>
            </div>
            
            <?php if ($this->detector->isSalient()): ?>
                <?php $this->renderSalientRecommendations($config); ?>
            <?php else: ?>
                <?php $this->renderGenericRecommendations($config); ?>
            <?php endif; ?>
            
            <div class="fp-ps-card">
                <h2>⚙️ Azioni</h2>
                <form method="post" action="">
                    <?php wp_nonce_field('fp_ps_apply_compat', 'fp_ps_compat_nonce'); ?>
                    
                    <p>
                        <label>
                            <input type="checkbox" name="auto_apply" value="1" <?php checked($status['auto_apply']); ?>>
                            <strong>Attiva applicazione automatica</strong>
                            <br><small>Le raccomandazioni verranno applicate automaticamente quando rilevi un nuovo tema o builder</small>
                        </label>
                    </p>
                    
                    <p>
                        <button type="submit" name="action" value="apply" class="button button-primary">
                            ⚡ Applica Configurazione Raccomandata
                        </button>
                        <button type="submit" name="action" value="save_settings" class="button">
                            💾 Salva Solo Impostazioni
                        </button>
                    </p>
                </form>
            </div>
        </div>
        
        <style>
        .fp-ps-compatibility .fp-ps-card {
            background: #fff;
            padding: 20px;
            margin: 20px 0;
            border: 1px solid #ccd0d4;
            box-shadow: 0 1px 1px rgba(0,0,0,.04);
        }
        .fp-ps-compatibility h2 {
            margin-top: 0;
        }
        .fp-ps-badge {
            display: inline-block;
            padding: 3px 8px;
            font-size: 12px;
            font-weight: 600;
            border-radius: 3px;
            margin-left: 5px;
        }
        .fp-ps-badge-success {
            background: #d4edda;
            color: #155724;
        }
        .fp-ps-badge-warning {
            background: #fff3cd;
            color: #856404;
        }
        .fp-ps-badge-danger {
            background: #f8d7da;
            color: #721c24;
        }
        .fp-ps-badge-info {
            background: #d1ecf1;
            color: #0c5460;
        }
        .fp-ps-recommendation {
            margin: 15px 0;
            padding: 15px;
            border-left: 4px solid #ddd;
        }
        .fp-ps-recommendation.priority-high {
            border-left-color: #dc3545;
            background: #fff5f5;
        }
        .fp-ps-recommendation.priority-medium {
            border-left-color: #ffc107;
            background: #fffbf0;
        }
        .fp-ps-recommendation.priority-low {
            border-left-color: #17a2b8;
            background: #f0f9fb;
        }
        .fp-ps-recommendation h3 {
            margin: 0 0 10px 0;
            font-size: 16px;
        }
        .fp-ps-recommendation .priority {
            float: right;
            font-size: 12px;
            font-weight: 600;
            padding: 3px 8px;
            border-radius: 3px;
        }
        .priority-high { background: #dc3545; color: #fff; }
        .priority-medium { background: #ffc107; color: #000; }
        .priority-low { background: #17a2b8; color: #fff; }
        </style>
        <?php
    }
    
    private function renderSalientRecommendations(array $config): void
    {
        $recommendations = $config['recommendations'];
        ?>
        <div class="fp-ps-card">
            <h2>🎨 Raccomandazioni Specifiche per Salient + <?php echo esc_html($config['page_builder']['name']); ?></h2>
            
            <div class="fp-ps-recommendation priority-<?php echo $recommendations['object_cache']['priority']; ?>">
                <h3>
                    <span class="dashicons dashicons-database"></span>
                    Object Cache (Redis/Memcached)
                    <span class="priority priority-<?php echo $recommendations['object_cache']['priority']; ?>">
                        Priorità: <?php echo ucfirst($recommendations['object_cache']['priority']); ?>
                    </span>
                </h3>
                <p>
                    <strong>Raccomandazione:</strong> 
                    <?php echo $recommendations['object_cache']['enabled'] ? '✅ Attivare' : '❌ Disattivare'; ?>
                </p>
                <p>
                    <strong>Motivo:</strong> <?php echo esc_html($recommendations['object_cache']['reason']); ?>
                </p>
                <p>
                    <strong>Benefici attesi:</strong> Riduzione query database del 70-80%, TTFB più veloce
                </p>
            </div>
            
            <div class="fp-ps-recommendation priority-<?php echo $recommendations['core_web_vitals']['priority']; ?>">
                <h3>
                    <span class="dashicons dashicons-chart-line"></span>
                    Core Web Vitals Monitor
                    <span class="priority priority-<?php echo $recommendations['core_web_vitals']['priority']; ?>">
                        Priorità: <?php echo ucfirst($recommendations['core_web_vitals']['priority']); ?>
                    </span>
                </h3>
                <p>
                    <strong>Raccomandazione:</strong> 
                    <?php echo $recommendations['core_web_vitals']['enabled'] ? '✅ Attivare' : '❌ Disattivare'; ?>
                </p>
                <p>
                    <strong>Motivo:</strong> <?php echo esc_html($recommendations['core_web_vitals']['reason']); ?>
                </p>
                <p>
                    <strong>Configurazione specifica:</strong>
                    <br>• Soglia CLS: <?php echo $recommendations['core_web_vitals']['alert_threshold_cls']; ?>
                    <br>• Alert email attivi
                    <br>• Sample rate 50% utenti
                </p>
            </div>
            
            <div class="fp-ps-recommendation priority-<?php echo $recommendations['third_party_scripts']['priority']; ?>">
                <h3>
                    <span class="dashicons dashicons-media-code"></span>
                    Third-Party Script Manager
                    <span class="priority priority-<?php echo $recommendations['third_party_scripts']['priority']; ?>">
                        Priorità: <?php echo ucfirst($recommendations['third_party_scripts']['priority']); ?>
                    </span>
                </h3>
                <p>
                    <strong>Raccomandazione:</strong> 
                    <?php echo $recommendations['third_party_scripts']['enabled'] ? '✅ Attivare' : '❌ Disattivare'; ?>
                </p>
                <p>
                    <strong>Motivo:</strong> <?php echo esc_html($recommendations['third_party_scripts']['reason']); ?>
                </p>
                <p>
                    <strong>Script esclusi (non ritardati):</strong>
                    <br><?php echo implode(', ', $recommendations['third_party_scripts']['exclude_patterns']); ?>
                </p>
            </div>
            
            <div class="fp-ps-recommendation priority-<?php echo $recommendations['avif_converter']['priority']; ?>">
                <h3>
                    <span class="dashicons dashicons-format-image"></span>
                    AVIF Image Converter
                    <span class="priority priority-<?php echo $recommendations['avif_converter']['priority']; ?>">
                        Priorità: <?php echo ucfirst($recommendations['avif_converter']['priority']); ?>
                    </span>
                </h3>
                <p>
                    <strong>Raccomandazione:</strong> 
                    <?php echo $recommendations['avif_converter']['enabled'] ? '✅ Attivare' : '❌ Disattivare inizialmente'; ?>
                </p>
                <p>
                    <strong>⚠️ ATTENZIONE:</strong> <?php echo esc_html($recommendations['avif_converter']['reason']); ?>
                </p>
                <p>
                    <strong>Piano di test:</strong>
                    <br>1. Abilitare conversione senza auto-delivery
                    <br>2. Testare slider Nectar Slider
                    <br>3. Testare portfolio/lightbox
                    <br>4. Solo dopo test OK, attivare auto-delivery
                </p>
            </div>
            
            <div class="fp-ps-recommendation priority-<?php echo $recommendations['service_worker']['priority']; ?>">
                <h3>
                    <span class="dashicons dashicons-smartphone"></span>
                    Service Worker (PWA)
                    <span class="priority priority-<?php echo $recommendations['service_worker']['priority']; ?>">
                        Priorità: <?php echo ucfirst($recommendations['service_worker']['priority']); ?>
                    </span>
                </h3>
                <p>
                    <strong>Raccomandazione:</strong> 
                    <?php echo $recommendations['service_worker']['enabled'] ? '✅ Attivare' : '❌ Disattivare'; ?>
                </p>
                <p>
                    <strong>Motivo:</strong> <?php echo esc_html($recommendations['service_worker']['reason']); ?>
                </p>
            </div>
        </div>
        <?php
    }
    
    private function renderGenericRecommendations(array $config): void
    {
        ?>
        <div class="fp-ps-card">
            <h2>📋 Raccomandazioni Generali</h2>
            <p>Le seguenti configurazioni sono raccomandate per il tuo tema/builder:</p>
            
            <?php foreach ($config['recommendations'] as $service => $recommendation): ?>
            <div class="fp-ps-recommendation priority-<?php echo $recommendation['priority']; ?>">
                <h3>
                    <?php echo esc_html(ucwords(str_replace('_', ' ', $service))); ?>
                    <span class="priority priority-<?php echo $recommendation['priority']; ?>">
                        Priorità: <?php echo ucfirst($recommendation['priority']); ?>
                    </span>
                </h3>
                <p>
                    <strong>Raccomandazione:</strong> 
                    <?php echo $recommendation['enabled'] ? '✅ Attivare' : '❌ Disattivare'; ?>
                </p>
                <?php if (isset($recommendation['reason'])): ?>
                <p><strong>Motivo:</strong> <?php echo esc_html($recommendation['reason']); ?></p>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
        <?php
    }
    
    public function handlePost(): void
    {
        if (!isset($_POST['fp_ps_compat_nonce']) || 
            !wp_verify_nonce($_POST['fp_ps_compat_nonce'], 'fp_ps_apply_compat')) {
            return;
        }
        
        $action = $_POST['action'] ?? '';
        
        if ($action === 'apply') {
            // Apply recommended configuration
            $this->compat->update([
                'auto_apply' => !empty($_POST['auto_apply']),
            ]);
            
            $this->compat->applyCompatibilityRules();
            
            wp_redirect(admin_url('admin.php?page=fp-performance-compatibility&applied=1'));
            exit;
        }
        
        if ($action === 'save_settings') {
            // Just save settings
            $this->compat->update([
                'auto_apply' => !empty($_POST['auto_apply']),
            ]);
            
            wp_redirect(admin_url('admin.php?page=fp-performance-compatibility&saved=1'));
            exit;
        }
    }
}
