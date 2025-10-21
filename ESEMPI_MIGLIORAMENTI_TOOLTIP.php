<?php
/**
 * Esempi Pratici di Miglioramenti Tooltip e Descrizioni
 * 
 * Questi esempi mostrano come migliorare la chiarezza dell'interfaccia
 * seguendo le raccomandazioni del documento VALUTAZIONE_CHIAREZZA_INTERFACCIA.md
 * 
 * @package FP\PerfSuite
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */

// ============================================
// ESEMPIO 1: Campo Input con Tooltip Completo
// ============================================

function esempio_campo_input_migliorato() {
    ?>
    <div class="fp-ps-input-group">
        <label for="page_cache_ttl">
            <?php esc_html_e('Durata Cache (secondi)', 'fp-performance-suite'); ?>
            <span class="fp-ps-help-icon" style="cursor: help; color: #3b82f6;" 
                  title="Determina per quanto tempo le pagine vengono servite dalla cache">ℹ️</span>
        </label>
        
        <input type="number" 
               name="page_cache_ttl" 
               id="page_cache_ttl" 
               value="3600" 
               min="60" 
               step="60" 
               placeholder="3600"
               class="regular-text" />
        
        <div class="fp-ps-input-help" style="margin-top: 8px;">
            <p class="fp-ps-recommended" style="background: #dbeafe; border-left: 3px solid #3b82f6; padding: 10px; margin: 10px 0; border-radius: 4px;">
                💡 <strong>Consigliato: 3600 secondi (1 ora)</strong>
                <br><small style="color: #64748b;">Buon equilibrio tra performance e aggiornamenti contenuti</small>
            </p>
            
            <details class="fp-ps-input-details" style="margin-top: 10px;">
                <summary style="cursor: pointer; font-weight: 600; color: #475569;">📚 Guida valori in base al tipo di sito</summary>
                <table class="fp-ps-help-table" style="width: 100%; margin-top: 10px; border-collapse: collapse;">
                    <thead>
                        <tr style="background: #f1f5f9; border-bottom: 2px solid #e2e8f0;">
                            <th style="padding: 10px; text-align: left;">Tipo Sito</th>
                            <th style="padding: 10px; text-align: left;">TTL Consigliato</th>
                            <th style="padding: 10px; text-align: left;">Motivazione</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr style="border-bottom: 1px solid #e2e8f0;">
                            <td style="padding: 10px;"><strong>🗞️ Blog/News</strong></td>
                            <td style="padding: 10px;"><code>1800-3600s</code></td>
                            <td style="padding: 10px;">Contenuti aggiornati più volte al giorno</td>
                        </tr>
                        <tr style="border-bottom: 1px solid #e2e8f0;">
                            <td style="padding: 10px;"><strong>🛒 E-commerce</strong></td>
                            <td style="padding: 10px;"><code>300-900s</code></td>
                            <td style="padding: 10px;">Prezzi, stock e carrello dinamici</td>
                        </tr>
                        <tr style="border-bottom: 1px solid #e2e8f0;">
                            <td style="padding: 10px;"><strong>🏢 Sito Aziendale</strong></td>
                            <td style="padding: 10px;"><code>7200-14400s</code></td>
                            <td style="padding: 10px;">Contenuti prevalentemente statici</td>
                        </tr>
                        <tr style="border-bottom: 1px solid #e2e8f0;">
                            <td style="padding: 10px;"><strong>📰 Portale Alto Traffico</strong></td>
                            <td style="padding: 10px;"><code>3600-7200s</code></td>
                            <td style="padding: 10px;">Bilanciamento carico/aggiornamenti</td>
                        </tr>
                        <tr>
                            <td style="padding: 10px;"><strong>🎨 Portfolio</strong></td>
                            <td style="padding: 10px;"><code>14400-86400s</code></td>
                            <td style="padding: 10px;">Contenuti raramente aggiornati</td>
                        </tr>
                    </tbody>
                </table>
                
                <div style="background: #fef3c7; border-left: 3px solid #fbbf24; padding: 12px; margin-top: 15px; border-radius: 4px;">
                    <strong style="color: #92400e;">⚠️ Attenzione</strong>
                    <p style="margin: 8px 0 0 0; color: #78350f; line-height: 1.5;">
                        Valori troppo alti (&gt;86400s) possono mostrare contenuti obsoleti. 
                        Valori troppo bassi (&lt;60s) annullano i benefici della cache.
                    </p>
                </div>
            </details>
        </div>
    </div>
    <?php
}

