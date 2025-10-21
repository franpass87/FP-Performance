/**
 * WebP Bulk Conversion with Real-time Progress
 * 
 * Refactored to use the generic BulkProcessor utility
 * 
 * @package FP\PerfSuite
 */

import { BulkProcessor } from '../utils/bulk-processor.js';

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
    
    // Crea il processor con configurazione per WebP
    const processor = new BulkProcessor({
        container: container,
        button: btn,
        startAction: 'fp_ps_webp_bulk_convert',
        statusAction: 'fp_ps_webp_queue_status',
        nonce: nonce,
        statusNonce: statusNonce,
        pollInterval: 2000,
        labels: {
            starting: 'Avvio conversione WebP...',
            inProgress: 'Conversione in corso...',
            completed: 'Conversione WebP completata!',
            noItems: 'Nessuna immagine da convertire',
            buttonIdle: 'Avvia Conversione Bulk',
            buttonProcessing: 'Conversione in corso...'
        },
        onProgress: (status) => {
            // Formattazione personalizzata per WebP
            // Il processor gestisce già l'aggiornamento, questa è solo per personalizzazioni extra
        }
    });
    
    // Sovrascrivi il formato dell'etichetta del progresso per WebP
    processor.formatProgressLabel = function(status) {
        return `${status.processed}/${status.total} immagini (${status.converted} convertite)`;
    };
    
    // Gestione submit del form
    form.addEventListener('submit', function(event) {
        event.preventDefault();
        startConversion();
    });
    
    // Gestione click del bottone
    btn.addEventListener('click', function(event) {
        event.preventDefault();
        startConversion();
    });
    
    /**
     * Avvia la conversione con i parametri del form
     */
    function startConversion() {
        const limit = parseInt(form.querySelector('[name="bulk_limit"]')?.value || '20');
        const offset = parseInt(form.querySelector('[name="bulk_offset"]')?.value || '0');
        
        processor.start({
            limit: limit,
            offset: offset
        });
    }
}
