/**
 * Bulk Processor Utility
 * 
 * Sistema generico per gestire operazioni bulk con progress bar e polling
 * 
 * @package FP\PerfSuite
 */

import { showNotice } from '../components/notice.js';
import { showProgress, removeProgress, updateProgress } from '../components/progress.js';

/**
 * Classe per gestire operazioni bulk con progress tracking
 */
export class BulkProcessor {
    /**
     * @param {Object} config - Configurazione del processor
     * @param {HTMLElement} config.container - Container element
     * @param {HTMLButtonElement} config.button - Button element
     * @param {string} config.startAction - WordPress AJAX action per iniziare l'operazione
     * @param {string} config.statusAction - WordPress AJAX action per controllare lo stato
     * @param {string} config.processAction - WordPress AJAX action per processare i batch (opzionale)
     * @param {string} config.nonce - WordPress nonce per start action
     * @param {string} config.statusNonce - WordPress nonce per status action
     * @param {Object} config.labels - Etichette personalizzate
     * @param {number} config.pollInterval - Intervallo di polling in ms (default: 2000)
     * @param {number} config.maxErrors - Numero massimo di errori consecutivi (default: 3)
     */
    constructor(config) {
        this.container = config.container;
        this.button = config.button;
        this.startAction = config.startAction;
        this.statusAction = config.statusAction;
        this.processAction = config.processAction; // Nuovo endpoint per processare i batch
        this.nonce = config.nonce;
        this.statusNonce = config.statusNonce;
        this.pollInterval = config.pollInterval || 2000;
        this.maxErrors = config.maxErrors || 3;
        
        this.labels = {
            starting: config.labels?.starting || 'Avvio operazione...',
            inProgress: config.labels?.inProgress || 'Elaborazione in corso...',
            completed: config.labels?.completed || 'Operazione completata!',
            noItems: config.labels?.noItems || 'Nessun elemento da processare',
            buttonIdle: config.labels?.buttonIdle || 'Avvia',
            buttonProcessing: config.labels?.buttonProcessing || 'Elaborazione...',
            ...config.labels
        };
        
        this.pollIntervalId = null;
        this.consecutiveErrors = 0;
        this.isProcessing = false;
        
        this.onComplete = config.onComplete || null;
        this.onError = config.onError || null;
        this.onProgress = config.onProgress || null;
    }
    
    /**
     * Inizia l'operazione bulk
     * 
     * @param {Object} params - Parametri aggiuntivi per l'operazione
     * @returns {Promise}
     */
    async start(params = {}) {
        console.log('FP Performance Suite: BulkProcessor.start called with params:', params);
        
        if (this.isProcessing) {
            showNotice('Un\'operazione è già in corso', 'warning');
            return;
        }
        
        this.isProcessing = true;
        this.consecutiveErrors = 0;
        this.button.disabled = true;
        this.button.textContent = this.labels.starting;
        
        // Rimuovi notifiche precedenti
        this.container.querySelectorAll('.notice').forEach(notice => notice.remove());
        
        // Mostra progress iniziale
        showProgress(this.container, 0, 100, this.labels.starting);
        
        // Prepara i dati
        const formData = new FormData();
        formData.append('action', this.startAction);
        formData.append('nonce', this.nonce);
        
        // Aggiungi parametri custom
        Object.keys(params).forEach(key => {
            formData.append(key, params[key]);
        });
        
        try {
            console.log('FP Performance Suite: Making AJAX request to:', window.fpPerfSuite?.ajaxUrl || window.ajaxurl);
            console.log('FP Performance Suite: Request data:', Object.fromEntries(formData));
            
            const response = await fetch(window.fpPerfSuite?.ajaxUrl || window.ajaxurl, {
                method: 'POST',
                credentials: 'same-origin',
                body: formData
            });
            
            console.log('FP Performance Suite: AJAX response status:', response.status);
            const data = await response.json();
            console.log('FP Performance Suite: AJAX response data:', data);
            
            if (!data.success) {
                throw new Error(data.data || 'Errore durante l\'avvio dell\'operazione');
            }
            
            const result = data.data;
            
            if (result.error) {
                throw new Error(result.error);
            }
            
            if (!result.queued) {
                // Nessun elemento da processare
                this.complete(this.labels.noItems, 'info', false);
                return;
            }
            
            // Avvia polling per il progresso
            console.log('FP Performance Suite: Starting polling for operation');
            showNotice(`Operazione avviata: ${result.total} elementi in coda`, 'success');
            this.startPolling();
            
        } catch (error) {
            this.handleError(error);
        }
    }
    
