#!/usr/bin/env pwsh
<#
.SYNOPSIS
    PHPCS Runner Script
.DESCRIPTION
    Runs PHPCS with custom config to avoid escapeshellarg length limits
.PARAMETER Path
    File or directory to check
.EXAMPLE
    .\phpcs-run.ps1 includes\Admin\Dashboard.php
    .\phpcs-run.ps1 .
#>

param(
    [Parameter(Mandatory=$true)]
    [string]$Path
)

$scriptDir = Split-Path -Parent $MyInvocation.MyCommand.Path
Set-Location $scriptDir

$phpcsPath = Join-Path $scriptDir "vendor\bin\phpcs"
$configPath = Join-Path $scriptDir "phpcs.xml"

& $phpcsPath --standard=$configPath $Path