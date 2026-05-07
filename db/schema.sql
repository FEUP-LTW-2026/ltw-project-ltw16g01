-- Enable foreign keys in SQLite
PRAGMA foreign_keys = ON;

-- Stores all users: members, trainers and admins
CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    username TEXT UNIQUE NOT NULL,
    email TEXT UNIQUE NOT NULL,
    password TEXT NOT NULL,
    role TEXT NOT NULL DEFAULT 'member' CHECK(role IN ('member','trainer','admin')),
    photo TEXT DEFAULT NULL,
    active INTEGER NOT NULL DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Extra information for trainers
CREATE TABLE IF NOT EXISTS trainer_profiles (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL UNIQUE,
    bio TEXT,
    specializations TEXT,
    certifications TEXT,
    FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Extra information for members
CREATE TABLE IF NOT EXISTS member_profiles (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL UNIQUE,
    membership_plan TEXT DEFAULT 'basic' CHECK(membership_plan IN ('basic','premium','vip')),
    plan_start DATE,
    plan_end DATE,
    FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Gym classes
CREATE TABLE IF NOT EXISTS classes (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    description TEXT,
    type TEXT NOT NULL,
    trainer_id INTEGER,
    capacity INTEGER NOT NULL DEFAULT 20,
    duration_min INTEGER NOT NULL DEFAULT 60,
    day_of_week TEXT,
    start_time TEXT,
    location TEXT DEFAULT 'Studio A',
    active INTEGER NOT NULL DEFAULT 1,
    FOREIGN KEY(trainer_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Scheduled class sessions
CREATE TABLE IF NOT EXISTS class_sessions (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    class_id INTEGER NOT NULL,
    session_date DATE NOT NULL,
    status TEXT DEFAULT 'scheduled' CHECK(status IN ('scheduled','cancelled','completed')),
    FOREIGN KEY(class_id) REFERENCES classes(id) ON DELETE CASCADE
);

-- Member enrollments in classes
CREATE TABLE IF NOT EXISTS enrollments (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    member_id INTEGER NOT NULL,
    session_id INTEGER NOT NULL,
    enrolled_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    status TEXT DEFAULT 'enrolled' CHECK(status IN ('enrolled','waitlist','cancelled')),
    UNIQUE(member_id, session_id),
    FOREIGN KEY(member_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY(session_id) REFERENCES class_sessions(id) ON DELETE CASCADE
);

-- Gym equipment availability
CREATE TABLE IF NOT EXISTS equipment (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    category TEXT NOT NULL,
    quantity INTEGER NOT NULL DEFAULT 1,
    available INTEGER NOT NULL DEFAULT 1,
    status TEXT NOT NULL DEFAULT 'available' CHECK(status IN ('available','maintenance','unavailable')),
    notes TEXT,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Reviews and ratings for classes
CREATE TABLE IF NOT EXISTS reviews (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    member_id INTEGER NOT NULL,
    class_id INTEGER NOT NULL,
    rating INTEGER NOT NULL CHECK(rating BETWEEN 1 AND 5),
    comment TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(member_id, class_id),
    FOREIGN KEY(member_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY(class_id) REFERENCES classes(id) ON DELETE CASCADE
);

-- Personal trainer bookings
CREATE TABLE IF NOT EXISTS personal_bookings (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    member_id INTEGER NOT NULL,
    trainer_id INTEGER NOT NULL,
    booking_date DATE NOT NULL,
    start_time TEXT NOT NULL,
    duration_min INTEGER DEFAULT 60,
    status TEXT DEFAULT 'pending' CHECK(status IN ('pending','confirmed','cancelled')),
    notes TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY(member_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY(trainer_id) REFERENCES users(id) ON DELETE CASCADE
);

-- User notifications
CREATE TABLE IF NOT EXISTS notifications (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    message TEXT NOT NULL,
    type TEXT DEFAULT 'info',
    read_at DATETIME DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE
);