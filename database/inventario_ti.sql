-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost:3306
-- Tiempo de generación: 17-05-2026 a las 01:14:30
-- Versión del servidor: 8.4.3
-- Versión de PHP: 8.3.16

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `inventario_ti`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `activo`
--

CREATE TABLE `activo` (
  `id_activo` int NOT NULL,
  `id_categoria` int NOT NULL,
  `id_modelo` int NOT NULL,
  `id_ubicacion_actual` int DEFAULT NULL,
  `id_responsable_actual` int DEFAULT NULL,
  `id_condicion_actual` int NOT NULL,
  `id_situacion_actual` int NOT NULL,
  `codigo_interno` varchar(50) NOT NULL,
  `codigo_patrimonial` varchar(100) NOT NULL,
  `numero_serie` varchar(150) DEFAULT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `fecha_adquisicion` date DEFAULT NULL,
  `valor_compra` decimal(12,2) DEFAULT NULL,
  `proveedor` varchar(150) DEFAULT NULL,
  `garantia_inicio` date DEFAULT NULL,
  `garantia_fin` date DEFAULT NULL,
  `observaciones` text,
  `qr_token` varchar(255) DEFAULT NULL,
  `creado_por` int DEFAULT NULL,
  `actualizado_por` int DEFAULT NULL,
  `creado_en` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `actualizado_en` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `adjuntos_activo`
--

CREATE TABLE `adjuntos_activo` (
  `id_adjunto` int NOT NULL,
  `id_activo` int NOT NULL,
  `nombre_original` varchar(255) NOT NULL,
  `nombre_guardado` varchar(255) NOT NULL,
  `ruta` varchar(255) NOT NULL,
  `mime_type` varchar(100) NOT NULL,
  `tamano_bytes` bigint NOT NULL,
  `tipo_adjunto` varchar(50) NOT NULL,
  `subido_por` int NOT NULL,
  `creado_en` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `auditoria`
--

CREATE TABLE `auditoria` (
  `id_auditoria` bigint NOT NULL,
  `id_usuario` int DEFAULT NULL,
  `modulo` varchar(100) NOT NULL,
  `accion` varchar(100) NOT NULL,
  `tabla_afectada` varchar(100) NOT NULL,
  `id_registro_afectado` varchar(100) NOT NULL,
  `valor_anterior_json` json DEFAULT NULL,
  `valor_nuevo_json` json DEFAULT NULL,
  `direccion_ip` varchar(45) DEFAULT NULL,
  `agente_usuario` varchar(255) DEFAULT NULL,
  `creado_en` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `bajas_activo`
--

CREATE TABLE `bajas_activo` (
  `id_baja` int NOT NULL,
  `id_activo` int NOT NULL,
  `tipo_baja` varchar(50) NOT NULL,
  `motivo` text NOT NULL,
  `documento_sustento` varchar(255) DEFAULT NULL,
  `aprobado_por` int DEFAULT NULL,
  `fecha_baja` date NOT NULL,
  `observaciones` text,
  `creado_en` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cargo`
--

-- Tabla `cargo` eliminada — el cargo ahora es un campo libre en `colaboradores`.

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categoria_activo`
--

CREATE TABLE `categoria_activo` (
  `id_categoria` int NOT NULL,
  `nombre` varchar(150) NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `estado` varchar(20) NOT NULL DEFAULT 'ACTIVO',
  `creado_en` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `actualizado_en` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `categoria_activo`
--

INSERT INTO `categoria_activo` (`id_categoria`, `nombre`, `descripcion`, `estado`, `creado_en`, `actualizado_en`) VALUES
(1, 'TECLADO', 'Teclados', 'ACTIVO', '2026-04-12 19:15:30', '2026-04-13 09:35:06'),
(2, 'CELULAR', 'Celulares', 'ACTIVO', '2026-04-12 19:24:41', '2026-04-12 21:45:59'),
(3, 'PORTATIL', 'Portatiles', 'INACTIVO', '2026-04-12 19:28:05', '2026-05-16 17:37:33');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `dependencia`
--

CREATE TABLE `dependencias` (
  `id_dependencia` int NOT NULL,
  `nombre_dependencia` varchar(150) NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `estado` enum('ACTIVO','INACTIVO') NOT NULL DEFAULT 'ACTIVO',
  `creado_en` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `actualizado_en` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `dependencias`
--

INSERT INTO `dependencias` (`id_dependencia`, `nombre_dependencia`, `descripcion`, `estado`, `creado_en`, `actualizado_en`) VALUES
(1, 'OFICINA DE TECNOLOGIA DE LA INFORMACION', NULL, 'ACTIVO', '2026-04-12 23:11:33', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `dependencia_sede`
--

CREATE TABLE `sede_dependencia` (
  `id_sede_dependencia` int NOT NULL,
  `id_sede` int NOT NULL,
  `id_dependencia` int NOT NULL,
  `estado` enum('ACTIVO','INACTIVO') NOT NULL DEFAULT 'ACTIVO',
  `creado_en` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `actualizado_en` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `sede_dependencia`
--

INSERT INTO `sede_dependencia` (`id_sede_dependencia`, `id_sede`, `id_dependencia`, `estado`, `creado_en`, `actualizado_en`) VALUES
(1, 1, 1, 'ACTIVO', '2026-04-12 23:12:14', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_movimiento_activo`
--

CREATE TABLE `detalle_movimiento_activo` (
  `id_detalle_movimiento` int NOT NULL,
  `id_movimiento` int NOT NULL,
  `id_activo` int NOT NULL,
  `condicion_salida_id` int DEFAULT NULL,
  `condicion_entrada_id` int DEFAULT NULL,
  `observaciones` varchar(255) DEFAULT NULL,
  `creado_en` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estado_activo`
--

CREATE TABLE `estado_activo` (
  `id_estado_activo` int NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `tipo_estado` enum('CONDICION','SITUACION') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `estado` varchar(20) NOT NULL DEFAULT 'ACTIVO'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estado_movimiento`
--

CREATE TABLE `estado_movimiento` (
  `id_estado_movimiento` int NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `estado` varchar(20) NOT NULL DEFAULT 'ACTIVO'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `mantenimiento`
--

CREATE TABLE `mantenimiento` (
  `id_mantenimiento` int NOT NULL,
  `id_activo` int NOT NULL,
  `id_responsable_registra` int NOT NULL,
  `tipo_mantenimiento` varchar(50) NOT NULL,
  `descripcion` text NOT NULL,
  `diagnostico` text,
  `proveedor` varchar(150) DEFAULT NULL,
  `costo` decimal(12,2) DEFAULT NULL,
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date DEFAULT NULL,
  `resultado` text,
  `estado` varchar(20) NOT NULL DEFAULT 'ABIERTO',
  `creado_en` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `actualizado_en` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `marca`
--

CREATE TABLE `marca` (
  `id_marca` int NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `estado` varchar(20) NOT NULL DEFAULT 'ACTIVO',
  `creado_en` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `actualizado_en` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `modelo`
--

CREATE TABLE `modelo` (
  `id_modelo` int NOT NULL,
  `id_marca` int NOT NULL,
  `id_categoria` int DEFAULT NULL,
  `nombre` varchar(150) NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `estado` varchar(20) NOT NULL DEFAULT 'ACTIVO',
  `creado_en` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `actualizado_en` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `movimiento`
--

CREATE TABLE `movimiento` (
  `id_movimiento` int NOT NULL,
  `id_tipo_movimiento` int NOT NULL,
  `id_estado_movimiento` int NOT NULL,
  `id_responsable_origen` int DEFAULT NULL,
  `id_responsable_destino` int DEFAULT NULL,
  `id_ubicacion_origen` int DEFAULT NULL,
  `id_ubicacion_destino` int DEFAULT NULL,
  `codigo_movimiento` varchar(50) NOT NULL,
  `fecha_movimiento` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_devolucion_programada` date DEFAULT NULL,
  `fecha_cierre` datetime DEFAULT NULL,
  `motivo` text,
  `observaciones` text,
  `documento_sustento` varchar(255) DEFAULT NULL,
  `registrado_por` int NOT NULL,
  `creado_en` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `actualizado_en` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `permisos`
--

CREATE TABLE `permisos` (
  `id_permiso` int NOT NULL,
  `codigo` varchar(100) NOT NULL,
  `nombre` varchar(150) NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `colaboradores`
-- Reemplaza las tablas `persona` + `personal` + `cargo` del diseño original.
--

CREATE TABLE `colaboradores` (
  `id_colaborador` int NOT NULL,
  `nro_documento` varchar(20) NOT NULL,
  `per_nombre` varchar(100) NOT NULL,
  `per_apepat` varchar(100) NOT NULL,
  `per_apemat` varchar(100) DEFAULT NULL,
  `fecha_nacimiento` date DEFAULT NULL,
  `nro_movil` varchar(20) DEFAULT NULL,
  `per_email` varchar(150) DEFAULT NULL,
  `per_direccion` varchar(255) DEFAULT NULL,
  `per_foto` varchar(255) DEFAULT NULL,
  `id_sede_dependencia` int DEFAULT NULL,
  `cargo` varchar(100) DEFAULT NULL,
  `tipo_colaborador` enum('ADMINISTRATIVO','DOCENTE','TECNICO','PRACTICANTE','EXTERNO') NOT NULL DEFAULT 'ADMINISTRATIVO',
  `fecha_ingreso` date DEFAULT NULL,
  `fecha_salida` date DEFAULT NULL,
  `estado` enum('ACTIVO','INACTIVO') NOT NULL DEFAULT 'ACTIVO',
  `creado_en` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `actualizado_en` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `colaboradores`
--

INSERT INTO `colaboradores` (`id_colaborador`, `nro_documento`, `per_nombre`, `per_apepat`, `per_apemat`, `fecha_nacimiento`, `nro_movil`, `per_email`, `per_direccion`, `per_foto`, `id_sede_dependencia`, `cargo`, `tipo_colaborador`, `fecha_ingreso`, `fecha_salida`, `estado`, `creado_en`, `actualizado_en`) VALUES
(1, '74432978', 'DANTE JOSSUE', 'CAMPOS', 'OCHOA', '2004-09-01', '964890773', 'dantecampos888@gmail.com', 'Laura Caller', NULL, 1, 'PRACTICANTE', 'PRACTICANTE', NULL, NULL, 'ACTIVO', '2026-04-12 23:12:57', NULL);

-- --------------------------------------------------------

-- Tabla `personal` eliminada — datos fusionados en `colaboradores`.

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `roles`
--

CREATE TABLE `roles` (
  `id_rol` int NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `estado` enum('ACTIVO','INACTIVO') NOT NULL DEFAULT 'ACTIVO',
  `creado_en` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `roles`
--

INSERT INTO `roles` (`id_rol`, `nombre`, `descripcion`, `estado`, `creado_en`) VALUES
(1, 'ADMINISTRADOR', 'ADMINISTRADOR DEL SISTEMA', 'ACTIVO', '2026-04-12 17:17:41');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `rol_permiso`
--

CREATE TABLE `rol_permiso` (
  `id_rol` int NOT NULL,
  `id_permiso` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `rol_permiso`
-- ADMINISTRADOR recibe todos los permisos.
--

INSERT INTO `rol_permiso` (`id_rol`, `id_permiso`) VALUES
(1,1),(1,2),(1,3),(1,4),(1,5),(1,6),(1,7),(1,8),(1,9),(1,10),
(1,11),(1,12),(1,13),(1,14),(1,15),(1,16),(1,17),(1,18);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sede`
--

CREATE TABLE `sede` (
  `id_sede` int NOT NULL,
  `nombre_sede` varchar(150) NOT NULL,
  `direccion` varchar(255) DEFAULT NULL,
  `estado` enum('ACTIVO','INACTIVO') NOT NULL DEFAULT 'ACTIVO',
  `creado_en` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `actualizado_en` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `sede`
--

INSERT INTO `sede` (`id_sede`, `nombre_sede`, `direccion`, `estado`, `creado_en`, `actualizado_en`) VALUES
(1, 'HUALCARÁ', NULL, 'ACTIVO', '2026-04-12 23:11:42', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipo_movimiento`
--

CREATE TABLE `tipo_movimiento` (
  `id_tipo_movimiento` int NOT NULL,
  `codigo` varchar(50) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `requiere_destino` int NOT NULL DEFAULT '0',
  `requiere_devolucion` int NOT NULL DEFAULT '0',
  `afecta_responsable` int NOT NULL DEFAULT '0',
  `afecta_ubicacion` int NOT NULL DEFAULT '0',
  `estado` enum('ACTIVO','INACTIVO') NOT NULL DEFAULT 'ACTIVO'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ubicaciones`
--

CREATE TABLE `ubicaciones` (
  `id_ubicacion` int NOT NULL,
  `id_sede` int NOT NULL,
  `id_ubicacion_padre` int DEFAULT NULL,
  `id_dependencia_sede` int DEFAULT NULL,
  `nombre` varchar(150) NOT NULL,
  `tipo` enum('PABELLON','PISO','OFICINA','LABORATORIO','AULA','OTRO') NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `estado` enum('ACTIVO','INACTIVO') NOT NULL DEFAULT 'ACTIVO',
  `creado_en` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `actualizado_en` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario`
--

CREATE TABLE `usuario` (
  `id_usuario` int NOT NULL,
  `id_persona` int NOT NULL,
  `id_rol` int NOT NULL,
  `nombre_usuario` varchar(100) NOT NULL,
  `contrasena` varchar(255) NOT NULL,
  `estado` enum('ACTIVO','INACTIVO') NOT NULL DEFAULT 'ACTIVO',
  `ultimo_acceso_en` datetime DEFAULT NULL,
  `fecha_cambio_clave_en` datetime DEFAULT NULL,
  `intentos_fallidos` int NOT NULL DEFAULT '0',
  `bloqueado_hasta` datetime DEFAULT NULL,
  `creado_en` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `actualizado_en` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `fecha_baja` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `usuario`
--

INSERT INTO `usuario` (`id_usuario`, `id_persona`, `id_rol`, `nombre_usuario`, `contrasena`, `estado`, `ultimo_acceso_en`, `fecha_cambio_clave_en`, `intentos_fallidos`, `bloqueado_hasta`, `creado_en`, `actualizado_en`, `fecha_baja`) VALUES
(1, 1, 1, 'admin', '$2y$10$XV6zG.kni4OZSE/xmBtdW.4MnwXv1tl/CFgxx48Sm5hsZJmZcJrwe', 'ACTIVO', NULL, NULL, 0, NULL, '2026-04-12 17:19:01', '2026-04-13 10:30:49', NULL);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `activo`
--
ALTER TABLE `activo`
  ADD PRIMARY KEY (`id_activo`),
  ADD UNIQUE KEY `uk_activo_codigo_interno` (`codigo_interno`),
  ADD UNIQUE KEY `uk_activo_codigo_patrimonial` (`codigo_patrimonial`),
  ADD UNIQUE KEY `uk_activo_numero_serie` (`numero_serie`),
  ADD KEY `fk_activo_categoria` (`id_categoria`),
  ADD KEY `fk_activo_modelo` (`id_modelo`),
  ADD KEY `fk_activo_ubicacion` (`id_ubicacion_actual`),
  ADD KEY `fk_activo_responsable` (`id_responsable_actual`),
  ADD KEY `fk_activo_condicion` (`id_condicion_actual`),
  ADD KEY `fk_activo_situacion` (`id_situacion_actual`),
  ADD KEY `fk_activo_creado_por` (`creado_por`),
  ADD KEY `fk_activo_actualizado_por` (`actualizado_por`);

--
-- Indices de la tabla `adjuntos_activo`
--
ALTER TABLE `adjuntos_activo`
  ADD PRIMARY KEY (`id_adjunto`),
  ADD KEY `fk_adjuntos_activo` (`id_activo`),
  ADD KEY `fk_adjuntos_usuario` (`subido_por`);

--
-- Indices de la tabla `auditoria`
--
ALTER TABLE `auditoria`
  ADD PRIMARY KEY (`id_auditoria`),
  ADD KEY `fk_auditoria_usuario` (`id_usuario`);

--
-- Indices de la tabla `bajas_activo`
--
ALTER TABLE `bajas_activo`
  ADD PRIMARY KEY (`id_baja`),
  ADD KEY `fk_bajas_activo` (`id_activo`),
  ADD KEY `fk_bajas_aprobado_por` (`aprobado_por`);

--
-- Indices de la tabla `cargo`
--
ALTER TABLE `cargo`
  ADD PRIMARY KEY (`id_cargo`);

--
-- Indices de la tabla `categoria_activo`
--
ALTER TABLE `categoria_activo`
  ADD PRIMARY KEY (`id_categoria`),
  ADD UNIQUE KEY `uk_categoria_activo_nombre` (`nombre`);

--
-- Indices de la tabla `dependencia`
--
ALTER TABLE `dependencia`
  ADD PRIMARY KEY (`id_dependencia`),
  ADD UNIQUE KEY `uk_dependencia_nombre` (`nombre_dependencia`);

--
-- Indices de la tabla `dependencia_sede`
--
ALTER TABLE `dependencia_sede`
  ADD PRIMARY KEY (`id_dependencia_sede`),
  ADD KEY `fk_dependencia_sede_dependencia` (`id_dependencia`),
  ADD KEY `fk_dependencia_sede_sede` (`id_sede`);

--
-- Indices de la tabla `detalle_movimiento_activo`
--
ALTER TABLE `detalle_movimiento_activo`
  ADD PRIMARY KEY (`id_detalle_movimiento`),
  ADD UNIQUE KEY `uk_detalle_movimiento_activo` (`id_movimiento`,`id_activo`),
  ADD KEY `fk_detalle_activo` (`id_activo`),
  ADD KEY `fk_detalle_condicion_salida` (`condicion_salida_id`),
  ADD KEY `fk_detalle_condicion_entrada` (`condicion_entrada_id`);

--
-- Indices de la tabla `estado_activo`
--
ALTER TABLE `estado_activo`
  ADD PRIMARY KEY (`id_estado_activo`),
  ADD UNIQUE KEY `uk_estado_activo_nombre_tipo` (`nombre`,`tipo_estado`);

--
-- Indices de la tabla `estado_movimiento`
--
ALTER TABLE `estado_movimiento`
  ADD PRIMARY KEY (`id_estado_movimiento`),
  ADD UNIQUE KEY `uk_estados_movimiento_nombre` (`nombre`);

--
-- Indices de la tabla `mantenimiento`
--
ALTER TABLE `mantenimiento`
  ADD PRIMARY KEY (`id_mantenimiento`),
  ADD KEY `fk_mantenimiento_activo` (`id_activo`),
  ADD KEY `fk_mantenimiento_responsable` (`id_responsable_registra`);

--
-- Indices de la tabla `marca`
--
ALTER TABLE `marca`
  ADD PRIMARY KEY (`id_marca`),
  ADD UNIQUE KEY `uk_marca_nombre` (`nombre`);

--
-- Indices de la tabla `modelo`
--
ALTER TABLE `modelo`
  ADD PRIMARY KEY (`id_modelo`),
  ADD UNIQUE KEY `uk_modelo_marca_categoria_nombre` (`id_marca`,`id_categoria`,`nombre`),
  ADD KEY `fk_modelo_categoria` (`id_categoria`);

--
-- Indices de la tabla `movimiento`
--
ALTER TABLE `movimiento`
  ADD PRIMARY KEY (`id_movimiento`),
  ADD UNIQUE KEY `uk_movimiento_codigo` (`codigo_movimiento`),
  ADD KEY `fk_movimiento_tipo` (`id_tipo_movimiento`),
  ADD KEY `fk_movimiento_estado` (`id_estado_movimiento`),
  ADD KEY `fk_movimiento_responsable_origen` (`id_responsable_origen`),
  ADD KEY `fk_movimiento_responsable_destino` (`id_responsable_destino`),
  ADD KEY `fk_movimiento_ubicacion_origen` (`id_ubicacion_origen`),
  ADD KEY `fk_movimiento_ubicacion_destino` (`id_ubicacion_destino`),
  ADD KEY `fk_movimiento_registrado_por` (`registrado_por`);

--
-- Indices de la tabla `permisos`
--
ALTER TABLE `permisos`
  ADD PRIMARY KEY (`id_permiso`),
  ADD UNIQUE KEY `uk_permiso_codigo` (`codigo`);

--
-- Indices de la tabla `persona`
--
ALTER TABLE `persona`
  ADD PRIMARY KEY (`id_persona`),
  ADD UNIQUE KEY `uk_persona_documento` (`nro_documento`);

--
-- Indices de la tabla `personal`
--
ALTER TABLE `personal`
  ADD PRIMARY KEY (`id_personal`),
  ADD UNIQUE KEY `uk_personal_persona` (`id_persona`),
  ADD KEY `fk_personal_dependencia_sede` (`id_dependencia_sede`),
  ADD KEY `fk_administrativos_cargo` (`id_cargo`);

--
-- Indices de la tabla `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id_rol`),
  ADD UNIQUE KEY `uk_rol_nombre` (`nombre`);

--
-- Indices de la tabla `rol_permiso`
--
ALTER TABLE `rol_permiso`
  ADD PRIMARY KEY (`id_rol`,`id_permiso`),
  ADD KEY `fk_rol_permiso_permiso` (`id_permiso`);

--
-- Indices de la tabla `sede`
--
ALTER TABLE `sede`
  ADD PRIMARY KEY (`id_sede`),
  ADD UNIQUE KEY `uk_sede_nombre` (`nombre_sede`);

--
-- Indices de la tabla `tipo_movimiento`
--
ALTER TABLE `tipo_movimiento`
  ADD PRIMARY KEY (`id_tipo_movimiento`);

--
-- Indices de la tabla `ubicaciones`
--
ALTER TABLE `ubicaciones`
  ADD PRIMARY KEY (`id_ubicacion`),
  ADD KEY `fk_ubicacion_sede` (`id_sede`),
  ADD KEY `fk_ubicacion_padre` (`id_ubicacion_padre`),
  ADD KEY `fk_ubicacion_dependencia_sede` (`id_dependencia_sede`);

--
-- Indices de la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD PRIMARY KEY (`id_usuario`),
  ADD UNIQUE KEY `uk_usuario_nombre_usuario` (`nombre_usuario`),
  ADD UNIQUE KEY `uk_usuario_persona` (`id_persona`),
  ADD KEY `fk_usuario_rol` (`id_rol`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `activo`
--
ALTER TABLE `activo`
  MODIFY `id_activo` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `adjuntos_activo`
--
ALTER TABLE `adjuntos_activo`
  MODIFY `id_adjunto` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `auditoria`
--
ALTER TABLE `auditoria`
  MODIFY `id_auditoria` bigint NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `bajas_activo`
--
ALTER TABLE `bajas_activo`
  MODIFY `id_baja` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `cargo`
--
ALTER TABLE `cargo`
  MODIFY `id_cargo` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `categoria_activo`
--
ALTER TABLE `categoria_activo`
  MODIFY `id_categoria` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `dependencia`
--
ALTER TABLE `dependencia`
  MODIFY `id_dependencia` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `dependencia_sede`
--
ALTER TABLE `dependencia_sede`
  MODIFY `id_dependencia_sede` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `detalle_movimiento_activo`
--
ALTER TABLE `detalle_movimiento_activo`
  MODIFY `id_detalle_movimiento` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `estado_activo`
--
ALTER TABLE `estado_activo`
  MODIFY `id_estado_activo` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `estado_movimiento`
--
ALTER TABLE `estado_movimiento`
  MODIFY `id_estado_movimiento` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `mantenimiento`
--
ALTER TABLE `mantenimiento`
  MODIFY `id_mantenimiento` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `marca`
--
ALTER TABLE `marca`
  MODIFY `id_marca` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `modelo`
--
ALTER TABLE `modelo`
  MODIFY `id_modelo` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `movimiento`
--
ALTER TABLE `movimiento`
  MODIFY `id_movimiento` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `permisos`
--
ALTER TABLE `permisos`
  MODIFY `id_permiso` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `persona`
--
ALTER TABLE `persona`
  MODIFY `id_persona` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `personal`
--
ALTER TABLE `personal`
  MODIFY `id_personal` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `roles`
--
ALTER TABLE `roles`
  MODIFY `id_rol` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `sede`
--
ALTER TABLE `sede`
  MODIFY `id_sede` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `tipo_movimiento`
--
ALTER TABLE `tipo_movimiento`
  MODIFY `id_tipo_movimiento` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `ubicaciones`
--
ALTER TABLE `ubicaciones`
  MODIFY `id_ubicacion` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `usuario`
--
ALTER TABLE `usuario`
  MODIFY `id_usuario` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `activo`
--
ALTER TABLE `activo`
  ADD CONSTRAINT `fk_activo_actualizado_por` FOREIGN KEY (`actualizado_por`) REFERENCES `usuario` (`id_usuario`),
  ADD CONSTRAINT `fk_activo_categoria` FOREIGN KEY (`id_categoria`) REFERENCES `categoria_activo` (`id_categoria`),
  ADD CONSTRAINT `fk_activo_condicion` FOREIGN KEY (`id_condicion_actual`) REFERENCES `estado_activo` (`id_estado_activo`),
  ADD CONSTRAINT `fk_activo_creado_por` FOREIGN KEY (`creado_por`) REFERENCES `usuario` (`id_usuario`),
  ADD CONSTRAINT `fk_activo_modelo` FOREIGN KEY (`id_modelo`) REFERENCES `modelo` (`id_modelo`),
  ADD CONSTRAINT `fk_activo_responsable` FOREIGN KEY (`id_responsable_actual`) REFERENCES `personal` (`id_personal`),
  ADD CONSTRAINT `fk_activo_situacion` FOREIGN KEY (`id_situacion_actual`) REFERENCES `estado_activo` (`id_estado_activo`),
  ADD CONSTRAINT `fk_activo_ubicacion` FOREIGN KEY (`id_ubicacion_actual`) REFERENCES `ubicaciones` (`id_ubicacion`);

--
-- Filtros para la tabla `adjuntos_activo`
--
ALTER TABLE `adjuntos_activo`
  ADD CONSTRAINT `fk_adjuntos_activo` FOREIGN KEY (`id_activo`) REFERENCES `activo` (`id_activo`),
  ADD CONSTRAINT `fk_adjuntos_usuario` FOREIGN KEY (`subido_por`) REFERENCES `usuario` (`id_usuario`);

--
-- Filtros para la tabla `auditoria`
--
ALTER TABLE `auditoria`
  ADD CONSTRAINT `fk_auditoria_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`);

--
-- Filtros para la tabla `bajas_activo`
--
ALTER TABLE `bajas_activo`
  ADD CONSTRAINT `fk_bajas_activo` FOREIGN KEY (`id_activo`) REFERENCES `activo` (`id_activo`),
  ADD CONSTRAINT `fk_bajas_aprobado_por` FOREIGN KEY (`aprobado_por`) REFERENCES `usuario` (`id_usuario`);

--
-- Filtros para la tabla `dependencia_sede`
--
ALTER TABLE `dependencia_sede`
  ADD CONSTRAINT `fk_dependencia_sede_dependencia` FOREIGN KEY (`id_dependencia`) REFERENCES `dependencia` (`id_dependencia`),
  ADD CONSTRAINT `fk_dependencia_sede_sede` FOREIGN KEY (`id_sede`) REFERENCES `sede` (`id_sede`);

--
-- Filtros para la tabla `detalle_movimiento_activo`
--
ALTER TABLE `detalle_movimiento_activo`
  ADD CONSTRAINT `fk_detalle_activo` FOREIGN KEY (`id_activo`) REFERENCES `activo` (`id_activo`),
  ADD CONSTRAINT `fk_detalle_condicion_entrada` FOREIGN KEY (`condicion_entrada_id`) REFERENCES `estado_activo` (`id_estado_activo`),
  ADD CONSTRAINT `fk_detalle_condicion_salida` FOREIGN KEY (`condicion_salida_id`) REFERENCES `estado_activo` (`id_estado_activo`),
  ADD CONSTRAINT `fk_detalle_movimiento` FOREIGN KEY (`id_movimiento`) REFERENCES `movimiento` (`id_movimiento`);

--
-- Filtros para la tabla `mantenimiento`
--
ALTER TABLE `mantenimiento`
  ADD CONSTRAINT `fk_mantenimiento_activo` FOREIGN KEY (`id_activo`) REFERENCES `activo` (`id_activo`),
  ADD CONSTRAINT `fk_mantenimiento_responsable` FOREIGN KEY (`id_responsable_registra`) REFERENCES `usuario` (`id_usuario`);

--
-- Filtros para la tabla `modelo`
--
ALTER TABLE `modelo`
  ADD CONSTRAINT `fk_modelo_categoria` FOREIGN KEY (`id_categoria`) REFERENCES `categoria_activo` (`id_categoria`),
  ADD CONSTRAINT `fk_modelo_marca` FOREIGN KEY (`id_marca`) REFERENCES `marca` (`id_marca`);

--
-- Filtros para la tabla `movimiento`
--
ALTER TABLE `movimiento`
  ADD CONSTRAINT `fk_movimiento_estado` FOREIGN KEY (`id_estado_movimiento`) REFERENCES `estado_movimiento` (`id_estado_movimiento`),
  ADD CONSTRAINT `fk_movimiento_registrado_por` FOREIGN KEY (`registrado_por`) REFERENCES `usuario` (`id_usuario`),
  ADD CONSTRAINT `fk_movimiento_responsable_destino` FOREIGN KEY (`id_responsable_destino`) REFERENCES `personal` (`id_personal`),
  ADD CONSTRAINT `fk_movimiento_responsable_origen` FOREIGN KEY (`id_responsable_origen`) REFERENCES `personal` (`id_personal`),
  ADD CONSTRAINT `fk_movimiento_tipo` FOREIGN KEY (`id_tipo_movimiento`) REFERENCES `tipo_movimiento` (`id_tipo_movimiento`),
  ADD CONSTRAINT `fk_movimiento_ubicacion_destino` FOREIGN KEY (`id_ubicacion_destino`) REFERENCES `ubicaciones` (`id_ubicacion`),
  ADD CONSTRAINT `fk_movimiento_ubicacion_origen` FOREIGN KEY (`id_ubicacion_origen`) REFERENCES `ubicaciones` (`id_ubicacion`);

--
-- Filtros para la tabla `personal`
--
ALTER TABLE `personal`
  ADD CONSTRAINT `fk_administrativos_cargo` FOREIGN KEY (`id_cargo`) REFERENCES `cargo` (`id_cargo`),
  ADD CONSTRAINT `fk_personal_dependencia_sede` FOREIGN KEY (`id_dependencia_sede`) REFERENCES `dependencia_sede` (`id_dependencia_sede`),
  ADD CONSTRAINT `fk_personal_persona` FOREIGN KEY (`id_persona`) REFERENCES `persona` (`id_persona`);

--
-- Filtros para la tabla `rol_permiso`
--
ALTER TABLE `rol_permiso`
  ADD CONSTRAINT `fk_rol_permiso_permiso` FOREIGN KEY (`id_permiso`) REFERENCES `permisos` (`id_permiso`),
  ADD CONSTRAINT `fk_rol_permiso_rol` FOREIGN KEY (`id_rol`) REFERENCES `roles` (`id_rol`);

--
-- Filtros para la tabla `ubicaciones`
--
ALTER TABLE `ubicaciones`
  ADD CONSTRAINT `fk_ubicacion_dependencia_sede` FOREIGN KEY (`id_dependencia_sede`) REFERENCES `dependencia_sede` (`id_dependencia_sede`),
  ADD CONSTRAINT `fk_ubicacion_padre` FOREIGN KEY (`id_ubicacion_padre`) REFERENCES `ubicaciones` (`id_ubicacion`),
  ADD CONSTRAINT `fk_ubicacion_sede` FOREIGN KEY (`id_sede`) REFERENCES `sede` (`id_sede`);

--
-- Filtros para la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD CONSTRAINT `fk_usuario_persona` FOREIGN KEY (`id_persona`) REFERENCES `persona` (`id_persona`),
  ADD CONSTRAINT `fk_usuario_rol` FOREIGN KEY (`id_rol`) REFERENCES `roles` (`id_rol`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
