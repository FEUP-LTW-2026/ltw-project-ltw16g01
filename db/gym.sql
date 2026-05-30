--
-- File generated with SQLiteStudio v3.4.17 on Sat May 30 22:26:38 2026
--
-- Text encoding used: UTF-8
--
PRAGMA foreign_keys = off;
BEGIN TRANSACTION;

-- Table: class_schedules
DROP TABLE IF EXISTS class_schedules;

CREATE TABLE IF NOT EXISTS class_schedules (
    id           INTEGER PRIMARY KEY AUTOINCREMENT,
    class_id     INTEGER NOT NULL,
    trainer_id   INTEGER,
    day_of_week  TEXT    NOT NULL,-- Monday, Tuesday, etc.
    start_time   TEXT    NOT NULL,
    location     TEXT    NOT NULL
                         DEFAULT 'Studio A',
    capacity     INTEGER NOT NULL
                         DEFAULT 20,
    duration_min INTEGER NOT NULL
                         DEFAULT 60,
    FOREIGN KEY (
        class_id
    )
    REFERENCES classes (id) ON DELETE CASCADE,
    FOREIGN KEY (
        trainer_id
    )
    REFERENCES users (id) ON DELETE SET NULL
);

INSERT INTO class_schedules (id, class_id, trainer_id, day_of_week, start_time, location, capacity, duration_min) VALUES (1, 1, 1, 'Monday', '08:00', 'Studio A', 15, 60);
INSERT INTO class_schedules (id, class_id, trainer_id, day_of_week, start_time, location, capacity, duration_min) VALUES (2, 2, 2, 'Tuesday', '18:00', 'Main Floor', 20, 45);
INSERT INTO class_schedules (id, class_id, trainer_id, day_of_week, start_time, location, capacity, duration_min) VALUES (3, 3, 3, 'Wednesday', '19:00', 'Cycling Studio', 18, 50);
INSERT INTO class_schedules (id, class_id, trainer_id, day_of_week, start_time, location, capacity, duration_min) VALUES (4, 4, 1, 'Thursday', '10:00', 'Studio B', 12, 55);
INSERT INTO class_schedules (id, class_id, trainer_id, day_of_week, start_time, location, capacity, duration_min) VALUES (5, 5, 2, 'Friday', '17:30', 'Main Floor', 20, 60);
INSERT INTO class_schedules (id, class_id, trainer_id, day_of_week, start_time, location, capacity, duration_min) VALUES (9, 9, 2, 'Wednesday', '17:30', 'Boxing Area', 14, 50);
INSERT INTO class_schedules (id, class_id, trainer_id, day_of_week, start_time, location, capacity, duration_min) VALUES (10, 10, 3, 'Tuesday', '10:00', 'Studio B', 12, 45);
INSERT INTO class_schedules (id, class_id, trainer_id, day_of_week, start_time, location, capacity, duration_min) VALUES (15, 14, 1, 'Sunday', '12:32', 'Studio A', 20, 60);

-- Table: class_sessions
DROP TABLE IF EXISTS class_sessions;

CREATE TABLE IF NOT EXISTS class_sessions (
    id           INTEGER PRIMARY KEY AUTOINCREMENT,
    schedule_id  INTEGER NOT NULL,
    session_date DATE    NOT NULL,
    status       TEXT    DEFAULT 'scheduled'
                         CHECK (status IN ('scheduled', 'cancelled', 'completed') ),
    FOREIGN KEY (
        schedule_id
    )
    REFERENCES class_schedules (id) ON DELETE CASCADE
);

INSERT INTO class_sessions (id, schedule_id, session_date, status) VALUES (1, 1, '2026-06-01', 'cancelled');
INSERT INTO class_sessions (id, schedule_id, session_date, status) VALUES (3, 2, '2026-06-02', 'cancelled');
INSERT INTO class_sessions (id, schedule_id, session_date, status) VALUES (4, 10, '2026-06-02', 'cancelled');
INSERT INTO class_sessions (id, schedule_id, session_date, status) VALUES (5, 3, '2026-05-27', 'scheduled');
INSERT INTO class_sessions (id, schedule_id, session_date, status) VALUES (6, 9, '2026-05-27', 'scheduled');
INSERT INTO class_sessions (id, schedule_id, session_date, status) VALUES (8, 4, '2026-05-28', 'scheduled');
INSERT INTO class_sessions (id, schedule_id, session_date, status) VALUES (9, 5, '2026-05-29', 'scheduled');
INSERT INTO class_sessions (id, schedule_id, session_date, status) VALUES (13, 9, '2026-06-03', 'cancelled');
INSERT INTO class_sessions (id, schedule_id, session_date, status) VALUES (14, 5, '2026-06-05', 'cancelled');
INSERT INTO class_sessions (id, schedule_id, session_date, status) VALUES (15, 4, '2026-06-04', 'cancelled');
INSERT INTO class_sessions (id, schedule_id, session_date, status) VALUES (16, 3, '2026-06-03', 'cancelled');
INSERT INTO class_sessions (id, schedule_id, session_date, status) VALUES (18, 15, '2026-05-31', 'cancelled');
INSERT INTO class_sessions (id, schedule_id, session_date, status) VALUES (20, 15, '2026-06-07', 'cancelled');
INSERT INTO class_sessions (id, schedule_id, session_date, status) VALUES (21, 15, '2026-06-07', 'cancelled');
INSERT INTO class_sessions (id, schedule_id, session_date, status) VALUES (22, 15, '2026-06-07', 'cancelled');
INSERT INTO class_sessions (id, schedule_id, session_date, status) VALUES (23, 15, '2026-06-07', 'cancelled');
INSERT INTO class_sessions (id, schedule_id, session_date, status) VALUES (24, 15, '2026-06-07', 'cancelled');
INSERT INTO class_sessions (id, schedule_id, session_date, status) VALUES (25, 15, '2026-06-14', 'cancelled');
INSERT INTO class_sessions (id, schedule_id, session_date, status) VALUES (26, 15, '2026-06-21', 'cancelled');
INSERT INTO class_sessions (id, schedule_id, session_date, status) VALUES (27, 1, '2026-06-08', 'cancelled');
INSERT INTO class_sessions (id, schedule_id, session_date, status) VALUES (28, 15, '2026-06-28', 'cancelled');
INSERT INTO class_sessions (id, schedule_id, session_date, status) VALUES (29, 1, '2026-06-15', 'cancelled');
INSERT INTO class_sessions (id, schedule_id, session_date, status) VALUES (30, 4, '2026-06-11', 'cancelled');
INSERT INTO class_sessions (id, schedule_id, session_date, status) VALUES (31, 15, '2026-07-05', 'scheduled');
INSERT INTO class_sessions (id, schedule_id, session_date, status) VALUES (32, 4, '2026-06-18', 'cancelled');
INSERT INTO class_sessions (id, schedule_id, session_date, status) VALUES (33, 1, '2026-06-22', 'cancelled');
INSERT INTO class_sessions (id, schedule_id, session_date, status) VALUES (34, 1, '2026-06-29', 'cancelled');

-- Table: class_types
DROP TABLE IF EXISTS class_types;

CREATE TABLE IF NOT EXISTS class_types (
    id          INTEGER PRIMARY KEY AUTOINCREMENT,
    name        TEXT    UNIQUE
                        NOT NULL,
    label       TEXT    NOT NULL,
    description TEXT,
    icon        TEXT,
    active      INTEGER NOT NULL
                        DEFAULT 1
);

