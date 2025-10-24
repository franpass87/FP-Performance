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
    console.log('FP Performance Suite: initWebPBulkConvert called');
    
    const form = document.querySelector('#fp-ps-webp-bulk-form');
    const btn = document.querySelector('#fp-ps-webp-bulk-btn');
    
    console.log('FP Performance Suite: Form found:', !!form);
    console.log('FP Performance Suite: Button found:', !!btn);
    
    if (!form || !btn) {
        console.log('FP Performance Suite: WebP bulk convert elements not found, retrying...');
        // Retry after a short delay in case elements are loaded later
        setTimeout(() => {
            const retryForm = document.querySelector('#fp-ps-webp-bulk-form');
            const retryBtn = document.querySelector('#fp-ps-webp-bulk-btn');
            if (retryForm && retryBtn) {
                console.log('FP Performance Suite: WebP bulk convert elements found on retry');
                initWebPBulkConvert();
            } else {
                console.warn('FP Performance Suite: WebP bulk convert elements still not found after retry');
            }
        }, 1000);
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
        processAction: 'fp_ps_webp_process_batch', // Nuovo endpoint per processare i batch
        nonce: nonce,
        statusNonce: statusNonce,
        pollInterval: 3000, // Aumentiamo l'intervallo per dare tempo al processing
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
        console.log('FP Performance Suite: WebP bulk convert button clicked');
        startConversion();
    });
    
    /**
     * Avvia la conversione con i parametri del form
     */
    function startConversion() {
        console.log('FP Performance Suite: Starting WebP bulk conversion');
        const limit = parseInt(form.querySelector('[name="bulk_limit"]')?.value || '20');
        const offset = parseInt(form.querySelector('[name="bulk_offset"]')?.value || '0');
        
        console.log('FP Performance Suite: Conversion parameters:', { limit, offset });
        
        processor.start({
            limit: limit,
            offset: offset
        });
    }
}