// ============================================
// ESEMPIO 2: Toggle con Tooltip Rischio Completo
// ============================================

function esempio_toggle_con_tooltip_completo() {
    ?>
    <label class="fp-ps-toggle">
        <span class="info">
            <strong><?php esc_html_e('Defer JavaScript', 'fp-performance-suite'); ?></strong>
            <span class="fp-ps-risk-indicator amber">
                <div class="fp-ps-risk-tooltip amber">
                    <!-- Titolo con Icona -->
                    <div class="fp-ps-risk-tooltip-title">
                        <span class="icon">⚠</span>
                        <?php esc_html_e('Rischio Medio', 'fp-performance-suite'); ?>
                    </div>
                    
                    <!-- Descrizione -->
                    <div class="fp-ps-risk-tooltip-section">
                        <div class="fp-ps-risk-tooltip-label"><?php esc_html_e('Cosa Fa', 'fp-performance-suite'); ?></div>
                        <div class="fp-ps-risk-tooltip-text">
                            <?php esc_html_e('Posticipa il caricamento dei file JavaScript fino a dopo il rendering della pagina. Migliora il First Contentful Paint (FCP) e il Largest Contentful Paint (LCP).', 'fp-performance-suite'); ?>
                        </div>
                    </div>
                    
                    <!-- Benefici -->
                    <div class="fp-ps-risk-tooltip-section">
                        <div class="fp-ps-risk-tooltip-label"><?php esc_html_e('Benefici', 'fp-performance-suite'); ?></div>
                        <div class="fp-ps-risk-tooltip-text">
                            <ul style="margin: 8px 0; padding-left: 20px;">
                                <li>⚡ Riduzione Main Thread Work fino al 40%</li>
                                <li>📈 Miglioramento FCP di 0.5-1.5 secondi</li>
                                <li>🎯 Score Lighthouse +15-25 punti</li>
                                <li>💾 Parsing JavaScript non bloccante</li>
                            </ul>
                        </div>
                    </div>
                    
                    <!-- Rischi -->
                    <div class="fp-ps-risk-tooltip-section">
                        <div class="fp-ps-risk-tooltip-label"><?php esc_html_e('Rischi Potenziali', 'fp-performance-suite'); ?></div>
                        <div class="fp-ps-risk-tooltip-text">
                            <ul style="margin: 8px 0; padding-left: 20px;">
                                <li>⚠️ Alcuni script potrebbero non funzionare correttamente</li>
                                <li>⚠️ jQuery plugins possono generare errori</li>
                                <li>⚠️ Incompatibilità con builder (Elementor, Divi)</li>
                            </ul>
                        </div>
                    </div>
                    
                    <!-- Compatibilità -->
                    <div class="fp-ps-risk-tooltip-section">
                        <div class="fp-ps-risk-tooltip-label"><?php esc_html_e('Compatibilità', 'fp-performance-suite'); ?></div>
                        <div class="fp-ps-risk-tooltip-text">
                            ✅ WooCommerce | ✅ Contact Form 7 | ⚠️ Elementor* | ⚠️ Slider Revolution*
                            <br><small>*Potrebbero richiedere esclusioni specifiche</small>
                        </div>
                    </div>
                    
                    <!-- Consiglio -->
                    <div class="fp-ps-risk-tooltip-section">
                        <div class="fp-ps-risk-tooltip-label"><?php esc_html_e('Consiglio', 'fp-performance-suite'); ?></div>
                        <div class="fp-ps-risk-tooltip-text">
                            <?php esc_html_e('✅ Attiva questa opzione e testa accuratamente il sito. Usa le esclusioni intelligenti per script critici. Monitora errori console per 24-48 ore.', 'fp-performance-suite'); ?>
                        </div>
                    </div>
                    
                    <!-- Come Testare -->
                    <div class="fp-ps-risk-tooltip-section" style="background: #e0f2fe; padding: 10px; border-radius: 4px; margin-top: 10px;">
                        <div class="fp-ps-risk-tooltip-label"><?php esc_html_e('Come Testare', 'fp-performance-suite'); ?></div>
                        <div class="fp-ps-risk-tooltip-text">
                            <ol style="margin: 8px 0; padding-left: 20px; font-size: 13px;">
                                <li>Attiva l'opzione</li>
                                <li>Testa tutte le pagine principali</li>
                                <li>Verifica form, slider, menu mobile</li>
                                <li>Controlla Console Browser (F12)</li>
                                <li>Se errori: aggiungi script alle esclusioni</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </span>
            <small><?php esc_html_e('Posticipa esecuzione JavaScript per migliorare rendering', 'fp-performance-suite'); ?></small>
        </span>
        <input type="checkbox" name="defer_js" value="1" data-risk="amber" />
    </label>
    <?php
}

