# Installation Guide - Event Registration Module

## System Requirements

- **Drupal**: 10.0 or higher
- **PHP**: 8.1 or higher
- **MySQL**: 5.7+ or MariaDB 10.3+
- **Composer**: 2.0 or higher

## Pre-Installation Steps

### 1. Verify Drupal Installation

```bash
# From Drupal root directory
composer show drupal/core | grep Version
```

Should output: `Version : 10.x.x` or higher

### 2. Check PHP Version

```bash
php --version
```

Should be PHP 8.1 or higher

### 3. Backup Database

```bash
# Create database backup before installing any module
mysqldump -u username -p database_name > backup_`date +%Y%m%d_%H%M%S`.sql
```

## Installation Steps

### Step 1: Download Module

**Option A: Using Git**

```bash
cd web/modules/custom
git clone <repository-url> event_registration
cd event_registration
```

**Option B: Manual Download**

1. Download the module code as ZIP
2. Extract to `web/modules/custom/event_registration`
3. Verify directory structure matches:
   ```
   event_registration/
   ├── src/
   │   ├── Form/
   │   ├── Controller/
   │   ├── Service/
   │   └── Mail/
   ├── config/
   ├── event_registration.info.yml
   ├── event_registration.install
   ├── event_registration.module
   ├── README.md
   └── [other files]
   ```

### Step 2: Clear Cache

**Via Drush (if available):**

```bash
drush cache:rebuild
```

**Via Drupal UI:**

1. Navigate to `/admin/config/development/performance`
2. Click "Clear all caches"

### Step 3: Enable Module

**Via Drupal UI (Recommended):**

1. Go to `/admin/modules`
2. Search for "Event Registration"
3. Check the checkbox
4. Click "Install"
5. Wait for installation to complete

**Via Drush:**

```bash
drush en event_registration
```

**Via Composer (Alternative):**

```bash
# If module is published on Drupal.org
composer require drupal/event_registration
drush en event_registration
```

### Step 4: Verify Installation

After enabling the module, verify:

1. Navigate to `/admin/modules`
2. Search for "Event Registration"
3. Confirm it shows as "Enabled"
4. Check for any warning messages

### Step 5: Database Tables Creation

The module automatically creates required tables when enabled:

- `event_config` - Event configuration
- `event_registration` - User registrations

**Verify tables exist:**

```bash
mysql -u username -p database_name
SHOW TABLES LIKE 'event_%';
```

Should show:

```
event_config
event_registration
```

## Post-Installation Configuration

### 1. Set Admin Email

Navigate to `/admin/config/event-registration/settings`:

1. Enter your administrator email address
2. Enable/disable admin notifications as desired
3. Customize email subject line (optional)
4. Click "Save configuration"

**Expected behavior:** Settings saved without errors

### 2. Configure User Permissions

Navigate to `/admin/people/permissions`:

Find these permissions:

- "Administer event registration" - Can create events and access settings
- "Access event registrations" - Can view registrations and export CSV
- "Access content" - Can access the public registration form

Assign to appropriate roles:

```
Administrator: All permissions
Authenticated User: "Access content" only
Event Manager: "Administer event registration" + "Access event registrations"
```

Click "Save permissions"

### 3. Create First Event

Navigate to `/admin/event-registration/config`:

Fill in test event details:

- **Event Name**: "Welcome Workshop"
- **Category**: "Online Workshop"
- **Event Date**: Select date (7 days from now recommended)
- **Registration Start**: Today's date
- **Registration End**: 3-5 days from now

Click "Save Event Configuration"

Expected: Confirmation message "Event configuration saved successfully!"

## Verification Checklist

After installation, verify:

- [ ] Module appears in `/admin/modules`
- [ ] Tables created: `event_config`, `event_registration`
- [ ] Configuration page accessible: `/admin/config/event-registration/settings`
- [ ] Admin email configured
- [ ] First event created successfully
- [ ] Event appears in registration form dropdown
- [ ] Registration form accessible at `/event-register`
- [ ] No error messages in `/admin/reports/dblog`

## Uninstallation

To remove the module:

**Via Drupal UI:**

1. Go to `/admin/modules/uninstall`
2. Search for "Event Registration"
3. Check the checkbox
4. Click "Uninstall"
5. Confirm the uninstallation

**Via Drush:**

```bash
drush pmu event_registration
```

**Database Cleanup (Optional):**

