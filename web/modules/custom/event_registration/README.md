# Event Registration Module

A comprehensive Drupal 10 custom module for managing event registrations with advanced features including event configuration, user registration forms with dynamic AJAX callbacks, email notifications, and admin dashboard.

## Features

- **Event Configuration Management**: Admin interface to create and manage events
- **Dynamic Registration Form**: Public registration form with conditional dropdowns using AJAX
- **Email Notifications**: Automated confirmation emails to users and administrators
- **Admin Dashboard**: View and manage all registrations with CSV export functionality
- **Validation**: Comprehensive validation including duplicate prevention and special character filtering
- **Configuration API**: Secure settings management without hardcoded values
- **Dependency Injection**: Clean architecture following Drupal best practices
- **PSR-4 Autoloading**: Proper namespace organization

## Installation

### Prerequisites

- Drupal 10.x
- MySQL/MariaDB database
- PHP 8.1 or higher

### Installation Steps

1. **Download and Place Module**

   ```bash
   cd web/modules/custom
   git clone <repository-url> event_registration
   cd event_registration
   composer install
   ```

2. **Install the Module**
   - Navigate to `/admin/modules` in your Drupal installation
   - Find "Event Registration" in the list
   - Check the checkbox and click "Install"
   - Or use Drush: `drush en event_registration`

3. **Configure Admin Email Settings**
   - Go to `/admin/config/event-registration/settings`
   - Enter your admin email address
   - Configure notification preferences
   - Save the configuration

4. **Set Up Initial Events**
   - Navigate to `/admin/event-registration/config`
   - Fill in event details:
     - Event Name
     - Category (Online Workshop, Hackathon, Conference, One-day Workshop)
     - Event Date
     - Registration Start Date
     - Registration End Date
   - Click "Save Event Configuration"

## Module URLs

### Public Routes

| Route             | Purpose                 | Permission     |
| ----------------- | ----------------------- | -------------- |
| `/event-register` | Event registration form | access content |

### Admin Routes

| Route                                       | Purpose                     | Permission                    |
| ------------------------------------------- | --------------------------- | ----------------------------- |
| `/admin/event-registration/config`          | Create event configurations | administer event registration |
| `/admin/config/event-registration/settings` | Module settings             | administer event registration |
| `/admin/event-registration/listings`        | View & manage registrations | access event registrations    |
| `/admin/event-registration/export-csv`      | Export registrations as CSV | access event registrations    |

## Database Schema

### Table: `event_config`

Stores event configuration details.

| Column       | Type         | Description                         |
| ------------ | ------------ | ----------------------------------- |
| `id`         | INT (serial) | Primary key                         |
| `event_name` | VARCHAR(255) | Name of the event                   |
| `category`   | VARCHAR(100) | Event category                      |
| `event_date` | INT          | Event date (Unix timestamp)         |
| `reg_start`  | INT          | Registration start (Unix timestamp) |
| `reg_end`    | INT          | Registration end (Unix timestamp)   |
| `created`    | INT          | Creation timestamp                  |

**Indexes:**

- `PRIMARY KEY (id)`
- `INDEX event_date (event_date)`
- `INDEX category (category)`
- `INDEX reg_dates (reg_start, reg_end)`

### Table: `event_registration`

Stores user registrations for events.

| Column       | Type           | Description                 |
| ------------ | -------------- | --------------------------- |
| `id`         | INT (serial)   | Primary key                 |
| `event_id`   | INT (unsigned) | Foreign key to event_config |
| `event_name` | VARCHAR(255)   | Event name                  |
| `category`   | VARCHAR(100)   | Event category              |
| `event_date` | INT            | Event date (Unix timestamp) |
| `name`       | VARCHAR(255)   | User's full name            |
| `email`      | VARCHAR(255)   | User's email address        |
| `college`    | VARCHAR(255)   | User's college name         |
| `department` | VARCHAR(255)   | User's department           |
| `created`    | INT            | Registration timestamp      |

