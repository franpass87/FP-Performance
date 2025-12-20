# Servizi Migrati - Status Tracker

Questo documento traccia lo stato della migrazione dei servizi alla nuova architettura.

**Ultimo aggiornamento**: 2025-11-06

---

## ✅ Servizi Migrati a OptionsRepository

**Totale migrati**: 74 servizi

### 1. PatternStorage ✅
- **File**: `src/Services/ML/PatternLearner/PatternStorage.php`
- **Status**: ✅ Completato
- **Changes**: 
  - Aggiunto `OptionsRepositoryInterface` al costruttore (opzionale)
  - Metodi `save()` e `get()` usano OptionsRepository con fallback
- **Service Provider**: `MLServiceProvider.php` aggiornato

### 2. PatternLearner ✅
- **File**: `src/Services/ML/PatternLearner.php`
- **Status**: ✅ Completato
- **Changes**:
  - Aggiunto `OptionsRepositoryInterface` al costruttore (opzionale)
  - Metodo `getSettings()` usa OptionsRepository con fallback
  - Passa OptionsRepository a PatternStorage
- **Service Provider**: `MLServiceProvider.php` aggiornato

### 3. CDNProviderDetector ✅
- **File**: `src/Services/Intelligence/CDNExclusionSync/CDNProviderDetector.php`
- **Status**: ✅ Completato
- **Changes**:
  - Aggiunto `OptionsRepositoryInterface` al costruttore (opzionale)
  - Metodo helper privato `getOption()` con fallback
  - Tutti i metodi `has*API()` usano OptionsRepository
- **Service Provider**: `IntelligenceServiceProvider.php` aggiornato

### 4. CDNReportGenerator ✅
- **File**: `src/Services/Intelligence/CDNExclusionSync/CDNReportGenerator.php`
- **Status**: ✅ Completato
- **Changes**:
  - Aggiunto `OptionsRepositoryInterface` al costruttore (opzionale)
  - Metodo helper privato `getOption()` con fallback
  - Metodo `generate()` usa OptionsRepository per `last_sync`
- **Service Provider**: `IntelligenceServiceProvider.php` aggiornato

### 5. CDNExclusionSync ✅
- **File**: `src/Services/Intelligence/CDNExclusionSync.php`
- **Status**: ✅ Completato
- **Changes**:
  - Aggiunto `OptionsRepositoryInterface` al costruttore (opzionale)
  - Passa OptionsRepository a CDNProviderDetector e CDNReportGenerator
- **Service Provider**: `IntelligenceServiceProvider.php` aggiornato

### 6. SettingsManager ✅
- **File**: `src/Services/Assets/Optimizer/SettingsManager.php`
- **Status**: ✅ Completato
- **Changes**:
  - Aggiunto `OptionsRepositoryInterface` al costruttore (opzionale)
  - Metodi helper `getOption()` e `setOption()` con fallback
  - Metodi `get()` e `update()` usano OptionsRepository
- **Service Provider**: Da verificare se registrato

### 7. ThirdPartyScriptManager ✅
- **File**: `src/Services/Assets/ThirdPartyScriptManager.php`
- **Status**: ✅ Completato
- **Changes**:
  - Aggiunto `OptionsRepositoryInterface` al costruttore (opzionale)
  - Metodi helper `getOption()` e `setOption()` con fallback
  - Metodi `settings()`, `update()` e `getCustomScripts()` usano OptionsRepository
  - Option keys: `fp_ps_third_party_scripts`, `fp_ps_custom_scripts`
- **Service Provider**: `AssetServiceProvider.php` aggiornato

### 8. SiteAnalyzer ✅
- **File**: `src/Services/AI/Analyzer/SiteAnalyzer.php`
- **Status**: ✅ Completato
- **Changes**:
  - Aggiunto `OptionsRepositoryInterface` al costruttore (opzionale)
  - Metodo helper `getOption()` con fallback
  - Metodo `estimateTraffic()` usa OptionsRepository per `fp_ps_traffic_stats`
- **Service Provider**: Da verificare (passato tramite Analyzer)

### 9. Analyzer ✅
- **File**: `src/Services/AI/Analyzer.php`
- **Status**: ✅ Completato
- **Changes**:
  - Già aveva `OptionsRepositoryInterface` nel costruttore
  - Aggiunto metodo helper `getOption()` con fallback
  - Metodo `estimateTraffic()` usa OptionsRepository
  - Nota: `get_option('active_plugins')` rimane diretto (opzione WordPress core)
- **Service Provider**: Da verificare

### 10. ResponsiveImageOptimizer ✅
- **File**: `src/Services/Assets/ResponsiveImageOptimizer.php`
- **Status**: ✅ Completato
- **Changes**:
  - Aggiunto `OptionsRepositoryInterface` al costruttore (opzionale)
  - Metodo helper `getOption()` con fallback
  - Metodo `getSettings()` usa OptionsRepository per plugin settings
  - Nota: Dimensioni immagini WordPress (`thumbnail_size_w`, etc.) rimangono dirette (opzioni core)
- **Service Provider**: `AssetServiceProvider.php` aggiornato

### 11. PageCache ✅
- **File**: `src/Services/Cache/PageCache.php`
- **Status**: ✅ Completato
- **Changes**:
  - Aggiunto `OptionsRepositoryInterface` al costruttore (opzionale)
  - Metodi helper `getOption()` e `setOption()` con fallback
  - Metodi `isEnabled()`, `settings()`, e `update()` usano OptionsRepository
  - Option key: `fp_ps_page_cache_settings`
- **Service Provider**: `CacheServiceProvider.php` aggiornato

