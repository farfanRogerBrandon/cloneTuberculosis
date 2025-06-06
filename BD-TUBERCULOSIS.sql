DROP DATABASE bdtuberculosis;

CREATE DATABASE `bdtuberculosis`;
USE `bdtuberculosis`;

INSERT INTO departamento (nombre) VALUES 
('Chuquisaca'),
('La Paz'),
('Cochabamba'),
('Oruro'),
('Potosí'),
('Tarija'),
('Santa Cruz'),
('Beni'),
('Pando');

-- Provincias de Chuquisaca (id = 1)
INSERT INTO provincia (nombre, idDepartamento) VALUES
('Oropeza', 1),
('Yamparáez', 1),
('Zudáñez', 1),
('Tomina', 1),
('Hernando Siles', 1),
('Belisario Boeto', 1),
('Nor Cinti', 1),
('Sud Cinti', 1);

-- Provincias de La Paz (id = 2)
INSERT INTO provincia (nombre, idDepartamento) VALUES
('Murillo', 2),
('Pacajes', 2),
('Ingavi', 2),
('Los Andes', 2),
('Manco Kapac', 2),
('Camacho', 2),
('Muñecas', 2),
('Larecaja', 2);

-- Provincias de Cochabamba (id = 3)
INSERT INTO provincia (nombre, idDepartamento) VALUES
('Cercado', 3),
('Chapare', 3),
('Quillacollo', 3),
('Capinota', 3),
('Esteban Arce', 3),
('Germán Jordán', 3),
('Mizque', 3),
('Campero', 3);

-- Provincias de Oruro (id = 4)
INSERT INTO provincia (nombre, idDepartamento) VALUES
('Cercado', 4),
('Sajama', 4),
('Carangas', 4),
('Mejillones', 4),
('Nor Carangas', 4),
('Ladislao Cabrera', 4);

-- Provincias de Potosí (id = 5)
INSERT INTO provincia (nombre, idDepartamento) VALUES
('Tomás Frías', 5),
('Nor Chichas', 5),
('Sud Chichas', 5),
('Nor Lípez', 5),
('Sud Lípez', 5),
('Rafael Bustillo', 5);

-- Provincias de Tarija (id = 6)
INSERT INTO provincia (nombre, idDepartamento) VALUES
('Cercado', 6),
('Avilez', 6),
('Gran Chaco', 6),
('Arce', 6),
('O’Connor', 6);

-- Provincias de Santa Cruz (id = 7)
INSERT INTO provincia (nombre, idDepartamento) VALUES
('Andrés Ibáñez', 7),
('Chiquitos', 7),
('Cordillera', 7),
('Ichilo', 7),
('Sara', 7),
('Warnes', 7),
('Vallegrande', 7);

-- Provincias de Beni (id = 8)
INSERT INTO provincia (nombre, idDepartamento) VALUES
('Cercado', 8),
('Iténez', 8),
('Mamoré', 8),
('Moxos', 8),
('Yacuma', 8),
('Vaca Díez', 8);

-- Provincias de Pando (id = 9)
INSERT INTO provincia (nombre, idDepartamento) VALUES
('Madre de Dios', 9),
('Manuripi', 9),
('Abuná', 9);

CREATE TABLE IF NOT EXISTS `departamento` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(11) NOT NULL,
  PRIMARY KEY (`id`)
);

CREATE TABLE IF NOT EXISTS `provincia` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(20) NOT NULL,
  `idDepartamento` INT NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `idDepartamento` (`idDepartamento` ASC),
  CONSTRAINT `provincia_ibfk_1`
    FOREIGN KEY (`idDepartamento`)
    REFERENCES `departamento` (`id`)
);

CREATE TABLE IF NOT EXISTS `establecimiento` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `Abreviacion` VARCHAR(4) NOT NULL,
  `nombre` VARCHAR(50) NOT NULL,
  `telefono` VARCHAR(8) NULL,
  `idProvincia` INT NOT NULL,
  `estado` CHAR(1) NOT NULL DEFAULT '1',
  `fechaRegistro` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fechaActualizacion` DATETIME NULL DEFAULT NULL,
  `registradoPor` INT NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `idProvincia` (`idProvincia` ASC),
  CONSTRAINT `establecimiento_ibfk_1`
    FOREIGN KEY (`idProvincia`)
    REFERENCES `provincia` (`id`)
);

CREATE TABLE IF NOT EXISTS `paciente` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `ci` VARCHAR(12) NOT NULL,
  `nombres` VARCHAR(70) NOT NULL,
  `primerApellido` VARCHAR(70) NOT NULL,
  `segundoApellido` VARCHAR(70) NULL DEFAULT NULL,
  `celular` VARCHAR(8) NOT NULL,
  `sexo` CHAR(1) NOT NULL,
  `fechaNacimiento` DATE NOT NULL,
  `nombreUsuario` VARCHAR(20) NOT NULL,
  `idEstablecimiento` INT NOT NULL,
  `estado` CHAR(1) NOT NULL DEFAULT '1',
  `fechaRegistro` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fechaActualizacion` DATETIME NULL DEFAULT NULL,
  `registradoPor` INT NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `idEstablecimiento` (`idEstablecimiento` ASC),
  CONSTRAINT `paciente_ibfk_1`
    FOREIGN KEY (`idEstablecimiento`)
    REFERENCES `establecimiento` (`id`)
);

