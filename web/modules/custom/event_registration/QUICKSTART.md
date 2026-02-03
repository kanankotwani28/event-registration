# Event Registration Module - Quick Start Guide

## What's Included

This is a **complete, production-ready Drupal 10 event registration module** with:

✅ Event configuration management  
✅ Dynamic event registration form with AJAX  
✅ Email notifications (users & admins)  
✅ Admin dashboard with CSV export  
✅ Comprehensive validation & duplicate prevention  
✅ Full documentation & testing guide

## Quick Links

📖 **Documentation**

- [README.md](README.md) - Complete feature documentation
- [INSTALLATION.md](INSTALLATION.md) - Step-by-step setup
- [TESTING.md](TESTING.md) - Testing procedures with 10+ scenarios
- [PROJECT_SUMMARY.md](PROJECT_SUMMARY.md) - Delivery checklist & status

## 5-Minute Setup

### 1. Enable the Module

```bash
# Navigate to /admin/modules
# Find "Event Registration" and click Install
# OR use: drush en event_registration
```

### 2. Configure Email

```
Go to: /admin/config/event-registration/settings
Enter: Your admin email address
Save
```

### 3. Create an Event

```
Go to: /admin/event-registration/config
Fill in:
  - Event Name: "Test Workshop"
  - Category: "Online Workshop"
  - Event Date: (pick a date)
  - Registration Start: Today
  - Registration End: 3 days from now
Click: Save Event Configuration
```

### 4. Test Registration

```
Go to: /event-register
Fill in the form and submit
Check that:
  - Confirmation message appears
  - Dropdowns work dynamically
  - Email sent (if configured)
```

## File Structure

```
event_registration/
│
├── Core Module Files (YAML)
│   ├── event_registration.info.yml ........ Module metadata
│   ├── event_registration.install ........ Database schema
│   ├── event_registration.module ......... Module hooks
│   ├── event_registration.routing.yml ... Routes (5 routes)
│   ├── event_registration.services.yml .. Services (4 services)
│   ├── event_registration.permissions.yml Custom permissions
│   ├── event_registration.links.menu.yml  Admin menu
│   └── event_registration.sql ........... SQL schema
│
├── Forms (src/Form/)
│   ├── EventRegistrationForm.php ........ Public registration form
│   ├── EventConfigForm.php ............ Event creation form
│   ├── AdminConfigForm.php ........... Settings form
│   └── AdminListingsForm.php ......... Registrations listing
│
├── Services (src/Service/)
│   ├── EventService.php .............. Event operations
│   ├── RegistrationService.php ....... Registration ops
│   └── ValidationService.php ........ Input validation
│
├── Other Classes
│   ├── src/Controller/AdminListingsController.php
│   ├── src/Mail/MailHandler.php
│   └── config/schema/event_registration.schema.yml
│
└── Documentation
    ├── README.md ................... Full documentation
    ├── INSTALLATION.md ............ Setup guide
    ├── TESTING.md ................ Testing procedures
    ├── PROJECT_SUMMARY.md ........ Delivery status
    └── QUICKSTART.md ............ This file
```

## Admin URLs

| URL                                         | Purpose                      |
| ------------------------------------------- | ---------------------------- |
| `/event-register`                           | **Public registration form** |
| `/admin/event-registration/config`          | Create events                |
| `/admin/config/event-registration/settings` | Email settings               |
| `/admin/event-registration/listings`        | View registrations           |
| `/admin/event-registration/export-csv`      | Export as CSV                |

## Key Features

### 📋 Event Configuration

- Create events with date ranges
- Define registration periods (start/end dates)
- Assign categories (Online Workshop, Hackathon, Conference, One-day Workshop)
- Events only appear during registration period

### 📝 Registration Form

- **Dynamic dropdowns**: Category → Event Date → Event Name (via AJAX)
- **Full validation**: Email format, no special chars, duplicate prevention
- **Responsive**: Works on all devices
- **Accessible**: Proper labels and error messages

