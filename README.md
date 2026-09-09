# Bakso Gala — Integrated Smart Dining & Restaurant Management System

[![Laravel](https://img.shields.io/badge/Laravel-12.x-FF2D20?style=flat-square&logo=laravel&logoColor=white)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?style=flat-square&logo=php&logoColor=white)](https://php.net)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind_CSS-v4.0-06B6D4?style=flat-square&logo=tailwindcss&logoColor=white)](https://tailwindcss.com)
[![Vite](https://img.shields.io/badge/Vite-7.x-646CFF?style=flat-square&logo=vite&logoColor=white)](https://vitejs.dev)
[![Midtrans](https://img.shields.io/badge/Payment-Midtrans_Snap-002B49?style=flat-square)](https://midtrans.com)
[![Firebase](https://img.shields.io/badge/Push_Notifications-Firebase_FCM-FFCA28?style=flat-square&logo=firebase&logoColor=black)](https://firebase.google.com)
[![Google Gemini](https://img.shields.io/badge/AI_Engine-Gemini_Flash-4E75F8?style=flat-square&logo=googlegemini&logoColor=white)](https://ai.google.dev)

---

## Executive Overview

**Bakso Gala Digital Platform** is an enterprise-grade, end-to-end contactless dining and restaurant management system custom-engineered for **Bakso Gala**, an Indonesian culinary brand. The platform digitalizes the entire dining lifecycle—from table-specific QR code scanning, dynamic visual menus, and contactless payment processing, to real-time kitchen order dispatching, role-based operations management, and AI-curated customer feedback.

Engineered as a high-performance, single-stack web application, the system streamlines restaurant floor operations, eliminates ordering bottlenecks during peak hours, and provides business owners with real-time financial telemetry and audit logs.

---

## Key System Features

### 1. Customer Dining & Ordering Workflow
- **Table-Aware QR Ingestion:** Diners scan table-specific QR codes mapped to restaurant sections (*Lantai 2 Gym, Indoor More, Depan Utama, Area Photobooth*) to initiate localized ordering sessions without manual table entry.
- **Dynamic Digital Catalog:** Interactive menu catalog organized by categories with real-time price reflection, item descriptions, and dietary notes.
- **Precision Cart & Custom Notes:** Diners can attach specific culinary instructions and preparation notes directly to individual cart items.
- **Automated Order State Machine:** Self-expiring pending orders (10-minute timeout window) to prevent inventory locks and phantom unpaid tickets.
- **Digital Receipt & Print Layout:** Built-in web invoice and thermal-compatible receipt rendering for on-demand customer printing.

### 2. Payment & Real-Time Notification Pipeline
- **Midtrans Payment Gateway Integration:** Secure transactions supporting virtual accounts, e-wallets (GoPay, ShopeePay), and QRIS via Midtrans Snap.
- **Asynchronous Webhook Processing:** Dedicated webhook receiver (`/api/midtrans-callback`) to update order states deterministically upon payment verification.
- **Firebase Cloud Messaging (FCM):** Live web-push dispatch alerting kitchen staff and cashiers instantly whenever a new paid order arrives.

### 3. Multimodal AI-Assisted Customer Reviews
- **Vision-Driven Sentiment & Image Moderation:** Powered by Google Gemini Vision (`gemini-2.5-flash`), the system automatically curates submitted customer reviews and food photographs.
- **Automated Feature Selection:** High-rating feedback accompanied by verified food imagery is automatically curated for social proof, while inappropriate submissions are filtered out seamlessly without degrading user experience.
- **Review Polishing API:** Automated grammar and sentiment enhancement to elevate organic customer reviews into polished testimonials.

### 4. Back-Office & Operations Management
- **Role-Based Access Control (RBAC):** Distinct permission separation between operational staff (**Kasir**) and executive leadership (**Owner**).
- **Live Order Board:** Cashier console featuring polling-based real-time order tracking (`checkNew`), kitchen status transitions, and fast invoice lookups.
- **Executive Analytics & Reporting:** Comprehensive revenue metrics, date-range sales reports, and daily transaction breakdowns.
- **Activity Audit Trail:** Granular logging (`ActivityLog`) capturing critical staff actions, status modifications, and menu edits for accountability.
- **Automated QR Generation Utility:** Administrative tool generating bulk print-ready QR codes for physical table deployment across all floor zones.

---

## System Architecture & Technology Stack

| Layer | Technology | Purpose & Evidence in Codebase |
| :--- | :--- | :--- |
| **Backend Framework** | **Laravel 12.x (PHP 8.2+)** | RESTful routing, Eloquent ORM, form requests, custom middleware (`IsOwner`), and database migrations |
| **Frontend & UI** | **Tailwind CSS v4 + Vite 7** | Modern, responsive mobile-first UI with custom theme tokens and pre-bundled assets |
| **Database** | **MySQL** | Relational schema modeling orders, line items, menus, promotions, audit logs, and reviews |
| **Payment Gateway** | **Midtrans Snap SDK** | Multi-channel payment processing and secure signature-validated webhook handling |
| **Push Notifications** | **Firebase Cloud Messaging (FCM)** | Asynchronous device-token dispatch for instant floor staff notifications |
| **Artificial Intelligence**| **Google Gemini Flash Vision API** | Automated multimodal review evaluation, sentiment classification, and photo verification |
| **QR Code Engine** | **SimpleSoftwareIO QR Code** | Dynamic SVG/PNG QR generation for physical table provisioning |

---

## Project Contribution & Ownership

### Solo Project — Custom Client Solution
> **Designed, architected, and developed by Prabu Alam Tian Try Suherman — Lead Architect & Full-Stack Master.**

This project was commissioned as a bespoke digital transformation solution for the culinary brand **Bakso Gala**. Every component was individually architected, engineered, and deployed by a single author:

- **System & Solution Architecture:** Conceived and drafted the end-to-end workflow bridging physical restaurant tables with cloud-based order dispatching and payment settlements.
- **Database Architecture:** Modeled the normalized schema in MySQL, ensuring transactional integrity across order states, detailed line items, session carts, audit logs, and promotion rules.
- **Backend Engineering:** Developed clean, decoupled controllers, event-driven payment listeners, and hardened security filters in Laravel 12.
- **Third-Party Integrations:** Implemented end-to-end API integrations with Midtrans payment webhooks, Firebase Cloud Messaging, and Google Gemini AI.
- **Frontend & UI/UX Design:** Crafted an intuitive, mobile-optimized ordering interface and a streamlined operational dashboard using Tailwind CSS v4.
- **Testing, Optimization & Deployment:** Managed environment hardening, cache orchestration, route optimization, and final production server deployment.

---

## About the Creator

### Prabu Alam Tian Try Suherman
**Lead Architect & Full-Stack Master**  
*Founder of [Qisa Studio](https://qisastudio.com)*

Prabu Alam Tian Try Suherman is a Lead Architect & Full-Stack Master and the Founder of **Qisa Studio**, a digital product studio focused on website development, application development, and digitalization. His work spans system architecture, UI/UX, full-stack engineering, database design, and end-to-end digital product development.

Prabu focuses on architecting scalable, high-performance systems and executing complex end-to-end applications — from system analysis and architecture to UI/UX, development, database design, testing, deployment, and optimization.

**Areas of Focus:**
- System Architecture & Engineering
- Full-Stack Web Application Development
- Enterprise Digitalization & Custom Digital Solutions
- Database Architecture & Data Modeling
- Third-Party API Orchestration (Payments, Cloud Messaging, AI APIs)
- UI/UX & Responsive Product Design
- Scalable System Design & Performance Optimization

---

## Installation & Local Development

### Prerequisites
- **PHP** >= 8.2 with `pdo_mysql`, `curl`, `mbstring`, `fileinfo` extensions
- **Composer** >= 2.x
- **Node.js** >= 18.x & **NPM**
- **MySQL Server** >= 8.0

### Setup Steps

1. **Clone the repository:**
   ```bash
   git clone https://github.com/sauva20/baksogala.git
   cd baksogala
   ```

2. **Install PHP and JavaScript dependencies:**
   ```bash
   composer install
   npm install
   ```

3. **Configure Environment Variables:**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Set Required Credentials in `.env`:**
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=baksogala
   DB_USERNAME=root
   DB_PASSWORD=

   # Midtrans Payment Gateway
   MIDTRANS_SERVER_KEY=your_midtrans_server_key
   MIDTRANS_CLIENT_KEY=your_midtrans_client_key
   MIDTRANS_IS_PRODUCTION=false

   # Google Gemini AI
   GEMINI_API_KEY=your_gemini_api_key

   # Firebase Cloud Messaging
   FCM_SERVER_KEY=your_firebase_server_key
   ```

5. **Run Migrations & Seed Data:**
   ```bash
   php artisan migrate --seed
   ```

6. **Build Frontend Assets & Run Local Server:**
   ```bash
   # In terminal 1 (Vite bundler):
   npm run dev

   # In terminal 2 (Laravel local server):
   php artisan serve
   ```

7. **Access the Application:**
   - Customer Portal: `http://localhost:8000`
   - Table QR Simulation: `http://localhost:8000/scan/Depan%20Utama/1`
   - Admin Back-Office: `http://localhost:8000/admin/login`

---

## Built & Architected by

**Prabu Alam Tian Try Suherman**  
*Lead Architect & Full-Stack Master*  
*Founder — Qisa Studio*

> *Architecting scalable systems. Building high-performance digital products. Turning complex ideas into working applications.*
