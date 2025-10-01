(function () {
    const { ajaxurl, fpPerfSuite = {} } = window;
    const messages = fpPerfSuite.messages || {};

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
                        btn.dispatchEvent(new CustomEvent('fp:preset:applied', { bubbles: true }));
                    })
                    .catch((error) => {
                        const message = (error && error.message) ? error.message : (messages.presetError || 'Unable to apply preset.');
                        alert(message);
                    })
                    .finally(() => {
                        btn.disabled = false;
                    });
            });
        });
    });
})();
