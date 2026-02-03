# Event Registration Module - Testing Guide

## Pre-Installation Checklist

- [ ] Drupal 10 installation is active
- [ ] Database is accessible
- [ ] PHP 8.1+ is installed
- [ ] Mail system is configured (for testing emails)
- [ ] Administrator access available

## Installation & Setup

### 1. Module Installation

```bash
# Navigate to your Drupal root
cd web/modules/custom

# Clone or copy the event_registration module
git clone <repository-url> event_registration

# Clear cache (using Drupal UI or terminal)
# Via UI: /admin/config/development/performance - Clear all caches
```

### 2. Enable the Module

Navigate to `/admin/modules` and search for "Event Registration":

- [ ] Check the checkbox next to "Event Registration"
- [ ] Click "Install"
- [ ] Verify the module appears in the list

### 3. Set Permissions

Navigate to `/admin/people/permissions`:

- [ ] Assign "Administer event registration" to Administrator role
- [ ] Assign "Access event registrations" to Administrator role
- [ ] Optionally assign to other roles as needed

### 4. Configure Settings

Navigate to `/admin/config/event-registration/settings`:

- [ ] Enter administrator email address
- [ ] Enable/disable admin notifications (recommended: enabled)
- [ ] Customize notification email subject (optional)
- [ ] Save configuration

## Testing Scenarios

### Scenario 1: Create Event Configuration

**Steps:**

1. Navigate to `/admin/event-registration/config`
2. Fill in the form with test data:
   - Event Name: "Test Workshop 2024"
   - Category: "Online Workshop"
   - Event Date: Select a future date (e.g., 7 days from now)
   - Registration Start: Select today or past date
   - Registration End: Select 3 days from now
3. Click "Save Event Configuration"

**Expected Results:**

- [ ] Form submits successfully
- [ ] Confirmation message displays: "Event configuration saved successfully!"
- [ ] Event appears in database (`event_config` table)

**Validation Tests:**

- [ ] Try setting Registration End before Registration Start (should fail)
- [ ] Try setting Event Date before Registration End (should fail)
- [ ] Try entering special characters in Event Name (should fail)

### Scenario 2: View Registration Form (Public)

**Steps:**

1. Navigate to `/event-register`

**Expected Results:**

- [ ] Form displays all fields: Name, Email, College, Department
- [ ] Category dropdown populated with active event categories
- [ ] Registration form shows "Event registration is currently closed" if no active events exist

**Dynamic AJAX Tests:**

- [ ] Change Category dropdown
- [ ] Verify Event Date dropdown updates with relevant dates
- [ ] Select an Event Date
- [ ] Verify Event Name dropdown updates with relevant events

### Scenario 3: Register for Event (Valid Data)

**Steps:**

1. Navigate to `/event-register`
2. Fill in form with valid data:
   - Full Name: "John Doe"
   - Email: "john@example.com"
   - College: "State University"
   - Department: "Computer Science"
   - Category: Select available category
   - Event Date: Select available date
   - Event Name: Select available event
3. Click "Register"

**Expected Results:**

- [ ] Form submits successfully
- [ ] Success message displays: "Registration successful! A confirmation email has been sent."
- [ ] User is redirected to homepage
- [ ] Registration appears in `event_registration` table
- [ ] Confirmation email sent to user (check mail if configured)
- [ ] Admin notification email sent to configured admin email

### Scenario 4: Duplicate Registration Prevention

**Steps:**

1. Complete Scenario 3 with first email address
2. Attempt to register again with same email for same event
3. Try different event/date with same email (should succeed)

**Expected Results:**

- [ ] Same email + same event date: Error "You have already registered for an event on this date."
- [ ] Same email + different event date: Registration succeeds
- [ ] Different email + same event date: Registration succeeds

### Scenario 5: Form Validation

**Test Case 5a - Invalid Name:**

- [ ] Enter special characters like "@#$%" in Full Name
- [ ] Submit form
- [ ] Verify error: "Full name contains invalid characters..."

**Test Case 5b - Invalid Email:**

- [ ] Enter "notanemail" in Email field
- [ ] Submit form
- [ ] Verify error: "Please enter a valid email address."

**Test Case 5c - Invalid College/Department:**

- [ ] Enter special characters (excluding allowed: hyphen, parentheses, etc.)
- [ ] Submit form
- [ ] Verify error messages for invalid characters

**Test Case 5d - Required Fields:**

- [ ] Try submitting with empty fields
- [ ] Verify all required fields show error messages

### Scenario 6: Admin Listings Page

**Steps:**

1. Navigate to `/admin/event-registration/listings`
2. Select an Event Date from dropdown

**Expected Results:**

- [ ] Event Date dropdown displays all available event dates
- [ ] Event Names dropdown auto-updates via AJAX
- [ ] Table displays participant count

**With Event Selected:**

1. Select both Event Date and Event Name
2. View registrations table

**Expected Results:**

- [ ] Table displays all registrations for selected event
- [ ] Columns shown: Name, Email, Event Date, College, Department, Submission Date
- [ ] Total Participants count displays
- [ ] Data is accurate and complete

### Scenario 7: CSV Export

**Steps:**

1. Navigate to `/admin/event-registration/listings`
2. Click "Export as CSV" link

**Expected Results:**

- [ ] CSV file downloads to your computer
- [ ] File name format: "registrations\_[timestamp].csv"
- [ ] CSV contains headers: ID, Event ID, Name, Email, College, Department, Category, Event Date, Event Name, Registration Date
- [ ] All registration data is included and properly formatted
- [ ] Dates are formatted as YYYY-MM-DD HH:MM:SS

**CSV Validation:**

