# Catálogo de Películas

Aplicación web en PHP para iniciar sesión, consultar, buscar, ordenar y administrar un catálogo de películas. Los administradores pueden agregar, editar y eliminar películas; los usuarios pueden consultar el catálogo.

## Estructura

- `index.php`: portada y catálogo de películas.
- `login.php` y `logout.php`: inicio y cierre de sesión.
- `agregar.php`, `editar.php` y `eliminar.php`: operaciones administrativas sobre películas.
- `includes/`: conexión a la base de datos y componentes compartidos de página.
- `js/`: validaciones de formularios en el navegador.
- `img/`: imágenes utilizadas en la interfaz.
- `schema.sql`: creación de la base de datos, tablas y datos de prueba.

## Requisitos

- PHP con PDO MySQL habilitado.
- MySQL o MariaDB.
- Apache u otro servidor web compatible con PHP (por ejemplo, XAMPP).

## Configuración básica

1. Importa `schema.sql` en MySQL para crear la base de datos y los datos iniciales.
2. Si es necesario, actualiza las credenciales de conexión en `includes/db.php`.
3. Coloca el proyecto dentro de la carpeta pública del servidor web y abre `index.php` desde el navegador.
