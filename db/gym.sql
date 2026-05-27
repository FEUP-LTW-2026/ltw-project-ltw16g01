PRAGMA foreign_keys = ON;

-- ============================================================
-- 1. TABELAS DE LOOKUP (NORMALIZAÇÃO)
-- ============================================================

-- Tipos de aula
CREATE TABLE IF NOT EXISTS class_types (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT UNIQUE NOT NULL,
    label TEXT NOT NULL,
    description TEXT,
    icon TEXT,
    active INTEGER NOT NULL DEFAULT 1
);

-- Categorias de equipamento
CREATE TABLE IF NOT EXISTS equipment_categories (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT UNIQUE NOT NULL,
    label TEXT NOT NULL,
    description TEXT,
    active INTEGER NOT NULL DEFAULT 1
);

-- Tipos de notificação
CREATE TABLE IF NOT EXISTS notification_types (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT UNIQUE NOT NULL,
    label TEXT NOT NULL,
    css_class TEXT DEFAULT 'info',
    active INTEGER NOT NULL DEFAULT 1
);

-- ============================================================
-- 2. TABELAS PRINCIPAIS
-- ============================================================

-- Utilizadores (membros, treinadores, admin)
CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    username TEXT UNIQUE NOT NULL,
    email TEXT UNIQUE NOT NULL,
    password TEXT NOT NULL,
    role TEXT NOT NULL DEFAULT 'member' CHECK(role IN ('member','trainer','admin')),
    is_admin INTEGER NOT NULL DEFAULT 0,
    photo TEXT DEFAULT NULL,
    active INTEGER NOT NULL DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Tokens de recuperação de password
