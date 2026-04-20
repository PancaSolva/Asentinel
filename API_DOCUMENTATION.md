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

## 6. Premission Management (For Admin)
- **URL**: '/admin/api/permission/{endpoint}'
- **Endpoints**:
  - `guest-list` : List all guest
  - `guest-add/{id}` : Add Guest
  - `remove-guest/{id}` : Remove Guest

## 7. Guest Access Management
**Base URL**: `http://127.0.0.1:8010/api`  
**Authentication**: **None required** (public)

### List all guest access
```
GET /admin/guest-list
```
**Response**:
```json
[
  {
    "id": 1,
    "id": 2,
    "id_aplikasi": 1,
    "id_service": 3,
    "user": {"id": 2, "name": "John Doe", "email": "john@example.com"},
    "aplikasi": {"id": 1, "name": "My App"},
    "service": {"id_service": 3, "nama_service": "My Service"}
  }
]
```

### Add guest access
```
POST /admin/add-guest/{user_id}/{id_service?}
```
**Path parameters**:
- `user_id` (required): User ID
- `id_service` (optional): Service ID (omit or use `/user_id/` for no service)

**Body** (JSON):
```json
{
  "id_aplikasi": 1  // Required, must exist in aplikasi table
}
```
**Responses**:
- **200 Success**: `{"success": true, "data": {...}}`
- **422 Validation**: `{"errors": {"id_aplikasi": ["The id aplikasi field is required."]}}`
- **409 Conflict**: `{"error": "Guest access already exists"}`

### Remove guest access
```
DELETE /remove-guest/{user_id}/{id_service}
```
**Path parameters**:
- `user_id`: User ID
- `id_service`: Service ID

**Response**:
```json
{
  "success": true,
  "message": "Guest access removed successfully",
  "deleted_count": 1
}
```

**Example (curl)**:
```bash
# Add
curl -X POST "http://127.0.0.1:8010/api/admin/add-guest/2/3" \
  -H "Content-Type: application/json" \
  -d '{"id_aplikasi": 1}'

# List
curl "http://127.0.0.1:8010/api/admin/guest-list"

# Remove
curl -X DELETE "http://127.0.0.1:8010/api/remove-guest/2/3"
```