- [ ] Open CSV in Excel/LibreOffice
- [ ] Verify all columns are present
- [ ] Verify data is properly escaped (commas in names handled correctly)
- [ ] Compare row count with admin panel

### Scenario 8: Email Notifications

**Setup:**
Configure a test mail service or use mail log:

```bash
# In Drupal settings.php, add:
$config['system.mail']['interface']['default'] = 'dblog_mail';
```

**Steps:**

1. Complete a registration form submission
2. Check email delivery

**Expected User Email Should Contain:**

- [ ] Greeting with user's name
- [ ] Event name
- [ ] Event date
- [ ] Event category
- [ ] Thank you message
- [ ] Site branding

**Expected Admin Email Should Contain:**

- [ ] Notification of new registration
- [ ] User's name and email
- [ ] Event details
- [ ] Registration timestamp
- [ ] Custom subject line from settings

### Scenario 9: Time-Based Access Control

**Setup:**

1. Create event with registration period ending today
2. Create event with registration period starting tomorrow

**Expected Results:**

- [ ] Form shows "Event registration is currently closed" for past events
- [ ] Form doesn't display future events
- [ ] Only current registration period events appear in dropdowns

### Scenario 10: Special Characters in Data

**Test Various Inputs:**

- Full Name: "Mary-Anne O'Brien" (should pass)
- College: "St. Joseph's University" (should pass)
- Department: "Computer Science & Engineering" (should pass)

**Expected Results:**

- [ ] Names with hyphens and apostrophes accepted
- [ ] College/Department with periods, ampersands, parentheses accepted
- [ ] Data stored correctly in database
- [ ] Data displays correctly in admin panel

## Database Verification

### Check Event Config Table

```sql
SELECT * FROM event_config;
```

Verify:

- [ ] Records have all required fields
- [ ] Timestamps are valid Unix timestamps
- [ ] Categories match options: online_workshop, hackathon, conference, one_day_workshop

### Check Registration Table

```sql
SELECT er.*, ec.event_name FROM event_registration er
JOIN event_config ec ON er.event_id = ec.id;
```

Verify:

- [ ] All registrations have valid event_id
- [ ] Email addresses are valid format
- [ ] Timestamps are reasonable
- [ ] No duplicate entries for same email+event_date

## Performance Testing

### Test with Multiple Events

1. Create 5-10 events with various dates
2. Register multiple users (10+)
3. Navigate admin listings

**Verify:**

- [ ] Dropdowns load quickly
- [ ] AJAX callbacks return promptly
- [ ] Tables render without lag
- [ ] CSV export completes successfully

### Test with Large Dataset

1. Populate database with 100+ registrations (manual SQL insert if needed)
2. Test admin listings filtering
3. Test CSV export

**Verify:**

- [ ] No timeout errors
- [ ] Admin panel responsive
- [ ] CSV export memory efficient

## Accessibility Testing

- [ ] Form labels properly associated with fields
- [ ] Error messages clearly marked
- [ ] Dropdown navigation works with keyboard
- [ ] Required field indicators visible
- [ ] Email field accepts tab navigation

## Browser Compatibility

Test in:

- [ ] Chrome/Chromium (latest)
- [ ] Firefox (latest)
- [ ] Safari (if applicable)
- [ ] Edge (latest)

Verify:

- [ ] Forms render correctly
- [ ] AJAX callbacks work
- [ ] CSS styling applies properly
- [ ] Dropdown interactions work

## Troubleshooting Tests

### If Module Won't Install

1. [ ] Clear cache in admin panel
2. [ ] Check PHP error logs
3. [ ] Verify module file structure
4. [ ] Check database permissions

### If AJAX Not Working

1. [ ] Check browser console for JavaScript errors
2. [ ] Verify jQuery is loaded
3. [ ] Check form element IDs match callback selectors
4. [ ] Review server logs

### If Emails Not Sending

1. [ ] Check Drupal mail system configuration
2. [ ] Verify admin email is set in settings
3. [ ] Check system logs at `/admin/reports/dblog`
4. [ ] Test with dblog mail interface if available

### If Registration Form Shows Closed

1. [ ] Verify event_config table has records
2. [ ] Check registration start/end dates are current
3. [ ] Verify event_date is in future

## Security Testing

- [ ] SQL injection attempts (should fail)
- [ ] XSS attacks in form fields (should be sanitized)
- [ ] CSRF protection (should be enforced by Drupal)
- [ ] Unauthorized access to admin pages (should require permission)
- [ ] Try accessing `/admin/event-registration/*` without proper permissions

## Rollback Procedure

If issues occur during testing:

```bash
# Uninstall module
# Navigate to /admin/modules and uninstall Event Registration

# OR via SQL (nuclear option):
# DROP TABLE IF EXISTS event_config;
# DROP TABLE IF EXISTS event_registration;
# DELETE FROM config WHERE name = 'event_registration.settings';
```

## Post-Testing Checklist

After all tests pass:

- [ ] All code is committed to repository
- [ ] README.md is up-to-date
- [ ] SQL schema file is current
- [ ] No unresolved errors in logs
- [ ] Module is ready for production use

## Test Report Template

```
Test Date: [DATE]
Tester: [NAME]
Environment: Drupal [VERSION], PHP [VERSION]

Passed Tests: [NUMBER]
Failed Tests: [NUMBER]
Skipped Tests: [NUMBER]

Issues Found:
1. [Description]
   - Steps to reproduce
   - Expected behavior
   - Actual behavior
   - Severity (Critical/High/Medium/Low)

Recommendations:
- [Any recommendations for improvements]

Sign-off: ___________________
```

## Continuous Testing

For ongoing maintenance:

- Test after any Drupal core updates
- Test after adding new events
- Monitor error logs regularly
- Verify emails sending properly
- Check database performance with large datasets