INSERT INTO class_types (id, name, label, description, icon, active) VALUES (1, 'yoga', 'Yoga', 'Mind-body practice focusing on flexibility and relaxation', '🧘', 1);
INSERT INTO class_types (id, name, label, description, icon, active) VALUES (2, 'pilates', 'Pilates', 'Core strength and controlled movements', '🏋️', 1);
INSERT INTO class_types (id, name, label, description, icon, active) VALUES (3, 'hiit', 'HIIT', 'High-intensity interval training for cardio and strength', '🔥', 1);
INSERT INTO class_types (id, name, label, description, icon, active) VALUES (4, 'cycling', 'Cycling', 'Indoor cycling / spinning classes', '🚴', 1);
INSERT INTO class_types (id, name, label, description, icon, active) VALUES (5, 'strength', 'Strength', 'Weight training and muscle building', '💪', 1);
INSERT INTO class_types (id, name, label, description, icon, active) VALUES (6, 'crossfit', 'CrossFit', 'Functional fitness and high-intensity workouts', '⚡', 1);
INSERT INTO class_types (id, name, label, description, icon, active) VALUES (7, 'boxing', 'Boxing', 'Combat sports conditioning and agility', '🥊', 1);
INSERT INTO class_types (id, name, label, description, icon, active) VALUES (8, 'senior-fitness', 'Senior Fitness', 'Low-impact training for older adults', '👴', 1);

-- Table: classes
DROP TABLE IF EXISTS classes;

CREATE TABLE IF NOT EXISTS classes (
    id            INTEGER PRIMARY KEY AUTOINCREMENT,
    name          TEXT    NOT NULL,
    description   TEXT,
    class_type_id INTEGER NOT NULL,
    active        INTEGER NOT NULL
                          DEFAULT 1,
    FOREIGN KEY (
        class_type_id
    )
    REFERENCES class_types (id) 
);

INSERT INTO classes (id, name, description, class_type_id, active) VALUES (1, 'Morning Yoga', 'Start your day with calm and flexibility', 1, 1);
INSERT INTO classes (id, name, description, class_type_id, active) VALUES (2, 'Power HIIT', 'Burn calories and build strength', 3, 1);
INSERT INTO classes (id, name, description, class_type_id, active) VALUES (3, 'Spinning Class', 'High-energy cycling for all levels', 4, 1);
INSERT INTO classes (id, name, description, class_type_id, active) VALUES (4, 'Pilates Core', 'Strengthen your core with controlled movements', 2, 1);
INSERT INTO classes (id, name, description, class_type_id, active) VALUES (5, 'CrossFit WOD', 'Workout of the day — push your limits', 6, 1);
INSERT INTO classes (id, name, description, class_type_id, active) VALUES (6, 'Evening Yoga', 'Wind down and restore with evening yoga', 1, 1);
INSERT INTO classes (id, name, description, class_type_id, active) VALUES (7, 'Saturday Spin', 'Weekend ride to start your Saturday right', 4, 1);
INSERT INTO classes (id, name, description, class_type_id, active) VALUES (8, 'Strength Basics', 'Build strength with proper technique and progressive overload', 5, 1);
INSERT INTO classes (id, name, description, class_type_id, active) VALUES (9, 'Boxing Conditioning', 'Improve endurance, speed and coordination with boxing drills', 7, 1);
INSERT INTO classes (id, name, description, class_type_id, active) VALUES (10, 'Senior Fitness', 'Low-impact training focused on mobility, balance and strength', 8, 1);

-- Table: enrollments
DROP TABLE IF EXISTS enrollments;

CREATE TABLE IF NOT EXISTS enrollments (
    id          INTEGER  PRIMARY KEY AUTOINCREMENT,
    member_id   INTEGER  NOT NULL,
    session_id  INTEGER  NOT NULL,
    enrolled_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    status      TEXT     DEFAULT 'enrolled'
                         CHECK (status IN ('enrolled', 'waitlist', 'cancelled') ),
    UNIQUE (
        member_id,
        session_id
    ),
    FOREIGN KEY (
        member_id
    )
    REFERENCES users (id) ON DELETE CASCADE,
    FOREIGN KEY (
        session_id
    )
    REFERENCES class_sessions (id) ON DELETE CASCADE
);

INSERT INTO enrollments (id, member_id, session_id, enrolled_at, status) VALUES (1, 14, 1, '2026-05-27 14:49:20', 'cancelled');
INSERT INTO enrollments (id, member_id, session_id, enrolled_at, status) VALUES (2, 15, 3, '2026-05-27 14:49:20', 'enrolled');
INSERT INTO enrollments (id, member_id, session_id, enrolled_at, status) VALUES (3, 16, 5, '2026-05-27 14:49:20', 'enrolled');
INSERT INTO enrollments (id, member_id, session_id, enrolled_at, status) VALUES (4, 20, 1, '2026-05-29 23:45:19', 'cancelled');
INSERT INTO enrollments (id, member_id, session_id, enrolled_at, status) VALUES (7, 20, 3, '2026-05-29 23:23:59', 'enrolled');
INSERT INTO enrollments (id, member_id, session_id, enrolled_at, status) VALUES (8, 20, 4, '2026-05-29 23:24:06', 'enrolled');
INSERT INTO enrollments (id, member_id, session_id, enrolled_at, status) VALUES (10, 20, 13, '2026-05-29 23:29:28', 'enrolled');
INSERT INTO enrollments (id, member_id, session_id, enrolled_at, status) VALUES (11, 20, 14, '2026-05-29 23:29:07', 'enrolled');
INSERT INTO enrollments (id, member_id, session_id, enrolled_at, status) VALUES (12, 20, 15, '2026-05-29 23:29:08', 'cancelled');
INSERT INTO enrollments (id, member_id, session_id, enrolled_at, status) VALUES (13, 20, 16, '2026-05-29 23:29:09', 'enrolled');
INSERT INTO enrollments (id, member_id, session_id, enrolled_at, status) VALUES (15, 20, 18, '2026-05-29 23:44:11', 'cancelled');
INSERT INTO enrollments (id, member_id, session_id, enrolled_at, status) VALUES (16, 14, 4, '2026-05-29 23:46:04', 'enrolled');
INSERT INTO enrollments (id, member_id, session_id, enrolled_at, status) VALUES (18, 20, 22, '2026-05-30 12:44:32', 'cancelled');
INSERT INTO enrollments (id, member_id, session_id, enrolled_at, status) VALUES (19, 20, 23, '2026-05-30 12:45:20', 'cancelled');
INSERT INTO enrollments (id, member_id, session_id, enrolled_at, status) VALUES (20, 20, 24, '2026-05-30 12:45:52', 'cancelled');
INSERT INTO enrollments (id, member_id, session_id, enrolled_at, status) VALUES (21, 20, 34, '2026-05-30 13:01:46', 'enrolled');

-- Table: equipment
DROP TABLE IF EXISTS equipment;

CREATE TABLE IF NOT EXISTS equipment (
    id          INTEGER  PRIMARY KEY AUTOINCREMENT,
    name        TEXT     NOT NULL,
    category_id INTEGER  NOT NULL,
    quantity    INTEGER  NOT NULL
                         DEFAULT 1,
    available   INTEGER  NOT NULL
                         DEFAULT 1,
    status      TEXT     NOT NULL
                         DEFAULT 'available'
                         CHECK (status IN ('available', 'maintenance', 'unavailable') ),
    notes       TEXT,
    updated_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (
        category_id
    )
    REFERENCES equipment_categories (id) 
);

