-- sql/seed.sql
USE wechecha_con;

-- Roles
INSERT INTO roles (role_name, description) VALUES 
('GM', 'General Manager - Full System Access'),
('HR', 'Human Resources Manager'),
('Finance', 'Finance Manager'),
('Planning', 'Planning & Scheduling Engineer'),
('Bidding Technical', 'Tender Technical Team'),
('Bidding Finance', 'Tender Finance Team'),
('Project Manager', 'Overall Project Oversight'),
('Site Manager', 'Site Specific Management'),
('Foreman', 'Site Operations Leader'),
('Store Manager', 'Multi-Store Inventory Manager'),
('Store Keeper', 'Single Store Inventory Keeper'),
('Audit', 'Construction Auditor'),
('Logistics', 'Transport & Fleet Manager'),
('Procurement', 'Purchasing & Vendor Manager'),
('Client', 'External Client View'),
('Safety Officer', 'Site Safety & Compliance'),
('Quantity Surveyor', 'Project Costing & Measurement'),
('IT Admin', 'System Maintenance'),
('Executive Board', 'High-level reporting'),
('Legal Counsel', 'Contract & Tender Legal Review'),
('Quality Control', 'Material & Build Quality Audit'),
('Maintenance', 'Vehicle & Equipment Upkeep'),
('Operations', 'General Project Operations');

-- Permissions (Basic set)
INSERT INTO permissions (perm_key, description) VALUES 
('view_dashboard', 'Ability to view role dashboard'),
('approve_payroll', 'Ability to approve employee payroll'),
('manage_tenders', 'Create and edit tenders'),
('approve_finance', 'Final financial approval'),
('view_audit_logs', 'Read-only access to audit logs');

-- Default User (GM)
-- Password 'admin123' hashed with Bcrypt
INSERT INTO users (username, password, email, role_id, status) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'gm@wechecha.com', 1, 'active');
