# API Documentation

## Overview
This API provides endpoints for user authentication, department management, role/permission management, and a hierarchical request approval workflow system.

## Base URL
```
http://localhost:8000/api
```

## Authentication
All protected endpoints require authentication via Laravel Passport OAuth2. Include the access token in the Authorization header:

```
Authorization: Bearer {access_token}
```

## Response Format
All responses follow this format:

**Success Response:**
```json
{
  "success": true,
  "data": { ... },
  "message": "Optional success message"
}
```

**Error Response:**
```json
{
  "success": false,
  "message": "Error message",
  "errors": { ... }
}
```

---

## User Authentication & Management

### Register User
**POST** `/api/register`

Register a new user account.

**Request Body:**
```json
{
  "firstname": "John",
  "lastname": "Doe",
  "email": "john@example.com",
  "password": "password123",
  "password_confirmation": "password123"
}
```

**Response:** `201 Created`

### Login
**POST** `/api/login`

Authenticate user and receive access token.

**Request Body:**
```json
{
  "email": "john@example.com",
  "password": "password123"
}
```

**Response:** `200 OK`
```json
{
  "success": true,
  "data": {
    "token": "access_token_here",
    "user": { ... }
  }
}
```

### Logout
**GET** `/api/logout` (Protected)

Invalidate the current access token.

**Response:** `200 OK`

### Get Current User Data
**GET** `/api/fetchuserdata` (Protected)

Get the authenticated user's information.

**Response:** `200 OK`

### Forgot Password
**POST** `/api/forgot_password`

Request a password reset code.

**Request Body:**
```json
{
  "email": "john@example.com"
}
```

**Response:** `200 OK`

### Verify Email
**GET** `/api/verify_email`

Verify user email with code.

**Query Parameters:**
- `code` (string): Verification code

**Response:** `200 OK`

### Reset Password
**POST** `/api/reset_password`

Reset password with code.

**Request Body:**
```json
{
  "email": "john@example.com",
  "code": "reset_code",
  "password": "new_password123",
  "password_confirmation": "new_password123"
}
```

**Response:** `200 OK`

---

## User Management

### Get All Users
**GET** `/api/users` (Protected)
**Permissions:** `View_users`, `superadmin|sub_unit_head`

Get list of all users.

**Query Parameters:**
- `page` (integer): Page number for pagination
- `per_page` (integer): Items per page

**Response:** `200 OK`

### Get User by ID
**GET** `/api/users/{id}` (Protected)
**Permissions:** `View_users`, `superadmin|sub_unit_head`

Get specific user details.

**Response:** `200 OK`

### Update User
**PUT** `/api/users/{id}` (Protected)
**Permissions:** `Update_users`, `superadmin`

Update user information.

**Request Body:**
```json
{
  "firstname": "John",
  "lastname": "Doe",
  "email": "john.new@example.com",
  "department_id": 1
}
```

**Response:** `200 OK`

### Delete User
**DELETE** `/api/users/{id}` (Protected)
**Permissions:** `Delete_users`, `superadmin`

Soft delete a user.

**Response:** `200 OK`

### Assign Role to User
**POST** `/api/users/{id}/assign-role` (Protected)
**Permissions:** `Assign_role`, `superadmin`

Assign a role to a user.

**Request Body:**
```json
{
  "role": "manager"
}
```

**Response:** `200 OK`

### Remove Role from User
**POST** `/api/users/{id}/remove-role` (Protected)
**Permissions:** `Remove_role`, `superadmin`

Remove a role from a user.

**Request Body:**
```json
{
  "role": "manager"
}
```

**Response:** `200 OK`

### Sync User Roles
**POST** `/api/users/{id}/sync-roles` (Protected)
**Permissions:** `Assign_role`, `superadmin`

Replace all user roles with the provided roles.

**Request Body:**
```json
{
  "roles": ["manager", "editor"]
}
```

**Response:** `200 OK`

### Get User Roles
**GET** `/api/users/{id}/roles` (Protected)
**Permissions:** `View_users`, `superadmin|sub_unit_head`

