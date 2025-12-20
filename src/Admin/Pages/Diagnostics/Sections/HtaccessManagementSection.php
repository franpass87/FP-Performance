<?php

namespace FP\PerfSuite\Admin\Pages\Diagnostics\Sections;

use FP\PerfSuite\Utils\Htaccess;
use FP\PerfSuite\Utils\Fs;

use function _e;
use function esc_attr;
use function esc_attr_e;
use function esc_html;
use function wp_nonce_field;
use function size_format;

/**
 * Renderizza la sezione Gestione .htaccess
 * 
 * @package FP\PerfSuite\Admin\Pages\Diagnostics\Sections
 * @author Francesco Passeri
 */
class HtaccessManagementSection
{
    /**
     * Renderizza la sezione
     */
    public function render(array $htaccessInfo, array $htaccessBackups): string
    {
        ob_start();
        ?>
        <!-- Informazioni file -->
        <div style="background: #f0f0f1; padding: 15px; margin: 15px 0; border-radius: 4px;">
            <h3 style="margin-top: 0;">📊 <?php _e('Informazioni File', 'fp-performance-suite'); ?></h3>
            <table class="widefat">
                <tbody>
                    <tr>
                        <td style="width: 200px;"><strong><?php _e('Percorso:', 'fp-performance-suite'); ?></strong></td>
                        <td><code><?php echo esc_html($htaccessInfo['path']); ?></code></td>
                    </tr>
                    <tr>
                        <td><strong><?php _e('Scrivibile:', 'fp-performance-suite'); ?></strong></td>
                        <td>
                            <?php if ($htaccessInfo['writable']): ?>
                                <span style="color: green;">✅ <?php _e('Sì', 'fp-performance-suite'); ?></span>
                            <?php else: ?>
                                <span style="color: red;">❌ <?php _e('No', 'fp-performance-suite'); ?></span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <td><strong><?php _e('Dimensione:', 'fp-performance-suite'); ?></strong></td>
                        <td><?php echo esc_html($htaccessInfo['size_formatted']); ?></td>
                    </tr>
                    <tr>
                        <td><strong><?php _e('Ultima modifica:', 'fp-performance-suite'); ?></strong></td>
                        <td><?php echo esc_html($htaccessInfo['modified_formatted'] ?? 'N/A'); ?></td>
                    </tr>
                    <tr>
                        <td><strong><?php _e('Righe totali:', 'fp-performance-suite'); ?></strong></td>
                        <td><?php echo esc_html($htaccessInfo['lines']); ?></td>
                    </tr>
                    <tr>
                        <td><strong><?php _e('Sezioni trovate:', 'fp-performance-suite'); ?></strong></td>
                        <td>
                            <?php if (!empty($htaccessInfo['sections'])): ?>
                                <?php foreach ($htaccessInfo['sections'] as $section): ?>
                                    <span style="background: #2271b1; color: white; padding: 2px 6px; border-radius: 3px; margin-right: 5px; font-size: 11px; display: inline-block; margin-bottom: 3px;">
                                        <?php echo esc_html($section); ?>
                                    </span>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <?php _e('Nessuna sezione', 'fp-performance-suite'); ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Strumenti di validazione e riparazione -->
        <div style="margin: 20px 0;">
            <h3>🔧 <?php _e('Strumenti di Diagnostica e Riparazione', 'fp-performance-suite'); ?></h3>
            <p><?php _e('Usa questi strumenti per verificare e riparare eventuali problemi nel file .htaccess.', 'fp-performance-suite'); ?></p>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 15px; margin-top: 15px;">
                <!-- Valida -->
                <form method="post" style="margin: 0;">
                    <?php wp_nonce_field('fp_ps_diagnostics', 'fp_ps_nonce'); ?>
                    <input type="hidden" name="action" value="validate_htaccess">
                    <button type="submit" class="button button-secondary" style="width: 100%; min-height: 80px; padding: 12px 8px; display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center; line-height: 1.3;">
                        <span style="font-size: 24px; display: block; margin-bottom: 6px;">✓</span>
                        <span style="font-size: 14px; font-weight: 500; white-space: nowrap;"><?php _e('Valida Sintassi', 'fp-performance-suite'); ?></span>
                    </button>
                </form>

                <!-- Ripara -->
                <form method="post" style="margin: 0;">
                    <?php wp_nonce_field('fp_ps_diagnostics', 'fp_ps_nonce'); ?>
                    <input type="hidden" name="action" value="repair_htaccess">
                    <button type="submit" class="button button-primary" style="width: 100%; min-height: 80px; padding: 12px 8px; display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center; line-height: 1.3;">
                        <span style="font-size: 24px; display: block; margin-bottom: 6px;">🔧</span>
                        <span style="font-size: 14px; font-weight: 500; white-space: nowrap;"><?php _e('Ripara Automaticamente', 'fp-performance-suite'); ?></span>
                    </button>
                </form>
            </div>

            <div style="background: #e7f5fe; border-left: 4px solid #00a0d2; padding: 12px; margin: 15px 0; font-size: 13px;">
                <strong>ℹ️ <?php _e('Info:', 'fp-performance-suite'); ?></strong>
                <?php _e('La riparazione automatica crea sempre un backup prima di modificare il file.', 'fp-performance-suite'); ?>
            </div>
        </div>

        <!-- Backup disponibili -->
        <?php if (!empty($htaccessBackups)): ?>
        <div style="margin: 20px 0;">
            <h3>💾 <?php _e('Backup Disponibili', 'fp-performance-suite'); ?></h3>
            <p><?php printf(__('Trovati %d backup del file .htaccess.', 'fp-performance-suite'), count($htaccessBackups)); ?></p>
            
            <table class="widefat" style="margin-top: 15px;">
                <thead>
                    <tr>
                        <th><?php _e('Data Backup', 'fp-performance-suite'); ?></th>
                        <th><?php _e('Dimensione', 'fp-performance-suite'); ?></th>
                        <th><?php _e('Nome File', 'fp-performance-suite'); ?></th>
                        <th><?php _e('Azioni', 'fp-performance-suite'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($htaccessBackups as $backup): ?>
                    <tr>
                        <td><?php echo esc_html($backup['readable_date']); ?></td>
                        <td><?php echo esc_html(size_format($backup['size'])); ?></td>
                        <td><code style="font-size: 11px;"><?php echo esc_html($backup['filename']); ?></code></td>
                        <td>
                            <form method="post" style="display: inline-block; margin-right: 5px;">
                                <?php wp_nonce_field('fp_ps_diagnostics', 'fp_ps_nonce'); ?>
                                <input type="hidden" name="action" value="restore_htaccess">
                                <input type="hidden" name="backup_path" value="<?php echo esc_attr($backup['path']); ?>">
                                <button type="submit" class="button button-small" onclick="return confirm('<?php esc_attr_e('Sei sicuro di voler ripristinare questo backup?', 'fp-performance-suite'); ?>');">
                                    🔄 <?php _e('Ripristina', 'fp-performance-suite'); ?>
                                </button>
                            </form>
                            <form method="post" style="display: inline-block;">
                                <?php wp_nonce_field('fp_ps_diagnostics', 'fp_ps_nonce'); ?>
                                <input type="hidden" name="action" value="delete_htaccess_backup">
                                <input type="hidden" name="backup_path" value="<?php echo esc_attr($backup['path']); ?>">
                                <button type="submit" class="button button-small" onclick="return confirm('<?php esc_attr_e('Sei sicuro di voler eliminare questo backup?', 'fp-performance-suite'); ?>');">
                                    🗑️ <?php _e('Elimina', 'fp-performance-suite'); ?>
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div style="background: #f0f0f1; padding: 15px; margin: 15px 0; border-radius: 4px;">
            <p><strong>💾 <?php _e('Nessun backup disponibile', 'fp-performance-suite'); ?></strong></p>
            <p><?php _e('I backup vengono creati automaticamente quando modifichi il file .htaccess tramite questo plugin.', 'fp-performance-suite'); ?></p>
        </div>
        <?php endif; ?>

        <!-- Reset alle regole WordPress -->
        <div style="margin: 20px 0; padding: 20px; background: #fff3cd; border-left: 4px solid #ff9800; border-radius: 4px;">
            <h3 style="margin-top: 0; color: #d63638;">⚠️ <?php _e('Zona Pericolosa', 'fp-performance-suite'); ?></h3>
            <p><strong><?php _e('Reset alle Regole WordPress Standard', 'fp-performance-suite'); ?></strong></p>
            <p><?php _e('Questa operazione sovrascriverà completamente il file .htaccess con le regole WordPress standard.', 'fp-performance-suite'); ?></p>
            
            <form method="post" style="margin-top: 15px;">
                <?php wp_nonce_field('fp_ps_diagnostics', 'fp_ps_nonce'); ?>
                <input type="hidden" name="action" value="reset_htaccess">
                <label style="display: block; margin-bottom: 10px;">
                    <input type="checkbox" name="confirm_reset" value="1" required>
                    <?php _e('Confermo di voler resettare il file .htaccess', 'fp-performance-suite'); ?>
                </label>
                <button type="submit" class="button button-secondary" style="background: #d63638; border-color: #d63638; color: white;" onclick="return confirm('<?php esc_attr_e('ATTENZIONE! Questa operazione resetterà completamente il file .htaccess. Sei sicuro?', 'fp-performance-suite'); ?>');">
                    🔥 <?php _e('Reset Completo .htaccess', 'fp-performance-suite'); ?>
                </button>
            </form>
        </div>
        <?php
        return ob_get_clean();
    }
}
















