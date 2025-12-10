#!/bin/bash
cd assets
echo "Starting PHP server on http://localhost:8000..."
echo "Opening browser in 2 seconds..."
sleep 2 && open http://localhost:8000 &
php -S localhost:8000
