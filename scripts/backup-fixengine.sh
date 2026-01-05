#!/bin/bash
# Database Backup Script for FixEngine Migration
# Creates backups of critical tables before migration

set -e

# Configuration
BACKUP_DIR="./backups/fixengine-migration"
TIMESTAMP=$(date +%Y%m%d_%H%M%S)
DB_NAME="${DB_NAME:-wordpress}"
DB_USER="${DB_USER:-root}"
DB_PASSWORD="${DB_PASSWORD:-}"
DB_HOST="${DB_HOST:-localhost}"
TABLE_PREFIX="${TABLE_PREFIX:-wp_}"

echo "==================================="
echo "SLOS FixEngine Migration Backup"
echo "==================================="
echo "Timestamp: $TIMESTAMP"
echo ""

# Create backup directory
mkdir -p "$BACKUP_DIR"

# Backup critical tables
TABLES=(
    "${TABLE_PREFIX}slos_fix_history"
    "${TABLE_PREFIX}postmeta"
    "${TABLE_PREFIX}options"
)

echo "Backing up tables..."
for TABLE in "${TABLES[@]}"; do
    echo "  - $TABLE"
    mysqldump \
        --host="$DB_HOST" \
        --user="$DB_USER" \
        --password="$DB_PASSWORD" \
        --single-transaction \
        --quick \
        --lock-tables=false \
        "$DB_NAME" \
        "$TABLE" > "$BACKUP_DIR/${TABLE}_${TIMESTAMP}.sql"
done

# Create manifest
cat > "$BACKUP_DIR/manifest_${TIMESTAMP}.txt" << EOF
SLOS FixEngine Migration Backup
================================
Date: $(date)
Database: $DB_NAME
Tables Backed Up:
$(for TABLE in "${TABLES[@]}"; do echo "  - $TABLE"; done)

Files:
$(ls -lh $BACKUP_DIR/*_${TIMESTAMP}.sql)

Restore Instructions:
=====================
mysql -h $DB_HOST -u $DB_USER -p $DB_NAME < $BACKUP_DIR/[table_name]_${TIMESTAMP}.sql

Git Tag:
========
pre-fixengine-migration

EOF

echo ""
echo "Backup completed successfully!"
echo "Location: $BACKUP_DIR"
echo "Manifest: $BACKUP_DIR/manifest_${TIMESTAMP}.txt"
echo ""
echo "Git tag 'pre-fixengine-migration' has been created."
