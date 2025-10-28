**Timeline:** 06 May 2024 - 13 Sep 2024    
**Role:** Backend API Developer     
**Type of Project:** Android Mobile App for 2FA (Academic Project)      
**Location:** Huddersfield(UK)     
**Outcome:** Learn and about existing Authentication, Authorization technologies and proposed new methodology for 2FA API    

---

# 2FA Auth API

A full-featured **Two-Factor Authentication (2FA)** backend built on **CodeIgniter 3**, designed for secure mobile communication, OTP verification, and push notifications via **Firebase Cloud Messaging (FCM)**.

This repository contains a complete **CI3 application structure** — including **Controller**, **Model**, **Helper**, and **Custom Configuration** files — optimized for scalability, security, and modular integration with mobile apps.

---

## Features

- Secure **Two-Factor Authentication (2FA)** via OTP and device verification  
- User **account registration** with unique account code generation  
- **Device token management** for push notifications  
- **Firebase Cloud Messaging (FCM)** integration for OTP and alerts  
- **Real-time device location tracking** and geofencing validation  
- **Account and location code validation** for secure user access  
- **Multi-language support** (English / French) using CI’s language system  
- **Form validation** and **XSS filtering** for safe API requests  
- **Standardized JSON API responses** for mobile app integration  
- **Centralized configuration** for Firebase and localization  

---

## Custom Configuration (`config/custom_config.php`)
- Update `$config['firebase_access_token']` and `$config['curl_url']` Firebase credentials for sending push notifications.

```php
/*
|--------------------------------------------------------------------------
| Firebase Server Key and URL
|--------------------------------------------------------------------------
*/
$config['firebase_access_token'] = 'SERVER_KEY';
$config['curl_url'] = "https://fcm.googleapis.com/v1/projects/PROJECT_ID/messages:send";
```
---
## Tech Stack  
- PHP: 7.2+
- CodeIgniter: 3.x
- Database: MySQL
- Push Service: Firebase Cloud Messaging (FCM)
- Extensions: cURL, JSON

---

## Quick Start
**Clone the repository:**
```bash
   git clone https://github.com/amitne7/2fa-auth-api;
   cd 2fa-auth-api;
```
**Create a Firebase Project and Get Access Token**
- Go to [Firebase Console](https://console.firebase.google.com/)
- Click ***“Add project”*** and create a new Firebase project.
- Navigate to ***Project Settings → General***. and get `Project ID `
- Navigate to ***Project Settings → Cloud Messaging***.
- Enable ***Cloud Messaging API (Legacy)*** and get `Server Key`
  
**Update Config**
- Open `application/config/custom.php`
- Create project on Firebase and
- Add your Server key and FCM project URL.
  
**Database Setup**
- Import database file [Databse](auth_api.sql)
- Configure database credentials in `application/config/database.php`

---
## Security Highlights
- All inputs sanitized using `$this->security->xss_clean()`
- Strong validation via `form_validation` rules
- Restricted direct script access using `defined('BASEPATH')`
- Authentication and geofencing validation ensure device authenticity
- Consistent JSON responses prevent data leakage

---
## Localization
- The app supports multilingual output via:
  ```
  application/language/english/app_lang.php
  application/language/french/app_lang.php
  ```

## Timeline
**Progress log week wise**
- [Progress log](PROGRESSLOG.md)



