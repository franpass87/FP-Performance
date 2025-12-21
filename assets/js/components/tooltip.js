/**
 * Tooltip positioning system
 * Dynamically positions tooltips to prevent overflow and ensure visibility
 * 
 * @package FP\PerfSuite
 */

'use strict';

/**
 * Position a tooltip relative to its trigger element
 * @param {HTMLElement} trigger - The element that triggers the tooltip
 * @param {HTMLElement} tooltip - The tooltip element to position
 */
function positionTooltip(trigger, tooltip) {
    // FIX: Assicura che il tooltip sia visibile per le misurazioni
    const originalDisplay = tooltip.style.display;
    const originalVisibility = tooltip.style.visibility;
    const originalOpacity = tooltip.style.opacity;
    const originalHeight = tooltip.style.height;
    const originalMaxHeight = tooltip.style.maxHeight;
    
    // FIX: Rendi temporaneamente visibile per misurazioni accurate
    tooltip.style.display = 'block';
    tooltip.style.visibility = 'visible';
    tooltip.style.opacity = '0';
    tooltip.style.position = 'fixed';
    tooltip.style.height = 'auto';
    tooltip.style.maxHeight = 'none';
    tooltip.style.top = '-9999px';
    tooltip.style.left = '-9999px';
    
    // Force reflow per ottenere dimensioni corrette
    void tooltip.offsetHeight;
    
    const triggerRect = trigger.getBoundingClientRect();
    const tooltipRect = tooltip.getBoundingClientRect();
    const viewportWidth = window.innerWidth;
    const viewportHeight = window.innerHeight;
    const spacing = 12; // FIX: Aumentato spacing per più spazio
    const arrowSize = 8; // FIX: Aumentato arrow size
    const padding = 15; // Padding dal bordo viewport

    // Calculate available space in each direction
    const spaceAbove = triggerRect.top;
    const spaceBelow = viewportHeight - triggerRect.bottom;
    const spaceLeft = triggerRect.left;
    const spaceRight = viewportWidth - triggerRect.right;

    let tooltipTop, tooltipLeft;
    let arrowPosition = 'bottom'; // Default arrow position (tooltip above element)

    // FIX: Usa dimensioni minime se tooltip non è ancora renderizzato
    const tooltipHeight = tooltipRect.height || 200;
    const tooltipWidth = tooltipRect.width || 380;

    // Determine vertical position (prefer above, fallback to below)
    if (spaceAbove >= tooltipHeight + spacing + padding || spaceAbove > spaceBelow) {
        // Position above
        tooltipTop = triggerRect.top - tooltipHeight - spacing;
        arrowPosition = 'bottom';
    } else {
        // Position below
        tooltipTop = triggerRect.bottom + spacing;
        arrowPosition = 'top';
    }

    // Determine horizontal position (center aligned, but adjust if overflow)
    tooltipLeft = triggerRect.left + (triggerRect.width / 2) - (tooltipWidth / 2);

    // Adjust if tooltip overflows left
    if (tooltipLeft < padding) {
        tooltipLeft = padding;
    }

    // Adjust if tooltip overflows right
    if (tooltipLeft + tooltipWidth > viewportWidth - padding) {
        tooltipLeft = viewportWidth - tooltipWidth - padding;
        // Se ancora troppo largo, usa larghezza viewport con padding
        if (tooltipLeft < padding) {
            tooltip.style.maxWidth = (viewportWidth - padding * 2) + 'px';
            tooltipLeft = padding;
        }
    }

    // Ensure tooltip stays within viewport vertically
    if (tooltipTop < padding) {
        tooltipTop = padding;
    }
    if (tooltipTop + tooltipHeight > viewportHeight - padding) {
        tooltipTop = viewportHeight - tooltipHeight - padding;
        // Se ancora troppo alto, posiziona in alto
        if (tooltipTop < padding) {
            tooltipTop = padding;
        }
    }

    // FIX: Usa position: fixed per evitare tagli quando tooltip è in basso
    tooltip.style.position = 'fixed';
    tooltip.style.top = `${tooltipTop}px`;
    tooltip.style.left = `${tooltipLeft}px`;
    tooltip.style.bottom = 'auto';
    tooltip.style.right = 'auto';
    tooltip.style.transform = 'none';
    tooltip.style.zIndex = '999999999';

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
    const sections = tooltip.querySelectorAll('.fp-ps-risk-tooltip-section');
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

    // Position arrow
    positionArrow(tooltip, triggerRect, arrowPosition, arrowSize);
}

/**
 * Position the tooltip arrow
 * @param {HTMLElement} tooltip - The tooltip element
 * @param {DOMRect} triggerRect - Bounding rect of trigger element
 * @param {string} position - Arrow position ('top' or 'bottom')
 * @param {number} arrowSize - Size of the arrow
 */
function positionArrow(tooltip, triggerRect, position, arrowSize) {
    const tooltipRect = tooltip.getBoundingClientRect();
    
    // Calculate arrow left position (centered on trigger)
    const arrowLeft = triggerRect.left + (triggerRect.width / 2) - tooltipRect.left;
    
    // Set arrow styles via CSS custom properties
    tooltip.style.setProperty('--arrow-left', `${arrowLeft}px`);
    
    // Update arrow position via data attribute (for CSS)
    tooltip.setAttribute('data-arrow-position', position);
}

/**
 * Initialize tooltip positioning
 */
export function initTooltips() {
    const indicators = document.querySelectorAll('.fp-ps-risk-indicator');

    indicators.forEach(indicator => {
        const tooltip = indicator.querySelector('.fp-ps-risk-tooltip');
        if (!tooltip) return;

        // Position on hover
        indicator.addEventListener('mouseenter', function() {
            // FIX: Doppio requestAnimationFrame per assicurare che il tooltip sia renderizzato
            requestAnimationFrame(() => {
                requestAnimationFrame(() => {
                    positionTooltip(indicator, tooltip);
                });
            });
        });

        // Reposition on window resize (debounced)
        let resizeTimeout;
        window.addEventListener('resize', () => {
            clearTimeout(resizeTimeout);
            resizeTimeout = setTimeout(() => {
                if (tooltip.offsetParent !== null) { // Only if tooltip is visible
                    positionTooltip(indicator, tooltip);
                }
            }, 100);
        });

        // Reposition on scroll (throttled)
        let scrollTimeout;
        window.addEventListener('scroll', () => {
            clearTimeout(scrollTimeout);
            scrollTimeout = setTimeout(() => {
                if (tooltip.offsetParent !== null) { // Only if tooltip is visible
                    positionTooltip(indicator, tooltip);
                }
            }, 50);
        }, { passive: true });
    });
}