**Indexes:**

- `PRIMARY KEY (id)`
- `FOREIGN KEY event_id -> event_config.id`
- `UNIQUE KEY email_event_date (email, event_date)`
- `INDEX event_id (event_id)`
- `INDEX email (email)`
- `INDEX event_date (event_date)`
- `INDEX created (created)`

## Form Descriptions

### Event Registration Form (`/event-register`)

The public registration form available to all users during the event registration period.

**Fields:**

- **Full Name** (Text, Required): User's full name
- **Email Address** (Email, Required): User's email for notifications
- **College Name** (Text, Required): User's college/institution name
- **Department** (Text, Required): User's academic department
- **Category** (Dropdown, Required): Select event category
- **Event Date** (Dropdown, Required): Choose event date (populated based on category via AJAX)
- **Event Name** (Dropdown, Required): Select specific event (populated based on date & category via AJAX)

**Validation Logic:**

- Name validation: Only letters and spaces allowed
- Email validation: Valid email format required
- Duplicate prevention: Prevents registering same email for same event date
- Text field validation: Only letters, numbers, and spaces allowed
- All fields required

### Event Configuration Form (`/admin/event-registration/config`)

Admin form to create and manage events.

**Fields:**

- **Event Name** (Text, Required, 255 chars): Name of the event
- **Category** (Dropdown, Required): Select from predefined categories
- **Event Date** (DateTime, Required): Date and time of the event
- **Registration Start Date** (DateTime, Required): When registration opens
- **Registration End Date** (DateTime, Required): When registration closes

**Validation:**

- Registration start < Registration end < Event date
- Special characters validation for event name

### Configuration Form (`/admin/config/event-registration/settings`)

Configure module-wide settings using Drupal's Config API.

**Settings:**

- **Admin Email**: Email address for admin notifications
- **Enable Admin Notifications**: Toggle admin email notifications
- **Email Subject**: Customizable email subject line

## Validation Rules

### Email & Duplicate Prevention

- Email must be in valid format (RFC compliant)
- Duplicate registrations prevented by Email + Event Date combination
- Unique constraint prevents the same user registering twice for the same event date

### Text Field Validation

- **Name**: Letters and spaces only
- **College**: Letters, numbers, and spaces only
- **Department**: Letters, numbers, and spaces only
- All fields have maximum length enforced at database level

### Form-Level Validation

- All fields required
- Email format validated
- Event date cannot be before registration period ends
- No closed registration periods (start must be before end)

## Email Notifications

### User Confirmation Email

Sent immediately after successful registration.

**Content:**

- Greeting with user's name
- Event name
- Event date
- Event category
- Thank you message
- Site branding

### Admin Notification Email

Sent to admin email configured in settings (if enabled).

**Content:**

- New registration notification
- User name and email
- Event details
- Registration timestamp
- Customizable subject line from settings

**Email Hook:** `hook_mail()` implementation in `event_registration.module`

## Service Architecture

### Services (Dependency Injection)

All services are defined in `event_registration.services.yml`:

1. **EventService** (`event_registration.event_service`)
   - Manages event queries and filtering
   - Provides active events, categories, dates, names
   - Methods: `getActiveEvents()`, `getActiveCategories()`, `getEventDatesByCategory()`

2. **RegistrationService** (`event_registration.registration_service`)
   - Handles registration operations
   - Manages email notifications
   - Methods: `sendConfirmationEmail()`, `getRegistrationCount()`

3. **ValidationService** (`event_registration.validation_service`)
   - Validates form inputs
   - Checks duplicates
   - Methods: `isValidName()`, `isValidText()`, `isDuplicateRegistration()`

4. **MailHandler** (`event_registration.mail_handler`)
   - Handles all email sending
   - Integrates with Drupal Mail API
   - Methods: `sendUserConfirmation()`, `sendAdminNotification()`

## File Structure

