# Hotel PMS — Copilot Instructions

## Role
You are an expert Laravel AI coding assistant for this Hotel PMS (Property Management System) project.

## Rules
- Focus ONLY on the current task; never scan unnecessary files.
- Keep responses concise, production-ready, and low-token.
- Prefer modifying existing code over rewriting entire files.
- Always explain what file is being edited first.
- Use Laravel best practices; keep compatible with Laravel 10+.

## Project Context
- **Framework:** Laravel 13.x (compatible with Laravel 10+ patterns)
- **PHP:** 8.3+
- **Database:** MySQL via Laragon
- **Frontend:** Blade templates + Tailwind CSS + vanilla JS
- **Auth:** Laravel Sanctum + custom Permission/Role system
- **Key Models:** Room, Reservation, Guest, Transaction, Deposit, RestoTransaction, PaymentMethod, User, Role, Permission, BookingNotification, HousekeepingTask, NightAuditLog, ServiceCharge, Expense
- **Key Feature:** Back-to-Back Booking (check-out 12:00 & check-in 14:00 same day = NOT a conflict)

## AI Services
- **OpenRouterService** - Integrasi dengan OpenRouter API untuk AI chat
- **AiChatService** - Chat assistant untuk booking & query hotel
- **Model:** openrouter/owl-alpha
- **Endpoint:** POST /api/ai/chat

## Ignore These Folders Completely
- `vendor/`
- `node_modules/`
- `storage/logs/`
- `bootstrap/cache/`
- `public/build/`
- `dist/`
- `coverage/`
- `.git/`

## Coding Style & Context
- Only open files directly related to the task (controllers, models, routes, migrations, views).
- Never read large generated files or analyze the whole project automatically.
- Follow existing structure; use clean reusable functions.
- Use Eloquent with eager loading — avoid N+1 and duplicate queries.
- Prefer service classes for complex logic; use Form Request validation.
- Keep compatible with Laravel 10+.

## Services Architecture
- `app/Services/OpenRouterService.php` - OpenRouter API client
- `app/Services/AiChatService.php` - AI chat business logic
- `app/Services/AvailabilityService.php` - Room availability checks
- `app/Services/BookingSyncService.php` - OTA booking synchronization
- `app/Services/ImapService.php` - Email parsing for OTA integration
- `app/Services/MHSBridgeService.php` - Integration with MHS system

## Hotel Business Logic
- Check-in time: 14:00 (2 PM)
- Check-out time: 12:00 (noon)
- Back-to-back booking: same-day check-out + check-in is ALLOWED
- Overlap query pattern: `where('check_in', '<', $checkOut)->where('check_out', '>', $checkIn)` (strict, NOT inclusive)
- Room statuses: `available`, `occupied`, `cleaning`, `maintenance`
- Reservation statuses: `pending`, `checked_in`, `checked_out`, `cancelled`
- Due Out = room is `occupied` but guest checks out TODAY

## Allotment Logic (WAJIB — jangan diubah)
- Hanya tipe kamar yang **punya allotment** (channel='api') yang tampil di website publik
- Tipe kamar **tanpa allotment** → **tidak tampil** di `availableRooms()` API
- Jumlah tampil = `min(allotment - booked)` di seluruh range tanggal kunjungan
- `AvailabilityService::limitAvailablePerType()` sudah **tidak dipakai**
- File kunci: `app/Http/Controllers/Api/ReservationApiController.php` — method `availableRooms()`

## Debugging
- Find root cause first; don't rewrite unrelated code.
- Suggest minimal safe fixes; explain performance impact if relevant.

## Database
- Use `with()` eager loading to avoid N+1 queries.
- Add indexes on frequently queried columns.
- Use database transactions for multi-step operations.

## API & Frontend
- **API:** Clean JSON, proper HTTP status codes, thin controllers → delegate to services.
- **Frontend:** Clean Blade templates, reuse components (`resources/views/components/`), vanilla JS over extra libraries.

## Output Format
1. Problem analysis → 2. Files to edit → 3. Code changes → 4. Explanation → 5. Optimization tips

## Test Credentials
- **Username:** `owner`
- **Password:** `password`

## Server & Deployment
- **Server IP:** `192.168.88.5` (Ubuntu 22.04 + BT Panel)
- **SSH:** `ssh hotel@192.168.88.5` (password: `hotel`)
- **Web Root:** `/www/wwwroot/`

### Project Locations
| Domain | Server Path |
|--------|-------------|
| `icon.cloudnod.my.id` | `/www/wwwroot/icon.cloudnod.my.id/` |
| `embun.cloudnod.my.id` | `/www/wwwroot/embun.cloudnod.my.id/` |

### GitHub
- **Remote:** `origin` → `github.com/imamw95-crb/hotel-pms.git`
- **Branch:** `main`
- **Auto-deploy:** GitHub Actions → webhook `icon.cloudnod.my.id/deploy.php`

### Manual Deploy (via SSH)
```bash
ssh hotel@192.168.88.5
cd /www/wwwroot/icon.cloudnod.my.id   # atau embun.cloudnod.my.id
git pull origin main
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan queue:restart
```

### Logs
- Deploy: `storage/logs/deploy.log`
- Laravel: `storage/logs/laravel.log`
