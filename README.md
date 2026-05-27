# ltw16g01

# First Delivery

For the first delivery, we developed three HTML pages:

- `index.html`
- `classes.html`
- `admin-dashboard.html`

These files are located in the root folder of the project.

### How to run the project

To open and view the pages, we use the **Live Server** extension in Visual Studio Code.

From the homepage (`index.html`), it is possible to navigate to the Classes page through the navigation menu.

To open the Admin Dashboard page, manually change the browser URL to:

```text
/admin-dashboard.html
```

# Final Delivery

## Features

**All users:**
- [x] Register a new account.
- [x] Log in and out.
- [x] Edit their profile, including name, username, password, and profile photo.

**Members:**
- [x] Browse the schedule of available fitness classes, filtering by type, trainer, day, or time.
- [x] Enroll in and cancel enrollment from upcoming classes, subject to capacity limits.
- [x] View trainer profiles, including their specializations and the classes they teach.
- [x] Check the current availability of equipment in the main training area.
- [x] Leave ratings and reviews for classes they have attended.

**Trainers:**
- [x] Manage their public profile, including bio, specializations, and certifications.
- [x] View the roster of members enrolled in their classes.
- [x] Track and manage their assigned class schedule.

**Admins:**
- [x] Manage members and trainers (create, update, and deactivate accounts).
- [x] Manage the class catalog (create, edit, and remove classes) and assign trainers to them.
- [x] Manage equipment in the main training area (add, update availability status, and remove items).
- [x] Elevate a user to admin status.
- [x] Oversee and ensure the smooth operation of the entire system.

**Extra:**
- [x] Personal Training Bookings: Members can browse trainer availability and book one-on-one personal training sessions.
- [x] Membership Plans: Define tiered membership plans (e.g., basic, premium) with different access levels, and allow members to subscribe to or upgrade their plan.
- [x] Equipment Reservation: Members can reserve a specific piece of equipment for a time slot in the main training area.
- [x] Forgot Password: If a user forgets their password, they can request a password reset email containing instructions to create a new password.
- [x] Notification System: Send in-app or email notifications for class reminders, booking confirmations, waitlist updates, or membership renewals.
- [x] Trainer Analytics Dashboard: Trainers can view attendance statistics, ratings summaries, and engagement data for their classes.

## Running

    sqlite3 database/database.db < database/database.sql
    php -S localhost:9000

## Credentials

- admin/12345678
- member/12345678
- trainer/12345678