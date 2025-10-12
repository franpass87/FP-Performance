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
    const triggerRect = trigger.getBoundingClientRect();
    const tooltipRect = tooltip.getBoundingClientRect();
    const viewportWidth = window.innerWidth;
    const viewportHeight = window.innerHeight;
    const spacing = 10; // Space between trigger and tooltip
    const arrowSize = 6; // Size of the arrow

    // Calculate available space in each direction
    const spaceAbove = triggerRect.top;
    const spaceBelow = viewportHeight - triggerRect.bottom;
    const spaceLeft = triggerRect.left;
    const spaceRight = viewportWidth - triggerRect.right;

    let tooltipTop, tooltipLeft;
    let arrowPosition = 'bottom'; // Default arrow position (tooltip above element)

    // Determine vertical position (prefer above, fallback to below)
    if (spaceAbove >= tooltipRect.height + spacing || spaceAbove > spaceBelow) {
        // Position above
        tooltipTop = triggerRect.top - tooltipRect.height - spacing;
        arrowPosition = 'bottom';
    } else {
        // Position below
        tooltipTop = triggerRect.bottom + spacing;
        arrowPosition = 'top';
    }

    // Determine horizontal position (center aligned, but adjust if overflow)
    tooltipLeft = triggerRect.left + (triggerRect.width / 2) - (tooltipRect.width / 2);

    // Adjust if tooltip overflows left
    if (tooltipLeft < spacing) {
        tooltipLeft = spacing;
    }

    // Adjust if tooltip overflows right
    if (tooltipLeft + tooltipRect.width > viewportWidth - spacing) {
        tooltipLeft = viewportWidth - tooltipRect.width - spacing;
    }

    // Ensure tooltip stays within viewport vertically
    if (tooltipTop < spacing) {
        tooltipTop = spacing;
    }
    if (tooltipTop + tooltipRect.height > viewportHeight - spacing) {
        tooltipTop = viewportHeight - tooltipRect.height - spacing;
    }

    // Apply position
    tooltip.style.top = `${tooltipTop}px`;
    tooltip.style.left = `${tooltipLeft}px`;
    tooltip.style.transform = 'none';

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
            // Small delay to allow tooltip to render
            requestAnimationFrame(() => {
                positionTooltip(indicator, tooltip);
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