// ============================================
// ESEMPIO 3: Pannello Introduttivo Pagina
// ============================================

function esempio_pannello_introduttivo() {
    ?>
    <div class="fp-ps-page-intro" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; border-radius: 8px; margin-bottom: 30px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
        <h2 style="margin: 0 0 15px 0; color: white; font-size: 28px;">
            💾 Ottimizzazione Database
        </h2>
        <p style="font-size: 18px; line-height: 1.6; margin-bottom: 25px; opacity: 0.95;">
            Il database è il cuore del tuo WordPress. Queste operazioni lo mantengono veloce, leggero e ottimizzato.
        </p>
        
        <div class="fp-ps-grid three" style="gap: 20px;">
            <div style="background: rgba(255,255,255,0.15); padding: 20px; border-radius: 8px; backdrop-filter: blur(10px);">
                <div style="font-size: 32px; margin-bottom: 10px;">🧹</div>
                <strong style="display: block; margin-bottom: 8px; font-size: 16px;">Pulizia</strong>
                <p style="margin: 0; font-size: 14px; opacity: 0.9; line-height: 1.5;">
                    Rimuove dati obsoleti come revisioni, bozze automatiche, spam e transient scaduti
                </p>
            </div>
            <div style="background: rgba(255,255,255,0.15); padding: 20px; border-radius: 8px; backdrop-filter: blur(10px);">
                <div style="font-size: 32px; margin-bottom: 10px;">⚡</div>
                <strong style="display: block; margin-bottom: 8px; font-size: 16px;">Ottimizzazione</strong>
                <p style="margin: 0; font-size: 14px; opacity: 0.9; line-height: 1.5;">
                    Riorganizza tabelle, converte a InnoDB, aggiunge indici e recupera spazio sprecato
                </p>
            </div>
            <div style="background: rgba(255,255,255,0.15); padding: 20px; border-radius: 8px; backdrop-filter: blur(10px);">
                <div style="font-size: 32px; margin-bottom: 10px;">📊</div>
                <strong style="display: block; margin-bottom: 8px; font-size: 16px;">Monitoraggio</strong>
                <p style="margin: 0; font-size: 14px; opacity: 0.9; line-height: 1.5;">
                    Analizza query lente, identifica problemi e fornisce raccomandazioni proattive
                </p>
            </div>
        </div>
    </div>

    <!-- Alert Sicurezza -->
    <div class="notice notice-info inline" style="margin-bottom: 25px; border-left-color: #10b981;">
        <p style="margin: 0.5em 0;">
            <strong>🛡️ Sicurezza Garantita:</strong> 
            Viene creato automaticamente un backup completo prima di ogni operazione critica. 
            <a href="<?php echo admin_url('admin.php?page=fp-performance-suite-logs'); ?>" style="text-decoration: none;">
                Visualizza log operazioni →
            </a>
        </p>
    </div>
    <?php
}

// ============================================
// ESEMPIO 4: Sidebar Aiuto Contestuale
// ============================================

