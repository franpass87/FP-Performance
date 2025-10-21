/**
 * Accessibility Utilities
 * 
 * Gestisce miglioramenti di accessibilità per componenti del plugin
 * 
 * @package FP\PerfSuite
 * @author Francesco Passeri
 */

/**
 * Inizializza gli switch accessibili con gestione ARIA
 */
export function initAccessibleToggles() {
    const toggles = document.querySelectorAll('input[type="checkbox"][role="switch"]');
    
    toggles.forEach(toggle => {
        // Aggiorna aria-checked quando cambia lo stato
        toggle.addEventListener('change', function() {
            this.setAttribute('aria-checked', this.checked ? 'true' : 'false');
        });
        
        // Supporto keyboard per spazio
        toggle.addEventListener('keydown', function(e) {
            if (e.key === ' ' || e.key === 'Spacebar') {
                e.preventDefault();
                this.click();
            }
        });
    });
}

/**
 * Gestione focus trap per modal/dialog
 * 
 * @param {HTMLElement} container - Elemento contenitore
 * @returns {Function} Funzione per rilasciare il focus trap
 */
export function trapFocus(container) {
    const focusableElements = container.querySelectorAll(
        'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
    );
    
    const firstElement = focusableElements[0];
    const lastElement = focusableElements[focusableElements.length - 1];
    
    // Salva l'elemento precedentemente focalizzato
    const previouslyFocused = document.activeElement;
    
    // Focus sul primo elemento
    if (firstElement) {
        firstElement.focus();
    }
    
    const handleKeyDown = (e) => {
        if (e.key !== 'Tab') return;
        
        if (e.shiftKey) {
            // Shift + Tab
            if (document.activeElement === firstElement) {
                e.preventDefault();
                lastElement.focus();
            }
        } else {
            // Tab
            if (document.activeElement === lastElement) {
                e.preventDefault();
                firstElement.focus();
            }
        }
    };
    
    container.addEventListener('keydown', handleKeyDown);
    
    // Funzione per rilasciare il trap
    return function releaseFocusTrap() {
        container.removeEventListener('keydown', handleKeyDown);
        if (previouslyFocused) {
            previouslyFocused.focus();
        }
    };
}

/**
 * Annuncia un messaggio agli screen reader
 * 
 * @param {string} message - Messaggio da annunciare
 * @param {string} politeness - Livello di politeness ('polite' o 'assertive')
 */
export function announceToScreenReader(message, politeness = 'polite') {
    // Usa wp.a11y se disponibile (WordPress nativo)
    if (typeof wp !== 'undefined' && wp.a11y && wp.a11y.speak) {
        wp.a11y.speak(message, politeness);
        return;
    }
    
    // Fallback: crea live region manualmente
    const liveRegion = document.getElementById('fp-ps-a11y-speak');
    
    if (!liveRegion) {
        const region = document.createElement('div');
        region.id = 'fp-ps-a11y-speak';
        region.className = 'screen-reader-text';
        region.setAttribute('aria-live', politeness);
        region.setAttribute('aria-atomic', 'true');
        region.style.cssText = 'position: absolute; margin: -1px; padding: 0; height: 1px; width: 1px; overflow: hidden; clip: rect(0 0 0 0); border: 0; word-wrap: normal;';
        document.body.appendChild(region);
        
        // Ritarda leggermente per dare tempo agli screen reader
        setTimeout(() => {
            region.textContent = message;
        }, 100);
    } else {
        liveRegion.textContent = message;
    }
}

/**
 * Gestione tooltip accessibili
 * 
 * @param {HTMLElement} trigger - Elemento trigger del tooltip
 * @param {HTMLElement} tooltip - Elemento tooltip
 */
