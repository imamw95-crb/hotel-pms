# Hotel PMS — OTA Autopilot Supervisor Setup

## Cara Deploy di Linux

### Quick Setup (One Command)

```bash
cd /var/www/hotel-pms/deploy/supervisor
sudo bash setup.sh
```

### Manual Setup

```bash
# 1. Install supervisor
sudo apt-get install -y supervisor

# 2. Copy config
sudo cp hotel-pms-worker.conf /etc/supervisor/conf.d/hotel-pms-worker.conf

# 3. Buat log files
sudo touch /var/log/hotel-pms-worker.log /var/log/hotel-pms-scheduler.log
sudo chown www-data:www-data /var/log/hotel-pms-*.log

# 4. Reload supervisor
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start hotel-pms-worker:*
sudo supervisorctl start hotel-pms-scheduler:*

# 5. Enable on boot
sudo systemctl enable supervisor
```

### Cron Fallback (Safety Net)

Tambahkan cron sebagai backup kalau supervisor gagal:

```bash
sudo crontab -e
```

Tambahkan baris ini:

```
* * * * * cd /var/www/hotel-pms && php artisan schedule:run >> /dev/null 2>&1
```

> Cron ini jalan setiap menit. Kalau supervisor sudah jalan, `withoutOverlapping()` di scheduler akan mencegah duplikasi.

## Monitoring

```bash
# Cek status
sudo supervisorctl status

# Restart worker
sudo supervisorctl restart hotel-pms-worker:*

# Restart scheduler
sudo supervisorctl restart hotel-pms-scheduler

# Lihat log
sudo tail -f /var/log/hotel-pms-worker.log
sudo tail -f /var/log/hotel-pms-scheduler.log
```

## Konfigurasi

| Proses | Jumlah | Fungsi |
|--------|--------|--------|
| `hotel-pms-worker` | 2 proses | Queue worker — proses email OTA via Redis |
| `hotel-pms-scheduler` | 1 proses | Scheduler — jalanin `hotel:read-emails` setiap menit |

## Troubleshooting

| Masalah | Solusi |
|---------|--------|
| `FATAL` state | Cek log: `tail /var/log/hotel-pms-worker.log` |
| Queue tidak jalan | `sudo supervisorctl restart hotel-pms-worker:*` |
| Scheduler tidak jalan | `sudo supervisorctl restart hotel-pms-scheduler` |
| Setelah reboot tidak jalan | Pastikan `systemctl enable supervisor` sudah di-run |
| Permission error | Pastikan `user=www-data` dan file `.env` readable |
