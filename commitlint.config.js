module.exports = {
  extends: ["@commitlint/config-conventional"],
  rules: {
    "type-enum": [
      2,
      "always",
      [
        "feat", // Nueva funcionalidad
        "fix", // Corrección de bugs
        "docs", // Documentación
        "style", // Cambios de estilo (formato, etc)
        "refactor", // Refactoring de código
        "test", // Agregar o modificar tests
        "chore", // Tareas de mantenimiento
        "perf", // Mejoras de rendimiento
        "ci", // Cambios en CI/CD
        "build", // Cambios en el sistema de build
        "revert", // Revertir commits
      ],
    ],
    "subject-case": [2, "never", ["start-case", "pascal-case", "upper-case"]],
    "subject-empty": [2, "never"],
    "subject-full-stop": [2, "never", "."],
    "header-max-length": [2, "always", 72],
  },
};
