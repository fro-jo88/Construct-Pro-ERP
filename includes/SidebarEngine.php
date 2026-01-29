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
    }

    private function getMenuItems() {
        $role_items = $this->menus[$this->role] ?? [];
        $default_items = $this->menus['default'] ?? [];
        
        // Return merged list (role items first, then common items)
        return array_merge($role_items, $default_items);
    }

    public function render() {
        $items = $this->getMenuItems();
        $html = '<div class="nav-links-wrapper"><ul class="nav-links">';
        $html .= '<style>
            .nav-links li.active a {
                background: linear-gradient(90deg, rgba(255, 204, 0, 0.15) 0%, rgba(255, 204, 0, 0.05) 100%);
                color: var(--gold);
                border-left: 3px solid var(--gold);
                padding-left: calc(1rem - 3px);
                font-weight: 600;
            }
            .nav-links a i { margin-right: 10px; width: 20px; text-align: center; }
        </style>';

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