CREATE TABLE IF NOT EXISTS `historialMedico` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `descripcion` TEXT NOT NULL,
  `imagen` VARCHAR(300) NOT NULL,
  `Origen` VARCHAR(70) NOT NULL,
  `Destino` VARCHAR(70) NOT NULL,
  `idPaciente` INT NOT NULL,
  `idEstablecimiento` INT NOT NULL,
  `estado` CHAR(1) NOT NULL DEFAULT '1',
  `fechaRegistro` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fechaActualizacion` DATETIME NULL DEFAULT NULL,
  `registradoPor` INT NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `idPaciente` (`idPaciente` ASC),
  INDEX `idEstablecimiento` (`idEstablecimiento` ASC),
  CONSTRAINT `historialmedico_ibfk_1`
    FOREIGN KEY (`idPaciente`)
    REFERENCES `paciente` (`id`),
  CONSTRAINT `historialmedico_ibfk_2`
    FOREIGN KEY (`idEstablecimiento`)
    REFERENCES `establecimiento` (`id`)
);

CREATE TABLE IF NOT EXISTS `empleado` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `codigoEmpleado` CHAR(6) NOT NULL, -- Solo 6 números
  `nombreUsuario` VARCHAR(35) NOT NULL,
  `password` VARCHAR(100) NOT NULL,
  `idEstablecimiento` INT NOT NULL,
  `rol` VARCHAR(13) NOT NULL,
  `fechaRegistro` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fechaActualizacion` DATETIME NULL DEFAULT NULL,
  `registradoPor` INT NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE (`codigoEmpleado`), -- Asegura unicidad
  INDEX `idEstablecimiento` (`idEstablecimiento` ASC),
  CONSTRAINT `empleados_ibfk_1`
    FOREIGN KEY (`idEstablecimiento`)
    REFERENCES `establecimiento` (`id`)
);

CREATE TABLE IF NOT EXISTS `dosis` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `idPaciente` INT NOT NULL,
  `nroDosis` INT NOT NULL,
  `rutaVideo` VARCHAR(300) NULL,
  `fechaGrabacion` DATETIME NULL,
  `descripcion` TEXT NULL,
  `estado` CHAR(1) NOT NULL DEFAULT '1',
  `registradoPor` INT NOT NULL,
  `fechaRegistro` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fechaActualizacion` DATETIME NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `idPaciente` (`idPaciente` ASC),
  INDEX `registradoPor` (`registradoPor` ASC),
  CONSTRAINT `dosis_ibfk_1`
    FOREIGN KEY (`idPaciente`)
    REFERENCES `paciente` (`id`),
  CONSTRAINT `dosis_ibfk_2`
    FOREIGN KEY (`registradoPor`)
    REFERENCES `empleado` (`id`)
);

CREATE TABLE IF NOT EXISTS `notificacion` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `id_usuario` INT NOT NULL,
  `tipo_usuario` CHAR(1) NOT NULL, 
  `titulo` VARCHAR(50) NOT NULL,
  `mensaje` VARCHAR(75) NOT NULL,
  `leido_en` TIMESTAMP NULL DEFAULT NULL,
  `creado_en` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `actualizado_en` DATETIME NULL DEFAULT NULL,
  `idDosis` INT NULL,
  PRIMARY KEY (`id`),
  INDEX `id_usuario` (`id_usuario` ASC),
  INDEX `idDosis` (`idDosis` ASC),
  CONSTRAINT `notificacion_ibfk_1`
    FOREIGN KEY (`idDosis`)
    REFERENCES `dosis` (`id`)
    ON DELETE SET NULL
);

INSERT INTO empleado (
  codigoEmpleado,
  nombreUsuario,
  password,
  idEstablecimiento,
  rol,
  registradoPor
) VALUES (
  '123456',
  'adminuser',
  '$2b$10$hG1Xx3YoPi8XwD9X9J.yqOqRmChT9gK2vnnLbZijgR8H2/EmOX1ey',
  1, 
  'ADMINISTRADOR',
  1 
);

