/**
 * Bulk Actions Feature
 * 
 * @package FP\PerfSuite
 */

import { showNotice } from '../components/notice.js';
import { showProgress, removeProgress } from '../components/progress.js';

/**
 * Initialize bulk action buttons with progress tracking
 */
export function initBulkActions() {
    document.querySelectorAll('[data-fp-bulk-action]').forEach(function (btn) {
        btn.addEventListener('click', function (event) {
            event.preventDefault();
            
            const action = btn.dataset.fpBulkAction;
            const nonce = btn.dataset.fpNonce;
            const container = btn.closest('.fp-ps-card') || btn.parentElement;
            
            if (!action || !nonce) {
                return;
            }
            
            btn.disabled = true;
            showProgress(container, 0, 100, 'Starting...');
            
            // Simulate progress for demonstration
            // In real implementation, this would poll an API endpoint
            let progress = 0;
            const interval = setInterval(() => {
                progress += 10;
                
                if (progress <= 100) {
                    showProgress(container, progress, 100);
                }
                
                if (progress >= 100) {
                    clearInterval(interval);
                    removeProgress(container);
                    showNotice('Operation completed successfully!', 'success');
                    btn.disabled = false;
                }
            }, 500);
        });
    });
}