### 12. ObjectCacheManager ✅
- **File**: `src/Services/Cache/ObjectCacheManager.php`
- **Status**: ✅ Completato
- **Changes**:
  - Aggiunto `OptionsRepositoryInterface` al costruttore (opzionale)
  - Metodi helper `getOption()` e `setOption()` con fallback
  - Metodi `getSettings()` e `updateSettings()` usano OptionsRepository
  - Option key: `fp_ps_object_cache`
- **Service Provider**: `CacheServiceProvider.php` aggiornato

### 13. QueryCacheManager ✅
- **File**: `src/Services/DB/QueryCacheManager.php`
- **Status**: ✅ Completato
- **Changes**:
  - Aggiunto `OptionsRepositoryInterface` al costruttore (opzionale)
  - Metodi helper `getOption()` e `setOption()` con fallback
  - Metodi `getSettings()`, `updateSettings()`, `getStats()`, `resetStats()` usano OptionsRepository
  - Metodi privati `incrementHits()` e `incrementMisses()` migrati
  - Option keys: `fp_ps_query_cache_settings`, `fp_ps_query_cache_stats`
- **Service Provider**: `DatabaseServiceProvider.php` aggiornato

### 14. DatabaseQueryMonitor ✅
- **File**: `src/Services/DB/DatabaseQueryMonitor.php`
- **Status**: ✅ Completato
- **Changes**:
  - Aggiunto `OptionsRepositoryInterface` al costruttore (opzionale)
  - Metodi helper `getOption()` e `setOption()` con fallback
  - Metodi `getSettings()`, `updateSettings()`, `getLastAnalysis()` usano OptionsRepository
  - Metodi `logStatistics()` e `saveStatistics()` migrati
  - Option keys: `fp_ps_query_monitor`, `fp_ps_query_monitor_last_analysis`, `fp_ps_query_monitor_stats`
- **Service Provider**: `DatabaseServiceProvider.php` aggiornato

### 15. PerformanceMonitor ✅
- **File**: `src/Services/Monitoring/PerformanceMonitor.php`
- **Status**: ✅ Completato
- **Changes**:
  - Aggiunto `OptionsRepositoryInterface` al costruttore (opzionale)
  - Metodo `instance()` aggiornato per accettare OptionsRepository
  - Metodi helper `getOption()` e `setOption()` con fallback
  - Metodi `isEnabled()`, `settings()`, e `update()` usano OptionsRepository
  - Option key: `fp_ps_monitoring_settings`
- **Service Provider**: `MonitoringServiceProvider.php` aggiornato
- **Nota**: Singleton pattern mantenuto con supporto OptionsRepository

### 16. SystemMonitor ✅
- **File**: `src/Services/Monitoring/SystemMonitor.php`
- **Status**: ✅ Completato
- **Changes**:
  - Aggiunto `OptionsRepositoryInterface` al costruttore (opzionale)
  - Metodo `instance()` aggiornato per accettare OptionsRepository
  - Metodi helper `getOption()` e `setOption()` con fallback
  - Metodi `storeMetrics()`, `getStats()`, e `cleanup()` usano OptionsRepository
  - Option key: `fp_ps_system_monitor_data`
- **Service Provider**: `MonitoringServiceProvider.php` aggiornato
- **Nota**: Singleton pattern mantenuto con supporto OptionsRepository

### 17. CoreWebVitalsMonitor ✅
- **File**: `src/Services/Monitoring/CoreWebVitalsMonitor.php`
- **Status**: ✅ Completato
- **Changes**:
  - Aggiunto `OptionsRepositoryInterface` al costruttore (opzionale)
  - Metodi helper `getOption()` e `setOption()` con fallback
  - Metodi `getCoreWebVitals()`, `saveVitals()`, e `update()` usano OptionsRepository
  - Option keys: `fp_core_web_vitals`, `fp_ps_cwv_settings`
- **Service Provider**: `MonitoringServiceProvider.php` aggiornato

### 18. PerformanceAnalyzer ✅
- **File**: `src/Services/Monitoring/PerformanceAnalyzer.php`
- **Status**: ✅ Completato
- **Changes**:
  - Aggiunto `OptionsRepositoryInterface` al costruttore (opzionale)
  - Metodo helper `getOption()` con fallback
  - Metodo `analyze()` usa OptionsRepository per critical CSS e settings
  - Option keys: `fp_ps_critical_css`, `fp_ps_settings`
- **Service Provider**: `MonitoringServiceProvider.php` aggiornato

### 19. DatabaseReportService ✅
- **File**: `src/Services/DB/DatabaseReportService.php`
- **Status**: ✅ Completato
- **Changes**:
  - Aggiunto `OptionsRepositoryInterface` al costruttore (opzionale)
  - Metodi helper `getOption()` e `setOption()` con fallback
  - Metodi `createSnapshot()`, `getSnapshots()`, `calculateROI()`, `generateAutomaticReport()` usano OptionsRepository
  - Option keys: `fp_ps_db_snapshots`, `fp_ps_db_report_history`, `DatabaseOptimizer::class . '_history'`
- **Service Provider**: `DatabaseServiceProvider.php` aggiornato
- **Nota**: `get_option('admin_email')` rimane diretto (opzione WordPress core)

### 20. Cleaner ✅
- **File**: `src/Services/DB/Cleaner.php`
- **Status**: ✅ Completato
- **Changes**:
  - Aggiunto `OptionsRepositoryInterface` al costruttore (opzionale)
  - Metodi helper `getOption()` e `setOption()` con fallback
  - Metodo privato `getDbOption()` migrato a usare OptionsRepository
  - Metodi `update()`, `cleanup()` usano OptionsRepository
  - Option keys: `fp_ps_db_last_run`, `fp_ps_db_cleaner_settings`, `fp_ps_db`
