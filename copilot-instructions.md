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
- **Key Models:** Room, Reservation, Guest, Transaction, Deposit, RestoTransaction, PaymentMethod, User, Role, Permission
- **Key Feature:** Back-to-Back Booking (check-out 12:00 & check-in 14:00 same day = NOT a conflict)

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
- Speed and efficiency
- Low token usage
- Maintainability
- Production safety

## CRITICAL: Database Protection
⚠️ **PRODUCTION SAFETY RULES:**
- **NEVER** suggest or execute: `php artisan migrate:fresh`, `php artisan migrate:reset`, `php artisan migrate:refresh`
- These commands **DELETE ALL DATA** in production
- **SYSTEM AUTOMATICALLY BLOCKS THESE** — they will fail with error message in production
- Use `php artisan migrate` ONLY for running new migrations
- If reset is needed → require explicit user confirmation + backup verification

## Migration File Optimization
- **Read ONLY new/modified migration files** relevant to current task
- **NEVER** read all migrations or historical migration files
- Migration file reading = high token cost, avoid unless essential
- Focus on `database/migrations/` files created/changed in this session only

## Implementation Details
- `app/Providers/AppServiceProvider.php` — Listens for Artisan `CommandStarting` event
- Blocks dangerous commands before they execute in production environment
- Logs all blocked attempts for audit trail
- Safe commands like `migrate` work normally