# GM Role - Quick Reference Guide

## 🎯 GM Role Purpose
**General Manager (GM)** is the **central oversight and approval authority** for the entire ERP system.

---

## 📋 What GM Can Do

### ✅ **Approvals**
- Approve/Reject employee hiring
- Approve/Reject leave requests
- Approve/Reject budgets
- Approve/Reject material requests
- Approve/Reject master schedules
- Final bid decisions (Won/Loss)

### ✅ **Monitoring**
- View all project progress
- Monitor budget utilization
- Track inventory levels
- Review site reports
- Monitor workforce status

### ✅ **Auditing**
- View system-wide activity logs
- Access audit trail
- Review approval history
- Monitor risk scores
- Track compliance metrics

---

## ❌ What GM Cannot Do

- ❌ Create finance records (budgets, expenses)
- ❌ Create HR records (employees, payroll)
- ❌ Create planning records (schedules, resources)
- ❌ Modify department data directly
- ❌ Delete operational records

**GM reads from departments, approves/rejects, but does NOT create or edit.**

---

## 🗂️ GM Menu Navigation

| Menu Item | Purpose | Access Level |
|-----------|---------|--------------|
| **Executive Dashboard** | KPI overview | View |
| **Pending Approvals** | Unified approval center | Approve/Reject |
| **Bid Review** | Tender final decisions | Approve/Reject |
| **Project Oversight** | Project progress monitoring | View |
| **Finance Oversight** | Budget & expense tracking | View |
| **Inventory Oversight** | Material & stock monitoring | View |
| **Site Reports** | Site operations review | View |
| **Audit Reports** | Compliance & risk monitoring | View |
| **System Logs** | Activity & decision history | View |

---

## 🔄 Typical GM Workflows

### **1. Approve Employee Hire**
1. Navigate to **Pending Approvals**
2. Find employee in HR section
3. Review details (position, salary, department)
4. Click **Approve** or **Reject**
5. Enter reason/notes
6. Confirm decision
7. System updates employee status
8. Action logged in approval history

### **2. Approve Budget**
1. Navigate to **Pending Approvals**
2. Find budget in Finance section
3. Review project, amount, breakdown
4. Click **Approve** or **Reject**
5. Enter justification
6. Confirm decision
7. Budget becomes active/rejected
8. Finance team notified

### **3. Monitor Project Progress**
1. Navigate to **Project Oversight**
2. View all active projects
3. Check progress percentages
4. Review risk scores
5. Identify delayed projects
6. Take corrective action if needed

### **4. Review System Logs**
1. Navigate to **System Logs**
2. View approval history
3. Check activity stream
4. Filter by module/user
5. Export for compliance

---

## 🔑 Key GMManager Methods

### **For Developers:**

```php
// Get all pending approvals
$approvals = GMManager::getPendingApprovals();

// Process approval decision
GMManager::processApproval($module, $ref_id, $decision, $reason, $user_id);

// Get system logs
$logs = GMManager::getSystemLogs(100);

// Get audit trail
$trail = GMManager::getAuditTrail('FINANCE', 50);

// Get executive KPIs
$kpis = GMManager::getExecutiveKPIs();

// Get finance overview
$finance = GMManager::getFinanceOverview();

// Get HR overview
$hr = GMManager::getHROverview();

// Get planning overview
$planning = GMManager::getPlanningOverview();

// Get project oversight
$projects = GMManager::getProjectOversight();

// Get inventory oversight
$inventory = GMManager::getInventoryOversight();
```

---

## 📊 Dashboard KPIs

The GM Executive Dashboard shows:

- **Active Projects** - Number of ongoing projects
- **Active Bids** - Tenders in progress
- **Workforce Count** - Total active employees
- **Pending Approvals** - Items awaiting GM decision
- **Pending Procurement** - Purchase requests in queue
- **Budget Utilization %** - Spent vs allocated
- **Cash Exposure** - Total active budget commitments
- **Critical Incidents** - Unacknowledged emergencies

---

## 🔐 Security & Permissions

### **GM Role Code:** `GM`

### **Access Control:**
```php
// All GM modules require GM role
AuthManager::requireRole('GM');
```

### **Data Access Pattern:**
```php
// ✅ CORRECT: Read from department managers
$finance = new FinanceManager($db);
$budgets = $finance->getAllBudgets(); // Read-only

// ❌ WRONG: Direct data manipulation
$db->query("UPDATE budgets SET amount = ?"); // NO!
```

---

## 📝 Approval Decision Types

| Decision | Effect | Use Case |
|----------|--------|----------|
| `approved` | Activates request | Standard approval |
| `rejected` | Denies request | Not meeting criteria |
| `pre_approved` | Conditional approval | Bids (before final) |
| `won` | Bid won | Final bid decision |
| `loss` | Bid lost | Final bid decision |

---

## 🚨 Important Notes

1. **All GM actions are logged** - Full audit trail maintained
2. **Approvals are immutable** - Cannot be undone (new entry required)
3. **GM cannot bypass workflows** - Must follow approval chain
4. **Read-only access to departments** - Cannot modify source data
5. **Centralized decision point** - All major decisions flow through GM

---

## 🛠️ Troubleshooting

### **"Module not found" errors:**
- Ensure you're using correct module paths
- Check `config/role_menus.php` for valid URLs
- Use `gm/approvals` instead of `gm/hr_approvals`, `gm/leaves`, etc.

### **"Undefined method" errors:**
- Ensure `GMManager.php` is up to date
- Check method exists: `getSystemLogs()`, `getPendingApprovals()`, etc.
- Clear any PHP opcode cache

### **No pending approvals showing:**
- Check database for records with `status = 'pending'`
- Verify `gm_approval_status` columns exist
- Run `sql/gm_oversight_extensions.sql` to add missing columns

---

## 📞 Support

For GM role issues:
1. Check `docs/gm_architecture_fix.md` for detailed architecture
2. Review `includes/GMManager.php` for available methods
3. Verify database schema with `sql/gm_oversight_extensions.sql`

---

**Last Updated:** 2026-01-29  
**Version:** 2.0 (Restructured Architecture)
