# EduTrack LMS — Backend API

A RESTful API backend for **EduTrack**, a Learning Management System designed to manage employee training and development within an organization. Built with Laravel 12 and secured using Laravel Sanctum token-based authentication.

---

## Overview

EduTrack enables companies to deliver structured learning programs to their employees. Admins can create courses with various content types, assign them to employees or departments, track progress in real-time, and review reports. Employees can consume content, take quizzes, submit assignments, and earn activity points.

---

## Tech Stack

| Layer | Technology |
|---|---|
| Framework | Laravel 12 (PHP 8.2+) |
| Authentication | Laravel Sanctum |
| Database | MySQL / SQLite |
| ORM | Eloquent |
| Queue | Laravel Queue |
| Dev Tooling | Laravel Sail, Pint, Pail, Tinker |
| Testing | PHPUnit 11 |

---

## Features

### Course Management
- Hierarchical structure: **Course → Module → Content**
- Supports multiple content types: **Video, Audio, E-Book, Text, Quiz**
- Progress tracking per content item (completion status, percentage, last playback position)
- Course publishing workflow (draft / published)
- Assign courses to individual employees or entire departments

### Quiz System
- **Standalone Quizzes** — independent quiz management with questions, answer choices, scoring, and leaderboards (per-quiz and global)
- **Integrated Course Quizzes** — quiz content embedded within a course module, with per-answer point tracking

### Assignment Workflow
- Create assignment templates
- Assign to specific employees or departments
- Employees upload file submissions
- Admin approval / rejection with status tracking

### Employee & Department Management
- Department CRUD with searchable, paginated listing
- Employee CRUD with auto-generated employee numbers per department
- Profile management with photo upload
- Role-based access: **Admin** and **Staff**

### Progress & Reporting
- Per-employee dashboard: total points, quizzes completed, course progress
- 7-day weekly points trend report
- Course-level completion reports
- Comprehensive employee-wide reports across all assigned courses

### Notifications & Gamification
- In-app notification system with read/unread status and time-ago formatting
- **Activity Points** — polymorphic points system, awarded for completing activities

---

## Database Structure

```
users               → Authentication & roles (admin / staff)
employees           → Employee profiles linked to users
departments         → Organizational units

courses             → Learning programs
course_modules      → Modules within a course
course_contents     → Individual lesson items
content_types       → video | audio | ebook | assignment | quiz-pg

questions           → Course-integrated quiz questions
choices             → Answer choices per question
question_answers    → Employee responses to course questions

quizzes             → Standalone quiz definitions
quiz_questions      → Questions in a standalone quiz
quiz_answers        → Answer choices
quiz_results        → Scores per employee per quiz

assignments         → Assignment templates
content_assignments → Assigned instances with approval state

course_assignments  → Course-to-employee assignment tracking
content_progress    → Per-content progress (status, %, playback position)
content_submissions → File submissions with scoring

notifications       → In-app notifications
activity_points     → Gamification point records (polymorphic)
personal_access_tokens → Sanctum API tokens
```

---

## API Endpoints

All endpoints are prefixed with `/api`. Protected routes require an `Authorization: Bearer {token}` header.

### Authentication
| Method | Endpoint | Description |
|---|---|---|
| GET | `/ping` | Health check |
| POST | `/login` | Login and obtain token |
| GET | `/me` | Validate token & get current user |
| POST | `/logout` | Revoke current token |

### Profile
| Method | Endpoint | Description |
|---|---|---|
| GET | `/profile` | Get user profile |
| POST | `/profile/update` | Update profile info & photo |
| POST | `/profile/change-password` | Change password |

### Dashboard
| Method | Endpoint | Description |
|---|---|---|
| GET | `/dashboard` | Summary stats |
| GET | `/dashboard/weekly` | 7-day points trend |
| GET | `/dashboard/report` | General report |
| GET | `/dashboard/report-employee` | Employee-specific report |

