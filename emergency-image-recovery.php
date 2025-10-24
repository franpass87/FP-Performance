<?php
/**
 * Script di Emergenza per Ripristino Immagini
 * 
 * Questo script tenta di ripristinare le immagini danneggiate dal bulk processing
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
 * Classe per il ripristino delle immagini
 */
class FP_Performance_Image_Recovery {
    
    private $backup_dir;
    private $recovered_count = 0;
    private $errors = [];
    
    public function __construct() {
        $upload_dir = wp_upload_dir();
        $this->backup_dir = $upload_dir['basedir'] . '/fp-performance-backup';
    }
    
    /**
     * Avvia il processo di ripristino
     */
    public function start_recovery() {
        echo "<h2>🔧 Ripristino Immagini FP Performance Suite</h2>";
        
        // 1. Verifica se esistono backup
        if (!$this->check_backups()) {
            echo "<div style='color: red;'>❌ Nessun backup trovato. Impossibile ripristinare automaticamente.</div>";
            $this->show_manual_recovery_options();
            return;
        }
        
        // 2. Trova immagini danneggiate
        $damaged_images = $this->find_damaged_images();
        
        if (empty($damaged_images)) {
            echo "<div style='color: green;'>✅ Nessuna immagine danneggiata trovata.</div>";
            return;
        }
        
        echo "<div style='color: orange;'>⚠️ Trovate " . count($damaged_images) . " immagini potenzialmente danneggiate.</div>";
        
        // 3. Ripristina le immagini
        $this->recover_images($damaged_images);
        
        // 4. Mostra risultati
        $this->show_results();
    }
    
    /**
     * Verifica se esistono backup
     */
    private function check_backups() {
        return is_dir($this->backup_dir) && count(scandir($this->backup_dir)) > 2;
    }
    
    /**
     * Trova immagini danneggiate
     */
    private function find_damaged_images() {
        global $wpdb;
        
        // Trova attachment con meta WebP ma senza file originale
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
        ";
        
        $results = $wpdb->get_results($query);
        $damaged = [];
        
        foreach ($results as $attachment) {
            $file_path = get_attached_file($attachment->ID);
            if (!$file_path || !file_exists($file_path)) {
                $damaged[] = $attachment;
            }
        }
        
        return $damaged;
    }
    
    /**
     * Ripristina le immagini
     */
    private function recover_images($damaged_images) {
        foreach ($damaged_images as $attachment) {
            $this->recover_single_image($attachment);
        }
    }
    
    /**
     * Ripristina una singola immagine
     */
    private function recover_single_image($attachment) {
        $attachment_id = $attachment->ID;
        $original_path = get_attached_file($attachment_id);
        
        if (!$original_path) {
            $this->errors[] = "Impossibile ottenere il percorso per l'immagine ID: {$attachment_id}";
            return;
        }
        
        // Cerca backup
        $backup_path = $this->find_backup($original_path);
        
        if (!$backup_path) {
            $this->errors[] = "Nessun backup trovato per: " . basename($original_path);
            return;
        }
        
        // Ripristina il file
        if (copy($backup_path, $original_path)) {
            $this->recovered_count++;
            
            // Aggiorna metadata
            $this->update_attachment_metadata($attachment_id, $original_path);
            
            echo "<div style='color: green;'>✅ Ripristinata: " . basename($original_path) . "</div>";
        } else {
            $this->errors[] = "Impossibile copiare il backup per: " . basename($original_path);
        }
    }
    
    /**
     * Trova il backup di un file
     */
    private function find_backup($original_path) {
        $filename = basename($original_path);
        $backup_file = $this->backup_dir . '/' . $filename;
        
        if (file_exists($backup_file)) {
            return $backup_file;
        }
        
        // Cerca con estensioni diverse
        $extensions = ['.jpg', '.jpeg', '.png', '.webp'];
        foreach ($extensions as $ext) {
            $test_file = $this->backup_dir . '/' . pathinfo($filename, PATHINFO_FILENAME) . $ext;
            if (file_exists($test_file)) {
                return $test_file;
            }
        }
        
        return null;
    }
    
    /**
     * Aggiorna i metadata dell'attachment
     */
    private function update_attachment_metadata($attachment_id, $file_path) {
        // Genera nuovi metadata
        $metadata = wp_generate_attachment_metadata($attachment_id, $file_path);
        wp_update_attachment_metadata($attachment_id, $metadata);
        
        // Rimuovi flag WebP se il file originale è stato ripristinato
        delete_post_meta($attachment_id, '_fp_ps_webp_generated');
        delete_post_meta($attachment_id, '_fp_ps_webp_settings');
    }
    