function esempio_sidebar_aiuto() {
    ?>
    <aside class="fp-ps-help-sidebar" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 25px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
        <h3 style="margin: 0 0 20px 0; color: #1e293b; font-size: 18px;">
            ❓ Hai bisogno di aiuto?
        </h3>
        
        <!-- Documentazione -->
        <div class="fp-ps-help-section" style="margin-bottom: 20px; padding-bottom: 20px; border-bottom: 1px solid #e2e8f0;">
            <h4 style="margin: 0 0 12px 0; color: #475569; font-size: 15px; font-weight: 600;">
                📖 Documentazione
            </h4>
            <ul style="margin: 0; padding-left: 20px; line-height: 1.8;">
                <li><a href="https://docs.example.com/cache" target="_blank" style="color: #3b82f6; text-decoration: none;">Guida Cache Completa</a></li>
                <li><a href="https://docs.example.com/faq-cache" target="_blank" style="color: #3b82f6; text-decoration: none;">FAQ Frequenti</a></li>
                <li><a href="https://docs.example.com/best-practices" target="_blank" style="color: #3b82f6; text-decoration: none;">Best Practices</a></li>
            </ul>
        </div>
        
        <!-- Video Tutorial -->
        <div class="fp-ps-help-section" style="margin-bottom: 20px; padding-bottom: 20px; border-bottom: 1px solid #e2e8f0;">
            <h4 style="margin: 0 0 12px 0; color: #475569; font-size: 15px; font-weight: 600;">
                🎥 Video Tutorial
            </h4>
            <a href="https://youtube.com/watch?v=..." target="_blank" class="button button-secondary" style="width: 100%; text-align: center; justify-content: center; display: flex; align-items: center; gap: 8px; height: 40px;">
                ▶️ Guarda Tutorial (5 min)
            </a>
            <p style="margin: 10px 0 0 0; font-size: 13px; color: #64748b; text-align: center;">
                Setup guidato passo-passo
            </p>
        </div>
        
        <!-- Supporto -->
        <div class="fp-ps-help-section" style="margin-bottom: 20px; padding-bottom: 20px; border-bottom: 1px solid #e2e8f0;">
            <h4 style="margin: 0 0 12px 0; color: #475569; font-size: 15px; font-weight: 600;">
                💬 Supporto Tecnico
            </h4>
            <p style="font-size: 14px; line-height: 1.6; color: #64748b; margin-bottom: 12px;">
                Non riesci a configurare qualcosa? Il nostro team è qui per aiutarti.
            </p>
            <a href="https://support.example.com" target="_blank" class="button button-primary" style="width: 100%; text-align: center; justify-content: center; display: flex; height: 40px; align-items: center;">
                Contatta Supporto
            </a>
        </div>
        
        <!-- Suggerimento Rapido -->
        <div class="fp-ps-quick-tip" style="background: #fef3c7; border: 1px solid #fbbf24; border-radius: 6px; padding: 15px;">
            <div style="display: flex; align-items: start; gap: 10px;">
                <span style="font-size: 20px;">💡</span>
                <div>
                    <strong style="color: #92400e; display: block; margin-bottom: 6px;">Suggerimento Rapido</strong>
                    <p style="margin: 0; font-size: 13px; line-height: 1.5; color: #78350f;">
                        Per siti con aggiornamenti frequenti, imposta una cache TTL tra 1800-3600 secondi. 
                        Per siti statici, puoi aumentare fino a 14400 secondi per massimizzare le performance.
                    </p>
                </div>
            </div>
        </div>
        
        <!-- Link Esterni Utili -->
        <div class="fp-ps-help-section" style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #e2e8f0;">
            <h4 style="margin: 0 0 12px 0; color: #475569; font-size: 15px; font-weight: 600;">
                🔗 Risorse Utili
            </h4>
            <ul style="margin: 0; padding-left: 20px; line-height: 1.8; font-size: 14px;">
                <li><a href="https://web.dev/performance" target="_blank" style="color: #3b82f6; text-decoration: none;">Web.dev Performance</a></li>
                <li><a href="https://developers.google.com/speed/pagespeed/insights" target="_blank" style="color: #3b82f6; text-decoration: none;">PageSpeed Insights</a></li>
                <li><a href="https://gtmetrix.com" target="_blank" style="color: #3b82f6; text-decoration: none;">GTmetrix</a></li>
            </ul>
        </div>
    </aside>
    <?php
}

// ============================================
// ESEMPIO 5: Glossario Termini Tecnici
// ============================================

