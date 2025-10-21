<?php

namespace FP\PerfSuite\Admin\Pages;

use FP\PerfSuite\Services\Security\HtaccessSecurity;

use function __;
use function esc_attr;
use function esc_attr_e;
use function esc_html;
use function esc_html_e;
use function esc_url;
use function esc_textarea;
use function get_option;
use function sanitize_text_field;
use function selected;
use function checked;
use function update_option;
use function wp_nonce_field;
use function wp_unslash;
use function wp_verify_nonce;
use function array_map;
use function admin_url;

class Security extends AbstractPage
{
    public function slug(): string
    {
        return 'fp-performance-suite-security';
    }

    public function title(): string
    {
        return __('Security & .htaccess', 'fp-performance-suite');
    }

    public function capability(): string
    {
        return 'manage_options';
    }

    public function view(): string
    {
        return FP_PERF_SUITE_DIR . '/views/admin-page.php';
    }

    protected function data(): array
    {
        return [
            'title' => $this->title(),
            'breadcrumbs' => [__('Sicurezza', 'fp-performance-suite')],
        ];
    }

    protected function content(): string
    {
        /** @var HtaccessSecurity $securityService */
        $securityService = $this->container->get(HtaccessSecurity::class);
        $settings = $securityService->settings();
        $status = $securityService->status();
        
        $message = '';
        
        if ('POST' === $_SERVER['REQUEST_METHOD'] && isset($_POST['fp_ps_security_nonce']) && wp_verify_nonce(wp_unslash($_POST['fp_ps_security_nonce']), 'fp-ps-security')) {
            
            $newSettings = [
                'enabled' => !empty($_POST['enabled']),
                'canonical_redirect' => [
                    'enabled' => !empty($_POST['canonical_redirect_enabled']),
                    'force_https' => !empty($_POST['force_https']),
                    'force_www' => !empty($_POST['force_www']),
                    'domain' => sanitize_text_field(wp_unslash($_POST['domain'] ?? '')),
                ],
                'security_headers' => [
                    'enabled' => !empty($_POST['security_headers_enabled']),
                    'hsts' => !empty($_POST['hsts']),
                    'hsts_max_age' => max(0, (int)($_POST['hsts_max_age'] ?? 31536000)),
                    'hsts_subdomains' => !empty($_POST['hsts_subdomains']),
                    'hsts_preload' => !empty($_POST['hsts_preload']),
                    'x_content_type_options' => !empty($_POST['x_content_type_options']),
                    'x_frame_options' => sanitize_text_field($_POST['x_frame_options'] ?? 'SAMEORIGIN'),
                    'referrer_policy' => sanitize_text_field($_POST['referrer_policy'] ?? 'strict-origin-when-cross-origin'),
                    'permissions_policy' => sanitize_text_field(wp_unslash($_POST['permissions_policy'] ?? '')),
                ],
                'cache_rules' => [
                    'enabled' => !empty($_POST['cache_rules_enabled']),
                    'html_cache' => !empty($_POST['html_cache']),
                    'fonts_cache' => !empty($_POST['fonts_cache']),
                    'fonts_max_age' => max(0, (int)($_POST['fonts_max_age'] ?? 31536000)),
                    'images_max_age' => max(0, (int)($_POST['images_max_age'] ?? 31536000)),
                    'css_js_max_age' => max(0, (int)($_POST['css_js_max_age'] ?? 2592000)),
                ],
                'compression' => [
                    'deflate_enabled' => !empty($_POST['deflate_enabled']),
                    'brotli_enabled' => !empty($_POST['brotli_enabled']),
                    'brotli_quality' => max(1, min(11, (int)($_POST['brotli_quality'] ?? 5))),
                ],
                'cors' => [
                    'enabled' => !empty($_POST['cors_enabled']),
                    'fonts_origin' => sanitize_text_field($_POST['fonts_origin'] ?? '*'),
                    'svg_origin' => sanitize_text_field($_POST['svg_origin'] ?? '*'),
                ],
                'file_protection' => [
                    'enabled' => !empty($_POST['file_protection_enabled']),
                    'protect_hidden_files' => !empty($_POST['protect_hidden_files']),
                    'protect_wp_config' => !empty($_POST['protect_wp_config']),
                ],
                'xmlrpc_disabled' => !empty($_POST['xmlrpc_disabled']),
                'hotlink_protection' => [
                    'enabled' => !empty($_POST['hotlink_protection_enabled']),
                    'allowed_domains' => array_map('sanitize_text_field', array_filter(array_map('trim', explode("\n", wp_unslash($_POST['hotlink_allowed_domains'] ?? ''))))),
                    'allow_google' => !empty($_POST['hotlink_allow_google']),
                ],
            ];
            
            $securityService->update($newSettings);
            $settings = $securityService->settings();
            $status = $securityService->status();
            
            $message = __('Security settings saved successfully!', 'fp-performance-suite');
        }
        
        // Tab system
        $validTabs = ['security', 'performance'];
        $currentTab = 'performance';
        
        // Mantieni il tab dopo il POST
        if ('POST' === $_SERVER['REQUEST_METHOD'] && !empty($_POST['current_tab']) && in_array($_POST['current_tab'], $validTabs, true)) {
            $currentTab = sanitize_key($_POST['current_tab']);
        } elseif (isset($_GET['tab']) && in_array($_GET['tab'], $validTabs, true)) {
            $currentTab = sanitize_key($_GET['tab']);
        }
        
        ob_start();
        ?>
        
        <?php if ($message) : ?>
            <div class="notice notice-success is-dismissible"><p><?php echo esc_html($message); ?></p></div>
        <?php endif; ?>
        
        <!-- Tab Navigation -->
        <nav class="nav-tab-wrapper wp-clearfix" style="margin-bottom: 20px;">
            <a href="?page=<?php echo esc_attr($this->slug()); ?>&tab=performance" 
               class="nav-tab <?php echo $currentTab === 'performance' ? 'nav-tab-active' : ''; ?>">
                ⚡ <?php esc_html_e('.htaccess Performance', 'fp-performance-suite'); ?>
            </a>
            <a href="?page=<?php echo esc_attr($this->slug()); ?>&tab=security" 
               class="nav-tab <?php echo $currentTab === 'security' ? 'nav-tab-active' : ''; ?>">
                🛡️ <?php esc_html_e('Security & Protection', 'fp-performance-suite'); ?>
            </a>
        </nav>
        
        <form method="post">
            <?php wp_nonce_field('fp-ps-security', 'fp_ps_security_nonce'); ?>
            <input type="hidden" name="current_tab" value="<?php echo esc_attr($currentTab); ?>" />
        
        <div style="background: #e7f5ff; border-left: 4px solid #2271b1; padding: 15px; margin-bottom: 20px;">
            <p style="margin: 0;">
                <strong>💡 <?php esc_html_e('Informazioni:', 'fp-performance-suite'); ?></strong>
                <?php esc_html_e('Queste impostazioni modificano il file .htaccess per migliorare sicurezza e performance. Viene creato un backup automatico prima di ogni modifica.', 'fp-performance-suite'); ?>
            </p>
        </div>
        
        <?php if ($status['section_applied']) : ?>
        <div class="notice notice-success inline" style="margin: 0 0 20px 0;">
            <p>
                <span class="dashicons dashicons-yes-alt" style="color: #00a32a;"></span>
                <strong><?php esc_html_e('Stato: Attivo', 'fp-performance-suite'); ?></strong> -
                <?php esc_html_e('Le regole di sicurezza sono state applicate al file .htaccess', 'fp-performance-suite'); ?>
            </p>
        </div>
        <?php endif; ?>
        
        <!-- Tab: .htaccess Performance -->
        <div class="tab-content" style="<?php echo $currentTab !== 'performance' ? 'display:none;' : ''; ?>">
            
            <!-- Master Switch -->
            <section class="fp-ps-card">
                <h2><?php esc_html_e('Stato Generale', 'fp-performance-suite'); ?></h2>
                <label class="fp-ps-toggle">
                    <span class="info">
                        <strong><?php esc_html_e('Abilita Ottimizzazioni .htaccess', 'fp-performance-suite'); ?></strong>
                        <span class="fp-ps-risk-indicator green">
                            <div class="fp-ps-risk-tooltip green">
                                <div class="fp-ps-risk-tooltip-title">
                                    <span class="icon">✓</span>
                                    <?php esc_html_e('Rischio Basso', 'fp-performance-suite'); ?>
                                </div>
                                <div class="fp-ps-risk-tooltip-section">
                                    <div class="fp-ps-risk-tooltip-label"><?php esc_html_e('Descrizione', 'fp-performance-suite'); ?></div>
                                    <div class="fp-ps-risk-tooltip-text"><?php esc_html_e('Attiva tutte le ottimizzazioni di sicurezza e performance via .htaccess.', 'fp-performance-suite'); ?></div>
                                </div>
                            </div>
                        </span>
                    </span>
                    <input type="checkbox" name="enabled" value="1" <?php checked($settings['enabled']); ?> />
                </label>
            </section>
            
            <!-- 1. Redirect Canonico -->
            <section class="fp-ps-card">
                <h2>🔄 <?php esc_html_e('Redirect Canonico (HTTPS + WWW)', 'fp-performance-suite'); ?></h2>
                <p class="description"><?php esc_html_e('Unifica HTTP/HTTPS e WWW/non-WWW in un singolo redirect 301 per evitare loop e migliorare SEO.', 'fp-performance-suite'); ?></p>
                
                <label class="fp-ps-toggle">
                    <span class="info">
                        <strong><?php esc_html_e('Abilita Redirect Canonico', 'fp-performance-suite'); ?></strong>
                    </span>
                    <input type="checkbox" name="canonical_redirect_enabled" value="1" <?php checked($settings['canonical_redirect']['enabled']); ?> />
                </label>
                
                <div style="margin-left: 20px; margin-top: 15px;">
                    <p>
                        <label>
                            <input type="checkbox" name="force_https" value="1" <?php checked($settings['canonical_redirect']['force_https']); ?> />
                            <?php esc_html_e('Forza HTTPS', 'fp-performance-suite'); ?>
                        </label>
                        <span class="description" style="display: block; margin-left: 24px;"><?php esc_html_e('Reindirizza automaticamente HTTP → HTTPS', 'fp-performance-suite'); ?></span>
                    </p>
                    
                    <p>
                        <label>
                            <input type="checkbox" name="force_www" value="1" <?php checked($settings['canonical_redirect']['force_www']); ?> />
                            <?php esc_html_e('Forza WWW', 'fp-performance-suite'); ?>
                        </label>
                        <span class="description" style="display: block; margin-left: 24px;"><?php esc_html_e('Reindirizza automaticamente non-WWW → WWW (esempio.com → www.esempio.com)', 'fp-performance-suite'); ?></span>
                    </p>
                    
                    <p>
                        <label for="domain"><?php esc_html_e('Dominio', 'fp-performance-suite'); ?></label><br>
                        <input type="text" name="domain" id="domain" value="<?php echo esc_attr($settings['canonical_redirect']['domain']); ?>" class="regular-text" placeholder="esempio.com">
                        <span class="description"><?php esc_html_e('Il tuo dominio senza www (es: esempio.com)', 'fp-performance-suite'); ?></span>
                    </p>
                </div>
                
                <div style="background: #fff3cd; border-left: 4px solid #ffc107; padding: 10px; margin-top: 15px;">
                    <strong>✅ <?php esc_html_e('Beneficio:', 'fp-performance-suite'); ?></strong>
                    <?php esc_html_e('Evita redirect multipli che rallentano il caricamento e causano loop infiniti.', 'fp-performance-suite'); ?>
                </div>
            </section>
            
            <!-- 2. Security Headers -->
            <section class="fp-ps-card">
                <h2>🛡️ <?php esc_html_e('Security Headers', 'fp-performance-suite'); ?></h2>
                <p class="description"><?php esc_html_e('Migliora il punteggio di sicurezza e proteggi da attacchi comuni (XSS, clickjacking, MIME sniffing).', 'fp-performance-suite'); ?></p>
                
                <label class="fp-ps-toggle">
                    <span class="info">
                        <strong><?php esc_html_e('Abilita Security Headers', 'fp-performance-suite'); ?></strong>
                    </span>
                    <input type="checkbox" name="security_headers_enabled" value="1" <?php checked($settings['security_headers']['enabled']); ?> />
                </label>
                
                <div style="margin-left: 20px; margin-top: 15px;">
                    <!-- HSTS -->
                    <h3><?php esc_html_e('HSTS (HTTP Strict Transport Security)', 'fp-performance-suite'); ?></h3>
                    <p>
                        <label>
                            <input type="checkbox" name="hsts" value="1" <?php checked($settings['security_headers']['hsts']); ?> />
                            <?php esc_html_e('Abilita HSTS', 'fp-performance-suite'); ?>
                        </label>
                    </p>
                    <p>
                        <label for="hsts_max_age"><?php esc_html_e('Max Age (secondi)', 'fp-performance-suite'); ?></label><br>
                        <input type="number" name="hsts_max_age" id="hsts_max_age" value="<?php echo esc_attr($settings['security_headers']['hsts_max_age']); ?>" min="0" class="small-text">
                        <span class="description"><?php esc_html_e('(default: 31536000 = 1 anno)', 'fp-performance-suite'); ?></span>
                    </p>
                    <p>
                        <label>
                            <input type="checkbox" name="hsts_subdomains" value="1" <?php checked($settings['security_headers']['hsts_subdomains']); ?> />
                            <?php esc_html_e('Includi sottodomini', 'fp-performance-suite'); ?>
                        </label>
                    </p>
                    <p>
                        <label>
                            <input type="checkbox" name="hsts_preload" value="1" <?php checked($settings['security_headers']['hsts_preload']); ?> />
                            <?php esc_html_e('Preload', 'fp-performance-suite'); ?>
                        </label>
                        <span class="description" style="display: block; margin-left: 24px; color: #d63638;"><?php esc_html_e('⚠️ Attiva solo se tutti i sottodomini supportano HTTPS!', 'fp-performance-suite'); ?></span>
                    </p>
                    
                    <hr style="margin: 20px 0;">
                    
                    <!-- Altri Header -->
                    <p>
                        <label>
                            <input type="checkbox" name="x_content_type_options" value="1" <?php checked($settings['security_headers']['x_content_type_options']); ?> />
                            <?php esc_html_e('X-Content-Type-Options: nosniff', 'fp-performance-suite'); ?>
                        </label>
                        <span class="description" style="display: block; margin-left: 24px;"><?php esc_html_e('Previene MIME type sniffing', 'fp-performance-suite'); ?></span>
                    </p>
                    
                    <p>
                        <label for="x_frame_options"><?php esc_html_e('X-Frame-Options', 'fp-performance-suite'); ?></label><br>
                        <select name="x_frame_options" id="x_frame_options">
                            <option value="SAMEORIGIN" <?php selected($settings['security_headers']['x_frame_options'], 'SAMEORIGIN'); ?>>SAMEORIGIN</option>
                            <option value="DENY" <?php selected($settings['security_headers']['x_frame_options'], 'DENY'); ?>>DENY</option>
                        </select>
                        <span class="description"><?php esc_html_e('Protegge da clickjacking', 'fp-performance-suite'); ?></span>
                    </p>
                    
                    <p>
                        <label for="referrer_policy"><?php esc_html_e('Referrer-Policy', 'fp-performance-suite'); ?></label><br>
                        <input type="text" name="referrer_policy" id="referrer_policy" value="<?php echo esc_attr($settings['security_headers']['referrer_policy']); ?>" class="regular-text">
                        <span class="description"><?php esc_html_e('(default: strict-origin-when-cross-origin)', 'fp-performance-suite'); ?></span>
                    </p>
                    
                    <p>
                        <label for="permissions_policy"><?php esc_html_e('Permissions-Policy', 'fp-performance-suite'); ?></label><br>
                        <input type="text" name="permissions_policy" id="permissions_policy" value="<?php echo esc_attr($settings['security_headers']['permissions_policy']); ?>" class="large-text">
                        <span class="description"><?php esc_html_e('(default: camera=(), microphone=(), geolocation=())', 'fp-performance-suite'); ?></span>
                    </p>
                </div>
            </section>
            
            <!-- 3. Cache Rules -->
            <section class="fp-ps-card">
                <h2>⏱️ <?php esc_html_e('Regole di Cache Ottimizzate', 'fp-performance-suite'); ?></h2>
                <p class="description"><?php esc_html_e('Definisci tempi di cache precisi per HTML, font, immagini, CSS/JS.', 'fp-performance-suite'); ?></p>
                
                <label class="fp-ps-toggle">
                    <span class="info">
                        <strong><?php esc_html_e('Abilita Cache Rules', 'fp-performance-suite'); ?></strong>
                    </span>
                    <input type="checkbox" name="cache_rules_enabled" value="1" <?php checked($settings['cache_rules']['enabled']); ?> />
                </label>
                
                <div style="margin-left: 20px; margin-top: 15px;">
                    <p>
                        <label>
                            <input type="checkbox" name="html_cache" value="1" <?php checked($settings['cache_rules']['html_cache']); ?> />
                            <?php esc_html_e('Cache HTML', 'fp-performance-suite'); ?>
                        </label>
                        <span class="description" style="display: block; margin-left: 24px; color: #d63638;"><?php esc_html_e('❌ Sconsigliato: meglio no-cache per contenuti dinamici', 'fp-performance-suite'); ?></span>
                    </p>
                    
                    <p>
                        <label>
                            <input type="checkbox" name="fonts_cache" value="1" <?php checked($settings['cache_rules']['fonts_cache']); ?> />
                            <?php esc_html_e('Cache Font (woff2, woff, ttf, otf)', 'fp-performance-suite'); ?>
                        </label>
                    </p>
                    <p style="margin-left: 24px;">
                        <label for="fonts_max_age"><?php esc_html_e('Durata cache font (secondi)', 'fp-performance-suite'); ?></label><br>
                        <input type="number" name="fonts_max_age" id="fonts_max_age" value="<?php echo esc_attr($settings['cache_rules']['fonts_max_age']); ?>" min="0" class="small-text">
                        <span class="description"><?php esc_html_e('(default: 31536000 = 1 anno)', 'fp-performance-suite'); ?></span>
                    </p>
                    
                    <p style="margin-left: 24px;">
                        <label for="images_max_age"><?php esc_html_e('Durata cache immagini (secondi)', 'fp-performance-suite'); ?></label><br>
                        <input type="number" name="images_max_age" id="images_max_age" value="<?php echo esc_attr($settings['cache_rules']['images_max_age']); ?>" min="0" class="small-text">
                        <span class="description"><?php esc_html_e('(default: 31536000 = 1 anno)', 'fp-performance-suite'); ?></span>
                    </p>
                    
                    <p style="margin-left: 24px;">
                        <label for="css_js_max_age"><?php esc_html_e('Durata cache CSS/JS (secondi)', 'fp-performance-suite'); ?></label><br>
                        <input type="number" name="css_js_max_age" id="css_js_max_age" value="<?php echo esc_attr($settings['cache_rules']['css_js_max_age']); ?>" min="0" class="small-text">
                        <span class="description"><?php esc_html_e('(default: 2592000 = 1 mese)', 'fp-performance-suite'); ?></span>
                    </p>
                </div>
            </section>
            
            <!-- 4. Compressione -->
            <section class="fp-ps-card">
                <h2>📦 <?php esc_html_e('Compressione (Brotli + Deflate)', 'fp-performance-suite'); ?></h2>
                <p class="description"><?php esc_html_e('Riduci la dimensione dei file trasferiti. Brotli è più efficiente di Deflate (gzip).', 'fp-performance-suite'); ?></p>
                
                <p>
                    <label>
                        <input type="checkbox" name="brotli_enabled" value="1" <?php checked($settings['compression']['brotli_enabled']); ?> />
                        <?php esc_html_e('Abilita Brotli', 'fp-performance-suite'); ?>
                    </label>
                    <span class="description" style="display: block; margin-left: 24px;"><?php esc_html_e('Richiede mod_brotli sul server', 'fp-performance-suite'); ?></span>
                </p>
                <p style="margin-left: 24px;">
                    <label for="brotli_quality"><?php esc_html_e('Qualità Brotli (1-11)', 'fp-performance-suite'); ?></label><br>
                    <input type="number" name="brotli_quality" id="brotli_quality" value="<?php echo esc_attr($settings['compression']['brotli_quality']); ?>" min="1" max="11" class="small-text">
                    <span class="description"><?php esc_html_e('(default: 5, più alto = migliore compressione ma più lento)', 'fp-performance-suite'); ?></span>
                </p>
                
                <p>
                    <label>
                        <input type="checkbox" name="deflate_enabled" value="1" <?php checked($settings['compression']['deflate_enabled']); ?> />
                        <?php esc_html_e('Abilita Deflate (gzip)', 'fp-performance-suite'); ?>
                    </label>
                    <span class="description" style="display: block; margin-left: 24px;"><?php esc_html_e('Fallback compatibile per tutti i browser', 'fp-performance-suite'); ?></span>
                </p>
            </section>
            
            <!-- 5. CORS -->
            <section class="fp-ps-card">
                <h2>🌐 <?php esc_html_e('CORS Headers (Font/SVG)', 'fp-performance-suite'); ?></h2>
                <p class="description"><?php esc_html_e('Permette il caricamento di font e SVG da CDN o sottodomini.', 'fp-performance-suite'); ?></p>
                
                <label class="fp-ps-toggle">
                    <span class="info">
                        <strong><?php esc_html_e('Abilita CORS', 'fp-performance-suite'); ?></strong>
                    </span>
                    <input type="checkbox" name="cors_enabled" value="1" <?php checked($settings['cors']['enabled']); ?> />
                </label>
                
                <div style="margin-left: 20px; margin-top: 15px;">
                    <p>
                        <label for="fonts_origin"><?php esc_html_e('Origin permessa per font', 'fp-performance-suite'); ?></label><br>
                        <input type="text" name="fonts_origin" id="fonts_origin" value="<?php echo esc_attr($settings['cors']['fonts_origin']); ?>" class="regular-text" placeholder="*">
                        <span class="description"><?php esc_html_e('(* = tutti, oppure dominio specifico)', 'fp-performance-suite'); ?></span>
                    </p>
                    
                    <p>
                        <label for="svg_origin"><?php esc_html_e('Origin permessa per SVG', 'fp-performance-suite'); ?></label><br>
                        <input type="text" name="svg_origin" id="svg_origin" value="<?php echo esc_attr($settings['cors']['svg_origin']); ?>" class="regular-text" placeholder="*">
                        <span class="description"><?php esc_html_e('(* = tutti, oppure dominio specifico)', 'fp-performance-suite'); ?></span>
                    </p>
                </div>
            </section>
            
            </div><!-- End Tab: .htaccess Performance -->
            
            <!-- Tab: Security & Protection -->
            <div class="tab-content" style="<?php echo $currentTab !== 'security' ? 'display:none;' : ''; ?>">
            
            <!-- 6. Protezione File -->
            <section class="fp-ps-card">
                <h2>🔒 <?php esc_html_e('Protezione File Sensibili', 'fp-performance-suite'); ?></h2>
                <p class="description"><?php esc_html_e('Blocca accessi diretti a file di configurazione e file nascosti.', 'fp-performance-suite'); ?></p>
                
                <label class="fp-ps-toggle">
                    <span class="info">
                        <strong><?php esc_html_e('Abilita Protezione File', 'fp-performance-suite'); ?></strong>
                    </span>
                    <input type="checkbox" name="file_protection_enabled" value="1" <?php checked($settings['file_protection']['enabled']); ?> />
                </label>
                
                <div style="margin-left: 20px; margin-top: 15px;">
                    <p>
                        <label>
                            <input type="checkbox" name="protect_hidden_files" value="1" <?php checked($settings['file_protection']['protect_hidden_files']); ?> />
                            <?php esc_html_e('Proteggi file nascosti (.env, .git, ecc.)', 'fp-performance-suite'); ?>
                        </label>
                    </p>
                    
                    <p>
                        <label>
                            <input type="checkbox" name="protect_wp_config" value="1" <?php checked($settings['file_protection']['protect_wp_config']); ?> />
                            <?php esc_html_e('Proteggi wp-config.php', 'fp-performance-suite'); ?>
                        </label>
                    </p>
                </div>
            </section>
            
            <!-- 7. XML-RPC -->
            <section class="fp-ps-card">
                <h2>🚫 <?php esc_html_e('XML-RPC', 'fp-performance-suite'); ?></h2>
                <p class="description"><?php esc_html_e('Disabilita XML-RPC se non usi Jetpack, app mobile WordPress o pubblicazione remota.', 'fp-performance-suite'); ?></p>
                
                <p>
                    <label>
                        <input type="checkbox" name="xmlrpc_disabled" value="1" <?php checked($settings['xmlrpc_disabled']); ?> />
                        <?php esc_html_e('Disabilita XML-RPC', 'fp-performance-suite'); ?>
                    </label>
                    <span class="description" style="display: block; margin-left: 24px; color: #d63638;"><?php esc_html_e('⚠️ Attenzione: disabilita solo se sei sicuro di non usarlo', 'fp-performance-suite'); ?></span>
                </p>
            </section>
            
            <!-- 8. Anti-Hotlink -->
            <section class="fp-ps-card">
                <h2>🖼️ <?php esc_html_e('Anti-Hotlink Immagini', 'fp-performance-suite'); ?></h2>
                <p class="description"><?php esc_html_e('Blocca l\'uso delle tue immagini da altri siti (risparmia banda).', 'fp-performance-suite'); ?></p>
                
                <label class="fp-ps-toggle">
                    <span class="info">
                        <strong><?php esc_html_e('Abilita Anti-Hotlink', 'fp-performance-suite'); ?></strong>
                    </span>
                    <input type="checkbox" name="hotlink_protection_enabled" value="1" <?php checked($settings['hotlink_protection']['enabled']); ?> />
                </label>
                
                <div style="margin-left: 20px; margin-top: 15px;">
                    <p>
                        <label>
                            <input type="checkbox" name="hotlink_allow_google" value="1" <?php checked($settings['hotlink_protection']['allow_google']); ?> />
                            <?php esc_html_e('Permetti a Google (search, immagini)', 'fp-performance-suite'); ?>
                        </label>
                    </p>
                    
                    <p>
                        <label for="hotlink_allowed_domains"><?php esc_html_e('Domini permessi aggiuntivi (uno per riga)', 'fp-performance-suite'); ?></label><br>
                        <textarea name="hotlink_allowed_domains" id="hotlink_allowed_domains" rows="3" class="large-text"><?php echo esc_textarea(implode("\n", $settings['hotlink_protection']['allowed_domains'])); ?></textarea>
                        <span class="description"><?php esc_html_e('Aggiungi domini fidati che possono usare le tue immagini', 'fp-performance-suite'); ?></span>
                    </p>
                </div>
            </section>
            
        </div><!-- End Tab: Security & Protection -->
        
        <!-- Pulsante Salva (visibile sempre) -->
        <div class="fp-ps-card" style="margin-top: 20px;">
            <p>
                <button type="submit" class="button button-primary button-large"><?php esc_html_e('Salva Tutte le Impostazioni', 'fp-performance-suite'); ?></button>
            </p>
            <p class="description">
                <?php 
                echo sprintf(
                    __('Un backup del file .htaccess viene creato automaticamente prima di ogni modifica. Puoi gestire i backup dalla pagina %s', 'fp-performance-suite'),
                    '<a href="' . esc_url(admin_url('admin.php?page=fp-performance-suite-diagnostics')) . '">' . __('Diagnostica', 'fp-performance-suite') . '</a>'
                );
                ?>
            </p>
        </div>
        
        </form>
        
        <?php
        return (string) ob_get_clean();
    }
}

