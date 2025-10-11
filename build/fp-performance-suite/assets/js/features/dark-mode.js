/**
 * Dark Mode Toggle
 * 
 * Handles optional dark mode with localStorage persistence
 * Respects system preference by default
 * 
 * @package FP\PerfSuite
 */

/**
 * Initialize dark mode functionality
 */
export function initDarkMode() {
    // Create toggle button
    createToggleButton();
    
    // Apply saved preference or system preference
    applyDarkModePreference();
    
    // Listen for system preference changes
    watchSystemPreference();
}

/**
 * Get current dark mode preference
 * Returns: 'dark', 'light', or 'auto' (follows system)
 */
function getDarkModePreference() {
    return localStorage.getItem('fp_ps_dark_mode') || 'auto';
}

/**
 * Set dark mode preference
 */
function setDarkModePreference(mode) {
    localStorage.setItem('fp_ps_dark_mode', mode);
    applyDarkModePreference();
}

/**
 * Check if system prefers dark mode
 */
function systemPrefersDark() {
    return window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
}

/**
 * Apply dark mode based on preference
 */
function applyDarkModePreference() {
    const preference = getDarkModePreference();
    const body = document.body;
    
    // Remove existing classes
    body.classList.remove('fp-dark-mode', 'fp-light-mode');
    
    if (preference === 'dark') {
        // Force dark mode
        body.classList.add('fp-dark-mode');
    } else if (preference === 'light') {
        // Force light mode (prevents auto dark mode)
        body.classList.add('fp-light-mode');
    }
    // else: 'auto' - let CSS media query handle it
    
    // Update toggle button icon
    updateToggleButton();
}

/**
 * Create the toggle button
 */
function createToggleButton() {
    // Only add on FP Performance Suite pages
    if (!document.querySelector('.fp-ps-wrap')) {
        return;
    }
    
    const button = document.createElement('button');
    button.className = 'fp-dark-mode-toggle';
    button.setAttribute('aria-label', 'Attiva/Disattiva modalità scura');
    button.setAttribute('title', 'Cambia tema');
    
    button.innerHTML = `
        <svg class="fp-dark-mode-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
            <path d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
        </svg>
        <span class="fp-dark-mode-label">Auto</span>
    `;
    
    button.addEventListener('click', toggleDarkMode);
    
    document.body.appendChild(button);
}

/**
 * Toggle between dark, light, and auto modes
 */
function toggleDarkMode() {
    const current = getDarkModePreference();
    
    let next;
    if (current === 'auto') {
        // Auto -> Force opposite of current system preference
        next = systemPrefersDark() ? 'light' : 'dark';
    } else if (current === 'dark') {
        // Dark -> Light
        next = 'light';
    } else {
        // Light -> Auto
        next = 'auto';
    }
    
    setDarkModePreference(next);
}

/**
 * Update toggle button icon and label
 */
function updateToggleButton() {
    const button = document.querySelector('.fp-dark-mode-toggle');
    if (!button) return;
    
    const preference = getDarkModePreference();
    const label = button.querySelector('.fp-dark-mode-label');
    const icon = button.querySelector('.fp-dark-mode-icon');
    
    // Update label
    const labels = {
        'auto': 'Auto',
        'dark': 'Scuro',
        'light': 'Chiaro'
    };
    label.textContent = labels[preference] || 'Auto';
    
    // Update icon
    const icons = {
        'auto': '<path d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>',
        'dark': '<path d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>',
        'light': '<path d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" fill="none"/>'
    };
    icon.innerHTML = icons[preference] || icons.auto;
    
    // Update aria-label
    const descriptions = {
        'auto': 'Modalità automatica (segue il sistema)',
        'dark': 'Modalità scura attiva',
        'light': 'Modalità chiara attiva'
    };
    button.setAttribute('aria-label', descriptions[preference]);
}

/**
 * Watch for system preference changes
 */
function watchSystemPreference() {
    if (!window.matchMedia) return;
    
    const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
    
    // Modern browsers
    if (mediaQuery.addEventListener) {
        mediaQuery.addEventListener('change', (e) => {
            // Only react if in auto mode
            if (getDarkModePreference() === 'auto') {
                applyDarkModePreference();
            }
        });
    } else if (mediaQuery.addListener) {
        // Legacy browsers
        mediaQuery.addListener((e) => {
            if (getDarkModePreference() === 'auto') {
                applyDarkModePreference();
            }
        });
    }
}
