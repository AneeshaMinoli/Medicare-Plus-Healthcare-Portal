# Medicare Plus

## Project Overview
Medicare Plus is a PHP/MySQL healthcare management portal designed for clinics and medical centers. It provides role-based access for administrators, doctors, and patients, enabling appointment booking, patient records, lab reports, messaging, and feedback management.

## Features
- Role-based authentication for Admin, Doctor, and Patient users
- Admin dashboard for user, doctor, patient, appointment, payment, and feedback management
- Doctor dashboard for patient listings, care management, appointment details, and lab report updates
- Patient dashboard for booking appointments, viewing lab results, submitting feedback, and contacting support
- Static healthcare service pages for Cardiology, Dermatology, Neurology, Orthopedics, Primary Care, and Surgery
- Secure user registration and login with hashed passwords
- Message center for patients and medical staff communication
- Feedback and review submission system
- Responsive page templates and professional health service UI

## Technologies Used
- PHP 8.x
- MySQL / MariaDB
- HTML5
- CSS3
- JavaScript
- XAMPP / WAMP server environment
- phpMyAdmin for database administration

## Modules
- `admin_dashboard.php` — Admin control panel for clinic operations
- `doctor_dashboard.php` — Doctor interface for appointments, patients, and reports
- `patient_dashboard.php` — Patient portal for booking, messaging, and feedback
- `login.php` — User authentication and registration
- `book_appointment.php` — Appointment booking and scheduling
- `view_lab_results.php` — Lab result access for patients and doctors
- `send_message.php` / `view_messages.php` — Internal messaging system
- `give_feedback.php` / `view_all_feedback.php` — Feedback submission and admin review
- `contactus.html`, `aboutus.html`, and service pages — informational healthcare pages

## How to Run
1. Install a local PHP development stack such as XAMPP, WAMP, or MAMP.
2. Place the project folder in your web server document root.
   - Example: `C:\xampp\htdocs\medicare-plus` or `C:\wamp64\www\medicare-plus`
3. Create a MySQL database named `medi_plus`.
4. Import the database schema into MySQL using a sanitized SQL export.
   - Do not publish the original data dump containing user records and emails.
5. Update database credentials in `medi_plus/config/db_connect.php` if needed:
   ```php
   $servername = "localhost";
   $username = "root";
   $password = "";
   $database = "medi_plus";
   ```
6. Open the application in a browser:
   - `http://localhost/medicare-plus/medi_plus/`
7. Use the app’s login and dashboard pages to verify access and functionality.

## Developer
- Built for portfolio presentation and professional deployment.
- Ready to demonstrate PHP-based healthcare system development.
- Designed to support future enhancements such as API integration, modern UI, and production-ready deployment.

## Project Purpose
Medicare Plus is intended to showcase a complete, functional healthcare appointment and management system. It demonstrates web application development skills, secure user role handling, database interactions, and practical clinic workflow automation.

## Notes for GitHub Upload
- Do not upload raw database backup files or internal database engine files.
- Keep sensitive environment files and runtime uploads out of version control.
- Use a sanitized schema-only SQL export if the database structure must be shared publicly.
