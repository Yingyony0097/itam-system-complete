#!/bin/bash
# ===================================================
# ITAM System - DigitalOcean Deploy Script
# ວາງຄຳສັ່ງນີ້ໃນ Droplet Console ແລ້ວລໍຖ້າ
# ===================================================

set -e

echo "========================================"
echo "  ITAM System - ການ Deploy ເລີ່ມຕົ້ນ"
echo "========================================"

# 1. ອັບເດດ ແລະ ຕິດຕັ້ງ Docker
echo "[1/5] ຕິດຕັ້ງ Docker..."
apt-get update -y
apt-get install -y docker.io docker-compose-v2
systemctl enable docker
systemctl start docker

# 2. ຕັ້ງ Firewall
echo "[2/5] ຕັ້ງ Firewall..."
ufw allow 22/tcp
ufw allow 80/tcp
ufw allow 443/tcp
echo "y" | ufw enable

# 3. Clone ໂປຣເຈກ
echo "[3/5] Clone ໂປຣເຈກ..."
mkdir -p /opt
cd /opt
if [ -d "itam-system-complete" ]; then
    cd itam-system-complete
    git pull
else
    git clone https://github.com/Yingyony0097/itam-system-complete.git
    cd itam-system-complete
fi

# 4. Build ແລະ ເລີ່ມ containers
echo "[4/5] Build ແລະ ເລີ່ມ Docker containers..."
docker compose -f docker-compose.prod.yml up -d --build

# 5. ລໍຖ້າ MySQL ພ້ອມ
echo "[5/5] ລໍຖ້າ MySQL ພ້ອມໃຊ້ງານ..."
sleep 15

# ສະແດງຜົນ
echo ""
echo "========================================"
echo "  DEPLOY ສຳເລັດແລ້ວ!"
echo "========================================"
DROPLET_IP=$(curl -s http://169.254.169.254/metadata/v1/interfaces/public/0/ipv4/address 2>/dev/null || hostname -I | awk '{print $1}')
echo ""
echo "  URL: http://$DROPLET_IP"
echo ""
echo "  Login:"
echo "    Email: admin@pline.com"
echo "    Password: password"
echo ""
echo "  ກະລຸນາປ່ຽນລະຫັດຜ່ານຫຼັງເຂົ້າລະບົບ!"
echo "========================================"
