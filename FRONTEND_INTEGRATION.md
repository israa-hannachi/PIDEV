# Frontend Integration into Backend - Complete Guide

**Date:** February 9, 2026  
**Project:** Naja7ni - Unified Single Symfony Application  
**Status:** ✅ Integration Complete

---

## 📋 Overview

The Naja7ni frontend Symfony project has been successfully merged into the backend Symfony project as an internal module. The frontend is now accessible at `/front/*` paths within the single unified application.

---

## 📁 Directory Structure

### New Frontend Controllers
```
src/Controller/Front/
├── FrontController.php        (Main dashboard & navigation routes)
├── ProfileController.php       (User profile management)
├── CaptchaController.php      (Captcha generation)
└── GoogleController.php       (Google OAuth integration)
```

### Frontend Templates
```
templates/front/
├── index.html.twig            (Dashboard homepage)
├── categories.html.twig       (Course categories)
├── meet.html.twig             (Virtual meetings)
├── jeux.html.twig             (Educational games)
├── events.html.twig           (Events listing)
├── forums.html.twig           (Forums & discussions)
├── partials/
│   ├── _sidebar.html.twig     (Left navigation sidebar)
│   └── _navbar.html.twig      (Top navigation bar)
└── profile/
    └── index.html.twig        (Profile page)
```

### Frontend Assets (Symlink/Copy to Public)
```
public/front/
├── assets/
│   ├── js/                    (JavaScript files)
│   ├── css/                   (Stylesheet files)
│   └── images/                (Images & icons)
└── uploads/                   (User-generated uploads)
```

---

## 🚀 Routes & Accessibility

All frontend routes are now accessible through the unified Symfony application:

| Route | Controller | Method | Name | Access |
|-------|-----------|--------|------|--------|
| `/front` | FrontController::index | GET | `app_front` | ROLE_USER |
| `/front/categories` | FrontController::categories | GET | `app_front_categories` | ROLE_USER |
| `/front/meet` | FrontController::meet | GET | `app_front_meet` | ROLE_USER |
| `/front/jeux` | FrontController::jeux | GET | `app_front_jeux` | ROLE_USER |
| `/front/events` | FrontController::events | GET | `app_front_events` | ROLE_USER |
| `/front/forums` | FrontController::forums | GET | `app_front_forums` | ROLE_USER |
| `/front/profile` | ProfileController::index | GET/POST | `app_front_profile` | ROLE_USER |
| `/front/captcha/generate` | CaptchaController::generate | GET | `captcha_generate` | - |
| `/front/connect/google` | GoogleController::connectAction | GET | `connect_google_start` | - |
| `/front/connect/google/check` | GoogleController::connectCheckAction | GET | `connect_google_check` | - |

### Route Naming Convention
- **Backend routes:** `app_*`
- **Frontend routes:** `app_front_*` or `app_front_profile_*`
- **Shared routes:** `captcha_generate`, `connect_google_start`, `connect_google_check`

---

## 🔌 Navigation Integration

### "GO TO FRONT" Button
**Location:** `templates/partials/_header.html.twig` (Backend Header)  
**Before:**
```twig
<a href="http://127.0.0.1:8001/" class="btn btn-light-brand">GO TO FRONT</a>
```

**After:**
```twig
<a href="{{ path('app_front') }}" class="btn btn-light-brand">GO TO FRONT</a>
```

### Frontend Navigation
All frontend templates use Symfony routing for internal links:
```twig
<!-- Sidebar Navigation -->
<a href="{{ path('app_front') }}">Home</a>
<a href="{{ path('app_front_categories') }}">Categories</a>
<a href="{{ path('app_front_profile') }}">Profile</a>
<a href="{{ path('app_front_meet') }}">Meet</a>
<a href="{{ path('app_front_jeux') }}">Games</a>
<a href="{{ path('app_front_events') }}">Events</a>
<a href="{{ path('app_front_forums') }}">Forums</a>
```

---

## 🔐 Security & Authentication

All frontend routes require `ROLE_USER` authentication via the `#[IsGranted('ROLE_USER')]` attribute.

### Protected Routes
- ✅ `/front` (All dashboard & info pages)
- ✅ `/front/categories`
- ✅ `/front/meet`
- ✅ `/front/jeux`
- ✅ `/front/events`
- ✅ `/front/forums`
- ✅ `/front/profile`

### Public Routes
- ✅ `/front/captcha/generate` (For registration)
- ✅ `/front/connect/google` (OAuth initiation)
- ✅ `/front/connect/google/check` (OAuth callback)

---

## 📦 Asset Linking

### Correct Asset Usage in Frontend Templates

All frontend templates use `{{ asset() }}` function for proper asset linking:

```twig
<!-- Stylesheets -->
<link rel="stylesheet" href="{{ asset('assets/css/main.css') }}">

<!-- Images -->
<img src="{{ asset('assets/images/logo-small.png') }}" alt="Logo">
<img src="{{ asset('assets/images/logo-full.png') }}" alt="Logo">

<!-- User Avatars -->
<img src="{{ asset('uploads/profiles/' ~ app.user.profilePicture) }}" alt="Profile">

<!-- JavaScript -->
<script src="{{ asset('assets/js/main.js') }}"></script>
```

### File Organization
- Place CSS files in: `public/assets/css/`
- Place JS files in: `public/assets/js/`
- Place images in: `public/assets/images/`
- Place user uploads in: `public/uploads/profiles/` (symlink to `public/front/uploads/`)

---

## 🔄 Namespace Convention

### Controller Namespaces
- **Backend Controllers:** `App\Controller\*`
- **Frontend Controllers:** `App\Controller\Front\*`

