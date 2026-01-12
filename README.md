# Drew Bankston Author Website

Custom WordPress theme and plugin for author Drew Bankston's official website.

## 🌐 Live Site

- **Production:** TBD (domain transferring from Wix)
- **Staging:** https://honeydew-caribou-244132.hostingersite.com

## 📦 Project Structure

```
Drew Bankston Website/
├── theme/
│   └── drew-bankston/              # Custom WordPress theme
├── plugins/
│   └── drew-bankston-custom/       # Custom plugin (CPTs, cart, payments)
├── scripts/
│   └── migrate-to-hostinger.js     # Deployment script
├── Site Plan/                      # Design docs and content maps
├── temp-uploads/                   # Media files for deployment
├── SQUARE-PRODUCTION-SETUP.md      # Square payment setup guide
└── HOSTINGER-SERVER-INFO.md        # Server access (local only, gitignored)
```

## 🚀 Deployment

### Upload Theme & Plugin to Hostinger

```powershell
$env:HOSTINGER_PASSWORD='your-password'; node scripts/migrate-to-hostinger.js
```

This uploads:
- Custom theme files
- Custom plugin files
- Media files (if present in `temp-uploads/`)

## 🛠️ Features

### Custom Post Types
- **Books** - Author's published works
- **Series** - Book series organization
- **Events** - Author appearances and book signings

### E-commerce
- Custom shopping cart system
- Signed book purchases
- Square payment integration (sandbox + production)
- Order history linked to user accounts

### User Accounts
- Profile management
- Newsletter subscription
- Free chapter downloads
- Purchase history

## 🔧 Development

### Prerequisites
- Node.js (for deployment scripts)
- SSH2 SFTP Client (`npm install`)

### Local Setup
1. Clone the repository
2. Install dependencies: `npm install`
3. Theme files: `theme/drew-bankston/`
4. Plugin files: `plugins/drew-bankston-custom/`

### Key Files
- `theme/drew-bankston/functions.php` - Theme setup and Square config
- `plugins/drew-bankston-custom/includes/class-square-payment.php` - Payment processing
- `theme/drew-bankston/page-checkout.php` - Checkout page with Square Web Payments SDK
- `theme/drew-bankston/page-account.php` - User account dashboard

## 📚 Documentation

- **[Square Production Setup](SQUARE-PRODUCTION-SETUP.md)** - How to switch from sandbox to live payments
- **[Server Info](HOSTINGER-SERVER-INFO.md)** - Server access and deployment (local only)

## 🔐 Security

**Important:** Never commit sensitive files:
- Server credentials
- API keys
- Database passwords
- `HOSTINGER-SERVER-INFO.md`

See `.gitignore` for excluded files.

## 📞 Support

- **WordPress Admin:** `/wp-admin/`
- **Square Settings:** WordPress Admin → Settings → Square Integration
- **Hostinger Panel:** https://hpanel.hostinger.com/

---

**Built for Drew Bankston**  
**Last Updated:** January 2026