- **Service Provider**: `DatabaseServiceProvider.php` aggiornato

### 21. BrowserCache ✅
- **File**: `src/Services/Cache/BrowserCache.php`
- **Status**: ✅ Completato
- **Changes**:
  - Aggiunto `OptionsRepositoryInterface` al costruttore (opzionale)
  - Metodi helper `getOption()` e `setOption()` con fallback
  - Metodi `getSettings()` e `updateSettings()` usano OptionsRepository
  - Option key: `fp_ps_browser_cache`
- **Service Provider**: `CacheServiceProvider.php` aggiornato

### 22. Headers ✅
- **File**: `src/Services/Cache/Headers.php`
- **Status**: ✅ Completato
- **Changes**:
  - Aggiunto `OptionsRepositoryInterface` al costruttore (opzionale)
  - Metodi helper `getOption()` e `setOption()` con fallback
  - Metodi `settings()`, `status()`, e `update()` usano OptionsRepository
  - Option key: `fp_ps_cache_headers_settings`
- **Service Provider**: `CacheServiceProvider.php` aggiornato

### 23. ImageOptimizer ✅
- **File**: `src/Services/Assets/ImageOptimizer.php`
- **Status**: ✅ Completato
- **Changes**:
  - Aggiunto `OptionsRepositoryInterface` al costruttore (opzionale)
  - Metodo helper `getOption()` con fallback
  - Metodo `getSettings()` usa OptionsRepository
  - Option key: `fp_ps_image_optimizer`
- **Service Provider**: `AssetServiceProvider.php` aggiornato

### 24. LazyLoadManager ✅
- **File**: `src/Services/Assets/LazyLoadManager.php`
- **Status**: ✅ Completato
- **Changes**:
  - Aggiunto `OptionsRepositoryInterface` al costruttore (opzionale)
  - Metodo helper `getOption()` con fallback
  - Metodo `getSettings()` usa OptionsRepository
  - Option key: `fp_ps_lazy_load`
- **Service Provider**: `AssetServiceProvider.php` aggiornato

### 25. DelayedJavaScriptExecutor ✅
- **File**: `src/Services/Assets/DelayedJavaScriptExecutor.php`
- **Status**: ✅ Completato
- **Changes**:
  - Aggiunto `OptionsRepositoryInterface` al costruttore (opzionale)
  - Metodi helper `getOption()` e `setOption()` con fallback
  - Metodi `getSettings()` e `updateSettings()` usano OptionsRepository
  - Option key: `fp_ps_delay_js`
- **Service Provider**: `AssetServiceProvider.php` aggiornato

### 26. EmbedFacades ✅
- **File**: `src/Services/Assets/EmbedFacades.php`
- **Status**: ✅ Completato
- **Changes**:
  - Aggiunto `OptionsRepositoryInterface` al costruttore (opzionale)
  - Metodi helper `getOption()` e `setOption()` con fallback
  - Metodi `getSettings()` e `updateSettings()` usano OptionsRepository
  - Option key: `fp_ps_embed_facades`
- **Service Provider**: `AssetServiceProvider.php` aggiornato

### 27. InstantPageLoader ✅
- **File**: `src/Services/Assets/InstantPageLoader.php`
- **Status**: ✅ Completato
- **Changes**:
  - Aggiunto `OptionsRepositoryInterface` al costruttore (opzionale)
  - Metodi helper `getOption()` e `setOption()` con fallback
  - Metodi `getSettings()` e `updateSettings()` usano OptionsRepository
  - Option key: `fp_ps_instant_page`
- **Service Provider**: `AssetServiceProvider.php` aggiornato

### 28. SmartAssetDelivery ✅
- **File**: `src/Services/Assets/SmartAssetDelivery.php`
- **Status**: ✅ Completato
- **Changes**:
  - Aggiunto `OptionsRepositoryInterface` al costruttore (opzionale)
  - Metodi helper `getOption()` e `setOption()` con fallback
  - Metodi `getSettings()` e `updateSettings()` usano OptionsRepository
  - Option key: `fp_ps_smart_delivery`
- **Service Provider**: `AssetServiceProvider.php` aggiornato

### 29. CriticalCssAutomation ✅
- **File**: `src/Services/Assets/CriticalCssAutomation.php`
- **Status**: ✅ Completato
- **Changes**:
  - Aggiunto `OptionsRepositoryInterface` al costruttore (opzionale)
  - Metodi helper `getOption()`, `setOption()`, e `deleteOption()` con fallback
  - Metodi `getSettings()`, `updateSettings()`, `regenerateAll()`, `inlineCriticalCss()`, `getCriticalCss()`, `setCriticalCss()`, `clearCriticalCss()`, e `getStats()` usano OptionsRepository
  - Option keys: `fp_ps_critical_css_automation`, `fp_ps_critical_css`
- **Service Provider**: `AssetServiceProvider.php` aggiornato

### 30. CriticalPathOptimizer ✅
- **File**: `src/Services/Assets/CriticalPathOptimizer.php`
- **Status**: ✅ Completato
- **Changes**:
  - Aggiunto `OptionsRepositoryInterface` al costruttore (opzionale)
  - Metodi helper `getOption()` e `setOption()` con fallback
  - Metodi `getSettings()` e `updateSettings()` usano OptionsRepository
  - Option key: `fp_ps_critical_path_optimization`
- **Service Provider**: `AssetServiceProvider.php` aggiornato