Get all roles assigned to a user.

**Response:** `200 OK`

### Get User Permissions
**GET** `/api/users/{id}/permissions` (Protected)
**Permissions:** `View_users`, `superadmin|sub_unit_head`

Get all permissions for a user.

**Response:** `200 OK`

---

## Department Management

### Get All Departments
**GET** `/api/departments` (Protected)
**Permissions:** `View_dept`, `superadmin|sub_unit_head`

Get list of all departments.

**Response:** `200 OK`

### Create Department
**POST** `/api/departments` (Protected)
**Permissions:** `Create_dept`, `superadmin`

Create a new department.

**Request Body:**
```json
{
  "name": "Engineering",
  "description": "Software development department",
  "is_active": true
}
```

**Response:** `201 Created`

### Get Department by ID
**GET** `/api/departments/{id}` (Protected)
**Permissions:** `View_dept`, `superadmin|sub_unit_head`

Get specific department details.

**Response:** `200 OK`

### Update Department
**PUT** `/api/departments/{id}` (Protected)
**Permissions:** `Update_dept`, `superadmin|sub_unit_head`

Update department information.

**Request Body:**
```json
{
  "name": "Engineering",
  "description": "Updated description",
  "is_active": true
}
```

**Response:** `200 OK`

### Delete Department
**DELETE** `/api/departments/{id}` (Protected)
**Permissions:** `delete_dept`, `superadmin`

Soft delete a department.

**Response:** `200 OK`

### Assign Users to Department
**POST** `/api/departments/{id}/assign-users` (Protected)
**Permissions:** `assign_dept_to_user`, `superadmin|sub_unit_head`

Assign users to a department.

**Request Body:**
```json
{
  "user_ids": [1, 2, 3]
}
```

**Response:** `200 OK`

### Remove Users from Department
**POST** `/api/departments/{id}/remove-users` (Protected)
**Permissions:** `remove_user_from_dept`, `superadmin|sub_unit_head`

Remove users from a department.

**Request Body:**
```json
{
  "user_ids": [1, 2, 3]
}
```

**Response:** `200 OK`

---

## Role & Permission Management

### Get All Roles
**GET** `/api/roles` (Protected)
**Permissions:** `View_roles`, `superadmin`

Get list of all roles.

**Response:** `200 OK`

### Create Role
**POST** `/api/roles` (Protected)
**Permissions:** `Create_roles`, `superadmin`

Create a new role.

**Request Body:**
```json
{
  "name": "manager",
  "guard_name": "web"
}
```

**Response:** `201 Created`

### Get Role by ID
**GET** `/api/roles/{id}` (Protected)
**Permissions:** `View_roles`, `superadmin`

Get specific role details.

**Response:** `200 OK`

### Update Role
**PUT** `/api/roles/{id}` (Protected)
**Permissions:** `Update_roles`, `superadmin`

Update role information.

**Request Body:**
```json
{
  "name": "senior_manager",
  "guard_name": "web"
}
```

**Response:** `200 OK`

### Delete Role
**DELETE** `/api/roles/{id}` (Protected)
**Permissions:** `Delete_roles`, `superadmin`

Delete a role.

**Response:** `200 OK`

### Assign Permissions to Role
**POST** `/api/roles/{id}/assign-permissions` (Protected)
**Permissions:** `Assign_permissions`, `superadmin`

Assign permissions to a role.

**Request Body:**
```json
{
  "permissions": ["View_users", "Create_users", "Update_users"]
}
```

**Response:** `200 OK`

### Get Role Permissions
**GET** `/api/roles/{id}/permissions` (Protected)
**Permissions:** `View_permissions`, `superadmin`

Get all permissions assigned to a role.

**Response:** `200 OK`

### Get All Permissions
**GET** `/api/permissions` (Protected)
**Permissions:** `View_permissions`, `superadmin`

Get list of all available permissions.

**Response:** `200 OK`

---

## Request Management

### Get All Requests
**GET** `/api/requests` (Protected)

