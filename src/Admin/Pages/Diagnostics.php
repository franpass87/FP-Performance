<?php

/**
 * Diagnostics Page
 * 
 * Pagina per la diagnostica del sistema e risoluzione problemi
 *
 * @package FP\PerfSuite\Admin\Pages
 * @author Francesco Passeri
 */

namespace FP\PerfSuite\Admin\Pages;

use FP\PerfSuite\ServiceContainer;
use FP\PerfSuite\Utils\InstallationRecovery;
use FP\PerfSuite\Utils\Htaccess;
use FP\PerfSuite\Utils\Fs;
use FP\PerfSuite\Utils\ServiceDiagnostics;
use FP\PerfSuite\Admin\Components\PageIntro;
use FP\PerfSuite\Admin\Pages\Diagnostics\FormHandler;
use FP\PerfSuite\Admin\Pages\Diagnostics\Sections\HtaccessManagementSection;

use function __;
use function _e;
use function esc_attr;
use function esc_html;
use function esc_html_e;
use function wp_kses_post;
use function wp_nonce_field;
use function get_option;
use function current_user_can;
use function extension_loaded;
use function get_bloginfo;
use function ini_get;
use function json_encode;
use function JSON_PRETTY_PRINT;
use function JSON_UNESCAPED_UNICODE;

class Diagnostics extends AbstractPage
{
    private array $notices = [];

    public function __construct(ServiceContainer $container)
    {
        parent::__construct($container);
    }

    public function slug(): string
    {
        return 'fp-performance-diagnostics';
    }

    public function capability(): string
    {
        return 'manage_options';
    }

    public function title(): string
    {
        return __('System Diagnostics', 'fp-performance-suite');
    }

    public function view(): string
    {
        return FP_PERF_SUITE_DIR . '/views/admin-page.php';
    }

    protected function data(): array
    {
        return [
            'title' => $this->title(),
            'breadcrumbs' => [
                __('Tools', 'fp-performance-suite'),
                __('Diagnostics', 'fp-performance-suite'),
            ],
        ];
    }

    protected function content(): string
    {
        $noticesHtml = '';
        
        // Handle form submission
        if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
            $formHandler = new FormHandler($this->container);
            $result = $formHandler->handle();
            if (!empty($result)) {
                // Il risultato è già formattato come HTML notice
                $noticesHtml = $result;
            }
        }
        
        $diagnostics = get_option('fp_ps_last_diagnostics');
        $activationError = get_option('fp_perfsuite_activation_error');
        
        // Render notices legacy se presenti (per compatibilità)
        if (!empty($this->notices)) {
            ob_start();
            $this->renderNotices();
            $noticesHtml .= ob_get_clean();
        }
        
        ob_start();
        ?>
        
        <?php
        // Intro Box con PageIntro Component
        echo PageIntro::render(
            '🔧',
            __('System Diagnostics', 'fp-performance-suite'),
            __('Diagnostica completa del sistema, verifica dello stato dei servizi, recupero da errori e strumenti di risoluzione problemi.', 'fp-performance-suite')
        );
        ?>
        
        <?php echo $noticesHtml; ?>
        
        <!-- Service Status Section -->
        <section class="fp-ps-card">
            <h2>⚙️ <?php _e('Stato Servizi Plugin', 'fp-performance-suite'); ?></h2>
            <p class="description">
                <?php _e('Verifica rapidamente quali servizi del plugin sono attivi e funzionanti.', 'fp-performance-suite'); ?>
            </p>
            
            <?php
            $serviceStatus = ServiceDiagnostics::checkAllServices();
            $totalServices = 0;
            $activeServices = 0;
            
            foreach ($serviceStatus as $category => $services) {
                foreach ($services as $service) {
                    $totalServices++;
                    if (!empty($service['enabled'])) {
                        $activeServices++;
                    }
                }
            }
            ?>
            
            <div style="background: #f0f9ff; border-left: 4px solid #0ea5e9; padding: 15px; margin: 15px 0; border-radius: 4px;">
                <strong>📊 Riepilogo:</strong>
                <?php printf(__('%d servizi attivi su %d totali', 'fp-performance-suite'), $activeServices, $totalServices); ?>
            </div>
            
            <details style="margin-top: 20px;">
                <summary style="cursor: pointer; padding: 10px; background: #f0f0f1; border-radius: 4px; font-weight: 600;">
                    🔍 <?php _e('Mostra Dettagli Servizi', 'fp-performance-suite'); ?>
                </summary>
                <div style="margin-top: 15px; padding: 15px; background: white; border: 1px solid #ddd; border-radius: 4px;">
                    <?php echo ServiceDiagnostics::generateHtmlReport(); ?>
                </div>
            </details>
        </section>
        
        <!-- Diagnostic Section -->
        <section class="fp-ps-card">
            <h2>🔍 <?php _e('Diagnostica di Sistema', 'fp-performance-suite'); ?></h2>
            <p class="description">
                <?php _e('Esegui una diagnostica completa del sistema per identificare eventuali problemi di configurazione o incompatibilità.', 'fp-performance-suite'); ?>
            </p>
            
