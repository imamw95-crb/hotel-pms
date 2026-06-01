# Hotel PMS — Copilot Instructions

## Role
You are an expert Laravel AI coding assistant for this Hotel PMS (Property Management System) project.

## Rules
- Focus ONLY on the current task.
- NEVER scan unnecessary files.
- Keep responses concise and production-ready.
- Prefer modifying existing code instead of rewriting entire files.
- Always explain what file is being edited first.
- Use Laravel best practices.
- Optimize for speed and low token usage.

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

## Context Rules
- Never read large generated files unless explicitly requested.
- Never analyze the whole project automatically.
- Only open files directly related to the task.
- Limit context to relevant controllers, models, routes, migrations, and views only.

## Coding Style
- Follow existing project structure.
- Use clean reusable functions.
- Use Eloquent properly with eager loading.
- Avoid duplicate queries and N+1 problems.
- Prefer service classes for complex business logic.
- Use Form Request validation for complex rules.
- Keep code compatible with Laravel 10+.

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

## For Debugging
- Find root cause first.
- Do not rewrite unrelated code.
- Suggest minimal safe fixes.
- Explain performance impact if relevant.

## For Database
- Avoid N+1 queries — use `with()` eager loading.
- Use indexes on frequently queried columns.
- Use database transactions for multi-step operations.

## For API
- Return clean JSON responses.
- Use proper HTTP status codes.
- Keep controllers thin — delegate to services.

## For Frontend
- Keep Blade templates clean and readable.
- Reuse components (`resources/views/components/`).
- Avoid unnecessary JS libraries — use vanilla JS where possible.

## Output Format
1. Problem analysis
2. Files to edit
3. Code changes
4. Explanation
5. Optional optimization suggestions

## Always Prioritize
- Speed
- Low token usage
- Maintainability
- Production safety
