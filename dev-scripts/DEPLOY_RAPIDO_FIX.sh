#!/bin/bash
# 🚀 Deploy Rapido - Fix DatabaseReportService

echo "🚀 Upload file corretto sul server..."

# Sostituisci con le tue credenziali FTP/SSH
SERVER="user@server.com"
SERVER_PATH="/homepages/20/d4299220163/htdocs/clickandbuilds/FPDevelopmentEnvironment/wp-content/plugins/FP-Performance"

# Upload solo il file corretto
scp fp-performance-suite/src/Services/DB/DatabaseReportService.php \
    $SERVER:$SERVER_PATH/src/Services/DB/DatabaseReportService.php

echo "✅ File caricato!"
echo "🔄 Verifica sul server..."

# Verifica che il fix sia applicato
ssh $SERVER "grep -n '?array' $SERVER_PATH/src/Services/DB/DatabaseReportService.php | head -2"

echo ""
echo "✅ Se vedi '?array' nelle linee 244 e 256, il fix è applicato!"

