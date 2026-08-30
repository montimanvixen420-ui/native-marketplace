# TINDA Marketplace — Native PHP MVC

Simpleng custom MVC skeleton (walang Laravel), may authentication na naka-role
para sa: **superadmin** (developers), **admin** (seller), **supplier**, at **customer**.

## Folder structure

```
config/database.php     ← PDO connection settings (i-edit ang database name)
core/Router.php         ← simpleng router
core/Controller.php     ← base controller (may view() helper)
controllers/            ← AuthController, DashboardController
models/User.php         ← database queries para sa users table
views/                  ← mga .php templates (may Tailwind CDN)
routes/web.php          ← listahan ng URLs → controllers
public/index.php        ← entry point (front controller)
database/schema.sql     ← SQL para gawin ang users table
generate_hash.php       ← tool para gumawa ng hashed password
```

## Setup

### 1. I-edit ang database config

Buksan `config/database.php`, siguraduhin tama ang:
```php
private static string $dbname = 'tinda_marketplace';
private static string $username = 'root';
private static string $password = '';
```

### 2. Gumawa ng database

1. Buksan ang XAMPP Control Panel, i-Start ang **MySQL**
2. Buksan `http://localhost/phpmyadmin`
3. Gumawa ng bagong database, pangalanan: `tinda_marketplace` (o kung ano man ang nilagay mo sa config)
4. Piliin ang database, pumunta sa tab na **SQL**, i-paste ang laman ng `database/schema.sql`, tapos i-**Go**

### 3. Gumawa ng superadmin account (kayong developers)

1. I-edit muna ang password sa `generate_hash.php` kung gusto mo
2. I-run sa CLI:
   ```
   php generate_hash.php
   ```
3. I-copy ang "Hashed" na resulta
4. Sa phpMyAdmin, buksan ulit ang tab na **SQL**, i-paste ang INSERT statement mula sa dulo ng `database/schema.sql`, palitan ang `PASTE_HASHED_PASSWORD_DITO` ng na-copy mong hash, tapos i-Go

### 4. Patakbuhin ang server

**Paraan A — gamit ang built-in PHP server (pinakamadali, hindi kailangan ng Apache/htdocs):**
```
cd native-marketplace
php -S localhost:8000 -t public
```
Buksan sa browser: `http://localhost:8000`

**Paraan B — gamit ang XAMPP/Apache:**
1. I-copy ang buong `native-marketplace` folder papunta sa `C:\xampp\htdocs\`
2. I-Start ang Apache sa XAMPP Control Panel
3. Buksan: `http://localhost/native-marketplace/public`

## Paano gumagana

- `/register` — pwedeng mag-sign up bilang **customer**, **admin (seller)**, o **supplier**. Ang **superadmin** ay hindi pwedeng gawin dito — dapat manual (Step 3 sa itaas), para hindi basta-basta makagawa ang kahit sino ng superadmin account.
- Pagkatapos mag-login, awtomatikong nade-redirect ang user papunta sa dashboard na naaayon sa role niya.
- Ang session ang gamit para malaman kung sino ang naka-login (`$_SESSION['user_id']`, `$_SESSION['user_role']`).

## Susunod na hakbang (para sa'yo)

Ito ay foundation lang — authentication muna. Ang mga sumusunod ay kailangan mo pang idagdag:
- Products table + CRUD (Admin/seller side)
- Orders + checkout flow (Customer side)
- Stock requests sa pagitan ng admin at supplier
- Mas maayos na role-based na "middleware" (ngayon, simpleng `if` check pa lang ito sa loob ng DashboardController)
