<?php

namespace FP\PerfSuite\Admin\Pages\Assets\Tabs\Components\ScriptDetector;

use FP\PerfSuite\Utils\ErrorHandler;
use FP\PerfSuite\Services\Assets\ThirdPartyScriptManager;

use function home_url;
use function wp_verify_nonce;
use function wp_unslash;
use function wp_safe_redirect;
use function admin_url;
use function set_transient;
use function get_transient;
use function delete_transient;
use function parse_url;
use function PHP_URL_HOST;
use function sanitize_text_field;
use function sanitize_textarea_field;
use function array_filter;
use function array_map;
use function trim;
use function explode;
use function implode;
use function array_unique;
use function array_merge;
use function count;
use function stripos;
use function in_array;
use function esc_html;
use const HOUR_IN_SECONDS;

/**
 * Gestisce le azioni POST del Script Detector
 * 
 * @package FP\PerfSuite\Admin\Pages\Assets\Tabs\Components\ScriptDetector
 * @author Francesco Passeri
 */
class ActionHandler
{
    private ThirdPartyScriptManager $thirdPartyScripts;
    private array $thirdPartySettings;

    public function __construct(ThirdPartyScriptManager $thirdPartyScripts, array &$thirdPartySettings)
    {
        $this->thirdPartyScripts = $thirdPartyScripts;
        $this->thirdPartySettings = &$thirdPartySettings;
    }

    /**
     * Gestisce tutte le azioni POST
     */
    public function handle(): void
    {
        if (!isset($_POST['detector_action']) || !isset($_POST['fp_ps_detector_nonce']) || 
            !wp_verify_nonce(wp_unslash($_POST['fp_ps_detector_nonce']), 'fp-ps-detector')) {
            return;
        }

        $action = $_POST['detector_action'];

        switch ($action) {
            case 'scan':
                $this->handleScan();
                break;
            case 'add_exclusion':
                $this->handleAddExclusion();
                break;
            case 'remove_exclusion':
                $this->handleRemoveExclusion();
                break;
            case 'add_all_exclusions':
                $this->handleAddAllExclusions();
                break;
            case 'quick_add':
                $this->handleQuickAdd();
                break;
            case 'clear_all_exclusions':
                $this->handleClearAllExclusions();
                break;
            case 'clear_all':
            case 'clear_detected':
                $this->handleClearDetected();
                break;
        }
    }

    /**
     * Gestisce la scansione
     */
    private function handleScan(): void
    {
        try {
            $detector = new \FP\PerfSuite\Services\Assets\ThirdPartyScriptDetector($this->thirdPartyScripts);
            $analysis = $detector->analyzePage(home_url());
            
            if (isset($analysis['error'])) {
                echo '<div style="background: #fee; border-left: 4px solid #dc2626; padding: 15px; margin: 20px 0; border-radius: 6px;">';
                echo '<p style="margin: 0; color: #991b1b;"><strong>❌ Errore durante la scansione:</strong> ' . esc_html($analysis['error']) . '</p>';
                echo '<p style="margin: 5px 0 0 0; color: #991b1b; font-size: 13px;">Verifica che la homepage sia accessibile e riprova.</p>';
                echo '</div>';
            } elseif (!empty($analysis['scripts']) && is_array($analysis['scripts'])) {
                $detected = $analysis['scripts'];
                set_transient('fp_ps_detected_scripts', $detected, HOUR_IN_SECONDS);
                
                echo '<div style="background: #d1f2eb; border-left: 4px solid #16a34a; padding: 15px; margin: 20px 0; border-radius: 6px;">';
                echo '<p style="margin: 0; color: #14532d;"><strong>✅ Scansione completata con successo!</strong></p>';
                echo '<p style="margin: 5px 0 0 0; color: #14532d;"><strong>Trovati:</strong> ' . count($detected) . ' script di terze parti</p>';
                if (!empty($analysis['by_category'])) {
                    echo '<p style="margin: 5px 0 0 0; color: #14532d; font-size: 13px;"><strong>Categorie:</strong> ' . count($analysis['by_category']) . '</p>';
                }
                echo '</div>';
            } else {
                echo '<div style="background: #fef3c7; border-left: 4px solid #eab308; padding: 15px; margin: 20px 0; border-radius: 6px;">';
                echo '<p style="margin: 0; color: #713f12;"><strong>⚠️ Nessuno script esterno rilevato.</strong></p>';
                echo '<p style="margin: 5px 0 0 0; color: #713f12; font-size: 13px;">Possibili cause:</p>';
                echo '<ul style="margin: 8px 0 0 20px; color: #713f12; font-size: 13px;">';
                echo '<li>La homepage non ha script di terze parti</li>';
                echo '<li>Gli script sono caricati solo dopo interazione utente</li>';
                echo '<li>Gli script sono in inline (non file esterni)</li>';
                echo '</ul>';
                echo '</div>';
            }
        } catch (\Throwable $e) {
            echo '<div style="background: #fee; border-left: 4px solid #dc2626; padding: 15px; margin: 20px 0; border-radius: 6px;">';
            echo '<p style="margin: 0; color: #991b1b;"><strong>❌ Errore critico:</strong> ' . esc_html($e->getMessage()) . '</p>';
            echo '<p style="margin: 5px 0 0 0; color: #991b1b; font-size: 13px;">Controlla il file debug.log per dettagli.</p>';
            echo '</div>';
            
            ErrorHandler::handleSilently($e, 'Script Detector ActionHandler Error');
        }
    }