function esempio_glossario_inline() {
    ?>
    <div class="fp-ps-glossary-section" style="background: #f1f5f9; border-radius: 8px; padding: 20px; margin-bottom: 25px;">
        <h3 style="margin: 0 0 15px 0; color: #1e293b; display: flex; align-items: center; gap: 8px;">
            📚 Glossario Termini
        </h3>
        
        <dl style="margin: 0;">
            <!-- Termine 1 -->
            <dt style="font-weight: 600; color: #475569; margin-top: 12px;">
                <span style="background: #3b82f6; color: white; padding: 2px 8px; border-radius: 4px; font-size: 12px; margin-right: 8px;">TERM</span>
                Render Blocking
            </dt>
            <dd style="margin: 8px 0 0 0; padding-left: 70px; color: #64748b; line-height: 1.6;">
                Risorse (CSS/JS) che bloccano la visualizzazione della pagina. Il browser deve scaricarle 
                completamente prima di mostrare contenuti all'utente. Ridurle migliora FCP e LCP.
            </dd>
            
            <!-- Termine 2 -->
            <dt style="font-weight: 600; color: #475569; margin-top: 12px;">
                <span style="background: #10b981; color: white; padding: 2px 8px; border-radius: 4px; font-size: 12px; margin-right: 8px;">TERM</span>
                Critical CSS
            </dt>
            <dd style="margin: 8px 0 0 0; padding-left: 70px; color: #64748b; line-height: 1.6;">
                CSS minimo necessario per il rendering della parte visibile della pagina (above the fold). 
                Viene inserito inline nell'HTML per velocizzare il primo rendering.
            </dd>
            
            <!-- Termine 3 -->
            <dt style="font-weight: 600; color: #475569; margin-top: 12px;">
                <span style="background: #8b5cf6; color: white; padding: 2px 8px; border-radius: 4px; font-size: 12px; margin-right: 8px;">TERM</span>
                Main Thread Work
            </dt>
            <dd style="margin: 8px 0 0 0; padding-left: 70px; color: #64748b; line-height: 1.6;">
                Lavoro che il thread principale del browser deve eseguire (parsing HTML/CSS, esecuzione JS, layout). 
                Troppo lavoro causa pagine lente e non responsive.
            </dd>
            
            <!-- Termine 4 -->
            <dt style="font-weight: 600; color: #475569; margin-top: 12px;">
                <span style="background: #f59e0b; color: white; padding: 2px 8px; border-radius: 4px; font-size: 12px; margin-right: 8px;">TERM</span>
                LCP (Largest Contentful Paint)
            </dt>
            <dd style="margin: 8px 0 0 0; padding-left: 70px; color: #64748b; line-height: 1.6;">
                Tempo impiegato per renderizzare l'elemento più grande visibile. Metrica Core Web Vitals. 
                Target: &lt;2.5s (buono), 2.5-4s (da migliorare), &gt;4s (scarso).
            </dd>
            
            <!-- Termine 5 -->
            <dt style="font-weight: 600; color: #475569; margin-top: 12px;">
                <span style="background: #ef4444; color: white; padding: 2px 8px; border-radius: 4px; font-size: 12px; margin-right: 8px;">TERM</span>
                Server Push (HTTP/2)
            </dt>
            <dd style="margin: 8px 0 0 0; padding-left: 70px; color: #64748b; line-height: 1.6;">
                Funzionalità HTTP/2 che permette al server di inviare risorse al browser prima che vengano richieste, 
                anticipando le necessità e riducendo latenza.
            </dd>
        </dl>
        
        <div style="margin-top: 20px; padding-top: 15px; border-top: 1px solid #cbd5e1;">
            <a href="https://docs.example.com/glossary" target="_blank" style="color: #3b82f6; text-decoration: none; font-size: 14px;">
                📖 Vedi glossario completo →
            </a>
        </div>
    </div>
    <?php
}

// ============================================
// ESEMPIO 6: Status Badge e Indicatori
// ============================================

