# API and Application Documentation

This document describes the REST API endpoints and web interface pages available for the LAMP stack application.

## Base URL

```
http://your-ec2-public-ip/
```

## API Endpoints

### List All Users

**Request:**

- Method: GET
- URL: `/api.php`

**Response:**

```json
{
  "success": true,
  "users": [
    {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com",
      "created_at": "2023-06-15 10:30:45"
    },
    {
      "id": 2,
      "name": "Jane Smith",
      "email": "jane@example.com",
      "created_at": "2023-06-15 11:20:15"
    }
  ]
}
```

### Create New User

**Request:**

- Method: POST
- URL: `/api.php`
- Headers:
  - Content-Type: application/json
- Body:

````

## Web Interface Pages

### Home Page

- **URL:** `/index.php` or `/`
- **Description:** Main application page with links to all features
- **Features:**
  - Server information display
  - Database connection test
  - Visit counter
  - Links to user management and system info

### View Users Page

- **URL:** `/view_users.php`
- **Description:** Displays all users in the database
- **Features:**
  - Tabular display of users
  - Server information display
  - Link to create new users

### PHP Info Page

- **URL:** `/info.php`
- **Description:** Displays detailed PHP configuration information
- **Features:**
  - PHP version
  - Server information
  - Loaded modules
  - Environment variables

### Health Check Page

- **URL:** `/healthcheck.php`
- **Description:** Simple health check endpoint for load balancer
- **Features:**
  - Returns "OK" when the server is healthy
  - Used by Application Load Balancer for health checks

## Error Responses

### Invalid Input

```json
{
  "error": "Name and email are required"
}
````

### Invalid Email Format

```json
{
  "error": "Invalid email format"
}
```

### Database Connection Error

```json
{
  "error": "Database connection failed: [error message]"
}
```

```

```
