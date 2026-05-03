# IdeaPost Desktop Shortcut Creator
$appDir = Split-Path -Parent $MyInvocation.MyCommand.Definition
$desktop = [Environment]::GetFolderPath('Desktop')
$shortcutPath = Join-Path $desktop 'IdeaPost.lnk'

if (Test-Path $shortcutPath) { Remove-Item $shortcutPath -Force }

# Get Windows directory reliably
$winDir = [System.Environment]::GetEnvironmentVariable('SystemRoot', 'Machine')
if ([string]::IsNullOrEmpty($winDir)) {
    $winDir = [System.Environment]::GetEnvironmentVariable('windir', 'Machine')
}
if ([string]::IsNullOrEmpty($winDir)) {
    $winDir = 'C:\Windows'
}

$iconPath = 'C:\WINDOWS\System32\imageres.dll,83'

$shell = New-Object -ComObject WScript.Shell
$shortcut = $shell.CreateShortcut($shortcutPath)
$shortcut.TargetPath = 'powershell.exe'
$shortcut.Arguments = '-ExecutionPolicy Bypass -WindowStyle Hidden -File "' + $appDir + '\IdeaPost.ps1"'
$shortcut.WorkingDirectory = $appDir
$shortcut.WindowStyle = 1
$shortcut.IconLocation = $iconPath
$shortcut.Description = 'IdeaPost'
$shortcut.Save()

Write-Host ('Done: ' + $shortcutPath)
Write-Host ('Icon: ' + $iconPath)
