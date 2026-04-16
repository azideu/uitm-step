### **UiTM STEP (Student Talent Exchange Platform)**

UiTM STEP is a centralized, "Fiverr-style" marketplace developed exclusively for UiTM students. The platform aims to solve the problem of "Campus Isolation" by unifying all 34 campuses into a single digital economy, allowing students to buy and sell academic and creative services in a high-trust environment.

## Features

- **Verified Student Access:** Restricted login using `@student.uitm.edu.my` credentials.
- **National & Local Filtering:** Toggle between "My Campus" and "All Campuses" to find physical or digital tasks.
- **Dynamic Marketplace:** Pagination-supported grid view of services with category and tag-based filtering.
- **Real-Time Messaging:** AJAX-powered chat system for seamless buyer-seller communication.
- **Secure Transaction Workflow:** - Buyers upload bank transfer proof (JPG, PNG, PDF).
    - Sellers manage gig lifecycles (Active, Delivered).
    - Admin oversight for transaction moderation.
- **Role-Based Dashboards:** Dedicated views for Buying, Selling, and System Administration.

## Tech Stack

- **Frontend:** HTML5, CSS3, Tailwind CSS (via CDN), Vanilla JavaScript.
- **Backend:** PHP 8.x (using PDO for SQL security).
- **Database:** MySQL 8.0.
- **Timezone:** Asia/Kuala_Lumpur.

## Project Structure

```text
├── api/                # AJAX endpoints for real-time chat
├── assets/
│   ├── css/            # Custom UI styling
│   ├── img/            # Marketplace category & hero assets
│   └── js/             # Frontend logic & chat polling
├── db/
│   ├── schema.sql      # Database table definitions
│   └── seed.sql        # Demo data (Admins, Students, Gigs)
├── includes/           # Core configurations, DB connection, & functions
├── uploads/            # Secure directory for payment proofs
└── *.php               # Primary application pages (Marketplace, Dashboards, etc.)
```

## Installation (Local Environment)

1. **Prerequisites:** Install **MAMP**, **XAMPP**, or **WampServer** on your machine.
2. **Database Setup:**
   - Open `phpMyAdmin`.
   - Create a new database named `uitm_step`.
   - Import `db/schema.sql` first, followed by `db/seed.sql` for demo data.
3. **Application Setup:**
   - Clone this repository into your `htdocs` or `www` directory.
   - Configure `includes/db.php` with your local database credentials.
4. **Access:** Open your browser and navigate to `http://localhost/uitm-step`.

## Security Implementations

- **XSS Protection:** All user-generated content is escaped before rendering.
- **SQL Injection Prevention:** Universal use of PDO Prepared Statements.
- **Session Security:** Implementation of `session_regenerate_id()` and HTTP-only cookies.
- **File Upload Validation:** Strict MIME-type checking and 2MB file size limits for payment proofs.

---
*Developed as part of the **CSC264 and ISP250 group project** for **StepUp!**.*
