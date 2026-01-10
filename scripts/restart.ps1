# Restart Dashboard Docker Container

Write-Host "Restarting dashboard-container..." -ForegroundColor Yellow
docker restart dashboard-container

if ($LASTEXITCODE -eq 0) {
    Write-Host "Done! Dashboard restarted on port 9000" -ForegroundColor Green
} else {
    Write-Host "Restart failed! Is the container running?" -ForegroundColor Red
    exit 1
}