### 31. RenderBlockingOptimizer ✅
- **File**: `src/Services/Assets/RenderBlockingOptimizer.php`
- **Status**: ✅ Completato
- **Changes**:
  - Aggiunto `OptionsRepositoryInterface` al costruttore (opzionale)
  - Metodi helper `getOption()` e `setOption()` con fallback
  - Metodi `getSettings()` e `updateSettings()` usano OptionsRepository
  - Option key: `fp_ps_render_blocking_optimization`
- **Service Provider**: `AssetServiceProvider.php` aggiornato

### 32. ExternalResourceCacheManager ✅
- **File**: `src/Services/Assets/ExternalResourceCacheManager.php`
- **Status**: ✅ Completato
- **Changes**:
  - Aggiunto `OptionsRepositoryInterface` al costruttore (opzionale)
  - Metodi helper `getOption()` e `setOption()` con fallback
  - Metodi `getSettings()` e `updateSettings()` usano OptionsRepository
  - Option key: `fp_ps_external_cache`
- **Service Provider**: `AssetServiceProvider.php` aggiornato

### 33. jQueryOptimizer ✅
- **File**: `src/Services/Assets/jQueryOptimizer.php`
- **Status**: ✅ Completato
- **Changes**:
  - Aggiunto `OptionsRepositoryInterface` al costruttore (opzionale)
  - Metodi helper `getOption()` e `setOption()` con fallback
  - Metodi `settings()` e `update()` usano OptionsRepository
  - Option key: `fp_ps_jquery_optimization`
- **Service Provider**: Non registrato (da registrare se necessario)

### 34. UnusedCSSOptimizer ✅
- **File**: `src/Services/Assets/UnusedCSSOptimizer.php`
- **Status**: ✅ Completato
- **Changes**:
  - Aggiunto `OptionsRepositoryInterface` al costruttore (opzionale)
  - Metodi helper `getOption()` e `setOption()` con fallback
  - Metodi `getSettings()` e `updateSettings()` usano OptionsRepository
  - Option key: `fp_ps_unused_css_optimization`
- **Service Provider**: `AssetServiceProvider.php` aggiornato

### Note: CriticalPathOptimizer ✅ (Correzione)
- **File**: `src/Services/Assets/CriticalPathOptimizer.php`
- **Status**: ✅ Corretto
- **Changes**:
  - Corretta chiamata diretta a `get_option('fp_ps_assets')` nel metodo `isEnabled()` per usare `getOption()` helper
  - Ora tutte le chiamate a opzioni usano OptionsRepository

### 35. CSSOptimizer ✅
- **File**: `src/Services/Assets/CSSOptimizer.php`
- **Status**: ✅ Completato
- **Changes**:
  - Aggiunto `OptionsRepositoryInterface` al costruttore (opzionale)
  - Metodi helper `getOption()` e `setOption()` con fallback
  - Metodi `getSettings()` e `updateSettings()` usano OptionsRepository
  - Option key: `fp_ps_css_optimization`
- **Service Provider**: `AssetServiceProvider.php` aggiornato (aggiunto)

### 36. UnusedJavaScriptOptimizer ✅
- **File**: `src/Services/Assets/UnusedJavaScriptOptimizer.php`
- **Status**: ✅ Completato
- **Changes**:
  - Aggiunto `OptionsRepositoryInterface` al costruttore (opzionale, dopo `$aggressive_mode`)
  - Metodi helper `getOption()` e `setOption()` con fallback
  - Metodi `settings()` e `updateSettings()` usano OptionsRepository
  - Option key: `fp_ps_unused_js_optimizer`
- **Service Provider**: `AssetServiceProvider.php` aggiornato

### 37. MLPredictor ✅
- **File**: `src/Services/ML/MLPredictor.php`
- **Status**: ✅ Completato
- **Changes**:
  - Aggiunto `OptionsRepositoryInterface` al costruttore (opzionale, dopo `AnomalyDetector`)
  - Metodi helper `getOption()` e `setOption()` con fallback
  - Metodi `getSettings()`, `updateSettings()`, e `getLastAnalysisTime()` usano OptionsRepository
  - Option keys: `fp_ps_ml_predictor`, `fp_ps_ml_last_analysis`
  - Passa OptionsRepository a DataStorage e DataCollector
- **Service Provider**: `MLServiceProvider.php` aggiornato

### 38. DataCollector ✅
- **File**: `src/Services/ML/MLPredictor/DataCollector.php`
- **Status**: ✅ Completato
- **Changes**:
  - Aggiunto `OptionsRepositoryInterface` al costruttore (opzionale)
  - Metodo helper `getOption()` con fallback
  - Metodi `getCacheHits()`, `getCacheMisses()`, e `getErrorCount()` usano OptionsRepository
  - Option keys: `fp_ps_cache_hits`, `fp_ps_cache_misses`, `fp_ps_error_count`
- **Note**: Istanzato da MLPredictor che passa OptionsRepository

### 39. DataStorage ✅
- **File**: `src/Services/ML/MLPredictor/DataStorage.php`
- **Status**: ✅ Completato
- **Changes**:
  - Aggiunto `OptionsRepositoryInterface` al costruttore (opzionale)
  - Metodi helper `getOption()` e `setOption()` con fallback
  - Metodi `getStoredData()`, `storePerformanceData()`, `getLearnedPatterns()`, `updateLearnedPatterns()`, `storePredictions()`, `getStoredPredictions()`, e `cleanupOldData()` usano OptionsRepository
  - Option keys: `fp_ps_ml_data`, `fp_ps_ml_patterns`, `fp_ps_ml_predictions`
- **Note**: Istanzato da MLPredictor che passa OptionsRepository

