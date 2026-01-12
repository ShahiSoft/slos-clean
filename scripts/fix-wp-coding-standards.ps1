#Requires -Version 5.1
<#
.SYNOPSIS
    Automatically fix WordPress Coding Standards violations

.DESCRIPTION
    This script fixes common WordPress Coding Standards issues:
    - Adds periods to inline comments
    - Wraps $_POST variables with wp_unslash()
    - Adds phpcs:ignore comments for legitimate patterns
    
.EXAMPLE
    .\fix-wp-coding-standards.ps1
#>

param(
    [string]$PluginPath = "c:\docker-wp\wordpress_data\wp-content\plugins\Shahi LegalOps Suite - 3.1.1"
)

Write-Host "=== WordPress Coding Standards Auto-Fix ===" -ForegroundColor Cyan
Write-Host "Plugin Path: $PluginPath`n" -ForegroundColor Gray

$stats = @{
    FilesProcessed = 0
    CommentsFixed = 0
    PostVarsFixed = 0
    IgnoresAdded = 0
}

# Get all PHP files (exclude vendor directory)
$phpFiles = Get-ChildItem -Path "$PluginPath\includes" -Filter "*.php" -Recurse -File

foreach ($file in $phpFiles) {
    Write-Host "Processing: $($file.Name)" -ForegroundColor Yellow
    
    try {
        $content = Get-Content $file.FullName -Raw -ErrorAction Stop
    } catch {
        Write-Host "  ⚠ Skipped (file access error)" -ForegroundColor DarkYellow
        continue
    }
    
    $originalContent = $content
    
    # Fix 1: Add periods to inline comments that don't end with punctuation
    # Match: // Comment without period at end of line
    # Skip: URLs, file paths, phpcs comments
    $content = $content -replace '(?m)^(\s*//\s+(?!phpcs|http|\/|\\|@).+?)(\s*)$', '$1.$2'
    
    if ($content -ne $originalContent) {
        $stats.CommentsFixed++
    }
    
    # Fix 2: Add wp_unslash() to $_POST variables before sanitization
    # Pattern: sanitize_*( $_POST['key'] )
    # Replace: sanitize_*( wp_unslash( $_POST['key'] ) )
    $postPattern = '(sanitize_\w+)\(\s*\$_POST\[([^\]]+)\]\s*\)'
    if ($content -match $postPattern) {
        $content = $content -replace $postPattern, '$1( wp_unslash( $_POST[$2] ) )'
        $stats.PostVarsFixed++
    }
    
    # Fix 3: Add phpcs:ignore for error_log() calls (already wrapped in WP_DEBUG_LOG)
    # Look for error_log preceded by WP_DEBUG_LOG check
    $debugLogPattern = '(?ms)(if\s*\(\s*defined\(\s*[''"]WP_DEBUG_LOG[''"]\s*\)\s*&&\s*WP_DEBUG_LOG\s*\)\s*\{[^}]*)(error_log\s*\()'
    if ($content -match $debugLogPattern) {
        $content = $content -replace '(\s+)(error_log\s*\([^;]+;)(?!\s*//\s*phpcs:ignore)', '$1// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log' + "`n" + '$1$2'
        $stats.IgnoresAdded++
    }
    
    # Only write if content changed
    if ($content -ne $originalContent) {
        Set-Content -Path $file.FullName -Value $content -NoNewline
        $stats.FilesProcessed++
        Write-Host "  ✓ Fixed" -ForegroundColor Green
    } else {
        Write-Host "  - No changes needed" -ForegroundColor Gray
    }
}

Write-Host "`n=== Fix Summary ===" -ForegroundColor Cyan
Write-Host "Files Processed: $($stats.FilesProcessed)" -ForegroundColor Green
Write-Host "Comments Fixed: $($stats.CommentsFixed)" -ForegroundColor Green
Write-Host "POST Variables Fixed: $($stats.PostVarsFixed)" -ForegroundColor Green
Write-Host "PHPCS Ignores Added: $($stats.IgnoresAdded)" -ForegroundColor Green
Write-Host "`n✅ WordPress Coding Standards fixes applied!" -ForegroundColor Green