INSERT INTO equipment (id, name, category_id, quantity, available, status, notes, updated_at) VALUES (1, 'Treadmill', 1, 8, 6, 'available', '', '2026-05-27 14:49:20');
INSERT INTO equipment (id, name, category_id, quantity, available, status, notes, updated_at) VALUES (2, 'Stationary Bike', 1, 6, 5, 'available', '', '2026-05-27 14:49:20');
INSERT INTO equipment (id, name, category_id, quantity, available, status, notes, updated_at) VALUES (3, 'Rowing Machine', 1, 4, 0, 'unavailable', 'Motor reparação', '2026-05-27 14:49:20');
INSERT INTO equipment (id, name, category_id, quantity, available, status, notes, updated_at) VALUES (4, 'Elliptical', 1, 4, 4, 'available', '', '2026-05-27 14:49:20');
INSERT INTO equipment (id, name, category_id, quantity, available, status, notes, updated_at) VALUES (5, 'Weight Bench', 2, 10, 8, 'available', '', '2026-05-27 14:49:20');
INSERT INTO equipment (id, name, category_id, quantity, available, status, notes, updated_at) VALUES (6, 'Squat Rack', 2, 4, 3, 'available', '', '2026-05-27 14:49:20');
INSERT INTO equipment (id, name, category_id, quantity, available, status, notes, updated_at) VALUES (7, 'Pull-up Bar', 2, 6, 6, 'available', '', '2026-05-27 14:49:20');
INSERT INTO equipment (id, name, category_id, quantity, available, status, notes, updated_at) VALUES (8, 'Dumbbell Set', 3, 20, 18, 'available', '', '2026-05-27 14:49:20');
INSERT INTO equipment (id, name, category_id, quantity, available, status, notes, updated_at) VALUES (9, 'Barbell Set', 3, 8, 7, 'available', '', '2026-05-27 14:49:20');
INSERT INTO equipment (id, name, category_id, quantity, available, status, notes, updated_at) VALUES (10, 'Kettlebell Set', 3, 12, 10, 'available', '', '2026-05-27 14:49:20');
INSERT INTO equipment (id, name, category_id, quantity, available, status, notes, updated_at) VALUES (11, 'Yoga Mat', 4, 30, 25, 'maintenance', '', '2026-05-29 22:20:44');
INSERT INTO equipment (id, name, category_id, quantity, available, status, notes, updated_at) VALUES (12, 'Foam Roller', 4, 15, 12, 'available', '', '2026-05-27 14:49:20');
INSERT INTO equipment (id, name, category_id, quantity, available, status, notes, updated_at) VALUES (13, 'Battle Rope', 5, 4, 3, 'available', '', '2026-05-27 14:49:20');
INSERT INTO equipment (id, name, category_id, quantity, available, status, notes, updated_at) VALUES (14, 'TRX Suspension', 5, 6, 5, 'available', '', '2026-05-27 14:49:20');
INSERT INTO equipment (id, name, category_id, quantity, available, status, notes, updated_at) VALUES (15, 'Punching Bag', 1, 3, 2, 'available', '', '2026-05-27 14:49:20');

-- Table: equipment_categories
DROP TABLE IF EXISTS equipment_categories;

CREATE TABLE IF NOT EXISTS equipment_categories (
    id          INTEGER PRIMARY KEY AUTOINCREMENT,
    name        TEXT    UNIQUE
                        NOT NULL,
    label       TEXT    NOT NULL,
    description TEXT,
    active      INTEGER NOT NULL
                        DEFAULT 1
);

INSERT INTO equipment_categories (id, name, label, description, active) VALUES (1, 'Cardio', 'Cardio Machines', 'Treadmills, bikes, rowers, ellipticals', 1);
INSERT INTO equipment_categories (id, name, label, description, active) VALUES (2, 'Strength', 'Strength Equipment', 'Benches, racks, bars, machines', 1);
INSERT INTO equipment_categories (id, name, label, description, active) VALUES (3, 'Free Weights', 'Free Weights', 'Dumbbells, kettlebells, barbells', 1);
INSERT INTO equipment_categories (id, name, label, description, active) VALUES (4, 'Accessories', 'Accessories', 'Mats, rollers, balls, straps', 1);
INSERT INTO equipment_categories (id, name, label, description, active) VALUES (5, 'Functional', 'Functional Training', 'TRX, battle ropes, agility equipment', 1);

-- Table: equipment_reservations
DROP TABLE IF EXISTS equipment_reservations;

CREATE TABLE IF NOT EXISTS equipment_reservations (
    id               INTEGER  PRIMARY KEY AUTOINCREMENT,
    member_id        INTEGER  NOT NULL,
    equipment_id     INTEGER  NOT NULL,
    reservation_date DATE     NOT NULL,
    reservation_time TEXT     NOT NULL,
    quantity         INTEGER  NOT NULL
                              DEFAULT 1,
    status           TEXT     NOT NULL
                              DEFAULT 'active'
                              CHECK (status IN ('active', 'cancelled', 'completed') ),
    created_at       DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (
        member_id
    )
    REFERENCES users (id) ON DELETE CASCADE,
    FOREIGN KEY (
        equipment_id
    )
    REFERENCES equipment (id) ON DELETE CASCADE
);


-- Table: member_profiles
DROP TABLE IF EXISTS member_profiles;

CREATE TABLE IF NOT EXISTS member_profiles (
    id              INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id         INTEGER NOT NULL
                            UNIQUE,
    membership_plan TEXT    DEFAULT 'basic'
                            CHECK (membership_plan IN ('basic', 'premium', 'vip') ),
    plan_start      DATE,
    plan_end        DATE,
    FOREIGN KEY (
        user_id
    )
    REFERENCES users (id) ON DELETE CASCADE
);

INSERT INTO member_profiles (id, user_id, membership_plan, plan_start, plan_end) VALUES (1, 14, 'vip', '2026-05-27', '2027-05-27');
INSERT INTO member_profiles (id, user_id, membership_plan, plan_start, plan_end) VALUES (2, 15, 'premium', '2026-05-27', '2027-05-27');
INSERT INTO member_profiles (id, user_id, membership_plan, plan_start, plan_end) VALUES (3, 16, 'basic', '2026-05-27', '2027-05-27');
INSERT INTO member_profiles (id, user_id, membership_plan, plan_start, plan_end) VALUES (4, 17, 'vip', '2026-05-27', '2027-05-27');
INSERT INTO member_profiles (id, user_id, membership_plan, plan_start, plan_end) VALUES (5, 18, 'premium', '2026-05-27', '2027-05-27');
INSERT INTO member_profiles (id, user_id, membership_plan, plan_start, plan_end) VALUES (6, 19, 'basic', '2026-05-27', '2026-06-27');
INSERT INTO member_profiles (id, user_id, membership_plan, plan_start, plan_end) VALUES (7, 20, 'premium', '2026-05-29', '');
INSERT INTO member_profiles (id, user_id, membership_plan, plan_start, plan_end) VALUES (8, 22, 'vip', '2026-05-29', '');
INSERT INTO member_profiles (id, user_id, membership_plan, plan_start, plan_end) VALUES (9, 23, 'basic', '2026-05-29', '2027-05-29');
INSERT INTO member_profiles (id, user_id, membership_plan, plan_start, plan_end) VALUES (10, 21, 'vip', '2026-05-30', '2027-05-30');
INSERT INTO member_profiles (id, user_id, membership_plan, plan_start, plan_end) VALUES (11, 25, 'vip', '2026-05-30', '');

-- Table: notification_reads
DROP TABLE IF EXISTS notification_reads;

CREATE TABLE IF NOT EXISTS notification_reads (
    id              INTEGER  PRIMARY KEY AUTOINCREMENT,
    notification_id INTEGER  NOT NULL,
    user_id         INTEGER  NOT NULL,
    read_at         DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE (
        notification_id,
        user_id
    ),
    FOREIGN KEY (
        notification_id
    )
    REFERENCES notifications (id) ON DELETE CASCADE,
    FOREIGN KEY (
        user_id
    )
    REFERENCES users (id) ON DELETE CASCADE
);

INSERT INTO notification_reads (id, notification_id, user_id, read_at) VALUES (1, 1, 13, '2026-05-29 19:28:36');
INSERT INTO notification_reads (id, notification_id, user_id, read_at) VALUES (2, 6, 20, '2026-05-29 19:47:17');
INSERT INTO notification_reads (id, notification_id, user_id, read_at) VALUES (3, 2, 13, '2026-05-29 20:52:56');
INSERT INTO notification_reads (id, notification_id, user_id, read_at) VALUES (4, 3, 13, '2026-05-29 20:59:44');
INSERT INTO notification_reads (id, notification_id, user_id, read_at) VALUES (5, 9, 12, '2026-05-29 21:46:02');
INSERT INTO notification_reads (id, notification_id, user_id, read_at) VALUES (6, 17, 1, '2026-05-29 22:04:32');

-- Table: notification_types
DROP TABLE IF EXISTS notification_types;

CREATE TABLE IF NOT EXISTS notification_types (
    id        INTEGER PRIMARY KEY AUTOINCREMENT,
    name      TEXT    UNIQUE
                      NOT NULL,
    label     TEXT    NOT NULL,
    css_class TEXT    DEFAULT 'info',
    icon      TEXT    DEFAULT 'bell',
    active    INTEGER NOT NULL
                      DEFAULT 1
);

