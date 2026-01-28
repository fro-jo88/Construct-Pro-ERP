<?php
// includes/SidebarEngine.php

class SidebarEngine {
    private $role;
    private $menus;
    private $current_module;

    public function __construct($role) {
        $this->role = strtoupper($role); // Normalize role to UPPERCASE
        $this->menus = require __DIR__ . '/../config/role_menus.php';
        
        // Determine current module for active highlighting
        $this->current_module = $_GET['module'] ?? 'dashboard';
        
        // Handle alias roles (e.g., finance_head -> finance)
        $this->normalizeRole();
    }

    private function normalizeRole() {
        // Direct mapping is now used in role_menus.php
        // Only ensuring uppercase just in case
        $this->role = strtoupper($this->role);
    }

    private function getMenuItems() {
        if (array_key_exists($this->role, $this->menus)) {
            return $this->menus[$this->role];
        }
        return $this->menus['default'];
    }

    public function render() {
        $items = $this->getMenuItems();
        $html = '<div class="nav-links-wrapper"><ul class="nav-links">';

        foreach ($items as $item) {
            $activeClass = $this->isActive($item['url']) ? 'active' : '';
            $icon = $item['icon'];
            $label = $item['label'];
            $url = $item['url'];
            
            $html .= "<li class='$activeClass'><a href='$url'><i class='fas fa-$icon'></i> $label</a></li>";
        }

        $html .= '</ul></div>';

        return $html;
    }

    private function isActive($url) {
        // Check if the current URL param matches
        // Url format: index.php?module=xyz
        if (strpos($url, 'module=') !== false) {
             parse_str(parse_url($url, PHP_URL_QUERY), $params);
             if (isset($params['module'])) {
                 // Simple substring check to keep active for sub-pages
                 // e.g. 'hr/employees' matches 'hr/employees_edit'
                 return strpos($this->current_module, $params['module']) === 0;
             }
        }
        return false;
    }
}
?>
