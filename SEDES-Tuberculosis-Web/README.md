# Proyecto Laravel

Este es un proyecto basado en Laravel. Sigue las siguientes instrucciones para configurarlo y ejecutarlo correctamente.

## Requisitos previos

Antes de comenzar, asegúrate de tener instalados los siguientes componentes en tu sistema:

- PHP (>=8.0 recomendado)
- Composer
- MySQL o cualquier base de datos compatible
- Node.js y NPM (opcional, si se utilizan assets frontend)

## Instalación

1. Clona este repositorio en tu máquina local:

   ```sh
   git clone https://github.com/tu-usuario/tu-repositorio.git
   ```

2. Accede al directorio del proyecto:

   ```sh
   cd tu-repositorio
   ```

3. Instala las dependencias de PHP con Composer:

   ```sh
   composer install
   ```

4. Copia el archivo de configuración de entorno y ajústalo según tus necesidades:

   ```sh
   cp .env.example .env
   ```

5. Genera la clave de aplicación de Laravel:

   ```sh
   php artisan key:generate
   ```

6. Configura la base de datos en el archivo `.env` y luego ejecuta las migraciones:

   ```sh
   php artisan migrate
   ```

7. Si el proyecto utiliza frontend, instala las dependencias de NPM y compila los assets:

   ```sh
   npm install && npm run dev
   ```

## Ejecución del proyecto

Para iniciar el servidor de desarrollo de Laravel, usa el siguiente comando:

```sh
php artisan serve
```

El proyecto estará disponible en `http://127.0.0.1:8000/`.

## Comandos útiles

- Para ejecutar pruebas:

  ```sh
  php artisan test
  ```

- Para ejecutar tareas en background (si se usan colas de trabajos):

  ```sh
  php artisan queue:work
  ```

- Para limpiar caché:

  ```sh
  php artisan cache:clear
  php artisan config:clear
  php artisan route:clear
  php artisan view:clear
  ```

## Contribuciones

Si deseas contribuir a este proyecto, por favor abre un Issue o envía un Pull Request.

## Licencia

Este proyecto está bajo la licencia [MIT](LICENSE).