### 40. AutoTuner ✅
- **File**: `src/Services/ML/AutoTuner.php`
- **Status**: ✅ Completato
- **Changes**:
  - Aggiunto `OptionsRepositoryInterface` al costruttore (opzionale, dopo `PatternLearner`)
  - Metodi helper `getOption()` e `setOption()` con fallback
  - Tutti i metodi che usano `get_option()` e `update_option()` migrati (molti metodi privati)
  - Option keys: `fp_ps_auto_tuner`, `fp_ps_cache`, `fp_ps_db`, `fp_ps_general`, `fp_ps_assets`, `fp_ps_mobile`, `fp_ps_tuning_history`, `fp_ps_ml_data`
- **Service Provider**: `MLServiceProvider.php` aggiornato
- **Note**: Servizio complesso che modifica opzioni di altri servizi per auto-tuning

### 41. AnomalyDetector ✅
- **File**: `src/Services/ML/AnomalyDetector.php`
- **Status**: ✅ Completato
- **Changes**:
  - Aggiunto `OptionsRepositoryInterface` al costruttore (opzionale)
  - Metodo helper `getOption()` con fallback
  - Metodo `getRecentHistoricalData()` usa OptionsRepository
  - Option key: `fp_ps_ml_data`
- **Service Provider**: `MLServiceProvider.php` aggiornato

### 42. SmartExclusionDetector ✅
- **File**: `src/Services/Intelligence/SmartExclusionDetector.php`
- **Status**: ✅ Completato
- **Changes**:
  - Aggiunto `OptionsRepositoryInterface` al costruttore (opzionale)
  - Metodi helper `getOption()` e `setOption()` con fallback
  - Metodi `addExclusion()`, `getAppliedExclusions_OLD()`, e `removeExclusion_OLD()` usano OptionsRepository
  - Option keys: `fp_ps_tracked_exclusions`, `fp_ps_page_cache`
- **Service Provider**: `IntelligenceServiceProvider.php` aggiornato
- **Note**: Metodi _OLD sono per backward compatibility, i metodi attivi sono gestiti da ExclusionManager

### 43. CacheAutoConfigurator ✅
- **File**: `src/Services/Intelligence/CacheAutoConfigurator.php`
- **Status**: ✅ Completato
- **Changes**:
  - Aggiunto `OptionsRepositoryInterface` al costruttore (opzionale)
  - Metodi helper `getOption()` e `setOption()` con fallback
  - Metodi `applySmartExclusions()`, `optimizeCacheSettings()`, `applyAdvancedCacheRules()`, e `validateCacheConfiguration()` usano OptionsRepository
  - Option key: `fp_ps_page_cache`
- **Service Provider**: `IntelligenceServiceProvider.php` aggiornato
- **Note**: Servizio complesso che auto-configura regole di cache basate su performance

### 44. PageCacheAutoConfigurator ✅
- **File**: `src/Services/Intelligence/PageCacheAutoConfigurator.php`
- **Status**: ✅ Completato
- **Changes**:
  - Aggiunto `OptionsRepositoryInterface` al costruttore (opzionale, dopo `SmartExclusionDetector`)
  - Metodi helper `getOption()` e `setOption()` con fallback
  - Metodo `apply()` usa OptionsRepository
  - Option key: `fp_ps_cache_exclusions`
- **Service Provider**: `IntelligenceServiceProvider.php` aggiornato

### 45. AssetOptimizationIntegrator ✅
- **File**: `src/Services/Intelligence/AssetOptimizationIntegrator.php`
- **Status**: ✅ Completato
- **Changes**:
  - Aggiunto `OptionsRepositoryInterface` al costruttore (opzionale)
  - Metodi helper `getOption()` e `setOption()` con fallback
  - Molti metodi privati migrati: `applyJavaScriptExclusions()`, `applyCssExclusions()`, `optimizeAssetConfiguration()`, `analyzeAssetExclusionEffectiveness()`, `getOptimizationStatus()`, `calculatePerformanceImpact()`, e `generateAssetOptimizationReport()`
  - Option key: `fp_ps_assets`
- **Service Provider**: `IntelligenceServiceProvider.php` aggiornato
- **Note**: Servizio complesso che integra Smart Exclusion con Asset Optimization

### 46. CriticalAssetsDetector ✅
- **File**: `src/Services/Intelligence/CriticalAssetsDetector.php`
- **Status**: ✅ Completato
- **Changes**:
  - Aggiunto `OptionsRepositoryInterface` al costruttore (opzionale)
  - Metodi helper `getOption()` e `setOption()` con fallback
  - Metodo `applyPreloadRecommendations()` usa OptionsRepository (fallback deprecato)
  - Option key: `fp_ps_assets`
- **Service Provider**: `IntelligenceServiceProvider.php` aggiornato (aggiunto)

### 47. CDNSyncHandlers ✅
- **File**: `src/Services/Intelligence/CDNExclusionSync/CDNSyncHandlers.php`
- **Status**: ✅ Completato
- **Changes**:
  - Aggiunto `OptionsRepositoryInterface` al costruttore (opzionale, dopo `CDNProviderDetector`)
  - Metodo helper `setOption()` con fallback
  - Metodo `syncWithGenericCDN()` usa OptionsRepository
  - Option key: `fp_ps_cdn_exclusion_rules`
- **Note**: Istanzato da CDNExclusionSync che passa OptionsRepository

### 48. SchedulerManager ✅
- **File**: `src/Services/DB/Cleaner/SchedulerManager.php`
- **Status**: ✅ Completato
- **Changes**:
  - Aggiunto `OptionsRepositoryInterface` al costruttore (opzionale)
  - Metodo helper `getOption()` con fallback
  - Metodi `loadSettings()` e `getScheduledScope()` usano OptionsRepository
  - Option keys: `fp_ps_db`, `fp_ps_db_cleaner_settings`
