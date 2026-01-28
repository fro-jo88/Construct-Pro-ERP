<?php
// includes/DashboardEngine.php

class DashboardEngine {
    private $role;
    private $user_id;

    public function __construct($role, $user_id) {
        $this->role = $role;
        $this->user_id = $user_id;
    }

    public function getWidgets() {
        $widgets = [];
        
        // Context-Aware Widgets
        switch ($this->role) {
            case 'GM':
            case 'Executive Board':
                $widgets = array_merge($widgets, $this->getExecutiveWidgets());
                break;
            case 'HR':
                $widgets = array_merge($widgets, $this->getHRWidgets());
                break;
            case 'Finance':
                $widgets = array_merge($widgets, $this->getFinanceWidgets());
                break;
            case 'Foreman':
            case 'Site Manager':
                $widgets = array_merge($widgets, $this->getSiteOperationWidgets());
                break;
            case 'Planning':
            case 'Quantity Surveyor':
                $widgets = array_merge($widgets, $this->getEngineeringWidgets());
                break;
            case 'Store Manager':
            case 'Store Keeper':
                $widgets = array_merge($widgets, $this->getInventoryWidgets());
                break;
            case 'Procurement':
                $widgets = array_merge($widgets, $this->getProcurementWidgets());
                break;
            case 'Logistics':
            case 'Maintenance':
                $widgets = array_merge($widgets, $this->getFleetWidgets());
                break;
            case 'Audit':
            case 'Quality Control':
            case 'Safety Officer':
                $widgets = array_merge($widgets, $this->getAuditWidgets());
                break;
            case 'Bidding Technical':
            case 'Bidding Finance':
            case 'Legal Counsel':
                $widgets = array_merge($widgets, $this->getTenderWidgets());
                break;
            default:
                $widgets[] = ['type' => 'stat', 'title' => 'System Access', 'value' => 'Basic User'];
        }

        return $widgets;
    }

    private function getExecutiveWidgets() {
        return [
            ['type' => 'kpi', 'title' => 'Global Revenue', 'value' => '$4.2M', 'trend' => '+12%'],
            ['type' => 'chart', 'title' => 'Project Progress vs Budget', 'id' => 'exec_chart'],
            ['type' => 'alert', 'title' => 'Priority Approvals', 'count' => 8, 'color' => 'gold'],
            ['type' => 'list', 'title' => 'Critical Variance Sites', 'items' => ['Site A: +15% Cost', 'Site B: -5% Delay']]
        ];
    }

    private function getHRWidgets() {
        return [
            ['type' => 'stat', 'title' => 'Active Employees', 'value' => 342],
            ['type' => 'alert', 'title' => 'Pending Onboarding', 'count' => 4],
            ['type' => 'kpi', 'title' => 'Payroll Health', 'value' => 'On Time'],
            ['type' => 'list', 'title' => 'Leave Requests', 'items' => ['John Doe (Annual)', 'Jane Smith (Sick)']]
        ];
    }

    private function getFinanceWidgets() {
        return [
            ['type' => 'kpi', 'title' => 'Cash on Hand', 'value' => '$850k'],
            ['type' => 'chart', 'title' => 'Weekly Expenses', 'id' => 'finance_chart'],
            ['type' => 'alert', 'title' => 'Pending Vouchers', 'count' => 15],
            ['type' => 'list', 'title' => 'Awaiting Finance Approval', 'items' => ['Purchase #88', 'Invoice #92']]
        ];
    }

    private function getSiteOperationWidgets() {
        return [
            ['type' => 'kpi', 'title' => 'Site Progress', 'value' => '72%'],
            ['type' => 'alert', 'title' => 'Material Shortage', 'count' => 2, 'color' => 'red'],
            ['type' => 'list', 'title' => 'Today\'s Task List', 'items' => ['Pouring Deck 4', 'Arrival: Reinforcement steel']],
            ['type' => 'stat', 'title' => 'Active Crew', 'value' => 18]
        ];
    }

