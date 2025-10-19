# Correzione Type Hints per Attachment ID

**Data**: 19 Ottobre 2025  
**Versione**: 1.3.3  
**Tipo**: Bug Fix Critico

## Problema Riscontrato

Errore critico su sito in produzione durante il rendering di template Salient/WPBakery:

```
Uncaught Error: FP\PerfSuite\Services\Assets\SmartAssetDelivery::filterImageQuality(): 
Argument #2 ($attachment_id) must be of type int, string given, 
called in wp-includes/class-wp-hook.php on line 324
```

### Causa Root

WordPress, in particolare quando chiamato da template di terze parti (Salient, WPBakery, etc.), 
può passare gli ID degli attachment come **stringhe** invece che come **interi** attraverso i filtri.

I metodi avevano type hints troppo rigidi (`int $attachment_id`) che causavano errori fatali 
in PHP 8+ quando WordPress passava stringhe.

## Soluzione Implementata

### File Modificati

1. **src/Services/Assets/SmartAssetDelivery.php**
   - Metodo: `filterImageQuality()`
   - Filtro: `wp_get_attachment_image_src`

2. **src/Services/Media/WebPConverter.php**
   - Metodo: `generateWebp()`
   - Filtri: `wp_generate_attachment_metadata`, `wp_update_attachment_metadata`

3. **src/Services/Media/AVIFConverter.php**
   - Metodo: `generateAVIF()`
   - Filtri: `wp_generate_attachment_metadata`, `wp_update_attachment_metadata`

### Correzione Applicata

**Prima:**
```php
public function filterImageQuality($image, int $attachment_id, $size, bool $icon)
{
    // ...
}
```

**Dopo:**
```php
public function filterImageQuality($image, int|string $attachment_id, $size, bool $icon)
{
    // Ensure attachment_id is an integer (WordPress may pass it as string)
    $attachment_id = (int) $attachment_id;
    
    // ...
}
```

### Modifiche Specifiche

1. **Type Hint Flessibile**: Cambiato `int $attachment_id` in `int|string $attachment_id`
2. **Cast Esplicito**: Aggiunto `$attachment_id = (int) $attachment_id;` all'inizio del metodo
3. **Commento Esplicativo**: Documentato il motivo del cast

## Compatibilità

✅ **PHP 8.0+**: Union types (`int|string`) supportati nativamente  
✅ **WordPress Core**: Gestisce correttamente entrambi i tipi  
✅ **Salient Theme**: Risolto problema con template WPBakery  
✅ **WPBakery Page Builder**: Compatibilità completa

## Test Eseguiti

- ✅ Rendering template Salient con video player
- ✅ Upload e generazione metadata immagini
- ✅ Conversione WebP/AVIF su attachment
- ✅ Filtri wp_get_attachment_image_src
- ✅ Nessun errore di linting

## Impatto

- **Criticità**: Alta (errore fatale su produzione)
- **Frequenza**: Media (solo con certi template/plugin)
- **Scope**: Tutte le installazioni con Salient/WPBakery

## Note Tecniche

### Perché WordPress passa stringhe?

Gli ID possono essere passati come stringhe quando:
1. Provengono da attributi HTML/shortcode
2. Sono processati attraverso catene di filtri
3. Vengono da query string o POST data
4. Sono gestiti da page builder (Visual Composer, Elementor, etc.)

### Best Practice

Per tutti i filtri WordPress che ricevono ID:
```php
// ✅ CORRETTO
public function myFilter($data, int|string $id)
{
    $id = (int) $id; // Cast esplicito
    // ...
}

// ❌ ERRATO (troppo rigido)
public function myFilter($data, int $id)
{
    // ...
}
```

## Checklist Pre-Deploy

- [x] Modificati tutti i metodi critici
- [x] Verificato nessun errore di linting
- [x] Testato con Salient/WPBakery
- [x] Documentazione aggiornata
- [ ] Test su ambiente staging
- [ ] Deploy su produzione

## Commit

```bash
git add src/Services/Assets/SmartAssetDelivery.php
git add src/Services/Media/WebPConverter.php
git add src/Services/Media/AVIFConverter.php
git add docs/05-changelog/CORREZIONE_TYPE_HINTS_ATTACHMENT_ID.md
git commit -m "fix: Accept int|string for attachment_id parameters

Fixes fatal error when WordPress passes attachment IDs as strings
through filters, especially in Salient/WPBakery templates.

- SmartAssetDelivery::filterImageQuality()
- WebPConverter::generateWebp()
- AVIFConverter::generateAVIF()

All methods now accept int|string and cast to int explicitly."
```

## Versioning

Questa correzione sarà inclusa nella versione **1.3.3** come patch critica.

