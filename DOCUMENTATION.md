# Documentation Summary

This directory contains all documentation for the Drew Bankston website project.

## 📄 Available Documentation

### 1. **README.md**
Main project overview with:
- Project structure
- Deployment instructions
- Feature list
- Development setup

### 2. **SQUARE-PRODUCTION-SETUP.md** ⭐
Step-by-step guide for switching Square from sandbox to production mode:
- Getting production credentials from Square
- Updating WordPress settings
- Configuring webhooks
- Testing production payments
- Troubleshooting guide

**Use this after domain transfer is complete!**

### 3. **HOSTINGER-SERVER-INFO.md** 🔐
Server access credentials and deployment information:
- SFTP/SSH credentials
- WordPress admin login
- Directory structure
- Quick reference commands

**⚠️ This file is gitignored and should never be committed!**

---

## 🚀 Quick Start

### Deploying to Hostinger
```powershell
$env:HOSTINGER_PASSWORD='your-password'; node scripts/migrate-to-hostinger.js
```

### Switching to Production Payments
See **SQUARE-PRODUCTION-SETUP.md** for the complete guide.

---

## 📞 Important Links

- **Live Site:** https://honeydew-caribou-244132.hostingersite.com (temporary)
- **WordPress Admin:** `/wp-admin/`
- **Square Settings:** WordPress Admin → Settings → Square Integration
- **Hostinger Panel:** https://hpanel.hostinger.com/

---

## 🗂️ Site Plan Directory

Additional planning documents in `Site Plan/`:
- Design guidelines
- Content maps
- Sitemaps
- Asset library

---

**Last Updated:** January 12, 2026
