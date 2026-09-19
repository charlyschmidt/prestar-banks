#!/bin/bash

echo "========================================"
echo "  AERIA Finance - Development"
echo "========================================"

php artisan serve &
LARAVEL_PID=$!

npm run dev &
VITE_PID=$!

php artisan reverb:start &
REVERB_PID=$!

trap 'kill $LARAVEL_PID $VITE_PID $REVERB_PID 2>/dev/null' EXIT INT TERM

wait