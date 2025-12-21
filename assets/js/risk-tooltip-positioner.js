/**
 * Risk Tooltip Auto-Positioner
 * 
 * Automatically repositions tooltips to prevent them from going off-screen
 * 
 * @package FP\PerfSuite
 */

(function() {
    'use strict';
    
    /**
     * Position tooltip to avoid overflow
     */
    function positionTooltip(indicator) {
        const tooltip = indicator.querySelector('.fp-ps-risk-tooltip');
        if (!tooltip) return;
        
        // Reset classes and inline styles
        tooltip.classList.remove('bottom', 'align-left', 'align-right');
        tooltip.style.left = '';
        tooltip.style.right = '';
        tooltip.style.top = '';
        tooltip.style.bottom = '';
        tooltip.style.transform = '';
        tooltip.style.maxWidth = '';
        
        // FIX: Usa position: fixed per posizionamento viewport-based (evita tagli)
        const originalVisibility = tooltip.style.visibility;
        const originalOpacity = tooltip.style.opacity;
        const originalHeight = tooltip.style.height;
        const originalMaxHeight = tooltip.style.maxHeight;
        
        // FIX: Rendi temporaneamente visibile per misurazioni accurate
        tooltip.style.visibility = 'visible';
        tooltip.style.opacity = '0';
        tooltip.style.position = 'fixed';
        tooltip.style.display = 'block';
        tooltip.style.height = 'auto';
        tooltip.style.maxHeight = 'none';
        tooltip.style.top = '-9999px';
        tooltip.style.left = '-9999px';
        
        // FIX: Assicura che tutte le sezioni siano visibili per misurazioni
        const sections = tooltip.querySelectorAll('.fp-ps-risk-tooltip-section');
        sections.forEach(section => {
            section.style.visibility = 'visible';
            section.style.display = 'block';
            section.style.opacity = '1';
        });
        
        // Force reflow per ottenere dimensioni corrette
        void tooltip.offsetHeight;
        
        const indicatorRect = indicator.getBoundingClientRect();
        const viewportWidth = window.innerWidth;
        const viewportHeight = window.innerHeight;
        const padding = 15; // Padding from viewport edges
        const spacing = 12; // FIX: Aumentato spacing per più spazio
        
        // Get tooltip dimensions - FIX: Usa dimensioni aggiornate dal CSS
        const tooltipMaxWidth = 500; // Match CSS max-width aggiornato
        const tooltipMinWidth = 380; // Match CSS min-width aggiornato
        const tooltipWidth = Math.min(tooltipMaxWidth, Math.max(tooltipMinWidth, tooltip.offsetWidth || tooltipMaxWidth));
        const tooltipHeight = tooltip.offsetHeight || 250; // FIX: Aumentato altezza stimata
        
        // Calculate preferred horizontal position (centered on indicator)
        let left = indicatorRect.left + (indicatorRect.width / 2) - (tooltipWidth / 2);
        
        // Check if tooltip goes off left
        if (left < padding) {
            left = padding;
            tooltip.classList.add('align-left');
        }
        // Check if tooltip goes off right
        else if (left + tooltipWidth > viewportWidth - padding) {
            const rightPos = viewportWidth - tooltipWidth - padding;
            if (rightPos >= padding) {
                left = rightPos;
                tooltip.classList.add('align-right');
            } else {
                // Tooltip is wider than viewport, use full width with padding
                tooltip.style.maxWidth = (viewportWidth - padding * 2) + 'px';
                left = padding;
                tooltip.classList.add('align-left');
            }
        } else {
            tooltip.classList.remove('align-left', 'align-right');
        }
        
        // Calculate vertical position (prefer above, fallback to below)
        const spaceAbove = indicatorRect.top;
        const spaceBelow = viewportHeight - indicatorRect.bottom;
        
        let top, bottom;
        let position = 'top';
        
        if (spaceAbove >= tooltipHeight + spacing + padding && spaceAbove > spaceBelow) {
            // Posiziona sopra
            bottom = viewportHeight - indicatorRect.top + spacing;
            top = 'auto';
            position = 'top';
            tooltip.classList.remove('bottom');
        } else if (spaceBelow >= tooltipHeight + spacing + padding) {
            // Posiziona sotto
            top = indicatorRect.bottom + spacing;
            bottom = 'auto';
            position = 'bottom';
            tooltip.classList.add('bottom');
        } else {
            // Non c'è spazio né sopra né sotto, posiziona dove c'è più spazio
            if (spaceAbove > spaceBelow) {
                // Più spazio sopra, ma potrebbe essere tagliato - posiziona in alto
                top = padding;
                bottom = 'auto';
                position = 'top';
                tooltip.classList.remove('bottom');
            } else {
                // Più spazio sotto, ma potrebbe essere tagliato - posiziona in basso
                bottom = padding;
                top = 'auto';
                position = 'bottom';
                tooltip.classList.add('bottom');
            }
        }
        
        // Apply positioning
        tooltip.style.top = top !== undefined ? top + 'px' : 'auto';
        tooltip.style.bottom = bottom !== undefined ? bottom + 'px' : 'auto';
        tooltip.style.left = left + 'px';
        tooltip.style.right = 'auto';
        tooltip.style.height = 'auto';
        tooltip.style.maxHeight = '80vh';
        
        // FIX: Restore visibility e assicura background sempre visibile
        tooltip.style.visibility = 'visible';
        tooltip.style.opacity = '1';
        tooltip.style.display = 'block';
        tooltip.style.height = 'auto';
        tooltip.style.maxHeight = '85vh';
        
        // FIX: Background sempre solido e visibile
        tooltip.style.background = '#1e293b';
        tooltip.style.backgroundColor = '#1e293b';
        tooltip.style.backgroundImage = 'none';
        
        // FIX: Assicura che il wrapper content sia visibile
        const content = tooltip.querySelector('.fp-ps-risk-tooltip-content');
        if (content) {
            content.style.visibility = 'visible';
            content.style.display = 'flex';
            content.style.opacity = '1';
            content.style.background = 'transparent';
        }
        
        // FIX: Assicura che tutte le sezioni siano visibili
        sections.forEach(section => {
            section.style.visibility = 'visible';
            section.style.display = 'block';
            section.style.opacity = '1';
            section.style.background = 'transparent';
        });
        
        // FIX: Assicura che tutti i testi siano visibili
        const texts = tooltip.querySelectorAll('.fp-ps-risk-tooltip-text, .fp-ps-risk-tooltip-label, .fp-ps-risk-tooltip-title');
        texts.forEach(text => {
            text.style.visibility = 'visible';
            text.style.display = 'block';
            text.style.opacity = '1';
            text.style.background = 'transparent';
        });
        
        // FIX: NON ripristinare visibility/opacity - il CSS :hover li gestisce
        // Il tooltip deve rimanere visibile quando posizionato
        // tooltip.style.visibility = originalVisibility || '';
        // tooltip.style.opacity = originalOpacity || '';
        
        // FIX: Assicura che position: fixed sia sempre applicato
        tooltip.style.position = 'fixed';
        tooltip.style.zIndex = '999999999';
    }
    
    /**
     * Initialize tooltip positioning on hover
     */
    function init() {
        const indicators = document.querySelectorAll('.fp-ps-risk-indicator');
        
        indicators.forEach(indicator => {
            const tooltip = indicator.querySelector('.fp-ps-risk-tooltip');
            if (!tooltip) return;
            
            // FIX: Assicura che position: fixed sia sempre applicato al tooltip
            tooltip.style.position = 'fixed';
            tooltip.style.zIndex = '999999999';
            
            indicator.addEventListener('mouseenter', function() {
                // FIX: Doppio requestAnimationFrame per assicurare che il tooltip sia renderizzato
                requestAnimationFrame(() => {
                    requestAnimationFrame(() => {
                        positionTooltip(indicator);
                    });
                });
            });
            
            // FIX: Riposiziona anche quando il tooltip diventa visibile via CSS :hover
            indicator.addEventListener('mouseover', function() {
                requestAnimationFrame(() => {
                    requestAnimationFrame(() => {
                        positionTooltip(indicator);
                    });
                });
            });
        });
    }
    
    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
    
    // Re-initialize on window resize
    let resizeTimer;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(init, 250);
    });
    
})();

