# Loove - Application de Rencontre

## Description
Loove is a dating application designed to help users find compatible partners. It features profile creation, advanced search filters, real-time chat, and premium subscription options.

## Project Structure
The application follows the MVC (Model-View-Controller) architecture.
- `/app`: Contains the core application logic (Controllers, Models, Views).
  - `/controllers`: Handles user requests and interacts with models and views.
  - `/models`: Manages data interaction with the database.
  - `/views`: Contains the presentation layer (HTML templates).
- `/config`: Configuration files (database credentials, application settings).
- `/core`: Core framework classes (Router, Controller base, Model base, App bootstrap, ErrorHandler).
- `/public`: Web server's document root. Contains the entry point (`index.php`), CSS, JavaScript, and images.
  - `/css`: Stylesheets.
  - `/js`: JavaScript files.
  - `/img`: Image assets.
- `/logs`: Application and error log files.
- `/docs`: User and technical documentation.

## Technical Stack
- **Frontend**: HTML, CSS, JavaScript (Vanilla JS, Object-Oriented principles)
- **Backend**: PHP (Vanilla PHP, Object-Oriented principles)
- **Database**: MySQL
- **Architecture**: MVC

## Setup
1.  **Clone the repository:**
    ```bash
    git clone <your-gitlab-repo-url> app-loove
    cd app-loove
    ```
2.  **Database Setup:**
    - Create a MySQL database (e.g., `loove_db`).
    - Import the `loove_database.sql` file into your database.
    - Configure database credentials in `config/config.php`:
      ```php
      define('DB_HOST', 'localhost');
      define('DB_NAME', 'loove_db'); // Your database name
      define('DB_USER', 'root');     // Your database username
      define('DB_PASS', '');       // Your database password
      ```
3.  **Web Server Configuration:**
    - Point your web server's document root to the `public` directory (e.g., `c:\xampp\htdocs\loove\public`).
    - Ensure `mod_rewrite` (Apache) or equivalent is enabled for URL rewriting. The `.htaccess` file in `public` handles this for Apache.
    - The application URL should be configured in `config/config.php`:
      ```php
      define('APP_URL', 'http://localhost/loove/public'); // Adjust if your setup is different
      ```
4.  **Permissions:**
    - Ensure the `logs` directory is writable by the web server.
    - Ensure the `public/img/profiles` directory is writable by the web server if you implement profile picture uploads.

5.  **Access the application:**
    Open your browser and navigate to `http://localhost/loove/public` (or your configured `APP_URL`).

## Features (Planned)
- User Authentication (Registration, Login)
- Profile Management (Create, Edit, Delete)
- Advanced Search & Filtering
- Geolocation-based Matching
- Real-time Chat
- "Like" and "Match" System
- Premium Subscriptions
- Notifications (Push & Email)
- Moderation & Security (Reporting, Admin Panel)
- User & Admin Dashboards

## Error Logging
- PHP errors are logged to `logs/error.log`.
- Application-specific actions (e.g., user login, admin actions) are logged to `logs/app.log`.
- Ensure the `logs` directory is writable by the web server.

## No Frameworks/Libraries
This project is built using pure PHP, JavaScript, HTML, and CSS, without any external frameworks (like Laravel, Symfony, React, Vue, jQuery, Bootstrap, etc.).

## Documentation
- **User Documentation**: `docs/user_documentation.md`
- **Technical Documentation**: `docs/technical_documentation.md`

## Presentation
A presentation support will be created for the final defense.
