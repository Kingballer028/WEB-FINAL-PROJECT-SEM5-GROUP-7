-- =======================================================
-- DJANGO GIRLS SIERRA LEONE - COMMUNITY MANAGEMENT SYSTEM
-- Course: DCOMP207 Web Programming - Final Project
-- Student: Alpha Hamidu Jalloh (ID: 905005193, BIT2101)
-- Database Schema & Comprehensive Demonstration Seed Data
-- =======================================================

CREATE DATABASE IF NOT EXISTS djangogirls_db
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE djangogirls_db;

-- 1. USERS TABLE
CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('member', 'mentor', 'admin') NOT NULL DEFAULT 'member',
    bio TEXT NULL,
    phone VARCHAR(30) NULL,
    profile_picture VARCHAR(255) DEFAULT 'assets/default_avatar.svg',
    skills VARCHAR(255) NULL,
    github_url VARCHAR(255) NULL,
    linkedin_url VARCHAR(255) NULL,
    status ENUM('active', 'inactive', 'suspended') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. EVENTS & WORKSHOPS TABLE
CREATE TABLE IF NOT EXISTS events (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    description TEXT NOT NULL,
    event_date DATETIME NOT NULL,
    venue VARCHAR(200) NOT NULL,
    speaker VARCHAR(150) NOT NULL,
    capacity INT UNSIGNED DEFAULT 40,
    category VARCHAR(60) NOT NULL DEFAULT 'Workshop',
    created_by INT UNSIGNED NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. EVENT REGISTRATIONS TABLE
CREATE TABLE IF NOT EXISTS event_registrations (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    event_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NOT NULL,
    status ENUM('registered', 'attended', 'cancelled') NOT NULL DEFAULT 'registered',
    registered_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_event_user (event_id, user_id),
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. LEARNING TRACKS TABLE
CREATE TABLE IF NOT EXISTS learning_tracks (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(150) NOT NULL,
    description TEXT NOT NULL,
    total_modules INT UNSIGNED NOT NULL DEFAULT 5,
    icon VARCHAR(50) NOT NULL DEFAULT 'code'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. MEMBER TRAINING PROGRESS TABLE
CREATE TABLE IF NOT EXISTS member_progress (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    track_id INT UNSIGNED NOT NULL,
    completed_modules INT UNSIGNED NOT NULL DEFAULT 0,
    status ENUM('in_progress', 'completed') NOT NULL DEFAULT 'in_progress',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_user_track (user_id, track_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (track_id) REFERENCES learning_tracks(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. RESOURCES & LEARNING MATERIALS TABLE
CREATE TABLE IF NOT EXISTS resources (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    description TEXT NOT NULL,
    category VARCHAR(80) NOT NULL,
    resource_type ENUM('guide', 'tutorial', 'template', 'video', 'pdf') NOT NULL DEFAULT 'guide',
    external_link VARCHAR(255) NOT NULL,
    created_by INT UNSIGNED NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. FORUM CATEGORIES TABLE
CREATE TABLE IF NOT EXISTS forum_categories (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description VARCHAR(255) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. FORUM THREADS TABLE
CREATE TABLE IF NOT EXISTS forum_threads (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NOT NULL,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    views INT UNSIGNED NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES forum_categories(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9. FORUM REPLIES TABLE
CREATE TABLE IF NOT EXISTS forum_replies (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    thread_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NOT NULL,
    content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (thread_id) REFERENCES forum_threads(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 10. MENTORSHIP REQUESTS TABLE
CREATE TABLE IF NOT EXISTS mentorship_requests (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    mentee_id INT UNSIGNED NOT NULL,
    mentor_id INT UNSIGNED NOT NULL,
    goal_notes TEXT NOT NULL,
    status ENUM('pending', 'accepted', 'rejected', 'completed') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (mentee_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (mentor_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- =======================================================
-- SEED DATA INSERTION
-- =======================================================

-- 1. Insert Initial Users
-- Passwords:
-- admin@djangogirls.sl     => Admin123!
-- mentor@djangogirls.sl    => Mentor123!
-- alpha.jalloh@djangogirls.sl => Alpha123!
-- fatmata.kamara@djangogirls.sl => Member123!

INSERT INTO users (id, full_name, email, password_hash, role, bio, phone, skills, github_url, linkedin_url, status) VALUES
(1, 'System Administrator', 'admin@djangogirls.sl', '$2y$10$KtHVWadCLGPEpnGFvTqSJeq./L1mcjwlFm9dWFFAvhZ/ubmt8KPHC', 'admin', 'Lead Coordinator and System Administrator for Django Girls Sierra Leone community initiatives.', '+232 77 100 001', 'Python, Django, PHP, System Administration, Cloud Architecture', 'https://github.com/djangogirls', 'https://linkedin.com/company/django-girls', 'active'),
(2, 'Mariama Sesay (Senior Mentor)', 'mentor@djangogirls.sl', '$2y$10$GVDuq5eQhE5qBHBYxcDe1O4Qb0U9j/HTkE/t2xmbTQLT.pg36JQu6', 'mentor', 'Full-stack software engineer and tech speaker passionate about mentoring young women in Python and Django.', '+232 78 200 002', 'Python, Django REST, PostgreSQL, Docker, Web Security', 'https://github.com/mariama-dev', 'https://linkedin.com/in/mariama-sesay', 'active'),
(3, 'Alpha Hamidu Jalloh', 'alpha.jalloh@djangogirls.sl', '$2y$10$LNEJV3RSNm53dVi8waVnLeg8kmdHcAarFPUpnFZtarhh/ooRUoQ5e', 'member', 'Student at Limkokwing University, Faculty of ICT. Aspiring software engineer and web developer.', '+232 79 300 003', 'HTML, CSS, JavaScript, PHP, Python, MySQL', 'https://github.com/alphahjalloh', 'https://linkedin.com/in/alpha-hamidu-jalloh', 'active'),
(4, 'Fatmata Kamara', 'fatmata.kamara@djangogirls.sl', '$2y$10$U7uA.NN.uqw8I.f1oWsTy.z/upHqdR51VtX0erVS.6LqZC0MmOYR2', 'member', 'Passionate beginner coder diving into Python fundamentals and web backend architectures.', '+232 76 400 004', 'Python Basics, HTML5, Responsive CSS', 'https://github.com/fatmatakamara', 'https://linkedin.com/in/fatmata-kamara', 'active')
ON DUPLICATE KEY UPDATE full_name=VALUES(full_name);

-- 2. Insert Learning Tracks
INSERT INTO learning_tracks (id, title, description, total_modules, icon) VALUES
(1, 'Python Fundamentals for Beginners', 'Master Python syntax, data types, loops, functions, and object-oriented concepts.', 5, 'terminal'),
(2, 'Web Foundations with HTML & CSS', 'Learn modern responsive layouts, flexbox, CSS grid, and semantic HTML5 design.', 4, 'layout'),
(3, 'Django Web Framework Core', 'Build dynamic, database-driven web applications with MTV architecture and ORM.', 6, 'database'),
(4, 'Git, GitHub & Version Control', 'Collaborate like a pro using branches, pull requests, merge conflict resolution.', 3, 'git-branch')
ON DUPLICATE KEY UPDATE title=VALUES(title);

-- 3. Insert Progress for Members
INSERT INTO member_progress (user_id, track_id, completed_modules, status) VALUES
(3, 1, 5, 'completed'),
(3, 2, 4, 'completed'),
(3, 3, 3, 'in_progress'),
(3, 4, 2, 'in_progress'),
(4, 1, 3, 'in_progress'),
(4, 2, 2, 'in_progress')
ON DUPLICATE KEY UPDATE completed_modules=VALUES(completed_modules);

-- 4. Insert Upcoming & Past Events
INSERT INTO events (id, title, description, event_date, venue, speaker, capacity, category, created_by) VALUES
(1, 'Django Girls Freetown Coding Bootcamp 2026', 'A hands-on, beginner-friendly 2-day coding workshop where women build their very first blog application with Django.', '2026-12-15 09:30:00', 'Limkokwing ICT Lab 2, Hill Station, Freetown', 'Mariama Sesay & Lead Mentors', 45, 'Bootcamp', 1),
(2, 'Mastering Databases & MySQL for Web Apps', 'Learn relational database modeling, writing optimized SQL queries, and connecting PDO safely in backend PHP and Python.', '2026-12-22 14:00:00', 'Virtual via Zoom / Google Meet', 'Alpha Hamidu Jalloh', 60, 'Webinar', 1),
(3, 'Tech Career Readiness & Mentorship Mixer', 'Connect with industry mentors, polish your developer portfolio, and discover internship opportunities in Sierra Leone tech hubs.', '2027-01-10 11:00:00', 'Sierra Leone Innovation Hub, Freetown', 'Guest Tech Founders & DG Mentors', 50, 'Networking', 1),
(4, 'Deploying Your First Web Application to the Cloud', 'Step-by-step masterclass covering web servers, domain setup, HTTPS, and hosting PHP/Django apps on modern cloud platforms.', '2027-01-25 15:00:00', 'Limkokwing Auditorium / Hybrid', 'David Koroma (DevOps Lead)', 75, 'Workshop', 1)
ON DUPLICATE KEY UPDATE title=VALUES(title);

-- 5. Insert Event Registrations
INSERT INTO event_registrations (event_id, user_id, status) VALUES
(1, 3, 'registered'),
(2, 3, 'registered'),
(3, 3, 'registered'),
(1, 4, 'registered')
ON DUPLICATE KEY UPDATE status=VALUES(status);

-- 6. Insert Resources
INSERT INTO resources (id, title, description, category, resource_type, external_link, created_by) VALUES
(1, 'Official Django Girls Tutorial (English)', 'Step-by-step tutorial designed to guide beginners through building an entire dynamic blog from scratch.', 'Django', 'tutorial', 'https://tutorial.djangogirls.org/en/', 1),
(2, 'Python Cheat Sheet for Web Developers', 'Comprehensive quick reference card covering data structures, lambdas, file I/O, and string manipulation.', 'Python', 'guide', 'https://www.python.org/doc/', 1),
(3, 'PHP & MySQL PDO Security Handbook', 'Best practices for securing forms, preventing SQL injection using prepared statements, and hashing passwords with bcrypt.', 'Security', 'pdf', 'https://www.php.net/manual/en/book.pdo.php', 1),
(4, 'Git & GitHub Collaboration Starter Kit', 'Essential commands cheat sheet for cloning repositories, creating feature branches, and submitting pull requests.', 'Tools', 'guide', 'https://git-scm.com/docs', 1),
(5, 'Modern CSS Grid & Flexbox Masterclass', 'Interactive guide and templates for crafting responsive, mobile-first web designs with modern CSS custom properties.', 'Frontend', 'template', 'https://developer.mozilla.org/en-US/docs/Learn/CSS', 1),
(6, 'Django REST Framework Introduction', 'Learn how to expose robust JSON APIs to connect mobile apps and modern single page frontends.', 'Backend', 'video', 'https://www.django-rest-framework.org/', 1)
ON DUPLICATE KEY UPDATE title=VALUES(title);

-- 7. Insert Forum Categories
INSERT INTO forum_categories (id, name, description, slug) VALUES
(1, 'General Community Discussions', 'Introduce yourself, share inspiring tech stories, and discuss upcoming community initiatives.', 'general'),
(2, 'Python & Django Help Desk', 'Ask coding questions, troubleshoot bugs, and seek advice on Python and Django projects.', 'python-django'),
(3, 'Web Programming & Security (DCOMP207)', 'Discuss coursework, web technologies, database design, and best development practices.', 'web-programming'),
(4, 'Career, Jobs & Internships', 'Share developer job openings, CV reviews, interview prep tips, and scholarship opportunities.', 'career-opportunities')
ON DUPLICATE KEY UPDATE name=VALUES(name);

-- 8. Insert Forum Threads
INSERT INTO forum_threads (id, category_id, user_id, title, content, views) VALUES
(1, 1, 3, 'Welcome to the new Django Girls Sierra Leone Management Platform!', 'Hello everyone! Super excited to welcome all of you to our new community management portal. Here you can track your workshops, connect with mentors, and share your learning journey.', 124),
(2, 2, 4, 'How do I resolve "Table already exists" during database migrations in Django?', 'Hi mentors! I am working on my first Django app and ran into an issue where makemigrations worked, but migrate throws an error stating the table already exists. What is the safest way to fix this without losing test data?', 86),
(3, 3, 3, 'Best practices for session security and prepared statements in PHP PDO', 'When handling sensitive user authentication in PHP, always use PDO prepared statements to block SQL injection and use password_hash with PASSWORD_DEFAULT. What other security measures should we implement in our semester project?', 95)
ON DUPLICATE KEY UPDATE title=VALUES(title);

-- 9. Insert Forum Replies
INSERT INTO forum_replies (id, thread_id, user_id, content) VALUES
(1, 1, 2, 'Great work Alpha! This platform will significantly boost our community engagement and make organizing workshops so much easier.'),
(2, 2, 2, 'Hi Fatmata! You can use `python manage.py migrate --fake` if the database table structure already matches your migration state, or check if the table was manually created.'),
(3, 2, 3, 'I had this issue before too! If you are in early development, resetting the migration history or using `--fake-initial` usually fixes it cleanly.'),
(4, 3, 2, 'Excellent point, Alpha. Always remember to implement session regeneration with session_regenerate_id(true) upon login to guard against session fixation attacks!')
ON DUPLICATE KEY UPDATE content=VALUES(content);

-- 10. Insert Mentorship Requests
INSERT INTO mentorship_requests (id, mentee_id, mentor_id, goal_notes, status) VALUES
(1, 3, 2, 'Looking for guidance on advanced Django ORM optimization, building REST APIs, and preparing for junior software engineering interviews.', 'accepted'),
(2, 4, 2, 'Would love a mentor to guide me through Python fundamentals and debugging my first database web application.', 'pending')
ON DUPLICATE KEY UPDATE goal_notes=VALUES(goal_notes);
