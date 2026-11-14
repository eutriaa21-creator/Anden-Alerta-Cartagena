# Andén Alerta Cartagena

**Sistema web de reporte ciudadano de problemas en andenes y espacios peatonales de Cartagena de Indias.**

## Descripción del proyecto

Andén Alerta Cartagena es una aplicación web desarrollada como proyecto académico por estudiantes de la Corporación Universitaria Rafael Núñez (CURN).

Su objetivo es facilitar la identificación y el reporte de problemas que afectan la movilidad peatonal, como huecos, andenes deteriorados, obstáculos, rampas dañadas y señalización en mal estado.

La plataforma permite que los ciudadanos registren reportes y consulten su estado, mientras que los administradores pueden gestionar los reportes y las categorías.

## Funcionalidades principales

### Ciudadanos
- Registro e inicio de sesión.
- Creación de reportes.
- Adjuntar fotografías como evidencia.
- Consulta de reportes registrados.
- Visualización del estado de los reportes.

### Administradores
- Acceso mediante cuenta con rol de administrador.
- Visualización y gestión de reportes.
- Edición de reportes y actualización de estados.
- Creación, edición y eliminación de categorías.
- Consulta de estadísticas y filtros.

## Tecnologías utilizadas

- **PHP:** lógica y funcionamiento del sistema.
- **MySQL:** almacenamiento de usuarios, categorías y reportes.
- **PDO:** conexión entre PHP y MySQL.
- **HTML y CSS:** estructura y diseño de las interfaces.
- **JavaScript:** interacciones de la aplicación.
- **Git y GitHub:** control de versiones y trabajo colaborativo.
- **XAMPP:** entorno de desarrollo local.

## Requisitos

- Windows.
- XAMPP con Apache y PHP.
- MySQL Community Server.
- MySQL Workbench (recomendado).
- Navegador web.

## Instalación y configuración

1. Clonar o descargar este repositorio.
2. Colocar la carpeta del proyecto dentro del directorio `htdocs` de XAMPP.
3. Iniciar Apache desde XAMPP.
4. Iniciar el servicio de MySQL Community Server.
5. Abrir MySQL Workbench y ejecutar el script `database/anden_alerta.sql`.
6. Crear el archivo `includes/config.local.php` dentro de la carpeta `includes`, con la siguiente estructura:

   ```php
   <?php

   return [
       'host' => 'localhost',
       'puerto' => '3306',
       'baseDatos' => 'anden_alerta',
       'usuario' => 'TU_USUARIO_MYSQL',
       'contrasena' => 'TU_CONTRASEÑA_MYSQL'
   ];
   ```

   Reemplazar `TU_USUARIO_MYSQL` y `TU_CONTRASEÑA_MYSQL` por las credenciales locales de MySQL. Este archivo es personal y está excluido del repositorio por seguridad.
7. Acceder desde el navegador a:

   `http://localhost/AndenAlertaCartagena/`

## Base de datos

La aplicación utiliza una base de datos llamada `anden_alerta`, compuesta por tres tablas principales:

- **usuarios:** almacena la información y los roles de los usuarios.
- **categorias:** contiene los tipos de problemas que se pueden reportar.
- **reportes:** almacena los reportes ciudadanos y sus relaciones con usuarios y categorías.

La estructura de la base de datos se encuentra en `database/anden_alerta.sql`.

## Trabajo colaborativo

El desarrollo se distribuyó entre los integrantes del equipo:

- **Felipe:** diseño y estructura de la base de datos MySQL.
- **José:** implementación de la conexión a MySQL mediante PDO.
- **Daniel:** desarrollo del CRUD y las reglas de negocio.
- **Integración principal:** autenticación, registro, permisos, integración de componentes y pruebas.

## Proyecto académico

**Institución:** Corporación Universitaria Rafael Núñez (CURN).

**Proyecto:** Andén Alerta Cartagena.

**Área:** Desarrollo de sistemas de información y software.