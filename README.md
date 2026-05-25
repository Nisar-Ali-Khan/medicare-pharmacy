# MediCare Pharmacy — Web Application
## CEP Assignment #4 | Web Engineering

---

## Project Overview
MediCare is a full-stack dynamic web application for an online pharmacy built with:
- **Frontend:** HTML5, CSS3, Vanilla JavaScript
- **Backend:** PHP 8.x
- **Database:** MySQL

---

## Project Structure
```
pharmacy/
├── index.html          ← Homepage
├── products.html       ← Products listing with search & filter
├── about.html          ← About Us page
├── contact.html        ← Contact form
├── admin.html          ← Admin dashboard
├── orders.html         ← Customer orders page
├── css/
│   └── style.css       ← Main stylesheet
├── js/
│   ├── main.js         ← Core JS (API calls, cart, auth)
│   └── navbar.js       ← Navbar + modals injected globally
├── php/
│   ├── config.php      ← DB connection & helpers
│   ├── auth.php        ← Login, Register, Logout
│   ├── products.php    ← Product CRUD API
│   ├── orders.php      ← Cart & Orders API
│   ├── admin.php       ← Admin-only API (users, messages)
│   └── contact.php     ← Contact form handler
└── database.sql        ← Full DB schema + sample data
```

---

## Setup Instructions

### 1. Requirements
- XAMPP / WAMP / LAMP (PHP 8+ & MySQL)
- A modern web browser

### 2. Database Setup
1. Open **phpMyAdmin** → `http://localhost/phpmyadmin`
2. Create new database: `medicare_pharmacy`
3. Import `database.sql` file
4. Done! Sample data is included.

### 3. Run the App
1. Copy the `pharmacy/` folder to `htdocs/` (XAMPP) or `www/` (WAMP)
2. Open browser → `http://localhost/pharmacy/`

### 4. Admin Login
- **Email:** `admin@medicare.com`
- **Password:** `password` *(default Laravel hash)*

> **Note:** Change the admin password after first login!

---

## Features

### Customer Features
- Homepage with categories & featured products
- Product search & category filtering
- Shopping cart (add, update, remove items)
- Place orders with the delivery address
- User registration & login
- View order history & status tracking
- Contact form

### Admin Features
- Dashboard with stats (products, orders, revenue, users)
- Product management (add, delete, view)
- Order management with status updates
- User list
- Contact messages inbox

### Security Features
- Password hashing (bcrypt)
- SQL injection prevention (prepared/sanitized queries)
- Session-based authentication
- Role-based access control (admin/customer)
- Input validation & sanitization
- XSS prevention via `strip_tags().`

---

## Testing Checklist
- [ ] User registration with validation
- [ ] Login/logout flow
- [ ] Add products to cart
- [ ] Place an order
- [ ] Admin login & dashboard
- [ ] Product add/delete
- [ ] Order status update
- [ ] Search functionality
- [ ] Category filtering
- [ ] Contact form submission
- [ ] SQL injection testing
- [ ] XSS testing

---

## 👨‍💻 Developed By
Group Assignment — CEP #4
Web Engineering Course