INSERT INTO notification_types (id, name, label, css_class, icon, active) VALUES (1, 'info', 'Info', 'info', 'bell', 1);
INSERT INTO notification_types (id, name, label, css_class, icon, active) VALUES (2, 'success', 'Success', 'success', 'bell', 1);
INSERT INTO notification_types (id, name, label, css_class, icon, active) VALUES (3, 'warning', 'Warning', 'warning', 'bell', 1);
INSERT INTO notification_types (id, name, label, css_class, icon, active) VALUES (4, 'error', 'Error', 'error', 'bell', 1);

-- Table: notifications
DROP TABLE IF EXISTS notifications;

CREATE TABLE IF NOT EXISTS notifications (
    id                   INTEGER  PRIMARY KEY AUTOINCREMENT,
    user_id              INTEGER  DEFAULT NULL,
    target_role          TEXT     DEFAULT NULL,
    scope                TEXT     NOT NULL
                                  DEFAULT 'personal',
    title                TEXT,
    message              TEXT     NOT NULL,
    notification_type_id INTEGER  NOT NULL,
    created_at           DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (
        user_id
    )
    REFERENCES users (id) ON DELETE CASCADE,
    FOREIGN KEY (
        notification_type_id
    )
    REFERENCES notification_types (id) 
);

INSERT INTO notifications (id, user_id, target_role, scope, title, message, notification_type_id, created_at) VALUES (4, 'success', NULL, 'personal', NULL, '20', 1, '2026-05-29 19:30:42');
INSERT INTO notifications (id, user_id, target_role, scope, title, message, notification_type_id, created_at) VALUES (6, 20, NULL, 'personal', NULL, 'Your personal training request has been confirmed by trainer André Ferreira', 2, '2026-05-29 19:47:05');
INSERT INTO notifications (id, user_id, target_role, scope, title, message, notification_type_id, created_at) VALUES (8, 20, NULL, 'personal', NULL, 'Your personal training request has been confirmed by trainer André Ferreira', 2, '2026-05-29 19:54:30');
INSERT INTO notifications (id, user_id, target_role, scope, title, message, notification_type_id, created_at) VALUES (9, 12, NULL, 'personal', 'Class Enrollment', 'João Costa enrolled in your class ''Saturday Spin''.', 1, '2026-05-29 21:45:44');
INSERT INTO notifications (id, user_id, target_role, scope, title, message, notification_type_id, created_at) VALUES (10, 12, NULL, 'personal', 'Class Enrollment', 'João Costa cancelled enrollment in your class ''Saturday Spin''.', 1, '2026-05-29 21:46:13');
INSERT INTO notifications (id, user_id, target_role, scope, title, message, notification_type_id, created_at) VALUES (11, 12, NULL, 'personal', 'New class assigned', 'You have been assigned to the class ''teste no'' on Sunday at 12:00.', 1, '2026-05-29 21:51:54');
INSERT INTO notifications (id, user_id, target_role, scope, title, message, notification_type_id, created_at) VALUES (12, 12, NULL, 'personal', 'Class removed', 'The class ''teste no'' on Sunday at 12:00 has been removed (schedule ID: 14).', 3, '2026-05-29 21:55:06');
INSERT INTO notifications (id, user_id, target_role, scope, title, message, notification_type_id, created_at) VALUES (13, 12, NULL, 'personal', 'Class removed', 'The class ''teste notificações'' on Saturday at 15:00 has been removed.', 3, '2026-05-29 21:57:51');
INSERT INTO notifications (id, user_id, target_role, scope, title, message, notification_type_id, created_at) VALUES (14, 12, NULL, 'personal', NULL, 'The class ''Strength Basics'' on Monday at 18:30 has been updated.', 3, '2026-05-29 21:59:57');
INSERT INTO notifications (id, user_id, target_role, scope, title, message, notification_type_id, created_at) VALUES (15, 12, NULL, 'personal', NULL, 'The class ''Strength Basics'' on Monday at 18:30 has been updated.', 3, '2026-05-29 22:00:00');
INSERT INTO notifications (id, user_id, target_role, scope, title, message, notification_type_id, created_at) VALUES (16, 12, NULL, 'personal', NULL, 'The class ''Strength Basics'' on Monday at 18:30 has been updated.', 3, '2026-05-29 22:03:26');
INSERT INTO notifications (id, user_id, target_role, scope, title, message, notification_type_id, created_at) VALUES (17, 1, NULL, 'personal', NULL, 'You have been assigned to the class ''Strength Basics'' on Monday at 18:30 (schedule ID: 8).', 2, '2026-05-29 22:03:29');
INSERT INTO notifications (id, user_id, target_role, scope, title, message, notification_type_id, created_at) VALUES (18, 12, NULL, 'personal', NULL, 'You are no longer assigned to the class ''Strength Basics'' on Monday at 18:30 (schedule ID: 8).', 3, '2026-05-29 22:03:29');
INSERT INTO notifications (id, user_id, target_role, scope, title, message, notification_type_id, created_at) VALUES (19, 1, NULL, 'personal', 'New class assigned', 'You have been assigned to the class ''teste noite'' on Sunday at 12:32.', 1, '2026-05-29 22:07:28');
INSERT INTO notifications (id, user_id, target_role, scope, title, message, notification_type_id, created_at) VALUES (27, 1, NULL, 'personal', NULL, 'The class ''Strength Basics'' on Monday at 18:30 has been updated.', 3, '2026-05-29 22:19:24');
INSERT INTO notifications (id, user_id, target_role, scope, title, message, notification_type_id, created_at) VALUES (35, 12, NULL, 'personal', 'PT Request', 'João Costa has requested a personal training session with you on Friday, December 12, 2031 at 12:00 PM (duration: 60 min).', 1, '2026-05-29 22:52:00');
INSERT INTO notifications (id, user_id, target_role, scope, title, message, notification_type_id, created_at) VALUES (37, 20, NULL, 'personal', NULL, 'Your personal training request has been confirmed by trainer André Ferreira', 2, '2026-05-29 22:52:30');
INSERT INTO notifications (id, user_id, target_role, scope, title, message, notification_type_id, created_at) VALUES (39, 12, NULL, 'personal', 'PT Request', 'João Costa has requested a personal training session with you on Monday, June 21, 2027 at 12:00 PM (duration: 60 min).', 1, '2026-05-29 22:53:15');
INSERT INTO notifications (id, user_id, target_role, scope, title, message, notification_type_id, created_at) VALUES (41, 20, NULL, 'personal', NULL, 'Your personal training request has been confirmed by trainer André Ferreira', 2, '2026-05-29 22:54:53');
INSERT INTO notifications (id, user_id, target_role, scope, title, message, notification_type_id, created_at) VALUES (42, 12, NULL, 'personal', 'Class Enrollment', 'João Costa enrolled in your class ''Saturday Spin''.', 1, '2026-05-29 22:55:47');
INSERT INTO notifications (id, user_id, target_role, scope, title, message, notification_type_id, created_at) VALUES (43, 1, NULL, 'personal', 'Class Enrollment', 'João Costa enrolled in your class ''Morning Yoga''.', 1, '2026-05-29 23:29:05');
INSERT INTO notifications (id, user_id, target_role, scope, title, message, notification_type_id, created_at) VALUES (44, 2, NULL, 'personal', 'Class Enrollment', 'João Costa enrolled in your class ''Boxing Conditioning''.', 1, '2026-05-29 23:29:06');
INSERT INTO notifications (id, user_id, target_role, scope, title, message, notification_type_id, created_at) VALUES (45, 2, NULL, 'personal', 'Class Enrollment', 'João Costa enrolled in your class ''CrossFit WOD''.', 1, '2026-05-29 23:29:07');
INSERT INTO notifications (id, user_id, target_role, scope, title, message, notification_type_id, created_at) VALUES (46, 1, NULL, 'personal', 'Class Enrollment', 'João Costa enrolled in your class ''Pilates Core''.', 1, '2026-05-29 23:29:08');
INSERT INTO notifications (id, user_id, target_role, scope, title, message, notification_type_id, created_at) VALUES (47, 3, NULL, 'personal', 'Class Enrollment', 'João Costa enrolled in your class ''Spinning Class''.', 1, '2026-05-29 23:29:09');
INSERT INTO notifications (id, user_id, target_role, scope, title, message, notification_type_id, created_at) VALUES (48, 1, NULL, 'personal', 'Class Enrollment', 'João Costa enrolled in your class ''Evening Yoga''.', 1, '2026-05-29 23:29:10');
INSERT INTO notifications (id, user_id, target_role, scope, title, message, notification_type_id, created_at) VALUES (49, 1, NULL, 'personal', 'Class Enrollment', 'João Costa enrolled in your class ''teste noite''.', 1, '2026-05-29 23:29:13');
INSERT INTO notifications (id, user_id, target_role, scope, title, message, notification_type_id, created_at) VALUES (50, 2, NULL, 'personal', 'Class Enrollment', 'João Costa enrolled in your class ''Boxing Conditioning''.', 1, '2026-05-29 23:29:28');
INSERT INTO notifications (id, user_id, target_role, scope, title, message, notification_type_id, created_at) VALUES (51, 1, NULL, 'personal', 'Class Enrollment', 'João Costa enrolled in your class ''teste noite''.', 1, '2026-05-29 23:33:35');
INSERT INTO notifications (id, user_id, target_role, scope, title, message, notification_type_id, created_at) VALUES (52, 1, NULL, 'personal', 'Class Enrollment', 'João Costa enrolled in your class ''Morning Yoga''.', 1, '2026-05-29 23:34:05');
INSERT INTO notifications (id, user_id, target_role, scope, title, message, notification_type_id, created_at) VALUES (53, 12, NULL, 'personal', 'Class Enrollment', 'João Costa enrolled in your class ''Saturday Spin''.', 1, '2026-05-29 23:43:13');
INSERT INTO notifications (id, user_id, target_role, scope, title, message, notification_type_id, created_at) VALUES (54, 12, NULL, 'personal', 'Class Enrollment', 'João Costa enrolled in your class ''Saturday Spin''.', 1, '2026-05-29 23:43:21');
INSERT INTO notifications (id, user_id, target_role, scope, title, message, notification_type_id, created_at) VALUES (55, 12, NULL, 'personal', 'Class Enrollment', 'João Costa enrolled in your class ''Saturday Spin''.', 1, '2026-05-29 23:43:43');
INSERT INTO notifications (id, user_id, target_role, scope, title, message, notification_type_id, created_at) VALUES (56, 12, NULL, 'personal', 'Class Enrollment', 'João Costa enrolled in your class ''Saturday Spin''.', 1, '2026-05-29 23:44:07');
INSERT INTO notifications (id, user_id, target_role, scope, title, message, notification_type_id, created_at) VALUES (57, 1, NULL, 'personal', 'Class Enrollment', 'João Costa enrolled in your class ''teste noite''.', 1, '2026-05-29 23:44:11');
INSERT INTO notifications (id, user_id, target_role, scope, title, message, notification_type_id, created_at) VALUES (58, 1, NULL, 'personal', 'Class Enrollment', 'João Costa enrolled in your class ''Morning Yoga''.', 1, '2026-05-29 23:45:19');
INSERT INTO notifications (id, user_id, target_role, scope, title, message, notification_type_id, created_at) VALUES (59, 1, NULL, 'personal', 'Class Enrollment', 'João Costa enrolled in your class ''Strength Basics''.', 1, '2026-05-29 23:45:20');
INSERT INTO notifications (id, user_id, target_role, scope, title, message, notification_type_id, created_at) VALUES (60, 12, NULL, 'personal', 'Class Enrollment', 'João Costa enrolled in your class ''Saturday Spin''.', 1, '2026-05-29 23:45:22');
INSERT INTO notifications (id, user_id, target_role, scope, title, message, notification_type_id, created_at) VALUES (61, 12, NULL, 'personal', 'Class Enrollment', 'João Costa enrolled in your class ''Saturday Spin''.', 1, '2026-05-29 23:45:24');
INSERT INTO notifications (id, user_id, target_role, scope, title, message, notification_type_id, created_at) VALUES (62, 3, NULL, 'personal', 'Class Enrollment', 'João Pereira enrolled in your class ''Senior Fitness''.', 1, '2026-05-29 23:46:04');
INSERT INTO notifications (id, user_id, target_role, scope, title, message, notification_type_id, created_at) VALUES (63, 12, NULL, 'personal', 'Class Enrollment', 'João Costa enrolled in your class ''Saturday Spin''.', 1, '2026-05-30 11:49:43');
INSERT INTO notifications (id, user_id, target_role, scope, title, message, notification_type_id, created_at) VALUES (64, 12, NULL, 'personal', 'Class Enrollment', 'João Costa enrolled in your class ''Saturday Spin''.', 1, '2026-05-30 11:52:29');
INSERT INTO notifications (id, user_id, target_role, scope, title, message, notification_type_id, created_at) VALUES (71, 1, NULL, 'personal', 'Class Enrollment', 'João Costa enrolled in your class ''teste noite''.', 1, '2026-05-30 12:04:40');
INSERT INTO notifications (id, user_id, target_role, scope, title, message, notification_type_id, created_at) VALUES (72, 1, NULL, 'personal', 'Class Enrollment', 'João Costa enrolled in your class ''teste noite''.', 1, '2026-05-30 12:08:08');
INSERT INTO notifications (id, user_id, target_role, scope, title, message, notification_type_id, created_at) VALUES (73, 1, NULL, 'personal', 'Class Enrollment', 'João Costa cancelled enrollment in your class ''teste noite''.', 1, '2026-05-30 12:43:43');
INSERT INTO notifications (id, user_id, target_role, scope, title, message, notification_type_id, created_at) VALUES (74, 1, NULL, 'personal', 'Class Enrollment', 'João Costa enrolled in your class ''teste noite''.', 1, '2026-05-30 12:44:32');
INSERT INTO notifications (id, user_id, target_role, scope, title, message, notification_type_id, created_at) VALUES (75, 1, NULL, 'personal', 'Class Enrollment', 'João Costa cancelled enrollment in your class ''teste noite''.', 1, '2026-05-30 12:44:46');
INSERT INTO notifications (id, user_id, target_role, scope, title, message, notification_type_id, created_at) VALUES (77, 1, NULL, 'personal', 'Class Enrollment', 'João Costa enrolled in your class ''teste noite''.', 1, '2026-05-30 12:45:20');
INSERT INTO notifications (id, user_id, target_role, scope, title, message, notification_type_id, created_at) VALUES (79, 1, NULL, 'personal', 'Class Enrollment', 'João Costa enrolled in your class ''teste noite''.', 1, '2026-05-30 12:45:52');
INSERT INTO notifications (id, user_id, target_role, scope, title, message, notification_type_id, created_at) VALUES (91, 1, NULL, 'personal', 'Class Enrollment', 'João Costa enrolled in your class ''Morning Yoga''.', 1, '2026-05-30 13:01:46');
INSERT INTO notifications (id, user_id, target_role, scope, title, message, notification_type_id, created_at) VALUES (92, 1, NULL, 'personal', NULL, 'The class ''Morning Yoga'' on Monday at 08:00 has been removed (schedule ID: 1).', 3, '2026-05-30 14:10:30');
INSERT INTO notifications (id, user_id, target_role, scope, title, message, notification_type_id, created_at) VALUES (93, 3, NULL, 'personal', NULL, 'The class ''Senior Fitness'' on Tuesday at 10:00 has been removed (schedule ID: 10).', 3, '2026-05-30 14:10:33');
INSERT INTO notifications (id, user_id, target_role, scope, title, message, notification_type_id, created_at) VALUES (94, 2, NULL, 'personal', NULL, 'The class ''Power HIIT'' on Tuesday at 18:00 has been removed (schedule ID: 2).', 3, '2026-05-30 14:10:56');
INSERT INTO notifications (id, user_id, target_role, scope, title, message, notification_type_id, created_at) VALUES (95, 2, NULL, 'personal', NULL, 'The class ''Boxing Conditioning'' on Wednesday at 17:30 has been removed (schedule ID: 9).', 3, '2026-05-30 14:12:26');
INSERT INTO notifications (id, user_id, target_role, scope, title, message, notification_type_id, created_at) VALUES (96, 3, NULL, 'personal', NULL, 'The class ''Spinning Class'' on Wednesday at 19:00 has been removed (schedule ID: 3).', 3, '2026-05-30 14:12:28');
INSERT INTO notifications (id, user_id, target_role, scope, title, message, notification_type_id, created_at) VALUES (97, 1, NULL, 'personal', NULL, 'The class ''Pilates Core'' on Thursday at 10:00 has been removed (schedule ID: 4).', 3, '2026-05-30 14:12:29');
INSERT INTO notifications (id, user_id, target_role, scope, title, message, notification_type_id, created_at) VALUES (98, 2, NULL, 'personal', NULL, 'The class ''CrossFit WOD'' on Friday at 17:30 has been removed (schedule ID: 5).', 3, '2026-05-30 14:12:32');
INSERT INTO notifications (id, user_id, target_role, scope, title, message, notification_type_id, created_at) VALUES (106, NULL, 'admin', 'role', 'New member', 'New member created: João Costa (username: joaocosta, email: jc@gmail.com, plan: Vip).', 1, '2026-05-30 21:18:05');
INSERT INTO notifications (id, user_id, target_role, scope, title, message, notification_type_id, created_at) VALUES (107, NULL, 'admin', 'role', 'Member updated', 'Member updated: João Costa (username: joaocosta, email: jc@gmail.com, plan: Vip, status: Active).', 1, '2026-05-30 21:18:12');

