<?php
/**
 * Script per Verificare i Backup delle Immagini
 * 
 * Controlla se esistono backup delle immagini danneggiate
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
 * Controlla i backup disponibili
 */
function check_available_backups() {
    $upload_dir = wp_upload_dir();
    $backup_dirs = [
        $upload_dir['basedir'] . '/fp-performance-backup',
        $upload_dir['basedir'] . '/backup',
        $upload_dir['basedir'] . '/fp-performance-suite/backup',
        WP_CONTENT_DIR . '/backup',
        WP_CONTENT_DIR . '/uploads/backup'
    ];
    
    $found_backups = [];
    
    foreach ($backup_dirs as $dir) {
        if (is_dir($dir)) {
            $files = scandir($dir);
            $image_files = array_filter($files, function($file) {
                return preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $file);
            });
            
            if (!empty($image_files)) {
                $found_backups[$dir] = count($image_files);
            }
        }
    }
    
    return $found_backups;
}

/**
 * Trova immagini danneggiate
 */
function find_damaged_images() {
    global $wpdb;
    
    $query = "
        SELECT p.ID, p.post_title, pm.meta_value as file_path
        FROM {$wpdb->posts} p
        INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
        INNER JOIN {$wpdb->postmeta} webp_meta ON p.ID = webp_meta.post_id
        WHERE p.post_type = 'attachment'
        AND p.post_mime_type LIKE 'image/%'
        AND pm.meta_key = '_wp_attached_file'
        AND webp_meta.meta_key = '_fp_ps_webp_generated'
        AND webp_meta.meta_value = '1'
        LIMIT 10
    ";
    
    $results = $wpdb->get_results($query);
    $damaged = [];
    
    foreach ($results as $attachment) {
        $file_path = get_attached_file($attachment->ID);
        if (!$file_path || !file_exists($file_path)) {
            $damaged[] = [
                'id' => $attachment->ID,
                'title' => $attachment->post_title,
                'path' => $file_path
            ];
        }
    }
    
    return $damaged;
}

// Esegui il controllo
$backups = check_available_backups();
$damaged = find_damaged_images();

?>
<div style="max-width: 800px; margin: 20px auto; padding: 20px; border: 1px solid #ddd;">
    <h1>🔍 Verifica Backup Immagini</h1>
    
    <div style="background: #f8f9fa; padding: 15px; margin: 10px 0;">
        <h3>📊 Risultati Controllo</h3>
        
        <?php if (!empty($backups)): ?>
            <div style="background: #d4edda; padding: 15px; margin: 10px 0; border: 1px solid #c3e6cb;">
                <h4>✅ Backup Trovati</h4>
                <?php foreach ($backups as $dir => $count): ?>
                    <p><strong><?php echo esc_html($dir); ?></strong> - <?php echo $count; ?> immagini</p>
                <?php endforeach; ?>
                <p style="color: green; font-weight: bold;">🎉 Buone notizie! Ci sono backup disponibili per il ripristino.</p>
            </div>
        <?php else: ?>
            <div style="background: #f8d7da; padding: 15px; margin: 10px 0; border: 1px solid #f5c6cb;">
                <h4>❌ Nessun Backup Trovato</h4>
                <p>Non sono stati trovati backup automatici delle immagini.</p>
                <p><strong>Opzioni di recupero:</strong></p>
                <ul>
                    <li>Backup del tuo hosting provider</li>
                    <li>Backup WordPress (se configurato)</li>
                    <li>Re-upload manuale delle immagini</li>
                </ul>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($damaged)): ?>
            <div style="background: #fff3cd; padding: 15px; margin: 10px 0; border: 1px solid #ffeaa7;">
                <h4>⚠️ Immagini Danneggiate Trovate</h4>
                <p><strong><?php echo count($damaged); ?> immagini potenzialmente danneggiate:</strong></p>
                <ul>
                    <?php foreach (array_slice($damaged, 0, 5) as $img): ?>
                        <li>ID <?php echo $img['id']; ?>: <?php echo esc_html($img['title']); ?></li>
                    <?php endforeach; ?>
                    <?php if (count($damaged) > 5): ?>
                        <li>... e altre <?php echo count($damaged) - 5; ?> immagini</li>
                    <?php endif; ?>
                </ul>
            </div>
        <?php else: ?>
            <div style="background: #d4edda; padding: 15px; margin: 10px 0; border: 1px solid #c3e6cb;">
                <h4>✅ Nessuna Immagine Danneggiata</h4>
                <p>Non sono state trovate immagini danneggiate dal bulk processing.</p>
            </div>
        <?php endif; ?>
    </div>
    
    <div style="text-align: center; margin: 20px 0;">
        <?php if (!empty($backups) && !empty($damaged)): ?>
            <a href="emergency-image-recovery.php" 
               style="background: #28a745; color: white; padding: 15px 30px; text-decoration: none; border-radius: 4px; margin: 5px; display: inline-block;">
                🔧 Avvia Ripristino
            </a>
        <?php endif; ?>
        
        <a href="disable-bulk-processing.php" 
           style="background: #dc3545; color: white; padding: 15px 30px; text-decoration: none; border-radius: 4px; margin: 5px; display: inline-block;">
            🛡️ Disabilita Bulk Processing
        </a>
    </div>
    
    <div style="background: #d1ecf1; padding: 15px; margin: 10px 0; border-left: 4px solid #17a2b8;">
        <h3>ℹ️ Informazioni Tecniche</h3>
        <p><strong>Directory controllate:</strong></p>
        <ul>
            <li><code>wp-content/uploads/fp-performance-backup</code></li>
            <li><code>wp-content/uploads/backup</code></li>
            <li><code>wp-content/uploads/fp-performance-suite/backup</code></li>
            <li><code>wp-content/backup</code></li>
            <li><code>wp-content/uploads/backup</code></li>
        </ul>
    </div>
</div>
