# Batch fix coding standards
$pluginDir = "c:\docker-wp\wordpress_data\wp-content\plugins\Shahi LegalOps Suite - 3.1.1"
$phpcbf = Join-Path $pluginDir "vendor\bin\phpcbf.bat"
$phpcsXml = Join-Path $pluginDir "phpcs.xml"

# Directories to process
$directories = @(
    "includes\API",
    "includes\Admin",
    "includes\Ajax",
    "includes\CLI",
    "includes\Core",
    "includes\Database",
    "includes\Helpers",
    "includes\Models",
    "includes\Modules",
    "includes\PostTypes",
    "includes\Services",
    "includes\Shortcodes",
    "includes\Widgets",
    "scripts",
    "config"
)

Write-Host "Starting batch coding standards fixes..." -ForegroundColor Cyan

foreach ($dir in $directories) {
    $fullPath = Join-Path $pluginDir $dir
    if (Test-Path $fullPath) {
        Write-Host "`nProcessing: $dir" -ForegroundColor Yellow
        try {
            $result = cmd /c "`"$phpcbf`" -d memory_limit=512M --standard=`"$phpcsXml`" `"$fullPath`" 2>&1"
            Write-Host $result
        } catch {
            Write-Host "Error processing $dir : $_" -ForegroundColor Red
        }
    } else {
        Write-Host "Directory not found: $fullPath" -ForegroundColor Red
    }
}

# Also fix main plugin file
Write-Host "`nProcessing: Main plugin file" -ForegroundColor Yellow
$mainFile = Join-Path $pluginDir "shahi-legalflowsuite.php"
if (Test-Path $mainFile) {
    $result = cmd /c "`"$phpcbf`" -d memory_limit=512M --standard=`"$phpcsXml`" `"$mainFile`" 2>&1"
    Write-Host $result
}

Write-Host "`nBatch processing complete!" -ForegroundColor Green
