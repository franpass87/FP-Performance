<?php
/**
 * Verifica Status Sistema ML
 * Script per controllare se il sistema ML è abilitato e funzionante
 */

// Carica WordPress se disponibile
if (file_exists('../../../wp-load.php')) {
    require_once('../../../wp-load.php');
}

echo "<h1>🔍 Verifica Status Sistema ML</h1>\n";
echo "<style>body{font-family:Arial,sans-serif;margin:20px;} .success{color:green;} .error{color:red;} .warning{color:orange;} .info{color:blue;} .section{margin:20px 0;padding:15px;border:1px solid #ddd;border-radius:5px;}</style>\n";

if (!function_exists('get_option')) {
    echo "<div class='section'>\n";
    echo "<h2>⚠️ WordPress non trovato</h2>\n";
    echo "<p class='warning'>Questo script deve essere eseguito nel contesto di WordPress.</p>\n";
    echo "<p class='info'>Per verificare il sistema ML:</p>\n";
    echo "<ol>\n";
    echo "<li>Accedi all'admin di WordPress</li>\n";
    echo "<li>Vai in <strong>FP Performance > ML</strong></li>\n";
    echo "<li>Controlla la sezione 'Stato Sistema ML'</li>\n";
    echo "<li>Usa i pulsanti di test manuale</li>\n";
    echo "</ol>\n";
    echo "</div>\n";
    exit;
}

echo "<div class='section'>\n";
echo "<h2>📊 Status Sistema ML</h2>\n";

// Verifica se il plugin è attivo
if (!class_exists('FP\PerfSuite\Plugin')) {
    echo "<p class='error'>❌ Plugin FP Performance Suite non trovato o non attivo</p>\n";
    echo "</div>\n";
    exit;
}

// Verifica impostazioni ML
$ml_settings = get_option('fp_ps_ml_predictor', []);
$is_enabled = isset($ml_settings['enabled']) && $ml_settings['enabled'];

echo "<p class='info'>🔧 Sistema ML: " . ($is_enabled ? '<span class="success">Abilitato</span>' : '<span class="warning">Disabilitato</span>') . "</p>\n";

if (!$is_enabled) {
    echo "<p class='warning'>⚠️ Il sistema ML non è abilitato!</p>\n";
    echo "<p class='info'>💡 Per abilitarlo: Vai in FP Performance > ML > Impostazioni e abilita il sistema</p>\n";
    echo "</div>\n";
    exit;
}

// Verifica dati raccolti
$ml_data = get_option('fp_ps_ml_data', []);
$data_count = count($ml_data);

echo "<p class='info'>📈 Dati raccolti: " . $data_count . " punti</p>\n";

if ($data_count < 10) {
    echo "<p class='warning'>⚠️ Dati insufficienti per analisi ML (minimo 10 punti consigliati)</p>\n";
    echo "<p class='info'>💡 Naviga il sito per generare più dati</p>\n";
} else {
    $latest_data = end($ml_data);
    $time_diff = time() - $latest_data['timestamp'];
    echo "<p class='info'>🕒 Ultimo dato: " . date('Y-m-d H:i:s', $latest_data['timestamp']) . " (" . 
        ($time_diff < 3600 ? 'meno di 1 ora fa' : round($time_diff/3600, 1) . ' ore fa') . ")</p>\n";
}

// Verifica cron jobs
$next_analysis = wp_next_scheduled('fp_ps_ml_analyze_patterns');
$next_prediction = wp_next_scheduled('fp_ps_ml_predict_issues');

echo "<p class='info'>⏰ Prossima analisi: " . 
    ($next_analysis ? date('Y-m-d H:i:s', $next_analysis) : '<span class="warning">Non programmata</span>') . "</p>\n";
echo "<p class='info'>⏰ Prossima predizione: " . 
    ($next_prediction ? date('Y-m-d H:i:s', $next_prediction) : '<span class="warning">Non programmata</span>') . "</p>\n";

if (!$next_analysis || !$next_prediction) {
    echo "<p class='warning'>⚠️ Cron jobs non programmati correttamente!</p>\n";
    echo "<p class='info'>💡 Usa 'Verifica Cron Jobs' nella pagina ML per riprogrammarli</p>\n";
}

// Verifica pattern appresi
$learned_patterns = get_option('fp_ps_learned_patterns', []);
echo "<p class='info'>🧠 Pattern appresi: " . count($learned_patterns) . "</p>\n";

// Verifica predizioni
$predictions = get_option('fp_ps_ml_predictions', []);
echo "<p class='info'>🔮 Predizioni: " . count($predictions) . "</p>\n";

// Verifica anomalie
$anomalies = get_option('fp_ps_ml_anomalies', []);
echo "<p class='info'>🚨 Anomalie: " . count($anomalies) . "</p>\n";

echo "</div>\n";

echo "<div class='section'>\n";
echo "<h2>🔍 Diagnostica</h2>\n";

$issues = [];

if (!$is_enabled) {
    $issues[] = "Sistema ML non abilitato";
}

if ($data_count < 10) {
    $issues[] = "Dati insufficienti per analisi";
}

if (!$next_analysis || !$next_prediction) {
    $issues[] = "Cron jobs non programmati";
}

if (empty($learned_patterns)) {
    $issues[] = "Nessun pattern appreso";
}

if (empty($issues)) {
    echo "<p class='success'>✅ Sistema ML sembra funzionare correttamente</p>\n";
    echo "<p class='info'>Se non rileva anomalie, potrebbe essere normale se:</p>\n";
    echo "<ul>\n";
    echo "<li>Il sito ha performance stabili</li>\n";
    echo "<li>Non ci sono comportamenti anomali</li>\n";
    echo "<li>Le soglie di rilevamento sono troppo alte</li>\n";
    echo "</ul>\n";
} else {
    echo "<p class='warning'>⚠️ Problemi rilevati:</p>\n";
    echo "<ul>\n";
    foreach ($issues as $issue) {
        echo "<li>" . $issue . "</li>\n";
    }
    echo "</ul>\n";
}

echo "</div>\n";

echo "<div class='section'>\n";
echo "<h2>🔧 Azioni Consigliate</h2>\n";

if (!$is_enabled) {
    echo "<p class='info'>1. Abilita il sistema ML nelle impostazioni</p>\n";
}

if ($data_count < 10) {
    echo "<p class='info'>2. Naviga il sito per generare più dati</p>\n";
}

if (!$next_analysis || !$next_prediction) {
    echo "<p class='info'>3. Usa 'Verifica Cron Jobs' per riprogrammarli</p>\n";
}

echo "<p class='info'>4. Usa i test manuali nella pagina ML:</p>\n";
echo "<ul>\n";
echo "<li><strong>Esegui Analisi Pattern</strong> - Per analizzare i dati</li>\n";
echo "<li><strong>Rileva Anomalie</strong> - Per cercare anomalie</li>\n";
echo "<li><strong>Genera Predizioni</strong> - Per generare predizioni</li>\n";
echo "</ul>\n";

echo "</div>\n";

echo "<hr>\n";
echo "<p><strong>Verifica completata!</strong></p>\n";
?>
