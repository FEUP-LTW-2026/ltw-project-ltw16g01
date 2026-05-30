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
- [x] Membership Plans: The system supports tiered membership plans (Basic, Premium, and VIP), each with different access levels. Members can subscribe to a plan or upgrade their current membership.
- [x] Equipment Reservation: Members can reserve specific pieces of equipment for selected time slots in the main training area, depending on their membership plan.
- [x] Forgot Password: Users who forget their password can request a password reset email with instructions to create a new password.
- [x] Notification System: Admins receive notifications about relevant actions involving members, trainers, classes, equipment, and bookings. Members also receive notifications about important updates, such as class changes, cancellations, and booking-related events. Trainers also receive notifications regarding relevant actions, including enrolments in their classes, PT requests and assignments to new classes (by the admin).
- [x] Admin Analytics Dashboard: Admins can view gym-wide metrics, such as the most popular classes, equipment usage, membership statistics, and member retention.
- [x] Analytics and Reports: Admins can export analytics and reports to CSV and PDF formats.

## Running

    sqlite3 db/gym.db < db/gym.sql
    php -S localhost:9000

## Credentials

- admin/12345678
- member/vip/12345678
- member/premium/12345678
- member/basic/12345678
- member/admin/12345678
- trainer/12345678
- trainer/admin/12345678