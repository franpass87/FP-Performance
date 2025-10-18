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
        <div class="fp-ps-progress" style="margin: 20px 0;" data-progress-id="${options.id || 'default'}">
            <div class="fp-ps-progress-bar" style="background: #f0f0f1; border-radius: 4px; overflow: hidden; height: ${height}; position: relative;">
                <div class="fp-ps-progress-fill" style="background: ${color}; height: 100%; width: ${percent}%; transition: ${animated ? 'width 0.3s ease-out' : 'none'};"></div>
                <div class="fp-ps-progress-label" style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; display: flex; align-items: center; justify-content: center; color: #1d2327; font-weight: 600; font-size: 12px;">
                    ${label || `${current}/${total} (${percent}%)`}
                </div>
            </div>
            ${showCancel ? '<button type="button" class="button fp-ps-progress-cancel" style="margin-top: 10px;">Annulla</button>' : ''}
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