-- Table: password_resets
DROP TABLE IF EXISTS password_resets;

CREATE TABLE IF NOT EXISTS password_resets (
    id         INTEGER  PRIMARY KEY AUTOINCREMENT,
    user_id    INTEGER  NOT NULL,
    token      TEXT     NOT NULL
                        UNIQUE,
    expires_at DATETIME NOT NULL,
    used       INTEGER  NOT NULL
                        DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (
        user_id
    )
    REFERENCES users (id) ON DELETE CASCADE
);

INSERT INTO password_resets (id, user_id, token, expires_at, used, created_at) VALUES (1, 19, 'cfe879a4771731659fb40b69d7775cc44859cc7be68ba76217ad41f0a2ec9780', '2026-05-27 15:50:16', 1, '2026-05-27 14:50:16');

-- Table: personal_bookings
DROP TABLE IF EXISTS personal_bookings;

CREATE TABLE IF NOT EXISTS personal_bookings (
    id           INTEGER  PRIMARY KEY AUTOINCREMENT,
    member_id    INTEGER  NOT NULL,
    trainer_id   INTEGER  NOT NULL,
    booking_date DATE     NOT NULL,
    start_time   TEXT     NOT NULL,
    duration_min INTEGER  DEFAULT 60,
    status       TEXT     DEFAULT 'pending'
                          CHECK (status IN ('pending', 'confirmed', 'cancelled') ),
    notes        TEXT,
    created_at   DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (
        member_id
    )
    REFERENCES users (id) ON DELETE CASCADE,
    FOREIGN KEY (
        trainer_id
    )
    REFERENCES users (id) ON DELETE CASCADE
);

