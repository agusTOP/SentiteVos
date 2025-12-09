-- Crear base de datos
CREATE DATABASE IF NOT EXISTS sentitevos CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE sentitevos;

-- Tabla de usuarios
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    telefono VARCHAR(20),
    -- Verificación de email
    email_verified TINYINT(1) NOT NULL DEFAULT 0,
    email_verify_token VARCHAR(64) DEFAULT NULL,
    email_verified_at DATETIME DEFAULT NULL,
    last_verification_sent_at DATETIME DEFAULT NULL,
    -- Recuperación de contraseña
    password_reset_token VARCHAR(64) DEFAULT NULL,
    password_reset_expires DATETIME DEFAULT NULL,
    -- Rol del usuario
    rol ENUM('admin','cliente') DEFAULT 'cliente',
    fecha_registro DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla de reservas
CREATE TABLE IF NOT EXISTS reservas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    servicio VARCHAR(100) NOT NULL,
    fecha DATE NOT NULL,
    hora TIME NOT NULL,
    estado ENUM('pendiente', 'confirmada', 'cancelada') DEFAULT 'pendiente',
    notas TEXT,
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Usuario de prueba (password: "123456")
-- Puedes borrarlo después de crear tu primer usuario real
INSERT INTO usuarios (nombre, email, password) VALUES 
('Usuario Prueba', 'test@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

