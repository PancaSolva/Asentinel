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

## 6. Guest Access Management

Manage guest-level permissions that grant a user read-only/viewer access to a specific **Aplikasi**.  
All routes sit inside the `api/admin` prefix and require **Sanctum authentication**.

- **Base URL**: `/api/admin`
- **Authentication**: Bearer token (Sanctum) — include `Authorization: Bearer <token>` header
- **Required Header**: `Accept: application/json`

---

### 6.1 List All Guest Access

Retrieve every guest-access record. Results are cached until the cache is invalidated by an add or remove operation.

```
GET /api/admin/guest-list
```

**Parameters**: None

**Success Response** (`200 OK`):
```json
[
  {
    "premission_id": 1,
    "id": 2,
    "id_aplikasi": 1,
    "user": {
      "id": 2,
      "name": "John Doe",
      "email": "john@example.com"
    },
    "aplikasi": {
      "id_aplikasi": 1,
      "nama": "My App",
      "deskripsi": "Application description",
      "tipe": "web",
      "status": "up"
    }
  }
]
```

**Error Responses**:
| Status | Body | Reason |
|--------|------|--------|
| `401`  | `{"message": "Unauthenticated."}` | Missing or invalid Sanctum token |

---

### 6.2 Add Guest Access

Grant a user guest access to a specific Aplikasi. Uses `firstOrCreate` — if the exact combination already exists, a `409` is returned instead of a duplicate.

```
POST /api/admin/add-guest
```

**Body Parameters** (JSON):
| Field | Type | Required | Validation |
|-------|------|----------|------------|
| `id` | integer | ✅ | Must exist in `users` table |
| `id_aplikasi` | integer | ✅ | Must exist in `aplikasi` table |

**Request Example**:
```json
{
  "id": 2,
  "id_aplikasi": 1
}
```

**Success Response** (`200 OK`):
```json
{
  "success": true,
  "data": {
    "premission_id": 5,
    "id": 2,
    "id_aplikasi": 1,
    "user": {
      "id": 2,
      "name": "John Doe",
      "email": "john@example.com"
    },
    "aplikasi": {
      "id_aplikasi": 1,
      "nama": "My App"
    }
  }
}
```

**Error Responses**:
| Status | Body | Reason |
|--------|------|--------|
| `401`  | `{"error": "Unauthorized"}` | Missing or invalid Sanctum token |
| `409`  | `{"error": "Guest access already exists"}` | The user already has access to this Aplikasi |
| `422`  | `{"errors": {"id": ["The id field is required."]}}` | Validation failure |
| `500`  | `{"error": "Database error"}` or `{"error": "Failed to create guest"}` | Server-side error |

---

### 6.3 Remove Guest Access

Revoke a user's guest access to a specific Aplikasi. The matching record is deleted from the `web_guests` table.

```
DELETE /api/admin/remove-guest
```

**Body Parameters** (JSON):
| Field | Type | Required | Validation |
|-------|------|----------|------------|
| `id` | integer | ✅ | Must exist in `users` table |
| `id_aplikasi` | integer | ✅ | Must exist in `aplikasi` table |

**Request Example**:
```json
{
  "id": 2,
  "id_aplikasi": 1
}
```

**Success Response** (`200 OK`):
```json
{
  "success": true,
  "message": "Guest access removed successfully"
}
```

**Error Responses**:
| Status | Body | Reason |
|--------|------|--------|
| `401`  | `{"message": "Unauthenticated."}` | Missing or invalid Sanctum token |
| `404`  | `{"error": "Record not found"}` | No matching guest-access record exists |
| `422`  | `{"errors": {"id_aplikasi": [...]}}` | Validation failure |

---

### Caching Behaviour

- **`guest-list`** results are cached indefinitely (`Cache::rememberForever`).
- The cache key `guest_list` is automatically **invalidated** whenever `add-guest` or `remove-guest` completes successfully.

---

### Example Usage (curl)

```bash
# 1. Login to obtain a Sanctum token
TOKEN="your-sanctum-token"

# 2. List all guest access records
curl -s -X GET "http://127.0.0.1:8010/api/admin/guest-list" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json"

# 3. Grant user (id=2) guest access to Aplikasi (id_aplikasi=1)
curl -s -X POST "http://127.0.0.1:8010/api/admin/add-guest" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{"id": 2, "id_aplikasi": 1}'

# 4. Revoke that access
curl -s -X DELETE "http://127.0.0.1:8010/api/admin/remove-guest" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{"id": 2, "id_aplikasi": 1}'
```

---

### Database Schema (`web_guests`)

| Column | Type | Description |
|--------|------|-------------|
| `premission_id` | bigint (PK, auto-increment) | Primary key |
| `id` | bigint (FK → `users.id`) | The user receiving guest access. Cascades on delete. |
| `id_aplikasi` | bigint (FK → `aplikasi.id_aplikasi`) | The application the user is granted access to. Cascades on delete. |

### Related Models

- **`WebGuest`** — `App\Models\WebGuest` (table: `web_guests`)
  - `belongsTo` → `User` (via `id`)
  - `belongsTo` → `Aplikasi` (via `id_aplikasi`)
- **`GuestController`** — `App\Http\Controllers\GuestController`

