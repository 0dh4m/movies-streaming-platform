# 🎬 Movies & Series Catalog Platform

[![Author](https://img.shields.io/badge/Author-Adham%20Cherkaoui%20Eddeqaqi%20(0dh4m)-10b981?style=for-the-badge)](https://github.com/0dh4m)
[![PHP](https://img.shields.io/badge/PHP-8.2-777bb4?style=for-the-badge&logo=php&logoColor=white)](https://php.net/)
[![MySQL](https://img.shields.io/badge/MySQL-PDO-4479a1?style=for-the-badge&logo=mysql&logoColor=white)](https://mysql.com/)
[![Languages](https://img.shields.io/badge/Localization-AR%20%7C%20EN%20%7C%20FR%20%7C%20IT-f59e0b?style=for-the-badge)](lang/)
[![License](https://img.shields.io/badge/License-Proprietary-blue?style=for-the-badge)](LICENSE)

> A PHP 8 + MySQL catalog for movies and series: browse by genre, search titles, save favorites, and open a watch page with third-party embed players.

[Features](#-features) • [Stack](#-stack) • [Setup](#-setup) • [Environment](#-environment-variables) • [Limitations](#-limitations)

---

## ⚖️ Important notice

This repository **does not host or stream any video files**. The watch page embeds players from third-party providers (`vidsrc.me`, `embed.su`, `multiembed.mov`) by TMDB ID. You are responsible for complying with copyright law and the terms of those providers in your jurisdiction. The catalog and metadata are for demonstration and educational use.

---

## ✨ Features

- 🎞️ **MySQL-backed catalog** — movies and series stored in `media_items`, with title, poster, rating, year and comma-separated genres.
- 🔎 **Browse and filter** — separate movie/series pages with genre filters, sorting by newest/oldest, top-rated filter and pagination.
- 🔍 **Server-side title search** — `search.php` filters the catalog by title (no external search service).
- ▶️ **Watch page** — opens a third-party embed by TMDB ID and lets the visitor switch between four providers.
- ❤️ **Favorites** — saved in the browser's `localStorage`; no user accounts required.
- 🌍 **Four languages** — Arabic (RTL), English, French and Italian, stored in `lang/*.php`.
- 📊 **Admin visitor log** — `visitors.php` shows IP address, browser, OS and visit time for the last sessions, protected by an environment-configured password.
- 🖼️ **Optional TMDB details modal** — when `TMDB_API_KEY` is set, the home page can fetch extra metadata from TMDB.

> Favorites are local to each browser. There is no registration, login, ratings, comments or recommendation engine.

---

## 🛠️ Stack

- **Backend:** PHP 8 with PDO prepared statements.
- **Database:** MySQL 8 / MariaDB (`media_items`, `settings`, `visitors_log`).
- **Frontend:** Tailwind CSS via CDN, vanilla JavaScript, custom CSS.
- **Data source:** `data/movies.json` seed catalog (about 37k movies and 37k series) imported into MySQL.

---

## 📂 Project Structure

```text
movies-streaming-platform/
├── index.php                 # Home page and optional TMDB details modal
├── movies.php                # Movie catalog, genre filters, sorting, pagination
├── series.php                # Series catalog
├── watch.php                 # Third-party embed player and server switcher
├── search.php                # Server-side title search
├── favorites.php             # localStorage favorites page
├── visitors.php              # Admin visitor log
├── 404.php                   # Custom error page
├── assets/
│   ├── css/style.css
│   └── js/main.js
├── data/
│   └── movies.json           # Seed catalog (not read at runtime)
├── database/
│   └── schema.sql            # MySQL schema and default settings
├── includes/
│   ├── db.php                # PDO connection, catalog queries, visitor logging
│   ├── header.php
│   └── footer.php
├── lang/
│   ├── ar.php / en.php / fr.php / it.php
└── scripts/
    └── import_movies.php     # Import data/movies.json into media_items
```

---

## 🚀 Setup

### 1. Create the database

```bash
mysql -u root -p < database/schema.sql
```

This creates `cinehub_db`, the `media_items`, `settings` and `visitors_log` tables, and the default `show_annoying_ads = 0` setting.

### 2. Configure the environment

`includes/db.php` reads these variables and falls back to local XAMPP defaults:

```text
MOVIES_DB_HOST=localhost
MOVIES_DB_PORT=3306
MOVIES_DB_NAME=cinehub_db
MOVIES_DB_USER=root
MOVIES_DB_PASS=
TMDB_API_KEY=            # optional
MOVIES_ADMIN_PASSWORD=   # required to open visitors.php
```

Set them in your shell, Apache `SetEnv`, or your hosting panel. Do not commit real credentials.

### 3. Import the catalog

```bash
php scripts/import_movies.php --limit=2000
```

Remove `--limit` to import the full `data/movies.json` catalog. The importer is idempotent: re-running it updates existing rows by `(type, tmdb_id)`.

### 4. Run the app

```bash
php -S localhost:8080
```

Open **http://localhost:8080**.

### 5. Admin visitor log

Open **http://localhost:8080/visitors.php** and enter `MOVIES_ADMIN_PASSWORD`. If the variable is empty, admin access is disabled.

---

## 🔐 Security Notes

- Database credentials are **not stored in source code**; they come from environment variables.
- `visitors.php` no longer has a hard-coded fallback password.
- The admin password is compared with `hash_equals()`.
- All SQL uses PDO prepared statements.
- The visitor log stores IP addresses and user-agent summaries. If you deploy this publicly, disclose that in your privacy policy and add retention limits.
- Rotate any database or TMDB credentials that were previously committed to this repository.

---

## ⚠️ Limitations

- No user accounts: favorites live in `localStorage` only.
- No video hosting or transcoding; playback depends on third-party embeds.
- Search matches titles only (not cast or directors).
- The visitor log is a simple table, not a full analytics platform.
- `data/movies.json` is a seed file; the site reads from MySQL at runtime.
- TMDB metadata is optional and requires your own API key.

---

## 👨‍💻 Author

**Adham Cherkaoui Eddeqaqi (0dh4m)**
- 🐙 GitHub: [@0dh4m](https://github.com/0dh4m)
- 🌐 Portfolio: [0dh4m.github.io](https://0dh4m.github.io/)
- ✉️ Email: [0dh4mm@gmail.com](mailto:0dh4mm@gmail.com)
- 📍 Rabat / Salé, Morocco

---

## 📄 License

Proprietary and confidential — see [LICENSE](LICENSE). Unauthorized copying or distribution is prohibited.

*Copyright © 2026 Adham Cherkaoui Eddeqaqi (0dh4m). All rights reserved.*
