-- Creación e inicialización de la base de datos
CREATE DATABASE IF NOT EXISTS farmacia;
USE farmacia;

-- 1. TABLA PROVEEDOR
CREATE TABLE proveedor (
    id_proveedor INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(150) NOT NULL,
    telefono VARCHAR(15),
    correo VARCHAR(100),
    direccion VARCHAR(255),
    estado TINYINT(1) NOT NULL DEFAULT 1
);

-- 2. TABLA PRODUCTO
CREATE TABLE producto (
    codigo INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(150) NOT NULL,
    tipo_medicamento ENUM('Patente', 'Genérico', 'Controlado', 'OTC / Libre Venta', 'Material Cuidado Personal') NOT NULL DEFAULT 'OTC / Libre Venta',
    laboratorio VARCHAR(100) NOT NULL,
    lote VARCHAR(50),
    fecha_ingreso DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    fecha_caducidad DATE NOT NULL,
    existencia INT NOT NULL DEFAULT 0 CHECK (existencia >= 0),
    precio_venta DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    id_proveedor INT,
    estado TINYINT(1) NOT NULL DEFAULT 1,
    CONSTRAINT fk_producto_proveedor FOREIGN KEY (id_proveedor) 
        REFERENCES proveedor(id_proveedor) ON DELETE SET NULL ON UPDATE CASCADE,
    imagen VARCHAR(255) NOT NULL
);

-- 3. TABLA CLIENTE
CREATE TABLE cliente (
    codigo INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(150) NOT NULL,
    telefono VARCHAR(15),
    rfc VARCHAR(13),
    direccion VARCHAR(255),
    estado TINYINT(1) NOT NULL DEFAULT 1
);

-- 4. TABLA EMPLEADO
CREATE TABLE empleado (
    id_empleado INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL,
    apellido_paterno VARCHAR(50) NOT NULL,
    apellido_materno VARCHAR(50),
    clave VARCHAR(255) NOT NULL, -- Password Hash (BCrypt/Argon2)
    correo VARCHAR(100) NOT NULL UNIQUE,
    telefono VARCHAR(15),
    rol ENUM('Administrador', 'Cajero', 'Almacenista') NOT NULL,
    estado TINYINT(1) NOT NULL DEFAULT 1 -- 1: Activo, 0: Inactivo (Baja lógica)
);

-- 5. TABLA VENTA (Encabezado de Transacción)
CREATE TABLE venta (
    numero_nota INT AUTO_INCREMENT PRIMARY KEY,
    fecha DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    metodo_pago ENUM('Efectivo', 'Tarjeta', 'Transferencia') NOT NULL DEFAULT 'Efectivo',
    total_pagar DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    codigo_cliente INT NULL, -- NULL permite ventas a "Público en General"
    id_empleado INT NOT NULL,
    CONSTRAINT fk_venta_cliente FOREIGN KEY (codigo_cliente) 
        REFERENCES cliente(codigo) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT fk_venta_empleado FOREIGN KEY (id_empleado) 
        REFERENCES empleado(id_empleado) ON UPDATE CASCADE
);

-- 6. TABLA DETALLE_VENTA
CREATE TABLE detalle_venta (
    numero_nota INT NOT NULL,
    codigo_producto INT NOT NULL,
    cantidad_salida INT NOT NULL CHECK (cantidad_salida > 0),
    precio_venta DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    PRIMARY KEY (numero_nota, codigo_producto),
    CONSTRAINT fk_detalle_venta FOREIGN KEY (numero_nota) 
        REFERENCES venta(numero_nota) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_detalle_producto FOREIGN KEY (codigo_producto) 
        REFERENCES producto(codigo) ON UPDATE CASCADE
);

-- 7. TABLA ABASTECIMIENTO (Entradas de Almacén)
CREATE TABLE abastecimiento (
    id_abastecimiento INT AUTO_INCREMENT PRIMARY KEY,
    fecha DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    cantidad_ingresada INT NOT NULL CHECK (cantidad_ingresada > 0),
    codigo_producto INT NOT NULL,
    id_empleado INT NOT NULL,
    id_proveedor INT NULL,
    CONSTRAINT fk_abast_producto FOREIGN KEY (codigo_producto) 
        REFERENCES producto(codigo) ON UPDATE CASCADE,
    CONSTRAINT fk_abast_empleado FOREIGN KEY (id_empleado) 
        REFERENCES empleado(id_empleado) ON UPDATE CASCADE,
    CONSTRAINT fk_abast_proveedor FOREIGN KEY (id_proveedor) 
        REFERENCES proveedor(id_proveedor) ON DELETE SET NULL ON UPDATE CASCADE
);

-- Inserciones para la tabla 'empleado'
INSERT INTO empleado (id_empleado, nombre, apellido_paterno, apellido_materno, clave, correo, telefono, rol, estado) VALUES
(1, 'Carlos', 'Vargas', 'Pérez', 'admin123', 'admin@farmavive.admin.com', '3324358416', 'Administrador', 1),
(2, 'Juan', 'Pérez', 'Lopez', 'almacen123', 'juan@almacen.farmavive.com', '3333333333', 'Almacenista', 1),
(3, 'Ana', 'Vargas', 'Domínguez', 'cajero123', 'ana@cajero.farmavive.com', '3333333333', 'Cajero', 1);

-- Inserciones para la tabla 'producto'
INSERT INTO producto (codigo, nombre, tipo_medicamento, laboratorio, lote, fecha_ingreso, fecha_caducidad, existencia, precio_venta, id_proveedor, estado, imagen) VALUES
(1, 'Paracetamol 500mg (10 Tabletas)', 'Genérico', 'Laboratorios Pisa', 'L-001', '2026-11-05 08:05:21', '2028-12-31', 100, 25.50, NULL, 1, 'placeholderFotoUsuario.png'),
(2, 'Ibuprofeno 400mg (20 Cápsulas)', 'OTC / Libre Venta', 'Bayer', 'L-002', '2026-11-05 08:05:21', '2027-10-15', 75, 48.00, NULL, 1, 'placeholderFotoUsuario.png'),
(3, 'Amoxicilina 500mg (12 Cápsulas)', 'Patente', 'Pfizer', 'L-003', '2026-11-05 08:05:21', '2027-08-20', 50, 125.00, NULL, 1, 'placeholderFotoUsuario.png'),
(4, 'Omeprazol 20mg (14 Cápsulas)', 'Genérico', 'Genomma Lab', 'L-004', '2026-11-05 08:05:21', '2028-05-10', 120, 35.00, NULL, 1, 'placeholderFotoUsuario.png'),
(5, 'Loratadina 10mg (10 Tabletas)', 'OTC / Libre Venta', 'Mylan', 'L-005', '2026-11-05 08:05:21', '2028-01-30', 90, 30.00, NULL, 1, 'placeholderFotoUsuario.png'),
(6, 'Clonazepam 2mg (30 Tabletas)', 'Controlado', 'Roche', 'L-006', '2026-11-05 08:05:21', '2027-06-18', 20, 210.50, NULL, 1, 'placeholderFotoUsuario.png'),
(7, 'Alcohol Etílico 70° (500ml)', 'Material Cuidado Personal', 'Jaloma', 'L-007', '2026-11-05 08:05:21', '2029-03-25', 150, 42.00, NULL, 1, 'placeholderFotoUsuario.png');