INSERT INTO personal_bookings (id, member_id, trainer_id, booking_date, start_time, duration_min, status, notes, created_at) VALUES (1, 17, 4, '2026-05-29', '15:00', 60, 'confirmed', 'Quero focar em agachamento livre', '2026-05-27 14:49:20');
INSERT INTO personal_bookings (id, member_id, trainer_id, booking_date, start_time, duration_min, status, notes, created_at) VALUES (2, 18, 1, '2026-05-30', '11:00', 45, 'confirmed', 'Aula de yoga privada', '2026-05-27 14:49:20');
INSERT INTO personal_bookings (id, member_id, trainer_id, booking_date, start_time, duration_min, status, notes, created_at) VALUES (3, 20, 12, '2026-07-27', '00:00', 60, 'confirmed', '', '2026-05-27 15:23:21');
INSERT INTO personal_bookings (id, member_id, trainer_id, booking_date, start_time, duration_min, status, notes, created_at) VALUES (4, 20, 12, '2026-06-01', '00:00', 60, 'confirmed', '', '2026-05-27 15:30:31');
INSERT INTO personal_bookings (id, member_id, trainer_id, booking_date, start_time, duration_min, status, notes, created_at) VALUES (5, 20, 12, '2026-05-31', '00:00', 60, 'confirmed', '', '2026-05-29 14:19:37');
INSERT INTO personal_bookings (id, member_id, trainer_id, booking_date, start_time, duration_min, status, notes, created_at) VALUES (6, 20, 5, '2026-08-01', '00:00', 60, 'confirmed', '', '2026-05-29 14:26:13');
INSERT INTO personal_bookings (id, member_id, trainer_id, booking_date, start_time, duration_min, status, notes, created_at) VALUES (7, 20, 12, '2026-07-01', '00:00', 60, 'confirmed', '', '2026-05-29 19:29:51');
INSERT INTO personal_bookings (id, member_id, trainer_id, booking_date, start_time, duration_min, status, notes, created_at) VALUES (8, 20, 12, '2026-08-02', '00:00', 60, 'confirmed', '', '2026-05-29 19:44:38');
INSERT INTO personal_bookings (id, member_id, trainer_id, booking_date, start_time, duration_min, status, notes, created_at) VALUES (9, 20, 12, '2026-05-30', '04:40', 60, 'confirmed', '', '2026-05-29 19:45:37');
INSERT INTO personal_bookings (id, member_id, trainer_id, booking_date, start_time, duration_min, status, notes, created_at) VALUES (10, 20, 12, '2031-12-12', '12:00', 60, 'confirmed', '', '2026-05-29 22:52:00');
INSERT INTO personal_bookings (id, member_id, trainer_id, booking_date, start_time, duration_min, status, notes, created_at) VALUES (11, 20, 12, '2027-06-21', '12:00', 60, 'confirmed', '', '2026-05-29 22:53:15');

-- Table: reviews
DROP TABLE IF EXISTS reviews;

CREATE TABLE IF NOT EXISTS reviews (
    id         INTEGER  PRIMARY KEY AUTOINCREMENT,
    member_id  INTEGER  NOT NULL,
    session_id INTEGER  NOT NULL,
    rating     INTEGER  NOT NULL
                        CHECK (rating BETWEEN 1 AND 5),
    comment    TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE (
        member_id,
        session_id
    ),
    FOREIGN KEY (
        member_id
    )
    REFERENCES users (id) ON DELETE CASCADE,
    FOREIGN KEY (
        session_id
    )
    REFERENCES class_sessions (id) ON DELETE CASCADE
);

INSERT INTO reviews (id, member_id, session_id, rating, comment, created_at) VALUES (1, 14, 1, 5, 'Excelente aula! A Ana é incrível.', '2026-05-27 14:49:20');
INSERT INTO reviews (id, member_id, session_id, rating, comment, created_at) VALUES (2, 15, 3, 4, 'Treino muito intenso, adorei!', '2026-05-27 14:49:20');
INSERT INTO reviews (id, member_id, session_id, rating, comment, created_at) VALUES (3, 16, 5, 5, 'A melhor aula de spinning da minha vida.', '2026-05-27 14:49:20');

-- Table: trainer_profiles
DROP TABLE IF EXISTS trainer_profiles;

CREATE TABLE IF NOT EXISTS trainer_profiles (
    id              INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id         INTEGER NOT NULL
                            UNIQUE,
    bio             TEXT,
    specializations TEXT,
    certifications  TEXT,
    is_pt           INTEGER NOT NULL
                            DEFAULT 0,
    pt_description  TEXT,
    pt_hourly_rate  REAL,
    pt_available    INTEGER NOT NULL
                            DEFAULT 1,
    FOREIGN KEY (
        user_id
    )
    REFERENCES users (id) ON DELETE CASCADE
);

