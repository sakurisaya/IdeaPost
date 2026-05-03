# IdeaPost デスクトップショートカット作成スクリプト
$appDir = Split-Path -Parent $MyInvocation.MyCommand.Definition
$batFile = Join-Path $appDir 'IdeaPost.bat'
$desktop = [Environment]::GetFolderPath('Desktop')
$shortcutPath = Join-Path $desktop 'IdeaPost.lnk'

# ショートカット作成
$shell = New-Object -ComObject WScript.Shell
$shortcut = $shell.CreateShortcut($shortcutPath)
$shortcut.TargetPath = 'powershell.exe'
$shortcut.Arguments = "-ExecutionPolicy Bypass -WindowStyle Hidden -File `"$appDir\IdeaPost.ps1`""
$shortcut.WorkingDirectory = $appDir
$shortcut.WindowStyle = 1

# Windows組み込みアイコン（imageres.dll の電球に近いアイコン）
# インデックス278 = 電球、109 = アイデア系
$shortcut.IconLocation = '%SystemRoot%\System32\imageres.dll,278'
$shortcut.Description = 'IdeaPost - アイデアメモアプリ'

$shortcut.Save()
Write-Host "デスクトップにショートカットを作成しました: $shortcutPath"
