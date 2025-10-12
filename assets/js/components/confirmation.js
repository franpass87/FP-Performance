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
    
    document.querySelectorAll('[data-risk="red"]').forEach(function (toggle) {
        toggle.addEventListener('change', function (event) {
            if (!event.target.checked) {
                return;
            }
            
            const confirmation = window.prompt(
                fpPerfSuite.confirmLabel || 'Type PROCEDI to continue'
            );
            
            if (confirmation !== 'PROCEDI') {
                event.target.checked = false;
                alert(fpPerfSuite.cancelledLabel || 'Action cancelled');
            }
        });
    });
}