INSERT INTO trainer_profiles (id, user_id, bio, specializations, certifications, is_pt, pt_description, pt_hourly_rate, pt_available) VALUES (1, 1, 'Yoga & Pilates specialist with 10 years experience.', 'Yoga, Pilates, Flexibility', 'ACE CPT, RYT-200', 0, NULL, NULL, 0);
INSERT INTO trainer_profiles (id, user_id, bio, specializations, certifications, is_pt, pt_description, pt_hourly_rate, pt_available) VALUES (2, 2, 'High-intensity training enthusiast and former athlete.', 'HIIT, CrossFit, Strength', 'NSCA-CSCS, CF-L2', 0, NULL, NULL, 0);
INSERT INTO trainer_profiles (id, user_id, bio, specializations, certifications, is_pt, pt_description, pt_hourly_rate, pt_available) VALUES (3, 3, 'Certified spinning instructor and endurance coach.', 'Spinning, Cycling, Cardio', 'Spinning Certified, ACE', 0, NULL, NULL, 0);
INSERT INTO trainer_profiles (id, user_id, bio, specializations, certifications, is_pt, pt_description, pt_hourly_rate, pt_available) VALUES (4, 4, 'Strength coach focused on hypertrophy and performance.', 'Strength, Hypertrophy, Functional Training', 'ACE CPT', 0, NULL, NULL, 0);
INSERT INTO trainer_profiles (id, user_id, bio, specializations, certifications, is_pt, pt_description, pt_hourly_rate, pt_available) VALUES (5, 5, 'Personal trainer focused on weight loss, mobility and beginner-friendly training.', 'Weight Loss, Mobility, Beginner Training', 'ACE CPT', 1, 'Beginner-friendly PT sessions focused on fat loss, confidence and safe technique.', NULL, 1);
INSERT INTO trainer_profiles (id, user_id, bio, specializations, certifications, is_pt, pt_description, pt_hourly_rate, pt_available) VALUES (6, 6, 'Strength and conditioning coach specialized in muscle gain and athletic performance.', 'Strength, Hypertrophy, Performance', 'NSCA-CSCS', 1, 'Strength-focused PT sessions for muscle gain, athletic performance and progressive overload.', NULL, 1);
INSERT INTO trainer_profiles (id, user_id, bio, specializations, certifications, is_pt, pt_description, pt_hourly_rate, pt_available) VALUES (7, 7, 'Functional training coach focused on posture, core strength and injury prevention.', 'Functional Training, Core, Injury Prevention', 'NASM CPT', 1, 'Functional PT sessions focused on posture, core strength and injury prevention.', NULL, 1);
INSERT INTO trainer_profiles (id, user_id, bio, specializations, certifications, is_pt, pt_description, pt_hourly_rate, pt_available) VALUES (8, 8, 'Coach focused on CrossFit, metabolic conditioning and high-intensity workouts.', 'CrossFit, HIIT, Conditioning', 'CF-L1, ACE CPT', 1, 'Personal training focused on CrossFit, conditioning and high-intensity performance.', NULL, 1);
INSERT INTO trainer_profiles (id, user_id, bio, specializations, certifications, is_pt, pt_description, pt_hourly_rate, pt_available) VALUES (9, 9, 'Yoga and Pilates instructor focused on flexibility, breathing and body control.', 'Yoga, Pilates, Flexibility', 'RYT-200, Pilates Instructor', 1, 'PT sessions focused on flexibility, posture, breathing and body control.', NULL, 1);
INSERT INTO trainer_profiles (id, user_id, bio, specializations, certifications, is_pt, pt_description, pt_hourly_rate, pt_available) VALUES (10, 10, 'Personal trainer specialized in strength training, fat loss and nutrition guidance.', 'Strength, Fat Loss, Nutrition', 'ACE CPT, Nutrition Coach', 1, 'Personal training focused on strength, fat loss and nutrition guidance.', NULL, 1);
INSERT INTO trainer_profiles (id, user_id, bio, specializations, certifications, is_pt, pt_description, pt_hourly_rate, pt_available) VALUES (11, 11, 'Trainer focused on women fitness, glute training and full-body strength.', 'Women Fitness, Glutes, Strength', 'NASM CPT', 1, 'PT sessions focused on glute training, full-body strength and confidence.', NULL, 1);
INSERT INTO trainer_profiles (id, user_id, bio, specializations, certifications, is_pt, pt_description, pt_hourly_rate, pt_available) VALUES (12, 12, 'Boxing and conditioning coach focused on endurance, agility and coordination.', 'Boxing, Cardio, Agility', 'Boxing Coach, ACE', 1, 'Personal training focused on boxing, cardio, agility and endurance.', NULL, 1);
INSERT INTO trainer_profiles (id, user_id, bio, specializations, certifications, is_pt, pt_description, pt_hourly_rate, pt_available) VALUES (13, 23, '', 'Yoga, Pilates, Flexibility', 'ACE CPT, RYT-200', 0, NULL, NULL, 1);
INSERT INTO trainer_profiles (id, user_id, bio, specializations, certifications, is_pt, pt_description, pt_hourly_rate, pt_available) VALUES (14, 24, '', 'Yoga, Pilates, Flexibility', 'ACE CPT, RYT-200', 0, NULL, NULL, 1);

-- Table: users
DROP TABLE IF EXISTS users;

