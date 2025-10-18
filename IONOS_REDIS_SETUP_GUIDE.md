# Guida Configurazione Redis/Memcached su Ionos

## 📋 Verifica Requisiti

### Passo 1: Controlla se Redis è disponibile

Crea un file PHP temporaneo nella root del tuo sito WordPress chiamato `check-redis.php` con questo contenuto:

```php
<?php
// Check Redis
echo "<h2>Verifica Redis/Memcached su Ionos</h2>";

echo "<h3>1. Estensione Redis PHP</h3>";
if (class_exists('Redis')) {
    echo "✅ <strong>Redis è disponibile!</strong><br>";
    $redis = new Redis();
    echo "Versione Redis PHP: " . phpversion('redis') . "<br>";
} else {
    echo "❌ Redis NON disponibile<br>";
}

echo "<h3>2. Estensione Memcached PHP</h3>";
if (class_exists('Memcached')) {
    echo "✅ <strong>Memcached è disponibile!</strong><br>";
    $memcached = new Memcached();
    echo "Versione Memcached PHP: " . phpversion('memcached') . "<br>";
} else {
    echo "❌ Memcached NON disponibile<br>";
}

echo "<h3>3. Test Connessione Redis (localhost)</h3>";
if (class_exists('Redis')) {
    try {
        $redis = new Redis();
        $connected = $redis->connect('127.0.0.1', 6379, 1);
        if ($connected) {
            echo "✅ <strong>Connessione a Redis riuscita!</strong><br>";
            echo "Versione server Redis: " . $redis->info()['redis_version'] . "<br>";
            $redis->close();
        } else {
            echo "❌ Impossibile connettersi a Redis su 127.0.0.1:6379<br>";
        }
    } catch (Exception $e) {
        echo "❌ Errore connessione Redis: " . $e->getMessage() . "<br>";
    }
} else {
    echo "⚠️ Skip (Redis non disponibile)<br>";
}

echo "<h3>4. Informazioni Hosting</h3>";
echo "PHP Version: " . phpversion() . "<br>";
echo "Server Software: " . $_SERVER['SERVER_SOFTWARE'] . "<br>";

// IMPORTANTE: Elimina questo file dopo il controllo!
echo "<hr><p style='color:red;'><strong>⚠️ IMPORTANTE: Elimina questo file dopo aver verificato!</strong></p>";
?>
```

**Accedi al file tramite browser**: `https://tuosito.com/check-redis.php`

### Passo 2: Interpretazione dei risultati

#### ✅ CASO 1: Redis è disponibile
Se vedi "Redis è disponibile" e "Connessione riuscita", puoi procedere con la configurazione nel plugin:

**Configurazione nel plugin FP Performance Suite:**
1. Vai in WordPress Admin → FP Performance Suite → Cache
2. Abilita "Object Cache"
3. Impostazioni:
   - **Driver**: `Auto` (o `Redis`)
   - **Host**: `127.0.0.1`
   - **Porta**: `6379`
   - **Password**: (lascia vuoto se non richiesta)
   - **Prefisso chiavi**: `fp_ps_` (o un nome univoco se hai più siti)
4. Salva le impostazioni

#### ❌ CASO 2: Redis NON disponibile

Hai diverse opzioni:

---

## 🎯 SOLUZIONI per Ionos Hosting Condiviso

### Opzione A: Contatta il Supporto Ionos
1. **Apri un ticket** al supporto tecnico Ionos
2. **Chiedi specificamente**:
   - "Vorrei attivare Redis o Memcached per il mio piano hosting"
   - "Il mio sito WordPress necessita di Object Caching persistente"
   - "È possibile attivare l'estensione php-redis o php-memcached?"
3. **Possibili risposte**:
   - ✅ Lo attivano gratuitamente
   - 💰 Richiede upgrade del piano
   - ❌ Non disponibile per il tuo piano

### Opzione B: Upgrade a Piano Superiore
Ionos offre Redis su:
- **Hosting WordPress Pro/Expert** (gestito, con Redis pre-configurato)
- **VPS Linux** (installazione manuale possibile)
- **Cloud Server** (pieno controllo)

### Opzione C: Soluzioni Alternative (senza Redis)

Se Redis non è disponibile, il plugin **continuerà a funzionare** con le altre ottimizzazioni:

