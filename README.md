# 📚 BookNest — AI-Powered Library Management System

**BookNest** is a full-stack **Laravel 12 Library Management System** designed to provide a modern digital library experience with secure authentication, role-based access control, book management, borrowing, favorites, personalized recommendations, and a built-in Rule-Based AI Library Assistant.

The system supports two roles:

* 👑 **Admin**
* 👤 **User**

BookNest combines a real database-driven library system with a deterministic recommendation engine and an intelligent rule-based assistant that works locally without requiring an external AI API.

---

## ✨ Features

### 👤 Authentication & Authorization

* User registration and login
* Secure logout
* Password hashing
* Session-based authentication
* Two roles: Admin and User
* Backend-enforced Admin authorization
* Unauthorized users receive `403 Forbidden`
* Users cannot access Admin routes by changing URLs or request parameters
* Public registration always creates a normal User account

---

### 📖 Book Management

Admins can:

* Create books
* Edit books
* Delete books
* Search books
* Manage book metadata
* Manage available and total copies
* Assign categories
* Assign authors
* Manage book covers

Users and visitors can:

* Browse books
* Search the library catalog
* Filter books
* View book details
* Check availability
* Browse books by category

---

### 🔎 Book Search

The library provides database-backed search across:

* Book title
* Description
* ISBN
* Author
* Category
* Availability

Search results support pagination and sorting.

---

### 📂 Categories

BookNest includes categories such as:

* Programming
* Artificial Intelligence
* Database
* Web Development
* Cyber Security
* Networking
* Business
* Science
* Literature
* Arabic Literature

Admins can create, edit, search, and delete categories.

---

### ✍️ Authors

Admins can manage authors through a dedicated CRUD system.

The system also protects authors that are still associated with books from unsafe deletion.

---

### 📚 Borrowing System

Authenticated users can borrow available books.

The system includes:

* Availability checking
* Automatic due dates
* 14-day borrowing period
* Duplicate active-loan prevention
* Transaction-safe borrowing
* Row locking for copy-count consistency
* Book return functionality
* Borrowing history
* Admin borrowing management

Available copies are automatically updated when books are borrowed or returned.

---

### ⏰ Overdue Books

A borrowing is considered **Overdue** automatically when:

* It has not been returned
* Its due date has passed

The application does not depend on manually updating an overdue status.

---

### ❤️ Favorites

Users can:

* Add books to favorites
* Remove books from favorites
* View their favorite books

Database constraints prevent duplicate favorites, and authorization prevents users from modifying another user's favorites.

---

## 🤖 Rule-Based AI Library Assistant

BookNest includes a built-in **Rule-Based AI Library Assistant**.

The assistant can help users with:

* Finding books
* Searching by author
* Searching by category
* Checking book availability
* Recommendations
* Comparing books
* Borrowing instructions
* Returning instructions
* Favorites
* Profile guidance
* General library help

### Admin AI Capabilities

Authenticated Admin users can also request authorized library statistics, such as:

* Total book copies
* Available copies
* Total users
* Top category
* Low-availability books

### 🔐 AI Security Architecture

The AI assistant follows a security-first flow:

```text
Authentication
      ↓
Authorization
      ↓
User Role
      ↓
Allowed Data & Operations
      ↓
AI Context
      ↓
AI Response
```

The AI layer:

* Does not directly query the database
* Does not execute SQL
* Does not determine user permissions
* Cannot grant itself Admin access
* Receives only data already authorized by the backend
* Rejects unauthorized Admin-only requests from normal users

This ensures that AI functionality does not become a way to bypass application security.

---

## 🧠 Personalized Recommendations

BookNest provides deterministic, database-backed recommendations based on the user's profile.

The recommendation engine considers information such as:

* Interests
* Favorite topics
* Skills
* Learning goals
* Preferred categories
* Book titles
* Book descriptions
* Authors
* Categories

Each recommendation receives a **match percentage** and can include an explanation of the matching interests/topics.

No random recommendation scores are used.

The recommendation system works locally without requiring an external AI service.

---

## 🖥️ User Experience

The application provides dedicated experiences for both visitors and authenticated users.

### Public Area

* Home page
* Book catalog
* Book details
* Categories
* Category details
* Login
* Registration

