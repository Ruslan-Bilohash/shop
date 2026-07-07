Push-Location C:\bilohash\shop
$files = @('lang/en.php','lang/uk.php','lang/no.php','lang/ru.php','lang/sv.php')
foreach ($f in $files) {
    powershell -NoProfile -File scripts\deploy-to-hostinger.ps1 -Files $f
}
Pop-Location
Write-Host 'Deploy lang tax homepage done.'