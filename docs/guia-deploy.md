# Guía de Despliegue del Proyecto Unimind

Esta guía explica cómo desplegar el proyecto Unimind localmente usando XAMPP y ngrok para exponerlo públicamente. Incluye instrucciones para Linux (donde se basa el script `deploy.sh`) y Windows, además de cómo detener el despliegue.

## Prerrequisitos

- **Git**: Para gestionar ramas.
- **XAMPP**: Para el servidor local (Apache y MySQL).
  - Linux: Instala XAMPP desde [apachefriends.org](https://www.apachefriends.org).
  - Windows: Descarga e instala XAMPP para Windows desde el mismo sitio.
- **ngrok**: Para exponer el servidor local a internet. Descárgalo desde [ngrok.com](https://ngrok.com) e instala la CLI.
- **jq** (opcional, para Linux): Para extraer la URL de ngrok automáticamente. Instálalo con `sudo apt install jq` (en Ubuntu/Debian).
- El proyecto debe estar clonado en `/opt/lampp/htdocs/unimind` (Linux) o en una ruta equivalente en Windows (ej. `C:\xampp\htdocs\unimind`).

## Despliegue en Linux

1. Abre una terminal.
2. Navega al directorio del proyecto y ejecuta el script:
   ```
   cd /opt/lampp/htdocs/unimind
   ./deploy.sh
   ```

   - Esto cambia a la rama `develop`, actualiza el código, inicia XAMPP, lanza ngrok en segundo plano y muestra la URL pública.
3. Pasa el enlace generado (ej. `https://nobby-unlettered-cathryn.ngrok-free.dev/`) a los desarrolladores. Desde ahí, pueden acceder a todas las funcionalidades según su rol.

## Despliegue en Windows

Windows no usa el script Bash directamente, pero puedes replicar los pasos manualmente con herramientas similares:

1. Abre una línea de comandos (CMD o PowerShell) como administrador.
2. Cambia a la rama `develop` y actualiza:
   ```
   cd C:\xampp\htdocs\unimind
   git checkout develop
   git pull origin develop
   ```
3. Inicia XAMPP: Ejecuta `C:\xampp\xampp-control.exe` y haz clic en "Start" para Apache y MySQL.
4. Instala y ejecuta ngrok: En una nueva terminal, ejecuta `ngrok http 80` (ajusta la ruta si es necesario).
5. Obtén la URL: Ve a `http://localhost:4040` en tu navegador para ver la URL pública de ngrok.
6. Pasa el enlace generado (ej. `https://nobby-unlettered-cathryn.ngrok-free.dev/`) a los desarrolladores. Desde ahí, pueden acceder a todas las funcionalidades según su rol.

## Detener el Despliegue

- **En Linux**: Ejecuta en la terminal:
  ```
  pkill ngrok && sudo /opt/lampp/lampp stop
  ```
  Esto detiene ngrok y apaga XAMPP.
- **En Windows**:
  - Detén ngrok: Cierra la terminal donde se ejecuta `ngrok` (o usa Ctrl+C).
  - Detén XAMPP: En el panel de control de XAMPP, haz clic en "Stop" para Apache y MySQL.

## Notas Adicionales

- Asegúrate de que el puerto 80 esté libre antes de iniciar.
- Si hay errores, verifica permisos (en Linux, usa `sudo` si es necesario).
- Para desarrollo continuo, puedes modificar el script o los pasos manuales según tus necesidades.
