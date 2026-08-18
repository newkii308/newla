Write-Host "=======================================" -ForegroundColor Cyan
Write-Host "       Installing NEWLA CLI...         " -ForegroundColor Cyan
Write-Host "=======================================" -ForegroundColor Cyan

# 1. Check PHP
try {
    $phpVer = & php -r "echo PHP_VERSION;"
    Write-Host "✓ Found PHP $phpVer" -ForegroundColor Green
} catch {
    Write-Host "Error: PHP is not found in your PATH. Please install PHP 8.2+." -ForegroundColor Red
    exit 1
}

# 2. Paths
$installRoot = "$env:LOCALAPPDATA\Programs\newla"
if (!(Test-Path $installRoot)) {
    New-Item -ItemType Directory -Force -Path $installRoot | Out-Null
}

# 3. Download zip from GitHub
$zipPath = "$env:TEMP\newla.zip"
Write-Host "Downloading latest NEWLA release from GitHub..." -ForegroundColor Cyan
Invoke-WebRequest -Uri "https://github.com/newkii308/newla/archive/refs/heads/main.zip" -OutFile $zipPath

Expand-Archive -Path $zipPath -DestinationPath "$env:TEMP\newla_extracted" -Force
Copy-Item -Path "$env:TEMP\newla_extracted\newla-main\*" -Destination $installRoot -Recurse -Force
Remove-Item -Path $zipPath -Force -ErrorAction SilentlyContinue
Remove-Item -Path "$env:TEMP\newla_extracted" -Recurse -Force -ErrorAction SilentlyContinue

# 4. Create batch & ps1 wrapper in bin
$binDir = "$installRoot\cli\bin"
Set-Content -Path "$binDir\newla.bat" -Value "@echo off`r`nphp `"$binDir\newla`" %*" -Encoding ascii
Set-Content -Path "$binDir\newla.cmd" -Value "@echo off`r`nphp `"$binDir\newla`" %*" -Encoding ascii

# 5. Add to User PATH
$userPath = [Environment]::GetEnvironmentVariable("Path", "User")
if ($userPath -notlike "*$binDir*") {
    [Environment]::SetEnvironmentVariable("Path", "$userPath;$binDir", "User")
    $env:Path = "$env:Path;$binDir"
}

Write-Host "✓ NEWLA CLI installed successfully to $installRoot" -ForegroundColor Green
Write-Host "Run 'newla --version' to get started." -ForegroundColor Yellow