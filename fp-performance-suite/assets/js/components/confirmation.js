/**
 * Confirmation Component for Risky Actions
 * 
 * @package FP\PerfSuite
 */

/**
 * Initialize confirmation prompts for risky toggles
 */
export function initRiskyToggles() {
    const { fpPerfSuite = {} } = window;
    
    // Handle checkboxes with red risk level
    document.querySelectorAll('input[type="checkbox"][data-risk="red"]').forEach(function (toggle) {
        toggle.addEventListener('change', function (event) {
            if (!event.target.checked) {
                return;
            }
            
            // QUALITY BUG #30: Internazionalizzazione + case-insensitive
            const confirmWord = fpPerfSuite.confirmWord || 'PROCEDI';
            const promptLabel = fpPerfSuite.confirmLabel || `Type ${confirmWord} to continue`;
            
            const confirmation = window.prompt(promptLabel);
            
            // Case-insensitive comparison per UX migliore
            if (!confirmation || confirmation.trim().toUpperCase() !== confirmWord.toUpperCase()) {
                event.target.checked = false;
                alert(fpPerfSuite.cancelledLabel || 'Action cancelled');
            }
        });
    });
    
    // Handle buttons with amber risk level (confirmation before action)
    document.querySelectorAll('button[data-risk="amber"]').forEach(function (button) {
        button.addEventListener('click', function (event) {
            const confirmMessage = fpPerfSuite.confirmAmberLabel || 'Are you sure you want to proceed with this action?';
            
            if (!confirm(confirmMessage)) {
                event.preventDefault();
                event.stopImmediatePropagation();
            }
        });
    });
}