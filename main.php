<?php
// main.php
require_once 'config/config.php';
require_once 'includes/AuthManager.php';

if (!AuthManager::isLoggedIn()) {
    header("Location: index.php");
    exit();
}

$role = $_SESSION['role'];
$user_id = $_SESSION['user_id'];

// LANDING LOGIC: If no module specified, go to role-specific dashboard
if (!isset($_GET['module'])) {
    $role_code = $_SESSION['role_code'] ?? 'default';
    header("Location: main.php?module=dashboards/roles/" . $role_code);
    exit();
}

$role_code = $_SESSION['role_code'];

// Fetch User Profile for Top Bar
require_once 'includes/Database.php';
$db_profile = Database::getInstance();
$u_pf = $db_profile->prepare("SELECT full_name, profile_photo FROM users WHERE id = ?");
$u_pf->execute([$user_id]);
$currentUser = $u_pf->fetch();
$img_src = !empty($currentUser['profile_photo']) ? "assets/uploads/profiles/" . $currentUser['profile_photo'] : "assets/img/default-avatar.png";
$displayName = !empty($currentUser['full_name']) ? $currentUser['full_name'] : $_SESSION['username'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?> - Dashboard</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <link rel="stylesheet" href="assets/css/hr_custom.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        /* Notification UI Refinements */
        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: rgba(0,0,0,0.1); border-radius: 10px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 10px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: rgba(255,255,255,0.2); }
        
        #notif-dropdown {
            animation: slideDown 0.3s cubic-bezier(0.165, 0.84, 0.44, 1);
            transform-origin: top right;
        }
        @keyframes slideDown {
            from { opacity: 0; transform: translateY(10px) scale(0.95); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }
        
        .notif-item:hover {
            background: rgba(255, 204, 0, 0.03) !important;
        }
        .notif-item::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 3px;
            background: transparent;
            transition: all 0.2s;
        }
        .notif-item:hover::before {
            background: var(--gold);
        }
        .dropdown-item:hover {
            background: rgba(255, 204, 0, 0.1) !important;
        }
        .dropdown-item.logout:hover {
            background: rgba(255, 68, 68, 0.1) !important;
        }
    </style>
