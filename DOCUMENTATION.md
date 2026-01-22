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

## ⚠️ Known Issues

### Caching Plugin Interference

The Hostinger server has a caching plugin installed that frequently causes issues with CSS and theme updates not appearing on the live site. This has been a recurring problem during development.

**Symptoms:**
- CSS changes not applying after deployment
- New styles showing `display: block` instead of expected values
- Theme updates appearing cached/stale even with browser cache-busting (`?nocache=X`)

**Workarounds used:**
1. **Inline styles in templates** - For critical CSS like grids, we've added inline `<style>` blocks directly in PHP templates (e.g., `front-page.php`) to bypass external CSS caching
2. **Cache-busting query parameters** - Adding `?nocache=X` to URLs for testing (doesn't always work due to server-side caching)
3. **WordPress Admin cache clear** - Check for cache clearing options in WordPress Admin

**Recommended solutions:**
- Clear the cache from Hostinger's hPanel after each deployment
- Consider disabling aggressive caching during active development
- Contact Hostinger support if caching continues to cause issues
- Document any cache-related plugins installed and their settings

**Files affected by this workaround:**
- `theme/drew-bankston/front-page.php` - Contains inline styles for `.transmissions-grid` and `.transmission-card` components

---

## 📞 Important Links

- **Live Site:** https://drewbankston.com
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

**Last Updated:** January 22, 2026
