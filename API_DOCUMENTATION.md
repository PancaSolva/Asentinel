# Admin API Documentation

This documentation describes the available API endpoints for the Asentinel admin dashboard. All endpoints are protected by admin authentication.

## Authentication
To access these endpoints, the user must be logged in as an admin. The API uses session-based authentication.

- **Header**: `Accept: application/json`
- **Error Response**: If not authenticated, the API returns:
  ```json
  {
    "message": "Unauthorized"
  }
  ```
  Status: `401 Unauthorized`

---

## 1. User Management
- **URL**: `/admin/api/users`
- **Endpoints**:
  - `GET /` : List all users
  - `POST /` : Create user (`name`, `email`, `password`, `role`)
  - `PUT /{id}` : Update user
  - `DELETE /{id}` : Delete user

---

## 2. Aplikasi Management
- **URL**: `/admin/api/aplikasi`
- **Body Parameters**: `nama`, `deskripsi`, `tipe`, `ip_local`, `url_service`, `url_repository`, `url_api_docs`
- **Endpoints**:
  - `GET /` : List all aplikasi
  - `POST /` : Create aplikasi
  - `GET /{id}` : Show aplikasi detail
  - `PUT /{id}` : Update aplikasi
  - `DELETE /{id}` : Delete aplikasi

---

## 3. Service Management
- **URL**: `/admin/api/services`
- **Body Parameters**: `id_aplikasi`, `nama`, `tipe_service`, `ip_local`, `url_service`, `url_repository`, `url_api_docs`
- **Endpoints**:
  - `GET /` : List all services
  - `POST /` : Create service
  - `GET /{id}` : Show service detail
  - `PUT /{id}` : Update service
  - `DELETE /{id}` : Delete service

---

## 4. Log Monitor
- **URL**: `/admin/api/log-monitor`
- **Body Parameters**: `id_aplikasi`, `id_service`, `url`, `status`, `http_status_code`, `response_time_ms`, `checked_at`
- **Endpoints**:
  - `GET /` : List all monitor logs
  - `POST /` : Create monitor log
  - `GET /{id}` : Show monitor log detail
  - `PUT /{id}` : Update monitor log
  - `DELETE /{id}` : Delete monitor log

---

## 5. Log Anomali
- **URL**: `/admin/api/log-anomali`
- **Body Parameters**: `id_aplikasi`, `id_service`, `description`, `severity`, `detected_at`
- **Endpoints**:
  - `GET /` : List all anomali logs
  - `POST /` : Create anomali log
  - `GET /{id}` : Show anomali log detail
  - `PUT /{id}` : Update anomali log
  - `DELETE /{id}` : Delete anomali log

## 6. Pin Management
- **URL**: `/admin/api/pin`
- **Body Parameters**: `id_user`, `id_aplikasi`
- **Endpoints**:
  - `GET /` : List all pins
  - `POST /` : Create pin
  - `GET /{id}` : Show pin detail
  - `PUT /{id}` : Update pin
  - `DELETE /{id}` : Delete pin
