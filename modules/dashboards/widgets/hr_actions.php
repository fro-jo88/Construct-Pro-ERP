<?php
// modules/dashboards/widgets/hr_actions.php
?>
<div class="glass-card hr-actions-widget">
    <div class="widget-header">
        <h3><i class="fas fa-bolt text-gold"></i> HR Quick Actions</h3>
    </div>
    <div class="widget-content">
        <div class="actions-grid">
            <button onclick="location.href='main.php?module=hr/tenders&action=new'" class="action-btn">
                <i class="fas fa-file-signature"></i>
                <span>New Bid</span>
            </button>
            <button onclick="location.href='main.php?module=hr/sites&action=new'" class="action-btn">
                <i class="fas fa-plus-square"></i>
                <span>New Site</span>
            </button>
            <button onclick="location.href='main.php?module=hr/add_employee'" class="action-btn">
                <i class="fas fa-user-plus"></i>
                <span>Onboarding</span>
            </button>
            <button onclick="location.href='main.php?module=hr/payroll'" class="action-btn">
                <i class="fas fa-calculator"></i>
                <span>Run Payroll</span>
            </button>
            <button onclick="location.href='main.php?module=hr/leaves'" class="action-btn">
                <i class="fas fa-calendar-check"></i>
                <span>Leave Review</span>
            </button>
            <button onclick="location.href='main.php?module=hr/messages'" class="action-btn">
                <i class="fas fa-envelope"></i>
                <span>Messaging</span>
            </button>
        </div>
    </div>
</div>

<style>
.actions-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 10px;
    margin-top: 10px;
}
.action-btn {
    background: rgba(255, 255, 255, 0.03);
    border: 1px solid rgba(255, 255, 255, 0.05);
    border-radius: 8px;
    padding: 15px 5px;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
    color: var(--text-dim);
    cursor: pointer;
    transition: all 0.2s ease;
}
.action-btn i {
    font-size: 1.2rem;
    color: var(--gold);
}
.action-btn span {
    font-size: 0.7rem;
    font-weight: 600;
    text-transform: uppercase;
}
.action-btn:hover {
    background: rgba(255, 204, 0, 0.1);
    border-color: var(--gold);
    color: #fff;
    transform: translateY(-2px);
}
</style>
