# Square Production Setup Guide

## 📋 Prerequisites
- Domain transferred and pointing to Hostinger
- SSL certificate active (HTTPS)
- WordPress admin access
- Square account with production credentials

---

## Step 1: Get Production Credentials from Square

1. **Log in to Square Developer Dashboard**
   - Go to: https://developer.squareup.com/apps
   - Select your application

2. **Switch to Production Mode**
   - In the left sidebar, click "Production" (not Sandbox)

3. **Copy Your Credentials:**
   
   **Application ID:**
   - Go to "Credentials" tab
   - Copy the "Application ID" (starts with `sq0idp-` for production)
   
   **Access Token:**
   - In the same "Credentials" tab
   - Under "Production Access Token"
   - Click "Show" and copy the token (starts with `EAAA...`)
   
   **Location ID:**
   - Go to "Locations" tab
   - Copy the Location ID for your business location
   - Format: alphanumeric string (e.g., `L1234567890ABC`)

---

## Step 2: Update WordPress Settings

1. **Navigate to Square Settings:**
   ```
   WordPress Admin → Settings → Square Integration
   ```

2. **Update Configuration:**
   - ✅ **Uncheck** "Sandbox Mode"
   - ✅ Paste **Production Application ID**
   - ✅ Paste **Production Access Token**
   - ✅ Paste **Production Location ID**
   - ✅ Click "Save Settings"

3. **Verify:**
   - You should see "✓ Square is configured and ready!" at the top
   - The webhook URL should show your production domain

---

## Step 3: Configure Square Webhooks (Optional but Recommended)

Webhooks allow Square to notify your site about payment status changes.

1. **In Square Developer Dashboard:**
   - Go to "Webhooks" tab
   - Click "Add Endpoint"

2. **Add Your Webhook URL:**
   ```
   https://YOUR-DOMAIN.com/square-webhook/
   ```
   ⚠️ Replace `YOUR-DOMAIN.com` with your actual domain

3. **Select Events:**
   - `payment.created`
   - `payment.updated`
   - `refund.created`
   - `refund.updated`

4. **Save & Activate**

---

## Step 4: Test Production Payment

⚠️ **This will process a REAL payment!**

1. **Add a book to cart**
2. **Go to checkout**
3. **Use a real credit card** (you can refund it later)
4. **Complete the order**
5. **Verify:**
   - ✅ Order confirmation page displays
   - ✅ Order appears in "My Account > Purchases"
   - ✅ Payment shows in Square Dashboard
   - ✅ Confirmation email received

---

## 🔧 Troubleshooting

### Payment Fails with "Invalid Credentials"
- Double-check you're using **Production** credentials (not Sandbox)
- Verify "Sandbox Mode" is **unchecked**
- Make sure there are no extra spaces in credentials

### Redirect to Cart Instead of Order Confirmation
- Check PHP error logs: `wp-content/debug.log`
- Verify orders table exists (visit `/wp-admin/?dbc_create_orders_table=1`)
- Check browser console for JavaScript errors

### Order Not Showing in My Account
- Verify you're logged in as the same user/email
- Check database table `wp_dbc_orders` exists
- Orders are linked by both `user_id` AND `customer_email`

---

## 📞 Support

### Square Support
- Dashboard: https://developer.squareup.com/apps
- Docs: https://developer.squareup.com/docs
- Support: https://squareup.com/help/contact

### WordPress Admin URLs
- Square Settings: `/wp-admin/options-general.php?page=dbt-square`
- Orders Table: `/wp-admin/?dbc_create_orders_table=1`

---

## 🎯 Quick Reference

| Setting | Sandbox | Production |
|---------|---------|------------|
| **Sandbox Mode Checkbox** | ✅ Checked | ⬜ Unchecked |
| **SDK URL** | `sandbox.web.squarecdn.com` | `web.squarecdn.com` |
| **App ID Prefix** | `sandbox-sq0idb-...` | `sq0idp-...` |
| **Access Token Prefix** | `EAAA...` (sandbox) | `EAAA...` (production) |
| **Test Cards** | Work | Don't work |
| **Real Cards** | Don't work | Work ✅ |

---

## ✅ Post-Launch Checklist

After switching to production:

- [ ] Test a real payment (can refund later)
- [ ] Verify order confirmation page works
- [ ] Check order appears in My Account
- [ ] Confirm email is sent
- [ ] Payment shows in Square Dashboard
- [ ] Webhook endpoint is configured (optional)
- [ ] Remove/archive sandbox credentials (security)
- [ ] Update any internal documentation

---

**Last Updated:** January 12, 2026  
**Current Mode:** Sandbox (switch after domain transfer)
