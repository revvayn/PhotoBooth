param([switch]$InstallAutostart)

$root = Split-Path -Parent $PSScriptRoot
$icon = Join-Path $root 'public\favicon.ico'
$wsh = New-Object -ComObject WScript.Shell
$desktop = [Environment]::GetFolderPath('Desktop')
$programs = [Environment]::GetFolderPath('Programs')

$targets = @(
    @{ Name = 'Photobooth (Lokal)'; Script = 'photobooth-local.bat'; Desc = 'Buka Photobooth dari server lokal' },
    @{ Name = 'Photobooth (Testing Ngrok)'; Script = 'photobooth-ngrok.bat'; Desc = 'Buka Photobooth lewat URL ngrok' }
)

foreach ($t in $targets) {
    $scriptPath = Join-Path $PSScriptRoot $t.Script
    foreach ($dir in @($desktop, $programs)) {
        $lnk = Join-Path $dir ($t.Name + '.lnk')
        $sc = $wsh.CreateShortcut($lnk)
        $sc.TargetPath = $scriptPath
        $sc.WorkingDirectory = $root
        $sc.IconLocation = "$icon,0"
        $sc.Description = $t.Desc
        $sc.WindowStyle = 7
        $sc.Save()
        Write-Host "Dibuat: $lnk"
    }
}

if ($InstallAutostart) {
    $startup = [Environment]::GetFolderPath('Startup')
    $lnk = Join-Path $startup 'Photobooth Server.lnk'
    $sc = $wsh.CreateShortcut($lnk)
    $sc.TargetPath = "$env:WINDIR\System32\wscript.exe"
    $sc.Arguments = '"' + (Join-Path $PSScriptRoot 'start-server.vbs') + '"'
    $sc.WorkingDirectory = $root
    $sc.IconLocation = "$icon,0"
    $sc.Description = 'Auto-start server lokal Photobooth'
    $sc.Save()
    Write-Host "Dibuat: $lnk"
}
