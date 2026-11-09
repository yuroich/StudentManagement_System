# Student Management System

A web-based system for managing student records, built with PHP and MySQL.

**Bicol University Polangui** | Final Project, 1st Semester SY 2026-2027
**Instructor:** Emmanuel S. Molina

## Team

- Joshua Belmonte
- Nathaniel Raynada

## Overview

Authorized users log in to add student records, search for students by name, and view each student's details. The admin can also edit and delete records.

## Features

- Secure login and logout (hashed passwords, sessions, lockout after repeated failed attempts)
- Sign-up for regular user accounts (admin accounts cannot be created from the app)
- Add new student records (admin and regular users)
- Edit and delete student records (admin only, enforced on the server)
- Search students by name, with live suggestions, and filter by course
- View a student's full details
- Only one admin account, with a message when a second admin login is attempted
- Errors are logged to `sms/logs/error.log`

## Roles

| Role | Can do |
|------|--------|
| Admin | Log in, add, edit, delete, search, view |
| User | Sign up, log in, add, search, view |

## Tech Stack

- PHP (mysqli with prepared statements)
- MySQL / MariaDB via XAMPP
- HTML, CSS and a little JavaScript
- VS Code

## Project Structure

```
README.md
.gitignore
index.php            Login page
register.php         Sign-up page
dashboard.php        Student list, search, add, view, update, delete
manage_account.php   Edit your own account
logout.php
database.sql         Creates the database, tables and the admin account
actions/             Add, update, delete, get and search handlers
includes/            Shared code (session setup, helpers, header)
config/              Database connection settings
assets/              CSS, JavaScript and uploaded photos
logs/                Error log (not committed to Git)
docs/                Planning notes, checklist and user guide
```

## Setup (XAMPP)

1. Install XAMPP and start **Apache** and **MySQL** in the XAMPP Control Panel.
2. Copy this whole project folder into `C:\xampp\htdocs\`.
3. Open `http://localhost/phpmyadmin`, choose **Import**, select `database.sql`, and click **Go**.
4. Open `http://localhost/<folder-name>/` in your browser, using the name of the folder you copied.
5. Log in with the admin account created by `database.sql`, then change its password from **Manage Account**.

If your MySQL has a root password, edit `config/db.php`.

## How to Use

_To be completed: one short section per feature, with screenshots._

## Project Status

Progress is tracked in `docs/SMS_Project_Checklist.xlsx`.