- **Note**: Istanzato da Cleaner che passa OptionsRepository

### 49. RecommendationApplicator ✅
- **File**: `src/Services/Monitoring/RecommendationApplicator.php`
- **Status**: ✅ Completato
- **Changes**:
  - Aggiunto `OptionsRepositoryInterface` al costruttore (opzionale, dopo `Cleaner`)
  - Metodo helper `setOption()` con fallback
  - Tutti i metodi privati che applicano raccomandazioni usano OptionsRepository: `enablePageCache()`, `enableBrowserCache()`, `enableMinifyHtml()`, `enableDeferJs()`, `removeEmojis()`, `optimizeHeartbeat()`
  - Option keys: `fp_ps_page_cache`, `fp_ps_cache_headers`, `fp_ps_minify_html`, `fp_ps_defer_js`, `fp_ps_remove_emojis`, `fp_ps_remove_embeds`, `fp_ps_heartbeat_admin`, `fp_ps_heartbeat_editor`, `fp_ps_heartbeat_frontend`
- **Service Provider**: `MonitoringServiceProvider.php` aggiornato

### 50. Scorer ✅
- **File**: `src/Services/Score/Scorer.php`
- **Status**: ✅ Completato
- **Changes**:
  - Aggiunto `OptionsRepositoryInterface` al costruttore (opzionale, dopo `CompressionManager`)
  - Metodo helper `getOption()` con fallback
  - Metodo `criticalCssScore()` usa OptionsRepository
  - Option keys: `fp_ps_critical_css`, `fp_ps_settings`
- **Service Provider**: `MonitoringServiceProvider.php` aggiornato

### 51. CdnManager ✅
- **File**: `src/Services/CDN/CdnManager.php`
- **Status**: ✅ Completato
- **Changes**:
  - Aggiunto `OptionsRepositoryInterface` al costruttore (opzionale, dopo `$zone_id`)
  - Metodi helper `getOption()` e `setOption()` con fallback
  - Metodo `update()` usa OptionsRepository
  - Option key: `fp_ps_cdn_settings`
- **Service Provider**: `AssetServiceProvider.php` aggiornato

### 52. MobileOptimizer ✅
- **File**: `src/Services/Mobile/MobileOptimizer.php`
- **Status**: ✅ Completato
- **Changes**:
  - Aggiunto `OptionsRepositoryInterface` al costruttore (opzionale, dopo `$responsive_images`)
  - Metodo helper `getOption()` con fallback
  - Metodo `getValidatedSettings()` usa OptionsRepository
  - Option key: `fp_ps_mobile_optimizer`
- **Service Provider**: `MonitoringServiceProvider.php` aggiornato

### 53. MobileCacheManager ✅
- **File**: `src/Services/Mobile/MobileCacheManager.php`
- **Status**: ✅ Completato
- **Changes**:
  - Aggiunto `OptionsRepositoryInterface` al costruttore (opzionale)
  - Metodo helper `getOption()` con fallback
  - Metodi `getCacheStats()` e `settings()` usano OptionsRepository
  - Option keys: `fp_ps_mobile_cache`, `fp_ps_mobile_cache_last_cleared`
- **Service Provider**: `MonitoringServiceProvider.php` aggiornato

### 54. ScheduledReports ✅
- **File**: `src/Services/Reports/ScheduledReports.php`
- **Status**: ✅ Completato
- **Changes**:
  - Aggiunto `OptionsRepositoryInterface` al costruttore (opzionale)
  - Metodi helper `getOption()` e `setOption()` con fallback
  - Metodi `settings()`, `update()` e `sendReport()` usano OptionsRepository
  - Option keys: `fp_ps_reports`, `fp_ps_last_report`
  - Nota: `get_option('admin_email')` e `get_option('date_format')` rimangono diretti (opzioni WordPress core)
- **Service Provider**: `MonitoringServiceProvider.php` aggiornato

### 55. BackendOptimizer ✅
- **File**: `src/Services/Admin/BackendOptimizer.php`
- **Status**: ✅ Completato
- **Changes**:
  - Aggiunto `OptionsRepositoryInterface` al costruttore (opzionale, dopo `$admin_bar`)
  - Metodi helper `getOption()` e `setOption()` con fallback
  - Metodi `getSettings()` e `updateSettings()` usano OptionsRepository
  - Option key: `fp_ps_backend_optimizer`
- **Service Provider**: `AdminServiceProvider.php` aggiornato

### 56. FontOptimizer ✅
- **File**: `src/Services/Assets/FontOptimizer.php`
- **Status**: ✅ Completato
- **Changes**:
  - Aggiunto `OptionsRepositoryInterface` al costruttore (opzionale, dopo `$display_swap`)
  - Metodi helper `getOption()` e `setOption()` con fallback
  - Metodi `getSettings()` e `updateSettings()` usano OptionsRepository
  - Option key: `fp_ps_font_optimizer`
- **Service Provider**: `AssetServiceProvider.php` aggiornato

### 57. PredictivePrefetching ✅
- **File**: `src/Services/Assets/PredictivePrefetching.php`
- **Status**: ✅ Completato
- **Changes**:
  - Aggiunto `OptionsRepositoryInterface` al costruttore (opzionale, dopo `$limit`)
  - Metodi helper `getOption()` e `setOption()` con fallback
  - Metodi `getSettings()` e `updateSettings()` usano OptionsRepository
  - Option key: `fp_ps_predictive_prefetch`
