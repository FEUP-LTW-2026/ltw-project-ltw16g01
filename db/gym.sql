PRAGMA foreign_keys = ON;

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

CREATE TABLE IF NOT EXISTS trainer_profiles (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL UNIQUE,
    bio TEXT,
    specializations TEXT,
    certifications TEXT,
    FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS member_profiles (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL UNIQUE,
    membership_plan TEXT DEFAULT 'basic' CHECK(membership_plan IN ('basic','premium','vip')),
    plan_start DATE,
    plan_end DATE,
    FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE
);

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

CREATE TABLE IF NOT EXISTS class_sessions (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    class_id INTEGER NOT NULL,
    session_date DATE NOT NULL,
    status TEXT DEFAULT 'scheduled' CHECK(status IN ('scheduled','cancelled','completed')),
    FOREIGN KEY(class_id) REFERENCES classes(id) ON DELETE CASCADE
);

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

CREATE TABLE IF NOT EXISTS notifications (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    message TEXT NOT NULL,
    type TEXT DEFAULT 'info',
    read_at DATETIME DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE
);

INSERT INTO users (name, username, email, password, role) VALUES
('Ana Silva', 'anasilva', 'ana@fitpeak.com', '12345678', 'trainer'),
('Bruno Costa', 'brunocosta', 'bruno@fitpeak.com', '12345678', 'trainer'),
('Carla Mendes', 'carlamendes', 'carla@fitpeak.com', '12345678', 'trainer'),
('Miguel Rocha', 'miguelrocha', 'miguel@fitpeak.com', '12345678', 'trainer'),
('Admin User', 'admin', 'admin@fitpeak.com', '12345678', 'admin');

INSERT INTO trainer_profiles (user_id, bio, specializations, certifications)
SELECT id, 'Yoga & Pilates specialist with 10 years experience.', 'Yoga, Pilates, Flexibility', 'ACE CPT, RYT-200'
FROM users WHERE username = 'anasilva';

INSERT INTO trainer_profiles (user_id, bio, specializations, certifications)
SELECT id, 'High-intensity training enthusiast and former athlete.', 'HIIT, CrossFit, Strength', 'NSCA-CSCS, CF-L2'
FROM users WHERE username = 'brunocosta';

INSERT INTO trainer_profiles (user_id, bio, specializations, certifications)
SELECT id, 'Certified spinning instructor and endurance coach.', 'Spinning, Cycling, Cardio', 'Spinning Certified, ACE'
FROM users WHERE username = 'carlamendes';

INSERT INTO trainer_profiles (user_id, bio, specializations, certifications)
SELECT id, 'Strength coach focused on hypertrophy and performance.', 'Strength, Hypertrophy, Functional Training', 'ACE CPT'
FROM users WHERE username = 'miguelrocha';

INSERT INTO classes (name, description, type, trainer_id, capacity, duration_min, day_of_week, start_time, location)
SELECT 'Morning Yoga', 'Start your day with calm and flexibility', 'yoga', id, 15, 60, 'Monday', '07:00', 'Studio A'
FROM users WHERE username = 'anasilva';

INSERT INTO classes (name, description, type, trainer_id, capacity, duration_min, day_of_week, start_time, location)
SELECT 'Power HIIT', 'Burn calories and build strength', 'hiit', id, 20, 45, 'Tuesday', '18:00', 'Main Floor'
FROM users WHERE username = 'brunocosta';

INSERT INTO classes (name, description, type, trainer_id, capacity, duration_min, day_of_week, start_time, location)
SELECT 'Spinning Class', 'High-energy cycling for all levels', 'cycling', id, 18, 50, 'Wednesday', '19:00', 'Cycling Studio'
FROM users WHERE username = 'carlamendes';

INSERT INTO classes (name, description, type, trainer_id, capacity, duration_min, day_of_week, start_time, location)
SELECT 'Pilates Core', 'Strengthen your core with controlled movements', 'pilates', id, 12, 55, 'Thursday', '10:00', 'Studio B'
FROM users WHERE username = 'anasilva';

INSERT INTO classes (name, description, type, trainer_id, capacity, duration_min, day_of_week, start_time, location)
SELECT 'CrossFit WOD', 'Workout of the day — push your limits', 'crossfit', id, 20, 60, 'Friday', '17:30', 'Main Floor'
FROM users WHERE username = 'brunocosta';

INSERT INTO classes (name, description, type, trainer_id, capacity, duration_min, day_of_week, start_time, location)
SELECT 'Evening Yoga', 'Wind down and restore with evening yoga', 'yoga', id, 15, 60, 'Friday', '20:00', 'Studio A'
FROM users WHERE username = 'anasilva';

INSERT INTO classes (name, description, type, trainer_id, capacity, duration_min, day_of_week, start_time, location)
SELECT 'Saturday Spin', 'Weekend ride to start your Saturday right', 'cycling', id, 18, 60, 'Saturday', '09:00', 'Cycling Studio'
FROM users WHERE username = 'carlamendes';

INSERT INTO classes (name, description, type, trainer_id, capacity, duration_min, day_of_week, start_time, location)
SELECT 'Strength Basics', 'Build strength with proper technique and progressive overload', 'strength', id, 16, 60, 'Monday', '18:30', 'Main Floor'
FROM users WHERE username = 'miguelrocha';

INSERT INTO classes (name, description, type, trainer_id, capacity, duration_min, day_of_week, start_time, location)
SELECT 'Boxing Conditioning', 'Improve endurance, speed and coordination with boxing drills', 'boxing', id, 14, 50, 'Wednesday', '17:30', 'Boxing Area'
FROM users WHERE username = 'brunocosta';

INSERT INTO classes (name, description, type, trainer_id, capacity, duration_min, day_of_week, start_time, location)
SELECT 'Senior Fitness', 'Low-impact training focused on mobility, balance and strength', 'senior-fitness', id, 12, 45, 'Tuesday', '10:00', 'Studio B'
FROM users WHERE username = 'carlamendes';

INSERT INTO class_sessions (class_id, session_date)
SELECT id, date('now', 'weekday 1') FROM classes WHERE day_of_week = 'Monday';

INSERT INTO class_sessions (class_id, session_date)
SELECT id, date('now', 'weekday 2') FROM classes WHERE day_of_week = 'Tuesday';

INSERT INTO class_sessions (class_id, session_date)
SELECT id, date('now', 'weekday 3') FROM classes WHERE day_of_week = 'Wednesday';

INSERT INTO class_sessions (class_id, session_date)
SELECT id, date('now', 'weekday 4') FROM classes WHERE day_of_week = 'Thursday';

INSERT INTO class_sessions (class_id, session_date)
SELECT id, date('now', 'weekday 5') FROM classes WHERE day_of_week = 'Friday';

INSERT INTO class_sessions (class_id, session_date)
SELECT id, date('now', 'weekday 6') FROM classes WHERE day_of_week = 'Saturday';

INSERT INTO equipment (name, category, quantity, available, status) VALUES
('Treadmill', 'Cardio', 8, 6, 'available'),
('Stationary Bike', 'Cardio', 6, 5, 'available'),
('Rowing Machine', 'Cardio', 4, 0, 'unavailable'),
('Elliptical', 'Cardio', 4, 4, 'available'),
('Weight Bench', 'Strength', 10, 8, 'available'),
('Squat Rack', 'Strength', 4, 3, 'available'),
('Pull-up Bar', 'Strength', 6, 6, 'available'),
('Dumbbell Set', 'Free Weights', 20, 18, 'available'),
('Barbell Set', 'Free Weights', 8, 7, 'available'),
('Kettlebell Set', 'Free Weights', 12, 10, 'available'),
('Yoga Mat', 'Accessories', 30, 25, 'available'),
('Foam Roller', 'Accessories', 15, 12, 'available'),
('Battle Rope', 'Functional', 4, 3, 'available'),
('TRX Suspension', 'Functional', 6, 5, 'available'),
('Punching Bag', 'Cardio', 3, 2, 'available');