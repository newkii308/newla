Write-Host "Installing NEWLA CLI on Windows..." -ForegroundColor Cyan

# Check PHP
try {
    $phpVer = & php -r "echo PHP_VERSION;"
    Write-Host "✓ Found PHP $phpVer" -ForegroundColor Green
} catch {
    Write-Host "Error: PHP is not found in your PATH. Please install PHP 8.2+." -ForegroundColor Red
    exit 1
}

$targetDir = "$env:LOCALAPPDATA\Programs\newla"
if (!(Test-Path $targetDir)) {
    New-Item -ItemType Directory -Force -Path $targetDir | Out-Null
}

Invoke-WebRequest -Uri "https://raw.githubusercontent.com/newkii308/newla/main/cli/bin/newla" -OutFile "$targetDir\newla"
Set-Content -Path "$targetDir\newla.bat" -Value "@echo off`r`nphp `"$targetDir\newla`" %*" -Encoding ascii

$userPath = [Environment]::GetEnvironmentVariable("Path", "User")
if ($userPath -notlike "*$targetDir*") {
    [Environment]::SetEnvironmentVariable("Path", "$userPath;$targetDir", "User")
    $env:Path = "$env:Path;$targetDir"
}

Write-Host "✓ NEWLA CLI installed successfully to $targetDir" -ForegroundColor Green
Write-Host "Run 'newla --version' to get started." -ForegroundColor Yellow