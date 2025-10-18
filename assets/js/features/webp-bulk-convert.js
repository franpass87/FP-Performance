/**
 * WebP Bulk Conversion with Real-time Progress
 * 
 * @package FP\PerfSuite
 */

import { showNotice } from '../components/notice.js';
import { showProgress, removeProgress } from '../components/progress.js';

/**
 * Initialize WebP bulk conversion with progress tracking
 */
export function initWebPBulkConvert() {
    const form = document.querySelector('#fp-ps-webp-bulk-form');
    const btn = document.querySelector('#fp-ps-webp-bulk-btn');
    
    if (!form || !btn) {
        return;
    }
    
    const container = form.closest('.fp-ps-card');
    const nonce = form.querySelector('[name="fp_ps_media_nonce"]')?.value;
    const statusNonce = btn.dataset.statusNonce;
    
    if (!container || !nonce || !statusNonce) {
        return;
    }
    
    // Remove default form submission
    form.addEventListener('submit', function(event) {
        event.preventDefault();
        startBulkConversion();
    });
    
    btn.addEventListener('click', function(event) {
        event.preventDefault();
        startBulkConversion();
    });
    
    /**
     * Start bulk conversion process
     */
    function startBulkConversion() {
        const limit = parseInt(form.querySelector('[name="bulk_limit"]')?.value || '20');
        const offset = parseInt(form.querySelector('[name="bulk_offset"]')?.value || '0');
        
        btn.disabled = true;
        btn.textContent = 'Avvio conversione...';
        
        // Remove any existing notices
        container.querySelectorAll('.notice').forEach(notice => notice.remove());
        
        // Show initial progress
        showProgress(container, 0, 100, 'Inizializzazione...');
        
        // Start conversion via AJAX
        const formData = new FormData();
        formData.append('action', 'fp_ps_webp_bulk_convert');
        formData.append('nonce', nonce);
        formData.append('limit', limit);
        formData.append('offset', offset);
        
        fetch(window.fpPerfSuite?.ajaxUrl || window.ajaxurl, {
            method: 'POST',
            credentials: 'same-origin',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                throw new Error(data.data || 'Errore durante l\'avvio della conversione');
            }
            
            const result = data.data;
            
            if (result.error) {
                throw new Error(result.error);
            }
            
            if (!result.queued) {
                // Conversion completed immediately (no images to convert)
                removeProgress(container);
                showNotice('Nessuna immagine da convertire', 'info');
                btn.disabled = false;
                btn.textContent = 'Avvia Conversione Bulk';
                return;
            }
            
            // Start polling for progress
            showNotice(`Conversione avviata: ${result.total} immagini in coda`, 'success');
            pollProgress(statusNonce);
        })
        .catch(error => {
            removeProgress(container);
            showNotice(error.message, 'error');
            btn.disabled = false;
            btn.textContent = 'Avvia Conversione Bulk';
        });
    }
    
    /**
     * Poll for conversion progress
     * 
     * @param {string} statusNonce - WordPress nonce for status checks
     */
    function pollProgress(statusNonce) {
        let pollInterval = null;
        let consecutiveErrors = 0;
        const maxErrors = 3;
        
        const checkStatus = () => {
            const formData = new FormData();
            formData.append('action', 'fp_ps_webp_queue_status');
            formData.append('nonce', statusNonce);
            
            fetch(window.fpPerfSuite?.ajaxUrl || window.ajaxurl, {
                method: 'POST',
                credentials: 'same-origin',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    throw new Error(data.data || 'Errore durante il controllo dello stato');
                }
                
                consecutiveErrors = 0; // Reset error counter on success
                const status = data.data;
                
                if (!status.active) {
                    // Conversion completed
                    if (pollInterval) {
                        clearInterval(pollInterval);
                    }
                    
                    removeProgress(container);
                    showNotice('Conversione completata!', 'success');
                    
                    btn.disabled = false;
                    btn.textContent = 'Avvia Conversione Bulk';
                    
                    // Refresh page after 2 seconds to update stats
                    setTimeout(() => {
                        window.location.reload();
                    }, 2000);
                    
                    return;
                }
                
                // Update progress bar
                const label = `${status.processed}/${status.total} immagini (${status.converted} convertite)`;
                showProgress(container, status.processed, status.total, label);
                
                // Update button text
                btn.textContent = `Conversione in corso... ${status.percent}%`;
            })
            .catch(error => {
                consecutiveErrors++;
                
                if (consecutiveErrors >= maxErrors) {
                    if (pollInterval) {
                        clearInterval(pollInterval);
                    }
                    
                    removeProgress(container);
                    showNotice('Errore durante il monitoraggio della conversione: ' + error.message, 'error');
                    
                    btn.disabled = false;
                    btn.textContent = 'Avvia Conversione Bulk';
                }
            });
        };
        
        // Check immediately
        checkStatus();
        
        // Then poll every 2 seconds
        pollInterval = setInterval(checkStatus, 2000);
    }
}
