# 🎬 Movies & Series Streaming Platform

[![Author](https://img.shields.io/badge/Author-Adham%20Cherkaoui%20Eddeqaqi%20(0dh4m)-10b981?style=for-the-badge)](https://github.com/0dh4m)
[![PHP](https://img.shields.io/badge/PHP-8.2-777bb4?style=for-the-badge&logo=php&logoColor=white)](https://php.net/)
[![Multilingual](https://img.shields.io/badge/Localization-AR%20%7C%20EN%20%7C%20FR%20%7C%20IT-f59e0b?style=for-the-badge)](lang/)
[![Frontend](https://img.shields.io/badge/UI-Modern%20Dark%20Cinema-e11d48?style=for-the-badge)](assets/)
[![License](https://img.shields.io/badge/License-Proprietary-blue?style=for-the-badge)](LICENSE)

> **High-Performance Cinema & Series Streaming Web Platform** engineered by **Adham Cherkaoui Eddeqaqi (`0dh4m`)**. Features a comprehensive media catalog, live embedded video streaming, full multilingual internationalization (Arabic RTL, English, French, Italian), real-time search, user favorites, and visitor traffic analytics.

---

## 🌟 Key Features & Highlights

1. **Rich Media Catalog & Streaming Engine (`index.php`, `watch.php`, `movies.php`, `series.php`):**
   - High-definition video player integration with multi-server stream switching.
   - Separate browsing views for trending Movies, latest TV series, and seasonal releases.
   - Comprehensive media metadata: IMDb ratings, release year, genres, plot synopsis, and duration.

2. **Multilingual Internationalization (i18n):**
   - Seamless 4-language localization switcher:
     - 🇲🇦 **Arabic (`lang/ar.php`):** Full Right-to-Left (RTL) typography and localized UI.
     - 🇬🇧 **English (`lang/en.php`):** Global modern layout.
     - 🇫🇷 **French (`lang/fr.php`):** Complete French localization.
     - 🇮🇹 **Italian (`lang/it.php`):** Complete Italian localization.

3. **Instant Search & Real-Time Filtering (`search.php`, `assets/js/main.js`):**
   - Debounced instant query search across titles, directors, and cast.
   - Category and release year filters with smooth transitions.

4. **User Favorites & Watchlist (`favorites.php`):**
   - Bookmark movies and series with instant LocalStorage / session synchronization.

5. **Traffic & Analytics Tracking (`visitors.php`):**
   - Real-time visitor logger tracking daily view counts, unique visitors, and geographic access.

---

## 📂 Project Architecture

```
├── index.php                 # Home showcase & featured cinema carousels
├── movies.php                # Dedicated movies catalog & category browser
├── series.php                # TV Series index & episode selector
├── watch.php                 # Responsive cinema video player & streaming engine
├── search.php                # Multi-criteria instant search
├── favorites.php             # User saved watchlist & favorites
├── visitors.php              # Real-time traffic analytics & visitor logs
├── 404.php                   # Custom branded cinema error page
├── assets/
│   ├── css/style.css         # Dark cinema glassmorphism styles
│   └── js/main.js            # Frontend interactivity, favorites & search
├── data/
│   └── movies.json           # Comprehensive movie & series catalog database
├── includes/
│   ├── header.php            # Multilingual navigation header & language selector
│   ├── footer.php            # Branded footer & quick links
│   └── db.php                # Core utility functions & data handlers
└── lang/
    ├── ar.php                # Arabic language dictionary (RTL)
    ├── en.php                # English language dictionary
    ├── fr.php                # French language dictionary
    └── it.php                # Italian language dictionary
```

---

## 🚀 Local Setup & Installation

1. **Clone the repository:**
   ```bash
   git clone https://github.com/0dh4m/movies-streaming-platform.git
   cd movies-streaming-platform
   ```

2. **Run using PHP built-in server:**
   ```bash
   php -S localhost:8080
   ```

3. **Open in browser:**
   - Navigate to `http://localhost:8080`.

---

## 👨‍💻 Author & Contact

**Adham Cherkaoui Eddeqaqi (0dh4m)**  
- **GitHub:** [@0dh4m](https://github.com/0dh4m)  
- **Portfolio:** [0dh4m.github.io/portfolio](https://0dh4m.github.io/portfolio/)  
- **Email:** [0dh4mm@gmail.com](mailto:0dh4mm@gmail.com)  
- **Location:** Rabat / Salé, Morocco
