# Django Girls Sierra Leone &bull; Community Management Platform
**Course:** DCOMP207 Web Programming &bull; Final Project (35% / 100 Marks)  
**Institution:** Limkokwing University of Creative Technology &bull; Faculty of ICT  
**Group:** 7 &bull; **Class/Programme:** BIT2101 (Semester 05)  
**Examiner:** Oluwatosin Babarinde Ayorinde &bull; **Checked by:** Sheik Umar Kanu  

---

## 1. Project Overview
This project is the direct continuation and full-stack transformation of **Assignment 1** (PHP Authentication Website for Django Girls Sierra Leone) into a comprehensive, database-driven **Community and Organisation Management Platform** built in PHP and MySQL.

The application satisfies 100% of the marking rubric requirements across authentication, dynamic member dashboards, curriculum progress tracking, event RSVPs, learning resources, community discussion forums, mentor-mentee matching, and an administrative analytics console.

---

## 2. System Architecture & Tech Stack
- **Backend:** PHP 8 (PDO with Prepared Statements, password_hash with bcrypt, session security).
- **Database:** MySQL / MariaDB (`djangogirls_db` with 10 relational tables, 3NF, foreign keys).
- **Frontend:** Semantic HTML5, CSS3 Custom Properties (Standardized on **Tahoma** font, 12pt per brief instructions), Vanilla JavaScript.
- **Environment:** XAMPP (Apache & MySQL).

---

## 3. Core Modules & Features

| # | Module / Feature | Script File | Description |
|---|---|---|---|
| 1 | **User Registration** | `register.php` | Sign up with role selection (Member/Mentor), client & server validation, password hashing, and auto-enrollment in tracks. |
| 2 | **Login Authentication** | `login.php` | Authentication against bcrypt hash, session regeneration, remember me, and role redirection. |
| 3 | **Member Profiles** | `profile.php` | Profile page, avatar image upload with MIME validation, bio, skills, and social links. |
| 4 | **Logout Functionality** | `logout.php` | Secure session termination and cookie invalidation. |
| 5 | **Personalized Dashboard** | `dashboard.php` | Progress tracker with interactive module completion, registered events, and mentorship status. |
| 6 | **Events & Workshops** | `events.php` | Workshop calendar, category filter, 1-click RSVP with capacity controls and cancellation. |
| 7 | **Learning Library** | `resources.php` | Searchable and filterable repository of coding cheat sheets, guides, and tutorial videos. |
| 8 | **Community Forum** | `community.php`, `thread.php` | Discussion boards, view counters, thread creation, and nested replies. |
| 9 | **Mentorship Program** | `mentorship.php` | Mentor directory, 1-on-1 request modal, and mentor acceptance action center. |
| 10 | **Administrative Panel** | `admin/*.php` | User management, workshop scheduler, resource manager, and downloadable CSV analytics reports. |

---

## 4. Database Setup & Installation Guide

### Step 1: Start XAMPP
Open the **XAMPP Control Panel** and click **Start** for both **Apache** and **MySQL**.

### Step 2: Import Database Schema
1. Open your browser and navigate to `http://localhost/phpmyadmin/`.
2. Click on the **Import** tab (or create database `djangogirls_db`).
3. Choose the file `db.sql` located inside this project folder (`WEB-FINAL SEM5/db.sql`).
4. Click **Go** / **Import**. The database tables and seed accounts will be populated automatically.

### Step 3: Run the Application
Place this folder inside your XAMPP `htdocs` directory (e.g., `C:/xampp/htdocs/WEB-FINAL SEM5/`), or access via local server:
```
http://localhost/WEB-FINAL SEM5/index.php
```

---

## 5. Demonstration Test Accounts (For Examiner Evaluation)

| Role | Email Address | Password | Privileges |
|---|---|---|---|
| **System Administrator** | `admin@djangogirls.sl` | `Admin123!` | Full Admin Panel access, user role toggling, workshop management, CSV reports export. |
| **Senior Tech Mentor** | `mentor@djangogirls.sl` | `Mentor123!` | Mentor directory listing, accept/decline incoming mentee requests, forum moderation. |
| **Student Member (Alpha Jalloh)** | `alpha.jalloh@djangogirls.sl` | `Alpha123!` | Personalized dashboard, track progress tracker, workshop RSVPs, forum participation. |
| **New Learner (Fatmata Kamara)** | `fatmata.kamara@djangogirls.sl` | `Member123!` | Beginner student account with pending mentorship applications. |

---

## 6. A3 Professional Project Portfolio Documentation
Per the submission guidelines, a 5-page professional portfolio formatted in **Tahoma font, size 12** is provided in:
- `portfolio_documentation.html` (Open in any modern browser and press `Ctrl + P` to print or save as A3 Landscape PDF).
- Contains Section 1 (Organisation), Section 2 (Problem & Research), Section 3 (UI/UX), Section 4 (Interface Design), Section 5 (System Architecture & ERD), and Section 6 (Final Product & QR Codes).
