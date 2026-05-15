-- Sucursales
CREATE TABLE IF NOT EXISTS branches (
    id INT PRIMARY KEY AUTO_INCREMENT,
    branch_code VARCHAR(10) UNIQUE,
    name VARCHAR(100),
    latitude DECIMAL(10,8),
    longitude DECIMAL(11,8),
    ip_address VARCHAR(45),
    radius_meters INT DEFAULT 50
);

-- Empleados
CREATE TABLE IF NOT EXISTS employees (
    id INT PRIMARY KEY AUTO_INCREMENT,
    employee_id VARCHAR(20) UNIQUE,
    full_name VARCHAR(100),
    email VARCHAR(100),
    phone VARCHAR(20),
    position VARCHAR(50),
    ci VARCHAR(15) UNIQUE,
    birth_date DATE,
    pin_hash VARCHAR(255),          
    jwt_token VARCHAR(500)          
);

-- Asistencias (registros)
CREATE TABLE IF NOT EXISTS attendances (
    id INT PRIMARY KEY AUTO_INCREMENT,
    employee_id VARCHAR(20),
    branch_code VARCHAR(10),
    punch_type ENUM('in', 'out'),
    device_timestamp DATETIME,      
    server_timestamp DATETIME,      
    latitude DECIMAL(10,8),
    longitude DECIMAL(11,8),
    status ENUM('pending', 'synced', 'rejected') DEFAULT 'pending',
    synced_at DATETIME NULL,
    FOREIGN KEY (employee_id) REFERENCES employees(employee_id),
    INDEX idx_employee (employee_id),
    INDEX idx_branch (branch_code),
    INDEX idx_server_time (server_timestamp)
);

-- Insertar datos semilla para pruebas
INSERT IGNORE INTO branches (branch_code, name, latitude, longitude, radius_meters) VALUES 
('SUC-01', 'Sucursal Central', 10.123456, -67.123456, 500000),
('SUC-02', 'Sucursal Norte', 10.223456, -67.223456, 500000);

-- PIN de ambos empleados: 1234
INSERT IGNORE INTO employees (employee_id, full_name, pin_hash) VALUES 
('EMP-001', 'Juan Perez',  '$2y$10$QeLpZQnnS7wNasVW/HOWtOgStP6oa6jKYhPQgVZu8j4nVrcqqb4Zm'),
('EMP-002', 'Maria Lopez', '$2y$10$QeLpZQnnS7wNasVW/HOWtOgStP6oa6jKYhPQgVZu8j4nVrcqqb4Zm');
