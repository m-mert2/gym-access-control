<div align="center">

# Gym Access Control System

**A full-stack membership management and turnstile access control system built for Eskisehir Osmangazi University Sports Center.**

[![PHP](https://img.shields.io/badge/PHP_7.4+-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://www.php.net/)
[![SQL Server](https://img.shields.io/badge/SQL_Server-CC2927?style=for-the-badge&logo=microsoftsqlserver&logoColor=white)](https://www.microsoft.com/sql-server)
[![Bootstrap](https://img.shields.io/badge/Bootstrap_5.3-7952B3?style=for-the-badge&logo=bootstrap&logoColor=white)](https://getbootstrap.com/)
[![jQuery](https://img.shields.io/badge/jQuery_3.7-0769AD?style=for-the-badge&logo=jquery&logoColor=white)](https://jquery.com/)
[![License](https://img.shields.io/badge/License-Academic-green?style=for-the-badge)]()

</div>

---

## Overview

A web-based gym management platform that handles member registration, RFID/NFC card-based turnstile entry, membership tracking, and administrative reporting. Built with vanilla PHP, Microsoft SQL Server, and a Bootstrap 5 frontend.

---

## Features

| Feature               | Description                                                                 |
| --------------------- | --------------------------------------------------------------------------- |
| **Member Management** | Register members, create/renew memberships, search & list all members       |
| **Card System**       | Assign and manage RFID/NFC cards linked to individual memberships           |
| **Turnstile Control** | Real-time card-based entry validation via stored procedures                 |
| **Membership Plans**  | Daily, monthly, 3-month, 6-month, annual, and unlimited plans               |
| **Access Logs**       | Full history of all turnstile events with advanced filtering                |
| **Dashboard**         | Live statistics — active members, today's entries, expiring memberships     |
| **Reporting**         | Generate and send detailed reports via email (HTML/JSON/XML)                |
| **Role-Based Access** | Admin, Staff, and Cashier roles with granular permissions                   |
| **Auto Maintenance**  | Scheduled and manual data cleanup, auto-deactivation of expired memberships |

---

## Tech Stack

| Layer        | Technology                                          |
| ------------ | --------------------------------------------------- |
| **Backend**  | PHP 7.4+ (vanilla, no framework)                    |
| **Database** | Microsoft SQL Server with `sqlsrv` driver           |
| **Frontend** | Bootstrap 5.3.0 · jQuery 3.7.1 · Font Awesome 6.4.0 |
| **Email**    | PHPMailer via SMTP _(optional)_                     |
| **API**      | JSON REST-style endpoints                           |
| **Auth**     | PHP Sessions with role-based permissions            |

---

## Project Structure

```
GYM Membership tracker/
├── sql codes/                               # Database scripts
│   ├── CREATE TABLE.sql                     # Schema definitions (10 tables)
│   ├── DROP TABLE.sql                       # Schema teardown
│   ├── sp_TurnikeGecisKontrol.sql           # Turnstile access control procedure
│   ├── sp_UyeDashboardRaporu.sql            # Dashboard report procedure
│   ├── sp_Uyelik_Olustur.sql                # Membership creation procedure
│   ├── sp_YillikVeriTemizligi.sql           # Yearly data cleanup procedure
│   ├── trg_UyelikOtomatikDuzenle.sql        # Auto-configure membership trigger
│   └── trg_UyelikOtomatikKapat.txt          # Auto-close expired membership trigger
│
└── source/
    └── gym/
        ├── index.php                        # Application entry point
        ├── api/                             # REST API endpoints (19 endpoints)
        │   ├── login.php                    # Authentication
        │   ├── turnike_kontrol.php          # Turnstile validation
        │   ├── uye_kaydet.php               # Member registration
        │   └── ...                          # 16 more endpoints
        ├── assets/
        │   ├── css/style.css                # Custom styles
        │   └── js/main.js                   # Application logic
        ├── includes/
        │   ├── db_config.php                # Database configuration
        │   └── mail_config.php              # SMTP configuration
        └── pages/                           # Page templates (13 pages)
            ├── dashboard.php                # Statistics overview
            ├── turnike.php                  # Turnstile control panel
            └── ...                          # 11 more pages
```

---

## Database Schema

The system uses **10 tables** with foreign key relationships, stored procedures, and triggers:

<div align="center">
  <img src="GYM Membership tracker/docs/er_diagram.jpg" alt="ER Diagram" width="800">
  <br>
  <em>Entity-Relationship Diagram</em>
</div>

---

## API Reference

### Authentication

| Endpoint         | Method | Description                          |
| ---------------- | ------ | ------------------------------------ |
| `api/login.php`  | `POST` | Authenticate user and create session |
| `api/logout.php` | `GET`  | Destroy session and clear cookies    |

### Member Management

| Endpoint                  | Method | Description                                          |
| ------------------------- | ------ | ---------------------------------------------------- |
| `api/kisi_ekle.php`       | `POST` | Register a new person                                |
| `api/uye_kaydet.php`      | `POST` | Save member with card and membership                 |
| `api/uye_listesi.php`     | `GET`  | List all members with membership status              |
| `api/uye_detay_stats.php` | `GET`  | Member detail statistics (gender, plan distribution) |

### Membership Operations

| Endpoint                       | Method | Description                     |
| ------------------------------ | ------ | ------------------------------- |
| `api/uyelik_olustur.php`       | `POST` | Create a new membership         |
| `api/uyelik_turleri.php`       | `GET`  | List available membership plans |
| `api/uyelik_turu_ekle.php`     | `POST` | Add a membership plan           |
| `api/uyelik_turu_guncelle.php` | `POST` | Update a membership plan        |
| `api/uyelik_turu_sil.php`      | `POST` | Delete a membership plan        |

### Access Control

| Endpoint                  | Method | Description                      |
| ------------------------- | ------ | -------------------------------- |
| `api/turnike_kontrol.php` | `POST` | Validate card scan at turnstile  |
| `api/kart_tanimla.php`    | `POST` | Assign RFID/NFC card to a member |
| `api/kayitli_kartlar.php` | `GET`  | List all registered cards        |
| `api/giris_loglari.php`   | `GET`  | Fetch entry logs with filters    |

### Reports & Maintenance

| Endpoint                    | Method | Description                      |
| --------------------------- | ------ | -------------------------------- |
| `api/dashboard_stats.php`   | `GET`  | Dashboard statistics             |
| `api/mail_rapor_gonder.php` | `POST` | Send report via email            |
| `api/cron_temizlik.php`     | `GET`  | Scheduled data cleanup (cron)    |
| `api/manuel_temizlik.php`   | `POST` | Manual data cleanup (admin only) |

---

## Getting Started

### Prerequisites

- **PHP 7.4+** with `sqlsrv` extension enabled
- **Microsoft SQL Server** (2017 or later recommended)
- **Web Server** — IIS or Apache
- **Composer** — for PHPMailer (optional)

### Installation

**1. Create the database:**

```sql
CREATE DATABASE OGUGYMDB;
```

**2. Deploy the schema:**

Execute `sql codes/CREATE TABLE.sql` in SQL Server Management Studio.

**3. Load stored procedures & triggers:**

Run all `sp_*.sql` and `trg_*.sql` files from the `sql codes/` directory.

**4. Configure database connection:**

Edit `source/gym/includes/db_config.php`:

```php
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'your_username');
define('DB_PASSWORD', 'your_password');
```

**5. Install frontend dependencies:**

Download and place the following libraries into the project (or load via CDN):

- [Bootstrap 5.3.0](https://getbootstrap.com/) — CSS & JS
- [jQuery 3.7.1](https://jquery.com/) — JS
- [Font Awesome 6.4.0](https://fontawesome.com/) — Icons

**6. Install PHPMailer** _(optional — for email reports):_

```bash
cd source/gym
composer require phpmailer/phpmailer
```

**7. Configure SMTP** _(optional):_

Edit `source/gym/includes/mail_config.php` with your SMTP credentials.

**8. Launch the application:**

Point your web server's document root to `source/gym/` and open `index.php` in your browser.

---

## User Roles

| Role        | Permissions                                                                 |
| ----------- | --------------------------------------------------------------------------- |
| **Admin**   | Full access — member management, system settings, reports, data maintenance |
| **Staff**   | Member registration, membership creation, turnstile control, access logs    |
| **Cashier** | Membership creation, payment operations                                     |

---

## Screenshots

> Add screenshots to a `screenshots/` directory and reference them here.

---

## License

This project was developed as part of the Database Systems course at Eskisehir Osmangazi University.
