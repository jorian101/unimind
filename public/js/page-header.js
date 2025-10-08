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

  setupBreadcrumbNavigation() {
    this.breadcrumbItems.forEach((item) => {
      item.addEventListener("click", () => {
        this.navigateToBreadcrumb(item);
      });
    });
  }

  navigateToBreadcrumb(item) {
    const breadcrumbPage = item.dataset.breadcrumbPage;
    const breadcrumbIndex = parseInt(item.dataset.breadcrumbIndex);
    const currentRole =
      new URLSearchParams(window.location.search).get("role") || "estudiante";

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

    if (breadcrumbIndex === 0) {
      this.navigateToPage(targetPage, currentRole);
    } else {
      this.navigateToPage(targetPage, currentRole, {
        fromBreadcrumb: true,
        breadcrumbIndex: breadcrumbIndex,
      });
    }
  }

  setupBreadcrumbHistory() {
    this.navigationHistory = JSON.parse(
      sessionStorage.getItem("unimind_nav_history") || "[]",
    );
  }

  addToNavigationHistory(page, title, role, metadata = {}) {
    const historyItem = {
      page,
      title,
      role,
      metadata,
      timestamp: Date.now(),
    };

    const lastItem = this.navigationHistory[this.navigationHistory.length - 1];
    if (!lastItem || lastItem.page !== page || lastItem.role !== role) {
      this.navigationHistory.push(historyItem);

      if (this.navigationHistory.length > 10) {
        this.navigationHistory.shift();
      }

      sessionStorage.setItem(
        "unimind_nav_history",
        JSON.stringify(this.navigationHistory),
      );
    }
  }

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

  navigateToPage(page, role, options = {}) {
    const url = new URL(window.location);
    url.searchParams.set("page", page);
    url.searchParams.set("role", role);

    if (options.fromBreadcrumb) {
      url.searchParams.set("from_breadcrumb", "true");
    }

    window.location.href = url.toString();
  }

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

function initializePageHeader() {
  document.addEventListener("DOMContentLoaded", () => {
    if (document.getElementById("page-header")) {
      const pageHeaderComponent = new PageHeaderComponent();

      window.pageHeaderComponent = pageHeaderComponent;
    }
  });
}

function setPageHeaderProps(props) {
  if (window.pageHeaderComponent) {
    window.pageHeaderComponent.updatePageHeaderProps(props);
  } else {
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

initializePageHeader();

window.setPageHeaderProps = setPageHeaderProps;
window.initializePageHeader = initializePageHeader;