    /**
     * Gestisce l'aggiunta di un'esclusione singola
     */
    private function handleAddExclusion(): void
    {
        if (!isset($_POST['script_pattern'])) {
            return;
        }

        $currentSettings = $this->thirdPartyScripts->settings();
        $exclusions = $currentSettings['exclusions'] ?? '';
        $newPattern = sanitize_text_field($_POST['script_pattern']);
        
        if (!empty($newPattern) && stripos($exclusions, $newPattern) === false) {
            $exclusions .= (!empty($exclusions) ? "\n" : '') . $newPattern;
            $this->thirdPartyScripts->updateSettings(['exclusions' => $exclusions]);
            $this->thirdPartySettings = $this->thirdPartyScripts->settings();
            
            echo '<div style="background: #d1f2eb; border-left: 4px solid #16a34a; padding: 15px; margin: 20px 0; border-radius: 6px;">';
            echo '<p style="margin: 0; color: #14532d;"><strong>✅ Pattern aggiunto alle esclusioni:</strong> <code>' . esc_html($newPattern) . '</code></p>';
            echo '<p style="margin: 5px 0 0 0; color: #14532d; font-size: 13px;">Questo script NON verrà più ritardato.</p>';
            echo '</div>';
        }
    }

    /**
     * Gestisce la rimozione di un'esclusione singola
     */
    private function handleRemoveExclusion(): void
    {
        if (!isset($_POST['script_pattern'])) {
            return;
        }

        $currentSettings = $this->thirdPartyScripts->settings();
        $exclusions = $currentSettings['exclusions'] ?? '';
        $patternToRemove = sanitize_text_field($_POST['script_pattern']);
        
        $exclusionsList = array_filter(array_map('trim', explode("\n", $exclusions)));
        $exclusionsList = array_filter($exclusionsList, function($pattern) use ($patternToRemove) {
            return stripos($pattern, $patternToRemove) === false;
        });
        
        $newExclusions = implode("\n", $exclusionsList);
        $this->thirdPartyScripts->updateSettings(['exclusions' => $newExclusions]);
        $this->thirdPartySettings = $this->thirdPartyScripts->settings();
        
        echo '<div style="background: #fee; border-left: 4px solid #dc2626; padding: 15px; margin: 20px 0; border-radius: 6px;">';
        echo '<p style="margin: 0; color: #991b1b;"><strong>⚠️ Pattern rimosso dalle esclusioni:</strong> <code>' . esc_html($patternToRemove) . '</code></p>';
        echo '<p style="margin: 5px 0 0 0; color: #991b1b; font-size: 13px;">Questo script VERRÀ ritardato se la funzionalità è attiva.</p>';
        echo '</div>';
    }

