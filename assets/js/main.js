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
// Import dinamico con cache busting stabile basato su timestamp file
// Il timestamp viene passato da PHP via fpPerfSuite.tooltipVersion
let initTooltips;
(function() {
	const tooltipVersion = (typeof fpPerfSuite !== 'undefined' && fpPerfSuite.tooltipVersion) 
		? fpPerfSuite.tooltipVersion 
		: Date.now();
	import('./components/tooltip.js?v=' + tooltipVersion).then(module => {
		initTooltips = module.initTooltips;
		// Inizializza immediatamente se DOM è già pronto
		if (document.readyState !== 'loading') {
			initTooltips();
		}
	}).catch(err => {
		console.error('[FP Performance] Errore caricamento tooltip.js:', err);
	});
})();

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
		if (typeof initTooltips === 'function') {
			initTooltips();
		} else {
			// Se import dinamico non è ancora completato, aspetta e riprova
			const tooltipVersion = (typeof fpPerfSuite !== 'undefined' && fpPerfSuite.tooltipVersion) 
				? fpPerfSuite.tooltipVersion 
				: Date.now();
			import('./components/tooltip.js?v=' + tooltipVersion).then(module => {
				module.initTooltips();
			}).catch(err => {
				console.error('[FP Performance] Errore caricamento tooltip.js:', err);
			});
		}

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
	deleteConfirm,
	// Export initTooltips - gestisce import dinamico se necessario
	get initTooltips() {
		if (typeof initTooltips === 'function') {
			return initTooltips;
		}
		// Fallback: carica modulo dinamicamente
		return function() {
			const tooltipVersion = (typeof fpPerfSuite !== 'undefined' && fpPerfSuite.tooltipVersion) 
				? fpPerfSuite.tooltipVersion 
				: Date.now();
			import('./components/tooltip.js?v=' + tooltipVersion).then(module => {
				module.initTooltips();
			}).catch(err => {
				console.error('[FP Performance] Errore caricamento tooltip.js:', err);
			});
		};
	}
};
