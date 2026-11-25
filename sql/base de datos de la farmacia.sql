CREATE DATABASE IF NOT EXISTS farmacia CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE farmacia;

-- 1. Tabla proveedor (se crea primero porque producto y abastecimiento dependen de ella)
CREATE TABLE proveedor (
    id_proveedor INT(11) NOT NULL AUTO_INCREMENT,
    nombre VARCHAR(150) NOT NULL,
    telefono VARCHAR(15) NULL DEFAULT NULL,
    correo VARCHAR(100) NULL DEFAULT NULL,
    direccion VARCHAR(255) NULL DEFAULT NULL,
    estado TINYINT(1) NOT NULL DEFAULT 1,
    PRIMARY KEY (id_proveedor)
) ENGINE=InnoDB;

-- 2. Tabla cliente (se crea antes de venta)
CREATE TABLE cliente (
    codigo INT(11) NOT NULL AUTO_INCREMENT,
    nombre VARCHAR(150) NOT NULL,
    telefono VARCHAR(15) NULL DEFAULT NULL,
    rfc VARCHAR(13) NULL DEFAULT NULL,
    direccion VARCHAR(255) NULL DEFAULT NULL,
    estado TINYINT(1) NOT NULL DEFAULT 1,
    PRIMARY KEY (codigo)
) ENGINE=InnoDB;

-- 3. Tabla empleado (se crea antes de venta y abastecimiento)
CREATE TABLE empleado (
    id_empleado INT(11) NOT NULL AUTO_INCREMENT,
    nombre VARCHAR(50) NOT NULL,
    apellido_paterno VARCHAR(50) NOT NULL,
    apellido_materno VARCHAR(50) NULL DEFAULT NULL,
    clave VARCHAR(255) NOT NULL,
    correo VARCHAR(100) NOT NULL,
    telefono VARCHAR(15) NULL DEFAULT NULL,
    rol ENUM('Administrador','Cajero','Almacenista') NOT NULL,
    estado TINYINT(1) NOT NULL DEFAULT 1,
    foto VARCHAR(255) NULL DEFAULT 'fotoDefectoEmpleado.png',
    PRIMARY KEY (id_empleado),
    UNIQUE KEY uk_empleado_correo (correo)
) ENGINE=InnoDB;

-- 4. Tabla producto (depende de proveedor)
CREATE TABLE producto (
    codigo INT(11) NOT NULL AUTO_INCREMENT,
    nombre VARCHAR(150) NOT NULL,
    tipo_medicamento ENUM('Patente','Genérico','Controlado','OTC / Libre Venta','Material Cuidado Personal') NOT NULL DEFAULT 'OTC / Libre Venta',
    laboratorio VARCHAR(100) NOT NULL,
    lote VARCHAR(50) NULL DEFAULT NULL,
    fecha_ingreso DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP(),
    fecha_caducidad DATE NOT NULL,
    existencia INT(11) NOT NULL DEFAULT 0,
    precio_venta DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    id_proveedor INT(11) NULL DEFAULT NULL,
    estado TINYINT(1) NOT NULL DEFAULT 1,
    imagen VARCHAR(255) NOT NULL DEFAULT 'imagenMedicinaDefecto.jpg',
    PRIMARY KEY (codigo),
    CONSTRAINT fk_producto_proveedor FOREIGN KEY (id_proveedor) REFERENCES proveedor(id_proveedor)
) ENGINE=InnoDB;

-- 5. Tabla abastecimiento (depende de producto, empleado y proveedor)
CREATE TABLE abastecimiento (
    id_abastecimiento INT(11) NOT NULL AUTO_INCREMENT,
    fecha DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP(),
    cantidad_ingresada INT(11) NOT NULL,
    codigo_producto INT(11) NOT NULL,
    id_empleado INT(11) NOT NULL,
    id_proveedor INT(11) NULL DEFAULT NULL,
    PRIMARY KEY (id_abastecimiento),
    CONSTRAINT fk_abastecimiento_producto FOREIGN KEY (codigo_producto) REFERENCES producto(codigo),
    CONSTRAINT fk_abastecimiento_empleado FOREIGN KEY (id_empleado) REFERENCES empleado(id_empleado),
    CONSTRAINT fk_abastecimiento_proveedor FOREIGN KEY (id_proveedor) REFERENCES proveedor(id_proveedor)
) ENGINE=InnoDB;

-- 6. Tabla venta (depende de cliente y empleado)
CREATE TABLE venta (
    numero_nota INT(11) NOT NULL AUTO_INCREMENT,
    fecha DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP(),
    total_pagar DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    codigo_cliente INT(11) NULL DEFAULT NULL,
    id_empleado INT(11) NOT NULL,
    PRIMARY KEY (numero_nota),
    CONSTRAINT fk_venta_cliente FOREIGN KEY (codigo_cliente) REFERENCES cliente(codigo),
    CONSTRAINT fk_venta_empleado FOREIGN KEY (id_empleado) REFERENCES empleado(id_empleado)
) ENGINE=InnoDB;

-- 7. Tabla detalle_venta (clave primaria compuesta y depende de venta y producto)
CREATE TABLE detalle_venta (
    numero_nota INT(11) NOT NULL,
    codigo_producto INT(11) NOT NULL,
    cantidad_salida INT(11) NOT NULL,
    precio_venta DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    PRIMARY KEY (numero_nota, codigo_producto),
    CONSTRAINT fk_detalle_venta_nota FOREIGN KEY (numero_nota) REFERENCES venta(numero_nota),
    CONSTRAINT fk_detalle_venta_producto FOREIGN KEY (codigo_producto) REFERENCES producto(codigo)
) ENGINE=InnoDB;