    /**
     * Gestisce l'aggiunta di tutte le esclusioni
     */
    private function handleAddAllExclusions(): void
    {
        $detectedScripts = get_transient('fp_ps_detected_scripts');
        if (empty($detectedScripts)) {
            return;
        }

        $currentSettings = $this->thirdPartyScripts->settings();
        $exclusions = $currentSettings['exclusions'] ?? '';
        $exclusionsList = array_filter(array_map('trim', explode("\n", $exclusions)));
        
        $addedCount = 0;
        foreach ($detectedScripts as $script) {
            if (empty($script['src'])) {
                continue;
            }
            $host = parse_url($script['src'], PHP_URL_HOST);
            if (!empty($host) && is_string($host) && !in_array($host, $exclusionsList, true)) {
                $exclusionsList[] = $host;
                $addedCount++;
            }
        }
        
        if ($addedCount > 0) {
            $newExclusions = implode("\n", $exclusionsList);
            $this->thirdPartyScripts->updateSettings(['exclusions' => $newExclusions]);
            $this->thirdPartySettings = $this->thirdPartyScripts->settings();
            
            echo '<div style="background: #d1f2eb; border-left: 4px solid #16a34a; padding: 15px; margin: 20px 0; border-radius: 6px;">';
            echo '<p style="margin: 0; color: #14532d;"><strong>✅ Aggiunti ' . $addedCount . ' pattern alle esclusioni!</strong></p>';
            echo '<p style="margin: 10px 0 0 0; color: #14532d;">Gli script rilevati NON verranno più ritardati.</p>';
            echo '</div>';
        } else {
            echo '<div style="background: #fef3c7; border-left: 4px solid #eab308; padding: 15px; margin: 20px 0; border-radius: 6px;">';
            echo '<p style="margin: 0; color: #713f12;"><strong>ℹ️ Tutti gli script sono già nelle esclusioni.</strong></p>';
            echo '</div>';
        }
    }

    /**
     * Gestisce il quick add
     */
    private function handleQuickAdd(): void
    {
        if (!isset($_POST['quick_patterns'])) {
            return;
        }

        $currentSettings = $this->thirdPartyScripts->settings();
        $exclusions = $currentSettings['exclusions'] ?? '';
        $newPatterns = sanitize_textarea_field($_POST['quick_patterns']);
        
        $existingList = array_filter(array_map('trim', explode("\n", $exclusions)));
        $newList = array_filter(array_map('trim', explode("\n", $newPatterns)));
        $mergedList = array_unique(array_merge($existingList, $newList));
        
        $newExclusions = implode("\n", $mergedList);
        $this->thirdPartyScripts->updateSettings(['exclusions' => $newExclusions]);
        $this->thirdPartySettings = $this->thirdPartyScripts->settings();
        
        $addedCount = count($mergedList) - count($existingList);
        
        echo '<div style="background: #d1f2eb; border-left: 4px solid #16a34a; padding: 15px; margin: 20px 0; border-radius: 6px;">';
        echo '<p style="margin: 0; color: #14532d;"><strong>✅ Quick Add completato!</strong> Aggiunti ' . $addedCount . ' nuovi pattern.</p>';
        echo '</div>';
    }

    /**
     * Gestisce la rimozione di tutte le esclusioni
     */
    private function handleClearAllExclusions(): void
    {
        $this->thirdPartyScripts->updateSettings(['exclusions' => '']);
        $this->thirdPartySettings = $this->thirdPartyScripts->settings();
        
        echo '<div style="background: #fee; border-left: 4px solid #dc2626; padding: 15px; margin: 20px 0; border-radius: 6px;">';
        echo '<p style="margin: 0; color: #991b1b;"><strong>⚠️ TUTTE le esclusioni sono state rimosse.</strong></p>';
        echo '<p style="margin: 5px 0 0 0; color: #991b1b; font-size: 13px;">Tutti gli script di terze parti verranno ritardati se la funzionalità è attiva.</p>';
        echo '</div>';
    }

    /**
     * Gestisce la cancellazione dei risultati
     */
    private function handleClearDetected(): void
    {
        delete_transient('fp_ps_detected_scripts');
        wp_safe_redirect(admin_url('admin.php?page=fp-performance-suite-assets&tab=thirdparty'));
        exit;
    }
}















