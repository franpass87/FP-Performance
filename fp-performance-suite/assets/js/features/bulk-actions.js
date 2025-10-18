/**
 * Bulk Actions Feature
 * 
 * Sistema generico per bottoni con attributo data-fp-bulk-action
 * Ora utilizza il BulkProcessor per gestione uniforme
 * 
 * @package FP\PerfSuite
 */

import { BulkProcessor } from '../utils/bulk-processor.js';

/**
 * Initialize bulk action buttons with progress tracking
 * 
 * Ogni bottone deve avere:
 * - data-fp-bulk-action: nome dell'azione AJAX di start
 * - data-fp-status-action: nome dell'azione AJAX di status (opzionale)
 * - data-fp-nonce: nonce per l'azione di start
 * - data-fp-status-nonce: nonce per l'azione di status (opzionale)
 */
export function initBulkActions() {
    document.querySelectorAll('[data-fp-bulk-action]').forEach(function (btn) {
        const action = btn.dataset.fpBulkAction;
        const statusAction = btn.dataset.fpStatusAction;
        const nonce = btn.dataset.fpNonce;
        const statusNonce = btn.dataset.fpStatusNonce;
        const container = btn.closest('.fp-ps-card') || btn.parentElement;
        
        if (!action || !nonce) {
            return;
        }
        
        // Se non c'è status action, usa una simulazione per demo
        if (!statusAction || !statusNonce) {
            // Fallback alla vecchia implementazione simulata per demo
            btn.addEventListener('click', function (event) {
                event.preventDefault();
                simulateBulkAction(btn, container);
            });
            return;
        }
        
        // Usa il BulkProcessor per azioni reali
        const processor = new BulkProcessor({
            container: container,
            button: btn,
            startAction: action,
            statusAction: statusAction,
            nonce: nonce,
            statusNonce: statusNonce,
            labels: {
                starting: btn.dataset.labelStarting || 'Avvio operazione...',
                inProgress: btn.dataset.labelProgress || 'Elaborazione in corso...',
                completed: btn.dataset.labelCompleted || 'Operazione completata!',
                noItems: btn.dataset.labelNoItems || 'Nessun elemento da processare',
                buttonIdle: btn.textContent,
                buttonProcessing: btn.dataset.labelProcessing || 'Elaborazione...'
            }
        });
        
        btn.addEventListener('click', function (event) {
            event.preventDefault();
            
            // Parametri custom dal dataset
            const params = {};
            Object.keys(btn.dataset).forEach(key => {
                if (key.startsWith('param')) {
                    const paramName = key.replace('param', '').toLowerCase();
                    params[paramName] = btn.dataset[key];
                }
            });
            
            processor.start(params);
        });
    });
}

/**
 * Simula un'azione bulk per scopi dimostrativi
 * 
 * @param {HTMLButtonElement} btn - Bottone
 * @param {HTMLElement} container - Container
 */
function simulateBulkAction(btn, container) {
    const { showProgress, removeProgress } = window.fpPerfSuiteUtils;
    const { showNotice } = window.fpPerfSuiteUtils;
    
    btn.disabled = true;
    showProgress(container, 0, 100, 'Starting...');
    
    let progress = 0;
    const interval = setInterval(() => {
        progress += 10;
        
        if (progress <= 100) {
            showProgress(container, progress, 100);
        }
        
        if (progress >= 100) {
            clearInterval(interval);
            removeProgress(container);
            showNotice('Operation completed successfully!', 'success');
            btn.disabled = false;
        }
    }, 500);
}