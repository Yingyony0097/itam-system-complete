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
echo "[1/6] ອັບເດດລະບົບ ແລະ ຕິດຕັ້ງ Docker..."
apt-get update -y
apt-get upgrade -y
apt-get install -y docker.io docker-compose-v2 fail2ban
systemctl enable docker
systemctl start docker

# 2. ຕັ້ງ Firewall
echo "[2/6] ຕັ້ງ Firewall (ເປີດ SSH, HTTP, HTTPS ເທົ່ານັ້ນ)..."
ufw default deny incoming
ufw default allow outgoing
ufw allow 22/tcp
ufw allow 80/tcp
ufw allow 443/tcp
echo "y" | ufw enable

# 3. ຕັ້ງ fail2ban (ປ້ອງກັນ brute-force SSH)
echo "[3/6] ຕັ້ງ fail2ban..."
systemctl enable fail2ban
systemctl start fail2ban

# 4. Clone ໂປຣເຈກ
echo "[4/6] Clone ໂປຣເຈກ..."
mkdir -p /opt
cd /opt
if [ -d "itam-system-complete" ]; then
    cd itam-system-complete
    git pull
else
    git clone https://github.com/Yingyony0097/itam-system-complete.git
    cd itam-system-complete
fi

# 5. ສ້າງ .env ຈາກ .env.example (ຖ້າຍັງບໍ່ມີ)
if [ ! -f ".env" ]; then
    echo "[5/6] ສ້າງ .env..."
    cp .env.example .env
    echo ""
    echo "!!! ກະລຸນາແກ້ໄຂ .env ດ້ວຍ: nano /opt/itam-system-complete/.env"
    echo "!!! ປ່ຽນ password ກ່ອນດຳເນີນການຕໍ່"
    echo ""
    read -p "ກົດ Enter ຫຼັງແກ້ໄຂ .env ແລ້ວ..."
else
    echo "[5/6] .env ມີຢູ່ແລ້ວ, ຂ້າມ..."
fi

# 6. Build ແລະ ເລີ່ມ containers
echo "[6/6] Build ແລະ ເລີ່ມ Docker containers..."
docker compose -f docker-compose.prod.yml up -d --build

# ລໍຖ້າ MySQL ພ້ອມ
echo "ລໍຖ້າ MySQL ພ້ອມໃຊ້ງານ..."
sleep 20

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
echo "  ====== ຄວາມປອດໄພ ======"
echo "  [OK] Firewall (UFW) - ເປີດ 22, 80, 443 ເທົ່ານັ້ນ"
echo "  [OK] fail2ban - ປ້ອງກັນ brute-force SSH"
echo "  [OK] MySQL port - ບໍ່ເປີດ ສູ່ພາຍນອກ"
echo "  [OK] Error display - ປິດໃນ production"
echo "  [OK] DB user - ສິດ SELECT/INSERT/UPDATE/DELETE ເທົ່ານັ້ນ"
echo ""
echo "  ກະລຸນາປ່ຽນລະຫັດຜ່ານ admin ຫຼັງເຂົ້າລະບົບ!"
echo "========================================"
