#!/bin/bash

##
## Example: WP-CLI Automation Scripts
##
## Collection of useful automation scripts using FP Performance WP-CLI commands
##

# Example 1: Daily Maintenance Script
# Add to cron: 0 2 * * * /path/to/daily-maintenance.sh

echo "=== FP Performance Daily Maintenance ==="
echo "Started: $(date)"

# Clear page cache
echo "Clearing page cache..."
wp fp-performance cache clear

# Database cleanup (dry-run first)
echo "Database cleanup (dry-run)..."
wp fp-performance db cleanup --dry-run --scope=revisions,auto_drafts,expired_transients

# If dry-run looks good, actually clean
read -p "Proceed with actual cleanup? (y/n) " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    echo "Running actual cleanup..."
    wp fp-performance db cleanup --scope=revisions,auto_drafts,expired_transients
fi

# Show final score
echo "Current performance score:"
wp fp-performance score

echo "Completed: $(date)"

# Example 2: Weekly WebP Conversion
# Add to cron: 0 3 * * 0 /path/to/weekly-webp.sh

echo "=== Weekly WebP Conversion ==="
wp fp-performance webp status
wp fp-performance webp convert --limit=100
echo "WebP conversion complete!"

# Example 3: Monthly Deep Clean
# Add to cron: 0 4 1 * * /path/to/monthly-deep-clean.sh

echo "=== Monthly Deep Database Clean ==="
wp fp-performance db cleanup \
    --scope=revisions,auto_drafts,trash_posts,spam_comments,expired_transients,orphan_postmeta,optimize_tables
echo "Deep clean complete!"

# Example 4: Pre-Deploy Performance Baseline
# Run before deploying changes

echo "=== Pre-Deploy Performance Baseline ==="
TIMESTAMP=$(date +%Y%m%d_%H%M%S)
REPORT_FILE="performance_baseline_${TIMESTAMP}.txt"

echo "Performance Score Before Deploy" > $REPORT_FILE
wp fp-performance score >> $REPORT_FILE
echo "" >> $REPORT_FILE
echo "Cache Status:" >> $REPORT_FILE
wp fp-performance cache status >> $REPORT_FILE
echo "" >> $REPORT_FILE
echo "Database Status:" >> $REPORT_FILE
wp fp-performance db status >> $REPORT_FILE
echo "" >> $REPORT_FILE
echo "WebP Status:" >> $REPORT_FILE
wp fp-performance webp status >> $REPORT_FILE

echo "Baseline saved to: $REPORT_FILE"

# Example 5: Performance Monitoring Report Generator

echo "=== Generating Performance Report ==="

# Get current stats
SCORE=$(wp fp-performance score --format=json 2>/dev/null | jq -r '.total' 2>/dev/null || echo "N/A")
CACHE_FILES=$(wp fp-performance cache status --format=json 2>/dev/null | jq -r '.files' 2>/dev/null || echo "N/A")

# Generate HTML report
cat > performance_report.html << HTML
<!DOCTYPE html>
<html>
<head>
    <title>Performance Report - $(date)</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; }
        .metric { background: #f0f0f0; padding: 20px; margin: 10px 0; border-radius: 8px; }
        .score { font-size: 48px; font-weight: bold; color: #2271b1; }
    </style>
</head>
<body>
    <h1>Performance Report</h1>
    <p>Generated: $(date)</p>
    
    <div class="metric">
        <h2>Performance Score</h2>
        <div class="score">$SCORE</div>
    </div>
    
    <div class="metric">
        <h2>Cached Pages</h2>
        <p>$CACHE_FILES files</p>
    </div>
</body>
</html>
HTML

echo "Report generated: performance_report.html"

# Example 6: CI/CD Integration

echo "=== CI/CD Performance Checks ==="

# Get score
SCORE=$(wp fp-performance score --format=json 2>/dev/null | jq -r '.total' 2>/dev/null || echo "0")

# Fail build if score too low
if [ "$SCORE" -lt 60 ]; then
    echo "❌ Performance score too low: $SCORE (minimum: 60)"
    exit 1
else
    echo "✅ Performance score acceptable: $SCORE"
fi

# Clear cache before deployment
wp fp-performance cache clear

echo "CI/CD checks passed!"

# Example 7: Multi-site Batch Operations

echo "=== Multi-site Batch Operations ==="

# Get all sites
SITES=$(wp site list --field=url)

# Run operations on each site
for SITE in $SITES; do
    echo "Processing: $SITE"
    wp fp-performance cache clear --url=$SITE
    wp fp-performance score --url=$SITE
done

echo "Batch operations complete!"
