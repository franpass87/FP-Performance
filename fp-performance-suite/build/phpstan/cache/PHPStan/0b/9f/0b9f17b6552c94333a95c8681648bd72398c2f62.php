<?php declare(strict_types = 1);

// odsl-/workspace/fp-performance-suite/src
return \PHPStan\Cache\CacheItem::__set_state(array(
   'variableKey' => 'v1',
   'data' => 
  array (
    '/workspace/fp-performance-suite/src/Events/Event.php' => 
    array (
      0 => 'a43303e69badedb17ecbe7eec31711143c50c00e',
      1 => 
      array (
        0 => 'fp\\perfsuite\\events\\event',
      ),
      2 => 
      array (
        0 => 'fp\\perfsuite\\events\\__construct',
        1 => 'fp\\perfsuite\\events\\name',
        2 => 'fp\\perfsuite\\events\\getdata',
        3 => 'fp\\perfsuite\\events\\get',
        4 => 'fp\\perfsuite\\events\\timestamp',
        5 => 'fp\\perfsuite\\events\\shouldpropagate',
      ),
      3 => 
      array (
      ),
    ),
    '/workspace/fp-performance-suite/src/Events/EventDispatcher.php' => 
    array (
      0 => 'e37ca5adf1fe81a2142446a4e7e6f82bc895d3c8',
      1 => 
      array (
        0 => 'fp\\perfsuite\\events\\eventdispatcher',
      ),
      2 => 
      array (
        0 => 'fp\\perfsuite\\events\\instance',
        1 => 'fp\\perfsuite\\events\\dispatch',
        2 => 'fp\\perfsuite\\events\\listen',
        3 => 'fp\\perfsuite\\events\\remove',
        4 => 'fp\\perfsuite\\events\\getdispatched',
        5 => 'fp\\perfsuite\\events\\clearhistory',
        6 => 'fp\\perfsuite\\events\\getlisteners',
      ),
      3 => 
      array (
      ),
    ),
    '/workspace/fp-performance-suite/src/Events/CacheClearedEvent.php' => 
    array (
      0 => '30ccead9f442e7824aa582b14b01a11a144e0203',
      1 => 
      array (
        0 => 'fp\\perfsuite\\events\\cacheclearedevent',
      ),
      2 => 
      array (
        0 => 'fp\\perfsuite\\events\\name',
        1 => 'fp\\perfsuite\\events\\getfilesdeleted',
        2 => 'fp\\perfsuite\\events\\getcachetype',
      ),
      3 => 
      array (
      ),
    ),
    '/workspace/fp-performance-suite/src/Events/WebPConvertedEvent.php' => 
    array (
      0 => '7f569b47109ac0e2d91ddf3cf7a444942fdf4d40',
      1 => 
      array (
        0 => 'fp\\perfsuite\\events\\webpconvertedevent',
      ),
      2 => 
      array (
        0 => 'fp\\perfsuite\\events\\name',
        1 => 'fp\\perfsuite\\events\\getoriginalfile',
        2 => 'fp\\perfsuite\\events\\getwebpfile',
        3 => 'fp\\perfsuite\\events\\getsizereduction',
      ),
      3 => 
      array (
      ),
    ),
    '/workspace/fp-performance-suite/src/Events/DatabaseCleanedEvent.php' => 
    array (
      0 => 'fa4636b01f1e5aa51704a63d5b2d945f304a78fb',
      1 => 
      array (
        0 => 'fp\\perfsuite\\events\\databasecleanedevent',
      ),
      2 => 
      array (
        0 => 'fp\\perfsuite\\events\\name',
        1 => 'fp\\perfsuite\\events\\isdryrun',
        2 => 'fp\\perfsuite\\events\\getresults',
        3 => 'fp\\perfsuite\\events\\gettotaldeleted',
        4 => 'fp\\perfsuite\\events\\getscope',
      ),
      3 => 
      array (
      ),
    ),
    '/workspace/fp-performance-suite/src/Monitoring/QueryMonitor/Collector.php' => 
    array (
      0 => '37594a4bf91f01a1e95141f99e4f8f8c30c6a664',
      1 => 
      array (
        0 => 'fp\\perfsuite\\monitoring\\querymonitor\\collector',
      ),
      2 => 
      array (
        0 => 'fp\\perfsuite\\monitoring\\querymonitor\\name',
        1 => 'fp\\perfsuite\\monitoring\\querymonitor\\process',
        2 => 'fp\\perfsuite\\monitoring\\querymonitor\\getmemorylimit',
      ),
      3 => 
      array (
      ),
    ),
    '/workspace/fp-performance-suite/src/Monitoring/QueryMonitor/Output.php' => 
    array (
      0 => 'a235d80fe36901ec7cafdb926b7c7286d1161269',
      1 => 
      array (
        0 => 'fp\\perfsuite\\monitoring\\querymonitor\\output',
      ),
      2 => 
      array (
        0 => 'fp\\perfsuite\\monitoring\\querymonitor\\__construct',
        1 => 'fp\\perfsuite\\monitoring\\querymonitor\\name',
        2 => 'fp\\perfsuite\\monitoring\\querymonitor\\output',
        3 => 'fp\\perfsuite\\monitoring\\querymonitor\\adminmenu',
      ),
      3 => 
      array (
      ),
    ),
    '/workspace/fp-performance-suite/src/Monitoring/QueryMonitor.php' => 
    array (
      0 => '4708c2eb37d75ba9d877bf1c4720f5a855310a5d',
      1 => 
      array (
        0 => 'fp\\perfsuite\\monitoring\\querymonitor',
      ),
      2 => 
      array (
        0 => 'fp\\perfsuite\\monitoring\\register',
        1 => 'fp\\perfsuite\\monitoring\\addcollector',
        2 => 'fp\\perfsuite\\monitoring\\addoutputter',
        3 => 'fp\\perfsuite\\monitoring\\track',
        4 => 'fp\\perfsuite\\monitoring\\starttimer',
        5 => 'fp\\perfsuite\\monitoring\\stoptimer',
        6 => 'fp\\perfsuite\\monitoring\\getmetrics',
      ),
      3 => 
      array (
      ),
    ),
    '/workspace/fp-performance-suite/src/Services/Score/Scorer.php' => 
    array (
      0 => 'ad28f4c9f68e658abf4a27a592939d4714f1fc19',
      1 => 
      array (
        0 => 'fp\\perfsuite\\services\\score\\scorer',
      ),
      2 => 
      array (
        0 => 'fp\\perfsuite\\services\\score\\__construct',
        1 => 'fp\\perfsuite\\services\\score\\calculate',
        2 => 'fp\\perfsuite\\services\\score\\activeoptimizations',
        3 => 'fp\\perfsuite\\services\\score\\gzipscore',
        4 => 'fp\\perfsuite\\services\\score\\browsercachescore',
        5 => 'fp\\perfsuite\\services\\score\\pagecachescore',
        6 => 'fp\\perfsuite\\services\\score\\assetsscore',
        7 => 'fp\\perfsuite\\services\\score\\webpscore',
        8 => 'fp\\perfsuite\\services\\score\\databasescore',
        9 => 'fp\\perfsuite\\services\\score\\heartbeatscore',
        10 => 'fp\\perfsuite\\services\\score\\emojiscore',
        11 => 'fp\\perfsuite\\services\\score\\criticalcssscore',
        12 => 'fp\\perfsuite\\services\\score\\logscore',
      ),
      3 => 
      array (
      ),
    ),
    '/workspace/fp-performance-suite/src/Services/Logs/DebugToggler.php' => 
    array (
      0 => 'ebb9b2f92d7588d7eeb0f678e14068e75c3d44ee',
      1 => 
      array (
        0 => 'fp\\perfsuite\\services\\logs\\debugtoggler',
      ),
      2 => 
      array (
        0 => 'fp\\perfsuite\\services\\logs\\__construct',
        1 => 'fp\\perfsuite\\services\\logs\\status',
        2 => 'fp\\perfsuite\\services\\logs\\toggle',
        3 => 'fp\\perfsuite\\services\\logs\\backup',
        4 => 'fp\\perfsuite\\services\\logs\\revertlatest',
        5 => 'fp\\perfsuite\\services\\logs\\determinelogvalue',
        6 => 'fp\\perfsuite\\services\\logs\\exportvalue',
        7 => 'fp\\perfsuite\\services\\logs\\extractconstant',
        8 => 'fp\\perfsuite\\services\\logs\\parseconstant',
      ),
      3 => 
      array (
      ),
    ),
    '/workspace/fp-performance-suite/src/Services/Logs/RealtimeLog.php' => 
    array (
      0 => '65733e4ae72b64c0198e92268234fd3b34d51df3',
      1 => 
      array (
        0 => 'fp\\perfsuite\\services\\logs\\realtimelog',
      ),
      2 => 
      array (
        0 => 'fp\\perfsuite\\services\\logs\\__construct',
        1 => 'fp\\perfsuite\\services\\logs\\tail',
        2 => 'fp\\perfsuite\\services\\logs\\readtail',
      ),
      3 => 
      array (
      ),
    ),
    '/workspace/fp-performance-suite/src/Services/Assets/CriticalCss.php' => 
    array (
      0 => '1975be5e6c08c7b31c470fc6abb32ffbdcdc4eec',
      1 => 
      array (
        0 => 'fp\\perfsuite\\services\\assets\\criticalcss',
      ),
      2 => 
      array (
        0 => 'fp\\perfsuite\\services\\assets\\register',
        1 => 'fp\\perfsuite\\services\\assets\\isenabled',
        2 => 'fp\\perfsuite\\services\\assets\\get',
        3 => 'fp\\perfsuite\\services\\assets\\update',
        4 => 'fp\\perfsuite\\services\\assets\\clear',
        5 => 'fp\\perfsuite\\services\\assets\\inlinecriticalcss',
        6 => 'fp\\perfsuite\\services\\assets\\generate',
        7 => 'fp\\perfsuite\\services\\assets\\isvalidcss',
        8 => 'fp\\perfsuite\\services\\assets\\extractcriticalstyles',
        9 => 'fp\\perfsuite\\services\\assets\\resolveurl',
        10 => 'fp\\perfsuite\\services\\assets\\fetchcss',
        11 => 'fp\\perfsuite\\services\\assets\\filterabovefoldcss',
        12 => 'fp\\perfsuite\\services\\assets\\minifycss',
        13 => 'fp\\perfsuite\\services\\assets\\status',
      ),
      3 => 
      array (
      ),
    ),
    '/workspace/fp-performance-suite/src/Services/Assets/WordPressOptimizer.php' => 
    array (
      0 => '806948030a75a242057d6a25af0d551b34238843',
      1 => 
      array (
        0 => 'fp\\perfsuite\\services\\assets\\wordpressoptimizer',
      ),
      2 => 
      array (
        0 => 'fp\\perfsuite\\services\\assets\\disableemojis',
        1 => 'fp\\perfsuite\\services\\assets\\configureheartbeat',
        2 => 'fp\\perfsuite\\services\\assets\\registerheartbeat',
      ),
      3 => 
      array (
      ),
    ),
    '/workspace/fp-performance-suite/src/Services/Assets/HtmlMinifier.php' => 
    array (
      0 => '5a8df0213af1264f6b821ad8dc49211a02badda3',
      1 => 
      array (
        0 => 'fp\\perfsuite\\services\\assets\\htmlminifier',
      ),
      2 => 
      array (
        0 => 'fp\\perfsuite\\services\\assets\\startbuffer',
        1 => 'fp\\perfsuite\\services\\assets\\endbuffer',
        2 => 'fp\\perfsuite\\services\\assets\\minify',
        3 => 'fp\\perfsuite\\services\\assets\\isbuffering',
      ),
      3 => 
      array (
      ),
    ),
    '/workspace/fp-performance-suite/src/Services/Assets/Combiners/JsCombiner.php' => 
    array (
      0 => '55c441daab9146f6fff83de10d611212add2e2f4',
      1 => 
      array (
        0 => 'fp\\perfsuite\\services\\assets\\combiners\\jscombiner',
      ),
      2 => 
      array (
        0 => 'fp\\perfsuite\\services\\assets\\combiners\\getextension',
        1 => 'fp\\perfsuite\\services\\assets\\combiners\\gettype',
        2 => 'fp\\perfsuite\\services\\assets\\combiners\\combine',
        3 => 'fp\\perfsuite\\services\\assets\\combiners\\combinedependencygroup',
        4 => 'fp\\perfsuite\\services\\assets\\combiners\\matchesgroup',
        5 => 'fp\\perfsuite\\services\\assets\\combiners\\iscombined',
        6 => 'fp\\perfsuite\\services\\assets\\combiners\\reset',
      ),
      3 => 
      array (
      ),
    ),
    '/workspace/fp-performance-suite/src/Services/Assets/Combiners/AssetCombinerBase.php' => 
    array (
      0 => 'a5e706856d2417aec33bca37a07f2740a755b499',
      1 => 
      array (
        0 => 'fp\\perfsuite\\services\\assets\\combiners\\assetcombinerbase',
      ),
      2 => 
      array (
        0 => 'fp\\perfsuite\\services\\assets\\combiners\\__construct',
        1 => 'fp\\perfsuite\\services\\assets\\combiners\\getextension',
        2 => 'fp\\perfsuite\\services\\assets\\combiners\\gettype',
        3 => 'fp\\perfsuite\\services\\assets\\combiners\\isdependencycombinable',
        4 => 'fp\\perfsuite\\services\\assets\\combiners\\resolvedependencysource',
        5 => 'fp\\perfsuite\\services\\assets\\combiners\\writecombinedasset',
        6 => 'fp\\perfsuite\\services\\assets\\combiners\\replacedependencies',
      ),
      3 => 
      array (
      ),
    ),
    '/workspace/fp-performance-suite/src/Services/Assets/Combiners/CssCombiner.php' => 
    array (
      0 => '52635e9cec8d9390277bbf0853055e9a71a59363',
      1 => 
      array (
        0 => 'fp\\perfsuite\\services\\assets\\combiners\\csscombiner',
      ),
      2 => 
      array (
        0 => 'fp\\perfsuite\\services\\assets\\combiners\\getextension',
        1 => 'fp\\perfsuite\\services\\assets\\combiners\\gettype',
        2 => 'fp\\perfsuite\\services\\assets\\combiners\\combine',
        3 => 'fp\\perfsuite\\services\\assets\\combiners\\combinedependencygroup',
        4 => 'fp\\perfsuite\\services\\assets\\combiners\\iscombined',
        5 => 'fp\\perfsuite\\services\\assets\\combiners\\reset',
      ),
      3 => 
      array (
      ),
    ),
    '/workspace/fp-performance-suite/src/Services/Assets/Combiners/DependencyResolver.php' => 
    array (
      0 => 'a1c6caccc4c86cb22b1ecd0337a056f3d4b3e2c5',
      1 => 
      array (
        0 => 'fp\\perfsuite\\services\\assets\\combiners\\dependencyresolver',
      ),
      2 => 
      array (
        0 => 'fp\\perfsuite\\services\\assets\\combiners\\resolvedependencies',
        1 => 'fp\\perfsuite\\services\\assets\\combiners\\filterexternaldependencies',
        2 => 'fp\\perfsuite\\services\\assets\\combiners\\normalizedependencies',
      ),
      3 => 
      array (
      ),
    ),
    '/workspace/fp-performance-suite/src/Services/Assets/Optimizer.php' => 
    array (
      0 => 'ee4d1a1305f80c7e2d44b57c65fed756fc2f8518',
      1 => 
      array (
        0 => 'fp\\perfsuite\\services\\assets\\optimizer',
      ),
      2 => 
      array (
        0 => 'fp\\perfsuite\\services\\assets\\__construct',
        1 => 'fp\\perfsuite\\services\\assets\\register',
        2 => 'fp\\perfsuite\\services\\assets\\settings',
        3 => 'fp\\perfsuite\\services\\assets\\update',
        4 => 'fp\\perfsuite\\services\\assets\\applycombination',
        5 => 'fp\\perfsuite\\services\\assets\\startbuffer',
        6 => 'fp\\perfsuite\\services\\assets\\endbuffer',
        7 => 'fp\\perfsuite\\services\\assets\\minifyhtml',
        8 => 'fp\\perfsuite\\services\\assets\\filterscripttag',
        9 => 'fp\\perfsuite\\services\\assets\\dnsprefetch',
        10 => 'fp\\perfsuite\\services\\assets\\preloadresources',
        11 => 'fp\\perfsuite\\services\\assets\\heartbeatsettings',
        12 => 'fp\\perfsuite\\services\\assets\\status',
        13 => 'fp\\perfsuite\\services\\assets\\risklevels',
        14 => 'fp\\perfsuite\\services\\assets\\sanitizeurllist',
        15 => 'fp\\perfsuite\\services\\assets\\resolveflag',
        16 => 'fp\\perfsuite\\services\\assets\\interpretflag',
        17 => 'fp\\perfsuite\\services\\assets\\gethtmlminifier',
        18 => 'fp\\perfsuite\\services\\assets\\getscriptoptimizer',
        19 => 'fp\\perfsuite\\services\\assets\\getwordpressoptimizer',
        20 => 'fp\\perfsuite\\services\\assets\\getresourcehintsmanager',
        21 => 'fp\\perfsuite\\services\\assets\\getcsscombiner',
        22 => 'fp\\perfsuite\\services\\assets\\getjscombiner',
      ),
      3 => 
      array (
      ),
    ),
    '/workspace/fp-performance-suite/src/Services/Assets/ScriptOptimizer.php' => 
    array (
      0 => '852d6cf030c46a561be55d809c9a2ab693baf7a1',
      1 => 
      array (
        0 => 'fp\\perfsuite\\services\\assets\\scriptoptimizer',
      ),
      2 => 
      array (
        0 => 'fp\\perfsuite\\services\\assets\\filterscripttag',
        1 => 'fp\\perfsuite\\services\\assets\\setskiphandles',
        2 => 'fp\\perfsuite\\services\\assets\\getskiphandles',
      ),
      3 => 
      array (
      ),
    ),
    '/workspace/fp-performance-suite/src/Services/Assets/ResourceHints/ResourceHintsManager.php' => 
    array (
      0 => 'e0db6ecc4577373a3b77eede7c476a9f5a20439d',
      1 => 
      array (
        0 => 'fp\\perfsuite\\services\\assets\\resourcehints\\resourcehintsmanager',
      ),
      2 => 
      array (
        0 => 'fp\\perfsuite\\services\\assets\\resourcehints\\adddnsprefetch',
        1 => 'fp\\perfsuite\\services\\assets\\resourcehints\\addpreloadhints',
        2 => 'fp\\perfsuite\\services\\assets\\resourcehints\\setdnsprefetchurls',
        3 => 'fp\\perfsuite\\services\\assets\\resourcehints\\setpreloadurls',
        4 => 'fp\\perfsuite\\services\\assets\\resourcehints\\sanitizeurllist',
        5 => 'fp\\perfsuite\\services\\assets\\resourcehints\\formatpreloadhints',
        6 => 'fp\\perfsuite\\services\\assets\\resourcehints\\mergepreloadhints',
        7 => 'fp\\perfsuite\\services\\assets\\resourcehints\\guesspreloadtype',
      ),
      3 => 
      array (
      ),
    ),
    '/workspace/fp-performance-suite/src/Services/Monitoring/PerformanceMonitor.php' => 
    array (
      0 => 'faba06148ec26ebc514e515220f98949779a4601',
      1 => 
      array (
        0 => 'fp\\perfsuite\\services\\monitoring\\performancemonitor',
      ),
      2 => 
      array (
        0 => 'fp\\perfsuite\\services\\monitoring\\__construct',
        1 => 'fp\\perfsuite\\services\\monitoring\\instance',
        2 => 'fp\\perfsuite\\services\\monitoring\\register',
        3 => 'fp\\perfsuite\\services\\monitoring\\isenabled',
        4 => 'fp\\perfsuite\\services\\monitoring\\settings',
        5 => 'fp\\perfsuite\\services\\monitoring\\update',
        6 => 'fp\\perfsuite\\services\\monitoring\\recordpageload',
        7 => 'fp\\perfsuite\\services\\monitoring\\storemetric',
        8 => 'fp\\perfsuite\\services\\monitoring\\track',
        9 => 'fp\\perfsuite\\services\\monitoring\\starttimer',
        10 => 'fp\\perfsuite\\services\\monitoring\\stoptimer',
        11 => 'fp\\perfsuite\\services\\monitoring\\getstats',
        12 => 'fp\\perfsuite\\services\\monitoring\\getrecent',
        13 => 'fp\\perfsuite\\services\\monitoring\\clearmetrics',
        14 => 'fp\\perfsuite\\services\\monitoring\\injecttimingscript',
        15 => 'fp\\perfsuite\\services\\monitoring\\gettrends',
      ),
      3 => 
      array (
      ),
    ),
    '/workspace/fp-performance-suite/src/Services/Media/WebPConverter.php' => 
    array (
      0 => '9de22d29e4e750bf630218dfcbeaef232519c3a3',
      1 => 
      array (
        0 => 'fp\\perfsuite\\services\\media\\webpconverter',
      ),
      2 => 
      array (
        0 => 'fp\\perfsuite\\services\\media\\__construct',
        1 => 'fp\\perfsuite\\services\\media\\register',
        2 => 'fp\\perfsuite\\services\\media\\settings',
        3 => 'fp\\perfsuite\\services\\media\\update',
        4 => 'fp\\perfsuite\\services\\media\\generatewebp',
        5 => 'fp\\perfsuite\\services\\media\\convert',
        6 => 'fp\\perfsuite\\services\\media\\bulkconvert',
        7 => 'fp\\perfsuite\\services\\media\\runqueue',
        8 => 'fp\\perfsuite\\services\\media\\coverage',
        9 => 'fp\\perfsuite\\services\\media\\status',
        10 => 'fp\\perfsuite\\services\\media\\getimageconverter',
        11 => 'fp\\perfsuite\\services\\media\\getqueue',
        12 => 'fp\\perfsuite\\services\\media\\getbatchprocessor',
        13 => 'fp\\perfsuite\\services\\media\\getattachmentprocessor',
        14 => 'fp\\perfsuite\\services\\media\\getpathhelper',
      ),
      3 => 
      array (
      ),
    ),
    '/workspace/fp-performance-suite/src/Services/Media/WebP/WebPQueue.php' => 
    array (
      0 => '45a2c5bc03b4825d0708ac0329135501bb2cffc7',
      1 => 
      array (
        0 => 'fp\\perfsuite\\services\\media\\webp\\webpqueue',
      ),
      2 => 
      array (
        0 => 'fp\\perfsuite\\services\\media\\webp\\__construct',
        1 => 'fp\\perfsuite\\services\\media\\webp\\initializebulkconversion',
        2 => 'fp\\perfsuite\\services\\media\\webp\\getstate',
        3 => 'fp\\perfsuite\\services\\media\\webp\\updatestate',
        4 => 'fp\\perfsuite\\services\\media\\webp\\clear',
        5 => 'fp\\perfsuite\\services\\media\\webp\\schedulebatch',
        6 => 'fp\\perfsuite\\services\\media\\webp\\getnextbatch',
        7 => 'fp\\perfsuite\\services\\media\\webp\\countqueuedimages',
        8 => 'fp\\perfsuite\\services\\media\\webp\\isactive',
        9 => 'fp\\perfsuite\\services\\media\\webp\\getcronhook',
      ),
      3 => 
      array (
      ),
    ),
    '/workspace/fp-performance-suite/src/Services/Media/WebP/WebPPathHelper.php' => 
    array (
      0 => 'd111307cc324c95bcf2fb6afb848a3fd3f09d591',
      1 => 
      array (
        0 => 'fp\\perfsuite\\services\\media\\webp\\webppathhelper',
      ),
      2 => 
      array (
        0 => 'fp\\perfsuite\\services\\media\\webp\\getwebppath',
        1 => 'fp\\perfsuite\\services\\media\\webp\\withwebpextension',
        2 => 'fp\\perfsuite\\services\\media\\webp\\safefilesize',
      ),
      3 => 
      array (
      ),
    ),
    '/workspace/fp-performance-suite/src/Services/Media/WebP/WebPBatchProcessor.php' => 
    array (
      0 => '7dd389c481ed0f60fdf05488193f28efd86acc29',
      1 => 
      array (
        0 => 'fp\\perfsuite\\services\\media\\webp\\webpbatchprocessor',
      ),
      2 => 
      array (
        0 => 'fp\\perfsuite\\services\\media\\webp\\__construct',
        1 => 'fp\\perfsuite\\services\\media\\webp\\processbatch',
        2 => 'fp\\perfsuite\\services\\media\\webp\\processattachments',
        3 => 'fp\\perfsuite\\services\\media\\webp\\setchunksize',
      ),
      3 => 
      array (
      ),
    ),
    '/workspace/fp-performance-suite/src/Services/Media/WebP/WebPImageConverter.php' => 
    array (
      0 => 'd19d332fee22951fd9dadff4c4c3b863bbe36e1a',
      1 => 
      array (
        0 => 'fp\\perfsuite\\services\\media\\webp\\webpimageconverter',
      ),
      2 => 
      array (
        0 => 'fp\\perfsuite\\services\\media\\webp\\convert',
        1 => 'fp\\perfsuite\\services\\media\\webp\\isconvertible',
        2 => 'fp\\perfsuite\\services\\media\\webp\\needsconversion',
        3 => 'fp\\perfsuite\\services\\media\\webp\\convertwithimagick',
        4 => 'fp\\perfsuite\\services\\media\\webp\\convertwithgd',
        5 => 'fp\\perfsuite\\services\\media\\webp\\createimageresource',
        6 => 'fp\\perfsuite\\services\\media\\webp\\logsuccess',
        7 => 'fp\\perfsuite\\services\\media\\webp\\getsupportedextensions',
      ),
      3 => 
      array (
      ),
    ),
    '/workspace/fp-performance-suite/src/Services/Media/WebP/WebPAttachmentProcessor.php' => 
    array (
      0 => '515b9f5479dbc3cf82fd092527dd4f08225b85f9',
      1 => 
      array (
        0 => 'fp\\perfsuite\\services\\media\\webp\\webpattachmentprocessor',
      ),
      2 => 
      array (
        0 => 'fp\\perfsuite\\services\\media\\webp\\__construct',
        1 => 'fp\\perfsuite\\services\\media\\webp\\process',
        2 => 'fp\\perfsuite\\services\\media\\webp\\shouldforceconversion',
        3 => 'fp\\perfsuite\\services\\media\\webp\\updatemainfilemetadata',
        4 => 'fp\\perfsuite\\services\\media\\webp\\processsizes',
        5 => 'fp\\perfsuite\\services\\media\\webp\\updatepostmeta',
      ),
      3 => 
      array (
      ),
    ),
    '/workspace/fp-performance-suite/src/Services/Presets/Manager.php' => 
    array (
      0 => '8f7e84b5249ef4b09a41d9ec0cd3ff2eafdbc622',
      1 => 
      array (
        0 => 'fp\\perfsuite\\services\\presets\\manager',
      ),
      2 => 
      array (
        0 => 'fp\\perfsuite\\services\\presets\\__construct',
        1 => 'fp\\perfsuite\\services\\presets\\presets',
        2 => 'fp\\perfsuite\\services\\presets\\apply',
        3 => 'fp\\perfsuite\\services\\presets\\rollback',
        4 => 'fp\\perfsuite\\services\\presets\\getactivepreset',
        5 => 'fp\\perfsuite\\services\\presets\\labelfor',
      ),
      3 => 
      array (
      ),
    ),
    '/workspace/fp-performance-suite/src/Services/Reports/ScheduledReports.php' => 
    array (
      0 => '7c60b210adfa8f7f0e97cc92770993eb89a24b19',
      1 => 
      array (
        0 => 'fp\\perfsuite\\services\\reports\\scheduledreports',
      ),
      2 => 
      array (
        0 => 'fp\\perfsuite\\services\\reports\\register',
        1 => 'fp\\perfsuite\\services\\reports\\addschedules',
        2 => 'fp\\perfsuite\\services\\reports\\settings',
        3 => 'fp\\perfsuite\\services\\reports\\update',
        4 => 'fp\\perfsuite\\services\\reports\\maybeschedule',
        5 => 'fp\\perfsuite\\services\\reports\\getrecurrence',
        6 => 'fp\\perfsuite\\services\\reports\\sendreport',
        7 => 'fp\\perfsuite\\services\\reports\\generatereport',
        8 => 'fp\\perfsuite\\services\\reports\\renderemailtemplate',
        9 => 'fp\\perfsuite\\services\\reports\\sendtestreport',
      ),
      3 => 
      array (
      ),
    ),
    '/workspace/fp-performance-suite/src/Services/Cache/PageCache.php' => 
    array (
      0 => 'f359767d1a8bd66bc1d111d29aa4b4f5e341abe6',
      1 => 
      array (
        0 => 'fp\\perfsuite\\services\\cache\\pagecache',
      ),
      2 => 
      array (
        0 => 'fp\\perfsuite\\services\\cache\\__construct',
        1 => 'fp\\perfsuite\\services\\cache\\register',
        2 => 'fp\\perfsuite\\services\\cache\\isenabled',
        3 => 'fp\\perfsuite\\services\\cache\\settings',
        4 => 'fp\\perfsuite\\services\\cache\\update',
        5 => 'fp\\perfsuite\\services\\cache\\cachedir',
        6 => 'fp\\perfsuite\\services\\cache\\clear',
        7 => 'fp\\perfsuite\\services\\cache\\maybeservecache',
        8 => 'fp\\perfsuite\\services\\cache\\startbuffering',
        9 => 'fp\\perfsuite\\services\\cache\\maybefilteroutput',
        10 => 'fp\\perfsuite\\services\\cache\\savebuffer',
        11 => 'fp\\perfsuite\\services\\cache\\iscacheablerequest',
        12 => 'fp\\perfsuite\\services\\cache\\cachefile',
        13 => 'fp\\perfsuite\\services\\cache\\status',
        14 => 'fp\\perfsuite\\services\\cache\\finishbuffering',
        15 => 'fp\\perfsuite\\services\\cache\\isheadrequest',
        16 => 'fp\\perfsuite\\services\\cache\\hasprivatecookies',
      ),
      3 => 
      array (
      ),
    ),
    '/workspace/fp-performance-suite/src/Services/Cache/Headers.php' => 
    array (
      0 => 'd020b51cb666c0dc99d742a7c1b2d13de111aec3',
      1 => 
      array (
        0 => 'fp\\perfsuite\\services\\cache\\headers',
      ),
      2 => 
      array (
        0 => 'fp\\perfsuite\\services\\cache\\__construct',
        1 => 'fp\\perfsuite\\services\\cache\\register',
        2 => 'fp\\perfsuite\\services\\cache\\settings',
        3 => 'fp\\perfsuite\\services\\cache\\update',
        4 => 'fp\\perfsuite\\services\\cache\\applyhtaccess',
        5 => 'fp\\perfsuite\\services\\cache\\status',
        6 => 'fp\\perfsuite\\services\\cache\\defaulthtaccess',
        7 => 'fp\\perfsuite\\services\\cache\\formatexpiresheader',
        8 => 'fp\\perfsuite\\services\\cache\\normalizehtaccess',
        9 => 'fp\\perfsuite\\services\\cache\\hasprivatecookies',
      ),
      3 => 
      array (
      ),
    ),
    '/workspace/fp-performance-suite/src/Services/DB/Cleaner.php' => 
    array (
      0 => 'e1dcf884a99e4abccf3d86864c25e8547271db0c',
      1 => 
      array (
        0 => 'fp\\perfsuite\\services\\db\\cleaner',
      ),
      2 => 
      array (
        0 => 'fp\\perfsuite\\services\\db\\__construct',
        1 => 'fp\\perfsuite\\services\\db\\register',
        2 => 'fp\\perfsuite\\services\\db\\primeschedules',
        3 => 'fp\\perfsuite\\services\\db\\settings',
        4 => 'fp\\perfsuite\\services\\db\\update',
        5 => 'fp\\perfsuite\\services\\db\\registerschedules',
        6 => 'fp\\perfsuite\\services\\db\\reschedule',
        7 => 'fp\\perfsuite\\services\\db\\maybeschedule',
        8 => 'fp\\perfsuite\\services\\db\\runscheduledcleanup',
        9 => 'fp\\perfsuite\\services\\db\\cleanup',
        10 => 'fp\\perfsuite\\services\\db\\cleanupposts',
        11 => 'fp\\perfsuite\\services\\db\\cleanupcomments',
        12 => 'fp\\perfsuite\\services\\db\\cleanuptransients',
        13 => 'fp\\perfsuite\\services\\db\\cleanupmeta',
        14 => 'fp\\perfsuite\\services\\db\\optimizetables',
        15 => 'fp\\perfsuite\\services\\db\\overhead',
        16 => 'fp\\perfsuite\\services\\db\\status',
        17 => 'fp\\perfsuite\\services\\db\\normalizeschedule',
      ),
      3 => 
      array (
      ),
    ),
    '/workspace/fp-performance-suite/src/Services/CDN/CdnManager.php' => 
    array (
      0 => '85e33e86488ce3d857a78064dda02da7c9098b2a',
      1 => 
      array (
        0 => 'fp\\perfsuite\\services\\cdn\\cdnmanager',
      ),
      2 => 
      array (
        0 => 'fp\\perfsuite\\services\\cdn\\register',
        1 => 'fp\\perfsuite\\services\\cdn\\settings',
        2 => 'fp\\perfsuite\\services\\cdn\\update',
        3 => 'fp\\perfsuite\\services\\cdn\\rewriteurl',
        4 => 'fp\\perfsuite\\services\\cdn\\rewritesrcset',
        5 => 'fp\\perfsuite\\services\\cdn\\rewritecontenturls',
        6 => 'fp\\perfsuite\\services\\cdn\\selectcdndomain',
        7 => 'fp\\perfsuite\\services\\cdn\\purgeall',
        8 => 'fp\\perfsuite\\services\\cdn\\purgefile',
        9 => 'fp\\perfsuite\\services\\cdn\\purgecloudflare',
        10 => 'fp\\perfsuite\\services\\cdn\\purgebunnycdn',
        11 => 'fp\\perfsuite\\services\\cdn\\testconnection',
        12 => 'fp\\perfsuite\\services\\cdn\\status',
        13 => 'fp\\perfsuite\\services\\cdn\\sanitizedomains',
        14 => 'fp\\perfsuite\\services\\cdn\\sanitizeextensions',
      ),
      3 => 
      array (
      ),
    ),
    '/workspace/fp-performance-suite/src/ValueObjects/CacheSettings.php' => 
    array (
      0 => '978670bdaf9b759709ad2e7752aa50e8a1bd61c2',
      1 => 
      array (
        0 => 'fp\\perfsuite\\valueobjects\\cachesettings',
      ),
      2 => 
      array (
        0 => 'fp\\perfsuite\\valueobjects\\__construct',
        1 => 'fp\\perfsuite\\valueobjects\\fromarray',
        2 => 'fp\\perfsuite\\valueobjects\\toarray',
        3 => 'fp\\perfsuite\\valueobjects\\withenabled',
        4 => 'fp\\perfsuite\\valueobjects\\withttl',
        5 => 'fp\\perfsuite\\valueobjects\\isactive',
        6 => 'fp\\perfsuite\\valueobjects\\getttlhumanreadable',
      ),
      3 => 
      array (
      ),
    ),
    '/workspace/fp-performance-suite/src/ValueObjects/WebPSettings.php' => 
    array (
      0 => 'e7c929c87123e43c58521e594abd17f0a378828f',
      1 => 
      array (
        0 => 'fp\\perfsuite\\valueobjects\\webpsettings',
      ),
      2 => 
      array (
        0 => 'fp\\perfsuite\\valueobjects\\__construct',
        1 => 'fp\\perfsuite\\valueobjects\\fromarray',
        2 => 'fp\\perfsuite\\valueobjects\\toarray',
        3 => 'fp\\perfsuite\\valueobjects\\withquality',
        4 => 'fp\\perfsuite\\valueobjects\\withenabled',
        5 => 'fp\\perfsuite\\valueobjects\\getcompressionmode',
        6 => 'fp\\perfsuite\\valueobjects\\getqualitylevel',
      ),
      3 => 
      array (
      ),
    ),
    '/workspace/fp-performance-suite/src/ValueObjects/PerformanceScore.php' => 
    array (
      0 => '9b73c37a2576171cc0837a6be99159168181e63c',
      1 => 
      array (
        0 => 'fp\\perfsuite\\valueobjects\\performancescore',
      ),
      2 => 
      array (
        0 => 'fp\\perfsuite\\valueobjects\\__construct',
        1 => 'fp\\perfsuite\\valueobjects\\fromarray',
        2 => 'fp\\perfsuite\\valueobjects\\toarray',
        3 => 'fp\\perfsuite\\valueobjects\\getgrade',
        4 => 'fp\\perfsuite\\valueobjects\\getcolor',
        5 => 'fp\\perfsuite\\valueobjects\\getstatusmessage',
        6 => 'fp\\perfsuite\\valueobjects\\ispassing',
        7 => 'fp\\perfsuite\\valueobjects\\getemoji',
        8 => 'fp\\perfsuite\\valueobjects\\getpercentage',
        9 => 'fp\\perfsuite\\valueobjects\\compareto',
        10 => 'fp\\perfsuite\\valueobjects\\isimprovedfrom',
        11 => 'fp\\perfsuite\\valueobjects\\getdelta',
      ),
      3 => 
      array (
      ),
    ),
    '/workspace/fp-performance-suite/src/Cli/Commands.php' => 
    array (
      0 => 'c5540085174c7a7bf73c1faacd84a8e22cd90cf7',
      1 => 
      array (
        0 => 'fp\\perfsuite\\cli\\commands',
      ),
      2 => 
      array (
        0 => 'fp\\perfsuite\\cli\\cache',
        1 => 'fp\\perfsuite\\cli\\cacheclear',
        2 => 'fp\\perfsuite\\cli\\cachestatus',
        3 => 'fp\\perfsuite\\cli\\db',
        4 => 'fp\\perfsuite\\cli\\dbcleanup',
        5 => 'fp\\perfsuite\\cli\\dbstatus',
        6 => 'fp\\perfsuite\\cli\\webp',
        7 => 'fp\\perfsuite\\cli\\webpconvert',
        8 => 'fp\\perfsuite\\cli\\webpstatus',
        9 => 'fp\\perfsuite\\cli\\score',
        10 => 'fp\\perfsuite\\cli\\info',
      ),
      3 => 
      array (
      ),
    ),
    '/workspace/fp-performance-suite/src/Http/Routes.php' => 
    array (
      0 => 'a64c5049ff9a69c217b3e47d6d89c097cacdcdb5',
      1 => 
      array (
        0 => 'fp\\perfsuite\\http\\routes',
      ),
      2 => 
      array (
        0 => 'fp\\perfsuite\\http\\__construct',
        1 => 'fp\\perfsuite\\http\\boot',
        2 => 'fp\\perfsuite\\http\\register',
        3 => 'fp\\perfsuite\\http\\permissioncheck',
        4 => 'fp\\perfsuite\\http\\logstail',
        5 => 'fp\\perfsuite\\http\\debugtoggle',
        6 => 'fp\\perfsuite\\http\\presetapply',
        7 => 'fp\\perfsuite\\http\\presetrollback',
        8 => 'fp\\perfsuite\\http\\dbcleanup',
        9 => 'fp\\perfsuite\\http\\score',
        10 => 'fp\\perfsuite\\http\\progress',
        11 => 'fp\\perfsuite\\http\\sanitizecleanupscope',
      ),
      3 => 
      array (
      ),
    ),
    '/workspace/fp-performance-suite/src/Contracts/CacheInterface.php' => 
    array (
      0 => 'e585e2576b2b4e02a9e424e44c40bbe24e6599d0',
      1 => 
      array (
        0 => 'fp\\perfsuite\\contracts\\cacheinterface',
      ),
      2 => 
      array (
        0 => 'fp\\perfsuite\\contracts\\isenabled',
        1 => 'fp\\perfsuite\\contracts\\settings',
        2 => 'fp\\perfsuite\\contracts\\update',
        3 => 'fp\\perfsuite\\contracts\\clear',
        4 => 'fp\\perfsuite\\contracts\\status',
      ),
      3 => 
      array (
      ),
    ),
    '/workspace/fp-performance-suite/src/Contracts/OptimizerInterface.php' => 
    array (
      0 => '4ca39f50de90f6181f954c53e43d5fb9da248b4a',
      1 => 
      array (
        0 => 'fp\\perfsuite\\contracts\\optimizerinterface',
      ),
      2 => 
      array (
        0 => 'fp\\perfsuite\\contracts\\register',
        1 => 'fp\\perfsuite\\contracts\\settings',
        2 => 'fp\\perfsuite\\contracts\\update',
        3 => 'fp\\perfsuite\\contracts\\status',
      ),
      3 => 
      array (
      ),
    ),
    '/workspace/fp-performance-suite/src/Contracts/LoggerInterface.php' => 
    array (
      0 => '78adf06b85c3afb52d49a8f71a426f5ddabd7f7f',
      1 => 
      array (
        0 => 'fp\\perfsuite\\contracts\\loggerinterface',
      ),
      2 => 
      array (
        0 => 'fp\\perfsuite\\contracts\\error',
        1 => 'fp\\perfsuite\\contracts\\warning',
        2 => 'fp\\perfsuite\\contracts\\info',
        3 => 'fp\\perfsuite\\contracts\\debug',
      ),
      3 => 
      array (
      ),
    ),
    '/workspace/fp-performance-suite/src/Contracts/SettingsRepositoryInterface.php' => 
    array (
      0 => '57464896877a3f6d45e012b4d0b6d904c52dbc2f',
      1 => 
      array (
        0 => 'fp\\perfsuite\\contracts\\settingsrepositoryinterface',
      ),
      2 => 
      array (
        0 => 'fp\\perfsuite\\contracts\\get',
        1 => 'fp\\perfsuite\\contracts\\set',
        2 => 'fp\\perfsuite\\contracts\\has',
        3 => 'fp\\perfsuite\\contracts\\delete',
        4 => 'fp\\perfsuite\\contracts\\all',
      ),
      3 => 
      array (
      ),
    ),
    '/workspace/fp-performance-suite/src/Admin/Pages/Media.php' => 
    array (
      0 => '53317d5614e1a40c70ecd5fea74dd847503f080e',
      1 => 
      array (
        0 => 'fp\\perfsuite\\admin\\pages\\media',
      ),
      2 => 
      array (
        0 => 'fp\\perfsuite\\admin\\pages\\slug',
        1 => 'fp\\perfsuite\\admin\\pages\\title',
        2 => 'fp\\perfsuite\\admin\\pages\\capability',
        3 => 'fp\\perfsuite\\admin\\pages\\view',
        4 => 'fp\\perfsuite\\admin\\pages\\data',
        5 => 'fp\\perfsuite\\admin\\pages\\content',
      ),
      3 => 
      array (
      ),
    ),
    '/workspace/fp-performance-suite/src/Admin/Pages/Presets.php' => 
    array (
      0 => '6fabf478f97a14e03c88a50e70d39174cab3a0c6',
      1 => 
      array (
        0 => 'fp\\perfsuite\\admin\\pages\\presets',
      ),
      2 => 
      array (
        0 => 'fp\\perfsuite\\admin\\pages\\slug',
        1 => 'fp\\perfsuite\\admin\\pages\\title',
        2 => 'fp\\perfsuite\\admin\\pages\\capability',
        3 => 'fp\\perfsuite\\admin\\pages\\view',
        4 => 'fp\\perfsuite\\admin\\pages\\data',
        5 => 'fp\\perfsuite\\admin\\pages\\content',
      ),
      3 => 
      array (
      ),
    ),
    '/workspace/fp-performance-suite/src/Admin/Pages/Performance.php' => 
    array (
      0 => '21d57990aa9f320a2020e1dfca1caa4c22715fdd',
      1 => 
      array (
        0 => 'fp\\perfsuite\\admin\\pages\\performance',
      ),
      2 => 
      array (
        0 => 'fp\\perfsuite\\admin\\pages\\slug',
        1 => 'fp\\perfsuite\\admin\\pages\\title',
        2 => 'fp\\perfsuite\\admin\\pages\\capability',
        3 => 'fp\\perfsuite\\admin\\pages\\view',
        4 => 'fp\\perfsuite\\admin\\pages\\data',
        5 => 'fp\\perfsuite\\admin\\pages\\content',
      ),
      3 => 
      array (
      ),
    ),
    '/workspace/fp-performance-suite/src/Admin/Pages/Cache.php' => 
    array (
      0 => '305002d34d0fdf6a2a002a7a625a6e418406044d',
      1 => 
      array (
        0 => 'fp\\perfsuite\\admin\\pages\\cache',
      ),
      2 => 
      array (
        0 => 'fp\\perfsuite\\admin\\pages\\slug',
        1 => 'fp\\perfsuite\\admin\\pages\\title',
        2 => 'fp\\perfsuite\\admin\\pages\\capability',
        3 => 'fp\\perfsuite\\admin\\pages\\view',
        4 => 'fp\\perfsuite\\admin\\pages\\data',
        5 => 'fp\\perfsuite\\admin\\pages\\content',
      ),
      3 => 
      array (
      ),
    ),
    '/workspace/fp-performance-suite/src/Admin/Pages/Logs.php' => 
    array (
      0 => '112fe7a515cca4999fb6769b9463d0c42779a8a8',
      1 => 
      array (
        0 => 'fp\\perfsuite\\admin\\pages\\logs',
      ),
      2 => 
      array (
        0 => 'fp\\perfsuite\\admin\\pages\\slug',
        1 => 'fp\\perfsuite\\admin\\pages\\title',
        2 => 'fp\\perfsuite\\admin\\pages\\capability',
        3 => 'fp\\perfsuite\\admin\\pages\\view',
        4 => 'fp\\perfsuite\\admin\\pages\\data',
        5 => 'fp\\perfsuite\\admin\\pages\\content',
      ),
      3 => 
      array (
      ),
    ),
    '/workspace/fp-performance-suite/src/Admin/Pages/Database.php' => 
    array (
      0 => 'be07563122943d2eed54e4c9c44a34261f40bff6',
      1 => 
      array (
        0 => 'fp\\perfsuite\\admin\\pages\\database',
      ),
      2 => 
      array (
        0 => 'fp\\perfsuite\\admin\\pages\\slug',
        1 => 'fp\\perfsuite\\admin\\pages\\title',
        2 => 'fp\\perfsuite\\admin\\pages\\capability',
        3 => 'fp\\perfsuite\\admin\\pages\\view',
        4 => 'fp\\perfsuite\\admin\\pages\\data',
        5 => 'fp\\perfsuite\\admin\\pages\\content',
      ),
      3 => 
      array (
      ),
    ),
    '/workspace/fp-performance-suite/src/Admin/Pages/Advanced.php' => 
    array (
      0 => '39c81c2a63d836f124473193a6434c4101566daf',
      1 => 
      array (
        0 => 'fp\\perfsuite\\admin\\pages\\advanced',
      ),
      2 => 
      array (
        0 => 'fp\\perfsuite\\admin\\pages\\__construct',
        1 => 'fp\\perfsuite\\admin\\pages\\slug',
        2 => 'fp\\perfsuite\\admin\\pages\\title',
        3 => 'fp\\perfsuite\\admin\\pages\\capability',
        4 => 'fp\\perfsuite\\admin\\pages\\view',
        5 => 'fp\\perfsuite\\admin\\pages\\data',
        6 => 'fp\\perfsuite\\admin\\pages\\content',
        7 => 'fp\\perfsuite\\admin\\pages\\rendercriticalcsssection',
        8 => 'fp\\perfsuite\\admin\\pages\\rendercdnsection',
        9 => 'fp\\perfsuite\\admin\\pages\\rendermonitoringsection',
        10 => 'fp\\perfsuite\\admin\\pages\\renderreportssection',
        11 => 'fp\\perfsuite\\admin\\pages\\handlesave',
      ),
      3 => 
      array (
      ),
    ),
    '/workspace/fp-performance-suite/src/Admin/Pages/Tools.php' => 
    array (
      0 => 'f28432bf97f642084381c49686fad2a18af8d9f7',
      1 => 
      array (
        0 => 'fp\\perfsuite\\admin\\pages\\tools',
      ),
      2 => 
      array (
        0 => 'fp\\perfsuite\\admin\\pages\\slug',
        1 => 'fp\\perfsuite\\admin\\pages\\title',
        2 => 'fp\\perfsuite\\admin\\pages\\capability',
        3 => 'fp\\perfsuite\\admin\\pages\\view',
        4 => 'fp\\perfsuite\\admin\\pages\\data',
        5 => 'fp\\perfsuite\\admin\\pages\\content',
        6 => 'fp\\perfsuite\\admin\\pages\\normalizebrowsercacheimport',
        7 => 'fp\\perfsuite\\admin\\pages\\normalizepagecacheimport',
        8 => 'fp\\perfsuite\\admin\\pages\\normalizewebpimport',
        9 => 'fp\\perfsuite\\admin\\pages\\normalizeassetsettingsimport',
        10 => 'fp\\perfsuite\\admin\\pages\\resolveboolean',
        11 => 'fp\\perfsuite\\admin\\pages\\parseinteger',
        12 => 'fp\\perfsuite\\admin\\pages\\interpretboolean',
      ),
      3 => 
      array (
      ),
    ),
    '/workspace/fp-performance-suite/src/Admin/Pages/AbstractPage.php' => 
    array (
      0 => '52f4c40ea9dd3e1da6469c56041f033f6288b7c0',
      1 => 
      array (
        0 => 'fp\\perfsuite\\admin\\pages\\abstractpage',
      ),
      2 => 
      array (
        0 => 'fp\\perfsuite\\admin\\pages\\__construct',
        1 => 'fp\\perfsuite\\admin\\pages\\slug',
        2 => 'fp\\perfsuite\\admin\\pages\\title',
        3 => 'fp\\perfsuite\\admin\\pages\\capability',
        4 => 'fp\\perfsuite\\admin\\pages\\view',
        5 => 'fp\\perfsuite\\admin\\pages\\content',
        6 => 'fp\\perfsuite\\admin\\pages\\render',
        7 => 'fp\\perfsuite\\admin\\pages\\data',
        8 => 'fp\\perfsuite\\admin\\pages\\requiredcapability',
      ),
      3 => 
      array (
      ),
    ),
    '/workspace/fp-performance-suite/src/Admin/Pages/Dashboard.php' => 
    array (
      0 => 'a042fdf0021e521722363fb452b257f59a5fb540',
      1 => 
      array (
        0 => 'fp\\perfsuite\\admin\\pages\\dashboard',
      ),
      2 => 
      array (
        0 => 'fp\\perfsuite\\admin\\pages\\__construct',
        1 => 'fp\\perfsuite\\admin\\pages\\slug',
        2 => 'fp\\perfsuite\\admin\\pages\\title',
        3 => 'fp\\perfsuite\\admin\\pages\\capability',
        4 => 'fp\\perfsuite\\admin\\pages\\view',
        5 => 'fp\\perfsuite\\admin\\pages\\data',
        6 => 'fp\\perfsuite\\admin\\pages\\content',
        7 => 'fp\\perfsuite\\admin\\pages\\exportcsv',
      ),
      3 => 
      array (
      ),
    ),
    '/workspace/fp-performance-suite/src/Admin/Pages/Assets.php' => 
    array (
      0 => 'afd86d53b4ab6c2774e1e613bb7631df57e0c11f',
      1 => 
      array (
        0 => 'fp\\perfsuite\\admin\\pages\\assets',
      ),
      2 => 
      array (
        0 => 'fp\\perfsuite\\admin\\pages\\slug',
        1 => 'fp\\perfsuite\\admin\\pages\\title',
        2 => 'fp\\perfsuite\\admin\\pages\\capability',
        3 => 'fp\\perfsuite\\admin\\pages\\view',
        4 => 'fp\\perfsuite\\admin\\pages\\data',
        5 => 'fp\\perfsuite\\admin\\pages\\content',
      ),
      3 => 
      array (
      ),
    ),
    '/workspace/fp-performance-suite/src/Admin/Pages/Settings.php' => 
    array (
      0 => '1e34dbee6e8689b09378d9ee9396d19e87296c91',
      1 => 
      array (
        0 => 'fp\\perfsuite\\admin\\pages\\settings',
      ),
      2 => 
      array (
        0 => 'fp\\perfsuite\\admin\\pages\\slug',
        1 => 'fp\\perfsuite\\admin\\pages\\title',
        2 => 'fp\\perfsuite\\admin\\pages\\capability',
        3 => 'fp\\perfsuite\\admin\\pages\\view',
        4 => 'fp\\perfsuite\\admin\\pages\\data',
        5 => 'fp\\perfsuite\\admin\\pages\\content',
      ),
      3 => 
      array (
      ),
    ),
    '/workspace/fp-performance-suite/src/Admin/Menu.php' => 
    array (
      0 => '80e414b3c848ecfeea657f7952d60bad10b8803d',
      1 => 
      array (
        0 => 'fp\\perfsuite\\admin\\menu',
      ),
      2 => 
      array (
        0 => 'fp\\perfsuite\\admin\\__construct',
        1 => 'fp\\perfsuite\\admin\\boot',
        2 => 'fp\\perfsuite\\admin\\register',
        3 => 'fp\\perfsuite\\admin\\pages',
      ),
      3 => 
      array (
      ),
    ),
    '/workspace/fp-performance-suite/src/Admin/Assets.php' => 
    array (
      0 => '7c4739dc911a9f3c26dd7f71386a09db86c7a4a9',
      1 => 
      array (
        0 => 'fp\\perfsuite\\admin\\assets',
      ),
      2 => 
      array (
        0 => 'fp\\perfsuite\\admin\\boot',
        1 => 'fp\\perfsuite\\admin\\enqueue',
      ),
      3 => 
      array (
      ),
    ),
    '/workspace/fp-performance-suite/src/Health/HealthCheck.php' => 
    array (
      0 => '61ccd3d4b25f144f49b4f094998e29f112f6ac8e',
      1 => 
      array (
        0 => 'fp\\perfsuite\\health\\healthcheck',
      ),
      2 => 
      array (
        0 => 'fp\\perfsuite\\health\\register',
        1 => 'fp\\perfsuite\\health\\addtests',
        2 => 'fp\\perfsuite\\health\\testpagecache',
        3 => 'fp\\perfsuite\\health\\testwebpcoverage',
        4 => 'fp\\perfsuite\\health\\testdatabasehealth',
        5 => 'fp\\perfsuite\\health\\testassetoptimization',
        6 => 'fp\\perfsuite\\health\\adddebuginfo',
        7 => 'fp\\perfsuite\\health\\errorresult',
      ),
      3 => 
      array (
      ),
    ),
    '/workspace/fp-performance-suite/src/Enums/LogLevel.php' => 
    array (
      0 => '1b1edc45220f66105c1b537aa793768569324d67',
      1 => 
      array (
        0 => 'fp\\perfsuite\\enums\\loglevel',
      ),
      2 => 
      array (
        0 => 'fp\\perfsuite\\enums\\priority',
        1 => 'fp\\perfsuite\\enums\\color',
        2 => 'fp\\perfsuite\\enums\\emoji',
        3 => 'fp\\perfsuite\\enums\\shouldlog',
        4 => 'fp\\perfsuite\\enums\\all',
      ),
      3 => 
      array (
      ),
    ),
    '/workspace/fp-performance-suite/src/Enums/HostingPreset.php' => 
    array (
      0 => 'b680a31b4fb67eee80a41559958217ed1f691f55',
      1 => 
      array (
        0 => 'fp\\perfsuite\\enums\\hostingpreset',
      ),
      2 => 
      array (
        0 => 'fp\\perfsuite\\enums\\label',
        1 => 'fp\\perfsuite\\enums\\description',
        2 => 'fp\\perfsuite\\enums\\config',
        3 => 'fp\\perfsuite\\enums\\all',
        4 => 'fp\\perfsuite\\enums\\fromstring',
        5 => 'fp\\perfsuite\\enums\\isrecommended',
      ),
      3 => 
      array (
      ),
    ),
    '/workspace/fp-performance-suite/src/Enums/CdnProvider.php' => 
    array (
      0 => '5e488e48040951525c212a39e09f755099c14905',
      1 => 
      array (
        0 => 'fp\\perfsuite\\enums\\cdnprovider',
      ),
      2 => 
      array (
        0 => 'fp\\perfsuite\\enums\\name',
        1 => 'fp\\perfsuite\\enums\\setupurl',
        2 => 'fp\\perfsuite\\enums\\supportsapipurge',
        3 => 'fp\\perfsuite\\enums\\requiresapikey',
        4 => 'fp\\perfsuite\\enums\\requiredfields',
        5 => 'fp\\perfsuite\\enums\\icon',
      ),
      3 => 
      array (
      ),
    ),
    '/workspace/fp-performance-suite/src/Enums/CleanupTask.php' => 
    array (
      0 => 'befd262073dd76c396a4dc663fc990b854d5400d',
      1 => 
      array (
        0 => 'fp\\perfsuite\\enums\\cleanuptask',
      ),
      2 => 
      array (
        0 => 'fp\\perfsuite\\enums\\label',
        1 => 'fp\\perfsuite\\enums\\description',
        2 => 'fp\\perfsuite\\enums\\risklevel',
        3 => 'fp\\perfsuite\\enums\\issafe',
        4 => 'fp\\perfsuite\\enums\\recommendedforscheduled',
        5 => 'fp\\perfsuite\\enums\\all',
        6 => 'fp\\perfsuite\\enums\\byrisklevel',
      ),
      3 => 
      array (
      ),
    ),
    '/workspace/fp-performance-suite/src/Enums/CacheType.php' => 
    array (
      0 => '55f4e1ff6bb89d295d9c1227f36f943056a8d913',
      1 => 
      array (
        0 => 'fp\\perfsuite\\enums\\cachetype',
      ),
      2 => 
      array (
        0 => 'fp\\perfsuite\\enums\\label',
        1 => 'fp\\perfsuite\\enums\\description',
        2 => 'fp\\perfsuite\\enums\\icon',
      ),
      3 => 
      array (
      ),
    ),
    '/workspace/fp-performance-suite/src/Repositories/WpOptionsRepository.php' => 
    array (
      0 => '7b6258a4eefa18970274c2582aea2353ac683a88',
      1 => 
      array (
        0 => 'fp\\perfsuite\\repositories\\wpoptionsrepository',
      ),
      2 => 
      array (
        0 => 'fp\\perfsuite\\repositories\\__construct',
        1 => 'fp\\perfsuite\\repositories\\get',
        2 => 'fp\\perfsuite\\repositories\\set',
        3 => 'fp\\perfsuite\\repositories\\has',
        4 => 'fp\\perfsuite\\repositories\\delete',
        5 => 'fp\\perfsuite\\repositories\\all',
        6 => 'fp\\perfsuite\\repositories\\bulkset',
        7 => 'fp\\perfsuite\\repositories\\getbypattern',
      ),
      3 => 
      array (
      ),
    ),
    '/workspace/fp-performance-suite/src/Repositories/TransientRepository.php' => 
    array (
      0 => '9abe0d57528d0f87b222850e69527d7995167916',
      1 => 
      array (
        0 => 'fp\\perfsuite\\repositories\\transientrepository',
      ),
      2 => 
      array (
        0 => 'fp\\perfsuite\\repositories\\__construct',
        1 => 'fp\\perfsuite\\repositories\\get',
        2 => 'fp\\perfsuite\\repositories\\set',
        3 => 'fp\\perfsuite\\repositories\\has',
        4 => 'fp\\perfsuite\\repositories\\delete',
        5 => 'fp\\perfsuite\\repositories\\remember',
        6 => 'fp\\perfsuite\\repositories\\increment',
        7 => 'fp\\perfsuite\\repositories\\decrement',
        8 => 'fp\\perfsuite\\repositories\\clear',
      ),
      3 => 
      array (
      ),
    ),
    '/workspace/fp-performance-suite/src/Utils/Htaccess.php' => 
    array (
      0 => '604edef03af37e77dd1f9ff753005d99e17ca30d',
      1 => 
      array (
        0 => 'fp\\perfsuite\\utils\\htaccess',
      ),
      2 => 
      array (
        0 => 'fp\\perfsuite\\utils\\__construct',
        1 => 'fp\\perfsuite\\utils\\issupported',
        2 => 'fp\\perfsuite\\utils\\backup',
        3 => 'fp\\perfsuite\\utils\\prunebackups',
        4 => 'fp\\perfsuite\\utils\\injectrules',
        5 => 'fp\\perfsuite\\utils\\removesection',
        6 => 'fp\\perfsuite\\utils\\hassection',
      ),
      3 => 
      array (
      ),
    ),
    '/workspace/fp-performance-suite/src/Utils/ArrayHelper.php' => 
    array (
      0 => '706dc965bdd3fcb7d4dcdd55ef70b4d1d2980c58',
      1 => 
      array (
        0 => 'fp\\perfsuite\\utils\\arrayhelper',
      ),
      2 => 
      array (
        0 => 'fp\\perfsuite\\utils\\get',
        1 => 'fp\\perfsuite\\utils\\set',
        2 => 'fp\\perfsuite\\utils\\only',
        3 => 'fp\\perfsuite\\utils\\except',
        4 => 'fp\\perfsuite\\utils\\pluck',
        5 => 'fp\\perfsuite\\utils\\flatten',
        6 => 'fp\\perfsuite\\utils\\groupby',
        7 => 'fp\\perfsuite\\utils\\isassoc',
        8 => 'fp\\perfsuite\\utils\\mergerecursive',
        9 => 'fp\\perfsuite\\utils\\sortby',
      ),
      3 => 
      array (
      ),
    ),
    '/workspace/fp-performance-suite/src/Utils/Benchmark.php' => 
    array (
      0 => '0482bc36a8c1121600457108952adb6a1fc3781b',
      1 => 
      array (
        0 => 'fp\\perfsuite\\utils\\benchmark',
      ),
      2 => 
      array (
        0 => 'fp\\perfsuite\\utils\\start',
        1 => 'fp\\perfsuite\\utils\\stop',
        2 => 'fp\\perfsuite\\utils\\measure',
        3 => 'fp\\perfsuite\\utils\\get',
        4 => 'fp\\perfsuite\\utils\\getall',
        5 => 'fp\\perfsuite\\utils\\increment',
        6 => 'fp\\perfsuite\\utils\\getcounter',
        7 => 'fp\\perfsuite\\utils\\reset',
        8 => 'fp\\perfsuite\\utils\\formatduration',
        9 => 'fp\\perfsuite\\utils\\formatmemory',
        10 => 'fp\\perfsuite\\utils\\report',
        11 => 'fp\\perfsuite\\utils\\logreport',
      ),
      3 => 
      array (
      ),
    ),
    '/workspace/fp-performance-suite/src/Utils/Fs.php' => 
    array (
      0 => '9d570d09438a10f75b9751ef7d64c40dd3f53a08',
      1 => 
      array (
        0 => 'fp\\perfsuite\\utils\\fs',
      ),
      2 => 
      array (
        0 => 'fp\\perfsuite\\utils\\ensure',
        1 => 'fp\\perfsuite\\utils\\putcontents',
        2 => 'fp\\perfsuite\\utils\\getcontents',
        3 => 'fp\\perfsuite\\utils\\exists',
        4 => 'fp\\perfsuite\\utils\\mkdir',
        5 => 'fp\\perfsuite\\utils\\delete',
        6 => 'fp\\perfsuite\\utils\\copy',
      ),
      3 => 
      array (
      ),
    ),
    '/workspace/fp-performance-suite/src/Utils/Semaphore.php' => 
    array (
      0 => 'f19228c98b8786da3b995b999a9a727275b932b4',
      1 => 
      array (
        0 => 'fp\\perfsuite\\utils\\semaphore',
      ),
      2 => 
      array (
        0 => 'fp\\perfsuite\\utils\\describe',
      ),
      3 => 
      array (
      ),
    ),
    '/workspace/fp-performance-suite/src/Utils/RateLimiter.php' => 
    array (
      0 => '713d63ef9a0b6ddb9ccf880709b6d3df279a2450',
      1 => 
      array (
        0 => 'fp\\perfsuite\\utils\\ratelimiter',
      ),
      2 => 
      array (
        0 => 'fp\\perfsuite\\utils\\isallowed',
        1 => 'fp\\perfsuite\\utils\\reset',
        2 => 'fp\\perfsuite\\utils\\getstatus',
        3 => 'fp\\perfsuite\\utils\\clearall',
      ),
      3 => 
      array (
      ),
    ),
    '/workspace/fp-performance-suite/src/Utils/Env.php' => 
    array (
      0 => '2887eed4c4edd5d9918e9aceb83437514d46906b',
      1 => 
      array (
        0 => 'fp\\perfsuite\\utils\\env',
      ),
      2 => 
      array (
        0 => 'fp\\perfsuite\\utils\\ismultisite',
        1 => 'fp\\perfsuite\\utils\\iscli',
        2 => 'fp\\perfsuite\\utils\\serversoftware',
        3 => 'fp\\perfsuite\\utils\\isapache',
      ),
      3 => 
      array (
      ),
    ),
    '/workspace/fp-performance-suite/src/Utils/Capabilities.php' => 
    array (
      0 => '61451de22194e3158b08ccca0df96754e285fabd',
      1 => 
      array (
        0 => 'fp\\perfsuite\\utils\\capabilities',
      ),
      2 => 
      array (
        0 => 'fp\\perfsuite\\utils\\required',
      ),
      3 => 
      array (
      ),
    ),
    '/workspace/fp-performance-suite/src/Utils/Logger.php' => 
    array (
      0 => '526c058f44ac01dafabdff3457fec4805b551d46',
      1 => 
      array (
        0 => 'fp\\perfsuite\\utils\\logger',
      ),
      2 => 
      array (
        0 => 'fp\\perfsuite\\utils\\error',
        1 => 'fp\\perfsuite\\utils\\warning',
        2 => 'fp\\perfsuite\\utils\\info',
        3 => 'fp\\perfsuite\\utils\\debug',
        4 => 'fp\\perfsuite\\utils\\write',
        5 => 'fp\\perfsuite\\utils\\shouldlog',
        6 => 'fp\\perfsuite\\utils\\setlevel',
      ),
      3 => 
      array (
      ),
    ),
    '/workspace/fp-performance-suite/src/ServiceContainer.php' => 
    array (
      0 => '332e47ddf8ed55bd69405c2085f8ba02d337049b',
      1 => 
      array (
        0 => 'fp\\perfsuite\\servicecontainer',
      ),
      2 => 
      array (
        0 => 'fp\\perfsuite\\set',
        1 => 'fp\\perfsuite\\get',
        2 => 'fp\\perfsuite\\has',
        3 => 'fp\\perfsuite\\getcachedsettings',
        4 => 'fp\\perfsuite\\invalidatesettingscache',
        5 => 'fp\\perfsuite\\clearsettingscache',
      ),
      3 => 
      array (
      ),
    ),
    '/workspace/fp-performance-suite/src/Plugin.php' => 
    array (
      0 => 'ed0a3b215bf5aacc4f319699cf5e248f00b1e843',
      1 => 
      array (
        0 => 'fp\\perfsuite\\plugin',
      ),
      2 => 
      array (
        0 => 'fp\\perfsuite\\init',
        1 => 'fp\\perfsuite\\registerclicommands',
        2 => 'fp\\perfsuite\\register',
        3 => 'fp\\perfsuite\\container',
        4 => 'fp\\perfsuite\\onactivate',
        5 => 'fp\\perfsuite\\ondeactivate',
      ),
      3 => 
      array (
        0 => 'FP_PERF_SUITE_FILE',
      ),
    ),
  ),
));