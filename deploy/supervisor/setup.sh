#!/bin/bash
# ==================================================
# Hotel PMS — Supervisor Setup Script (Ubuntu/Debian)
# ==================================================
# Run as root or with sudo:
#   sudo bash setup.sh
# ==================================================

set -e

PROJECT_DIR="/var/www/hotel-pms"
SUPERVISOR_CONF="/etc/supervisor/conf.d/hotel-pms-worker.conf"
LOG_DIR="/var/log"

echo "=========================================="
echo "  Hotel PMS — OTA Autopilot Supervisor Setup"
echo "=========================================="

# 1. Install supervisor if not present
if ! command -v supervisord &> /dev/null; then
    echo "[1/6] Installing supervisor..."
    apt-get update -qq
    apt-get install -y -qq supervisor
else
    echo "[1/6] Supervisor already installed"
fi

# 2. Ensure log files exist with proper permissions
echo "[2/6] Creating log files..."
touch ${LOG_DIR}/hotel-pms-worker.log
touch ${LOG_DIR}/hotel-pms-scheduler.log
chown www-data:www-data ${LOG_DIR}/hotel-pms-worker.log
chown www-data:www-data ${LOG_DIR}/hotel-pms-scheduler.log
chmod 644 ${LOG_DIR}/hotel-pms-worker.log
chmod 644 ${LOG_DIR}/hotel-pms-scheduler.log

# 3. Copy supervisor config
echo "[3/6] Copying supervisor config..."
cp hotel-pms-worker.conf ${SUPERVISOR_CONF}

# 4. Update project path in config if different
if [ "${PROJECT_DIR}" != "/var/www/hotel-pms" ]; then
    sed -i "s|/var/www/hotel-pms|${PROJECT_DIR}|g" ${SUPERVISOR_CONF}
fi

# 5. Reread and update supervisor
echo "[4/6] Reloading supervisor..."
supervisorctl reread
supervisorctl update

# 6. Start processes
echo "[5/6] Starting processes..."
supervisorctl start hotel-pms-worker:*
supervisorctl start hotel-pms-scheduler:*

# 7. Enable supervisor on boot
echo "[6/6] Enabling supervisor on boot..."
systemctl enable supervisor

echo ""
echo "=========================================="
echo "  ✅ Setup Complete!"
echo "=========================================="
echo ""
echo "Useful commands:"
echo "  sudo supervisorctl status          — Check status"
echo "  sudo supervisorctl restart hotel-pms-worker:*   — Restart workers"
echo "  sudo supervisorctl restart hotel-pms-scheduler  — Restart scheduler"
echo "  sudo tail -f ${LOG_DIR}/hotel-pms-worker.log    — Worker logs"
echo "  sudo tail -f ${LOG_DIR}/hotel-pms-scheduler.log — Scheduler logs"
echo ""
echo "Auto-restart is ENABLED — if server reboots,"
echo "supervisor will automatically start both processes."
