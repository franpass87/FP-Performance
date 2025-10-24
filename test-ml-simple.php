<?php
/**
 * Test semplice del sistema ML
 * Verifica le impostazioni e i dati senza caricare WordPress
 */

echo "<h1>🔍 Test Sistema ML - FP Performance Suite</h1>\n";
echo "<style>body{font-family:Arial,sans-serif;margin:20px;} .success{color:green;} .error{color:red;} .warning{color:orange;} .info{color:blue;} .section{margin:20px 0;padding:15px;border:1px solid #ddd;border-radius:5px;}</style>\n";

// Verifica se siamo in WordPress
if (!function_exists('get_option')) {
    echo "<div class='section'>\n";
    echo "<h2>⚠️ Test Richiede WordPress</h2>\n";
    echo "<p class='warning'>Questo test deve essere eseguito nel contesto di WordPress.</p>\n";
    echo "<p class='info'>Per eseguire il test:</p>\n";
    echo "<ol>\n";
    echo "<li>Carica questo file tramite WordPress admin</li>\n";
    echo "<li>Oppure esegui: <code>wp eval-file test-ml-simple.php</code> (se hai WP-CLI)</li>\n";
    echo "<li>Oppure accedi alla pagina ML nel plugin e usa i pulsanti di test manuale</li>\n";
    echo "</ol>\n";
    echo "</div>\n";
    
    echo "<div class='section'>\n";
    echo "<h2>🔍 Analisi del Codice ML</h2>\n";
    echo "<p class='info'>Dal codice analizzato, ho identificato i seguenti punti:</p>\n";
    echo "<ul>\n";
    echo "<li><strong>Raccolta Dati:</strong> Il sistema raccoglie dati ad ogni shutdown della pagina</li>\n";
    echo "<li><strong>Cron Jobs:</strong> Analisi pattern ogni ora, predizioni ogni 6 ore</li>\n";
    echo "<li><strong>Rilevamento Anomalie:</strong> Confronta dati attuali con storici</li>\n";
    echo "<li><strong>Pattern Learning:</strong> Apprende dai dati raccolti</li>\n";
    echo "</ul>\n";
    echo "</div>\n";
    
    echo "<div class='section'>\n";
    echo "<h2>🚨 Possibili Motivi per cui non rileva anomalie:</h2>\n";
    echo "<ol>\n";
    echo "<li><strong>Sistema non abilitato:</strong> Verifica nelle impostazioni ML</li>\n";
    echo "<li><strong>Dati insufficienti:</strong> Serve tempo per raccogliere dati storici</li>\n";
    echo "<li><strong>Soglie troppo alte:</strong> Le soglie di confidence potrebbero essere troppo restrittive</li>\n";
    echo "<li><strong>Cron jobs non attivi:</strong> WordPress cron potrebbe non funzionare</li>\n";
    echo "<li><strong>Performance stabili:</strong> Se il sito ha performance costanti, non ci sono anomalie da rilevare</li>\n";
    echo "</ol>\n";
    echo "</div>\n";
    
    echo "<div class='section'>\n";
    echo "<h2>🔧 Soluzioni Consigliate:</h2>\n";
    echo "<ol>\n";
    echo "<li><strong>Verifica abilitazione:</strong> Vai in FP Performance > ML > Impostazioni</li>\n";
    echo "<li><strong>Test manuale:</strong> Usa i pulsanti 'Esegui Analisi Pattern' e 'Rileva Anomalie'</li>\n";
    echo "<li><strong>Controlla cron:</strong> Usa 'Verifica Cron Jobs' per vedere se sono programmati</li>\n";
    echo "<li><strong>Riduci soglie:</strong> Abbassa le soglie di confidence nelle impostazioni</li>\n";
    echo "<li><strong>Forza raccolta:</strong> Naviga il sito per generare dati</li>\n";
    echo "</ol>\n";
    echo "</div>\n";
    
    exit;
}

// Se siamo in WordPress, esegui il test completo
echo "<div class='section'>\n";
echo "<h2>📊 Verifica Impostazioni ML</h2>\n";

$ml_settings = get_option('fp_ps_ml_predictor', []);
echo "<p class='info'>Impostazioni ML:</p>\n";
echo "<ul>\n";
echo "<li>Abilitato: " . (isset($ml_settings['enabled']) && $ml_settings['enabled'] ? 'Sì' : 'No') . "</li>\n";
echo "<li>Giorni ritenzione: " . ($ml_settings['data_retention_days'] ?? 'Non impostato') . "</li>\n";
echo "<li>Soglia predizione: " . ($ml_settings['prediction_threshold'] ?? 'Non impostato') . "</li>\n";
echo "<li>Soglia anomalia: " . ($ml_settings['anomaly_threshold'] ?? 'Non impostato') . "</li>\n";
echo "</ul>\n";
echo "</div>\n";

echo "<div class='section'>\n";
echo "<h2>📈 Verifica Dati ML</h2>\n";

$ml_data = get_option('fp_ps_ml_data', []);
echo "<p class='info'>Dati ML raccolti: " . count($ml_data) . " punti</p>\n";

if (empty($ml_data)) {
    echo "<p class='warning'>⚠️ Nessun dato ML trovato!</p>\n";
    echo "<p class='info'>Il sistema potrebbe non aver ancora raccolto dati o non essere abilitato.</p>\n";
} else {
    $latest = end($ml_data);
    echo "<p class='success'>✅ Ultimo dato: " . date('Y-m-d H:i:s', $latest['timestamp']) . "</p>\n";
}

echo "</div>\n";

echo "<div class='section'>\n";
echo "<h2>⏰ Verifica Cron Jobs</h2>\n";

$next_analysis = wp_next_scheduled('fp_ps_ml_analyze_patterns');
$next_prediction = wp_next_scheduled('fp_ps_ml_predict_issues');

echo "<p class='info'>Prossima analisi: " . 
    ($next_analysis ? date('Y-m-d H:i:s', $next_analysis) : 'Non programmata') . "</p>\n";
echo "<p class='info'>Prossima predizione: " . 
    ($next_prediction ? date('Y-m-d H:i:s', $next_prediction) : 'Non programmata') . "</p>\n";

if (!$next_analysis || !$next_prediction) {
    echo "<p class='warning'>⚠️ Cron jobs non programmati!</p>\n";
}
echo "</div>\n";

echo "<div class='section'>\n";
echo "<h2>🧪 Test Manuale</h2>\n";
echo "<p class='info'>Per testare manualmente il sistema ML:</p>\n";
echo "<ol>\n";
echo "<li>Vai in <strong>FP Performance > ML</strong></li>\n";
echo "<li>Usa i pulsanti nella sezione 'Azioni Manuali':</li>\n";
echo "<ul>\n";
echo "<li><strong>Esegui Analisi Pattern</strong> - Analizza i dati raccolti</li>\n";
echo "<li><strong>Rileva Anomalie</strong> - Cerca anomalie nei dati</li>\n";
echo "<li><strong>Genera Predizioni</strong> - Genera predizioni basate sui pattern</li>\n";
echo "<li><strong>Verifica Cron Jobs</strong> - Controlla lo stato dei cron job</li>\n";
echo "</ul>\n";
echo "</ol>\n";
echo "</div>\n";
?>
