# ForgeMVC (Mini-Laravel)

A lightweight, custom PHP MVC framework built from scratch, inspired by Laravel. It is designed to be simple, fast, and easy to understand, providing essential features for modern web development without the overhead of a massive framework.

## 🌟 Features

- **MVC Architecture**: Clear separation of concerns with Models, Views, and Controllers.
- **Routing System**: Simple and intuitive route definitions.
- **Middleware**: Built-in support for CSRF protection and request filtering (like Authentication).
- **Dependency Injection**: Built-in IoC container with auto-wiring capabilities for controllers.
- **Database & Migrations**: PDO-based database connection and a custom migration system (`forge`).
- **Queue Worker**: Background job processing via a database-driven queue (`forge queue:work`).
- **Templating**: Lightweight view compiler and layout rendering system.

## 🚀 Getting Started

### Prerequisites
- **PHP >= 8.2**
- **Composer** (Dependency manager for PHP)
- Database (SQLite, MySQL, or PostgreSQL)

### Installation

1. **Clone the repository:**
   ```bash
   git clone https://github.com/LevanBedinashvili/ForgeMVC.git
   cd ForgeMVC
   ```

2. **Install dependencies:**
   Run Composer to install the required packages and generate the autoloader.
   ```bash
   composer install
   ```

3. **Configure Environment:**
   Create a `.env` file in the root directory and configure your application settings (e.g., database credentials):
   ```env
   DB_HOST=127.0.0.1
   DB_NAME=your_database
   DB_USER=root
   DB_PASS=
   ```

4. **Run Migrations (Optional but Recommended):**
   Run the migration command to set up the necessary database tables (such as the `jobs` table for queues).
   ```bash
   php forge migrate
   ```

5. **Start the Development Server:**
   You can use PHP's built-in server to run the application locally:
   ```bash
   php -S localhost:8000 -t public
   ```
   Open your browser and navigate to `http://localhost:8000`. You should see the welcome page!

## 🛠 How to Use

### Routing

Define your application routes in `routes/web.php`:

```php
use App\Controllers\HomeController;
use Core\Router;

Router::get('/', [HomeController::class, 'index']);
Router::get('/hello/{name}', [HomeController::class, 'show']);
```

### Controllers and Views

Controllers reside in the `app/Controllers/` directory. They can return rendered views and receive auto-wired dependencies in their constructors:

```php
namespace App\Controllers;

use Core\Controller;
use App\Greeter;

class HomeController extends Controller
{
    public function __construct(protected Greeter $greeter) {}

    public function index(): string
    {
        return $this->render('home/index', [
            'title' => 'Home - ForgeMVC',
            'name'  => 'World',
        ]);
    }
}
```

Views are stored in `views/` and are rendered within layouts (like `views/layouts/main.php`).

### The `forge` Command Line Interface

The framework comes with a handy CLI tool called `forge` for running maintenance tasks. From your terminal, you can run:

- **Run all pending migrations:**
  ```bash
  php forge migrate
  ```
- **Create a new database migration:**
  ```bash
  php forge make:migration create_users_table
  ```
- **Start the Queue Worker** (for background jobs):
  ```bash
  php forge queue:work
  ```

## 🤝 Contributing
Contributions, issues, and feature requests are welcome. Feel free to fork the repository and submit Pull Requests!

## 📄 License
This project is open-source and available under the [MIT License](https://opensource.org/licenses/MIT).
