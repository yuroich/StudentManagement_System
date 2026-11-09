# Student Management System

A web-based system for managing student records, built with PHP and MySQL.

**Bicol University Polangui** | Final Project, 1st Semester SY 2026-2027
**Instructor:** Emmanuel S. Molina

## Team

- Joshua Belmonte
- Nathaniel Raynada

## Overview

The Student Management System lets authorized users log in, add student records, search for students by name, and view each student's details. An admin has extra privileges to edit and delete records.

## Planned Features

- Secure login and logout (hashed passwords, session handling)
- Add new student records (admin and regular users)
- Edit and delete student records (admin only)
- Search students by name and view detailed information
- A single admin account with admin-only features

## Roles

| Role | Can do |
|------|--------|
| Admin | Log in, add, edit, delete, search, view |
| User | Log in, add, search, view |

## Tech Stack

- PHP
- MySQL (via XAMPP)
- HTML and CSS
- VS Code

## Project Structure

```
config/      Database connection settings
includes/    Shared PHP files (header, footer, auth helpers)
css/         Stylesheets
database/    SQL file for creating the database and tables
logs/        Error logs (not committed to Git)
docs/        Planning notes and the user guide
```

## Setup

_To be completed once the project runs._

## How to Use

_To be completed: one short section per feature, with screenshots._

## Project Status

Progress is tracked in `SMS_Project_Checklist.xlsx`.
