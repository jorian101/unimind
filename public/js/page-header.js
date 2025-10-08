/**
 * Page Header Component JavaScript
 * Maneja la navegación breadcrumb y las funcionalidades específicas del header de página
 */

class PageHeaderComponent {
  constructor() {
    this.pageHeader = document.getElementById("page-header");
    this.breadcrumbItems = document.querySelectorAll(
      ".page-breadcrumb-item.navigable",
    );

    this.init();
  }

  init() {
    this.setupBreadcrumbNavigation();
    this.setupBreadcrumbHistory();
  }

  // Configurar navegación del breadcrumb
  setupBreadcrumbNavigation() {
    this.breadcrumbItems.forEach((item) => {
      item.addEventListener("click", () => {
        this.navigateToBreadcrumb(item);
      });
    });
  }

  // Navegar cuando se hace clic en un breadcrumb
  navigateToBreadcrumb(item) {
    const breadcrumbPage = item.dataset.breadcrumbPage;
    const breadcrumbIndex = parseInt(item.dataset.breadcrumbIndex);
    const currentRole =
      new URLSearchParams(window.location.search).get("role") || "estudiante";

    // Mapear breadcrumbs a páginas reales
    const breadcrumbPageMap = {
      inicio: "dashboard",
      dashboard: "dashboard",
      "resumen-de-clases": "clases",
      "reportes-de-clases": "reportes",
      usuarios: "usuarios",
      reportes: "reportes",
      configuración: "config",
      "tests-y-evaluaciones": "tests",
      recomendaciones: "recomendaciones",
      "calendario-de-citas": "calendario",
      tests: "tests",
      clases: "clases",
      calendario: "calendario",
    };

    const targetPage = breadcrumbPageMap[breadcrumbPage] || "dashboard";

    // Preparado para navegación multi-nivel (futuro)
    if (breadcrumbIndex === 0) {
      // Navegación a inicio/dashboard
      this.navigateToPage(targetPage, currentRole);
    } else {
      // Navegación a páginas intermedias (preparado para futuro)
      this.navigateToPage(targetPage, currentRole, {
        fromBreadcrumb: true,
        breadcrumbIndex: breadcrumbIndex,
      });
    }
  }

  // Configurar historial de navegación breadcrumb
  setupBreadcrumbHistory() {
    // Almacenar historial de navegación para breadcrumbs dinámicos
    this.navigationHistory = JSON.parse(
      sessionStorage.getItem("unimind_nav_history") || "[]",
    );
  }

  // Agregar página al historial de navegación
  addToNavigationHistory(page, title, role, metadata = {}) {
    const historyItem = {
      page,
      title,
      role,
      metadata,
      timestamp: Date.now(),
    };

    // Evitar duplicados consecutivos
    const lastItem = this.navigationHistory[this.navigationHistory.length - 1];
    if (!lastItem || lastItem.page !== page || lastItem.role !== role) {
      this.navigationHistory.push(historyItem);

      // Limitar historial a 10 elementos
      if (this.navigationHistory.length > 10) {
        this.navigationHistory.shift();
      }

      sessionStorage.setItem(
        "unimind_nav_history",
        JSON.stringify(this.navigationHistory),
      );
    }
  }

  // Actualizar props del page header dinámicamente
  updatePageHeaderProps(newProps) {
    if (newProps.title) {
      const titleElement = this.pageHeader?.querySelector(".page-title");
      if (titleElement) {
        titleElement.textContent = newProps.title;
      }
    }

    if (newProps.subtitle) {
      let subtitleElement = this.pageHeader?.querySelector(".page-subtitle");
      if (!subtitleElement && newProps.subtitle) {
        subtitleElement = document.createElement("p");
        subtitleElement.className = "page-subtitle";
        const titleSection = this.pageHeader?.querySelector(
          ".page-title-section",
        );
        titleSection?.appendChild(subtitleElement);
      }
      if (subtitleElement) {
        subtitleElement.textContent = newProps.subtitle;
      }
    }

    if (newProps.breadcrumb) {
      this.updateBreadcrumb(newProps.breadcrumb);
    }

    // Agregar al historial de navegación
    const currentPage =
      new URLSearchParams(window.location.search).get("page") || "dashboard";
    const currentRole =
      new URLSearchParams(window.location.search).get("role") || "estudiante";

    this.addToNavigationHistory(
      currentPage,
      newProps.title || "Página",
      currentRole,
      newProps.metadata || {},
    );
  }

  // Navegar a una página específica
  navigateToPage(page, role, options = {}) {
    const url = new URL(window.location);
    url.searchParams.set("page", page);
    url.searchParams.set("role", role);

    // Agregar parámetros adicionales si existen
    if (options.fromBreadcrumb) {
      url.searchParams.set("from_breadcrumb", "true");
    }

    window.location.href = url.toString();
  }

  // Actualizar breadcrumb dinámicamente
  updateBreadcrumb(breadcrumbArray) {
    const navElement = this.pageHeader?.querySelector(".page-breadcrumb-nav");
    if (!navElement) return;

    navElement.innerHTML = "";

    breadcrumbArray.forEach((item, index) => {
      const span = document.createElement("span");
      const isLast = index === breadcrumbArray.length - 1;
      span.className = `page-breadcrumb-item ${isLast ? "current" : "navigable"}`;
      span.textContent = item;
      span.dataset.breadcrumbIndex = index;
      span.dataset.breadcrumbPage = item.toLowerCase().replace(/\s+/g, "-");

      if (!isLast) {
        span.addEventListener("click", () =>
          this.navigateToBreadcrumb(span, index),
        );
      }

      navElement.appendChild(span);

      if (!isLast) {
        const separator = document.createElement("span");
        separator.className = "page-breadcrumb-separator";
        separator.textContent = "›";
        navElement.appendChild(separator);
      }
    });
  }
}

// Función para inicializar el page header
function initializePageHeader() {
  document.addEventListener("DOMContentLoaded", () => {
    if (document.getElementById("page-header")) {
      const pageHeaderComponent = new PageHeaderComponent();

      // Exponer la instancia globalmente
      window.pageHeaderComponent = pageHeaderComponent;
    }
  });
}

// Función helper para uso desde PHP
function setPageHeaderProps(props) {
  if (window.pageHeaderComponent) {
    window.pageHeaderComponent.updatePageHeaderProps(props);
  } else {
    // Si aún no está inicializado, almacenar para cuando esté listo
    window.pendingPageHeaderProps = props;
    document.addEventListener("DOMContentLoaded", () => {
      if (window.pendingPageHeaderProps && window.pageHeaderComponent) {
        window.pageHeaderComponent.updatePageHeaderProps(
          window.pendingPageHeaderProps,
        );
        delete window.pendingPageHeaderProps;
      }
    });
  }
}

// Auto-inicializar
initializePageHeader();

// Exponer funciones globalmente
window.setPageHeaderProps = setPageHeaderProps;
window.initializePageHeader = initializePageHeader;
