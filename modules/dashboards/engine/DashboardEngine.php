<?php
// modules/dashboards/engine/DashboardEngine.php

require_once __DIR__ . '/WidgetRegistry.php';

class DashboardEngine {
    private $role_code;
    private $user_id;
    private $map;

    public function __construct($role_code, $user_id) {
        $this->role_code = $role_code;
        $this->user_id = $user_id;
        $this->map = require __DIR__ . '/RoleWidgetMap.php';
    }

    public function render() {
        $config = $this->map[$this->role_code] ?? $this->map['default'];
        
        echo '<div class="dashboard-container">';
        echo '<div class="dashboard-grid">';
        
        foreach ($config['widgets'] as $wConfig) {
            $widgetId = $wConfig['id'];
            $size = $wConfig['size'] ?? 'full';
            $params = $wConfig['params'] ?? [];
            
            // Add global context to params
            $params['user_id'] = $this->user_id;
            $params['role_code'] = $this->role_code;

            echo "<div class='widget-wrapper widget-size-$size'>";
            echo WidgetRegistry::render($widgetId, [], $params);
            echo "</div>";
        }
        
        echo '</div>';
        echo '</div>';
        
        $this->renderStyles();
    }

    private function renderStyles() {
        ?>
        <style>
            .dashboard-container {
                padding: 1rem;
                max-width: 1600px;
                margin: 0 auto;
                height: 100%;
            }
            .dashboard-grid {
                display: grid;
                grid-template-columns: repeat(4, 1fr);
                grid-auto-rows: minmax(140px, auto);
                gap: 1.5rem;
            }
            .widget-wrapper {
                height: 100%;
            }
            .widget-size-full { grid-column: span 4; }
            .widget-size-half { grid-column: span 2; }
            .widget-size-quarter { grid-column: span 1; }
            
            @media (max-width: 1200px) {
                .dashboard-grid { grid-template-columns: repeat(2, 1fr); }
                .widget-size-quarter { grid-column: span 1; }
                .widget-size-half, .widget-size-full { grid-column: span 2; }
            }
            
            @media (max-width: 768px) {
                .dashboard-grid { grid-template-columns: 1fr; }
                .widget-size-quarter, .widget-size-half, .widget-size-full { grid-column: span 1; }
            }
            
            .glass-card {
                background: linear-gradient(135deg, rgba(255,255,255,0.05) 0%, rgba(255,255,255,0.02) 100%);
                border: 1px solid rgba(255,255,255,0.1);
                border-radius: 12px;
                padding: 1.25rem;
                height: 100%;
                display: flex;
                flex-direction: column;
                transition: transform 0.3s ease, border-color 0.3s ease;
            }
            .glass-card:hover {
                transform: translateY(-5px);
                border-color: rgba(255, 204, 0, 0.3);
                background: linear-gradient(135deg, rgba(255,255,255,0.08) 0%, rgba(255,255,255,0.03) 100%);
            }
            .widget-header h3 {
                font-size: 0.8rem;
                text-transform: uppercase;
                letter-spacing: 1px;
                color: var(--text-dim);
                margin: 0;
            }
            .widget-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 1rem;
            }
            .text-gold { color: var(--gold); }
            
            /* Enhanced Data Table */
            .data-table {
                width: 100%;
                border-collapse: collapse;
                margin-top: 0.5rem;
            }
            .data-table th {
                text-align: left;
                font-size: 0.75rem;
                padding: 10px;
                color: var(--gold);
                border-bottom: 2px solid rgba(255,204,0,0.1);
                text-transform: uppercase;
                letter-spacing: 0.5px;
            }
            .data-table td {
                padding: 12px 10px;
                font-size: 0.85rem;
                border-bottom: 1px solid rgba(255,255,255,0.05);
            }
            .status-badge {
                padding: 4px 8px;
                border-radius: 4px;
                font-size: 0.7rem;
                font-weight: 600;
            }
            .status-badge.active, .status-badge.won, .status-badge.approved { background: rgba(0, 255, 100, 0.1); color: #00ff64; }
            .status-badge.pending { background: rgba(255, 204, 0, 0.1); color: var(--gold); }
            
            /* Timeline & Activity Feed */
            .activity-feed .feed-item {
                padding: 10px 0;
                border-bottom: 1px solid rgba(255,255,255,0.03);
            }
            .activity-feed .feed-item:last-child { border-bottom: none; }
        </style>
        <?php
    }
}
