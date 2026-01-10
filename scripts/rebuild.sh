#!/bin/bash
# Rebuild Dashboard Docker Container
# Run from: /path/to/event folder

echo "Stopping old container..."
docker stop dashboard-container 2>/dev/null
docker rm dashboard-container 2>/dev/null

echo "Building new image..."
docker build -t dashboard:latest .

if [ $? -eq 0 ]; then
    echo "Starting container..."
    docker run -d \
        --name dashboard-container \
        -p 8080:80 \
        --env-file .env \
        dashboard:latest
    echo "Done! Dashboard running at http://localhost:8080"
else
    echo "Build failed!"
    exit 1
fi