- **Service Provider**: `Plugin.php` aggiornato

### 58. JavaScriptTreeShaker ✅
- **File**: `src/Services/Assets/JavaScriptTreeShaker.php`
- **Status**: ✅ Completato
- **Changes**:
  - Aggiunto `OptionsRepositoryInterface` al costruttore (opzionale, dopo `$aggressive_mode`)
  - Metodi helper `getOption()` e `setOption()` con fallback
  - Metodi `settings()`, `getSettings()` e `updateSettings()` usano OptionsRepository
  - Option key: `fp_ps_js_tree_shaker`
- **Service Provider**: `AssetServiceProvider.php` aggiornato

### 59. CodeSplittingManager ✅
- **File**: `src/Services/Assets/CodeSplittingManager.php`
- **Status**: ✅ Completato
- **Changes**:
  - Aggiunto `OptionsRepositoryInterface` al costruttore (opzionale, dopo `$lazy_loading`)
  - Metodi helper `getOption()` e `setOption()` con fallback
  - Metodi `settings()`, `getSettings()` e `updateSettings()` usano OptionsRepository
  - Option key: `fp_ps_code_splitting_manager`
- **Service Provider**: `AssetServiceProvider.php` aggiornato

### 60. SalientWPBakeryOptimizer ✅
- **File**: `src/Services/Compatibility/SalientWPBakeryOptimizer.php`
- **Status**: ✅ Completato
- **Changes**:
  - Aggiunto `OptionsRepositoryInterface` al costruttore (opzionale, dopo `ThemeDetector`)
  - Metodi helper `getOption()` e `setOption()` con fallback
  - Metodi `loadConfig()` e `updateConfig()` usano OptionsRepository
  - Option key: `fp_ps_salient_wpbakery_config`
- **Service Provider**: `IntegrationServiceProvider.php` aggiornato

### 61. HtaccessSecurity ✅
- **File**: `src/Services/Security/HtaccessSecurity.php`
- **Status**: ✅ Completato
- **Changes**:
  - Aggiunto `OptionsRepositoryInterface` al costruttore (opzionale, dopo `$security_headers`)
  - Metodi helper `getOption()` e `setOption()` con fallback
  - Metodi `settings()` e `update()` usano OptionsRepository
  - Option key: `fp_ps_htaccess_security`
- **Service Provider**: `MonitoringServiceProvider.php` aggiornato

### 62. WooCommerceOptimizer ✅
- **File**: `src/Services/Compatibility/WooCommerceOptimizer.php`
- **Status**: ✅ Completato
- **Changes**:
  - Aggiunto `OptionsRepositoryInterface` al costruttore (opzionale)
  - Metodi helper `getOption()` e `setOption()` con fallback
  - Metodi `getSettings()` e `updateSettings()` usano OptionsRepository
  - Option key: `fp_ps_woocommerce`
- **Service Provider**: `IntegrationServiceProvider.php` aggiornato

### 63. Presets\Manager ✅
- **File**: `src/Services/Presets/Manager.php`
- **Status**: ✅ Completato
- **Changes**:
  - Aggiunto `OptionsRepositoryInterface` al costruttore (opzionale, dopo `DebugToggler`)
  - Metodi helper `getOption()` e `setOption()` con fallback
  - Metodi `apply()`, `rollback()` e `getActivePreset()` usano OptionsRepository
  - Option key: `fp_ps_preset`
- **Service Provider**: `MonitoringServiceProvider.php` aggiornato

### 64. AutoFontOptimizer ✅
- **File**: `src/Services/Assets/AutoFontOptimizer.php`
- **Status**: ✅ Completato
- **Changes**:
  - Aggiunto `OptionsRepositoryInterface` al costruttore (opzionale)
  - Metodo helper `getOption()` con fallback
  - Metodo `getSettings()` usa OptionsRepository
  - Option key: `fp_ps_auto_font_optimization`
- **Service Provider**: `AssetServiceProvider.php` e `Plugin.php` aggiornati

### 65. CriticalCss ✅
- **File**: `src/Services/Assets/CriticalCss.php`
- **Status**: ✅ Completato
- **Changes**:
  - Aggiunto `OptionsRepositoryInterface` al costruttore (opzionale)
  - Metodi helper `getOption()` e `setOption()` con fallback
  - Metodi `get()` e `update()` usano OptionsRepository
  - Option key: `fp_ps_critical_css`
- **Service Provider**: `AssetServiceProvider.php` e `Plugin.php` aggiornati

### 66. Media\LazyLoadManager ✅
- **File**: `src/Services/Media/LazyLoadManager.php`
- **Status**: ✅ Completato
- **Changes**:
  - Aggiunto `OptionsRepositoryInterface` al costruttore (opzionale)
  - Metodi helper `getOption()` e `setOption()` con fallback
  - Metodi `getSettings()` e `updateSettings()` usano OptionsRepository
  - Option key: `fp_ps_lazy_load`
- **Service Provider**: `Plugin.php` aggiornato

### 67. DebugToggler ✅
- **File**: `src/Services/Logs/DebugToggler.php`
- **Status**: ✅ Completato
- **Changes**:
  - Aggiunto `OptionsRepositoryInterface` al costruttore (opzionale, dopo `Env`)
  - Metodi helper `getOption()` e `setOption()` con fallback
  - Metodi `updateSettings()` e `determineLogValue()` usano OptionsRepository
  - Option key: `fp_ps_debug_log_value`
- **Service Provider**: `MonitoringServiceProvider.php` e `Plugin.php` aggiornati

