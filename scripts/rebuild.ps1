# Rebuild Dashboard Docker Container
# Run from: d:\dev\trio\event

Write-Host "Stopping old container..." -ForegroundColor Yellow
docker stop dashboard-container 2>$null
docker rm dashboard-container 2>$null

Write-Host "Building new image..." -ForegroundColor Yellow
docker build -t dashboard:latest .

if ($LASTEXITCODE -eq 0) {
    Write-Host "Starting container..." -ForegroundColor Yellow
    docker run -d `
			--name dashboard-container `
			--network server `
			--label com.docker.compose.project=ticketing `
			--label com.docker.compose.service=dashboard `
			-p 8080:80 `
			dashboard:latest
    Write-Host "Done! Dashboard running at http://localhost:8080" -ForegroundColor Green
} else {
    Write-Host "Build failed!" -ForegroundColor Red
    exit 1
}
