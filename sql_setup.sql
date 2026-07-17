-- ============================================================
-- SCRIPT DE BASE DE DATOS - Lab 2FA
-- Desarrollo de Software VII - UTP
-- ============================================================

-- 1. Crear la base de datos
CREATE DATABASE IF NOT EXISTS company_info
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE company_info;

-- 2. Tabla de usuarios
CREATE TABLE IF NOT EXISTS `usuarios` (
  `id`          INT          NOT NULL AUTO_INCREMENT,
  `Nombre`      VARCHAR(255) NOT NULL,
  `Apellido`    VARCHAR(255) NOT NULL,
  `Sexo`        VARCHAR(10)  NOT NULL,
  `Usuario`     VARCHAR(255) NOT NULL UNIQUE,
  `Correo`      VARCHAR(255) NOT NULL UNIQUE,
  `HashMagic`   VARCHAR(255) NOT NULL,
  `secret_2fa`  VARCHAR(255) NULL,
  `FechaSistema` DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Tabla de intentos de login (auditoría)
CREATE TABLE IF NOT EXISTS `intentos_login` (
  `id`                INT          NOT NULL AUTO_INCREMENT,
  `Usuario`           VARCHAR(255) NOT NULL,
  `ipRemoto`          VARCHAR(255) NOT NULL,
  `timestamp`         DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `deteccion_anomalia` TINYINT(1)  NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Tabla de trazabilidad
CREATE TABLE IF NOT EXISTS `trazabilidad_acciones` (
  `id`             INT          NOT NULL AUTO_INCREMENT,
  `Tabla`          VARCHAR(100) NOT NULL,
  `Acciones`       VARCHAR(50)  NOT NULL,
  `CodigoRegistro` INT          NOT NULL,
  `Usuario`        VARCHAR(255) NOT NULL,
  `FechaSistema`   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- CREACIÓN DE USUARIO CON PRIVILEGIOS MÍNIMOS (no superusuario)
-- ============================================================

-- Eliminar el usuario si ya existe (para re-ejecución limpia)
DROP USER IF EXISTS 'app_lab2fa'@'localhost';

-- Crear usuario con contraseña segura
CREATE USER 'app_lab2fa'@'localhost' IDENTIFIED BY 'Lab2FA_2026#Secure';

-- Otorgar SOLO los privilegios necesarios (principio de mínimo privilegio)
-- NO se otorga SUPER, GRANT OPTION, FILE, ni privilegios globales
GRANT SELECT, INSERT, UPDATE ON company_info.usuarios            TO 'app_lab2fa'@'localhost';
GRANT SELECT, INSERT         ON company_info.intentos_login      TO 'app_lab2fa'@'localhost';
GRANT SELECT, INSERT         ON company_info.trazabilidad_acciones TO 'app_lab2fa'@'localhost';

-- Aplicar los cambios
FLUSH PRIVILEGES;

-- ============================================================
-- MOSTRAR LOS PRIVILEGIOS DEL USUARIO CREADO
-- (ejecutar esto para verificar en pantalla)
-- ============================================================
SHOW GRANTS FOR 'app_lab2fa'@'localhost';
