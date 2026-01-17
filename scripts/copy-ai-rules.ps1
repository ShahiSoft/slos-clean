# Copies workspace AI rules to clipboard for easy paste into any Agent chat input
$rules = Get-Content -Raw -Path .\.vscode\ai-rules.md
Set-Clipboard -Value $rules
Write-Output "AI rules copied to clipboard."