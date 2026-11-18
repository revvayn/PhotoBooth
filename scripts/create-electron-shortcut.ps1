$root = Split-Path -Parent $PSScriptRoot
$icon = Join-Path $root 'electron\icon.ico'
$exe = Join-Path $env:LOCALAPPDATA 'Programs\Photobooth\Photobooth.exe'

# Fallback ke hasil build bila installer belum dijalankan.
if (-not (Test-Path $exe)) {
    $exe = Join-Path $root 'release\win-unpacked\Photobooth.exe'
}

if (-not (Test-Path $exe)) {
    Write-Error "Aplikasi tidak ditemukan. Jalankan npm run electron:build dulu."
    exit 1
}

$wsh = New-Object -ComObject WScript.Shell
$dirs = @(
    [Environment]::GetFolderPath('Desktop'),
    [Environment]::GetFolderPath('Programs')
)

foreach ($dir in $dirs) {
    $lnk = Join-Path $dir 'Photobooth.lnk'
    $sc = $wsh.CreateShortcut($lnk)
    $sc.TargetPath = $exe
    $sc.WorkingDirectory = Split-Path -Parent $exe
    $sc.IconLocation = "$icon,0"
    $sc.Description = 'Aplikasi desktop Photobooth'
    $sc.Save()
    Write-Host "Dibuat: $lnk"
}