Get list of requests based on user role:
- Superadmin: All requests
- Department head: Requests in their department
- Regular users: Their own requests

**Query Parameters:**
- `status` (string): Filter by status (pending, approved, rejected)
- `page` (integer): Page number for pagination

**Response:** `200 OK`
```json
{
  "success": true,
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": 1,
        "title": "Budget Approval",
        "description": "Request for Q4 budget",
        "status": "pending",
        "current_approval_level": 1,
        "user": { ... },
        "department": { ... },
        "approvals": [ ... ]
      }
    ],
    "total": 10
  }
}
```

### Create Request
**POST** `/api/requests` (Protected)

Submit a new request for approval.

**Request Body:**
```json
{
  "title": "Budget Approval",
  "description": "Request for Q4 budget approval",
  "department_id": 1
}
```

**Response:** `201 Created`
```json
{
  "success": true,
  "message": "Request submitted successfully",
  "data": {
    "id": 1,
    "title": "Budget Approval",
    "status": "pending",
    "current_approval_level": 1,
    "submitted_at": "2024-01-15T10:00:00Z"
  }
}
```

### Get Request by ID
**GET** `/api/requests/{id}` (Protected)

Get specific request details with approval history.

**Response:** `200 OK`

### Get My Pending Approvals
**GET** `/api/requests/pending/my-approvals` (Protected)

Get all requests pending approval by the current user.

**Response:** `200 OK`
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "request": {
        "id": 1,
        "title": "Budget Approval",
        "user": { ... },
        "department": { ... }
      },
      "approval_level": {
        "id": 1,
        "name": "Level 1",
        "level": 1
      },
      "status": "pending"
    }
  ]
}
```

### Get Request Statistics
**GET** `/api/requests/statistics` (Protected)

Get request statistics based on user role.

**Response:** `200 OK`
```json
{
  "success": true,
  "data": {
    "total": 50,
    "pending": 15,
    "approved": 30,
    "rejected": 5
  }
}
```

---

## Approval/Rejection Actions

### Approve Request
**POST** `/api/requests/{requestId}/approve` (Protected)

Approve a request. If all approvers at current level approve, request moves to next level.

**Request Body:**
```json
{
  "comments": "Approved - budget looks reasonable"
}
```

**Response:** `200 OK`
```json
{
  "success": true,
  "message": "Request approved successfully",
  "data": {
    "id": 1,
    "status": "pending",
    "current_approval_level": 2
  }
}
```

### Reject Request
**POST** `/api/requests/{requestId}/reject` (Protected)

Reject a request. This immediately marks the request as rejected and skips all remaining approvals.

**Request Body:**
```json
{
  "comments": "Rejected - budget exceeds limit"
}
```

**Response:** `200 OK`
```json
{
  "success": true,
  "message": "Request rejected successfully",
  "data": {
    "id": 1,
    "status": "rejected",
    "rejection_reason": "Rejected - budget exceeds limit",
    "completed_at": "2024-01-15T11:00:00Z"
  }
}
```

### Get Approval History
**GET** `/api/requests/{requestId}/approvals/history` (Protected)

Get the complete approval history for a request.

**Response:** `200 OK`
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "approver": {
        "id": 5,
        "firstname": "Jane",
        "lastname": "Smith"
      },
      "approval_level": {
        "id": 1,
        "name": "Level 1",
        "level": 1
      },
      "status": "approved",
      "comments": "Approved",
      "actioned_at": "2024-01-15T10:30:00Z"
    }
  ]
}
```

---

## Approver Hierarchy Setup

### Get All Approval Levels
**GET** `/api/approval-levels` (Protected)

Get approval levels for departments based on user role.

**Query Parameters:**
- `department_id` (integer): Filter by department

