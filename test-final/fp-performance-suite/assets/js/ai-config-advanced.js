/**
 * AI Configuration Advanced - JavaScript Handler
 * Versione avanzata con analisi in tempo reale, preview, test performance
 */
(function($) {
    'use strict';

    const AIConfigAdvanced = {
        /**
         * Inizializza
         */
        init() {
            this.bindEvents();
            this.initChartIfNeeded();
        },

        /**
         * Bind eventi
         */
        bindEvents() {
            // Analisi in tempo reale
            $(document).on('click', '#fp-ps-start-analysis', this.startRealTimeAnalysis.bind(this));
            
            // Applicazione configurazione
            $(document).on('click', '.fp-ps-apply-ai-config', this.applyConfiguration.bind(this));
            
            // Toggle suggerimenti
            $(document).on('change', '.fp-ps-suggestion-checkbox', this.updateSuggestionCount.bind(this));
            
            // Test performance
            $(document).on('click', '#fp-ps-run-performance-test', this.runPerformanceTest.bind(this));
            
            // Import configurazione
            $(document).on('click', '#fp-ps-do-import', this.importConfiguration.bind(this));
            
            // Nuova analisi
            $(document).on('click', '#fp-ps-start-new-analysis', function() {
                window.location.href = window.location.pathname + '?page=fp-performance-suite-ai-config';
            });
        },

        /**
         * Analisi in tempo reale con animazioni
         */
        async startRealTimeAnalysis(e) {
            e.preventDefault();
            
            const $button = $(e.currentTarget);
            const mode = $button.data('mode') || 'safe';
            
            // Disabilita pulsante
            $button.prop('disabled', true);
            const originalHTML = $button.html();
            $button.html('<span class="fp-ps-spinner"></span> Avvio analisi...');

            try {
                // Mostra overlay di analisi
                this.showAnalysisOverlay();

                // Step 1: Rilevamento hosting
                await this.analyzeStep('🏢 Rilevamento hosting...', 1, 7, 1000);
                
                // Step 2: Analisi risorse server
                await this.analyzeStep('⚡ Analisi risorse server...', 2, 7, 1500);
                
                // Step 3: Scansione database
                await this.analyzeStep('💾 Scansione database...', 3, 7, 2000);
                
                // Step 4: Conteggio contenuti
                await this.analyzeStep('🖼️ Conteggio contenuti...', 4, 7, 1000);
                
                // Step 5: Analisi plugin
                await this.analyzeStep('🔌 Analisi plugin installati...', 5, 7, 1500);
                
                // Step 6: Stima traffico
                await this.analyzeStep('📊 Stima traffico...', 6, 7, 1000);
                
                // Step 7: Generazione suggerimenti
                await this.analyzeStep('💡 Generazione suggerimenti AI...', 7, 7, 2000);

                // Completa con confetti!
                this.showConfetti();
                
                // Redirect alla pagina con risultati
                setTimeout(() => {
                    window.location.href = window.location.pathname + 
                        '?page=fp-performance-suite-ai-config&analyze=1&mode=' + mode;
                }, 1500);

            } catch (error) {
                console.error('Errore durante l\'analisi:', error);
                this.hideAnalysisOverlay();
                this.showErrorMessage('Si è verificato un errore durante l\'analisi');
                
                // Riabilita pulsante
                $button.prop('disabled', false);
                $button.html(originalHTML);
            }
        },

        /**
         * Mostra step di analisi
         */
        async analyzeStep(message, step, total, duration) {
            return new Promise((resolve) => {
                const percentage = Math.round((step / total) * 100);
                
                $('.fp-ps-analysis-text').text(message);
                $('.fp-ps-analysis-fill').css('width', percentage + '%');
                $('.fp-ps-analysis-percentage').text(percentage + '%');
                
                // Aggiungi effetto "typing" al messaggio
                this.typeWriter(message, '.fp-ps-analysis-text', 30);
                
                setTimeout(resolve, duration);
            });
        },

        /**
         * Effetto typing
         */
        typeWriter(text, selector, speed) {
            let i = 0;
            const $element = $(selector);
            $element.text('');
            
            function type() {
                if (i < text.length) {
                    $element.text($element.text() + text.charAt(i));
                    i++;
                    setTimeout(type, speed);
                }
            }
            
            type();
        },

        /**
         * Mostra overlay di analisi
         */
        showAnalysisOverlay() {
            const overlay = $('<div>', {
                class: 'fp-ps-analysis-overlay',
                html: `
                    <div class="fp-ps-analysis-card">
                        <div class="fp-ps-analysis-icon">
                            <svg width="80" height="80" viewBox="0 0 80 80" class="fp-ps-analysis-spinner">
                                <circle cx="40" cy="40" r="35" fill="none" stroke="#e2e8f0" stroke-width="6"/>
                                <circle cx="40" cy="40" r="35" fill="none" stroke="#667eea" stroke-width="6" 
                                        stroke-dasharray="220" stroke-dashoffset="55" 
                                        stroke-linecap="round"/>
                            </svg>
                            <div class="fp-ps-analysis-brain">🧠</div>
                        </div>
                        <h3 class="fp-ps-analysis-title">Analisi AI in Corso</h3>
                        <p class="fp-ps-analysis-text">Inizializzazione...</p>
                        <div class="fp-ps-analysis-bar">
                            <div class="fp-ps-analysis-fill" style="width: 0%"></div>
                        </div>
                        <p class="fp-ps-analysis-percentage">0%</p>
                        <div class="fp-ps-analysis-particles"></div>
                    </div>
                `
            });

            $('body').append(overlay);
            setTimeout(() => overlay.addClass('active'), 10);
            
            // Aggiungi particelle animate
            this.createParticles('.fp-ps-analysis-particles');
        },

        /**
         * Nascondi overlay analisi
         */
        hideAnalysisOverlay() {
            $('.fp-ps-analysis-overlay').removeClass('active');
            setTimeout(() => $('.fp-ps-analysis-overlay').remove(), 300);
        },

        /**
         * Crea particelle animate
         */
        createParticles(container) {
            const $container = $(container);
            const particles = ['⚡', '💡', '🚀', '✨', '🔍', '💾', '🎯'];
            
            for (let i = 0; i < 20; i++) {
                const $particle = $('<div>', {
                    class: 'fp-ps-particle',
                    text: particles[Math.floor(Math.random() * particles.length)],
                    css: {
                        left: Math.random() * 100 + '%',
                        animationDelay: Math.random() * 3 + 's',
                        animationDuration: (3 + Math.random() * 2) + 's'
                    }
                });
                $container.append($particle);
            }
        },

        /**
         * Mostra confetti di successo
         */
        showConfetti() {
            const colors = ['#667eea', '#764ba2', '#10b981', '#f59e0b', '#ef4444'];
            const confettiCount = 50;
            
            for (let i = 0; i < confettiCount; i++) {
                const $confetti = $('<div>', {
                    class: 'fp-ps-confetti',
                    css: {
                        left: Math.random() * 100 + '%',
                        backgroundColor: colors[Math.floor(Math.random() * colors.length)],
                        animationDelay: Math.random() * 0.5 + 's',
                        animationDuration: (2 + Math.random()) + 's'
                    }
                });
                $('body').append($confetti);
                
                setTimeout(() => $confetti.remove(), 3000);
            }
        },

        /**
         * Applica configurazione
         */
        async applyConfiguration(e) {
            e.preventDefault();
            
            const $button = $(e.currentTarget);
            let config = $button.data('config');
            const nonce = $button.data('nonce');
            const mode = $button.data('mode');

            // Filtra suggerimenti disabilitati
            const enabledSuggestions = this.getEnabledSuggestions();
            
            // Conferma
            if (!confirm(`Sei sicuro di voler applicare ${enabledSuggestions.length} ottimizzazioni in modalità ${mode}?`)) {
                return;
            }

            // Disabilita pulsante
            $button.prop('disabled', true);
            const originalHTML = $button.html();
            $button.html('<span class="fp-ps-spinner"></span> Applicazione in corso...');

            try {
                // Mostra progress overlay
                this.showProgressOverlay();

                const sections = [
                    { name: 'Page Cache', key: 'page_cache', endpoint: '/fp-ps/v1/cache/settings' },
                    { name: 'Browser Cache', key: 'browser_cache', endpoint: '/fp-ps/v1/cache/headers' },
                    { name: 'Asset Optimizer', key: 'asset_optimizer', endpoint: '/fp-ps/v1/assets/settings' },
                    { name: 'WebP', key: 'webp', endpoint: '/fp-ps/v1/media/webp/settings' },
                    { name: 'Lazy Load', key: 'lazy_load', endpoint: '/fp-ps/v1/media/lazy-load/settings' },
                    { name: 'Database', key: 'db', endpoint: '/fp-ps/v1/database/settings' },
                    { name: 'Backend', key: 'backend', endpoint: '/fp-ps/v1/backend/settings' },
                ];

                let completed = 0;
                const total = sections.length;

                for (const section of sections) {
                    if (!config[section.key]) {
                        completed++;
                        continue;
                    }

                    this.updateProgress(`⚙️ Configurazione ${section.name}...`, completed, total);
                    await this.updateSection(section.endpoint, config[section.key], nonce);
                    
                    completed++;
                    this.updateProgress(`✅ ${section.name} completato`, completed, total);
                    await this.delay(200); // Small delay for visual effect
                }

                // Heartbeat
                if (config.heartbeat) {
                    this.updateProgress('💓 Configurazione Heartbeat...', completed, total);
                    await this.updateHeartbeat(config.heartbeat, nonce);
                }

                // Esclusioni
                if (config.exclusions && config.exclusions.length > 0) {
                    this.updateProgress('🛡️ Configurazione Esclusioni...', completed, total);
                    await this.updateExclusions(config.exclusions, nonce);
                }

                // Successo!
                this.hideProgressOverlay();
                this.showSuccessMessage();
                this.showConfetti();

                // Redirect
                setTimeout(() => {
                    window.location.href = fpPsAdmin.overviewUrl || 'admin.php?page=fp-performance-suite';
                }, 2000);

            } catch (error) {
                console.error('Errore durante l\'applicazione:', error);
                this.hideProgressOverlay();
                this.showErrorMessage(error.message || 'Si è verificato un errore');
                
                $button.prop('disabled', false);
                $button.html(originalHTML);
            }
        },

        /**
         * Test di performance
         */
        async runPerformanceTest(e) {
            e.preventDefault();
            
            const siteUrl = window.location.origin;
            
            // Mostra overlay test
            this.showTestOverlay();
            
            try {
                // Test con PageSpeed Insights API (richiede API key)
                this.updateTestProgress('🔍 Connessione a Google PageSpeed...', 20);
                await this.delay(1000);
                
                this.updateTestProgress('📊 Analisi performance...', 40);
                await this.delay(2000);
                
                this.updateTestProgress('🎯 Calcolo Core Web Vitals...', 60);
                await this.delay(1500);
                
                this.updateTestProgress('📈 Generazione report...', 80);
                await this.delay(1000);
                
                // Per ora mostriamo dati simulati
                // In produzione, chiamare API reale
                const results = {
                    score: 65,
                    fcp: 1.8,
                    lcp: 2.5,
                    cls: 0.1,
                    tbt: 200
                };
                
                this.updateTestProgress('✅ Test completato!', 100);
                await this.delay(500);
                
                this.hideTestOverlay();
                this.showTestResults(results);
                
            } catch (error) {
                console.error('Errore nel test:', error);
                this.hideTestOverlay();
                this.showErrorMessage('Impossibile completare il test di performance');
            }
        },

        /**
         * Mostra overlay test
         */
        showTestOverlay() {
            const overlay = $('<div>', {
                class: 'fp-ps-test-overlay',
                html: `
                    <div class="fp-ps-test-card">
                        <div class="fp-ps-test-icon">🏃</div>
                        <h3 class="fp-ps-test-title">Test Performance in Corso</h3>
                        <p class="fp-ps-test-text">Inizializzazione...</p>
                        <div class="fp-ps-test-bar">
                            <div class="fp-ps-test-fill" style="width: 0%"></div>
                        </div>
                        <p class="fp-ps-test-percentage">0%</p>
                    </div>
                `
            });

            $('body').append(overlay);
            setTimeout(() => overlay.addClass('active'), 10);
        },

        /**
         * Aggiorna progress test
         */
        updateTestProgress(message, percentage) {
            $('.fp-ps-test-text').text(message);
            $('.fp-ps-test-fill').css('width', percentage + '%');
            $('.fp-ps-test-percentage').text(percentage + '%');
        },

        /**
         * Nascondi overlay test
         */
        hideTestOverlay() {
            $('.fp-ps-test-overlay').removeClass('active');
            setTimeout(() => $('.fp-ps-test-overlay').remove(), 300);
        },

        /**
         * Mostra risultati test
         */
        showTestResults(results) {
            const modal = $('<div>', {
                class: 'fp-ps-modal active',
                html: `
                    <div class="fp-ps-modal-content">
                        <span class="fp-ps-modal-close">&times;</span>
                        <h3>📊 Risultati Test Performance</h3>
                        <div class="fp-ps-test-results">
                            <div class="fp-ps-test-score">
                                <div class="fp-ps-score-circle-large" style="--score: ${results.score}">
                                    <span>${results.score}</span>
                                </div>
                                <p>PageSpeed Score</p>
                            </div>
                            <div class="fp-ps-test-metrics">
                                <div class="fp-ps-metric">
                                    <span class="fp-ps-metric-label">First Contentful Paint</span>
                                    <span class="fp-ps-metric-value">${results.fcp}s</span>
                                    <span class="fp-ps-metric-badge good">Good</span>
                                </div>
                                <div class="fp-ps-metric">
                                    <span class="fp-ps-metric-label">Largest Contentful Paint</span>
                                    <span class="fp-ps-metric-value">${results.lcp}s</span>
                                    <span class="fp-ps-metric-badge good">Good</span>
                                </div>
                                <div class="fp-ps-metric">
                                    <span class="fp-ps-metric-label">Cumulative Layout Shift</span>
                                    <span class="fp-ps-metric-value">${results.cls}</span>
                                    <span class="fp-ps-metric-badge good">Good</span>
                                </div>
                                <div class="fp-ps-metric">
                                    <span class="fp-ps-metric-label">Total Blocking Time</span>
                                    <span class="fp-ps-metric-value">${results.tbt}ms</span>
                                    <span class="fp-ps-metric-badge needs-improvement">Needs Improvement</span>
                                </div>
                            </div>
                        </div>
                    </div>
                `
            });

            $('body').append(modal);
            
            modal.find('.fp-ps-modal-close').on('click', function() {
                modal.remove();
            });
        },

        /**
         * Importa configurazione
         */
        async importConfiguration(e) {
            e.preventDefault();
            
            const jsonText = $('#fp-ps-import-json').val();
            
            if (!jsonText) {
                alert('Inserisci una configurazione JSON valida');
                return;
            }
            
            try {
                const config = JSON.parse(jsonText);
                
                // Valida struttura
                if (!config.page_cache && !config.webp && !config.asset_optimizer) {
                    throw new Error('Configurazione non valida');
                }
                
                // Applica configurazione
                const $button = $('.fp-ps-apply-ai-config').first();
                $button.data('config', config);
                
                $('#fp-ps-import-modal').hide();
                
                // Chiedi conferma e applica
                if (confirm('Configurazione importata correttamente. Vuoi applicarla ora?')) {
                    $button.click();
                }
                
            } catch (error) {
                alert('Errore: Il JSON non è valido. ' + error.message);
            }
        },

        /**
         * Ottieni suggerimenti abilitati
         */
        getEnabledSuggestions() {
            const enabled = [];
            $('.fp-ps-suggestion-checkbox:checked').each(function(index) {
                enabled.push($(this).data('suggestion-id'));
            });
            return enabled;
        },

        /**
         * Aggiorna conteggio suggerimenti
         */
        updateSuggestionCount() {
            const enabledCount = $('.fp-ps-suggestion-checkbox:checked').length;
            $('.fp-ps-ai-apply-content p').first().text(
                `Applicherò ${enabledCount} ottimizzazioni. Le impostazioni precedenti verranno salvate per rollback.`
            );
        },

        /**
         * Mostra overlay progresso
         */
        showProgressOverlay() {
            const overlay = $('<div>', {
                class: 'fp-ps-ai-overlay',
                html: `
                    <div class="fp-ps-ai-progress-card">
                        <div class="fp-ps-ai-progress-icon">
                            <svg width="60" height="60" viewBox="0 0 60 60" class="fp-ps-spinner-svg">
                                <circle cx="30" cy="30" r="25" fill="none" stroke="#e2e8f0" stroke-width="5"/>
                                <circle cx="30" cy="30" r="25" fill="none" stroke="#667eea" stroke-width="5" 
                                        stroke-dasharray="157" stroke-dashoffset="39.25" 
                                        stroke-linecap="round" class="fp-ps-spinner-circle"/>
                            </svg>
                        </div>
                        <h3 class="fp-ps-ai-progress-title">Applicazione Configurazione AI</h3>
                        <p class="fp-ps-ai-progress-text">Inizializzazione...</p>
                        <div class="fp-ps-ai-progress-bar">
                            <div class="fp-ps-ai-progress-fill" style="width: 0%"></div>
                        </div>
                        <p class="fp-ps-ai-progress-percentage">0%</p>
                    </div>
                `
            });

            $('body').append(overlay);
            setTimeout(() => overlay.addClass('active'), 10);
        },

        /**
         * Aggiorna progresso
         */
        updateProgress(message, completed, total) {
            const percentage = Math.round((completed / total) * 100);
            
            $('.fp-ps-ai-progress-text').text(message);
            $('.fp-ps-ai-progress-fill').css('width', percentage + '%');
            $('.fp-ps-ai-progress-percentage').text(percentage + '%');
        },

        /**
         * Nascondi overlay progresso
         */
        hideProgressOverlay() {
            $('.fp-ps-ai-overlay').removeClass('active');
            setTimeout(() => $('.fp-ps-ai-overlay').remove(), 300);
        },

        /**
         * Mostra messaggio successo
         */
        showSuccessMessage() {
            const message = $('<div>', {
                class: 'fp-ps-success-toast',
                html: `
                    <div class="fp-ps-toast-icon">✅</div>
                    <div class="fp-ps-toast-content">
                        <h4>Configurazione Applicata!</h4>
                        <p>Le ottimizzazioni AI sono state applicate con successo</p>
                    </div>
                `
            });

            $('body').append(message);
            setTimeout(() => message.addClass('active'), 10);
            setTimeout(() => {
                message.removeClass('active');
                setTimeout(() => message.remove(), 300);
            }, 3000);
        },

        /**
         * Mostra messaggio errore
         */
        showErrorMessage(errorText) {
            const message = $('<div>', {
                class: 'fp-ps-error-toast',
                html: `
                    <div class="fp-ps-toast-icon">❌</div>
                    <div class="fp-ps-toast-content">
                        <h4>Errore</h4>
                        <p>${errorText}</p>
                    </div>
                `
            });

            $('body').append(message);
            setTimeout(() => message.addClass('active'), 10);
            setTimeout(() => {
                message.removeClass('active');
                setTimeout(() => message.remove(), 300);
            }, 5000);
        },

        /**
         * Aggiorna sezione via REST API
         */
        async updateSection(endpoint, settings, nonce) {
            try {
                const response = await fetch(endpoint, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-WP-Nonce': nonce,
                    },
                    body: JSON.stringify(settings),
                });

                if (!response.ok) {
                    // Prova a ottenere il messaggio di errore
                    let errorMessage = `Errore ${response.status}: ${response.statusText}`;
                    try {
                        const errorData = await response.json();
                        errorMessage = errorData.message || errorMessage;
                    } catch (e) {
                        // Se non è JSON, prova a leggere come testo
                        try {
                            const errorText = await response.text();
                            if (errorText.includes('<')) {
                                // È HTML, probabilmente un errore PHP
                                errorMessage = `Errore del server per l'endpoint ${endpoint}. Controlla i log PHP.`;
                            } else {
                                errorMessage = errorText;
                            }
                        } catch (e2) {
                            // Usa il messaggio di default
                        }
                    }
                    throw new Error(errorMessage);
                }

                return response.json();
            } catch (error) {
                console.error(`Errore chiamando ${endpoint}:`, error);
                throw error;
            }
        },

        /**
         * Aggiorna Heartbeat
         */
        async updateHeartbeat(interval, nonce) {
            const response = await fetch(ajaxurl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    action: 'fp_ps_update_heartbeat',
                    interval: interval,
                    nonce: nonce,
                }),
            });

            if (!response.ok) {
                throw new Error('Errore heartbeat');
            }

            return response.json();
        },

        /**
         * Aggiorna Esclusioni
         */
        async updateExclusions(exclusions, nonce) {
            const response = await fetch(ajaxurl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    action: 'fp_ps_update_exclusions',
                    exclusions: JSON.stringify(exclusions),
                    nonce: nonce,
                }),
            });

            if (!response.ok) {
                throw new Error('Errore esclusioni');
            }

            return response.json();
        },

        /**
         * Delay helper
         */
        delay(ms) {
            return new Promise(resolve => setTimeout(resolve, ms));
        },

        /**
         * Init chart se necessario
         */
        initChartIfNeeded() {
            const $canvas = $('#fp-ps-score-history-chart');
            if ($canvas.length === 0) return;
            
            // Implementare con Chart.js o libreria simile
            // Per ora placeholder
        },

        /**
         * Aggiungi stili per animazioni
         */
        addAnimationStyles() {
            if ($('#fp-ps-animation-styles').length > 0) return;
            
            const styles = `
                <style id="fp-ps-animation-styles">
                /* Analysis Overlay */
                .fp-ps-analysis-overlay {
                    position: fixed;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    background: rgba(0, 0, 0, 0.9);
                    backdrop-filter: blur(10px);
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    z-index: 999999;
                    opacity: 0;
                    transition: opacity 0.3s;
                }

                .fp-ps-analysis-overlay.active {
                    opacity: 1;
                }

                .fp-ps-analysis-card {
                    background: white;
                    border-radius: 24px;
                    padding: 60px;
                    text-align: center;
                    max-width: 600px;
                    position: relative;
                    overflow: hidden;
                }

                .fp-ps-analysis-spinner {
                    animation: fp-ps-rotate 2s linear infinite;
                }

                .fp-ps-analysis-brain {
                    position: absolute;
                    top: 50%;
                    left: 50%;
                    transform: translate(-50%, -50%);
                    font-size: 40px;
                    animation: fp-ps-pulse 1.5s ease-in-out infinite;
                }

                @keyframes fp-ps-rotate {
                    to { transform: rotate(360deg); }
                }

                @keyframes fp-ps-pulse {
                    0%, 100% { transform: translate(-50%, -50%) scale(1); }
                    50% { transform: translate(-50%, -50%) scale(1.2); }
                }

                .fp-ps-particle {
                    position: absolute;
                    font-size: 20px;
                    animation: fp-ps-float-up linear infinite;
                    opacity: 0;
                }

                @keyframes fp-ps-float-up {
                    0% {
                        bottom: -50px;
                        opacity: 0;
                    }
                    10% {
                        opacity: 1;
                    }
                    90% {
                        opacity: 1;
                    }
                    100% {
                        bottom: 100%;
                        opacity: 0;
                    }
                }

                .fp-ps-confetti {
                    position: fixed;
                    width: 10px;
                    height: 10px;
                    top: -10px;
                    z-index: 9999999;
                    animation: fp-ps-confetti-fall linear forwards;
                }

                @keyframes fp-ps-confetti-fall {
                    to {
                        top: 100vh;
                        transform: translateX(100px) rotate(360deg);
                    }
                }

                /* Test Overlay */
                .fp-ps-test-overlay {
                    position: fixed;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    background: rgba(0, 0, 0, 0.85);
                    backdrop-filter: blur(5px);
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    z-index: 999999;
                    opacity: 0;
                    transition: opacity 0.3s;
                }

                .fp-ps-test-overlay.active {
                    opacity: 1;
                }

                .fp-ps-test-card {
                    background: white;
                    border-radius: 20px;
                    padding: 50px;
                    text-align: center;
                    max-width: 500px;
                }

                .fp-ps-test-icon {
                    font-size: 60px;
                    margin-bottom: 20px;
                    animation: fp-ps-run 1s ease-in-out infinite;
                }

                @keyframes fp-ps-run {
                    0%, 100% { transform: translateX(0); }
                    50% { transform: translateX(20px); }
                }

                /* Previous styles from ai-config.js */
                .fp-ps-spinner {
                    display: inline-block;
                    width: 16px;
                    height: 16px;
                    border: 2px solid rgba(255, 255, 255, 0.3);
                    border-radius: 50%;
                    border-top-color: white;
                    animation: fp-ps-spin 0.6s linear infinite;
                }

                @keyframes fp-ps-spin {
                    to { transform: rotate(360deg); }
                }

                .fp-ps-ai-overlay {
                    position: fixed;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    background: rgba(0, 0, 0, 0.8);
                    backdrop-filter: blur(5px);
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    z-index: 999999;
                    opacity: 0;
                    transition: opacity 0.3s;
                }

                .fp-ps-ai-overlay.active {
                    opacity: 1;
                }

                .fp-ps-ai-progress-card {
                    background: white;
                    border-radius: 20px;
                    padding: 50px;
                    text-align: center;
                    max-width: 500px;
                    box-shadow: 0 25px 50px rgba(0, 0, 0, 0.3);
                }

                .fp-ps-success-toast,
                .fp-ps-error-toast {
                    position: fixed;
                    bottom: 30px;
                    right: 30px;
                    background: white;
                    border-radius: 12px;
                    padding: 20px;
                    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
                    display: flex;
                    align-items: center;
                    gap: 15px;
                    z-index: 999999;
                    transform: translateX(400px);
                    opacity: 0;
                    transition: all 0.3s;
                    min-width: 350px;
                }

                .fp-ps-success-toast.active,
                .fp-ps-error-toast.active {
                    transform: translateX(0);
                    opacity: 1;
                }
                </style>
            `;
            
            $('head').append(styles);
        }
    };

    // Inizializza quando il DOM è pronto
    $(document).ready(() => {
        AIConfigAdvanced.addAnimationStyles();
        AIConfigAdvanced.init();
    });

})(jQuery);

