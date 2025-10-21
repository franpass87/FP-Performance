/**
 * Accessible Modal Dialog Component
 * 
 * Modal personalizzato completamente accessibile che sostituisce window.confirm()
 * Conforme a WCAG 2.1 AA con focus trap, keyboard navigation e ARIA attributes
 * 
 * @package FP\PerfSuite
 * @author Francesco Passeri
 */

import { trapFocus, announceToScreenReader } from '../utils/accessibility.js';

/**
 * Modal Dialog Class
 */
export class Modal {
    /**
     * @param {Object} options - Opzioni configurazione modal
     * @param {string} options.title - Titolo del modal
     * @param {string} options.message - Messaggio/contenuto
     * @param {string} [options.confirmText='Conferma'] - Testo bottone conferma
     * @param {string} [options.cancelText='Annulla'] - Testo bottone annulla  
     * @param {string} [options.confirmClass='button-primary'] - Classe CSS bottone conferma
     * @param {boolean} [options.danger=false] - Se true, mostra stile danger (rosso)
     * @param {boolean} [options.showCancel=true] - Mostra bottone annulla
     * @param {string} [options.icon=''] - Emoji/icona da mostrare
     * @param {string} [options.size='medium'] - Dimensione modal: 'small', 'medium', 'large'
     */
    constructor(options) {
        this.title = options.title || 'Conferma';
        this.message = options.message || '';
        this.confirmText = options.confirmText || 'Conferma';
        this.cancelText = options.cancelText || 'Annulla';
        this.confirmClass = options.confirmClass || 'button-primary';
        this.danger = options.danger || false;
        this.showCancel = options.showCancel !== false;
        this.icon = options.icon || '';
        this.size = options.size || 'medium';
        
        this.modal = null;
        this.overlay = null;
        this.releaseFocusTrap = null;
        this.resolvePromise = null;
    }
    
    /**
     * Mostra il modal e restituisce una Promise
     * 
     * @returns {Promise<boolean>} True se confermato, false se annullato
     */
    async show() {
        return new Promise((resolve) => {
            this.resolvePromise = resolve;
            this.render();
            this.attachEvents();
            this.open();
        });
    }
    
    /**
     * Renderizza il modal nel DOM
     */
    render() {
        // Crea overlay
        this.overlay = document.createElement('div');
        this.overlay.className = 'fp-ps-modal-overlay';
        this.overlay.setAttribute('role', 'dialog');
        this.overlay.setAttribute('aria-modal', 'true');
        this.overlay.setAttribute('aria-labelledby', 'fp-ps-modal-title');
        this.overlay.setAttribute('aria-describedby', 'fp-ps-modal-message');
        
        // Crea modal
        const dangerClass = this.danger ? ' fp-ps-modal--danger' : '';
        const sizeClass = ` fp-ps-modal--${this.size}`;
        
        this.overlay.innerHTML = `
            <div class="fp-ps-modal${dangerClass}${sizeClass}">
                <div class="fp-ps-modal__header">
                    ${this.icon ? `<span class="fp-ps-modal__icon">${this.icon}</span>` : ''}
                    <h2 class="fp-ps-modal__title" id="fp-ps-modal-title">${this.escapeHtml(this.title)}</h2>
                </div>
                <div class="fp-ps-modal__body">
                    <p class="fp-ps-modal__message" id="fp-ps-modal-message">${this.escapeHtml(this.message)}</p>
                </div>
                <div class="fp-ps-modal__footer">
                    <button type="button" class="button ${this.confirmClass} fp-ps-modal__confirm" data-action="confirm">
                        ${this.escapeHtml(this.confirmText)}
                    </button>
                    ${this.showCancel ? `
                        <button type="button" class="button fp-ps-modal__cancel" data-action="cancel">
                            ${this.escapeHtml(this.cancelText)}
                        </button>
                    ` : ''}
                </div>
            </div>
        `;
        
        this.modal = this.overlay.querySelector('.fp-ps-modal');
        document.body.appendChild(this.overlay);
    }
    
    /**
     * Attacca event listeners
     */
    attachEvents() {
        // Click sui bottoni
        const confirmBtn = this.overlay.querySelector('[data-action="confirm"]');
        const cancelBtn = this.overlay.querySelector('[data-action="cancel"]');
        
        confirmBtn.addEventListener('click', () => this.confirm());
        if (cancelBtn) {
            cancelBtn.addEventListener('click', () => this.cancel());
        }
        
        // Click sull'overlay (fuori dal modal)
        this.overlay.addEventListener('click', (e) => {
            if (e.target === this.overlay) {
                this.cancel();
            }
        });
        
        // Keyboard
        this.overlay.addEventListener('keydown', (e) => {
            // ESC per annullare
            if (e.key === 'Escape') {
                this.cancel();
            }
            
            // Enter per confermare (se focus è sul modal, non sui bottoni)
            if (e.key === 'Enter' && e.target.tagName !== 'BUTTON') {
                this.confirm();
            }
        });
    }
    
