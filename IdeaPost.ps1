$appDir = Split-Path -Parent $MyInvocation.MyCommand.Definition
$env:Path = "C:\php;$env:USERPROFILE\scoop\shims;$env:Path"

$phpPath = 'php'
if (Test-Path 'C:\php\php.exe') {
    $phpPath = 'C:\php\php.exe'
}

$portInUse = netstat -ano | Select-String ':8190'
if (-not $portInUse) {
    Write-Host 'IdeaPost サーバーを起動中...'
    Start-Process -FilePath $phpPath -ArgumentList 'artisan', 'serve', '--port', '8190', '--quiet' -WorkingDirectory $appDir -WindowStyle Hidden
    Start-Sleep -Seconds 4
}

$url = 'http://127.0.0.1:8190'
$appArg = '--app=' + $url

$edgePath1 = $env:ProgramFiles + '\Microsoft\Edge\Application\msedge.exe'
$edgePath2 = ${env:ProgramFiles(x86)} + '\Microsoft\Edge\Application\msedge.exe'
$chromePath = $env:ProgramFiles + '\Google\Chrome\Application\chrome.exe'

if (Test-Path $edgePath2) {
    Start-Process $edgePath2 $appArg
} elseif (Test-Path $edgePath1) {
    Start-Process $edgePath1 $appArg
} elseif (Test-Path $chromePath) {
    Start-Process $chromePath $appArg
} else {
    Start-Process $url
}
