<?php
/**
 * Test completo del sistema ML
 * Verifica se il sistema ML sta effettivamente funzionando
 */

// Carica WordPress
require_once('../../../wp-load.php');

// Verifica se il plugin è attivo
if (!class_exists('FP\PerfSuite\Plugin')) {
    die('Plugin FP Performance Suite non trovato o non attivo');
}

use FP\PerfSuite\ServiceContainer;
use FP\PerfSuite\Services\ML\MLPredictor;
use FP\PerfSuite\Services\ML\AnomalyDetector;
use FP\PerfSuite\Services\ML\PatternLearner;
use FP\PerfSuite\Services\ML\AutoTuner;

echo "<h1>🔍 Test Sistema ML - FP Performance Suite</h1>\n";
echo "<style>body{font-family:Arial,sans-serif;margin:20px;} .success{color:green;} .error{color:red;} .warning{color:orange;} .info{color:blue;} .section{margin:20px 0;padding:15px;border:1px solid #ddd;border-radius:5px;}</style>\n";

try {
    // Inizializza container
    $container = new ServiceContainer();
    
    echo "<div class='section'>\n";
    echo "<h2>📊 1. Verifica Servizi ML</h2>\n";
    
    // Test MLPredictor
    try {
        $predictor = $container->get(MLPredictor::class);
        echo "<p class='success'>✅ MLPredictor caricato correttamente</p>\n";
        
        $settings = $predictor->getSettings();
        echo "<p class='info'>📋 Impostazioni MLPredictor:</p>\n";
        echo "<ul>\n";
        echo "<li>Abilitato: " . ($settings['enabled'] ? 'Sì' : 'No') . "</li>\n";
        echo "<li>Giorni di ritenzione dati: " . $settings['data_retention_days'] . "</li>\n";
        echo "<li>Soglia predizione: " . $settings['prediction_threshold'] . "</li>\n";
        echo "<li>Soglia anomalia: " . $settings['anomaly_threshold'] . "</li>\n";
        echo "</ul>\n";
        
        if (!$settings['enabled']) {
            echo "<p class='warning'>⚠️ Sistema ML non abilitato!</p>\n";
        }
    } catch (Exception $e) {
        echo "<p class='error'>❌ Errore MLPredictor: " . $e->getMessage() . "</p>\n";
    }
    
    // Test AnomalyDetector
    try {
        $anomaly_detector = $container->get(AnomalyDetector::class);
        echo "<p class='success'>✅ AnomalyDetector caricato correttamente</p>\n";
    } catch (Exception $e) {
        echo "<p class='error'>❌ Errore AnomalyDetector: " . $e->getMessage() . "</p>\n";
    }
    
    // Test PatternLearner
    try {
        $pattern_learner = $container->get(PatternLearner::class);
        echo "<p class='success'>✅ PatternLearner caricato correttamente</p>\n";
    } catch (Exception $e) {
        echo "<p class='error'>❌ Errore PatternLearner: " . $e->getMessage() . "</p>\n";
    }
    
    // Test AutoTuner
    try {
        $auto_tuner = $container->get(AutoTuner::class);
        echo "<p class='success'>✅ AutoTuner caricato correttamente</p>\n";
    } catch (Exception $e) {
        echo "<p class='error'>❌ Errore AutoTuner: " . $e->getMessage() . "</p>\n";
    }
    
    echo "</div>\n";
    
    echo "<div class='section'>\n";
    echo "<h2>📈 2. Verifica Raccolta Dati</h2>\n";
    
    // Verifica dati ML
    $ml_data = get_option('fp_ps_ml_data', []);
    echo "<p class='info'>📊 Dati ML memorizzati: " . count($ml_data) . " punti</p>\n";
    
    if (empty($ml_data)) {
        echo "<p class='warning'>⚠️ Nessun dato ML trovato! Il sistema potrebbe non raccogliere dati.</p>\n";
    } else {
        $latest_data = end($ml_data);
        echo "<p class='info'>🕒 Ultimo dato raccolto: " . date('Y-m-d H:i:s', $latest_data['timestamp']) . "</p>\n";
        echo "<p class='info'>📋 Esempio dati:</p>\n";
        echo "<pre>" . print_r($latest_data, true) . "</pre>\n";
    }
    
    // Verifica pattern appresi
    $learned_patterns = get_option('fp_ps_learned_patterns', []);
    echo "<p class='info'>🧠 Pattern appresi: " . count($learned_patterns) . "</p>\n";
    
    // Verifica predizioni
    $predictions = get_option('fp_ps_ml_predictions', []);
    echo "<p class='info'>🔮 Predizioni memorizzate: " . count($predictions) . "</p>\n";
    
    // Verifica anomalie
    $anomalies = get_option('fp_ps_ml_anomalies', []);
    echo "<p class='info'>🚨 Anomalie rilevate: " . count($anomalies) . "</p>\n";
    
    echo "</div>\n";
    
    echo "<div class='section'>\n";
    echo "<h2>⏰ 3. Verifica Cron Jobs</h2>\n";
    
    $next_analysis = wp_next_scheduled('fp_ps_ml_analyze_patterns');
    $next_prediction = wp_next_scheduled('fp_ps_ml_predict_issues');
    
    echo "<p class='info'>📅 Prossima analisi pattern: " . 
        ($next_analysis ? date('Y-m-d H:i:s', $next_analysis) : 'Non programmata') . "</p>\n";
    echo "<p class='info'>📅 Prossima predizione: " . 
        ($next_prediction ? date('Y-m-d H:i:s', $next_prediction) : 'Non programmata') . "</p>\n";
    
    if (!$next_analysis || !$next_prediction) {
        echo "<p class='warning'>⚠️ Alcuni cron job non sono programmati!</p>\n";
    }
    
    echo "</div>\n";
    
    echo "<div class='section'>\n";
    echo "<h2>🧪 4. Test Funzionalità ML</h2>\n";
    
    if (isset($predictor) && $predictor->isEnabled()) {
        echo "<h3>Test Rilevamento Anomalie</h3>\n";
        try {
            $anomalies = $predictor->detectAnomalies();
            echo "<p class='success'>✅ Rilevamento anomalie completato: " . count($anomalies) . " anomalie trovate</p>\n";
            
            if (!empty($anomalies)) {
                echo "<p class='info'>🔍 Anomalie rilevate:</p>\n";
                foreach ($anomalies as $anomaly) {
                    echo "<p>- " . $anomaly['message'] . " (Confidence: " . $anomaly['confidence'] . ")</p>\n";
                }
            }
        } catch (Exception $e) {
            echo "<p class='error'>❌ Errore rilevamento anomalie: " . $e->getMessage() . "</p>\n";
        }
        
        echo "<h3>Test Generazione Predizioni</h3>\n";
        try {
            $predictions = $predictor->predictIssues();
            echo "<p class='success'>✅ Generazione predizioni completata: " . count($predictions) . " predizioni generate</p>\n";
            
            if (!empty($predictions)) {
                echo "<p class='info'>🔮 Predizioni generate:</p>\n";
                foreach ($predictions as $prediction) {
                    echo "<p>- " . $prediction['message'] . " (Confidence: " . $prediction['confidence'] . ")</p>\n";
                }
            }
        } catch (Exception $e) {
            echo "<p class='error'>❌ Errore generazione predizioni: " . $e->getMessage() . "</p>\n";
        }
        
        echo "<h3>Test Analisi Pattern</h3>\n";
        try {
            $predictor->analyzePatterns();
            echo "<p class='success'>✅ Analisi pattern completata</p>\n";
        } catch (Exception $e) {
            echo "<p class='error'>❌ Errore analisi pattern: " . $e->getMessage() . "</p>\n";
        }
    } else {
        echo "<p class='warning'>⚠️ Sistema ML non abilitato - test saltati</p>\n";
    }
    
    echo "</div>\n";
    
    echo "<div class='section'>\n";
    echo "<h2>📊 5. Report Sistema ML</h2>\n";
    
    if (isset($predictor)) {
        try {
            $ml_report = $predictor->generateMLReport();
            echo "<p class='info'>📈 Report ML:</p>\n";
            echo "<ul>\n";
            echo "<li>Data Points: " . $ml_report['data_points'] . "</li>\n";
            echo "<li>Model Accuracy: " . $ml_report['model_accuracy'] . "%</li>\n";
            echo "<li>Last Analysis: " . date('Y-m-d H:i:s', $ml_report['last_analysis']) . "</li>\n";
            echo "<li>Next Analysis: " . date('Y-m-d H:i:s', $ml_report['next_analysis']) . "</li>\n";
            echo "</ul>\n";
        } catch (Exception $e) {
            echo "<p class='error'>❌ Errore generazione report: " . $e->getMessage() . "</p>\n";
        }
    }
    
    echo "</div>\n";
    
    echo "<div class='section'>\n";
    echo "<h2>🔧 6. Diagnostica e Soluzioni</h2>\n";
    
    $issues_found = false;
    
    // Verifica se il sistema è abilitato
    if (isset($predictor) && !$predictor->isEnabled()) {
        echo "<p class='warning'>⚠️ Sistema ML non abilitato</p>\n";
        echo "<p class='info'>💡 Soluzione: Abilita il sistema ML nelle impostazioni</p>\n";
        $issues_found = true;
    }
    
    // Verifica se ci sono dati
    if (empty($ml_data)) {
        echo "<p class='warning'>⚠️ Nessun dato ML raccolto</p>\n";
        echo "<p class='info'>💡 Soluzione: Il sistema potrebbe aver bisogno di tempo per raccogliere dati</p>\n";
        $issues_found = true;
    }
    
    // Verifica cron jobs
    if (!$next_analysis || !$next_prediction) {
        echo "<p class='warning'>⚠️ Cron jobs non programmati</p>\n";
        echo "<p class='info'>💡 Soluzione: Riprogramma i cron jobs</p>\n";
        $issues_found = true;
    }
    
    if (!$issues_found) {
        echo "<p class='success'>✅ Sistema ML sembra funzionare correttamente</p>\n";
    }
    
    echo "</div>\n";
    
} catch (Exception $e) {
    echo "<p class='error'>❌ Errore generale: " . $e->getMessage() . "</p>\n";
}

echo "<hr>\n";
echo "<p><strong>Test completato!</strong></p>\n";
echo "<p>Se il sistema ML non rileva anomalie, potrebbe essere normale se:</p>\n";
echo "<ul>\n";
echo "<li>Il sito ha performance stabili</li>\n";
echo "<li>Non ci sono abbastanza dati storici per il confronto</li>\n";
echo "<li>Le soglie di rilevamento sono troppo alte</li>\n";
echo "</ul>\n";
?>