CREATE TABLE IF NOT EXISTS users (
    id         INTEGER  PRIMARY KEY AUTOINCREMENT,
    name       TEXT     NOT NULL,
    username   TEXT     UNIQUE
                        NOT NULL,
    email      TEXT     UNIQUE
                        NOT NULL,
    password   TEXT     NOT NULL,
    role       TEXT     NOT NULL
                        DEFAULT 'member'
                        CHECK (role IN ('member', 'trainer', 'admin') ),
    is_admin   INTEGER  NOT NULL
                        DEFAULT 0,
    photo      TEXT     DEFAULT NULL,
    active     INTEGER  NOT NULL
                        DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO users (id, name, username, email, password, role, is_admin, photo, active, created_at) VALUES (1, 'Ana Silva', 'anasilva', 'ana@fitpeak.com', '$2y$12$PR0ITodgiM5l6cZfbMn8vOvakDy7C8WJulzZhOz8WT7bUgG7ftGvm', 'trainer', 0, NULL, 1, '2026-05-27 14:49:20');
INSERT INTO users (id, name, username, email, password, role, is_admin, photo, active, created_at) VALUES (2, 'Bruno Costa', 'brunocosta', 'bruno@fitpeak.com', '$2y$12$PR0ITodgiM5l6cZfbMn8vOvakDy7C8WJulzZhOz8WT7bUgG7ftGvm', 'trainer', 0, NULL, 1, '2026-05-27 14:49:20');
INSERT INTO users (id, name, username, email, password, role, is_admin, photo, active, created_at) VALUES (3, 'Carla Mendes', 'carlamendes', 'carla@fitpeak.com', '$2y$12$PR0ITodgiM5l6cZfbMn8vOvakDy7C8WJulzZhOz8WT7bUgG7ftGvm', 'trainer', 0, NULL, 1, '2026-05-27 14:49:20');
INSERT INTO users (id, name, username, email, password, role, is_admin, photo, active, created_at) VALUES (4, 'Miguel Rocha', 'miguelrocha', 'miguel@fitpeak.com', '$2y$12$PR0ITodgiM5l6cZfbMn8vOvakDy7C8WJulzZhOz8WT7bUgG7ftGvm', 'trainer', 0, NULL, 1, '2026-05-27 14:49:20');
INSERT INTO users (id, name, username, email, password, role, is_admin, photo, active, created_at) VALUES (5, 'Inês Almeida', 'inesalmeida', 'ines@fitpeak.com', '$2y$12$PR0ITodgiM5l6cZfbMn8vOvakDy7C8WJulzZhOz8WT7bUgG7ftGvm', 'trainer', 0, NULL, 1, '2026-05-27 14:49:20');
INSERT INTO users (id, name, username, email, password, role, is_admin, photo, active, created_at) VALUES (6, 'Tiago Martins', 'tiagomartins', 'tiago@fitpeak.com', '$2y$12$PR0ITodgiM5l6cZfbMn8vOvakDy7C8WJulzZhOz8WT7bUgG7ftGvm', 'trainer', 0, NULL, 1, '2026-05-27 14:49:20');
INSERT INTO users (id, name, username, email, password, role, is_admin, photo, active, created_at) VALUES (7, 'Beatriz Ramos', 'beatrizramos', 'beatriz@fitpeak.com', '$2y$12$PR0ITodgiM5l6cZfbMn8vOvakDy7C8WJulzZhOz8WT7bUgG7ftGvm', 'trainer', 0, NULL, 1, '2026-05-27 14:49:20');
INSERT INTO users (id, name, username, email, password, role, is_admin, photo, active, created_at) VALUES (8, 'Duarte Oliveira', 'duarteoliveira', 'duarte@fitpeak.com', '$2y$12$PR0ITodgiM5l6cZfbMn8vOvakDy7C8WJulzZhOz8WT7bUgG7ftGvm', 'trainer', 0, NULL, 1, '2026-05-27 14:49:20');
INSERT INTO users (id, name, username, email, password, role, is_admin, photo, active, created_at) VALUES (9, 'Mariana Lopes', 'marianalopes', 'mariana@fitpeak.com', '$2y$12$PR0ITodgiM5l6cZfbMn8vOvakDy7C8WJulzZhOz8WT7bUgG7ftGvm', 'trainer', 0, NULL, 1, '2026-05-27 14:49:20');
INSERT INTO users (id, name, username, email, password, role, is_admin, photo, active, created_at) VALUES (10, 'Ricardo Nunes', 'ricardonunes', 'ricardo@fitpeak.com', '$2y$12$PR0ITodgiM5l6cZfbMn8vOvakDy7C8WJulzZhOz8WT7bUgG7ftGvm', 'trainer', 0, NULL, 1, '2026-05-27 14:49:20');
INSERT INTO users (id, name, username, email, password, role, is_admin, photo, active, created_at) VALUES (11, 'Catarina Gomes', 'catarinagomes', 'catarina@fitpeak.com', '$2y$12$PR0ITodgiM5l6cZfbMn8vOvakDy7C8WJulzZhOz8WT7bUgG7ftGvm', 'trainer', 0, NULL, 1, '2026-05-27 14:49:20');
INSERT INTO users (id, name, username, email, password, role, is_admin, photo, active, created_at) VALUES (12, 'André Ferreira', 'andreferreira', 'andre@fitpeak.com', '$2y$12$PR0ITodgiM5l6cZfbMn8vOvakDy7C8WJulzZhOz8WT7bUgG7ftGvm', 'trainer', 0, NULL, 1, '2026-05-27 14:49:20');
INSERT INTO users (id, name, username, email, password, role, is_admin, photo, active, created_at) VALUES (13, 'Admin User', 'admin', 'admin@fitpeak.com', '$2y$12$PR0ITodgiM5l6cZfbMn8vOvakDy7C8WJulzZhOz8WT7bUgG7ftGvm', 'admin', 1, '../uploads/profiles/profile_7d5885f64f8520eea7eda0f0.jpg', 1, '2026-05-27 14:49:20');
INSERT INTO users (id, name, username, email, password, role, is_admin, photo, active, created_at) VALUES (14, 'João Pereira', 'joaop', 'joao@email.com', '$2y$12$PR0ITodgiM5l6cZfbMn8vOvakDy7C8WJulzZhOz8WT7bUgG7ftGvm', 'member', 1, NULL, 1, '2026-05-27 14:49:20');
INSERT INTO users (id, name, username, email, password, role, is_admin, photo, active, created_at) VALUES (15, 'Maria Santos', 'marias', 'maria@email.com', '$2y$12$PR0ITodgiM5l6cZfbMn8vOvakDy7C8WJulzZhOz8WT7bUgG7ftGvm', 'member', 0, NULL, 1, '2026-05-27 14:49:20');
INSERT INTO users (id, name, username, email, password, role, is_admin, photo, active, created_at) VALUES (16, 'Rita Ferreira', 'ritaf', 'rita@email.com', '$2y$12$PR0ITodgiM5l6cZfbMn8vOvakDy7C8WJulzZhOz8WT7bUgG7ftGvm', 'member', 0, NULL, 1, '2026-05-27 14:49:20');
INSERT INTO users (id, name, username, email, password, role, is_admin, photo, active, created_at) VALUES (17, 'Carlos Andrade', 'carlosa', 'carlos@email.com', '$2y$12$PR0ITodgiM5l6cZfbMn8vOvakDy7C8WJulzZhOz8WT7bUgG7ftGvm', 'member', 0, NULL, 1, '2026-05-27 14:49:20');
INSERT INTO users (id, name, username, email, password, role, is_admin, photo, active, created_at) VALUES (18, 'Sofia Lima', 'sofial', 'sofia@email.com', '$2y$12$PR0ITodgiM5l6cZfbMn8vOvakDy7C8WJulzZhOz8WT7bUgG7ftGvm', 'member', 0, NULL, 1, '2026-05-27 14:49:20');
INSERT INTO users (id, name, username, email, password, role, is_admin, photo, active, created_at) VALUES (19, 'Basic Member', 'member_basic', 'memberbasic@email.com', '$2y$12$PR0ITodgiM5l6cZfbMn8vOvakDy7C8WJulzZhOz8WT7bUgG7ftGvm', 'member', 0, NULL, 1, '2026-05-30 20:34:43');
INSERT INTO users (id, name, username, email, password, role, is_admin, photo, active, created_at) VALUES (20, 'Premium Member', 'member_premium', 'memberpremium@email.com', '$2y$12$PR0ITodgiM5l6cZfbMn8vOvakDy7C8WJulzZhOz8WT7bUgG7ftGvm', 'member', 0, NULL, 1, '2026-05-30 20:36:07');
INSERT INTO users (id, name, username, email, password, role, is_admin, photo, active, created_at) VALUES (21, 'VIP Member', 'member_vip', 'membervip@email.com', '$2y$12$PR0ITodgiM5l6cZfbMn8vOvakDy7C8WJulzZhOz8WT7bUgG7ftGvm', 'member', 0, NULL, 1, '2026-05-30 20:37:44');
INSERT INTO users (id, name, username, email, password, role, is_admin, photo, active, created_at) VALUES (22, 'Admin Member', 'member_admin', 'memberadmin@email.com', '$2y$12$PR0ITodgiM5l6cZfbMn8vOvakDy7C8WJulzZhOz8WT7bUgG7ftGvm', 'member', 1, NULL, 1, '2026-05-30 20:41:00');
INSERT INTO users (id, name, username, email, password, role, is_admin, photo, active, created_at) VALUES (23, 'Trainer', 'trainer', 'trainer@email.com', '$2y$12$PR0ITodgiM5l6cZfbMn8vOvakDy7C8WJulzZhOz8WT7bUgG7ftGvm', 'trainer', 0, NULL, 1, '2026-05-30 20:42:34');
INSERT INTO users (id, name, username, email, password, role, is_admin, photo, active, created_at) VALUES (24, 'Admin Trainer', 'trainer_admin', 'traineradmin@email.com', '$2y$12$PR0ITodgiM5l6cZfbMn8vOvakDy7C8WJulzZhOz8WT7bUgG7ftGvm', 'trainer', 1, NULL, 1, '2026-05-30 20:43:22');
INSERT INTO users (id, name, username, email, password, role, is_admin, photo, active, created_at) VALUES (25, 'João Costa', 'joaocosta', 'jc@gmail.com', '$2y$12$PR0ITodgiM5l6cZfbMn8vOvakDy7C8WJulzZhOz8WT7bUgG7ftGvm', 'member', 1, NULL, 1, '2026-05-30 21:18:02');

COMMIT TRANSACTION;
PRAGMA foreign_keys = on;
