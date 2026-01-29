# Archived Files Documentation

This directory contains files that have been removed from the production codebase.
These files are kept for reference but should NOT be restored to production.

---

## /debug_scripts/

Debug and one-time fix scripts that were in the root directory:

| File | Purpose | Why Archived | Safe to Delete |
|------|---------|--------------|----------------|
| `check_bids.php` | Describe bids table schema | Debug utility | ✅ Yes |
| `check_dina.php` | Query specific user (dina) | Debug utility with print_r | ✅ Yes |
| `check_employees_schema.php` | Check employees table | Debug utility | ✅ Yes |
| `check_fin_cols.php` | Check finance columns | Debug utility | ✅ Yes |
| `check_missing_roles.php` | Audit role assignments | Debug utility | ✅ Yes |
| `get_cols.php` | Get column info | Debug utility | ✅ Yes |
| `fix_employee_positions.php` | One-time position migration | Migration complete | ✅ Yes |
| `fix_gm_schema.php` | One-time GM schema fix | Migration complete | ✅ Yes |

---

## /old_versions/

Legacy code versions that have been superseded.
Currently empty - will be used for future deprecated code.

---

## /sql_fixes/

One-time SQL fix scripts:

| File | Purpose | Why Archived |
|------|---------|--------------|
| `check_all_defines.sql` | Query define statements | Debug query |
| `check_incidents.sql` | Check incidents table | Debug query |
| `fix_budgets.sql` | Budget table fixes | One-time fix applied |
| `fix_incidents.sql` | Incidents table fixes | One-time fix applied |

---

## How to Restore a File

If you need to restore a file:

1. Copy from `/_archive/{subdirectory}/{filename}`
2. Place in the appropriate directory
3. Update any include paths
4. Test thoroughly
5. Document why it was restored

---

## Deletion Policy

Files in this archive can be permanently deleted after:
- 30 days with no issues
- Confirmation that functionality is not needed
- Full backup has been taken

---

**Archive Created:** 2026-01-29  
**Created By:** Antigravity AI (ERP Refactor)