            <form method="post" style="margin-top: 20px;">
                <?php wp_nonce_field('fp_ps_diagnostics', 'fp_ps_nonce'); ?>
                <input type="hidden" name="action" value="run_diagnostics">
                <button type="submit" class="button button-primary button-large">
                    🔍 <?php _e('Esegui Diagnostica', 'fp-performance-suite'); ?>
                </button>
            </form>

            <?php if (!empty($diagnostics)): ?>
            <div style="margin-top: 30px;">
                <h3><?php _e('Ultimo Report Diagnostico', 'fp-performance-suite'); ?></h3>
                <?php echo InstallationRecovery::generateDiagnosticReport($diagnostics); ?>
                
                <div style="margin-top: 20px;">
                    <h4><?php _e('Dettagli Completi:', 'fp-performance-suite'); ?></h4>
                    <pre style="background: #f0f0f1; padding: 15px; overflow: auto; max-height: 400px; font-size: 12px; border-radius: 4px;"><?php echo esc_html(json_encode($diagnostics, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)); ?></pre>
                </div>
            </div>
            <?php endif; ?>
        </section>

        <?php if (!empty($activationError)): ?>
        <!-- Activation Error Section -->
        <section class="fp-ps-card">
            <h2>⚠️ <?php _e('Errore di Attivazione Rilevato', 'fp-performance-suite'); ?></h2>
            <p class="description">
                <?php _e('È stato rilevato un errore durante l\'ultima attivazione del plugin.', 'fp-performance-suite'); ?>
            </p>
            
            <div style="background: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 15px 0;">
                <p><strong><?php _e('Messaggio:', 'fp-performance-suite'); ?></strong></p>
                <p><?php echo esc_html($activationError['message'] ?? 'N/A'); ?></p>
                
                <?php if (!empty($activationError['solution'])): ?>
                <p style="margin-top: 15px;"><strong><?php _e('Soluzione Suggerita:', 'fp-performance-suite'); ?></strong></p>
                <p><?php echo esc_html($activationError['solution']); ?></p>
                <?php endif; ?>
            </div>

            <form method="post" style="margin-top: 20px;">
                <?php wp_nonce_field('fp_ps_diagnostics', 'fp_ps_nonce'); ?>
                <input type="hidden" name="action" value="clear_error">
                <button type="submit" class="button">
                    🗑️ <?php _e('Cancella Errore', 'fp-performance-suite'); ?>
                </button>
            </form>
        </section>
        <?php endif; ?>

        <!-- Recovery Tools Section -->
        <section class="fp-ps-card">
            <h2>🔧 <?php _e('Strumenti di Recupero', 'fp-performance-suite'); ?></h2>
            <p class="description">
                <?php _e('Utilizza questi strumenti per tentare di risolvere automaticamente problemi comuni.', 'fp-performance-suite'); ?>
            </p>

            <form method="post" style="margin-top: 20px;">
                <?php wp_nonce_field('fp_ps_diagnostics', 'fp_ps_nonce'); ?>
                <input type="hidden" name="action" value="fix_permissions">
                <button type="submit" class="button button-secondary">
                    🔧 <?php _e('Ripara Permessi Directory', 'fp-performance-suite'); ?>
                </button>
            </form>
        </section>

        <!-- System Information Section -->
        <section class="fp-ps-card">
            <h2>ℹ️ <?php _e('Informazioni di Sistema', 'fp-performance-suite'); ?></h2>
            <table class="widefat" style="margin-top: 15px;">
                <tbody>
                    <tr>
                        <td style="width: 200px;"><strong><?php _e('Versione PHP:', 'fp-performance-suite'); ?></strong></td>
                        <td><?php echo esc_html(PHP_VERSION); ?></td>
                    </tr>
                    <tr>
                        <td><strong><?php _e('Versione WordPress:', 'fp-performance-suite'); ?></strong></td>
                        <td><?php echo esc_html(get_bloginfo('version')); ?></td>
                    </tr>
                    <tr>
                        <td><strong><?php _e('Versione Plugin:', 'fp-performance-suite'); ?></strong></td>
                        <td><?php echo esc_html(defined('FP_PERF_SUITE_VERSION') ? FP_PERF_SUITE_VERSION : 'N/A'); ?></td>
                    </tr>
                    <tr>
                        <td><strong><?php _e('Memory Limit:', 'fp-performance-suite'); ?></strong></td>
                        <td><?php echo esc_html(ini_get('memory_limit')); ?></td>
                    </tr>
                    <tr>
                        <td><strong><?php _e('Max Execution Time:', 'fp-performance-suite'); ?></strong></td>
                        <td><?php echo esc_html(ini_get('max_execution_time')); ?>s</td>
                    </tr>
                    <tr>
                        <td><strong><?php _e('Upload Max Filesize:', 'fp-performance-suite'); ?></strong></td>
                        <td><?php echo esc_html(ini_get('upload_max_filesize')); ?></td>
                    </tr>
                    <tr>
                        <td><strong><?php _e('Post Max Size:', 'fp-performance-suite'); ?></strong></td>
                        <td><?php echo esc_html(ini_get('post_max_size')); ?></td>
                    </tr>
                    <tr>
                        <td><strong><?php _e('Server Software:', 'fp-performance-suite'); ?></strong></td>
                        <td><?php echo esc_html($_SERVER['SERVER_SOFTWARE'] ?? 'N/A'); ?></td>
                    </tr>
                    <tr>
                        <td><strong><?php _e('Sistema Operativo:', 'fp-performance-suite'); ?></strong></td>
                        <td><?php echo esc_html(PHP_OS); ?></td>
                    </tr>
                </tbody>
            </table>
        </section>

