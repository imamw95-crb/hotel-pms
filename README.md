<p align="center">
<a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a>
</p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

# Hotel PMS - Property Management System

**Dynamic PMS V.2** - Sistem manajemen hotel lengkap dengan integrasi AI, OTA, dan sistem permission yang canggih.

## 🚀 Fitur Utama

| Fitur | Deskripsi |
|-------|-----------|
| **AI Chat Assistant** | Chat dengan AI via OpenRouter untuk booking & query hotel |
| **OTA Integration** | Sync otomatis dari Tiket.com & Traveloka |
| **Room Management** | Kelola kamar, tipe, dan availability |
| **Reservation System** | Booking manual & OTA dengan back-to-back support |
| **Permission System** | Role-based access control (owner, admin, frontoffice) |
| **Front Office** | Check-in, check-out, housekeeping, issue card |

## ⚡ Quick Start

```bash
# Install dependencies
composer install
npm install

# Setup environment
cp .env.example .env
php artisan key:generate

# Migrate database
php artisan migrate --seed

# Run servers
php artisan serve
npm run dev
```

## 📖 Documentation

- **[Tutorial Lengkap](TUTORIAL.md)** - Panduan penggunaan dan fitur
- **[Permission System](PERMISSION_SYSTEM.md)** - RBAC & menu dinamis
- **[Deployment Guide](DEPLOY.md)** - Deploy ke production

## 🛠️ Tech Stack

- **Backend:** Laravel 13.x, PHP 8.3+
- **Database:** MySQL
- **Frontend:** Blade + Tailwind CSS + Vanilla JS
- **AI:** OpenRouter API (owl-alpha model)
- **OTA:** IMAP Email Parser

## 📱 AI Chat Endpoint

```
POST /api/ai/chat
```

Request:
```json
{
  "message": "Cari kamar untuk besok",
  "current_page": "/reservations"
}
```

## 🤝 Contributing

Terima kasih telah berkontribusi! Silakan buka issue atau pull request.