**Response:** `200 OK`
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "department_id": 1,
      "level": 1,
      "name": "Team Lead",
      "description": "First level approval",
      "is_active": true,
      "approvers": [
        {
          "id": 1,
          "user": {
            "id": 5,
            "firstname": "Jane",
            "lastname": "Smith"
          },
          "priority": 1
        }
      ]
    }
  ]
}
```

### Create Approval Level
**POST** `/api/approval-levels` (Protected)
**Permissions:** `superadmin|sub_unit_head`

Create a new approval level for a department.

**Request Body:**
```json
{
  "department_id": 1,
  "level": 1,
  "name": "Team Lead",
  "description": "First level approval"
}
```

**Response:** `201 Created`

### Get Approval Level by ID
**GET** `/api/approval-levels/{id}` (Protected)

Get specific approval level details with approvers.

**Response:** `200 OK`

### Update Approval Level
**PUT** `/api/approval-levels/{id}` (Protected)
**Permissions:** `superadmin|sub_unit_head`

Update approval level information.

**Request Body:**
```json
{
  "name": "Senior Team Lead",
  "description": "Updated description",
  "is_active": true
}
```

**Response:** `200 OK`

### Delete Approval Level
**DELETE** `/api/approval-levels/{id}` (Protected)
**Permissions:** `superadmin`

Delete an approval level.

**Response:** `200 OK`

### Assign Approvers to Approval Level
**POST** `/api/approval-levels/{id}/assign-approvers` (Protected)
**Permissions:** `superadmin|sub_unit_head`

Assign users as approvers to an approval level.

**Request Body:**
```json
{
  "user_ids": [5, 6, 7],
  "priorities": [1, 2, 3]
}
```

**Response:** `200 OK`

### Remove Approver from Approval Level
**DELETE** `/api/approval-levels/{approvalLevelId}/approvers/{userId}` (Protected)
**Permissions:** `superadmin|sub_unit_head`

Remove a user as approver from an approval level.

**Response:** `200 OK`

---

## Profile Management

### Edit Profile
**PUT** `/api/editprofile` (Protected)

Update user profile information.

**Request Body:**
```json
{
  "firstname": "John",
  "lastname": "Doe",
  "email": "john.new@example.com"
}
```

**Response:** `200 OK`

### Upload Profile Image
**POST** `/api/uploadprofileimage` (Protected)

Upload a profile image.

**Request Body:**
```
multipart/form-data
- image: file
```

**Response:** `200 OK`

### Get Profile Image
**GET** `/api/profileImage` (Protected)

Get the user's profile image.

**Response:** `200 OK`

### Fetch Users List
**GET** `/api/fectchuserslist` (Protected)
**Permissions:** `View_users`, `superadmin|sub_unit_head`

Get list of users for profile management.

**Response:** `200 OK`

---

## Error Codes

| Status Code | Description |
|-------------|-------------|
| 200 | Success |
| 201 | Created |
| 400 | Bad Request |
| 401 | Unauthorized |
| 403 | Forbidden |
| 404 | Not Found |
| 422 | Validation Error |
| 500 | Internal Server Error |

## Workflow Example

### Complete Request Approval Workflow

1. **Setup Approval Hierarchy**
   ```bash
   POST /api/approval-levels
   {
     "department_id": 1,
     "level": 1,
     "name": "Team Lead"
   }
   
   POST /api/approval-levels
   {
     "department_id": 1,
     "level": 2,
     "name": "Manager"
   }
   
   POST /api/approval-levels/{id}/assign-approvers
   {
     "user_ids": [5],
     "priorities": [1]
   }
   ```

2. **Submit Request**
   ```bash
   POST /api/requests
   {
     "title": "Budget Approval",
     "description": "Q4 budget request"
   }
   ```

3. **Level 1 Approval**
   ```bash
   POST /api/requests/{id}/approve
   {
     "comments": "Approved"
   }
   ```

4. **Level 2 Approval**
   ```bash
   POST /api/requests/{id}/approve
   {
     "comments": "Final approval"
   }
   ```

5. **Request Status**: Approved

### Rejection Workflow

1. **Submit Request** (as above)

2. **Level 1 Rejection**
   ```bash
   POST /api/requests/{id}/reject
   {
     "comments": "Budget exceeds limit"
   }
   ```

3. **Request Status**: Rejected (all remaining approvals skipped)
