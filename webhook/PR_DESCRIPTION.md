## 🔔 Webhook Telegram Integration

Menambahkan **FastAPI webhook microservice** untuk mengirim notifikasi Telegram otomatis saat sistem monitoring mendeteksi service DOWN atau RECOVERED.

### Perubahan

**Baru (`webhook/`)**
- `main.py` — FastAPI server dengan endpoint `POST /webhook`, `GET /health`, `GET /status`
- `services/telegram.py` — Integrasi Telegram Bot API dengan retry (3x exponential backoff)
- `services/monitor.py` — Async monitoring scheduler (opsional, default OFF)
- `utils/cooldown.py` — Anti-spam per-service (cooldown 5 menit per service, antar service tidak saling blokir)
- `utils/logger.py` — Logging ke console + rotating file
- `Dockerfile` + `requirements.txt` — Deployment ready

**Modifikasi**
- `CheckServiceJob.php` — Kirim payload ke webhook saat status UP/DOWN + deteksi non-2xx sebagai DOWN
- `config/services.php` — Tambah konfigurasi `webhook.url` dan `webhook.secret`
- `.env.example` — Tambah variabel `WEBHOOK_URL` dan `WEBHOOK_SECRET`
- `docker-compose.yml` — Tambah container `asentinel-webhook`

### Cara Kerja
1. Laravel `CheckServiceJob` cek service → deteksi DOWN/UP
2. Kirim payload JSON ke `POST /webhook` (FastAPI)
3. Cooldown check per-service → jika lolos, kirim ke Telegram
4. Format pesan: 🔴 SERVICE DOWN / 🟢 SERVICE RECOVERED

### Setup
```bash
cd webhook
cp .env.example .env
# Isi TELEGRAM_BOT_TOKEN dan TELEGRAM_CHAT_ID
pip install -r requirements.txt
python -m uvicorn main:app --port 9000
```

### Catatan
> ⚠️ Untuk bagian **sistem monitoring** (`CheckServiceJob`, scheduler, dll), nanti akan di-update dan disesuaikan lebih lanjut oleh **mas Bama**. Branch ini fokus pada integrasi webhook + Telegram notification saja.
