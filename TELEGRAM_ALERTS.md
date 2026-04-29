# Asentinel Telegram Alerts 🚀

Asentinel includes a native alerting system to send notifications directly to a Telegram group or chat whenever a service goes `DOWN` or recovers (`UP`).

## **1. Configuration**

Add the following credentials to your `.env` file:

```env
# Telegram Bot Configuration
TELEGRAM_BOT_TOKEN="your_bot_token_here"
TELEGRAM_CHAT_ID="your_chat_id_here"
TELEGRAM_COOLDOWN_MINUTES=5
```

- `TELEGRAM_BOT_TOKEN`: The token you get from [@BotFather](https://t.me/BotFather) when creating your bot.
- `TELEGRAM_CHAT_ID`: The ID of the user, group, or channel where the bot should send messages.
- `TELEGRAM_COOLDOWN_MINUTES`: Anti-spam duration. If a service stays `DOWN`, the system will wait for this amount of time before sending another `DOWN` notification.

---

## **2. How it Works**

- **DOWN Alert 🔴**: Sent immediately when a service check fails (non-2xx HTTP code, timeout, or connection error).
- **Anti-Spam (Cooldown)**: Subsequent failures for the *same* service will be suppressed until the cooldown period expires.
- **Recovery (UP) Alert 🟢**: If the service recovers, an `UP` notification is sent immediately, bypassing any active cooldown restrictions.
- **Retry Mechanism**: If the Telegram API cannot be reached, the system will automatically retry up to 3 times using exponential backoff (1s, 2s, 4s).

---

## **3. Testing Telegram Alerts**

You can quickly test if your Telegram notification integration is working by using the interactive Tinker console. Run the following commands in your terminal:

### **Test DOWN Alert**
```bash
php artisan tinker --execute="app(\App\Services\AlertService::class)->sendTelegramIfNeeded('Test Service', 'DOWN', 'Simulasi error connection timeout');"
```
*(You should immediately receive a red DOWN alert in your Telegram chat)*

### **Test Cooldown Anti-Spam**
Run the exact same command immediately after the first one:
```bash
php artisan tinker --execute="app(\App\Services\AlertService::class)->sendTelegramIfNeeded('Test Service', 'DOWN', 'Simulasi error connection timeout');"
```
*(This message should be suppressed and you will NOT receive it in Telegram. You can verify this by checking `storage/logs/laravel.log`)*

### **Test UP (Recovery) Alert**
```bash
php artisan tinker --execute="app(\App\Services\AlertService::class)->sendTelegramIfNeeded('Test Service', 'UP', 'Service has recovered successfully');"
```
*(This bypasses the cooldown because the status changed to UP, and you will receive a green recovery message)*
