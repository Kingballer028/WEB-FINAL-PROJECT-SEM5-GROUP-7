# WEB PROGRAMMING ASSIGNMENT 1
## PHP Authentication System and Dashboard
### Django Girls Sierra Leone

**Student:** [Your First Name] [Your Last Name]  
**Programme:** [Your Programme]  
**Semester:** [Semester]  
**ID:** [Your ID]  
**Year:** 2026

## 1. Introduction

This project is a basic web platform developed for the Web Programming Assignment 1. The system demonstrates a user registration page, login authentication, personalized dashboard and logout function using PHP and MySQL.

The design uses Django Girls branding as required by the assignment. The main aim is to demonstrate the web programming concepts covered in the first four weeks.

## 2. Objectives

The objectives of the project are:

1. To create a simple user registration form.
2. To create a login form for authentication.
3. To display personalized information after a successful login.
4. To provide a logout function that ends the user's session.
5. To protect user passwords using password hashing.
6. To create a simple and clear user interface.

## 3. Page Structure

### Home Page

The home page introduces Django Girls Sierra Leone. It contains the Django Girls logo/brand, a short description, a Sign Up button and a Login button.

### Sign-Up Page

The sign-up page collects the user's full name, email address, password and password confirmation. The system checks that all fields are completed, the email is valid, the password has a minimum length and both passwords match.

After successful registration, the password is stored as a secure hash rather than plain text.

### Login Page

The login page accepts the registered email and password. The system searches for the user and verifies the password. If the details are correct, a PHP session is created and the user is redirected to the dashboard.

### Dashboard

The dashboard is protected by a session check. It displays the logged-in user's name, email and account status. It also contains simple information about getting started and account security.

### Logout

The logout function clears the session and redirects the user to the login page. A user who tries to access the dashboard after logging out is sent back to the login page.

## 4. Design Choices

The website uses a simple layout so that users can understand the pages easily. The same navigation, buttons, cards and footer style are used across the pages to provide consistency.

Tahoma is used as the main font, in line with the assignment formatting requirement. The layout is also responsive, meaning it can adjust to smaller screens.

The footer contains the copyright format required by the assignment:

© [Your First Name] [Your Last Name], [Program], [Semester], [ID], [Year]

Replace the bracketed information with your own details before submission.

## 5. Security

The system includes the following security measures:

- `password_hash()` is used instead of saving passwords directly.
- `password_verify()` is used to verify login passwords.
- Prepared SQL statements are used when working with user input.
- The dashboard checks whether a valid session exists.
- The session ID is regenerated after successful login.
- `htmlspecialchars()` is used when displaying user information.

These measures reduce common security problems in a basic PHP authentication system.

## 6. Database

The project uses a MySQL database called `django_girls`.

The `users` table contains:

| Field | Purpose |
|---|---|
| id | Unique user number |
| name | User's full name |
| email | User's login email |
| password | Hashed password |
| created_at | Registration date and time |

## 7. Testing

The following tests should be performed before submission:

| Test | Expected Result |
|---|---|
| Open home page | Home page loads |
| Submit empty registration | Error message appears |
| Register valid user | Registration succeeds |
| Register same email again | Duplicate email is rejected |
| Login with correct details | Dashboard opens |
| Login with wrong password | Error message appears |
| Open dashboard without login | User is redirected to login |
| Click logout | Session ends and login page opens |

## 8. Screenshot Documentation

The printed submission should include screenshots of the working system as requested by the assignment. Take screenshots of:

1. Home page showing the Django Girls logo/branding.
2. Sign-up page.
3. Login page.
4. Dashboard showing your name and email.
5. Login page after logout, if needed to demonstrate the session has ended.

Place the screenshots in the final report and give each one a short caption.

## 9. Conclusion

The project demonstrates a basic PHP authentication system with registration, login, dashboard and logout functionality. It also demonstrates database connectivity, sessions, password hashing and a simple responsive interface. The system meets the main functional requirements of the assignment and can be extended later with more user features.