### User Area

* User dashboard
* Profile
* Recommendations
* Borrowings
* Favorites
* Chatbot

### Admin Area

* Admin dashboard
* User management
* Book management
* Category management
* Author management
* Borrowing management
* Library statistics

---

## 🎨 Frontend

BookNest uses a modern editorial-style library interface with a warm, premium visual design.

### Design Direction

* Warm cream backgrounds
* Deep green navigation
* Elegant typography
* Spacious layouts
* Clean cards
* Soft borders and shadows
* Responsive design
* Book-focused visual presentation

### Main Design Colors

| Purpose         | Color     |
| --------------- | --------- |
| Background      | `#F7F5F0` |
| Navbar          | `#24483F` |
| Primary         | `#6F8F85` |
| Secondary Beige | `#D8C7AD` |
| Cards           | `#FFFDF8` |
| Primary Text    | `#293330` |
| Secondary Text  | `#71807B` |
| Buttons         | `#315C50` |

### Typography

* **Playfair Display** — headings
* **Inter** — body text and UI

---

## 🛠️ Technology Stack

### Backend

* PHP 8.2+
* Laravel 12
* Laravel Sanctum
* Laravel Tinker
* Guzzle

### Frontend

* Blade
* Vite
* CSS
* JavaScript

### Database

The project supports:

* MySQL
* SQLite
* PostgreSQL-compatible Laravel database configuration

### Testing

* PHPUnit
* Laravel Feature Tests

---

## 🗂️ Project Structure

```text
BookNest-Final-RuleBased-AI/
└── booknest/
    ├── app/
    │   ├── Http/
    │   │   ├── Controllers/
    │   │   ├── Middleware/
    │   │   └── Requests/
    │   ├── Models/
    │   ├── Policies/
    │   ├── Providers/
    │   └── Services/
    │
    ├── bootstrap/
    ├── config/
    ├── database/
    │   ├── factories/
    │   ├── migrations/
    │   ├── seeders/
    │   └── seeders_data/
    │
    ├── public/
    │   ├── css/
    │   ├── images/
    │   └── storage/
    │
    ├── resources/
    │   └── views/
    │
    ├── routes/
    ├── tests/
    ├── composer.json
    ├── package.json
    └── artisan
```

---

## 🚀 Installation

### 1. Clone the Repository

```bash
git clone <YOUR_GITHUB_REPOSITORY_URL>
cd BookNest-Final-RuleBased-AI/booknest
```

### 2. Install PHP Dependencies

```bash
composer install
```

### 3. Create the Environment File

```bash
cp .env.example .env
```

On Windows PowerShell, you can use:

```powershell
Copy-Item .env.example .env
```

### 4. Generate the Application Key

```bash
php artisan key:generate
```

### 5. Configure the Database

By default, you can use SQLite for a simple local setup.

Set your `.env` database configuration accordingly:

```env
DB_CONNECTION=sqlite
DB_DATABASE=database/database.sqlite
```

Alternatively, configure MySQL:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=library-iti
DB_USERNAME=root
DB_PASSWORD=
```

### 6. Run Migrations and Seed the Database

```bash
php artisan migrate:fresh --seed
```

### 7. Create the Storage Link

```bash
php artisan storage:link
```

### 8. Install Frontend Dependencies

```bash
npm install
```

### 9. Build the Frontend

```bash
npm run build
```

### 10. Start Laravel

```bash
php artisan serve
```

The application will normally be available at:

```text
http://localhost:8000
```

---

## 💻 Frontend Development

For Vite development mode:

```bash
npm run dev
```

Run Laravel in another terminal:

```bash
php artisan serve
```

---

## 🔑 Demo Accounts

After running the database seeders, the following accounts are available:

### Admin

```text
Email: admin@booknest.test
Password: password
```

### User

```text
Email: demo@booknest.test
Password: password
```

### User

```text
Email: reader@booknest.test
Password: password
```

> These credentials are intended for local/demo development only. Change or remove seeded credentials before deploying the application publicly.

---

## 🧪 Running Tests

Run the Laravel test suite with:

```bash
php artisan test
```

The test suite covers important application workflows, including:

* Authentication
* Admin authorization
* Admin CRUD
* Book search
* Categories
* Authors
* Borrowing
* Returning books
* Duplicate borrowing prevention
* Unavailable books
* Overdue borrowing behavior
* Favorites ownership
* Recommendation matching
* Chatbot authorization
* Role spoofing attempts
* Rule-based AI fallback behavior

---

## 🔒 Security

Security is enforced on the backend rather than relying on frontend restrictions.

### Admin Protection

All Admin routes are protected by the `admin` middleware.

```text
Authenticated User
        ↓
