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

## User Management Endpoints

### 1. List All Users
Retrieves a list of all registered users.

- **URL**: `/admin/api/users`
- **Method**: `GET`
- **Success Response**:
  - **Code**: `200 OK`
  - **Content**:
    ```json
    {
      "success": true,
      "data": [
        {
          "id": 1,
          "name": "John Doe",
          "email": "john@example.com",
          "has_sandwich_bar": true,
          "created_at": "2026-04-13T03:56:53.000000Z",
          "updated_at": "2026-04-13T03:56:53.000000Z"
        }
      ]
    }
    ```

### 2. Create User
Adds a new user to the system.

- **URL**: `/admin/api/users`
- **Method**: `POST`
- **Body Parameters**:
  | Parameter | Type | Required | Description |
  |-----------|------|----------|-------------|
  | `name` | `string` | Yes | The user's full name. |
  | `email` | `string` | Yes | A unique email address. |
  | `password` | `string` | Yes | Minimum 8 characters. |
  | `has_sandwich_bar` | `boolean` | No | Default is `false`. |
- **Success Response**:
  - **Code**: `201 Created`
  - **Content**:
    ```json
    {
      "success": true,
      "message": "User created successfully",
      "data": { "id": 2, "name": "New User", ... }
    }
    ```
- **Error Response**:
  - **Code**: `422 Unprocessable Entity` (Validation failed)

### 3. Update User
Edits an existing user's information.

- **URL**: `/admin/api/users/{id}`
- **Method**: `PUT`
- **Body Parameters** (All optional):
  | Parameter | Type | Description |
  |-----------|------|-------------|
  | `name` | `string` | The user's updated name. |
  | `email` | `string` | The user's updated unique email address. |
  | `password` | `string` | The user's updated password. |
  | `has_sandwich_bar` | `boolean` | Updated sandwich bar access status. |
- **Success Response**:
  - **Code**: `200 OK`
  - **Content**:
    ```json
    {
      "success": true,
      "message": "User updated successfully",
      "data": { ... }
    }
    ```
- **Error Response**:
  - **Code**: `404 Not Found` (User doesn't exist)
  - **Code**: `422 Unprocessable Entity` (Validation failed)

### 4. Delete User
Removes a user from the system.

- **URL**: `/admin/api/users/{id}`
- **Method**: `DELETE`
- **Success Response**:
  - **Code**: `200 OK`
  - **Content**:
    ```json
    {
      "success": true,
      "message": "User deleted successfully"
    }
    ```
- **Error Response**:
  - **Code**: `404 Not Found` (User doesn't exist)
