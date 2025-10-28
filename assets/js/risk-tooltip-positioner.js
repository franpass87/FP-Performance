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
        
        // Reset classes
        tooltip.classList.remove('bottom', 'align-left', 'align-right');
        
        const rect = tooltip.getBoundingClientRect();
        const viewportWidth = window.innerWidth;
        const viewportHeight = window.innerHeight;
        
        // Check if tooltip goes off top
        if (rect.top < 0) {
            tooltip.classList.add('bottom');
        }
        
        // Check if tooltip goes off left
        if (rect.left < 10) {
            tooltip.classList.add('align-left');
        }
        
        // Check if tooltip goes off right
        if (rect.right > viewportWidth - 10) {
            tooltip.classList.add('align-right');
        }
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

