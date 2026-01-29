# PDO Query Syntax Fix - GMManager.php

## ❌ Problem

**Fatal Error:**
```
TypeError: PDO::query(): Argument #2 ($fetchMode) must be of type ?int, array given
in GMManager.php:221
```

## 🔍 Root Cause

The code was incorrectly using `PDO::query()` with parameters:

```php
// ❌ WRONG - query() doesn't accept parameters
$db->query("SELECT * FROM table WHERE id = ?", [$id])->fetchAll();
```

**PDO's `query()` method:**
- Does NOT accept bind parameters
- Second argument is `$fetchMode` (int), not parameters (array)

## ✅ Solution

Use `prepare()` and `execute()` pattern instead:

```php
// ✅ CORRECT - prepare/execute pattern
$stmt = $db->prepare("SELECT * FROM table WHERE id = ?");
$stmt->execute([$id]);
return $stmt->fetchAll();
```

## 🔧 Files Fixed

### **includes/GMManager.php**

#### **1. getSystemLogs() method (Line 214-227)**

**Before:**
```php
return $db->query("SELECT sl.*, u.username 
                  FROM system_logs sl 
                  LEFT JOIN users u ON sl.user_id = u.id 
                  ORDER BY sl.created_at DESC 
                  LIMIT ?", [$limit])->fetchAll();
```

**After:**
```php
$stmt = $db->prepare("SELECT sl.*, u.username 
                      FROM system_logs sl 
                      LEFT JOIN users u ON sl.user_id = u.id 
                      ORDER BY sl.created_at DESC 
                      LIMIT ?");
$stmt->execute([$limit]);
return $stmt->fetchAll();
```

#### **2. getAuditTrail() method (Line 233-257)**

**Before:**
```php
// With module
return $db->query("SELECT at.*, u.username ... WHERE at.module = ? ... LIMIT ?", 
                  [$module, $limit])->fetchAll();

// Without module
return $db->query("SELECT at.*, u.username ... LIMIT ?", 
                  [$limit])->fetchAll();
```

**After:**
```php
// With module
$stmt = $db->prepare("SELECT at.*, u.username ... WHERE at.module = ? ... LIMIT ?");
$stmt->execute([$module, $limit]);
return $stmt->fetchAll();

// Without module
$stmt = $db->prepare("SELECT at.*, u.username ... LIMIT ?");
$stmt->execute([$limit]);
return $stmt->fetchAll();
```

## 📚 PDO Method Reference

### **query() - For static queries without parameters**
```php
// Use when NO parameters needed
$result = $db->query("SELECT * FROM users")->fetchAll();
```

### **prepare() + execute() - For queries with parameters**
```php
// Use when parameters needed
$stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$id]);
$result = $stmt->fetchAll();
```

## ✅ Testing

The error should now be resolved. Test by:

1. Login as GM
2. Navigate to **Audit Reports** (`gm/audit.php`)
3. Navigate to **System Logs** (`gm/logs.php`)
4. Verify no fatal errors
5. Verify logs display correctly

## 🎯 Impact

**Affected Modules:**
- `modules/gm/audit.php` - Uses `GMManager::getSystemLogs()`
- `modules/gm/logs.php` - Uses `GMManager::getSystemLogs()` and `GMManager::getAuditTrail()`

**Status:** ✅ FIXED

---

**Fixed by:** Antigravity AI  
**Date:** 2026-01-29  
**Issue:** PDO query syntax error
