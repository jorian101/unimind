# Explicación de Husky en el Proyecto

## ¿Qué es Husky?

Husky es una herramienta que facilita la gestión de **hooks de Git**. Los hooks son scripts que se ejecutan automáticamente en ciertos momentos del ciclo de vida de Git, como antes de un commit o después de un push. En este proyecto, Husky está configurado para ejecutar tareas automáticas que aseguran la calidad del código y los mensajes de commit.

Husky se instala como una dependencia de desarrollo y se configura en el archivo `package.json` con el script `"prepare": "husky install"`, lo que significa que se activa automáticamente al instalar las dependencias.

## ¿Qué hace Husky en este proyecto?

En este proyecto, Husky ejecuta dos tipos principales de hooks:

1. **Pre-commit hook**: Antes de que se realice un commit, Husky ejecuta `lint-staged`. Esto significa que:
   - Se revisan solo los archivos que están en el staging area (los que has agregado con `git add`).
   - Se ejecuta ESLint para corregir errores de linting en archivos `.js`, `.jsx`, `.ts` y `.tsx`.
   - Se ejecuta Prettier para formatear automáticamente archivos `.js`, `.jsx`, `.ts`, `.tsx`, `.json`, `.css` y `.md`.

2. **Commit-msg hook**: Después de escribir el mensaje del commit, Husky ejecuta `commitlint` con la configuración convencional. Esto valida que el mensaje del commit siga el formato estándar de **Conventional Commits**.

## ¿Qué previene Husky? (¿Qué no puedes hacer?)

Husky **impide** que hagas commits que no cumplan con los estándares de calidad del proyecto. Específicamente:

- **No puedes commitear código con errores de linting**: Si ESLint encuentra problemas (como variables no usadas, sintaxis incorrecta o reglas de estilo violadas), el commit se detendrá hasta que los corrijas.
- **No puedes commitear código mal formateado**: Prettier reformateará automáticamente el código, pero si hay conflictos o errores, el commit fallará.
- **No puedes usar mensajes de commit inválidos**: Si el mensaje no sigue el formato de Conventional Commits (ej. "feat: agregar nueva funcionalidad" o "fix: corregir bug"), `commitlint` rechazará el commit con un error.

En resumen, Husky actúa como un "guardia" que bloquea commits de baja calidad, forzando a los desarrolladores a mantener el código limpio y los mensajes descriptivos.

## ¿Cómo deberías proceder para evitar errores?

Para que tus commits pasen sin problemas, sigue estos pasos:

### 1. **Antes de commitear, prepara tu código**

- Ejecuta los comandos de linting y formateo manualmente para verificar:
  ```bash
  npm run lint:check  # Verifica si hay errores de linting
  npm run format:check  # Verifica si el código está formateado
  ```
  Si hay errores, corrígelos con:
  ```bash
  npm run lint  # Corrige errores de linting automáticamente
  npm run format  # Formatea el código automáticamente
  ```

### 2. **Agrega archivos al staging area**

- Usa `git add` para seleccionar los archivos que quieres commitear. Solo estos serán revisados por `lint-staged`.

### 3. **Escribe mensajes de commit siguiendo Conventional Commits**

- El formato debe ser: `<tipo>(<ámbito opcional>): <descripción>`
- Ejemplos válidos:
  - `feat: agregar funcionalidad de login`
  - `fix: corregir error en la validación de formularios`
  - `docs: actualizar documentación de la API`
  - `style: corregir indentación en componente Header`
  - `refactor: simplificar lógica en utils.js`
  - `test: agregar pruebas para el módulo de autenticación`
  - `chore: actualizar dependencias`
- Evita mensajes como "cambios" o "fix bug" – deben ser descriptivos y seguir el patrón.

### 4. **Haz el commit**

- Una vez que el código esté limpio y el mensaje sea válido, ejecuta:
  ```bash
  git commit -m "feat: descripción del cambio"
  ```
- Si `lint-staged` encuentra problemas, corrígelos y vuelve a intentar.
- Si `commitlint` rechaza el mensaje, edítalo con `git commit --amend` o usa un editor.

### 5. **Consejos adicionales**

- **Trabaja en ramas**: Usa ramas para desarrollar features y haz merge solo cuando todo esté limpio.
- **Ejecuta hooks manualmente si es necesario**: Si quieres probar los hooks sin commitear, puedes simular con `npx husky run pre-commit` o `npx husky run commit-msg`.
- **Si hay errores persistentes**: Revisa la configuración en `.eslintrc`, `prettier.config.js`, `commitlint.config.js` o `lint-staged` en `package.json`.
- **Desactiva temporalmente solo si es necesario**: En casos excepcionales (como commits de emergencia), puedes saltar hooks con `git commit --no-verify`, pero úsalo con cuidado.

Siguiendo estos pasos, evitarás errores y mantendrás el proyecto en un estado de alta calidad. Si tienes dudas, consulta la documentación de [Husky](https://typicode.github.io/husky/), [ESLint](https://eslint.org/), [Prettier](https://prettier.io/) o [Conventional Commits](https://www.conventionalcommits.org/).
