# Database Backup Script for FixEngine Migration (PowerShell)
# Creates backups of critical tables before migration

param(
    [string]$BackupDir = "./backups/fixengine-migration",
    [string]$DbHost = "localhost",
    [string]$DbName = "wordpress",
    [string]$DbUser = "root",
    [string]$DbPassword = "",
    [string]$TablePrefix = "wp_"
)

$ErrorActionPreference = "Stop"

# Configuration
$Timestamp = Get-Date -Format "yyyyMMdd_HHmmss"

Write-Host "===================================" -ForegroundColor Cyan
Write-Host "SLOS FixEngine Migration Backup" -ForegroundColor Cyan
Write-Host "===================================" -ForegroundColor Cyan
Write-Host "Timestamp: $Timestamp"
Write-Host ""

# Create backup directory
New-Item -ItemType Directory -Force -Path $BackupDir | Out-Null

# Tables to backup
$Tables = @(
    "${TablePrefix}slos_fix_history",
    "${TablePrefix}postmeta",
    "${TablePrefix}options"
)

Write-Host "Backing up tables..." -ForegroundColor Yellow

foreach ($Table in $Tables) {
    Write-Host "  - $Table" -ForegroundColor Gray
    
    $OutputFile = Join-Path $BackupDir "${Table}_${Timestamp}.sql"
    
    # Build mysqldump command
    $MysqldumpArgs = @(
        "--host=$DbHost",
        "--user=$DbUser",
        "--single-transaction",
        "--quick",
        "--lock-tables=false",
        $DbName,
        $Table
    )
    
    if ($DbPassword) {
        $MysqldumpArgs = @("--password=$DbPassword") + $MysqldumpArgs
    }
    
    # Execute backup
    & mysqldump $MysqldumpArgs | Out-File -FilePath $OutputFile -Encoding UTF8
}

# Create manifest
$ManifestPath = Join-Path $BackupDir "manifest_${Timestamp}.txt"
$ManifestContent = @"
SLOS FixEngine Migration Backup
================================
Date: $(Get-Date -Format "yyyy-MM-dd HH:mm:ss")
Database: $DbName
Host: $DbHost
Tables Backed Up:
$(($Tables | ForEach-Object { "  - $_" }) -join "`n")

Files:
$(Get-ChildItem "$BackupDir\*_${Timestamp}.sql" | ForEach-Object { "  - $($_.Name) ($([math]::Round($_.Length/1KB, 2)) KB)" })

Restore Instructions:
=====================
mysql -h $DbHost -u $DbUser -p $DbName < $BackupDir\[table_name]_${Timestamp}.sql

Or in PowerShell:
Get-Content "$BackupDir\[table_name]_${Timestamp}.sql" | mysql -h $DbHost -u $DbUser -p $DbName

Git Tag:
========
pre-fixengine-migration

"@

$ManifestContent | Out-File -FilePath $ManifestPath -Encoding UTF8

Write-Host ""
Write-Host "Backup completed successfully!" -ForegroundColor Green
Write-Host "Location: $BackupDir" -ForegroundColor Green
Write-Host "Manifest: $ManifestPath" -ForegroundColor Green
Write-Host ""
Write-Host "Git tag 'pre-fixengine-migration' has been created." -ForegroundColor Cyan