export function initAccessibleTooltip(trigger, tooltip) {
    const tooltipId = tooltip.id || `tooltip-${Math.random().toString(36).substr(2, 9)}`;
    tooltip.id = tooltipId;
    
    // Imposta attributi ARIA
    tooltip.setAttribute('role', 'tooltip');
    tooltip.setAttribute('aria-hidden', 'true');
    trigger.setAttribute('aria-describedby', tooltipId);
    
    // Mostra/nascondi tooltip
    const show = () => {
        tooltip.setAttribute('aria-hidden', 'false');
        trigger.setAttribute('aria-expanded', 'true');
    };
    
    const hide = () => {
        tooltip.setAttribute('aria-hidden', 'true');
        trigger.setAttribute('aria-expanded', 'false');
    };
    
    // Eventi mouse
    trigger.addEventListener('mouseenter', show);
    trigger.addEventListener('mouseleave', hide);
    
    // Eventi keyboard
    trigger.addEventListener('focus', show);
    trigger.addEventListener('blur', hide);
    
    // Toggle con Enter/Spazio
    trigger.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' || e.key === ' ' || e.key === 'Spacebar') {
            e.preventDefault();
            const isHidden = tooltip.getAttribute('aria-hidden') === 'true';
            if (isHidden) {
                show();
            } else {
                hide();
            }
        }
        
        // Chiudi con Escape
        if (e.key === 'Escape') {
            hide();
        }
    });
    
    return { show, hide };
}

/**
 * Rendi accessibile un form con validazione live
 * 
 * @param {HTMLFormElement} form - Form da rendere accessibile
 */
export function initAccessibleForm(form) {
    const inputs = form.querySelectorAll('input, textarea, select');
    
    inputs.forEach(input => {
        // Aggiungi live region per errori se non esiste
        if (!input.hasAttribute('aria-describedby')) {
            const errorId = `${input.id || input.name}-error`;
            const errorContainer = document.getElementById(errorId);
            
            if (errorContainer) {
                input.setAttribute('aria-describedby', errorId);
                errorContainer.setAttribute('role', 'alert');
                errorContainer.setAttribute('aria-live', 'assertive');
            }
        }
        
        // Validazione on change
        input.addEventListener('blur', function() {
            const isValid = this.checkValidity();
            this.setAttribute('aria-invalid', isValid ? 'false' : 'true');
            
            // Annuncia errore se presente
            if (!isValid && this.validationMessage) {
                announceToScreenReader(this.validationMessage, 'assertive');
            }
        });
    });
    
    // Submit handler
    form.addEventListener('submit', function(e) {
        const firstInvalid = form.querySelector('[aria-invalid="true"]');
        
        if (firstInvalid) {
            e.preventDefault();
            firstInvalid.focus();
            announceToScreenReader(
                'Si prega di correggere gli errori nel modulo prima di inviare.',
                'assertive'
            );
        }
    });
}

/**
 * Controlla se un elemento è visibile per gli screen reader
 * 
 * @param {HTMLElement} element - Elemento da verificare
 * @returns {boolean} True se visibile per screen reader
 */
export function isAccessible(element) {
    if (!element) return false;
    
    const ariaHidden = element.getAttribute('aria-hidden') === 'true';
    const displayNone = window.getComputedStyle(element).display === 'none';
    const visibilityHidden = window.getComputedStyle(element).visibility === 'hidden';
    const opacity = window.getComputedStyle(element).opacity === '0';
    
    return !ariaHidden && !displayNone && !visibilityHidden && !opacity;
}

/**
 * Inizializza tutti i miglioramenti di accessibilità
 */
export function initAccessibility() {
    // Toggle switch accessibili
    initAccessibleToggles();
    
    // Tooltip accessibili
    document.querySelectorAll('[data-tooltip]').forEach(trigger => {
        const tooltipSelector = trigger.getAttribute('data-tooltip');
        const tooltip = document.querySelector(tooltipSelector);
        if (tooltip) {
            initAccessibleTooltip(trigger, tooltip);
        }
    });
    
    // Form accessibili
    document.querySelectorAll('form[data-accessible]').forEach(form => {
        initAccessibleForm(form);
    });
    
    // Log per debug
    if (window.FP_PS_DEBUG) {
        console.log('[FP Performance Suite] Accessibility utilities initialized');
    }
}

// Auto-init quando DOM è pronto
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initAccessibility);
} else {
    initAccessibility();
}

