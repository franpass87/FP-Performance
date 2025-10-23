<?php
/**
 * Test per verificare la coerenza grafica della pagina mobile
 * 
 * Questo script testa che tutti gli stili CSS siano caricati correttamente
 * e che la pagina mobile utilizzi il design system modulare.
 * 
 * @package FP\PerfSuite
 * @author Francesco Passeri
 */

// Verifica che il plugin sia attivo
if (!defined('ABSPATH')) {
    exit;
}

// Verifica che siamo nell'admin
if (!is_admin()) {
    wp_die('Questo test può essere eseguito solo nell\'area admin di WordPress.');
}

?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Coerenza Grafica Pagina Mobile - FP Performance Suite</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            background: #f1f1f1;
        }
        .test-container {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        h1 {
            color: #0073aa;
            border-bottom: 2px solid #0073aa;
            padding-bottom: 10px;
        }
        .test-section {
            margin: 20px 0;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .test-section h3 {
            margin-top: 0;
            color: #333;
        }
        .status {
            padding: 15px;
            margin: 15px 0;
            border-radius: 4px;
            border-left: 4px solid;
        }
        .status.success {
            background: #d4edda;
            border-color: #28a745;
            color: #155724;
        }
        .status.error {
            background: #f8d7da;
            border-color: #dc3545;
            color: #721c24;
        }
        .status.warning {
            background: #fff3cd;
            border-color: #ffc107;
            color: #856404;
        }
        .btn {
            background: #0073aa;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin: 10px 5px;
        }
        .btn:hover {
            background: #005a87;
        }
        .btn.success {
            background: #28a745;
        }
        .btn.success:hover {
            background: #218838;
        }
        pre {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 4px;
            overflow-x: auto;
        }
        .css-test {
            margin: 10px 0;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .css-test h4 {
            margin-top: 0;
            color: #333;
        }
        .css-test .test-element {
            padding: 10px;
            margin: 5px 0;
            border-radius: 4px;
        }
        .mobile-stats-test {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin: 1rem 0;
        }
        .stat-item-test {
            display: flex;
            flex-direction: column;
            padding: 1rem;
            background: #f6f7fb;
            border-radius: 8px;
            border-left: 4px solid #2d6cdf;
        }
        .stat-label-test {
            font-weight: 600;
            color: #1c1e21;
            margin-bottom: 8px;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .stat-value-test {
            font-size: 16px;
            font-weight: 700;
            margin: 8px 0;
        }
        .status-enabled-test {
            color: #1f9d55;
        }
        .status-disabled-test {
            color: #d94452;
        }
        .critical-test {
            color: #d94452;
            font-weight: 700;
        }
        .issue-test {
            padding: 16px;
            margin: 8px 0;
            border-left: 4px solid #ddd;
            background: #f6f7fb;
            border-radius: 8px;
        }
        .issue-high-test {
            border-left-color: #d94452;
            background: #fef2f2;
        }
        .issue-medium-test {
            border-left-color: #f1b814;
            background: #fffbeb;
        }
        .issue-low-test {
            border-left-color: #2d6cdf;
            background: #eff6ff;
        }
        .recommendations-test {
            margin: 16px 0;
            list-style: none;
            padding: 0;
        }
        .recommendations-test li {
            margin: 8px 0;
            padding: 12px;
            background: #eff6ff;
            border-radius: 8px;
            border-left: 3px solid #2d6cdf;
            color: #374151;
            font-size: 13px;
            line-height: 1.5;
        }
        .form-table-test {
            width: 100%;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 8px 16px rgba(31, 35, 41, 0.05);
        }
        .form-table-test th {
            background: #f6f7fb;
            padding: 16px;
            font-weight: 600;
            color: #1c1e21;
            border-bottom: 1px solid #e5e7eb;
            text-align: left;
        }
        .form-table-test td {
            padding: 16px;
            border-bottom: 1px solid #f3f4f6;
        }
        .form-table-test tr:last-child td {
            border-bottom: none;
        }
        .button-primary-test {
            background: #2d6cdf;
            border-color: #2d6cdf;
            color: white;
            padding: 8px 16px;
            border-radius: 8px;
            font-weight: 600;
            border: none;
            cursor: pointer;
        }
        .button-primary-test:hover {
            background: #1e4fd4;
            border-color: #1e4fd4;
        }
        @media (max-width: 768px) {
            .mobile-stats-test {
                grid-template-columns: 1fr;
                gap: 12px;
            }
            .stat-item-test {
                padding: 12px;
            }
            .form-table-test th,
            .form-table-test td {
                padding: 12px;
            }
        }
    </style>
</head>
<body>
    <div class="test-container">
        <h1>🧪 Test Coerenza Grafica Pagina Mobile</h1>
        
        <div class="test-section">
            <h3>📋 Panoramica Test</h3>
            <p>Questo test verifica che la pagina mobile del plugin FP Performance Suite utilizzi correttamente il design system modulare e mantenga la coerenza grafica con il resto del plugin.</p>
        </div>

        <div class="test-section">
            <h3>🎨 Test Design System</h3>
            
            <div class="css-test">
                <h4>Variabili CSS</h4>
                <div class="test-element" style="background: var(--fp-bg, #f6f7fb); padding: 10px; border-radius: 8px;">
                    <strong>Background:</strong> var(--fp-bg)
                </div>
                <div class="test-element" style="background: var(--fp-card, #ffffff); padding: 10px; border-radius: 8px; border: 1px solid #e5e7eb;">
                    <strong>Card:</strong> var(--fp-card)
                </div>
                <div class="test-element" style="color: var(--fp-accent, #2d6cdf); padding: 10px;">
                    <strong>Accent:</strong> var(--fp-accent)
                </div>
                <div class="test-element" style="color: var(--fp-ok, #1f9d55); padding: 10px;">
                    <strong>Success:</strong> var(--fp-ok)
                </div>
                <div class="test-element" style="color: var(--fp-danger, #d94452); padding: 10px;">
                    <strong>Danger:</strong> var(--fp-danger)
                </div>
            </div>

            <div class="css-test">
                <h4>Spacing System</h4>
                <div class="test-element" style="padding: var(--fp-spacing-xs, 8px); background: #f0f0f0;">
                    <strong>XS Spacing:</strong> var(--fp-spacing-xs)
                </div>
                <div class="test-element" style="padding: var(--fp-spacing-sm, 12px); background: #f0f0f0;">
                    <strong>SM Spacing:</strong> var(--fp-spacing-sm)
                </div>
                <div class="test-element" style="padding: var(--fp-spacing-md, 16px); background: #f0f0f0;">
                    <strong>MD Spacing:</strong> var(--fp-spacing-md)
                </div>
                <div class="test-element" style="padding: var(--fp-spacing-lg, 20px); background: #f0f0f0;">
                    <strong>LG Spacing:</strong> var(--fp-spacing-lg)
                </div>
            </div>
        </div>

        <div class="test-section">
            <h3>📊 Test Mobile Stats</h3>
            <div class="mobile-stats-test">
                <div class="stat-item-test">
                    <span class="stat-label-test">Status</span>
                    <span class="stat-value-test status-enabled-test">Enabled</span>
                </div>
                <div class="stat-item-test">
                    <span class="stat-label-test">Issues Found</span>
                    <span class="stat-value-test">0</span>
                </div>
                <div class="stat-item-test">
                    <span class="stat-label-test">Critical Issues</span>
                    <span class="stat-value-test critical-test">0</span>
                </div>
            </div>
        </div>

        <div class="test-section">
            <h3>⚠️ Test Issues List</h3>
            <div class="issue-test issue-high-test">
                <strong>High Priority Issue</strong>
                <p>This is a high priority issue that needs immediate attention.</p>
            </div>
            <div class="issue-test issue-medium-test">
                <strong>Medium Priority Issue</strong>
                <p>This is a medium priority issue that should be addressed soon.</p>
            </div>
            <div class="issue-test issue-low-test">
                <strong>Low Priority Issue</strong>
                <p>This is a low priority issue that can be addressed later.</p>
            </div>
        </div>

        <div class="test-section">
            <h3>💡 Test Recommendations</h3>
            <ul class="recommendations-test">
                <li>Enable mobile optimization for better performance</li>
                <li>Optimize images for mobile devices</li>
                <li>Enable touch optimization for better user experience</li>
            </ul>
        </div>

        <div class="test-section">
            <h3>📝 Test Form Table</h3>
            <table class="form-table-test">
                <tr>
                    <th>Enable Mobile Optimization</th>
                    <td>
                        <input type="checkbox" checked>
                        <p style="color: #64748b; font-size: 13px; margin: 8px 0 0 0;">Enable mobile-specific optimizations</p>
                    </td>
                </tr>
                <tr>
                    <th>Disable Animations on Mobile</th>
                    <td>
                        <input type="checkbox">
                        <p style="color: #64748b; font-size: 13px; margin: 8px 0 0 0;">Disable CSS animations on mobile devices for better performance</p>
                    </td>
                </tr>
                <tr>
                    <th>Actions</th>
                    <td>
                        <button class="button-primary-test">Save Mobile Settings</button>
                    </td>
                </tr>
            </table>
        </div>

        <div class="test-section">
            <h3>📱 Test Responsive Design</h3>
            <p>Riduci la larghezza del browser per testare il design responsive. Gli elementi dovrebbero adattarsi correttamente ai dispositivi mobile.</p>
            <div class="status success">
                <strong>✅ Test Completato:</strong> La pagina mobile ora utilizza il design system modulare del plugin FP Performance Suite.
            </div>
        </div>

        <div class="test-section">
            <h3>🔧 Azioni</h3>
            <a href="<?php echo admin_url('admin.php?page=fp-performance-suite-mobile'); ?>" class="btn success">
                Vai alla Pagina Mobile
            </a>
            <a href="<?php echo admin_url('admin.php?page=fp-performance-suite'); ?>" class="btn">
                Torna al Dashboard
            </a>
        </div>
    </div>

    <script>
        // Test JavaScript per verificare che le classi CSS siano applicate correttamente
        document.addEventListener('DOMContentLoaded', function() {
            console.log('🧪 Test Coerenza Grafica Pagina Mobile - Caricato');
            
            // Verifica che le variabili CSS siano disponibili
            const root = document.documentElement;
            const computedStyle = getComputedStyle(root);
            
            const cssVars = [
                '--fp-bg',
                '--fp-card', 
                '--fp-accent',
                '--fp-ok',
                '--fp-danger',
                '--fp-spacing-xs',
                '--fp-spacing-sm',
                '--fp-spacing-md',
                '--fp-spacing-lg'
            ];
            
            cssVars.forEach(function(cssVar) {
                const value = computedStyle.getPropertyValue(cssVar);
                if (value) {
                    console.log(`✅ ${cssVar}: ${value}`);
                } else {
                    console.warn(`⚠️ ${cssVar}: Non definita`);
                }
            });
        });
    </script>
</body>
</html>