```
event_registration/
├── event_registration.info.yml          # Module metadata
├── event_registration.install           # Database schema
├── event_registration.module            # Module hooks (mail)
├── event_registration.routing.yml       # Route definitions
├── event_registration.services.yml      # Service definitions
├── event_registration.permissions.yml   # Custom permissions
├── event_registration.links.menu.yml    # Menu links
├── src/
│   ├── Form/
│   │   ├── EventRegistrationForm.php   # Public registration form
│   │   ├── EventConfigForm.php         # Event configuration form
│   │   ├── AdminConfigForm.php         # Module settings
│   │   └── AdminListingsForm.php       # Registrations listing
│   ├── Controller/
│   │   └── AdminListingsController.php # Admin operations
│   ├── Service/
│   │   ├── EventService.php            # Event operations
│   │   ├── RegistrationService.php     # Registration operations
│   │   └── ValidationService.php       # Validation logic
│   ├── Mail/
│   │   └── MailHandler.php             # Email handling
│   └── Plugin/
│       └── (Plugin implementations if needed)
└── README.md                            # This file
```

## AJAX Functionality

### Registration Form AJAX

**Category Dropdown Change:**

- Triggers AJAX callback `ajaxUpdateEventDates`
- Fetches available event dates for selected category
- Populates Event Date dropdown
- Resets Event Name dropdown

**Event Date Dropdown Change:**

- Triggers AJAX callback `ajaxUpdateEventNames`
- Fetches available events for selected date and category
- Populates Event Name dropdown

### Admin Listings Form AJAX

**Event Date Selection:**

- Triggers AJAX callback `ajaxUpdateEventNames`
- Fetches event names for selected date

**Event Name Selection:**

- Triggers AJAX callback `ajaxLoadRegistrations`
- Displays registrations table with:
  - Participant names
  - Email addresses
  - College and department
  - Submission dates
  - Total participant count

## Permissions

### Custom Permissions

1. **access event registrations**
   - Required to view admin listings
   - Required to export CSV

2. **administer event registration**
   - Required to create events
   - Required to access settings

### Default Role Assignment

- Administrator role has all permissions by default
- Assign permissions at `/admin/people/permissions`

## CSV Export

The admin panel includes a CSV export feature for all registrations.

**Exported Fields:**

- ID
- Event ID
- Name
- Email
- College
- Department
- Category
- Event Date
- Event Name
- Registration Date (YYYY-MM-DD HH:MM:SS format)

**Access:** `/admin/event-registration/export-csv`

## Development Notes

### Coding Standards

- Follows PSR-4 autoloading standards
- Implements Drupal 10 coding standards
- Uses dependency injection throughout
- No use of `\Drupal::` static calls in business logic

### Database Queries

- Uses Drupal Database API
- Prepared statements (automatic)
- No direct SQL injection risks
- Proper indexing on frequently queried columns

### Form Handling

- FormBase and ConfigFormBase implementations
- Proper form state management
- AJAX callbacks for dynamic updates
- Server-side validation

## Troubleshooting

### Module Not Appearing

1. Clear Drupal cache: `drush cr`
2. Verify file structure is correct
3. Check event_registration.info.yml syntax

### AJAX Not Working

1. Check browser console for JavaScript errors
2. Ensure jQuery is loaded
3. Verify form IDs match callback selectors

### Emails Not Sending

1. Verify admin email is configured
2. Check Drupal mail configuration
3. Review system logs at `/admin/reports/dblog`

### Registration Form Shows "Closed"

- Current time must be within registration start and end dates
- Verify event_config table has active events

## Support & Contribution

For issues or contributions:

1. Submit bug reports with detailed reproduction steps
2. Include error logs and screenshots
3. Follow Drupal contribution guidelines

## License

This module is provided as-is for Drupal 10 installations.

## Changelog

### Version 1.0.0

- Initial release
- Event configuration management
- Dynamic registration form with AJAX
- Email notifications
- Admin dashboard with CSV export
- Comprehensive validation
