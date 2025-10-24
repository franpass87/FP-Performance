<?php
/**
 * Script SICURO per Controllare le Immagini
 * 
 * Questo script è COMPLETAMENTE SICURO - solo lettura, nessuna modifica
 * 
 * @package FP\PerfSuite
 * @author Francesco Passeri
 */

// Verifica che sia eseguito da WordPress
if (!defined('ABSPATH')) {
    die('Accesso diretto non consentito');
}

// Solo per amministratori
if (!current_user_can('manage_options')) {
    wp_die('Accesso negato');
}

/**
 * Controlla SOLO le immagini danneggiate (SENZA MODIFICARE NULLA)
 */
function safe_check_images() {
    global $wpdb;
    
    echo "<h2>🔍 Controllo SICURO delle Immagini</h2>";
    echo "<div style='background: #d4edda; padding: 15px; margin: 10px 0; border: 1px solid #c3e6cb;'>";
    echo "<strong>✅ SICURO:</strong> Questo script NON modifica nulla, solo legge le informazioni.";
    echo "</div>";
    
    // 1. Conta immagini totali
    $total_images = $wpdb->get_var("
        SELECT COUNT(*) FROM {$wpdb->posts} 
        WHERE post_type = 'attachment' 
        AND post_mime_type LIKE 'image/%'
    ");
    
    echo "<h3>📊 Statistiche Immagini</h3>";
    echo "<p><strong>Immagini totali nella libreria:</strong> {$total_images}</p>";
    
    // 2. Conta immagini con WebP
    $webp_images = $wpdb->get_var("
        SELECT COUNT(DISTINCT p.ID) 
        FROM {$wpdb->posts} p
        INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
        WHERE p.post_type = 'attachment'
        AND p.post_mime_type LIKE 'image/%'
        AND pm.meta_key = '_fp_ps_webp_generated'
        AND pm.meta_value = '1'
    ");
    
    echo "<p><strong>Immagini con conversione WebP:</strong> {$webp_images}</p>";
    
    // 3. Controlla immagini potenzialmente danneggiate
    $damaged_query = "
        SELECT p.ID, p.post_title, pm.meta_value as file_path
        FROM {$wpdb->posts} p
        INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
        INNER JOIN {$wpdb->postmeta} webp_meta ON p.ID = webp_meta.post_id
        WHERE p.post_type = 'attachment'
        AND p.post_mime_type LIKE 'image/%'
        AND pm.meta_key = '_wp_attached_file'
        AND webp_meta.meta_key = '_fp_ps_webp_generated'
        AND webp_meta.meta_value = '1'
        LIMIT 20
    ";
    
    $results = $wpdb->get_results($damaged_query);
    $damaged_count = 0;
    $damaged_list = [];
    
    foreach ($results as $attachment) {
        $file_path = get_attached_file($attachment->ID);
        if (!$file_path || !file_exists($file_path)) {
            $damaged_count++;
            $damaged_list[] = [
                'id' => $attachment->ID,
                'title' => $attachment->post_title,
                'path' => $file_path
            ];
        }
    }
    
    echo "<p><strong>Immagini potenzialmente danneggiate:</strong> {$damaged_count}</p>";
    
    if ($damaged_count > 0) {
        echo "<div style='background: #fff3cd; padding: 15px; margin: 10px 0; border: 1px solid #ffeaa7;'>";
        echo "<h4>⚠️ Immagini Danneggiate Trovate</h4>";
        echo "<p>Ecco le prime 10 immagini che potrebbero essere danneggiate:</p>";
        echo "<ul>";
        foreach (array_slice($damaged_list, 0, 10) as $img) {
            echo "<li>ID {$img['id']}: " . esc_html($img['title']) . "</li>";
        }
        if (count($damaged_list) > 10) {
            echo "<li>... e altre " . (count($damaged_list) - 10) . " immagini</li>";
        }
        echo "</ul>";
        echo "</div>";
    } else {
        echo "<div style='background: #d4edda; padding: 15px; margin: 10px 0; border: 1px solid #c3e6cb;'>";
        echo "<h4>✅ Nessuna Immagine Danneggiata</h4>";
        echo "<p>Non sono state trovate immagini danneggiate dal bulk processing.</p>";
        echo "</div>";
    }
    
    // 4. Controlla backup disponibili
    $upload_dir = wp_upload_dir();
    $backup_dirs = [
        $upload_dir['basedir'] . '/fp-performance-backup',
        $upload_dir['basedir'] . '/backup',
        $upload_dir['basedir'] . '/fp-performance-suite/backup'
    ];
    
    $backup_found = false;
    echo "<h3>💾 Controllo Backup</h3>";
    
    foreach ($backup_dirs as $dir) {
        if (is_dir($dir)) {
            $files = scandir($dir);
            $image_files = array_filter($files, function($file) {
                return preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $file);
            });
            
            if (!empty($image_files)) {
                $backup_found = true;
                echo "<p><strong>✅ Backup trovato in:</strong> {$dir} ({$count} immagini)</p>";
            }
        }
    }
    
    if (!$backup_found) {
        echo "<div style='background: #f8d7da; padding: 15px; margin: 10px 0; border: 1px solid #f5c6cb;'>";
        echo "<h4>❌ Nessun Backup Automatico Trovato</h4>";
        echo "<p>Non sono stati trovati backup automatici delle immagini.</p>";
        echo "</div>";
    }
    
    // 5. Suggerimenti sicuri
    echo "<h3>💡 Suggerimenti Sicuri</h3>";
    echo "<div style='background: #f8f9fa; padding: 15px; margin: 10px 0;'>";
    echo "<ol>";
    echo "<li><strong>Fai un backup completo</strong> del tuo sito prima di qualsiasi operazione</li>";
    echo "<li><strong>Disabilita temporaneamente</strong> il plugin FP Performance Suite</li>";
    echo "<li><strong>Controlla la libreria media</strong> per vedere quali immagini mancano</li>";
    echo "<li><strong>Ripristina da backup hosting</strong> se disponibile</li>";
    echo "<li><strong>Re-uploada manualmente</strong> le immagini mancanti</li>";
    echo "</ol>";
    echo "</div>";
}

// Esegui il controllo sicuro
safe_check_images();
?>
