$appDir = Split-Path -Parent $MyInvocation.MyCommand.Definition
$env:Path = "$env:USERPROFILE\scoop\shims;$env:Path"

$portInUse = netstat -ano | Select-String ':8000'
if (-not $portInUse) {
    Write-Host 'IdeaPost サーバーを起動中...'
    Start-Process -FilePath 'php' -ArgumentList 'artisan', 'serve', '--quiet' -WorkingDirectory $appDir -WindowStyle Hidden
    Start-Sleep -Seconds 2
}

$url = 'http://127.0.0.1:8000'
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
