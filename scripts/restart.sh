#!/bin/bash
# Restart Dashboard Docker Container

echo "Restarting dashboard-container..."
docker restart dashboard-container

if [ $? -eq 0 ]; then
    echo "Done! Dashboard restarted on port 9000"
else
    echo "Restart failed! Is the container running?"
    exit 1
fi