### Template Organization
- **Backend Templates:** `templates/{feature}/`
- **Frontend Templates:** `templates/front/{feature}/`

---

## ✨ Features Integrated

### 1. **Frontend Dashboard**
- Welcome greeting with user's first name
- Statistics cards (courses, certificates, XP, study time)
- Recent courses with progress bars
- Gamification (badges & leaderboard)

### 2. **Navigation**
- Top navbar with search, notifications, settings, user profile, logout
- Left sidebar with icon-based navigation
- Active route highlighting using `app.request.attributes.get('_route')`

### 3. **User Profile**
- Profile picture upload
- Cover picture upload
- Form validation & file handling
- Flash messages for success/error feedback

### 4. **Content Pages**
- **Categories:** Course category listing with modules
- **Meet:** Virtual meetings with quick join functionality
- **Jeux:** Educational games with player statistics
- **Events:** Event listings with registration
- **Forums:** Forum categories with trending discussions

### 5. **Security Features**
- Captcha generation for forms
- Google OAuth integration
- User authentication requirement
- ROLE_USER access control

---

## 🎨 Styling & UI

### CSS Framework
- **Tailwind CSS** (CDN)
- **Lucide Icons** (CDN)
- **Custom CSS:** Optional for specific styles

### Design Features
- Responsive grid layouts
- Gradient backgrounds
- Icon-based navigation
- Smooth transitions & hover effects
- Mobile-responsive design

---

## 📋 Configuration Checklist

### ✅ Completed Tasks
- [x] Created `src/Controller/Front/` directory with 4 controllers
- [x] Created `templates/front/` directory with 7 template files
- [x] Created frontend partials (_sidebar, _navbar)
- [x] Created `public/front/` directory structure for assets
- [x] Updated "GO TO FRONT" button to use `{{ path('app_front') }}`
- [x] Configured all routes with `ROLE_USER` authentication
- [x] Set up proper namespace conventions
- [x] Implemented correct asset() paths in templates
- [x] Added route name conventions for consistency

### ⚠️ Manual Tasks Required
- [ ] Copy frontend CSS files from `NAJA7NI_FRONT/assets/css/` to `public/assets/css/`
- [ ] Copy frontend JS files from `NAJA7NI_FRONT/assets/js/` to `public/assets/js/`
- [ ] Copy frontend images from `NAJA7NI_FRONT/public/assets/images/` to `public/assets/images/`
- [ ] (Optional) Copy `public/front/uploads/` data if migrating existing uploads
- [ ] Verify Forms with database entities
- [ ] Set up OAuth Google credentials if using authentication
- [ ] Create database migrations if needed

---

## 🚀 Next Steps

### 1. Copy Frontend Assets
```bash
# From Windows File Explorer or terminal
# Copy NAJA7NI_FRONT/assets/* to TEMPLATE_BACK_NAJA7NI/public/assets/
# Copy NAJA7NI_FRONT/public/uploads/* to TEMPLATE_BACK_NAJA7NI/public/front/uploads/
```

### 2. Test the Integration
```bash
# Start the Symfony server
symfony server:start -d

# Visit these URLs
http://localhost:8000/front          # Frontend Dashboard
http://localhost:8000/front/profile  # Profile Page
http://localhost:8000              # Backend Home (click "GO TO FRONT")
```

### 3. Verify Routes
```bash
# List all routes
symfony console debug:router

# Filter frontend routes
symfony console debug:router | grep app_front
```

### 4. Check Security
```bash
# Ensure authentication is working
# Try accessing /front without logging in (should redirect to /login)
# Log in and verify all pages load correctly
```

### 5. Forms & Database
- Update `ProfileType` form with all required fields
- Run migrations if database changes needed
- Test file uploads (profile pictures)

---

## 📚 Useful Commands

```bash
# Clear cache after changes
symfony console cache:clear

# Generate routes documentation
symfony console debug:router

# Test specific controller
symfony console debug:router app_front

# Check templates
symfony console debug:twig

# Run tests
symfony console test
php bin/phpunit
```

---

## 🔗 File References

### Controllers
- [FrontController.php](src/Controller/Front/FrontController.php)
- [ProfileController.php](src/Controller/Front/ProfileController.php)
- [CaptchaController.php](src/Controller/Front/CaptchaController.php)
- [GoogleController.php](src/Controller/Front/GoogleController.php)

### Templates
- [index.html.twig](templates/front/index.html.twig)
- [categories.html.twig](templates/front/categories.html.twig)
- [meet.html.twig](templates/front/meet.html.twig)
- [jeux.html.twig](templates/front/jeux.html.twig)
- [events.html.twig](templates/front/events.html.twig)
- [forums.html.twig](templates/front/forums.html.twig)
- [profile/index.html.twig](templates/front/profile/index.html.twig)

### Partials
- [_sidebar.html.twig](templates/front/partials/_sidebar.html.twig)
- [_navbar.html.twig](templates/front/partials/_navbar.html.twig)

### Updated Files
- [_header.html.twig](templates/partials/_header.html.twig) - Updated "GO TO FRONT" button

---

## 🎯 Summary

The Naja7ni frontend has been successfully integrated into the backend Symfony application as a unified system. The frontend is now:

✅ **Accessible** at `/front/*` paths  
✅ **Secure** with ROLE_USER authentication  
✅ **Maintainable** with clear namespace & directory conventions  
✅ **Routed** using Symfony routing (no hardcoded URLs)  
✅ **Styled** with Tailwind CSS & custom designs  
✅ **Responsive** across all devices  

The single Symfony application now serves both backend admin functionality and frontend user-facing features seamlessly!

---

**Questions?** Check the Symfony documentation or review specific controller implementations for detailed logic.
