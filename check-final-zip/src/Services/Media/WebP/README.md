# WebP Conversion Modules

Moduli per la conversione delle immagini in formato WebP del plugin FP Performance Suite.

## 📁 Struttura

```
Services/Media/
├── WebPConverter.php            # Orchestratore principale
└── WebP/                        # Moduli specializzati
    ├── WebPImageConverter.php   # Motore conversione immagini
    ├── WebPQueue.php            # Gestione coda conversioni
    ├── WebPBatchProcessor.php   # Processamento batch via cron
    ├── WebPAttachmentProcessor.php  # Processamento attachment WordPress
    └── WebPPathHelper.php       # Utilità path WebP
```

## 🎯 Componenti

### WebPConverter (Orchestratore)

Coordina tutti i moduli di conversione WebP.

**Uso:**
```php
$converter = $container->get(WebPConverter::class);
$converter->register(); // Registra hook WordPress

// Conversione singola (deprecata - usa WebPImageConverter)
$converter->convert($filePath, $settings, $force);

// Conversione bulk
$result = $converter->bulkConvert(100, 0); // limit, offset
```

**Accesso ai sotto-moduli:**
```php
$imageConverter = $converter->getImageConverter();
$queue = $converter->getQueue();
$batchProcessor = $converter->getBatchProcessor();
```

### WebPImageConverter

Motore di conversione immagini che supporta Imagick e GD.

**Uso:**
```php
$converter = new WebPImageConverter();

// Converti immagine
$settings = ['quality' => 82, 'lossy' => true];
$success = $converter->convert($sourceFile, $targetFile, $settings);

// Verifica se file è convertibile
if ($converter->isConvertible($file)) {
    // Procedi con conversione
}

// Estensioni supportate
$extensions = $converter->getSupportedExtensions();
// ['jpg', 'jpeg', 'png']
```

**Caratteristiche:**
- Priorità a **Imagick** (qualità superiore)
- Fallback a **GD** se Imagick non disponibile
- Supporto trasparenza PNG
- Conversione palette a truecolor
- Rilevamento automatico se riconversione necessaria

### WebPQueue

Gestisce la coda per conversioni bulk.

**Uso:**
```php
$queue = new WebPQueue($rateLimiter);

// Inizializza conversione bulk
$result = $queue->initializeBulkConversion(100, 0);
// ['converted' => 0, 'total' => 100, 'queued' => true]

// Verifica stato coda
$state = $queue->getState();
// ['limit' => 100, 'offset' => 0, 'processed' => 25, 'converted' => 23]

// Aggiorna stato
$queue->updateState(['processed' => 30, 'converted' => 28]);

// Ottieni prossimo batch
$ids = $queue->getNextBatch(5, 25);  // size, offset

// Pulisci coda
$queue->clear();
```

**Rate Limiting:**
- Massimo 3 conversioni bulk ogni 30 minuti
- Protezione contro abuso risorse server

### WebPBatchProcessor

Elabora batch di immagini via cron.

**Uso:**
```php
$processor = new WebPBatchProcessor($queue, $attachmentProcessor);

// Processa batch corrente (chiamato da cron)
$settings = ['quality' => 82, 'lossy' => true, 'keep_original' => true];
$processor->processBatch($settings);
```

**Caratteristiche:**
- Chunk size: 5 immagini per esecuzione cron
- Auto-schedule prossimo batch
- Pulizia automatica coda al completamento
- Aggiornamento stato progressivo

### WebPAttachmentProcessor

Processa singoli attachment WordPress con tutte le dimensioni.

**Uso:**
```php
$processor = new WebPAttachmentProcessor($imageConverter, $pathHelper);

$metadata = wp_get_attachment_metadata($attachmentId);
$settings = ['quality' => 82, 'lossy' => true, 'keep_original' => false];

$result = $processor->process($attachmentId, $metadata, $settings);
// ['metadata' => [...], 'converted' => true]

// Aggiorna metadata se modificato
if ($metadata !== $result['metadata']) {
    wp_update_attachment_metadata($attachmentId, $result['metadata']);
}
```

**Funzionalità:**
- Processamento file principale
- Processamento tutte le dimensioni (thumbnail, medium, large, ecc.)
- Aggiornamento metadata WordPress
- Gestione modalità `keep_original`
- Rilevamento cambio impostazioni (force reconversion)

### WebPPathHelper

Utilità per manipolazione path WebP.

**Uso:**
```php
$helper = new WebPPathHelper();

// Ottieni path WebP
$webpPath = $helper->getWebPPath('/path/image.jpg');
// '/path/image.webp'

// Converti estensione
$newPath = $helper->withWebPExtension('uploads/2024/image.jpg');
// 'uploads/2024/image.webp'

// Dimensione file sicura
$size = $helper->safeFilesize('/path/image.webp');
// 45678 or null
```

## 🔌 Integrazione ServiceContainer

Tutti i moduli sono registrati nel ServiceContainer:

```php
// Componenti WebP
$container->set(WebPPathHelper::class, fn() => new WebPPathHelper());
$container->set(WebPImageConverter::class, fn() => new WebPImageConverter());
$container->set(WebPQueue::class, fn($c) => new WebPQueue($c->get(RateLimiter::class)));
$container->set(WebPAttachmentProcessor::class, function($c) {
    return new WebPAttachmentProcessor(
        $c->get(WebPImageConverter::class),
        $c->get(WebPPathHelper::class)
    );
});
$container->set(WebPBatchProcessor::class, function($c) {
    return new WebPBatchProcessor(
        $c->get(WebPQueue::class),
        $c->get(WebPAttachmentProcessor::class)
    );
});

// Orchestratore con dependency injection
$container->set(WebPConverter::class, function($c) {
    return new WebPConverter(
        $c->get(Fs::class),
        $c->get(RateLimiter::class),
        $c->get(WebPImageConverter::class),
        $c->get(WebPQueue::class),
        $c->get(WebPAttachmentProcessor::class),
        $c->get(WebPBatchProcessor::class),
        $c->get(WebPPathHelper::class)
    );
});
```

## 🔄 Flusso di Lavoro

### Conversione Automatica (Upload)

```
1. Utente carica immagine
   ↓
2. WordPress chiama wp_generate_attachment_metadata
   ↓
3. WebPConverter::generateWebp()
   ↓
4. WebPAttachmentProcessor::process()
   ├── Converte file principale (WebPImageConverter)
   ├── Converte tutte le dimensioni
   └── Aggiorna metadata
```

### Conversione Bulk

```
1. Utente avvia bulk conversion
   ↓
2. WebPQueue::initializeBulkConversion()
   ├── Conta immagini totali
   ├── Inizializza stato coda
   └── Schedule primo batch cron
   ↓
3. Cron esegue WebPBatchProcessor::processBatch()
   ├── Legge stato coda
   ├── Ottiene prossimi 5 attachment
   ├── Processa con WebPAttachmentProcessor
   ├── Aggiorna stato
   └── Schedule prossimo batch
   ↓
4. Ripete step 3 fino a completamento
```

## 🧪 Testing

Ogni modulo può essere testato indipendentemente:

```php
class WebPImageConverterTest extends TestCase
{
    public function testConvertJpegToWebP()
    {
        $converter = new WebPImageConverter();
        $settings = ['quality' => 82, 'lossy' => true];
        
        $result = $converter->convert(
            '/path/test.jpg',
            '/path/test.webp',
            $settings
        );
        
        $this->assertTrue($result);
        $this->assertFileExists('/path/test.webp');
    }
}
```

## 📊 Performance

### WebPImageConverter
- **Imagick:** ~200-500ms per immagine 2MB
- **GD:** ~300-700ms per immagine 2MB
- **Risparmio dimensione:** 25-35% in media

### Batch Processing
- **Chunk size:** 5 immagini/cron
- **Overhead:** Minimo (stato salvato in options)
- **Scalabilità:** Ottima per migliaia di immagini

## ⚠️ Limitazioni

### Formati Supportati
- ✅ JPEG (.jpg, .jpeg)
- ✅ PNG (.png)
- ❌ GIF (animazioni non supportate)
- ❌ SVG (vettoriale, non applicabile)
- ❌ BMP, TIFF (raramente usati sul web)

### Requisiti Server
- **Imagick** o **GD** con supporto WebP
- PHP >= 7.4
- Memoria: Dipende da dimensione immagini (raccomandato 256MB+)

### Modalità keep_original=false
- Sostituisce file originali con WebP
- **Non reversibile** senza backup
- Potrebbe causare problemi con temi/plugin che si aspettano JPG/PNG

## 🔧 Estensione

### Creare Converter Personalizzato

```php
use FP\PerfSuite\Services\Media\WebP\WebPImageConverter;

class CustomWebPConverter extends WebPImageConverter
{
    protected function convertWithImagick(
        string $sourceFile,
        string $targetFile,
        array $settings
    ): bool {
        // Implementazione personalizzata
        // Es: applicare watermark prima della conversione
        
        return parent::convertWithImagick($sourceFile, $targetFile, $settings);
    }
}
```

### Hook Disponibili

```php
// Dopo conversione singola
add_action('fp_ps_webp_converted', function($file) {
    // $file = path del file originale convertito
    // Esempio: pulisci cache CDN
});

// Inizio bulk conversion
add_action('fp_ps_webp_bulk_start', function($total) {
    // $total = numero totale di immagini
    // Esempio: invia notifica admin
});
```

## 📚 Riferimenti

- [WebP Format](https://developers.google.com/speed/webp)
- [Imagick PHP Extension](https://www.php.net/manual/en/book.imagick.php)
- [GD PHP Extension](https://www.php.net/manual/en/book.image.php)
- [WordPress Cron](https://developer.wordpress.org/plugins/cron/)

## 📝 Licenza

Stesso del plugin principale FP Performance Suite.

---

**Versione:** 1.1.0  
**Ultima modifica:** 2025-10-07  
**Autore:** Francesco Passeri