```sql
DROP TABLE IF EXISTS event_config;
DROP TABLE IF EXISTS event_registration;
DELETE FROM config WHERE name = 'event_registration.settings';
```

## Troubleshooting Installation Issues

### Issue: Module Not Appearing in Module List

**Solution:**

1. Clear Drupal cache:
   ```bash
   drush cache:rebuild
   # OR via UI: /admin/config/development/performance
   ```
2. Clear file system cache:
   ```bash
   rm -rf web/sites/default/files/php
   ```
3. Verify module directory structure is correct
4. Check `event_registration.info.yml` syntax

### Issue: Database Table Creation Failed

**Solution:**

1. Check database user has CREATE TABLE permission
2. Verify database is accessible
3. Check database logs for errors
4. Manually create tables using provided SQL:
   ```bash
   mysql -u username -p database_name < web/modules/custom/event_registration/event_registration.sql
   ```

### Issue: Permission Denied Errors

**Solution:**

1. Verify file permissions:

   ```bash
   chmod 755 web/modules/custom/event_registration
   chmod 644 web/modules/custom/event_registration/*.yml
   chmod 644 web/modules/custom/event_registration/*.php
   chmod 755 web/modules/custom/event_registration/src
   chmod 755 web/modules/custom/event_registration/src/*
   chmod 644 web/modules/custom/event_registration/src/*/*.php
   ```

2. Verify directory ownership:
   ```bash
   chown -R www-user:www-group web/modules/custom/event_registration
   ```

### Issue: Configuration Page Shows Error

**Solution:**

1. Clear cache
2. Check `/admin/reports/dblog` for specific errors
3. Verify `event_registration.services.yml` syntax
4. Check database connection

### Issue: Form Not Displaying

**Solution:**

1. Verify at least one active event exists
2. Check event registration dates are current
3. Check browser console for JavaScript errors
4. Verify form permissions: authenticated user needs "access content"

## Testing After Installation

See `TESTING.md` for comprehensive testing procedures:

```bash
# Quick smoke test
# 1. Access registration form: /event-register
# 2. Create test event: /admin/event-registration/config
# 3. Register for event: /event-register
# 4. View registrations: /admin/event-registration/listings
```

## Multi-Site Drupal

For Drupal multi-site installations:

1. Place module in shared location:

   ```bash
   web/modules/custom/event_registration
   ```

2. Enable per site or all sites:

   ```bash
   # Per site
   drush -l site1.local en event_registration

   # All sites
   drush -l all en event_registration
   ```

3. Configure settings per site at:
   `/admin/config/event-registration/settings` (site-specific)

## Updating the Module

To update to a newer version:

**From Git:**

```bash
cd web/modules/custom/event_registration
git pull origin main
drush cache:rebuild
```

**Check for Schema Changes:**

```bash
drush updatedb
```

**Verify Installation:**

- Check `/admin/reports/dblog` for any errors
- Test registration form
- Test admin pages

## Production Deployment

For production environments:

1. **Install on Staging First**

   ```bash
   git clone <url> web/modules/custom/event_registration
   drush en event_registration
   ```

2. **Run Tests**
   - Test all forms
   - Verify emails send
   - Test admin pages

3. **Backup Production Database**

   ```bash
   mysqldump -u user -p database > backup_premodule.sql
   ```

4. **Deploy to Production**

   ```bash
   git pull
   drush en event_registration
   drush cache:rebuild
   ```

5. **Verify on Production**
   - Access configuration page
   - Create test event
   - Test registration
   - Check logs

6. **Configure Email Service**
   - Ensure SMTP/Mailgun configured
   - Send test email
   - Verify delivery

## Support Resources

- **Documentation**: See `README.md`
- **Testing Guide**: See `TESTING.md`
- **Database Schema**: See `event_registration.sql`
- **Drupal Docs**: https://www.drupal.org/docs/10
- **GitHub Issues**: [Your Repository Issues]

## Next Steps

After successful installation:

1. Read [README.md](README.md) for feature overview
2. Configure admin email in settings
3. Create sample events
4. Test registration form
5. Review [TESTING.md](TESTING.md) for testing procedures
6. Configure permissions for your users
7. Set up email notifications
8. Train content editors/administrators

## Rollback Procedure

If issues occur:

```bash
# Disable module
drush pmu event_registration

# Restore database backup
mysql -u username -p database_name < backup_20240101_120000.sql

# Clear cache
drush cache:rebuild
```

For detailed support, refer to the troubleshooting section in `README.md`.
