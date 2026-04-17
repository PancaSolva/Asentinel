# Asentinel - Infrastructure & Service Monitoring

Asentinel is a modern Laravel-based monitoring system designed to track the health and performance of applications and microservices in real-time. It features an aesthetic dark mode dashboard, real-time updates via Laravel Reverb, and automated health checks.

## **System Requirements**

To run Asentinel, your environment must meet the following requirements:

### **Backend**
- **PHP**: ^8.3
- **PHP Extensions**: 
  - `bcmath`, `ctype`, `fileinfo`, `json`, `mbstring`, `openssl`, `pdo_sqlite` (or `pdo_mysql`), `tokenizer`, `xml`.
- **Composer**: ^2.0

### **Frontend**
- **Node.js**: ^20.0
- **NPM**: ^10.0
- **Vite**: ^8.0 (included in dev dependencies)

### **Infrastructure**
- **Database**: 
  - **Local**: SQLite (default)
  - **Production**: MySQL 8.4+ / PostgreSQL
- **Real-time Engine**: Laravel Reverb (requires a persistent background process)
- **Queue Worker**: Database or Redis (required for background health checks)

---

## **Installation & Setup**

### **1. Clone & Dependencies**
```bash
git clone <repository-url>
cd Asentinel
composer install
npm install
```

### **2. Environment Configuration**
Copy the example environment file and generate the application key:
```bash
cp .env.example .env
php artisan key:generate
```
*Note: Ensure your `DB_CONNECTION` is set correctly. For SQLite, the database file will be created automatically in `database/database.sqlite`.*

### **3. Database Initialization**
Run the migrations and seed the initial admin user and sample data:
```bash
php artisan migrate --seed
```
*Default Admin Credentials:*
- **Email**: `admin@asentinel.com`
- **Password**: `password`

### **4. Build Frontend Assets**
```bash
npm run build
```

---

## **Running the Application**

Asentinel requires several processes to run concurrently for full functionality:

### **Development (All-in-one)**
The project includes a pre-configured `concurrently` command:
```bash
composer run dev
```
This command starts:
- **Laravel Server** (`php artisan serve`)
- **Queue Worker** (`php artisan queue:listen`)
- **Real-time Server** (`php artisan reverb:start`)
- **Vite Dev Server** (`npm run dev`)

### **Production**
In production, use **Supervisor** to manage background processes. Refer to [MONITOR_DOCUMENTATION.md](MONITOR_DOCUMENTATION.md) for detailed production setup guides including:
- **Cron Job**: For scheduling health checks (`php artisan schedule:run`).
- **Supervisor**: For keeping queue workers and Reverb alive.

---

## **Key Features & Tech Stack**
- **Laravel 13**: Robust backend framework.
- **React 19 & Vite**: Modern, fast frontend.
- **Tailwind CSS v4**: Aesthetic dark mode design.
- **Laravel Reverb**: WebSocket-based real-time data broadcasting.
- **Alpine.js**: Lightweight reactivity for administrative views.

---

## **License**
This project is licensed under the MIT license.
