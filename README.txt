WEB PROGRAMMING ASSIGNMENT 1
PHP AUTHENTICATION SYSTEM AND DASHBOARD
Django Girls Sierra Leone

FILES
------
index.php       Home page
register.php    User registration
login.php       User login
dashboard.php   Protected personalized dashboard
logout.php      Ends the user session
config.php      MySQL database connection
style.css       Page design
db.sql          Database and users table

HOW TO RUN USING XAMPP
----------------------
1. Install XAMPP.
2. Start Apache and MySQL from the XAMPP Control Panel.
3. Copy this project folder into:
   C:\xampp\htdocs\

4. Open phpMyAdmin:
   http://localhost/phpmyadmin

5. Create/import the database:
   - Select the Import option.
   - Choose db.sql.
   - Run the import.

6. Open the project in a browser:
   http://localhost/web_programming_assignment_1_solution/

7. Click Sign Up and create a test account.
8. Log in using that account.
9. Confirm that the dashboard displays your name and email.
10. Click Logout and confirm that the session ends.

IMPORTANT
---------
If your MySQL username/password is different, edit config.php.

SECURITY FEATURES USED
----------------------
- password_hash() stores passwords as secure hashes.
- password_verify() checks passwords during login.
- Prepared statements are used for database queries.
- Sessions protect the dashboard.
- session_regenerate_id(true) is used after successful login.
- Output is escaped with htmlspecialchars().
