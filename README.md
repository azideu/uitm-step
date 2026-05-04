![UiTM STEP banner](assets/img/banner.png)

# UiTM STEP (Student Talent Exchange Platform)

[![Website](https://img.shields.io/badge/Website-uitmstep.app-6B46C1?style=for-the-badge)](https://uitmstep.app)
[![PHP Version](https://img.shields.io/badge/PHP-8.x-777bb4?style=for-the-badge&logo=php)](https://www.php.net/)
[![MySQL](https://img.shields.io/badge/MySQL-8.0-4479a1?style=for-the-badge&logo=mysql)](https://www.mysql.com/)
[![Deployment](https://img.shields.io/badge/DigitalOcean-Droplet-0080FF?style=for-the-badge&logo=digitalocean)](https://www.digitalocean.com/)

> **Unifying the UiTM Ecosystem through a High-Trust, Academic-Elite Marketplace.**

---

## The Vision

UiTM STEP is a centralized, "Fiverr-style" marketplace exclusively for the **34 campuses** of Universiti Teknologi MARA. By leveraging a high-trust digital economy, the platform eliminates "Campus Isolation," allowing students to exchange academic, creative, and technical services regardless of geographical location.

### Core Pillars
- **Verified Trust:** Mandatory `@student.uitm.edu.my` authentication with OTP email verification.
- **National Reach:** Toggle between local campus proximity and nationwide digital tasking.
- **Trust-Based Transactions:** Manual payment verification for secure student-to-student settlements.

---

## Key Features

### Dynamic Marketplace
- **Intelligent Filtering:** Toggle between "My Campus" and "All Campuses."
- **Niche Categories:** Specifically curated for student needs (Design, Programming, Tutoring, Delivery).
- **Responsive Grid:** Optimized for mobile and desktop browsing.

### Real-Time Messaging
- **SSE-Powered Chat:** Low-latency communication using Server-Sent Events (SSE).
- **Contextual Orders:** Message sellers directly from gig listings.

### Secure Transaction Workflow
- **Verified Payments:** Buyers upload bank transfer or DuitNow proofs for verification.
- **Lifecycle Management:** Gigs move from *Active* → *Delivered* → *Completed* with verified status tracking.
- **Admin Oversight:** Centralized moderation dashboard for conflict resolution.

---

## Tech Stack & Infrastructure

| Component | Technology | Role |
| :--- | :--- | :--- |
| **Backend** | PHP 8.x (PDO) | Core Logic & API |
| **Frontend** | HTML5, CSS3, Vanilla JS | UI/UX & Interactions |
| **Styling** | Tailwind CSS (CDN) | Modern, Responsive Design |
| **Database** | MySQL 8.0 | Structured Data Persistence |
| **Storage** | DigitalOcean Spaces | S3-Compatible Persistent Storage |
| **Hosting** | DigitalOcean Droplet | Ubuntu/LAMP Stack Environment |

---

## Project Structure

```text
├── admin/              # Centralized moderation & oversight
├── api/                # Core business logic, SSE Streamers & AJAX Endpoints
├── assets/
│   ├── css/            # Premium "Academic-Elite" Styling with deep shadows
│   ├── img/            # Static Assets & Category Icons
│   └── js/             # Real-time polling & UI logic
├── db/
│   ├── schema.sql      # Core table structures
│   └── seed.sql        # Demo students, gigs, and admins
├── gigs/               # Gig lifecycle (Create, Edit, Details)
├── includes/           # DB Drivers, Config, Auth Checks, & Helpers
├── uploads/            # Local high-performance cache
├── .htaccess           # Clean URL routing & rewrite logic
└── *.php               # Core View Controllers (Marketplace, Profile, Auth)
```

---

## Installation & Setup

### Local Environment
1. **Prerequisites:** Install XAMPP with PHP 8.0+.
2. **Database:**
   - Create database `uitm_step`.
   - Import `db/schema.sql` then `db/seed.sql`.
3. **Config:** Update `includes/db.php` with your local credentials.
4. **Access:** Navigate to `http://localhost/STEP` or visit the live app at [uitmstep.app](https://uitmstep.app).

### Production Deployment
1. **Infrastructure:** Provision a DigitalOcean Droplet (Ubuntu 22.04+).
2. **Secrets:** Use `.env` for:
   - `DB_PASS`, `GOOGLE_CLIENT_ID`.
3. **Storage:** Configure S3 credentials in `includes/storage.php`.
4. **SSL:** Ensure `certbot` is configured for HTTPS.

---

## Security Protocol

- **Universal PDO:** 100% protection against SQL Injection via prepared statements.
- **XSS Mitigation:** Comprehensive HTML escaping for all user-generated content.
- **OAuth 2.0 & OTP:** Secure Google Authentication with domain-level filtering and manual OTP flows.
- **File Integrity:** MIME-type validation and restricted execution permissions on `/uploads`.
- **Centralized Routing:** Secure subdirectory resolution and pathing via `.htaccess` and `ROOT_URL` configurations.

---

## Google Sign-In Behavior
- **Account Linking:** Matches existing student emails.
- **Registration Deferral:** New users are created instantly; campus selection is handled post-login via `complete_registration.php`.
- **Domain Restricted:** Only `@student.uitm.edu.my` and approved subdomains allowed.

---

<div align="center">
  <p><i>Developed as part of the <b>CSC264 and ISP250 group project</b> for <b>StepUp!</b>.</i></p>
  <img src="https://img.shields.io/badge/Status-Stable-success?style=flat-square" />
</div>