### 💌 Email Notifications

- **User emails**: Confirmation with event details
- **Admin emails**: New registration notifications (toggleable)
- **Customizable**: Subject line configurable in settings
- **Powered by**: Drupal Mail API (works with any mail service)

### 📊 Admin Dashboard

- **Filter registrations** by event date and name
- **View details**: Name, email, college, department, submission date
- **Participant count**: See how many registered
- **CSV export**: Download all data in spreadsheet format

### ✅ Validation

| Field        | Rules                               |
| ------------ | ----------------------------------- |
| Name         | Letters and spaces only             |
| Email        | Valid email format required         |
| College/Dept | Letters, numbers, and spaces only   |
| Duplicate    | Email + Event Date combo prevented  |
| Required     | All fields must be filled           |

## Database Tables

### event_config (Events)

```
id | event_name | category | event_date | reg_start | reg_end | created
```

### event_registration (User Registrations)

```
id | event_id | event_name | category | event_date | name | email | college | department | created
```

## Permissions

Two custom permissions:

- **"Administer event registration"** - Create events, configure settings
- **"Access event registrations"** - View registrations, export CSV

Assign in `/admin/people/permissions`

## Troubleshooting

### Form shows "Event registration is currently closed"

- Create an event first at `/admin/event-registration/config`
- Make sure registration period is active (start date ≤ today ≤ end date)
- Make sure event date is in the future

### AJAX dropdowns not working

- Clear browser cache
- Check browser console for JavaScript errors
- Verify jQuery is loaded (included by Drupal)

### Emails not sending

- Configure Drupal mail system (SMTP, Mailgun, etc.)
- Check `/admin/reports/dblog` for mail errors
- Verify admin email is set in settings

### "You have already registered for an event on this date"

- This is correct! Users cannot register twice for the same event date

## Getting Help

1. Check [INSTALLATION.md](INSTALLATION.md) for setup issues
2. Check [TESTING.md](TESTING.md) for feature testing
3. Check [README.md](README.md) for detailed documentation
4. Check Drupal logs: `/admin/reports/dblog`

## Development Notes

### For Developers

- **PSR-4 autoloading**: All classes properly namespaced
- **Dependency injection**: No static `\Drupal::` calls in business logic
- **Clean code**: Follows Drupal 10 coding standards
- **Testable**: Service layer design allows easy testing

### Services Available

```php
// Use in your code:
$this->eventService->getActiveEvents()
$this->registrationService->sendConfirmationEmail()
$this->validationService->isDuplicateRegistration()
$this->mailHandler->sendUserConfirmation()
```

## What's NOT Included (By Design)

❌ No contrib modules - Uses only Drupal core  
❌ No hardcoded values - All config via Config API  
❌ No database.php calls - Uses Drupal Database API  
❌ No custom CSS - Uses Drupal form styling  
❌ No 3rd party JS - Uses jQuery (included by Drupal)

## Version Info

- **Drupal**: 10.0+
- **PHP**: 8.1+
- **Module Version**: 1.0.0
- **Database**: MySQL 5.7+ / MariaDB 10.3+

## License

This module is provided as-is for Drupal 10 installations.

## Next Steps

1. ✅ **Install**: Follow [INSTALLATION.md](INSTALLATION.md)
2. ✅ **Configure**: Set email at `/admin/config/event-registration/settings`
3. ✅ **Create Event**: Use `/admin/event-registration/config`
4. ✅ **Test**: Follow [TESTING.md](TESTING.md)
5. ✅ **Deploy**: Use in production with confidence!

---

## Summary

You have a **complete event management system** ready to use. Just install, configure email, create an event, and you're done!

All code is documented, tested, and committed to GitHub. See [PROJECT_SUMMARY.md](PROJECT_SUMMARY.md) for complete delivery details.

**Need help?** Start with [INSTALLATION.md](INSTALLATION.md) →
