/**
 * Preset Management Feature
 * 
 * @package FP\PerfSuite
 */

import { request } from '../utils/http.js';
import { showNotice } from '../components/notice.js';
import { getMessages, getRestUrl } from '../utils/dom.js';

/**
 * Initialize preset buttons
 */
export function initPresets() {
    const messages = getMessages();
    const restUrl = getRestUrl();
    
    document.querySelectorAll('[data-fp-preset]').forEach(function (btn) {
        btn.addEventListener('click', function (event) {
            event.preventDefault();
            
            const preset = btn.dataset.fpPreset;
            const nonce = btn.dataset.fpNonce;
            
            if (!preset) {
                console.error('FP Performance Suite: Missing preset ID');
                showNotice(messages.presetError || 'Unable to apply preset: missing preset ID', 'error');
                return;
            }
            
            if (!nonce) {
                console.error('FP Performance Suite: Missing nonce');
                showNotice(messages.presetError || 'Unable to apply preset: security check failed', 'error');
                return;
            }
            
            // QUALITY BUG #27: Miglior feedback UX
            btn.disabled = true;
            const originalText = btn.textContent;
            btn.textContent = '⏳ ' + (messages.loading || 'Applicazione...');
            btn.classList.add('fp-ps-loading');
            
            request(restUrl + 'preset/apply', 'POST', { id: preset }, nonce)
                .then((response) => {
                    // QUALITY BUG #27: Gestione errori più robusta
                    if (!response) {
                        throw new Error('Empty response from server');
                    }
                    
                    if (!response.success) {
                        const message = response.message || response.error || response.data?.message;
                        throw new Error(message || messages.presetError || 'Unable to apply preset.');
                    }
                    
                    // QUALITY BUG #27: Feedback visuale successo
                    btn.textContent = '✓ ' + (messages.presetSuccess || 'Preset applicato!');
                    btn.classList.remove('fp-ps-loading');
                    btn.classList.add('fp-ps-success');
                    
                    showNotice(messages.presetSuccess || 'Preset applied successfully!', 'success');
                    btn.dispatchEvent(new CustomEvent('fp:preset:applied', { 
                        bubbles: true,
                        detail: { preset, response }
                    }));
                    
                    // QUALITY BUG #27: Conferma prima del reload
                    setTimeout(() => {
                        if (confirm(messages.reloadConfirm || 'Preset applicato! Ricaricare la pagina per vedere le modifiche?')) {
                            window.location.reload();
                        } else {
                            btn.textContent = originalText;
                            btn.classList.remove('fp-ps-success');
                            btn.disabled = false;
                        }
                    }, 1000);
                })
                .catch((error) => {
                    console.error('FP Performance Suite: Preset apply error', error);
                    
                    // QUALITY BUG #27: Messaggio errore più dettagliato
                    let message = messages.presetError || 'Unable to apply preset.';
                    if (error instanceof Error) {
                        message = error.message;
                    } else if (typeof error === 'string') {
                        message = error;
                    } else if (error && error.message) {
                        message = error.message;
                    }
                    
                    showNotice(message, 'error');
                    
                    // Ripristina stato button
                    btn.textContent = originalText;
                    btn.classList.remove('fp-ps-loading');
                    btn.disabled = false;
                })
                .finally(() => {
                    // Cleanup già gestito nei blocchi specifici
                });
        });
    });
}