function esempio_status_badge($isActive = true, $lastRun = null, $filesCount = 0) {
    ?>
    <div class="fp-ps-feature-status" style="display: flex; align-items: center; gap: 15px; padding: 15px; background: <?php echo $isActive ? '#d1fae5' : '#f1f5f9'; ?>; border-radius: 8px; margin-bottom: 20px;">
        <!-- Badge Stato -->
        <div class="fp-ps-status-badge" style="display: flex; align-items: center; gap: 8px; padding: 8px 16px; background: <?php echo $isActive ? '#10b981' : '#94a3b8'; ?>; color: white; border-radius: 6px; font-weight: 600; font-size: 14px;">
            <?php if ($isActive) : ?>
                <span style="font-size: 16px;">✅</span> Attivo
            <?php else : ?>
                <span style="font-size: 16px;">⏸️</span> Non Attivo
            <?php endif; ?>
        </div>
        
        <!-- Dettagli -->
        <?php if ($isActive && $lastRun) : ?>
            <div class="fp-ps-status-details" style="flex: 1; color: #065f46; font-size: 14px;">
                <div style="margin-bottom: 4px;">
                    <strong>Ultima esecuzione:</strong> <?php echo esc_html($lastRun); ?>
                </div>
                <?php if ($filesCount > 0) : ?>
                    <div>
                        <strong>File processati:</strong> 
                        <span style="background: #059669; color: white; padding: 2px 8px; border-radius: 4px; font-weight: 600;">
                            <?php echo number_format($filesCount); ?>
                        </span>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        
        <!-- Health Indicator -->
        <div class="fp-ps-health-indicator" style="display: flex; align-items: center; gap: 6px;">
            <?php if ($isActive) : ?>
                <span style="width: 12px; height: 12px; background: #10b981; border-radius: 50%; display: block; animation: pulse 2s infinite;"></span>
                <span style="color: #065f46; font-size: 13px; font-weight: 500;">Operativo</span>
            <?php else : ?>
                <span style="width: 12px; height: 12px; background: #94a3b8; border-radius: 50%; display: block;"></span>
                <span style="color: #475569; font-size: 13px; font-weight: 500;">Inattivo</span>
            <?php endif; ?>
        </div>
    </div>
    
    <style>
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
    </style>
    <?php
}

// ============================================
// ESEMPIO 7: Progress Bar con Dettagli
// ============================================

function esempio_progress_bar($current, $max, $label = '') {
    $percentage = $max > 0 ? ($current / $max) * 100 : 0;
    $color = $percentage >= 80 ? '#10b981' : ($percentage >= 50 ? '#f59e0b' : '#ef4444');
    ?>
    <div class="fp-ps-progress-container" style="margin-bottom: 20px;">
        <!-- Header -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
            <strong style="color: #1e293b; font-size: 14px;">
                <?php echo esc_html($label); ?>
            </strong>
            <span style="color: #64748b; font-size: 13px; font-weight: 600;">
                <?php echo esc_html($current); ?> / <?php echo esc_html($max); ?>
                <span style="color: <?php echo $color; ?>; margin-left: 8px;">
                    (<?php echo number_format($percentage, 1); ?>%)
                </span>
            </span>
        </div>
        
        <!-- Progress Bar -->
        <div style="width: 100%; height: 24px; background: #e2e8f0; border-radius: 12px; overflow: hidden; position: relative;">
            <div style="width: <?php echo $percentage; ?>%; height: 100%; background: linear-gradient(90deg, <?php echo $color; ?> 0%, <?php echo $color; ?>dd 100%); transition: width 0.3s ease; display: flex; align-items: center; justify-content: flex-end; padding-right: 12px;">
                <?php if ($percentage > 10) : ?>
                    <span style="color: white; font-weight: 600; font-size: 12px;">
                        <?php echo number_format($percentage, 0); ?>%
                    </span>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Status Message -->
        <div style="margin-top: 8px; font-size: 13px; color: #64748b;">
            <?php if ($percentage >= 90) : ?>
                <span style="color: #059669;">✅ Ottimo! Configurazione completa.</span>
            <?php elseif ($percentage >= 70) : ?>
                <span style="color: #10b981;">👍 Buona configurazione, puoi ottimizzare ulteriormente.</span>
            <?php elseif ($percentage >= 50) : ?>
                <span style="color: #f59e0b;">⚠️ Configurazione parziale, consigliamo di completare.</span>
            <?php else : ?>
                <span style="color: #ef4444;">❌ Configurazione insufficiente, attiva più opzioni.</span>
            <?php endif; ?>
        </div>
    </div>
    <?php
}

// ============================================
// UTILIZZO ESEMPI
// ============================================

/*
// In una pagina admin:

// 1. Campo input migliorato
esempio_campo_input_migliorato();

// 2. Toggle con tooltip completo
esempio_toggle_con_tooltip_completo();

// 3. Pannello introduttivo (all'inizio della pagina)
esempio_pannello_introduttivo();

// 4. Sidebar aiuto (nella sidebar o in fondo alla pagina)
esempio_sidebar_aiuto();

// 5. Glossario (in pagine tecniche)
esempio_glossario_inline();

// 6. Status badge
esempio_status_badge(true, '2 ore fa', 1523);

// 7. Progress bar
esempio_progress_bar(42, 50, 'Ottimizzazioni Attive');
*/

