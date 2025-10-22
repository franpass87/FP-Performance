/**
 * Progress Bar Component
 * 
 * @package FP\PerfSuite
 */

/**
 * Show progress bar in a container
 * 
 * @param {HTMLElement} container - Container element
 * @param {number} current - Current progress value
 * @param {number} total - Total progress value
 * @param {string} label - Custom label (optional)
 * @param {Object} options - Opzioni aggiuntive
 */
export function showProgress(container, current, total, label = '', options = {}) {
    const percent = Math.round((current / total) * 100);
    const color = options.color || '#2271b1';
    const height = options.height || '24px';
    const animated = options.animated !== false;
    const showCancel = options.showCancel || false;
    
    const progressHtml = `
        <div class="fp-ps-progress fp-ps-my-lg" data-progress-id="${options.id || 'default'}">
            <div class="fp-ps-progress-bar" style="height: ${height};">
                <div class="fp-ps-progress-fill" style="background: ${color}; width: ${percent}%; transition: ${animated ? 'width var(--fp-transition-base)' : 'none'};"></div>
                <div class="fp-ps-progress-label">
                    ${label || `${current}/${total} (${percent}%)`}
                </div>
            </div>
            ${showCancel ? '<button type="button" class="button fp-ps-progress-cancel fp-ps-mt-sm">Annulla</button>' : ''}
        </div>
    `;
    
    let progressEl = container.querySelector('.fp-ps-progress');
    if (progressEl) {
        progressEl.outerHTML = progressHtml;
    } else {
        container.insertAdjacentHTML('beforeend', progressHtml);
    }
    
    // Gestione pulsante annulla
    if (showCancel && options.onCancel) {
        const cancelBtn = container.querySelector('.fp-ps-progress-cancel');
        if (cancelBtn) {
            cancelBtn.addEventListener('click', options.onCancel);
        }
    }
}

/**
 * Update progress bar (più efficiente di showProgress per aggiornamenti frequenti)
 * 
 * @param {HTMLElement} container - Container element
 * @param {number} current - Current progress value
 * @param {number} total - Total progress value
 * @param {string} label - Custom label (optional)
 */
export function updateProgress(container, current, total, label = '') {
    const progressEl = container.querySelector('.fp-ps-progress');
    if (!progressEl) {
        showProgress(container, current, total, label);
        return;
    }
    
    const percent = Math.round((current / total) * 100);
    const fillEl = progressEl.querySelector('.fp-ps-progress-fill');
    const labelEl = progressEl.querySelector('.fp-ps-progress-label');
    
    if (fillEl) {
        fillEl.style.width = `${percent}%`;
    }
    
    if (labelEl) {
        labelEl.textContent = label || `${current}/${total} (${percent}%)`;
    }
}

/**
 * Remove progress bar from container
 * 
 * @param {HTMLElement} container - Container element
 */
export function removeProgress(container) {
    const progressEl = container.querySelector('.fp-ps-progress');
    if (progressEl) {
        progressEl.remove();
    }
}