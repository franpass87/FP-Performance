/**
 * FP Performance Suite - Admin Scripts (Modular)
 * 
 * This is the main entry point for all admin JavaScript.
 * Scripts are organized into ES6 modules for better maintainability.
 * 
 * @package FP\PerfSuite
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */

// Components
import { showNotice } from './components/notice.js';
import { showProgress, removeProgress } from './components/progress.js';
import { initRiskyToggles } from './components/confirmation.js';

// Features
import { initLogViewer } from './features/log-viewer.js';
import { initPresets } from './features/presets.js';
import { initBulkActions } from './features/bulk-actions.js';

/**
 * Initialize all features on DOM ready
 */
document.addEventListener('DOMContentLoaded', function () {
    // Initialize risky action confirmations
    initRiskyToggles();
    
    // Initialize log viewer
    initLogViewer();
    
    // Initialize preset buttons
    initPresets();
    
    // Initialize bulk actions
    initBulkActions();
});

/**
 * Expose utilities globally for custom scripts
 * This maintains backwards compatibility with external code
 */
window.fpPerfSuiteUtils = {
    showNotice,
    showProgress,
    removeProgress
};