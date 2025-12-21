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
        
        // Temporarily make tooltip visible for measurements (off-screen)
        const originalVisibility = tooltip.style.visibility;
        const originalOpacity = tooltip.style.opacity;
        tooltip.style.visibility = 'hidden';
        tooltip.style.opacity = '0';
        tooltip.style.position = 'absolute';
        tooltip.style.display = 'block';
        
        // Force reflow
        void tooltip.offsetHeight;
        
        const indicatorRect = indicator.getBoundingClientRect();
        const viewportWidth = window.innerWidth;
        const viewportHeight = window.innerHeight;
        const padding = 15; // Padding from viewport edges
        
        // Get tooltip dimensions (use max-width for calculation)
        const tooltipMaxWidth = 450; // Match CSS max-width
        const tooltipMinWidth = 320; // Match CSS min-width
        const tooltipWidth = Math.min(tooltipMaxWidth, Math.max(tooltipMinWidth, tooltip.offsetWidth || tooltipMaxWidth));
        
        // Calculate preferred horizontal position (centered on indicator)
        let left = indicatorRect.left + (indicatorRect.width / 2) - (tooltipWidth / 2);
        let useLeft = true;
        let useRight = false;
        
        // Check if tooltip goes off left
        if (left < padding) {
            left = padding;
            tooltip.classList.add('align-left');
            useLeft = true;
            useRight = false;
        }
        // Check if tooltip goes off right
        else if (left + tooltipWidth > viewportWidth - padding) {
            const rightPos = viewportWidth - tooltipWidth - padding;
            if (rightPos >= padding) {
                left = rightPos;
                tooltip.classList.add('align-right');
                useLeft = true;
                useRight = false;
            } else {
                // Tooltip is wider than viewport, use full width with padding
                tooltip.style.maxWidth = (viewportWidth - padding * 2) + 'px';
                left = padding;
                tooltip.classList.add('align-left');
                useLeft = true;
                useRight = false;
            }
        }
        
        // Calculate vertical position (prefer above, fallback to below)
        const spaceAbove = indicatorRect.top;
        const spaceBelow = viewportHeight - indicatorRect.bottom;
        const estimatedTooltipHeight = 200; // Approximate height
        
        let position = 'top';
        if (spaceAbove < estimatedTooltipHeight + padding && spaceBelow > spaceAbove) {
            position = 'bottom';
            tooltip.classList.add('bottom');
        }
        
        // Apply positioning
        if (position === 'bottom') {
            tooltip.style.top = (indicatorRect.bottom + 8) + 'px';
            tooltip.style.bottom = '';
        } else {
            tooltip.style.bottom = (viewportHeight - indicatorRect.top + 8) + 'px';
            tooltip.style.top = '';
        }
        
        if (useLeft) {
            tooltip.style.left = left + 'px';
            tooltip.style.right = '';
        } else if (useRight) {
            tooltip.style.right = (viewportWidth - left - tooltipWidth) + 'px';
            tooltip.style.left = '';
        }
        
        // Restore original visibility (will be shown by CSS :hover)
        tooltip.style.visibility = originalVisibility || '';
        tooltip.style.opacity = originalOpacity || '';
    }
    
    /**
     * Initialize tooltip positioning on hover
     */
    function init() {
        const indicators = document.querySelectorAll('.fp-ps-risk-indicator');
        
        indicators.forEach(indicator => {
            indicator.addEventListener('mouseenter', function() {
                // Small delay to let tooltip render
                setTimeout(() => positionTooltip(indicator), 10);
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