CREATE TABLE IF NOT EXISTS password_resets (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    token TEXT NOT NULL UNIQUE,
    expires_at DATETIME NOT NULL,
    used INTEGER NOT NULL DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Perfis de treinador (1:1 com users)
CREATE TABLE IF NOT EXISTS trainer_profiles (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL UNIQUE,
    bio TEXT,
    specializations TEXT,
    certifications TEXT,
    is_pt INTEGER NOT NULL DEFAULT 0,
    pt_description TEXT,
    pt_hourly_rate REAL,
    pt_available INTEGER NOT NULL DEFAULT 1,
    FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Perfis de membro (1:1 com users)
CREATE TABLE IF NOT EXISTS member_profiles (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL UNIQUE,
    membership_plan TEXT DEFAULT 'basic' CHECK(membership_plan IN ('basic','premium','vip')),
    plan_start DATE,
    plan_end DATE,
    FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Aulas (apenas dados intrínsecos)
CREATE TABLE IF NOT EXISTS classes (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    description TEXT,
    class_type_id INTEGER NOT NULL,
    active INTEGER NOT NULL DEFAULT 1,
    FOREIGN KEY(class_type_id) REFERENCES class_types(id)
);

-- Horários regulares (cada aula pode ter vários horários)
CREATE TABLE IF NOT EXISTS class_schedules (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    class_id INTEGER NOT NULL,
    trainer_id INTEGER,
    day_of_week TEXT NOT NULL,   -- Monday, Tuesday, etc.
    start_time TEXT NOT NULL,
    location TEXT NOT NULL DEFAULT 'Studio A',
    capacity INTEGER NOT NULL DEFAULT 20,
    duration_min INTEGER NOT NULL DEFAULT 60,
    FOREIGN KEY(class_id) REFERENCES classes(id) ON DELETE CASCADE,
    FOREIGN KEY(trainer_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Sessões concretas (ocorrências reais no calendário)
CREATE TABLE IF NOT EXISTS class_sessions (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    schedule_id INTEGER NOT NULL,
    session_date DATE NOT NULL,
    status TEXT DEFAULT 'scheduled' CHECK(status IN ('scheduled','cancelled','completed')),
    FOREIGN KEY(schedule_id) REFERENCES class_schedules(id) ON DELETE CASCADE
);

-- Inscrições de membros em sessões
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

-- Equipamento
CREATE TABLE IF NOT EXISTS equipment (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    category_id INTEGER NOT NULL,
    quantity INTEGER NOT NULL DEFAULT 1,
    available INTEGER NOT NULL DEFAULT 1,
    status TEXT NOT NULL DEFAULT 'available' CHECK(status IN ('available','maintenance','unavailable')),
    notes TEXT,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY(category_id) REFERENCES equipment_categories(id)
);

-- Reservas de equipamento, disponíveis apenas para membros VIP
CREATE TABLE IF NOT EXISTS equipment_reservations (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    member_id INTEGER NOT NULL,
    equipment_id INTEGER NOT NULL,
    reservation_date DATE NOT NULL,
    reservation_time TEXT NOT NULL,
    quantity INTEGER NOT NULL DEFAULT 1,
    status TEXT NOT NULL DEFAULT 'active' CHECK(status IN ('active','cancelled','completed')),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY(member_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY(equipment_id) REFERENCES equipment(id) ON DELETE CASCADE
);

-- Avaliações (por sessão, não por aula)
CREATE TABLE IF NOT EXISTS reviews (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    member_id INTEGER NOT NULL,
    session_id INTEGER NOT NULL,
    rating INTEGER NOT NULL CHECK(rating BETWEEN 1 AND 5),
    comment TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(member_id, session_id),
    FOREIGN KEY(member_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY(session_id) REFERENCES class_sessions(id) ON DELETE CASCADE
);

-- Reservas pessoais com treinadores
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

-- Notificações
CREATE TABLE IF NOT EXISTS notifications (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    message TEXT NOT NULL,
    notification_type_id INTEGER NOT NULL,
    read_at DATETIME DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY(notification_type_id) REFERENCES notification_types(id)
);

-- ============================================================
-- 3. INSERÇÃO DE DADOS (LOOKUP TABLES)
-- ============================================================

-- Tipos de aula
INSERT INTO class_types (name, label, description, icon) VALUES
('yoga', 'Yoga', 'Mind-body practice focusing on flexibility and relaxation', '🧘'),
('pilates', 'Pilates', 'Core strength and controlled movements', '🏋️'),
('hiit', 'HIIT', 'High-intensity interval training for cardio and strength', '🔥'),
('cycling', 'Cycling', 'Indoor cycling / spinning classes', '🚴'),
('strength', 'Strength', 'Weight training and muscle building', '💪'),
('crossfit', 'CrossFit', 'Functional fitness and high-intensity workouts', '⚡'),
('boxing', 'Boxing', 'Combat sports conditioning and agility', '🥊'),
('senior-fitness', 'Senior Fitness', 'Low-impact training for older adults', '👴');

-- Categorias de equipamento
INSERT INTO equipment_categories (name, label, description) VALUES
('Cardio', 'Cardio Machines', 'Treadmills, bikes, rowers, ellipticals'),
('Strength', 'Strength Equipment', 'Benches, racks, bars, machines'),
('Free Weights', 'Free Weights', 'Dumbbells, kettlebells, barbells'),
('Accessories', 'Accessories', 'Mats, rollers, balls, straps'),
('Functional', 'Functional Training', 'TRX, battle ropes, agility equipment');

-- Tipos de notificação
INSERT INTO notification_types (name, label, css_class) VALUES
('info', 'Information', 'info'),
('warning', 'Warning', 'warning'),
('success', 'Success', 'success'),
('error', 'Error', 'danger');

-- ============================================================
-- 4. INSERÇÃO DE UTILIZADORES (TRAINERS, ADMIN, MEMBROS)
-- ============================================================

-- Senha padrão: 12345678 (em texto claro – apenas para demonstração)
INSERT INTO users (name, username, email, password, role, is_admin, active) VALUES
-- Treinadores
('Ana Silva', 'anasilva', 'ana@fitpeak.com', '12345678', 'trainer', 0, 1),
('Bruno Costa', 'brunocosta', 'bruno@fitpeak.com', '12345678', 'trainer', 0, 1),
('Carla Mendes', 'carlamendes', 'carla@fitpeak.com', '12345678', 'trainer', 0, 1),
('Miguel Rocha', 'miguelrocha', 'miguel@fitpeak.com', '12345678', 'trainer', 0, 1),
('Inês Almeida', 'inesalmeida', 'ines@fitpeak.com', '12345678', 'trainer', 0, 1),
('Tiago Martins', 'tiagomartins', 'tiago@fitpeak.com', '12345678', 'trainer', 0, 1),
('Beatriz Ramos', 'beatrizramos', 'beatriz@fitpeak.com', '12345678', 'trainer', 0, 1),
('Duarte Oliveira', 'duarteoliveira', 'duarte@fitpeak.com', '12345678', 'trainer', 0, 1),
('Mariana Lopes', 'marianalopes', 'mariana@fitpeak.com', '12345678', 'trainer', 0, 1),
('Ricardo Nunes', 'ricardonunes', 'ricardo@fitpeak.com', '12345678', 'trainer', 0, 1),
('Catarina Gomes', 'catarinagomes', 'catarina@fitpeak.com', '12345678', 'trainer', 0, 1),
('André Ferreira', 'andreferreira', 'andre@fitpeak.com', '12345678', 'trainer', 0, 1),

-- Administrador
('Admin User', 'admin', 'admin@fitpeak.com', '12345678', 'admin', 1, 1),

-- Membros
('João Pereira', 'joaop', 'joao@email.com', '12345678', 'member', 0, 1),
('Maria Santos', 'marias', 'maria@email.com', '12345678', 'member', 0, 1),
('Rita Ferreira', 'ritaf', 'rita@email.com', '12345678', 'member', 0, 1),
('Carlos Andrade', 'carlosa', 'carlos@email.com', '12345678', 'member', 0, 1),
('Sofia Lima', 'sofial', 'sofia@email.com', '12345678', 'member', 0, 1);

-- ============================================================
-- 5. PERFIS DE TREINADOR
-- ============================================================

INSERT INTO trainer_profiles 
(user_id, bio, specializations, certifications, is_pt, pt_description, pt_available) 
VALUES
((SELECT id FROM users WHERE username = 'anasilva'),
 'Yoga & Pilates specialist with 10 years experience.',
 'Yoga, Pilates, Flexibility',
 'ACE CPT, RYT-200',
 0,
 NULL,
 0),

((SELECT id FROM users WHERE username = 'brunocosta'),
 'High-intensity training enthusiast and former athlete.',
 'HIIT, CrossFit, Strength',
 'NSCA-CSCS, CF-L2',
 0,
 NULL,
 0),

((SELECT id FROM users WHERE username = 'carlamendes'),
 'Certified spinning instructor and endurance coach.',
 'Spinning, Cycling, Cardio',
 'Spinning Certified, ACE',
 0,
 NULL,
 0),

((SELECT id FROM users WHERE username = 'miguelrocha'),
 'Strength coach focused on hypertrophy and performance.',
 'Strength, Hypertrophy, Functional Training',
 'ACE CPT',
 0,
 NULL,
 0),

((SELECT id FROM users WHERE username = 'inesalmeida'),
 'Personal trainer focused on weight loss, mobility and beginner-friendly training.',
 'Weight Loss, Mobility, Beginner Training',
 'ACE CPT',
 1,
 'Beginner-friendly PT sessions focused on fat loss, confidence and safe technique.',
 1),

((SELECT id FROM users WHERE username = 'tiagomartins'),
 'Strength and conditioning coach specialized in muscle gain and athletic performance.',
 'Strength, Hypertrophy, Performance',
 'NSCA-CSCS',
 1,
 'Strength-focused PT sessions for muscle gain, athletic performance and progressive overload.',
 1),

((SELECT id FROM users WHERE username = 'beatrizramos'),
 'Functional training coach focused on posture, core strength and injury prevention.',
 'Functional Training, Core, Injury Prevention',
 'NASM CPT',
 1,
 'Functional PT sessions focused on posture, core strength and injury prevention.',
 1),

((SELECT id FROM users WHERE username = 'duarteoliveira'),
 'Coach focused on CrossFit, metabolic conditioning and high-intensity workouts.',
 'CrossFit, HIIT, Conditioning',
 'CF-L1, ACE CPT',
 1,
 'Personal training focused on CrossFit, conditioning and high-intensity performance.',
 1),

((SELECT id FROM users WHERE username = 'marianalopes'),
 'Yoga and Pilates instructor focused on flexibility, breathing and body control.',
 'Yoga, Pilates, Flexibility',
 'RYT-200, Pilates Instructor',
 1,
 'PT sessions focused on flexibility, posture, breathing and body control.',
 1),

((SELECT id FROM users WHERE username = 'ricardonunes'),
 'Personal trainer specialized in strength training, fat loss and nutrition guidance.',
 'Strength, Fat Loss, Nutrition',
 'ACE CPT, Nutrition Coach',
 1,
 'Personal training focused on strength, fat loss and nutrition guidance.',
 1),

((SELECT id FROM users WHERE username = 'catarinagomes'),
 'Trainer focused on women fitness, glute training and full-body strength.',
 'Women Fitness, Glutes, Strength',
 'NASM CPT',
 1,
 'PT sessions focused on glute training, full-body strength and confidence.',
 1),

((SELECT id FROM users WHERE username = 'andreferreira'),
 'Boxing and conditioning coach focused on endurance, agility and coordination.',
 'Boxing, Cardio, Agility',
 'Boxing Coach, ACE',
 1,
 'Personal training focused on boxing, cardio, agility and endurance.',
 1);

-- ============================================================
-- 6. PERFIS DE MEMBRO
-- ============================================================

INSERT INTO member_profiles (user_id, membership_plan, plan_start, plan_end) VALUES
((SELECT id FROM users WHERE username = 'joaop'),   'basic',   date('now'), date('now', '+1 year')),
((SELECT id FROM users WHERE username = 'marias'),  'premium', date('now'), date('now', '+1 year')),
((SELECT id FROM users WHERE username = 'ritaf'),   'basic',   date('now'), date('now', '+1 year')),
((SELECT id FROM users WHERE username = 'carlosa'), 'vip',     date('now'), date('now', '+1 year')),
((SELECT id FROM users WHERE username = 'sofial'),  'premium', date('now'), date('now', '+1 year'));

-- ============================================================
-- 7. AULAS (CLASSES) – APENAS DADOS INTRÍNSECOS
-- ============================================================

INSERT INTO classes (name, description, class_type_id) VALUES
('Morning Yoga', 'Start your day with calm and flexibility', (SELECT id FROM class_types WHERE name='yoga')),
('Power HIIT', 'Burn calories and build strength', (SELECT id FROM class_types WHERE name='hiit')),
('Spinning Class', 'High-energy cycling for all levels', (SELECT id FROM class_types WHERE name='cycling')),
('Pilates Core', 'Strengthen your core with controlled movements', (SELECT id FROM class_types WHERE name='pilates')),
('CrossFit WOD', 'Workout of the day — push your limits', (SELECT id FROM class_types WHERE name='crossfit')),
('Evening Yoga', 'Wind down and restore with evening yoga', (SELECT id FROM class_types WHERE name='yoga')),
('Saturday Spin', 'Weekend ride to start your Saturday right', (SELECT id FROM class_types WHERE name='cycling')),
('Strength Basics', 'Build strength with proper technique and progressive overload', (SELECT id FROM class_types WHERE name='strength')),
('Boxing Conditioning', 'Improve endurance, speed and coordination with boxing drills', (SELECT id FROM class_types WHERE name='boxing')),
('Senior Fitness', 'Low-impact training focused on mobility, balance and strength', (SELECT id FROM class_types WHERE name='senior-fitness'));

-- ============================================================
-- 8. HORÁRIOS (CLASS_SCHEDULES) – CADA AULA PODE TER MÚLTIPLOS
-- ============================================================

INSERT INTO class_schedules (class_id, trainer_id, day_of_week, start_time, location, capacity, duration_min) VALUES
((SELECT id FROM classes WHERE name='Morning Yoga'),   (SELECT id FROM users WHERE username='anasilva'),   'Monday',    '07:00', 'Studio A',     15, 60),
((SELECT id FROM classes WHERE name='Power HIIT'),     (SELECT id FROM users WHERE username='brunocosta'), 'Tuesday',   '18:00', 'Main Floor',   20, 45),
((SELECT id FROM classes WHERE name='Spinning Class'), (SELECT id FROM users WHERE username='carlamendes'),'Wednesday', '19:00', 'Cycling Studio',18, 50),
((SELECT id FROM classes WHERE name='Pilates Core'),   (SELECT id FROM users WHERE username='anasilva'),   'Thursday',  '10:00', 'Studio B',     12, 55),
((SELECT id FROM classes WHERE name='CrossFit WOD'),   (SELECT id FROM users WHERE username='brunocosta'), 'Friday',    '17:30', 'Main Floor',   20, 60),
((SELECT id FROM classes WHERE name='Evening Yoga'),   (SELECT id FROM users WHERE username='anasilva'),   'Friday',    '20:00', 'Studio A',     15, 60),
((SELECT id FROM classes WHERE name='Saturday Spin'),  (SELECT id FROM users WHERE username='carlamendes'),'Saturday',  '09:00', 'Cycling Studio',18, 60),
((SELECT id FROM classes WHERE name='Strength Basics'),(SELECT id FROM users WHERE username='miguelrocha'),'Monday',    '18:30', 'Main Floor',   16, 60),
((SELECT id FROM classes WHERE name='Boxing Conditioning'),(SELECT id FROM users WHERE username='brunocosta'),'Wednesday','17:30', 'Boxing Area',   14, 50),
((SELECT id FROM classes WHERE name='Senior Fitness'), (SELECT id FROM users WHERE username='carlamendes'), 'Tuesday',   '10:00', 'Studio B',     12, 45);

-- (Opcional) Adicionar segundo horário para a mesma aula (ex: Yoga também à Quarta)
INSERT INTO class_schedules (class_id, trainer_id, day_of_week, start_time, location, capacity, duration_min)
SELECT id, (SELECT id FROM users WHERE username='anasilva'), 'Wednesday', '09:00', 'Studio A', 15, 60
FROM classes WHERE name='Morning Yoga';

-- ============================================================
-- 9. SESSÕES CONCRETAS (PARA A PRÓXIMA SEMANA)
-- ============================================================

-- Segunda-feira
INSERT INTO class_sessions (schedule_id, session_date)
SELECT s.id, date('now', 'weekday 1')
FROM class_schedules s WHERE s.day_of_week = 'Monday';

-- Terça-feira
INSERT INTO class_sessions (schedule_id, session_date)
SELECT s.id, date('now', 'weekday 2')
FROM class_schedules s WHERE s.day_of_week = 'Tuesday';

-- Quarta-feira
INSERT INTO class_sessions (schedule_id, session_date)
SELECT s.id, date('now', 'weekday 3')
FROM class_schedules s WHERE s.day_of_week = 'Wednesday';

-- Quinta-feira
INSERT INTO class_sessions (schedule_id, session_date)
SELECT s.id, date('now', 'weekday 4')
FROM class_schedules s WHERE s.day_of_week = 'Thursday';

-- Sexta-feira
INSERT INTO class_sessions (schedule_id, session_date)
SELECT s.id, date('now', 'weekday 5')
FROM class_schedules s WHERE s.day_of_week = 'Friday';

-- Sábado
INSERT INTO class_sessions (schedule_id, session_date)
SELECT s.id, date('now', 'weekday 6')
FROM class_schedules s WHERE s.day_of_week = 'Saturday';

-- Domingo (nenhum horário definido, mas se houver, descomente)
-- INSERT INTO class_sessions ... WHERE day_of_week = 'Sunday';

-- ============================================================
-- 10. INSCRIÇÕES (ENROLLMENTS) – EXEMPLOS PARA MEMBROS
-- ============================================================

-- Inscrições aleatórias para demonstração
INSERT INTO enrollments (member_id, session_id, status)
SELECT 
    (SELECT id FROM users WHERE username = 'joaop'),
    s.id,
    'enrolled'
FROM class_sessions s
WHERE s.session_date = date('now', 'weekday 1')  -- próxima segunda
LIMIT 1;

INSERT INTO enrollments (member_id, session_id, status)
SELECT 
    (SELECT id FROM users WHERE username = 'marias'),
    s.id,
    'enrolled'
FROM class_sessions s
WHERE s.session_date = date('now', 'weekday 2')  -- terça
LIMIT 1;

INSERT INTO enrollments (member_id, session_id, status)
SELECT 
    (SELECT id FROM users WHERE username = 'ritaf'),
    s.id,
    'enrolled'
FROM class_sessions s
WHERE s.session_date = date('now', 'weekday 3')  -- quarta
LIMIT 1;

-- ============================================================
-- 11. EQUIPAMENTO
-- ============================================================

INSERT INTO equipment (name, category_id, quantity, available, status, notes) VALUES
('Treadmill',          (SELECT id FROM equipment_categories WHERE name='Cardio'),       8, 6, 'available', ''),
('Stationary Bike',    (SELECT id FROM equipment_categories WHERE name='Cardio'),       6, 5, 'available', ''),
('Rowing Machine',     (SELECT id FROM equipment_categories WHERE name='Cardio'),       4, 0, 'unavailable', 'Motor reparação'),
('Elliptical',         (SELECT id FROM equipment_categories WHERE name='Cardio'),       4, 4, 'available', ''),
('Weight Bench',       (SELECT id FROM equipment_categories WHERE name='Strength'),    10, 8, 'available', ''),
('Squat Rack',         (SELECT id FROM equipment_categories WHERE name='Strength'),     4, 3, 'available', ''),
('Pull-up Bar',        (SELECT id FROM equipment_categories WHERE name='Strength'),     6, 6, 'available', ''),
('Dumbbell Set',       (SELECT id FROM equipment_categories WHERE name='Free Weights'),20,18, 'available', ''),
('Barbell Set',        (SELECT id FROM equipment_categories WHERE name='Free Weights'), 8, 7, 'available', ''),
('Kettlebell Set',     (SELECT id FROM equipment_categories WHERE name='Free Weights'),12,10, 'available', ''),
('Yoga Mat',           (SELECT id FROM equipment_categories WHERE name='Accessories'), 30,25, 'available', ''),
('Foam Roller',        (SELECT id FROM equipment_categories WHERE name='Accessories'), 15,12, 'available', ''),
('Battle Rope',        (SELECT id FROM equipment_categories WHERE name='Functional'),   4, 3, 'available', ''),
('TRX Suspension',     (SELECT id FROM equipment_categories WHERE name='Functional'),   6, 5, 'available', ''),
('Punching Bag',       (SELECT id FROM equipment_categories WHERE name='Cardio'),       3, 2, 'available', '');

-- ============================================================
-- 12. AVALIAÇÕES (REVIEWS) – EXEMPLOS
-- ============================================================

INSERT INTO reviews (member_id, session_id, rating, comment) VALUES
((SELECT id FROM users WHERE username='joaop'),
 (SELECT s.id FROM class_sessions s JOIN class_schedules sch ON s.schedule_id = sch.id WHERE sch.day_of_week='Monday' LIMIT 1),
 5, 'Excelente aula! A Ana é incrível.'),
((SELECT id FROM users WHERE username='marias'),
 (SELECT s.id FROM class_sessions s JOIN class_schedules sch ON s.schedule_id = sch.id WHERE sch.day_of_week='Tuesday' LIMIT 1),
 4, 'Treino muito intenso, adorei!'),
((SELECT id FROM users WHERE username='ritaf'),
 (SELECT s.id FROM class_sessions s JOIN class_schedules sch ON s.schedule_id = sch.id WHERE sch.day_of_week='Wednesday' LIMIT 1),
 5, 'A melhor aula de spinning da minha vida.');

-- ============================================================
-- 13. RESERVAS PESSOAIS (PERSONAL_BOOKINGS) – EXEMPLOS
-- ============================================================

INSERT INTO personal_bookings (member_id, trainer_id, booking_date, start_time, duration_min, status, notes) VALUES
((SELECT id FROM users WHERE username='carlosa'),
 (SELECT id FROM users WHERE username='miguelrocha'),
 date('now', '+2 days'),
 '15:00',
 60,
 'confirmed',
 'Quero focar em agachamento livre'),

((SELECT id FROM users WHERE username='sofial'),
 (SELECT id FROM users WHERE username='anasilva'),
 date('now', '+3 days'),
 '11:00',
 45,
 'pending',
 'Aula de yoga privada');

-- ============================================================
-- 14. NOTIFICAÇÕES – EXEMPLOS
-- ============================================================

INSERT INTO notifications (user_id, message, notification_type_id) VALUES
((SELECT id FROM users WHERE username='joaop'),
 'A sua inscrição para Morning Yoga foi confirmada.',
 (SELECT id FROM notification_types WHERE name='success')),

((SELECT id FROM users WHERE username='marias'),
 'Nova aula de HIIT disponível – inscreva-se já!',
 (SELECT id FROM notification_types WHERE name='info')),

((SELECT id FROM users WHERE username='ritaf'),
 'A sessão de Spinning de amanhã foi cancelada devido a manutenção.',
 (SELECT id FROM notification_types WHERE name='warning'));

-- ============================================================
-- FIM DO SCRIPT
-- ============================================================

-- Verificação rápida (opcional)
SELECT 'Database created successfully!' AS status;