### Courses
| Method | Endpoint | Description |
|---|---|---|
| GET | `/courses` | List courses (role-filtered) |
| POST | `/courses/store/{slug?}` | Create or update course |
| GET | `/courses/show/{slug}` | Course details |
| GET | `/courses/edit/{slug}` | Get course for editing |
| POST | `/courses/change-status/{slug}` | Publish / unpublish |
| POST | `/courses/assign-course/{id?}` | Assign course to employees |
| GET | `/courses/assignment-targets` | Get assignable targets |
| POST | `/courses/store-lesson/{id?}` | Create or update lesson content |
| GET | `/courses/complete/{contentId}` | Mark content as complete |
| GET | `/courses/check-allow/{contentId}` | Check content access permission |
| POST | `/courses/submit-video` | Record video watch completion |
| POST | `/courses/submit-quiz` | Submit course-integrated quiz |
| GET | `/courses/submit-answer/{choice_id}/{question_id}` | Submit individual quiz answer |
| GET | `/courses/{slug}/report` | Course completion report |

### Quizzes
| Method | Endpoint | Description |
|---|---|---|
| GET | `/quiz` | List quizzes |
| POST | `/quiz/create` | Create quiz |
| PUT | `/quiz/update/{id}` | Update quiz |
| DELETE | `/quiz/delete/{id}` | Delete quiz |
| GET | `/quiz/detail/{id}` | Quiz details |
| GET | `/quiz/show/detail/{id}` | Detailed quiz info |
| POST | `/quiz/submit` | Submit quiz result |
| GET | `/quiz/leaderboard/{id}` | Per-quiz leaderboard |
| GET | `/quiz/leaderboard-all` | Global leaderboard |

### Assignments
| Method | Endpoint | Description |
|---|---|---|
| GET | `/assignment` | List assignments |
| POST | `/assignment/store/{slug?}` | Create or update assignment |
| GET | `/assignment/show/{slug}` | Assignment details |
| GET | `/assignment/edit/{slug}` | Get for editing |
| GET | `/assignment/show/list/{slug}` | Submission report |
| POST | `/assignment/assign/{id?}` | Assign to employees |
| GET | `/assignment/assignment-targets` | Get assignable targets |
| POST | `/assignment/upload-assign/{slug}` | Upload submission |
| POST | `/assignment/approve/{slug}` | Approve submission |
| POST | `/assignment/reject/{slug}` | Reject submission |

### Employees & Departments
| Method | Endpoint | Description |
|---|---|---|
| GET | `/employee` | List employees (paginated, searchable) |
| POST | `/employee` | Create employee |
| PUT | `/employee/{id}` | Update employee |
| DELETE | `/employee/{id}` | Delete employee |
| GET | `/employee/get-department/{id?}` | Get departments |
| GET | `/department` | List departments |
| POST | `/department` | Create department |
| PUT | `/department/{id}` | Update department |
| DELETE | `/department/{id}` | Delete department |

### Notifications
| Method | Endpoint | Description |
|---|---|---|
| GET | `/getNotification` | Fetch notifications |
| GET | `/getCount` | Unread count |
| POST | `/is-read/{id}` | Mark as read |

---

## Getting Started

### Requirements
- PHP 8.2+
- Composer
- MySQL or SQLite

### Installation

```bash
# Clone the repository
git clone https://github.com/Jovfrinn/lms-edutrack-be.git
cd lms-edutrack-be

# Install dependencies
composer install

# Copy and configure environment
cp .env.example .env
php artisan key:generate

# Configure database in .env, then run migrations and seeders
php artisan migrate --seed

# Start the development server
php artisan serve
```

The API will be available at `http://localhost:8000/api`.

---

## Project Structure

```
app/
├── Http/
│   ├── Controllers/Api/   # API controllers
│   ├── Requests/          # Form request validation
│   └── Resources/         # API response transformers
├── Models/                # Eloquent models
├── Helpers/               # NotificationHelper
└── Services/              # SeederDataBank

database/
├── migrations/            # Database schema
├── seeders/               # Initial data
└── factories/             # Test data factories

routes/
└── api.php                # All API route definitions
```

---

## Author

Developed by **Jovfrinn** as part of a portfolio project demonstrating full-stack LMS development with Laravel.
