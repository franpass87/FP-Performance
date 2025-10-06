(function () {
    const { ajaxurl, fpPerfSuite = {} } = window;
    const messages = fpPerfSuite.messages || {};

    /**
     * Show WordPress admin notice
     */
    function showNotice(message, type = 'success') {
        const notice = document.createElement('div');
        notice.className = `notice notice-${type} is-dismissible`;
        notice.innerHTML = `<p>${message}</p>`;
        
        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'notice-dismiss';
        button.innerHTML = '<span class="screen-reader-text">Dismiss this notice.</span>';
        button.addEventListener('click', () => notice.remove());
        notice.appendChild(button);
        
        const wrap = document.querySelector('.wrap h1');
        if (wrap) {
            wrap.parentNode.insertBefore(notice, wrap.nextSibling);
        }
    }

    /**
     * Show progress bar
     */
    function showProgress(container, current, total, label = '') {
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
     * Remove progress bar
     */
    function removeProgress(container) {
        const progressEl = container.querySelector('.fp-ps-progress');
        if (progressEl) {
            progressEl.remove();
        }
    }

    function request(url, method = 'GET', body = null, nonce = '') {
        const headers = { 'X-WP-Nonce': nonce };
        if (body) {
            headers['Content-Type'] = 'application/json';
        }
        return fetch(url, {
            method,
            headers,
            body: body ? JSON.stringify(body) : null,
            credentials: 'same-origin'
        }).then(async (response) => {
            let data = null;
            try {
                data = await response.json();
            } catch (e) {
                data = null;
            }
            if (!response.ok) {
                const error = data && (data.message || data.error);
                const err = new Error(error || 'Request failed');
                err.data = data;
                throw err;
            }
            return data;
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('[data-risk="red"]').forEach(function (toggle) {
            toggle.addEventListener('change', function (event) {
                if (!event.target.checked) {
                    return;
                }
                const confirmation = window.prompt(fpPerfSuite.confirmLabel || 'Type PROCEDI to continue');
                if (confirmation !== 'PROCEDI') {
                    event.target.checked = false;
                    alert(fpPerfSuite.cancelledLabel || 'Action cancelled');
                }
            });
        });

        const logContainer = document.querySelector('[data-fp-logs]');
        if (logContainer) {
            const nonce = logContainer.dataset.nonce;
            const levelSelect = document.querySelector('[data-fp-log-filter]');
            const searchInput = document.querySelector('[data-fp-log-search]');
            const lines = logContainer.dataset.lines || '200';

            function refreshLogs() {
                const params = new URLSearchParams({ lines, level: levelSelect ? levelSelect.value : '', query: searchInput ? searchInput.value : '' });
                request(fpPerfSuite.restUrl + 'logs/tail?' + params.toString(), 'GET', null, nonce)
                    .then((response) => {
                        logContainer.textContent = response.data.join('\n');
                    })
                    .catch(() => {
                        logContainer.textContent = messages.logsError || 'Unable to load log data.';
                    });
            }

            if (levelSelect) {
                levelSelect.addEventListener('change', refreshLogs);
            }
            if (searchInput) {
                searchInput.addEventListener('input', function () {
                    window.clearTimeout(searchInput._fpDelay);
                    searchInput._fpDelay = window.setTimeout(refreshLogs, 400);
                });
            }

            window.setInterval(refreshLogs, 2000);
            refreshLogs();
        }

        document.querySelectorAll('[data-fp-preset]').forEach(function (btn) {
            btn.addEventListener('click', function (event) {
                event.preventDefault();
                const preset = btn.dataset.fpPreset;
                const nonce = btn.dataset.fpNonce;
                if (!preset) {
                    return;
                }
                btn.disabled = true;
                request(fpPerfSuite.restUrl + 'preset/apply', 'POST', { id: preset }, nonce)
                    .then((response) => {
                        if (!response || !response.success) {
                            const message = response && (response.message || response.error);
                            throw new Error(message || messages.presetError || 'Unable to apply preset.');
                        }
                        showNotice('Preset applied successfully!', 'success');
                        btn.dispatchEvent(new CustomEvent('fp:preset:applied', { bubbles: true }));
                        // Refresh page after 1 second
                        setTimeout(() => window.location.reload(), 1000);
                    })
                    .catch((error) => {
                        const message = (error && error.message) ? error.message : (messages.presetError || 'Unable to apply preset.');
                        showNotice(message, 'error');
                    })
                    .finally(() => {
                        btn.disabled = false;
                    });
            });
        });

        // Handle bulk operations with progress
        document.querySelectorAll('[data-fp-bulk-action]').forEach(function (btn) {
            btn.addEventListener('click', function (event) {
                event.preventDefault();
                const action = btn.dataset.fpBulkAction;
                const nonce = btn.dataset.fpNonce;
                const container = btn.closest('.fp-ps-card') || btn.parentElement;
                
                if (!action || !nonce) return;
                
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
    });

    // Expose utilities globally for custom scripts
    window.fpPerfSuiteUtils = { showNotice, showProgress, removeProgress };
})();