Is Admin?
   ↙         ↘
 YES          NO
  ↓            ↓
Admin Area    403
```

A normal User cannot gain Admin access by:

* Changing a URL
* Modifying form data
* Sending custom requests
* Spoofing a role parameter
* Asking the chatbot for restricted information

The authenticated user's role is determined from the server-side `User` model.

---

## 🗃️ Database

The project contains migrations for:

* Users
* Cache
* Jobs
* Categories
* Authors
* Books
* User profiles
* Borrowings
* Favorites
* Chat messages
* Borrowing indexes

The database is populated using Laravel seeders.

Book seed data is stored with a dedicated manifest, while the project also preserves the supplied book-cover assets.

---

## 🖼️ Book Covers & Assets

The project includes real book-cover assets organized under:

```text
public/storage/books/
```

Book covers are mapped to the corresponding book records through the application's seeded book data.

Additional public assets include:

```text
public/images/logo.png
public/images/hero.jpg
public/images/reference-homepage.png
public/images/book-placeholder.svg
```

---

## 📊 Admin Dashboard

The Admin dashboard provides library-level information such as:

* Total books/copies
* Available copies
* Books by category
* Top category
* Low-availability books
* Total users
* Borrowing management

Admin functionality is separated from the normal User area and protected by backend authorization.

---

## 🏗️ Architecture

BookNest follows a layered Laravel architecture using:

* Routes
* Controllers
* Middleware
* Form Requests
* Models
* Policies
* Services
* Migrations
* Seeders
* Feature Tests
* Blade Views

Important application services include:

```text
AIService
ChatbotService
RecommendationService
```

The AI-related services are separated from database authorization logic to keep responsibilities clear and prevent the AI layer from controlling permissions.

---

## 📌 Current AI Approach

BookNest currently uses a **fully local Rule-Based AI Assistant**.

It does **not** require:

* OpenAI API
* Gemini API
* Anthropic API
* External AI model
* AI API key

This makes the assistant available immediately after installation without external API configuration.

The recommendation engine is also deterministic and database-backed.

---

## ⚠️ Important Notes

* Do not commit your real `.env` file to GitHub.
* Keep production credentials and secrets outside the repository.
* The included demo credentials are for development/testing.
* Before production deployment, configure a secure production environment.
* Run migrations and seeders only when appropriate for your environment.
* The application should be tested in the target production environment before deployment.

---

## 🧑‍💻 Development

Typical development workflow:

```bash
composer install
npm install

php artisan migrate:fresh --seed
php artisan storage:link

npm run dev
php artisan serve
```

Run tests with:

```bash
php artisan test
```

---

## 📦 Main Application Modules

```text
Authentication
│
├── Login
├── Registration
└── Logout

Library
│
├── Books
├── Categories
├── Authors
├── Search
└── Book Details

User
│
├── Dashboard
├── Profile
├── Recommendations
├── Borrowings
└── Favorites

Admin
│
├── Dashboard
├── Users
├── Books
├── Categories
├── Authors
└── Borrowings

AI Assistant
│
├── Book Search
├── Availability
├── Recommendations
├── Comparisons
├── Library Help
└── Admin Statistics
```

---

## 📜 License

This project is released under the **MIT License**.

See the `composer.json` file for the project license declaration.

---

## 👩‍💻 Project

**BookNest — AI-Powered Library Management System**

A Laravel-based digital library platform combining:

**Library Management + Role-Based Security + Borrowing + Favorites + Personalized Recommendations + Rule-Based AI Assistant**

---

<div align="center">

### ✨ Developed with passion by

Basmala El shenawy

<img src="assets/signature.png" width="180" alt="Basmala El shenawy Signature">

BookNest — Read • Learn • Grow

</div>
