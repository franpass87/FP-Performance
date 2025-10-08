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
 */
export function showProgress(container, current, total, label = '') {
    const percent = Math.round((current / total) * 100);
    const progressHtml = `
        <div class="fp-ps-progress" style="margin: 20px 0;">
            <div style="background: #f0f0f1; border-radius: 4px; overflow: hidden; height: 24px; position: relative;">
                <div style="background: #2271b1; height: 100%; width: ${percent}%; transition: width 0.3s;"></div>
                <div style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; display: flex; align-items: center; justify-content: center; color: #1d2327; font-weight: 600; font-size: 12px;">
                    ${label || `${current}/${total} (${percent}%)`}
                </div>
            </div>
        </div>
    `;
    
    let progressEl = container.querySelector('.fp-ps-progress');
    if (progressEl) {
        progressEl.outerHTML = progressHtml;
    } else {
        container.insertAdjacentHTML('beforeend', progressHtml);
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