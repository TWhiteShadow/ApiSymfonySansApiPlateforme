![alt text](https://raw.githubusercontent.com/TWhiteShadow/ApiSymfonySansApiPlateforme/refs/heads/master/public/upload/images/Screenshot%202025-03-02%20at%2019-20-32%20Mailtrap%20-%20Email%20Delivery%20Platform.png)

# 🎮 ApiSymfonySansApiPlateforme

A Symfony API to fetch upcoming video games and send weekly updates to subscribed users!

## 🚀 Features

- ✅ Full CRUD operations for video games
- 🔒 Authentication system to secure sensitive operations
- 🖼️ Image upload for game covers
- 📨 Weekly newsletter with upcoming game releases
- ⚡ Cache system to optimize performance
- 📖 Swagger/OpenAPI documentation
- 🏗️ Fixtures for test data

## 📋 Prerequisites

Make sure you have the following installed:

- 🐘 PHP 8.2 or higher
- 🎼 Composer
- 🐬 MySQL/MariaDB
- 🌍 Symfony CLI

## 📦 Installation

1. **Clone the project**
   ```sh
   git clone https://github.com/yourusername/ApiSymfonySansApiPlateforme.git
   cd ApiSymfonySansApiPlateforme
   ```

2. **Install dependencies**
   ```sh
   composer install
   ```

3. **Configure the database** in `.env`

4. **Create and load the database**
   ```sh
   php bin/console doctrine:database:create
   php bin/console doctrine:migrations:migrate
   php bin/console doctrine:fixtures:load
   ```
5. **And finally, serve the app !**
   ```sh
   symfony serve
   ```
## 🔑 Users & Authentication

### Default Users

| Email               | Role           | Password   | Subscribed |
|---------------------|---------------|-----------|------------|
| admin@example.com  | ROLE_ADMIN     | adminpass | No         |
| user@example.com   | ROLE_USER      | userpass  | Yes        |
| moderator@example.com | ROLE_MODERATOR | modpass  | No         |

### Authentication

To use protected API routes, first log in:

**POST** `http://127.0.0.1:8000/api/v1/login_check`

#### Request Body:
```json
{
    "email": "admin@example.com",
    "password": "adminpass"
}
```
#### Response:
```json
{
    "token": "your_jwt_token_here"
}
```
Use this token for authorization in all protected routes.

## 🔗 Common API Routes

| Endpoint | Description | Methods |
|----------|------------|---------|
| `/api/v1/video-games` | Manage video games | `GET`, `POST`, `PUT`, `DELETE` |
| `/api/v1/users` | Manage users | `GET`, `POST`, `PUT`, `DELETE` |
| `/api/v1/editors` | Manage editors | `GET`, `POST`, `PUT`, `DELETE` |
| `/api/v1/categories` | Manage categories | `GET`, `POST`, `PUT`, `DELETE` |

## 🛠 Tools
- 📝 **API Documentation:** Swagger/OpenAPI (`/api/docs`)
- 🏎 **Run Locally:** `symfony serve`
- 🕵️ **Test Requests:** Use Postman or cURL

Happy coding! 🎉