### 68. LighthouseFontOptimizer ✅
- **File**: `src/Services/Assets/LighthouseFontOptimizer.php`
- **Status**: ✅ Completato
- **Changes**:
  - Aggiunto `OptionsRepositoryInterface` al costruttore (opzionale)
  - Metodi helper `getOption()` e `setOption()` con fallback
  - Metodi `getSettings()` e `updateSettings()` usano OptionsRepository
  - Option key: `fp_ps_lighthouse_font_optimization`
- **Service Provider**: `AssetServiceProvider.php` e `Plugin.php` aggiornati

### 69. Http2ServerPush ✅
- **File**: `src/Services/Assets/Http2ServerPush.php`
- **Status**: ✅ Completato
- **Changes**:
  - Aggiunto `OptionsRepositoryInterface` al costruttore (opzionale)
  - Metodi helper `getOption()` e `setOption()` con fallback
  - Metodi `getSettings()` e `updateSettings()` usano OptionsRepository
  - Option key: `fp_ps_http2_push`
- **Service Provider**: `Plugin.php` aggiornato

### 70. DOMReflowOptimizer ✅
- **File**: `src/Services/Assets/DOMReflowOptimizer.php`
- **Status**: ✅ Completato
- **Changes**:
  - Aggiunto `OptionsRepositoryInterface` al costruttore (opzionale)
  - Metodi helper `getOption()` e `setOption()` con fallback
  - Metodi `settings()` e `update()` usano OptionsRepository
  - Option key: `fp_ps_dom_reflow_optimization`
- **Service Provider**: `AssetServiceProvider.php` e `Plugin.php` aggiornati

### 71. BatchDOMUpdater ✅
- **File**: `src/Services/Assets/BatchDOMUpdater.php`
- **Status**: ✅ Completato
- **Changes**:
  - Aggiunto `OptionsRepositoryInterface` al costruttore (opzionale)
  - Metodi helper `getOption()` e `setOption()` con fallback
  - Metodi `settings()` e `update()` usano OptionsRepository
  - Option key: `fp_ps_batch_dom_updates`
- **Service Provider**: `Plugin.php` aggiornato

### 72. ResponsiveImageManager ✅
- **File**: `src/Services/Mobile/ResponsiveImageManager.php`
- **Status**: ✅ Completato
- **Changes**:
  - Aggiunto `OptionsRepositoryInterface` al costruttore (opzionale)
  - Metodo helper `getOption()` con fallback
  - Metodo `settings()` usa OptionsRepository
  - Option key: `fp_ps_responsive_images`
- **Service Provider**: `MonitoringServiceProvider.php` e `Plugin.php` aggiornati

### 73. QueryStatistics ✅
- **File**: `src/Services/DB/DatabaseQueryMonitor/QueryStatistics.php`
- **Status**: ✅ Completato
- **Changes**:
  - Aggiunto `OptionsRepositoryInterface` al costruttore (opzionale, dopo `QueryTracker`)
  - Metodi helper `getOption()` e `setOption()` con fallback
  - Metodi `getStatistics()` e `saveStatistics()` usano OptionsRepository
  - Option key: `fp_ps_query_monitor_stats`
- **Note**: Istanzato da `DatabaseQueryMonitor` che passa OptionsRepository

### 74. ExclusionManager ✅
- **File**: `src/Services/Intelligence/SmartExclusionDetector/ExclusionManager.php`
- **Status**: ✅ Completato
- **Changes**:
  - Aggiunto `OptionsRepositoryInterface` al costruttore (opzionale)
  - Metodi helper `getOption()` e `setOption()` con fallback
  - Metodi `addExclusion()`, `getAppliedExclusions()` e `removeExclusion()` usano OptionsRepository
  - Option key: `fp_ps_smart_exclusions`
- **Note**: Istanzato da `SmartExclusionDetector` che passa OptionsRepository

---

## 🔄 Servizi da Migrare a OptionsRepository

### Servizi con chiamate dirette a `get_option()`

- [ ] Altri servizi Cache (EdgeCacheManager)
- [ ] Servizi Database (PluginSpecificOptimizer)
- [x] Altri servizi Assets (UnusedJavaScriptOptimizer) ✅
- **Nota**: ThemeAssetConfiguration usa solo `get_option('page_for_posts')` che è un'opzione WordPress core, quindi non necessita migrazione
- [ ] Servizi Admin che usano opzioni direttamente

---

## 📝 Servizi Migrati a Logger Injectable

### Nessuno ancora migrato

I seguenti servizi usano ancora Logger statico:
- PatternLearner (usa `Logger::info()`)
- CDNExclusionSync (usa `Logger::info()` e `Logger::error()`)
- Altri servizi (da identificare)

**Nota**: LoggerAdapter è disponibile per facilitare la migrazione graduale.

---

## 📋 Pattern di Migrazione

### Opzioni (OptionsRepository)
✅ Pattern stabilito con PatternStorage e CDNProviderDetector

### Logger
⏳ Pattern da stabilire (guida creata in MIGRATION-LOGGER.md)

### Hooks
⏳ Pattern da stabilire (HookRegistry disponibile)

---

## 🎯 Prossimi Servizi da Migrare

### Priorità Alta
1. Altri servizi Intelligence che usano `get_option()`
2. Servizi ML che usano `get_option()`
3. Servizi Cache che leggono configurazione

### Priorità Media
1. Migrare Logger statico a injectable (servizi critici)
2. Spostare hook nel HookRegistry

### Priorità Bassa
1. Refactoring completo pagine Admin
2. Migrazione completa Logger

---

**Note**: Tutte le migrazioni mantengono retrocompatibilità completa con fallback al codice originale.


