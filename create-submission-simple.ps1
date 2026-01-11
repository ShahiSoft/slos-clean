# WordPress.org Submission Package Creator - Simple & Fast
# Creates a clean ZIP for WordPress.org submission

$ErrorActionPreference = "Stop"

$source = "c:\docker-wp\wordpress_data\wp-content\plugins\Shahi LegalOps Suite - 3.1.1"
$temp = "c:\docker-wp\temp\wp-submission"
$output = "c:\docker-wp\wordpress_data\wp-content\plugins\shahi-legalflowsuite-wp-submission.zip"

Write-Host "`n==================================================" -ForegroundColor Cyan
Write-Host "WordPress.org Submission Package Creator" -ForegroundColor Cyan
Write-Host "==================================================" -ForegroundColor Cyan

# Step 1: Clean temp
Write-Host "`n[1/5] Cleaning temp directory..." -ForegroundColor Yellow
if (Test-Path $temp) { Remove-Item $temp -Recurse -Force }
if (Test-Path $output) { Remove-Item $output -Force }
New-Item -ItemType Directory -Path $temp -Force | Out-Null

# Step 2: Copy essential files
Write-Host "[2/5] Copying plugin files..." -ForegroundColor Yellow

# Copy PHP files
Get-ChildItem -Path $source -Filter "*.php" | ForEach-Object {
    Write-Host "  Copying: $($_.Name)" -ForegroundColor Gray
    Copy-Item $_.FullName -Destination $temp -Force
}

# Copy text files
Get-ChildItem -Path $source -Filter "*.txt" | ForEach-Object {
    Write-Host "  Copying: $($_.Name)" -ForegroundColor Gray
    Copy-Item $_.FullName -Destination $temp -Force
}

# Copy XML files
Get-ChildItem -Path $source -Filter "*.xml" | ForEach-Object {
    Write-Host "  Copying: $($_.Name)" -ForegroundColor Gray
    Copy-Item $_.FullName -Destination $temp -Force
}

# Copy essential directories
$dirs = @("assets", "config", "includes", "languages", "templates")
foreach ($dir in $dirs) {
    $sourcePath = Join-Path $source $dir
    if (Test-Path $sourcePath) {
        Write-Host "  Copying: $dir\" -ForegroundColor Gray
        Copy-Item -Path $sourcePath -Destination (Join-Path $temp $dir) -Recurse -Force
    }
}

# Copy vendor - only production packages
Write-Host "  Copying: vendor\ (production only)" -ForegroundColor Gray
$vendorSource = Join-Path $source "vendor"
$vendorDest = Join-Path $temp "vendor"
New-Item -ItemType Directory -Path $vendorDest -Force | Out-Null

# Copy vendor essentials
$vendorEssentials = @("autoload.php", "composer", "dompdf", "masterminds", "symfony", "phenx", "sabberworm")
foreach ($vendorItem in $vendorEssentials) {
    $vendorItemPath = Join-Path $vendorSource $vendorItem
    if (Test-Path $vendorItemPath) {
        Copy-Item -Path $vendorItemPath -Destination (Join-Path $vendorDest $vendorItem) -Recurse -Force
    }
}

# Step 3: Clean vendor
Write-Host "[3/5] Cleaning vendor directory..." -ForegroundColor Yellow

$vendorTemp = Join-Path $temp "vendor"
if (Test-Path $vendorTemp) {
    # Remove test directories
    Get-ChildItem -Path $vendorTemp -Recurse -Directory -Include @("tests", "test", "Tests", "docs", "examples") | 
        Remove-Item -Recurse -Force -ErrorAction SilentlyContinue
    
    # Remove docs and configs
    Get-ChildItem -Path $vendorTemp -Recurse -File -Include @("*.md", "phpunit.xml*", ".git*", "LICENSE", "COPYING") | 
        Remove-Item -Force -ErrorAction SilentlyContinue
    
    Write-Host "  Vendor cleaned" -ForegroundColor Green
}

# Step 4: Create ZIP
Write-Host "[4/5] Creating ZIP archive..." -ForegroundColor Yellow
Compress-Archive -Path "$temp\*" -DestinationPath $output -CompressionLevel Optimal -Force

# Step 5: Cleanup
Write-Host "[5/5] Cleaning up..." -ForegroundColor Yellow
Remove-Item $temp -Recurse -Force

# Results
Write-Host "`n==================================================" -ForegroundColor Green
Write-Host "✓ PACKAGE CREATED SUCCESSFULLY" -ForegroundColor Green
Write-Host "==================================================" -ForegroundColor Green

$zipInfo = Get-Item $output
Write-Host "`nFile: $($zipInfo.Name)" -ForegroundColor Cyan
Write-Host "Size: $([math]::Round($zipInfo.Length / 1MB, 2)) MB" -ForegroundColor Cyan
Write-Host "Location: $($zipInfo.FullName)" -ForegroundColor Yellow

Write-Host "`n📦 Ready for WordPress.org submission!" -ForegroundColor Green
Write-Host "Submit at: https://wordpress.org/plugins/developers/add/`n" -ForegroundColor White
