<?php

namespace FP\PerfSuite\Admin\Pages\Assets\Tabs\Components;

use FP\PerfSuite\Services\Assets\ThirdPartyScriptManager;
use FP\PerfSuite\Admin\Pages\Assets\Tabs\Components\ScriptDetector\ActionHandler;
use FP\PerfSuite\Admin\Pages\Assets\Tabs\Components\ScriptDetector\ScriptRenderer;

use function esc_attr;
use function esc_html;
use function esc_html_e;
use function esc_js;
use function esc_textarea;
use function home_url;
use function wp_nonce_field;
use function sanitize_text_field;
use function sanitize_textarea_field;
use function wp_verify_nonce;
use function wp_safe_redirect;
use function admin_url;
use function set_transient;
use function get_transient;
use function delete_transient;
use function parse_url;
use function PHP_URL_HOST;
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
use function error_log;

/**
 * Componente per il rilevatore di script di terze parti
 * 
 * @package FP\PerfSuite\Admin\Pages\Assets\Tabs\Components
 * @author Francesco Passeri
 */
class ScriptDetectorComponent
{
    private ThirdPartyScriptManager $thirdPartyScripts;
    private array $thirdPartySettings;

    public function __construct(ThirdPartyScriptManager $thirdPartyScripts, array $thirdPartySettings)
    {
        $this->thirdPartyScripts = $thirdPartyScripts;
        $this->thirdPartySettings = $thirdPartySettings;
    }