</head>
<body class="dashboard-body">
    <?php include 'includes/header.php'; ?>
    <nav class="sidebar glass-card">
        <h2 class="brand-small">WECHECHA</h2>
        
        <?php
        require_once 'includes/SidebarEngine.php';
        $sidebar = new SidebarEngine($role_code);
        echo $sidebar->render();
        ?>

        <div class="sidebar-footer">
            <h2 class="brand-small" style="font-size: 0.8rem; opacity: 0.3; text-align: center;">CP ERP v2.0</h2>
        </div>
    </nav>

    <main class="content">
        <header class="top-bar" style="position: relative; z-index: 1000; overflow: visible;">
            <h1 style="flex: 1;"><?php echo isset($_GET['module']) ? ucwords(str_replace(['dashboards/roles/', 'hr/', '_'], ['', 'HR: ', ' '], $_GET['module'])) : $role_code . ' Dashboard'; ?></h1>

                <div class="top-bar-right" style="display: flex; align-items: center; gap: 20px;">
                    <!-- Notification Bell -->
                    <div class="notification-wrapper" style="position: relative;">
                        <button class="btn-icon" onclick="toggleNotifications()" style="background: rgba(255,255,255,0.05); width: 40px; height: 40px; border-radius: 50%; border: 1px solid rgba(255,255,255,0.1); color: #fff; cursor: pointer; position: relative; display: flex; align-items: center; justify-content: center; transition: all 0.3s ease;">
                            <i class="fas fa-bell"></i>
                            <span id="notif-badge" style="position: absolute; top: -2px; right: -2px; background: #ff4444; color: white; font-size: 0.65rem; padding: 2px 5px; border-radius: 50%; display: none; font-weight: bold; border: 2px solid #1a1a1a;">0</span>
                        </button>
                        
                        <!-- Dropdown -->
                        <div id="notif-dropdown" class="glass-card custom-scrollbar" style="display: none; position: absolute; top: calc(100% + 15px); right: 0; width: 420px; z-index: 2000; padding: 0; max-height: 600px; min-height: 300px; overflow-y: auto; box-shadow: 0 30px 90px rgba(0,0,0,0.9); border: 1px solid rgba(255,255,255,0.15); border-radius: 24px; background: #131720;">
                            <div style="padding: 24px; border-bottom: 1px solid rgba(255,255,255,0.08); font-weight: 800; color: var(--gold); display: flex; justify-content: space-between; align-items: center; background: rgba(255,255,255,0.02); position: sticky; top: 0; z-index: 10; backdrop-filter: blur(10px);">
                                <span style="font-size: 1.15rem; letter-spacing: -0.5px;"><i class="fas fa-bell me-2"></i> Notifications</span>
                                <span id="notif-count-pill" style="font-size: 0.75rem; background: var(--gold); padding: 4px 12px; border-radius: 30px; color: #000; font-weight: 900; box-shadow: 0 4px 12px rgba(255, 204, 0, 0.4);">0 New</span>
                            </div>
                            <div id="notif-list" style="padding-bottom: 10px;"></div>
                        </div>
                    </div>

                    <!-- User Profile Dropdown -->
                    <div class="user-profile-wrapper" style="position: relative;">
                        <div class="user-trigger" onclick="toggleProfileMenu()" style="display: flex; align-items: center; gap: 12px; cursor: pointer; background: rgba(255,255,255,0.05); padding: 5px 15px 5px 6px; border-radius: 30px; border: 1px solid rgba(255,255,255,0.1); transition: all 0.3s ease;">
                            <img src="<?= $img_src ?>" style="width: 32px; height: 32px; border-radius: 50%; object-fit: cover; border: 2px solid var(--gold);">
                            <div class="user-meta" style="display: flex; flex-direction: column; line-height: 1;">
                                <span style="font-size: 0.85rem; font-weight: 700; color: #fff;"><?= $displayName ?></span>
                                <span style="font-size: 0.65rem; color: var(--gold); text-transform: uppercase; letter-spacing: 0.5px;"><?= $role_code ?></span>
                            </div>
                            <i class="fas fa-chevron-down" style="font-size: 0.7rem; color: rgba(255,255,255,0.3); margin-left: 5px;"></i>
                        </div>

                        <!-- Profile Dropdown Menu -->
                        <div id="user-dropdown" class="glass-card" style="display: none; position: absolute; top: 125%; right: 0; width: 220px; z-index: 1100; padding: 8px; box-shadow: 0 15px 40px rgba(0,0,0,0.8); border: 1px solid rgba(255,255,255,0.1); border-radius: 12px;">
                            <a href="main.php?module=profile/edit" class="dropdown-item" style="display: flex; align-items: center; gap: 10px; padding: 12px; color: #fff; text-decoration: none; border-radius: 8px; transition: background 0.2s;">
                                <i class="fas fa-user-circle" style="color: var(--gold); width: 20px;"></i>
                                <span style="font-size: 0.9rem;">My Profile</span>
                            </a>
                            <div style="height: 1px; background: rgba(255,255,255,0.05); margin: 5px 0;"></div>
                            <a href="logout.php" class="dropdown-item logout" style="display: flex; align-items: center; gap: 10px; padding: 12px; color: #ff4444; text-decoration: none; border-radius: 8px; transition: background 0.2s;">
                                <i class="fas fa-sign-out-alt" style="width: 20px;"></i>
                                <span style="font-size: 0.9rem; font-weight: 600;">Logout</span>
                            </a>
                        </div>
                    </div>

                    <div class="actions">
                        <?php if ($role_code !== 'HR_MANAGER'): ?>
                        <button class="btn-primary-sm" onclick="openHRMessageModal()" style="padding: 10px 18px; border-radius: 20px;">
                            <i class="fas fa-envelope me-1"></i> Message HR
                        </button>
                        <?php endif; ?>
                    </div>
                </div>
        </header>

        <div class="module-container">
            <?php 
            if (isset($_GET['module'])) {
                $module = $_GET['module'];
                // Basic security check: allow only specific patterns (a-z, 0-9, /, _)
                if (preg_match('/^[a-zA-Z0-9\/_]+$/', $module)) {
                    $module_file = "modules/" . $module . ".php";
                    $index_file = "modules/" . $module . "/index.php";

                    if (file_exists($module_file)) {
                        include $module_file;
                    } elseif (file_exists($index_file)) {
                        include $index_file;
                    } else {
                        echo "<div class='glass-card'>";
                        echo "<p style='color:#ff4444; font-weight:bold;'>Module '$module' not found.</p>";
                        echo "</div>";
                    }
                } else {
                    echo "<div class='glass-card'><p class='text-red'>Invalid module path.</p></div>";
                }
            }
            ?>
        </div>
    </main>

    <!-- Premium HR Message Modal -->
    <style>
    @keyframes slideUp {
        from { transform: translateY(20px); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
    }
    </style>
    <div id="hrMessageModal" class="modal-overlay" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6); backdrop-filter: blur(12px); z-index:9999; justify-content:center; align-items:center;">
        <div class="glass-card" style="width: 500px; padding: 0; border: 1px solid rgba(255,255,255,0.1); border-radius: 24px; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5); transform: translateY(0); animation: slideUp 0.3s ease-out;">
            <!-- Header -->
            <div style="padding: 24px 32px; border-bottom: 1px solid rgba(255,255,255,0.05); display: flex; justify-content: space-between; align-items: center; background: linear-gradient(to right, rgba(255,255,255,0.02), transparent);">
                <div>
                    <h3 style="margin: 0; font-size: 1.25rem; color: #fff; letter-spacing: -0.5px;">HR Direct Line</h3>
                    <p style="margin: 4px 0 0 0; font-size: 0.85rem; color: rgba(255,255,255,0.5);">Secure messaging channel</p>
                </div>
                <button onclick="closeHRMessageModal()" style="background: rgba(255,255,255,0.05); border: none; color: #fff; width: 32px; height: 32px; border-radius: 50%; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.2s;">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <form id="hrMessageForm" method="POST" action="modules/messages/send_to_hr.php" style="padding: 32px;">
                <!-- Subject -->
                <div class="form-group mb-4">
                    <label style="display: block; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1.5px; color: rgba(255,255,255,0.5); margin-bottom: 10px; font-weight: 600;">Subject</label>
                    <select name="subject" required style="width: 100%; background: rgba(0,0,0,0.3); border: 1px solid rgba(255,255,255,0.1); color: #fff; padding: 12px 16px; border-radius: 12px; font-size: 0.95rem; outline: none; transition: border-color 0.2s; appearance: none; cursor: pointer;">
                        <option value="" style="background: #1e293b;">Select Topic...</option>
                        <option value="General Inquiry" style="background: #1e293b;">General Inquiry</option>
                        <option value="Payroll Issue" style="background: #1e293b;">Payroll Issue</option>
                        <option value="Overtime Request" style="background: #1e293b;">Overtime Request</option>
                        <option value="Driver Request" style="background: #1e293b;">Transport / Driver Request</option>
                        <option value="Vehicle Issue" style="background: #1e293b;">Vehicle Issue</option>
                        <option value="Other" style="background: #1e293b;">Other</option>
                    </select>
                </div>

                <!-- Priority -->
                <div class="form-group mb-4">
                    <label style="display: block; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1.5px; color: rgba(255,255,255,0.5); margin-bottom: 10px; font-weight: 600;">Priority Level</label>
                    <div style="display: flex; gap: 10px;">
                        <label style="flex: 1; cursor: pointer;">
                            <input type="radio" name="priority" value="normal" checked style="display: none;" onchange="updatePriorityUI(this)">
                            <div class="priority-option active" style="padding: 10px; border-radius: 10px; border: 1px solid rgba(255,255,255,0.1); text-align: center; color: rgba(255,255,255,0.6); font-size: 0.9rem; transition: all 0.2s; background: rgba(255,255,255,0.02);">Normal</div>
                        </label>
                        <label style="flex: 1; cursor: pointer;">
                            <input type="radio" name="priority" value="high" style="display: none;" onchange="updatePriorityUI(this)">
                            <div class="priority-option" style="padding: 10px; border-radius: 10px; border: 1px solid rgba(255,255,255,0.1); text-align: center; color: rgba(255,255,255,0.6); font-size: 0.9rem; transition: all 0.2s; background: rgba(255,255,255,0.02);">High</div>
                        </label>
                        <label style="flex: 1; cursor: pointer;">
                            <input type="radio" name="priority" value="urgent" style="display: none;" onchange="updatePriorityUI(this)">
                            <div class="priority-option" style="padding: 10px; border-radius: 10px; border: 1px solid rgba(255,255,255,0.1); text-align: center; color: rgba(255,255,255,0.6); font-size: 0.9rem; transition: all 0.2s; background: rgba(255,255,255,0.02);">Urgent</div>
                        </label>
                    </div>
                </div>

                <!-- Message -->
                <div class="form-group mb-4">
                    <label style="display: block; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1.5px; color: rgba(255,255,255,0.5); margin-bottom: 10px; font-weight: 600;">Message</label>
                    <textarea name="message" required rows="4" placeholder="Type your message here..." style="width: 100%; background: rgba(0,0,0,0.3); border: 1px solid rgba(255,255,255,0.1); color: #fff; padding: 16px; border-radius: 12px; font-size: 0.95rem; outline: none; resize: none; font-family: inherit; transition: border-color 0.2s;"></textarea>
                </div>

                <!-- Actions -->
                <div style="display: flex; gap: 16px; margin-top: 8px;">
                    <button type="button" onclick="closeHRMessageModal()" style="flex: 1; padding: 14px; border-radius: 12px; background: transparent; border: 1px solid rgba(255,255,255,0.1); color: rgba(255,255,255,0.7); font-weight: 600; cursor: pointer; transition: all 0.2s;">Cancel</button>
                    <button type="submit" style="flex: 2; padding: 14px; border-radius: 12px; background: var(--gold); border: none; color: #000; font-weight: 700; cursor: pointer; box-shadow: 0 4px 12px rgba(255,204,0,0.2); transition: transform 0.2s;">
                        <i class="fas fa-paper-plane" style="margin-right: 8px;"></i> Send Message
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="assets/js/dashboard.js"></script>
    <script>
        function openHRMessageModal() {
            const modal = document.getElementById('hrMessageModal');
            modal.style.display = 'flex';
            // Trigger animation
            const card = modal.querySelector('.glass-card');
            card.style.transform = 'translateY(20px)';
            card.style.opacity = '0';
            setTimeout(() => {
                card.style.transform = 'translateY(0)';
                card.style.opacity = '1';
                card.style.transition = 'all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275)';
            }, 10);
            document.body.style.overflow = 'hidden';
        }
        
        function closeHRMessageModal() {
            const modal = document.getElementById('hrMessageModal');
            modal.style.display = 'none';
            document.body.style.overflow = 'auto';
            document.getElementById('hrMessageForm').reset();
            // Reset priority UI
            document.querySelectorAll('.priority-option').forEach(el => {
                el.style.background = 'rgba(255,255,255,0.02)';
                el.style.color = 'rgba(255,255,255,0.6)';
                el.style.borderColor = 'rgba(255,255,255,0.1)';
            });
            const defaultPrio = document.querySelector('input[name="priority"][value="normal"]').nextElementSibling;
            updatePriorityUI(document.querySelector('input[name="priority"][value="normal"]'));
        }

        function updatePriorityUI(radio) {
            // Reset all
            document.querySelectorAll('.priority-option').forEach(el => {
                el.style.background = 'rgba(255,255,255,0.02)';
                el.style.color = 'rgba(255,255,255,0.6)';
                el.style.borderColor = 'rgba(255,255,255,0.1)';
            });

            // Set active
            const target = radio.nextElementSibling;
            if (radio.value === 'normal') {
                target.style.background = 'rgba(0, 150, 255, 0.1)';
                target.style.color = '#0096ff';
                target.style.borderColor = '#0096ff';
            } else if (radio.value === 'high') {
                target.style.background = 'rgba(255, 204, 0, 0.1)';
                target.style.color = 'var(--gold)';
                target.style.borderColor = 'var(--gold)';
            } else if (radio.value === 'urgent') {
                target.style.background = 'rgba(255, 68, 68, 0.1)';
                target.style.color = '#ff4444';
                target.style.borderColor = '#ff4444';
            }
        }
        
        // Handle form submission
        document.getElementById('hrMessageForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const btn = this.querySelector('button[type="submit"]');
            const originalContent = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';
            btn.disabled = true;

            const formData = new FormData(this);
            
            fetch('modules/messages/send_to_hr.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Success toast or alert
                    alert('Message sent successfully to HR department!');
                    closeHRMessageModal();
                } else {
                    alert('Error: ' + (data.message || 'Failed to send message'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while sending the message');
            })
            .finally(() => {
                btn.innerHTML = originalContent;
                btn.disabled = false;
            });
        });


        // Initialize Priority UI
        updatePriorityUI(document.querySelector('input[name="priority"]:checked'));

        /* --- Notification System --- */
        function toggleNotifications() {
            const dropdown = document.getElementById('notif-dropdown');
            const userMenu = document.getElementById('user-dropdown');
            userMenu.style.display = 'none'; // Close other
            
            if (dropdown.style.display === 'none') {
                dropdown.style.display = 'block';
                fetchNotifications();
            } else {
                dropdown.style.display = 'none';
            }
        }

        function fetchNotifications() {
            fetch('modules/notifications/api.php?action=get_unread')
                .then(r => r.json())
                .then(data => {
                    const list = document.getElementById('notif-list');
                    const badge = document.getElementById('notif-badge');
                    
                    if (data.success) {
                        // Update Badge Count
                        const unreadCount = data.count || 0;
                        document.getElementById('notif-count-pill').innerText = unreadCount + ' New';
                        if (unreadCount > 0) {
                            badge.style.display = 'block';
                            badge.innerText = unreadCount;
                        } else {
                            badge.style.display = 'none';
                        }

                        // Update List Preview
                        if (data.notifications && data.notifications.length > 0) {
                            list.innerHTML = data.notifications.map(n => {
                                let label = '';
                                if (n.role === 'HR') {
                                    label = '<span style="background: var(--gold); color: #000; padding: 3px 8px; border-radius: 6px; font-size: 0.6rem; margin-right: 10px; font-weight: 800; letter-spacing: 0.5px;">HR MESSAGE</span>';
                                } else if (n.role === 'SYSTEM') {
                                    label = '<span style="background: rgba(0, 150, 255, 0.25); color: #4dbbff; border: 1px solid rgba(0, 150, 255, 0.3); padding: 2px 8px; border-radius: 6px; font-size: 0.6rem; margin-right: 10px; font-weight: 800; letter-spacing: 0.5px;">SYSTEM</span>';
                                }
                                
                                return `
                                    <div onclick="markReadAndRedirect(${n.id}, '${n.link || '#'}')" 
                                         style="padding: 24px; border-bottom: 1px solid rgba(255,255,255,0.05); cursor: pointer; transition: all 0.2s; position: relative;" 
                                         class="notif-item">
                                        <div style="display: flex; align-items: flex-start; margin-bottom: 12px; gap: 10px;">
                                            <div style="flex-shrink: 0; margin-top: 2px;">${label}</div>
                                            <div style="color: #fff; font-size: 1.05rem; font-weight: 700; flex: 1; line-height: 1.3;">${n.title}</div>
                                        </div>
                                        <div style="color: rgba(255,255,255,0.8); font-size: 0.95rem; line-height: 1.5; margin-bottom: 15px; background: rgba(0,0,0,0.2); padding: 15px; border-radius: 12px; white-space: pre-wrap; word-break: break-word; border: 1px solid rgba(255,255,255,0.03);">${n.message}</div>
                                        <div style="color: rgba(255,255,255,0.4); font-size: 0.75rem; display: flex; align-items: center; gap: 8px; justify-content: flex-end;">
                                            <i class="far fa-clock"></i> ${new Date(n.created_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})} • ${new Date(n.created_at).toLocaleDateString()}
                                        </div>
                                    </div>
                                `;
                            }).join('');
                        } else {
                            list.innerHTML = `
                                <div style="padding: 80px 20px; text-align: center; color: rgba(255,255,255,0.2);">
                                    <i class="fas fa-bell-slash fa-4x mb-4" style="opacity: 0.2;"></i>
                                    <p style="font-size: 1.1rem; font-weight: 500;">Your notification center is empty</p>
                                    <p style="font-size: 0.9rem; margin-top: 10px; opacity: 0.6;">We'll notify you when something important happens.</p>
                                </div>
                            `;
                        }
                    }
                });
        }
        
        function markReadAndRedirect(id, link) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'modules/notifications/api.php?action=mark_read';
            
            const idInput = document.createElement('input');
            idInput.type = 'hidden';
            idInput.name = 'id';
            idInput.value = id;
            
            const redirectInput = document.createElement('input');
            redirectInput.type = 'hidden';
            redirectInput.name = 'redirect';
            redirectInput.value = link;
            
            form.appendChild(idInput);
            form.appendChild(redirectInput);
            document.body.appendChild(form);
            form.submit();
        }

        function toggleProfileMenu() {
            const menu = document.getElementById('user-dropdown');
            const notif = document.getElementById('notif-dropdown');
            notif.style.display = 'none'; // Close other
            menu.style.display = menu.style.display === 'none' ? 'block' : 'none';
        }

        // Close dropdowns on click outside
        window.addEventListener('click', function(e) {
            if (!e.target.closest('.user-profile-wrapper') && !e.target.closest('.notification-wrapper')) {
                document.getElementById('user-dropdown').style.display = 'none';
                document.getElementById('notif-dropdown').style.display = 'none';
            }
        });

        // Real-time polling every 10 seconds (per ERP requirements)
        setInterval(fetchNotifications, 10000);
        fetchNotifications();


    </script>
</body>
</html>
