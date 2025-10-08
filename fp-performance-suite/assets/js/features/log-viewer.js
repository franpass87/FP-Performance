/**
 * Real-time Log Viewer Feature
 * 
 * @package FP\PerfSuite
 */

import { request } from '../utils/http.js';
import { getMessages, getRestUrl } from '../utils/dom.js';

/**
 * Initialize real-time log viewer
 */
export function initLogViewer() {
    const logContainer = document.querySelector('[data-fp-logs]');
    
    if (!logContainer) {
        return;
    }
    
    const nonce = logContainer.dataset.nonce;
    const levelSelect = document.querySelector('[data-fp-log-filter]');
    const searchInput = document.querySelector('[data-fp-log-search]');
    const lines = logContainer.dataset.lines || '200';
    const messages = getMessages();
    const restUrl = getRestUrl();

    function refreshLogs() {
        const params = new URLSearchParams({
            lines,
            level: levelSelect ? levelSelect.value : '',
            query: searchInput ? searchInput.value : ''
        });
        
        request(restUrl + 'logs/tail?' + params.toString(), 'GET', null, nonce)
            .then((response) => {
                logContainer.textContent = response.data.join('\n');
            })
            .catch(() => {
                logContainer.textContent = messages.logsError || 'Unable to load log data.';
            });
    }

    // Event listeners
    if (levelSelect) {
        levelSelect.addEventListener('change', refreshLogs);
    }
    
    if (searchInput) {
        searchInput.addEventListener('input', function () {
            window.clearTimeout(searchInput._fpDelay);
            searchInput._fpDelay = window.setTimeout(refreshLogs, 400);
        });
    }

    // Auto-refresh every 2 seconds
    window.setInterval(refreshLogs, 2000);
    
    // Initial load
    refreshLogs();
}