    /**
     * Apre il modal con animazione e focus trap
     */
    open() {
        // Disabilita scroll della pagina
        document.body.style.overflow = 'hidden';
        
        // Animazione entrata
        requestAnimationFrame(() => {
            this.overlay.classList.add('fp-ps-modal-overlay--open');
            this.modal.classList.add('fp-ps-modal--open');
        });
        
        // Imposta focus trap
        this.releaseFocusTrap = trapFocus(this.modal);
        
        // Annuncia agli screen reader
        announceToScreenReader(`${this.title}. ${this.message}`, 'polite');
    }
    
    /**
     * Chiude il modal
     * 
     * @param {boolean} confirmed - Se l'azione è stata confermata
     */
    close(confirmed) {
        // Animazione uscita
        this.overlay.classList.add('fp-ps-modal-overlay--closing');
        this.modal.classList.add('fp-ps-modal--closing');
        
        // Rilascia focus trap
        if (this.releaseFocusTrap) {
            this.releaseFocusTrap();
        }
        
        // Riabilita scroll
        document.body.style.overflow = '';
        
        // Rimuovi dal DOM dopo animazione
        setTimeout(() => {
            if (this.overlay && this.overlay.parentNode) {
                this.overlay.parentNode.removeChild(this.overlay);
            }
        }, 300); // Sincronizza con durata animazione CSS
        
        // Risolvi promise
        if (this.resolvePromise) {
            this.resolvePromise(confirmed);
        }
    }
    
    /**
     * Conferma azione
     */
    confirm() {
        announceToScreenReader('Azione confermata', 'polite');
        this.close(true);
    }
    
    /**
     * Annulla azione
     */
    cancel() {
        announceToScreenReader('Azione annullata', 'polite');
        this.close(false);
    }
    
    /**
     * Escape HTML per prevenire XSS
     * 
     * @param {string} text - Testo da escapare
     * @returns {string} Testo escapato
     */
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
}

/**
 * Funzione helper per conferma rapida
 * 
 * @param {string} message - Messaggio da mostrare
 * @param {Object} [options={}] - Opzioni aggiuntive
 * @returns {Promise<boolean>} True se confermato
 * 
 * @example
 * if (await confirm('Sei sicuro di voler eliminare?')) {
 *     // Elimina
 * }
 */
export async function confirm(message, options = {}) {
    const modal = new Modal({
        title: options.title || 'Conferma',
        message: message,
        confirmText: options.confirmText || 'Conferma',
        cancelText: options.cancelText || 'Annulla',
        danger: options.danger || false,
        icon: options.icon || '❓'
    });
    
    return await modal.show();
}

/**
 * Funzione helper per alert (solo OK button)
 * 
 * @param {string} message - Messaggio da mostrare
 * @param {Object} [options={}] - Opzioni aggiuntive
 * @returns {Promise<boolean>} Sempre true
 * 
 * @example
 * await alert('Operazione completata con successo!');
 */
export async function alert(message, options = {}) {
    const modal = new Modal({
        title: options.title || 'Avviso',
        message: message,
        confirmText: options.confirmText || 'OK',
        showCancel: false,
        danger: options.danger || false,
        icon: options.icon || 'ℹ️'
    });
    
    return await modal.show();
}

/**
 * Funzione helper per eliminazione (stile danger)
 * 
 * @param {string} message - Messaggio da mostrare
 * @param {Object} [options={}] - Opzioni aggiuntive
 * @returns {Promise<boolean>} True se confermato
 * 
 * @example
 * if (await deleteConfirm('Eliminare definitivamente 5 elementi?')) {
 *     // Elimina
 * }
 */
export async function deleteConfirm(message, options = {}) {
    const modal = new Modal({
        title: options.title || 'Conferma eliminazione',
        message: message,
        confirmText: options.confirmText || 'Elimina',
        cancelText: options.cancelText || 'Annulla',
        confirmClass: 'button-primary button-danger',
        danger: true,
        icon: options.icon || '⚠️'
    });
    
    return await modal.show();
}

/**
 * Inizializza modal su onclick attributes
 * Sostituisce automaticamente onclick="return confirm(...)"
 */
export function initConfirmModals() {
    document.querySelectorAll('[onclick*="confirm("]').forEach(element => {
        const onclickAttr = element.getAttribute('onclick');
        
        if (!onclickAttr) return;
        
        // Estrai messaggio dal confirm()
        const match = onclickAttr.match(/confirm\s*\(\s*['"](.+?)['"]\s*\)/);
        if (!match) return;
        
        const message = match[1];
        
        // Rimuovi onclick attribute
        element.removeAttribute('onclick');
        
        // Aggiungi nuovo event listener
        element.addEventListener('click', async function(e) {
            e.preventDefault();
            
            const confirmed = await confirm(message, {
                danger: element.classList.contains('delete') || element.classList.contains('danger')
            });
            
            if (confirmed) {
                // Se era un form submit, submitta
                if (element.tagName === 'BUTTON' && element.type === 'submit') {
                    element.closest('form')?.submit();
                }
                // Se era un link, segui il link
                else if (element.tagName === 'A' && element.href) {
                    window.location.href = element.href;
                }
            }
        });
    });
}

// Auto-init quando DOM è pronto
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initConfirmModals);
} else {
    initConfirmModals();
}