    /**
     * Avvia il polling dello stato
     */
    startPolling() {
        console.log('FP Performance Suite: startPolling called');
        const checkStatus = async () => {
            console.log('FP Performance Suite: Checking status...');
            
            // Se abbiamo un processAction, usalo per processare i batch
            if (this.processAction) {
                await this.processBatch();
            }
            
            const formData = new FormData();
            formData.append('action', this.statusAction);
            formData.append('nonce', this.statusNonce);
            
            try {
                const response = await fetch(window.fpPerfSuite?.ajaxUrl || window.ajaxurl, {
                    method: 'POST',
                    credentials: 'same-origin',
                    body: formData
                });
                
                const data = await response.json();
                console.log('FP Performance Suite: Status response:', data);
                
                if (!data.success) {
                    throw new Error(data.data || 'Errore durante il controllo dello stato');
                }
                
                this.consecutiveErrors = 0;
                const status = data.data;
                
                if (!status.active) {
                    // Operazione completata
                    this.complete(this.labels.completed, 'success', true);
                    return;
                }
                
                // Aggiorna progress bar
                this.updateProgressBar(status);
                
                // Callback personalizzato
                if (this.onProgress) {
                    this.onProgress(status);
                }
                
            } catch (error) {
                this.consecutiveErrors++;
                
                if (this.consecutiveErrors >= this.maxErrors) {
                    this.handleError(error);
                }
            }
        };
        
        // Prima chiamata immediata
        checkStatus();
        
        // Poi polling regolare
        this.pollIntervalId = setInterval(checkStatus, this.pollInterval);
    }
    
    /**
     * Processa un batch di elementi
     */
    async processBatch() {
        if (!this.processAction) {
            return;
        }
        
        console.log('FP Performance Suite: Processing batch...');
        const formData = new FormData();
        formData.append('action', this.processAction);
        formData.append('nonce', this.statusNonce);
        
        try {
            const response = await fetch(window.fpPerfSuite?.ajaxUrl || window.ajaxurl, {
                method: 'POST',
                credentials: 'same-origin',
                body: formData
            });
            
            const data = await response.json();
            console.log('FP Performance Suite: Process batch response:', data);
            
            if (!data.success) {
                console.warn('FP Performance Suite: Batch processing failed:', data.data);
            }
        } catch (error) {
            console.warn('FP Performance Suite: Batch processing error:', error);
        }
    }
    
    /**
     * Aggiorna la progress bar
     * 
     * @param {Object} status - Stato dell'operazione
     */
    updateProgressBar(status) {
        const label = this.formatProgressLabel(status);
        updateProgress(this.container, status.processed, status.total, label);
        
        // Aggiorna testo del bottone
        this.button.textContent = `${this.labels.buttonProcessing} ${status.percent}%`;
    }
    
    /**
     * Formatta l'etichetta del progresso
     * 
     * @param {Object} status - Stato dell'operazione
     * @returns {string}
     */
    formatProgressLabel(status) {
        const parts = [
            `${status.processed}/${status.total}`
        ];
        
        if (status.converted !== undefined) {
            parts.push(`(${status.converted} elaborati)`);
        }
        
        return parts.join(' ');
    }
    
    /**
     * Completa l'operazione
     * 
     * @param {string} message - Messaggio da mostrare
     * @param {string} type - Tipo di notice
     * @param {boolean} reload - Se ricaricare la pagina
     */
    complete(message, type = 'success', reload = false) {
        if (this.pollIntervalId) {
            clearInterval(this.pollIntervalId);
            this.pollIntervalId = null;
        }
        
        removeProgress(this.container);
        showNotice(message, type);
        
        this.button.disabled = false;
        this.button.textContent = this.labels.buttonIdle;
        this.isProcessing = false;
        
        // Callback personalizzato
        if (this.onComplete) {
            this.onComplete();
        }
        
        // Ricarica pagina dopo 2 secondi se richiesto
        if (reload) {
            setTimeout(() => {
                window.location.reload();
            }, 2000);
        }
    }
    
    /**
     * Gestisce gli errori
     * 
     * @param {Error} error - Errore da gestire
     */
    handleError(error) {
        if (this.pollIntervalId) {
            clearInterval(this.pollIntervalId);
            this.pollIntervalId = null;
        }
        
        removeProgress(this.container);
        showNotice('Errore: ' + error.message, 'error');
        
        this.button.disabled = false;
        this.button.textContent = this.labels.buttonIdle;
        this.isProcessing = false;
        
        // Callback personalizzato
        if (this.onError) {
            this.onError(error);
        }
    }
    
    /**
     * Cancella l'operazione in corso
     */
    cancel() {
        if (!this.isProcessing) {
            return;
        }
        
        if (this.pollIntervalId) {
            clearInterval(this.pollIntervalId);
            this.pollIntervalId = null;
        }
        
        removeProgress(this.container);
        showNotice('Operazione annullata', 'info');
        
        this.button.disabled = false;
        this.button.textContent = this.labels.buttonIdle;
        this.isProcessing = false;
    }
}