    /**
     * Renderizza il componente completo
     */
    public function render(): string
    {
        // Gestisci tutte le azioni prima del rendering
        $actionHandler = new ActionHandler($this->thirdPartyScripts, $this->thirdPartySettings);
        $actionHandler->handle();

        ob_start();
        ?>
        <!-- Script Detector & Manager -->
        <section class="fp-ps-card fp-ps-mt-xl" style="background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%); border: 2px solid #cbd5e1;">
            <h2 style="margin-top: 0; display: flex; align-items: center; gap: 10px;">
                🔍 <?php esc_html_e('Rilevatore Script di Terze Parti', 'fp-performance-suite'); ?>
                <span style="background: #3b82f6; color: white; padding: 4px 12px; border-radius: 20px; font-size: 14px; font-weight: normal;">
                    AUTO
                </span>
            </h2>
            <p style="color: #475569; font-size: 15px; line-height: 1.6;">
                <?php esc_html_e('Scansiona automaticamente la homepage per rilevare script esterni (Trustindex, chat widget, analytics, ecc.) e gestiscili con un click.', 'fp-performance-suite'); ?>
            </p>
            
            <!-- Quick Add Buttons -->
            <div style="background: white; padding: 20px; border-radius: 8px; margin: 20px 0; border: 2px solid #e2e8f0;">
                <h3 style="margin: 0 0 15px 0; color: #1e293b; font-size: 16px;">
                    ⚡ <?php esc_html_e('Pattern Comuni (Quick Add)', 'fp-performance-suite'); ?>
                </h3>
                <p style="margin: 0 0 15px 0; color: #64748b; font-size: 13px;">
                    <?php esc_html_e('Clicca per aggiungere rapidamente pattern comuni alle esclusioni:', 'fp-performance-suite'); ?>
                </p>
                <div style="display: flex; flex-wrap: wrap; gap: 8px;">
                    <?php
                    $quickPatterns = [
                        'trustindex' => ['label' => '⭐ Trustindex', 'patterns' => "trustindex\nwidget.trustindex.io\ncdn.trustindex.io"],
                        'livechat' => ['label' => '💬 LiveChat', 'patterns' => "livechat\ntawk.to\nzendesk"],
                        'recaptcha' => ['label' => '🔒 reCAPTCHA', 'patterns' => "recaptcha\ngoogle.com/recaptcha"],
                        'calendly' => ['label' => '📅 Calendly', 'patterns' => "calendly\nassets.calendly.com"],
                        'typeform' => ['label' => '📝 Typeform', 'patterns' => "typeform\nembed.typeform.com"],
                        'stripe' => ['label' => '💳 Stripe', 'patterns' => "stripe\njs.stripe.com"],
                        'maps' => ['label' => '🗺️ Google Maps', 'patterns' => "maps.googleapis.com\nmaps.google.com"],
                        'reviews' => ['label' => '⭐ Tutti i Widget Recensioni', 'patterns' => "trustindex\ntripadvisor\ntrustscore\nreviews\nrecensioni"],
                    ];
                    
                    foreach ($quickPatterns as $key => $data):
                    ?>
                    <form method="post" style="margin: 0; display: inline-block;">
                        <?php wp_nonce_field('fp-ps-detector', 'fp_ps_detector_nonce'); ?>
                        <input type="hidden" name="detector_action" value="quick_add" />
                        <input type="hidden" name="quick_patterns" value="<?php echo esc_attr($data['patterns']); ?>" />
                        <button type="submit" class="button" style="background: white; border: 2px solid #cbd5e1; color: #1e293b; font-size: 13px; padding: 6px 12px; border-radius: 6px; cursor: pointer; transition: all 0.2s;">
                            <?php echo $data['label']; ?>
                        </button>
                    </form>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- Scan Button -->
            <form method="post" action="?page=fp-performance-suite-assets&tab=thirdparty">
                <?php wp_nonce_field('fp-ps-detector', 'fp_ps_detector_nonce'); ?>
                <input type="hidden" name="detector_action" value="scan" />
                
                <div style="background: white; padding: 20px; border-radius: 8px; margin: 20px 0; border: 2px solid #3b82f6;">
                    <button type="submit" class="button button-primary button-large" style="font-size: 16px; padding: 10px 24px;">
                        🔍 <?php esc_html_e('Scansiona Homepage Ora', 'fp-performance-suite'); ?>
                    </button>
                    <span style="color: #64748b; font-size: 14px; margin-left: 15px;">
                        <?php esc_html_e('Rileva automaticamente tutti gli script di terze parti caricati sulla homepage', 'fp-performance-suite'); ?>
                    </span>
                </div>
            </form>
            
            <?php
            // Mostra script rilevati
            $detectedScripts = get_transient('fp_ps_detected_scripts');
            if (!empty($detectedScripts)) {
                $scriptRenderer = new ScriptRenderer($this->thirdPartySettings);
                $scriptRenderer->render($detectedScripts);
            } else {
                ?>
                <div style="background: white; padding: 30px; text-align: center; border-radius: 8px; margin: 20px 0; border: 2px dashed #cbd5e1;">
                    <p style="color: #64748b; font-size: 16px; margin: 0;">
                        🔍 <?php esc_html_e('Nessuno script rilevato. Clicca "Scansiona Homepage Ora" per iniziare.', 'fp-performance-suite'); ?>
                    </p>
                </div>
                <?php
            }
            ?>
            
            <div style="background: #fef3c7; border-left: 4px solid #eab308; padding: 15px; margin: 20px 0; border-radius: 6px;">
                <p style="margin: 0; color: #713f12; font-size: 14px;">
                    <strong>⚠️ Importante:</strong>
                    <?php esc_html_e('Il rilevatore analizza la homepage. Se usi script solo su pagine specifiche (es: checkout), potrebbero non essere rilevati. In quel caso, aggiungi manualmente il pattern nella sezione "Script Exclusions" sotto.', 'fp-performance-suite'); ?>
                </p>
            </div>
        </section>
        <?php
        return ob_get_clean();
    }

    // Metodi rimossi - ora gestiti da ActionHandler e ScriptRenderer
    // handleActions() -> ActionHandler::handle()
    // handleScan() -> ActionHandler::handleScan()
    // handleAddExclusion() -> ActionHandler::handleAddExclusion()
    // handleRemoveExclusion() -> ActionHandler::handleRemoveExclusion()
    // handleAddAllExclusions() -> ActionHandler::handleAddAllExclusions()
    // handleQuickAdd() -> ActionHandler::handleQuickAdd()
    // handleClearAllExclusions() -> ActionHandler::handleClearAllExclusions()
    // handleClearDetected() -> ActionHandler::handleClearDetected()
    // renderDetectedScripts() -> ScriptRenderer::render()

    /**
     * Aggiorna le impostazioni correnti (per riflettere le modifiche)
     */
    public function getUpdatedSettings(): array
    {
        return $this->thirdPartySettings;
    }
}


