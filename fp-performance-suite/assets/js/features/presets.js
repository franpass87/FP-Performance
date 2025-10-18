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
            
            btn.disabled = true;
            
            request(restUrl + 'preset/apply', 'POST', { id: preset }, nonce)
                .then((response) => {
                    if (!response || !response.success) {
                        const message = response && (response.message || response.error);
                        throw new Error(message || messages.presetError || 'Unable to apply preset.');
                    }
                    
                    showNotice(messages.presetSuccess || 'Preset applied successfully!', 'success');
                    btn.dispatchEvent(new CustomEvent('fp:preset:applied', { bubbles: true }));
                    
                    // Refresh page after 1 second
                    setTimeout(() => window.location.reload(), 1000);
                })
                .catch((error) => {
                    console.error('FP Performance Suite: Preset apply error', error);
                    const message = (error && error.message) 
                        ? error.message 
                        : (messages.presetError || 'Unable to apply preset.');
                    showNotice(message, 'error');
                })
                .finally(() => {
                    btn.disabled = false;
                });
        });
    });
}