    /**
     * Mostra opzioni di ripristino manuale
     */
    private function show_manual_recovery_options() {
        echo "<h3>🛠️ Opzioni di Ripristino Manuale</h3>";
        echo "<div style='background: #f0f0f0; padding: 15px; margin: 10px 0;'>";
        echo "<h4>1. Ripristino da Backup Hosting</h4>";
        echo "<p>Se hai un backup del tuo hosting, ripristina la cartella <code>wp-content/uploads</code> dal backup.</p>";
        
        echo "<h4>2. Ripristino da Backup WordPress</h4>";
        echo "<p>Se usi un plugin di backup, ripristina le immagini dalla libreria media.</p>";
        
        echo "<h4>3. Re-upload delle Immagini</h4>";
        echo "<p>Carica nuovamente le immagini mancanti dalla libreria media di WordPress.</p>";
        
        echo "<h4>4. Disabilita Temporaneamente il Plugin</h4>";
        echo "<p>Disabilita temporaneamente FP Performance Suite per evitare ulteriori danni.</p>";
        echo "</div>";
    }
    
    /**
     * Mostra i risultati del ripristino
     */
    private function show_results() {
        echo "<h3>📊 Risultati Ripristino</h3>";
        echo "<div style='background: #e8f5e8; padding: 15px; margin: 10px 0;'>";
        echo "<p><strong>Immagini ripristinate:</strong> {$this->recovered_count}</p>";
        
        if (!empty($this->errors)) {
            echo "<p><strong>Errori:</strong></p>";
            echo "<ul>";
            foreach ($this->errors as $error) {
                echo "<li style='color: red;'>{$error}</li>";
            }
            echo "</ul>";
        }
        echo "</div>";
        
        if ($this->recovered_count > 0) {
            echo "<div style='background: #fff3cd; padding: 15px; margin: 10px 0;'>";
            echo "<h4>⚠️ Azioni Consigliate</h4>";
            echo "<ol>";
            echo "<li>Verifica che le immagini siano visibili correttamente</li>";
            echo "<li>Controlla le impostazioni WebP del plugin</li>";
            echo "<li>Imposta <code>keep_original: true</code> per evitare futuri problemi</li>";
            echo "<li>Testa la conversione WebP su poche immagini prima di fare bulk</li>";
            echo "</ol>";
            echo "</div>";
        }
    }
}

// Esegui il ripristino se richiesto
if (isset($_GET['action']) && $_GET['action'] === 'recover') {
    $recovery = new FP_Performance_Image_Recovery();
    $recovery->start_recovery();
} else {
    // Mostra interfaccia
    ?>
    <div style="max-width: 800px; margin: 20px auto; padding: 20px; border: 1px solid #ddd;">
        <h1>🔧 Ripristino Immagini FP Performance Suite</h1>
        
        <div style="background: #fff3cd; padding: 15px; margin: 10px 0; border-left: 4px solid #ffc107;">
            <h3>⚠️ Attenzione</h3>
            <p>Questo script tenta di ripristinare le immagini danneggiate dal bulk processing WebP.</p>
            <p><strong>Assicurati di avere un backup prima di procedere!</strong></p>
        </div>
        
        <div style="background: #d1ecf1; padding: 15px; margin: 10px 0; border-left: 4px solid #17a2b8;">
            <h3>ℹ️ Informazioni</h3>
            <p>Il bulk processing WebP potrebbe aver eliminato i file originali quando <code>keep_original</code> era impostato su <code>false</code>.</p>
        </div>
        
        <div style="text-align: center; margin: 20px 0;">
            <a href="?action=recover" 
               style="background: #dc3545; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px;"
               onclick="return confirm('Sei sicuro di voler procedere con il ripristino?')">
                🔧 Avvia Ripristino
            </a>
        </div>
        
        <div style="background: #f8f9fa; padding: 15px; margin: 10px 0;">
            <h3>📋 Cosa fa questo script:</h3>
            <ul>
                <li>Identifica le immagini danneggiate</li>
                <li>Cerca backup automatici</li>
                <li>Ripristina i file originali</li>
                <li>Aggiorna i metadata WordPress</li>
                <li>Rimuove i flag WebP problematici</li>
            </ul>
        </div>
    </div>
    <?php
}
?>
