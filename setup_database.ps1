Write-Host "🚀 Setting up Terraponix Database..." -ForegroundColor Green

# Check if we're in the right directory
if (-not (Test-Path "artisan")) {
    Write-Host "❌ artisan file not found. Make sure you're in the Laravel project root." -ForegroundColor Red
    Read-Host "Press Enter to exit"
    exit 1
}

Write-Host "📦 Resetting database migrations..." -ForegroundColor Yellow
php artisan migrate:reset --force

Write-Host "📦 Running fresh database migrations..." -ForegroundColor Yellow  
php artisan migrate --force

Write-Host "🌱 Running database seeders..." -ForegroundColor Yellow
php artisan db:seed --force

Write-Host "✅ Database setup completed successfully!" -ForegroundColor Green
Write-Host ""
Write-Host "🌐 You can now start the server using:" -ForegroundColor Cyan
Write-Host "   php artisan serve" -ForegroundColor White
Write-Host ""
Write-Host "🔗 API Endpoints available:" -ForegroundColor Cyan
Write-Host "   - GET  /api/v1/devices" -ForegroundColor White
Write-Host "   - GET  /api/v1/devices/{id}/actuator-status" -ForegroundColor White
Write-Host "   - POST /api/v1/actuator/control" -ForegroundColor White
Write-Host "   - GET  /api/v1/devices/{id}/actuator-logs" -ForegroundColor White
Write-Host "   - POST /api/v1/actuator/auto-mode" -ForegroundColor White
Write-Host ""

Read-Host "Press Enter to exit"