    private function getEngineeringWidgets() {
        return [
            ['type' => 'list', 'title' => 'Upcoming Milestones', 'items' => ['Milestone #2: 3 Days Left', 'Milestone #3: Planning']],
            ['type' => 'chart', 'title' => 'Gantt Accuracy', 'id' => 'planning_chart'],
            ['type' => 'alert', 'title' => 'New Schedule Revisions', 'count' => 3]
        ];
    }

    private function getInventoryWidgets() {
        return [
            ['type' => 'alert', 'title' => 'Low Stock Alerts', 'count' => 12, 'color' => 'red'],
            ['type' => 'stat', 'title' => 'Total SKUs', 'value' => 1240],
            ['type' => 'list', 'title' => 'Pending Transfers', 'items' => ['Cement: Main Store -> Site A', 'Diesel: Tank 1 -> Trip 88']]
        ];
    }

    private function getProcurementWidgets() {
        return [
            ['type' => 'alert', 'title' => 'Pending PRs', 'count' => 7],
            ['type' => 'kpi', 'title' => 'LPO Cycle Time', 'value' => '1.2 Days'],
            ['type' => 'list', 'title' => 'Awaiting Delivery', 'items' => ['Concrete Mix #4', 'Steel Beam #12']]
        ];
    }

    private function getFleetWidgets() {
        return [
            ['type' => 'stat', 'title' => 'Vehicles on Road', 'value' => '8 / 12'],
            ['type' => 'kpi', 'title' => 'Fuel Efficiency', 'value' => '12 km/L'],
            ['type' => 'alert', 'title' => 'Maintenance Due', 'count' => 1]
        ];
    }

    private function getAuditWidgets() {
        return [
            ['type' => 'kpi', 'title' => 'System Variance', 'value' => '4.2%'],
            ['type' => 'list', 'title' => 'High Risk Findings', 'items' => ['Steel Quality Site C', 'Expense Match Gap #34']],
            ['type' => 'chart', 'title' => 'Compliance Trend', 'id' => 'audit_chart']
        ];
    }

    private function getTenderWidgets() {
        return [
            ['type' => 'stat', 'title' => 'Active Bids', 'value' => 4],
            ['type' => 'kpi', 'title' => 'Bid Win Rate', 'value' => '65%'],
            ['type' => 'alert', 'title' => 'Upcoming Deadlines', 'count' => 2, 'color' => 'gold']
        ];
    }

    public function renderWidget($widget) {
        $colorClass = isset($widget['color']) ? "widget-" . $widget['color'] : "";
        $html = "<div class='widget glass-card $colorClass'>";
        $html .= "<div class='widget-header'>";
        $html .= "<h3>" . htmlspecialchars($widget['title']) . "</h3>";
        if (isset($widget['trend'])) {
            $html .= "<span class='trend'>" . $widget['trend'] . "</span>";
        }
        $html .= "</div>";
        
        $html .= "<div class='widget-content'>";
        if ($widget['type'] === 'kpi' || $widget['type'] === 'stat') {
            $html .= "<div class='value'>" . htmlspecialchars($widget['value']) . "</div>";
        } elseif ($widget['type'] === 'alert') {
            $html .= "<div class='alert-visual'>";
            $html .= "<span class='alert-count'>" . ($widget['count'] ?? '!') . "</span>";
            $html .= "</div>";
        } elseif ($widget['type'] === 'list') {
            $html .= "<ul class='widget-list'>";
            foreach (($widget['items'] ?? []) as $item) {
                $html .= "<li>" . htmlspecialchars($item) . "</li>";
            }
            $html .= "</ul>";
        } elseif ($widget['type'] === 'chart') {
            $html .= "<canvas id='" . $widget['id'] . "' height='150'></canvas>";
        }
        $html .= "</div>";

        $html .= "</div>";
        return $html;
    }
}
?>
