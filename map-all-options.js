/**
 * Script per mappare tutte le opzioni del plugin FP Performance
 * Eseguire nel browser console sulla pagina admin
 */

const pages = [
    { slug: 'fp-performance-suite', name: 'Overview' },
    { slug: 'fp-performance-suite-cache', name: 'Cache' },
    { slug: 'fp-performance-suite-assets', name: 'Assets' },
    { slug: 'fp-performance-suite-compression', name: 'Compression' },
    { slug: 'fp-performance-suite-media', name: 'Media' },
    { slug: 'fp-performance-suite-mobile', name: 'Mobile' },
    { slug: 'fp-performance-suite-database', name: 'Database' },
    { slug: 'fp-performance-suite-cdn', name: 'CDN' },
    { slug: 'fp-performance-suite-backend', name: 'Backend' },
    { slug: 'fp-performance-suite-theme-optimization', name: 'Theme' },
    { slug: 'fp-performance-suite-ml', name: 'Machine Learning' },
    { slug: 'fp-performance-suite-intelligence', name: 'Intelligence' },
    { slug: 'fp-performance-suite-monitoring', name: 'Monitoring' },
    { slug: 'fp-performance-suite-security', name: 'Security' },
    { slug: 'fp-performance-suite-settings', name: 'Settings' },
    { slug: 'fp-performance-suite-ai-config', name: 'AI Config' }
];

function mapPageOptions() {
    const switches = Array.from(document.querySelectorAll('input[type="checkbox"], input[type="radio"]'));
    const tabs = Array.from(document.querySelectorAll('.nav-tab, .tab-link')).map(t => ({
        text: t.textContent?.trim(),
        active: t.classList.contains('nav-tab-active') || t.classList.contains('active')
    }));
    
    const options = switches.map(el => {
        const riskIndicator = el.closest('label, .form-field, .form-group')?.querySelector('.fp-ps-risk-indicator');
        let riskClass = 'none';
        let riskColor = null;
        
        if (riskIndicator) {
            riskClass = riskIndicator.classList.contains('green') ? 'green' : 
                       riskIndicator.classList.contains('amber') ? 'amber' : 
                       riskIndicator.classList.contains('red') ? 'red' : 'unknown';
            
            const beforeStyle = window.getComputedStyle(riskIndicator, '::before');
            riskColor = beforeStyle.backgroundColor;
        }
        
        const tooltip = riskIndicator?.querySelector('.fp-ps-risk-tooltip');
        const tooltipText = tooltip ? tooltip.textContent?.trim() : null;
        
        return {
            name: el.name,
            id: el.id,
            type: el.type,
            checked: el.checked,
            riskClass: riskClass,
            riskColor: riskColor,
            label: el.closest('label')?.textContent?.trim().split('\n')[0] || 'N/A',
            hasTooltip: !!tooltip,
            tooltipPreview: tooltipText?.substring(0, 100) || null
        };
    });
    
    return {
        url: window.location.href,
        tabs: tabs,
        options: options,
        totalOptions: options.length,
        riskIndicators: document.querySelectorAll('.fp-ps-risk-indicator').length
    };
}

// Esegui sulla pagina corrente
const result = mapPageOptions();
console.log('Opzioni mappate:', result);
console.table(result.options);

// Per mappare tutte le pagine, navigare manualmente e eseguire questo script su ciascuna

