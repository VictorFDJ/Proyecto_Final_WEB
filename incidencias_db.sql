CREATE DATABASE IF NOT EXISTS incidencias_db DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE incidencias_db;

CREATE TABLE usuarios (
  id INT AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(100) UNIQUE NOT NULL,
  password VARCHAR(255),
  rol ENUM('reportero', 'validador') NOT NULL
) ENGINE=InnoDB;

CREATE TABLE tipos_incidencias (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(50) NOT NULL
) ENGINE=InnoDB;

CREATE TABLE municipios (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(100) NOT NULL
) ENGINE=InnoDB;

CREATE TABLE barrios (
  id INT AUTO_INCREMENT PRIMARY KEY,
  municipio_id INT NOT NULL,
  nombre VARCHAR(100) NOT NULL,
  FOREIGN KEY (municipio_id) REFERENCES municipios(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE incidencias (
  id INT AUTO_INCREMENT PRIMARY KEY,
  titulo VARCHAR(255) NOT NULL,
  descripcion TEXT NOT NULL,
  provincia VARCHAR(100) NOT NULL,
  municipio VARCHAR(100) NOT NULL,
  barrio VARCHAR(100) NOT NULL,
  latitud DECIMAL(10,8) NOT NULL,
  longitud DECIMAL(11,8) NOT NULL,
  fecha DATE NOT NULL,
  muertos INT DEFAULT 0,
  heridos INT DEFAULT 0,
  perdida DECIMAL(15,2) DEFAULT 0.00,
  link_redes VARCHAR(255),
  foto VARCHAR(255),
  estado ENUM('pendiente', 'aprobado', 'rechazado') DEFAULT 'pendiente',
  reportero_id INT NOT NULL,
  FOREIGN KEY (reportero_id) REFERENCES usuarios(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE incidencia_tipos (
  incidencia_id INT NOT NULL,
  tipo_id INT NOT NULL,
  PRIMARY KEY (incidencia_id, tipo_id),
  FOREIGN KEY (incidencia_id) REFERENCES incidencias(id) ON DELETE CASCADE,
  FOREIGN KEY (tipo_id) REFERENCES tipos_incidencias(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE comentarios (
  id INT AUTO_INCREMENT PRIMARY KEY,
  incidencia_id INT NOT NULL,
  usuario_id INT NOT NULL,
  comentario TEXT NOT NULL,
  fecha DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (incidencia_id) REFERENCES incidencias(id) ON DELETE CASCADE,
  FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE correcciones (
  id INT AUTO_INCREMENT PRIMARY KEY,
  incidencia_id INT NOT NULL,
  usuario_id INT NOT NULL,
  campo VARCHAR(50) NOT NULL,
  nuevo_valor TEXT NOT NULL,
  estado ENUM('pendiente', 'aprobado', 'rechazado') DEFAULT 'pendiente',
  FOREIGN KEY (incidencia_id) REFERENCES incidencias(id) ON DELETE CASCADE,
  FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

INSERT INTO municipios (nombre) VALUES 
('Distrito Nacional'), 
('Santo Domingo Este'), 
('Santiago de los Caballeros');

INSERT INTO barrios (municipio_id, nombre) VALUES 
(1, 'Zona Colonial'), 
(1, 'Piantini'), 
(3, 'Los Jardines');

INSERT INTO tipos_incidencias (nombre) VALUES 
('Accidente'), 
('Pelea'), 
('Robo'), 
('Desastre');

INSERT INTO usuarios (email, password, rol) VALUES 
('validador@example.com', '$2y$10$K.7uOa8b3fWj9zL6q5fL1eOaWj3fG7b8z9yL4q2w1r0t5u3p', 'validador');

INSERT INTO usuarios (email, rol) VALUES 
('reportero@example.com', 'reportero');

SELECT id, email, password, rol 
FROM usuarios 
WHERE email = 'validador@example.com';

UPDATE usuarios 
SET password = MD5('123456') 
WHERE email = 'validador@example.com';

SELECT id, titulo, provincia, municipio, barrio, fecha, estado, reportero_id 
FROM incidencias;