**Ottimizzazioni attive senza Redis:**
- ✅ Page Cache (cache HTML su filesystem)
- ✅ Browser Cache Headers
- ✅ Compressione Gzip/Brotli
- ✅ Minificazione CSS/JS
- ✅ Ottimizzazione Database
- ✅ Lazy Loading immagini

**Nota**: L'Object Cache è un miglioramento significativo ma non essenziale se hai già attive le altre ottimizzazioni.

### Opzione D: Redis Esterno (Cloud)
Se hai un sito ad alto traffico, puoi usare un servizio Redis esterno:

1. **Redis Cloud** (gratuito fino a 30MB): https://redis.com/redis-enterprise-cloud/
2. **DigitalOcean Managed Redis**
3. **AWS ElastiCache**

**Configurazione con Redis esterno:**
- **Host**: `il-tuo-endpoint.redis.cloud` (fornito dal servizio)
- **Porta**: `porta-fornita` (es: 6379)
- **Password**: `la-tua-password-redis`

---

## 🔧 SOLUZIONI per Ionos VPS/Cloud

Se hai un VPS o Cloud Server Ionos, puoi installare Redis manualmente:

### Installazione Redis su Ubuntu/Debian (VPS Ionos)

```bash
# 1. Connettiti via SSH al tuo VPS
ssh utente@tuo-ip-ionos

# 2. Aggiorna il sistema
sudo apt update

# 3. Installa Redis Server
sudo apt install redis-server -y

# 4. Configura Redis per avvio automatico
sudo systemctl enable redis-server
sudo systemctl start redis-server

# 5. Verifica che Redis sia attivo
sudo systemctl status redis-server

# 6. Installa estensione PHP Redis
sudo apt install php-redis -y

# 7. Riavvia PHP-FPM (versione dipende dal tuo PHP)
sudo systemctl restart php8.1-fpm  # o php8.0-fpm, php7.4-fpm

# 8. Verifica installazione
php -m | grep redis
```

### Configurazione Redis sicura (VPS)

Edita il file di configurazione Redis:
```bash
sudo nano /etc/redis/redis.conf
```

Trova e modifica queste righe:
```conf
# Bind solo a localhost per sicurezza
bind 127.0.0.1

# Imposta una password (opzionale ma consigliato)
requirepass TuaPasswordSicuraQui123!

# Limita memoria usata (es: 256MB)
maxmemory 256mb
maxmemory-policy allkeys-lru
```

Riavvia Redis:
```bash
sudo systemctl restart redis-server
```

**Usa la password nel plugin:**
- **Password**: `TuaPasswordSicuraQui123!`

---

## 🧪 Test Finale

Dopo la configurazione, verifica che tutto funzioni:

1. **Nel plugin**: Salva le impostazioni Object Cache
2. **Controlla lo stato**: Dovresti vedere "Connesso a REDIS con successo!"
3. **Test performance**:
   ```php
   <?php
   // Aggiungi temporaneamente in un file test
   wp_cache_set('test_key', 'test_value', '', 3600);
   $value = wp_cache_get('test_key');
   echo $value === 'test_value' ? '✅ Cache funzionante!' : '❌ Cache non funzionante';
   ?>
   ```

---

## 📞 Supporto Ionos

- **Telefono**: 02 30377XXX (verifica sul sito Ionos Italia)
- **Chat**: Disponibile nel pannello di controllo Ionos
- **Email**: Tramite ticket nel Centro Assistenza

---

## ⚡ Riepilogo Veloce

**Se hai Hosting Condiviso Ionos:**
→ Contatta il supporto per richiedere Redis/Memcached
→ Se non disponibile, usa le altre ottimizzazioni del plugin

**Se hai VPS/Cloud Ionos:**
→ Installa Redis manualmente seguendo la guida sopra
→ Configura e testa la connessione

**Se hai dubbi:**
→ Esegui il file `check-redis.php` e invia l'output al supporto Ionos
→ Loro possono dirti esattamente cosa è disponibile sul tuo piano

---

## 🔒 Sicurezza

⚠️ **IMPORTANTE**: Dopo aver eseguito i controlli:
1. **Elimina** il file `check-redis.php` dal server
2. Non lasciare file di debug pubblicamente accessibili
3. Se usi una password Redis, non condividerla mai

---

**Documento creato da FP Performance Suite per utenti Ionos**
*Ultimo aggiornamento: Ottobre 2025*
