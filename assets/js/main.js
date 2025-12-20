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

console.log( 'FP Performance Suite: Main script loaded' );

// Components
import { showNotice } from './components/notice.js';
import { showProgress, removeProgress, updateProgress } from './components/progress.js';
import { initRiskyToggles } from './components/confirmation.js';
import { initTooltips } from './components/tooltip.js';

// Utilities
import { BulkProcessor } from './utils/bulk-processor.js';

// Features
import { initLogViewer } from './features/log-viewer.js';
import { initPresets } from './features/presets.js';
import { initBulkActions } from './features/bulk-actions.js';
import { initAccessibility } from './utils/accessibility.js';
import { confirm, alert, deleteConfirm, initConfirmModals } from './components/modal.js';

/**
 * Initialize all features on DOM ready
 */
document.addEventListener(
	'DOMContentLoaded',
	function () {
		console.log( 'FP Performance Suite: DOM ready, initializing features' );

		// Initialize tooltips (early to prevent positioning issues)
		initTooltips();

		// Initialize risky action confirmations
		initRiskyToggles();

		// Initialize log viewer
		initLogViewer();

		// Initialize preset buttons
		initPresets();

		// Initialize bulk actions
		initBulkActions();


		// Initialize accessibility features (ARIA, keyboard navigation)
		initAccessibility();

		// Initialize custom modal dialogs (sostituisce confirm() nativi)
		initConfirmModals();
	}
);

/**
 * Expose utilities globally for custom scripts
 * This maintains backwards compatibility with external code
 */
window.fpPerfSuiteUtils = {
	showNotice,
	showProgress,
	updateProgress,
	removeProgress,
	BulkProcessor,
	confirm,
	alert,
	deleteConfirm
};