        <!-- PHP Extensions Section -->
        <section class="fp-ps-card">
            <h2>📦 <?php _e('Estensioni PHP', 'fp-performance-suite'); ?></h2>
            <?php
            $requiredExtensions = ['json', 'mbstring', 'fileinfo', 'gd', 'imagick', 'curl', 'zip'];
            ?>
            <table class="widefat" style="margin-top: 15px;">
                <thead>
                    <tr>
                        <th><?php _e('Estensione', 'fp-performance-suite'); ?></th>
                        <th><?php _e('Stato', 'fp-performance-suite'); ?></th>
                        <th><?php _e('Note', 'fp-performance-suite'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($requiredExtensions as $ext): ?>
                    <tr>
                        <td><code><?php echo esc_html($ext); ?></code></td>
                        <td>
                            <?php if (extension_loaded($ext)): ?>
                                <span style="color: green;">✅ <?php _e('Attiva', 'fp-performance-suite'); ?></span>
                            <?php else: ?>
                                <span style="color: red;">❌ <?php _e('Non Attiva', 'fp-performance-suite'); ?></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php
                            $notes = [
                                'json' => __('Richiesta - Gestione dati JSON', 'fp-performance-suite'),
                                'mbstring' => __('Richiesta - Manipolazione stringhe multibyte', 'fp-performance-suite'),
                                'fileinfo' => __('Richiesta - Rilevamento tipo file', 'fp-performance-suite'),
                                'gd' => __('Consigliata - Elaborazione immagini', 'fp-performance-suite'),
                                'imagick' => __('Consigliata - Conversione immagini avanzata', 'fp-performance-suite'),
                                'curl' => __('Consigliata - Chiamate HTTP', 'fp-performance-suite'),
                                'zip' => __('Opzionale - Gestione archivi', 'fp-performance-suite'),
                            ];
                            echo esc_html($notes[$ext] ?? '');
                            ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>

        <?php
        // Sezione gestione .htaccess
        $htaccess = new Htaccess(new Fs());
        $htaccessInfo = $htaccess->getFileInfo();
        $htaccessBackups = $htaccess->getBackups();
        ?>

        <!-- .htaccess Management Section -->
        <section class="fp-ps-card">
            <h2>📝 <?php _e('Gestione File .htaccess', 'fp-performance-suite'); ?></h2>
            
            <?php if (!$htaccessInfo['exists']): ?>
                <div style="background: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 15px 0;">
                    <p><strong>⚠️ <?php _e('File .htaccess non trovato', 'fp-performance-suite'); ?></strong></p>
                    <p><?php _e('Il file .htaccess non esiste nella directory root di WordPress. Questo è normale se usi server Nginx.', 'fp-performance-suite'); ?></p>
                </div>
            <?php else: ?>
                <?php 
                $htaccessSection = new HtaccessManagementSection();
                echo $htaccessSection->render($htaccessInfo, $htaccessBackups); 
                ?>
            <?php endif; ?>
        </section>
        
        <?php
        return ob_get_clean();
    }

    /**
     * Aggiunge un messaggio di notifica
     */
    private function addNotice(string $type, string $message): void
    {
        $this->notices[] = [
            'type' => $type,
            'message' => $message
        ];
    }

    /**
     * Renderizza i messaggi di notifica
     */
    private function renderNotices(): void
    {
        foreach ($this->notices as $notice) {
            $class = 'notice notice-' . esc_attr($notice['type']);
            echo '<div class="' . $class . ' is-dismissible"><p>' . wp_kses_post($notice['message']) . '</p></div>';
        }
    }
    
    // Metodi handleActions(), runDiagnostics(), fixPermissions(), clearActivationError(),
    // validateHtaccess(), repairHtaccess(), restoreHtaccess(), resetHtaccess(), 
    // deleteHtaccessBackup(), renderHtaccessManagement() rimossi - ora gestiti da 
    // Diagnostics/FormHandler e Diagnostics/Sections/HtaccessManagementSection
}
