<?php
function renderPageHeader($title, $breadcrumb = []) {
    $breadcrumbHtml = '';
    
    if (!empty($breadcrumb) && count($breadcrumb) > 1) {
        $breadcrumbHtml = '<nav class="page-header__breadcrumb-nav">';
        foreach ($breadcrumb as $index => $item) {
            $isLast = $index === count($breadcrumb) - 1;
            $class = $isLast ? 'page-header__breadcrumb-item--current' : 'page-header__breadcrumb-item--navigable';
            $breadcrumbHtml .= '<span class="page-header__breadcrumb-item ' . $class . '">' . htmlspecialchars($item) . '</span>';
            if (!$isLast) {
                $breadcrumbHtml .= '<span class="page-header__breadcrumb-separator">›</span>';
            }
        }
        $breadcrumbHtml .= '</nav>';
    }
    
    echo '
    <header id="page-header" class="page-header">
        <div class="page-header__content">
            ' . $breadcrumbHtml . '
            <div class="page-header__title-section">
                <h1 class="page-header__title">' . htmlspecialchars($title) . '</h1>
            </div>
        </div>
        <div class="page-header__actions" id="page-header-actions"></div>
    </header>';
}
?>