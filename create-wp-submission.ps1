# WordPress Plugin Submission Package Creator
# This script creates a clean ZIP package for WordPress.org submission
# Excludes development files, tests, and unnecessary documentation

$pluginDir = "c:\docker-wp\wordpress_data\wp-content\plugins\Shahi LegalOps Suite - 3.1.1"
$outputDir = "c:\docker-wp\wordpress_data\wp-content\plugins"
$zipName = "shahi-legalflowsuite-wp-submission.zip"
$tempDir = "c:\docker-wp\temp\shahi-submission"

Write-Host "Creating WordPress.org Submission Package..." -ForegroundColor Cyan
Write-Host "=============================================" -ForegroundColor Cyan
Write-Host ""

# Clean up any existing temp directory
if (Test-Path $tempDir) {
    Write-Host "Cleaning up existing temp directory..." -ForegroundColor Yellow
    Remove-Item $tempDir -Recurse -Force -ErrorAction SilentlyContinue
}

# Create temp directory
New-Item -ItemType Directory -Path $tempDir -Force | Out-Null
Write-Host "Temp directory: $tempDir" -ForegroundColor Gray
Write-Host ""

Write-Host "Copying plugin files to temp directory..." -ForegroundColor Green
Write-Host ""
Write-Host ""

# Use robocopy for efficient copying with exclusions
$excludeDirs = @(
    ".git",
    ".github",
    "dist",
    "docs",
    "kb",
    "scripts",
    "tests",
    "node_modules"
)

$excludeFiles = @(
    "*.md",
    "composer.lock",
    ".gitignore",
    ".gitattributes",
    ".editorconfig",
    "phpunit.xml",
    "phpstan.neon",
    "package.json",
    "webpack.config.js",
    "tsconfig.json"
)

# Build robocopy exclude parameters
$excludeDirParams = $excludeDirs | ForEach-Object { "/XD `"$_`"" }
$excludeFileParams = $excludeFiles | ForEach-Object { "/XF `"$_`"" }

Write-Host "Copying main plugin files..." -ForegroundColor White
$robocopyArgs = @"
"$pluginDir" "$tempDir" /E /NFL /NDL /NJH /NJS /NC /NS /NP $($excludeDirParams -join ' ') $($excludeFileParams -join ' ')
"@

Start-Process -FilePath "robocopy.exe" -ArgumentList $robocopyArgs -Wait -NoNewWindow

Write-Host "Main files copied." -ForegroundColor Green
Write-Host ""

Write-Host "Cleaning up development packages from vendor..." -ForegroundColor Green

# Remove dev vendor packages
$devPackages = @(
    "brain",
    "mockery",
    "phpunit",
    "squizlabs",
    "dealerdirect",
    "phpcsstandards",
    "wp-coding-standards",
    "wp-phpunit",
    "yoast",
    "hamcrest",
    "myclabs",
    "nikic",
    "phar-io",
    "sebastian",
    "theseer",
    "bin",
    "antecedent"
)

$vendorPath = Join-Path $tempDir "vendor"
if (Test-Path $vendorPath) {
    foreach ($package in $devPackages) {
        $packagePath = Join-Path $vendorPath $package
        if (Test-Path $packagePath) {
            Write-Host "  Removing: vendor\$package" -ForegroundColor DarkGray
            Remove-Item $packagePath -Recurse -Force -ErrorAction SilentlyContinue
        }
    }
}

Write-Host ""
Write-Host "Cleaning up test files and documentation in vendor..." -ForegroundColor Green

# Clean up test files and docs in remaining vendor packages
if (Test-Path $vendorPath) {
    Get-ChildItem -Path $vendorPath -Recurse -Directory -Include @('tests', 'test', 'Tests', 'Test', 'docs', 'examples') | 
        ForEach-Object {
            Write-Host "  Removing: $($_.FullName.Replace($vendorPath, 'vendor'))" -ForegroundColor DarkGray
            Remove-Item $_.FullName -Recurse -Force -ErrorAction SilentlyContinue
        }
    
    # Remove markdown and license files from vendor
    Get-ChildItem -Path $vendorPath -Recurse -File -Include @('*.md', 'LICENSE', 'COPYING', 'AUTHORS', 'CONTRIBUTORS', 'CHANGELOG', 'phpunit.xml*') | 
        ForEach-Object {
            Remove-Item $_.FullName -Force -ErrorAction SilentlyContinue
        }
}

Write-Host ""
Write-Host "Creating ZIP archive..." -ForegroundColor Green

# Create the ZIP file
$zipPath = Join-Path $outputDir $zipName

if (Test-Path $zipPath) {
    Remove-Item $zipPath -Force
}

# Compress the temp directory
Compress-Archive -Path "$tempDir\*" -DestinationPath $zipPath -CompressionLevel Optimal

Write-Host ""
Write-Host "Package created successfully!" -ForegroundColor Green
Write-Host "Location: $zipPath" -ForegroundColor Cyan
Write-Host ""

# Show package size
$zipSize = (Get-Item $zipPath).Length / 1MB
Write-Host "Package size: $([math]::Round($zipSize, 2)) MB" -ForegroundColor Yellow

# Clean up temp directory
Write-Host ""
Write-Host "Cleaning up temp directory..." -ForegroundColor Yellow
Remove-Item $tempDir -Recurse -Force

Write-Host ""
Write-Host "=============================================" -ForegroundColor Cyan
Write-Host "PACKAGE READY FOR WORDPRESS.ORG SUBMISSION" -ForegroundColor Green
Write-Host "=============================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "Next steps:" -ForegroundColor Yellow
Write-Host "1. Review the package contents by extracting the ZIP" -ForegroundColor White
Write-Host "2. Ensure readme.txt is properly formatted" -ForegroundColor White
Write-Host "3. Test the plugin by installing from the ZIP" -ForegroundColor White
Write-Host "4. Submit to: https://wordpress.org/plugins/developers/add/" -ForegroundColor White
Write-Host ""
