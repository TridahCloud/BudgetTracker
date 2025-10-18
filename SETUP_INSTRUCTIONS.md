# Setup Instructions for Tridah Budget Tracker

## 🔐 Protecting Your Credentials

This project uses `.gitignore` to protect sensitive information from being committed to version control.

### **Files That Are Protected:**

1. **`config/database.php`** - Your actual database credentials
2. **`.env`** - Environment variables
3. **`uploads/*`** - User uploaded files
4. **`*.log`** - Error logs
5. **`api/debug.php`** - Debug tools

### **Files Safe to Commit:**

1. **`config/database.example.php`** - Template without credentials
2. **`.env.example`** - Template without secrets
3. **All other code files**

---

## 🚀 Setting Up Database Configuration

### **For New Installation:**

1. **Copy the example file:**
   ```bash
   cp config/database.example.php config/database.php
   ```

2. **Edit `config/database.php` with your credentials:**
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'BudgetTracker');
   define('DB_USER', 'your_username');
   define('DB_PASS', 'your_password');
   ```

3. **Never commit this file!**
   - It's already in `.gitignore`
   - Check with: `git status` (should not appear)

### **For Cloning from GitHub:**

1. **Clone the repository:**
   ```bash
   git clone https://github.com/TridahCloud/BudgetTracker.git
   cd BudgetTracker
   ```

2. **Create database config from example:**
   ```bash
   cp config/database.example.php config/database.php
   ```

3. **Update with your credentials:**
   - Open `config/database.php`
   - Replace placeholders with your actual database info

4. **Import database schema:**
   ```bash
   mysql -u your_username -p BudgetTracker < database/schema.sql
   mysql -u your_username -p BudgetTracker < database/add_trackers_migration.sql
   ```

---

## ⚠️ Security Checklist

Before committing to Git:

### **Check These Files Are NOT Staged:**
```bash
git status
```

Should NOT see:
- ❌ `config/database.php`
- ❌ `.env`
- ❌ `uploads/` (except .gitkeep)
- ❌ `*.log` files
- ❌ `api/debug.php`

### **If Accidentally Staged:**

**Remove from staging:**
```bash
git reset config/database.php
```

**If already committed:**
```bash
# Remove from history (careful!)
git rm --cached config/database.php
git commit -m "Remove database credentials from version control"
```

---

## 🔒 Environment Variables (Recommended)

### **Better Security: Use Environment Variables**

Instead of hardcoding in `database.php`, use environment variables:

**1. Set environment variables in Apache:**

Edit your Apache virtual host config:
```apache
<VirtualHost *:80>
    ...
    SetEnv DB_HOST "localhost"
    SetEnv DB_NAME "BudgetTracker"
    SetEnv DB_USER "your_user"
    SetEnv DB_PASS "your_password"
</VirtualHost>
```

**2. Or use .htaccess:**
```apache
SetEnv DB_HOST localhost
SetEnv DB_NAME BudgetTracker
SetEnv DB_USER your_user
SetEnv DB_PASS your_password
```

**3. Or use .env file:**
The `database.php` already supports `getenv()`, so it will read from environment variables if set.

---

## 📋 Deployment Checklist

### **For Production Server:**

- [ ] Create `config/database.php` from example
- [ ] Update with production database credentials
- [ ] Verify `.gitignore` is working
- [ ] Delete `api/debug.php`
- [ ] Delete `api/test-connection.php`
- [ ] Set proper file permissions (644 for config files)
- [ ] Test database connection
- [ ] Verify credentials not in Git history

### **For Development:**

- [ ] Copy `config/database.example.php` to `config/database.php`
- [ ] Update with local database credentials
- [ ] Import database schemas
- [ ] Test connection
- [ ] Never commit the actual `database.php` file

---

## 🔍 Verify Protection

### **Test that credentials are protected:**

```bash
# Check gitignore is working
git check-ignore config/database.php
# Should output: config/database.php

# Check what would be committed
git add .
git status
# Should NOT see config/database.php

# View what's tracked
git ls-files | grep database
# Should show: config/database.example.php (not database.php)
```

---

## 🆘 If Credentials Were Exposed

### **If you accidentally committed credentials:**

**1. Change all passwords immediately:**
```sql
ALTER USER 'username'@'localhost' IDENTIFIED BY 'new_secure_password';
```

**2. Remove from Git history:**
```bash
git filter-branch --force --index-filter \
  "git rm --cached --ignore-unmatch config/database.php" \
  --prune-empty --tag-name-filter cat -- --all

git push origin --force --all
```

**3. Add to .gitignore:**
```bash
echo "config/database.php" >> .gitignore
git add .gitignore
git commit -m "Protect database credentials"
```

---

## ✅ Best Practices

### **DO:**
- ✅ Use `database.example.php` as template
- ✅ Keep actual `database.php` local only
- ✅ Use environment variables for production
- ✅ Regularly check `git status` before committing
- ✅ Review `.gitignore` is working

### **DON'T:**
- ❌ Commit `config/database.php` to Git
- ❌ Share database passwords in code
- ❌ Push `.env` files to GitHub
- ❌ Ignore `.gitignore` warnings
- ❌ Use weak database passwords

---

## 📝 Quick Reference

### **Setup New Developer:**
```bash
# Clone repo
git clone https://github.com/TridahCloud/BudgetTracker.git
cd BudgetTracker

# Create config from example
cp config/database.example.php config/database.php

# Edit with actual credentials
nano config/database.php

# Import database
mysql -u root -p BudgetTracker < database/schema.sql
mysql -u root -p BudgetTracker < database/add_trackers_migration.sql

# Verify protection
git status  # database.php should NOT appear
```

### **Deploy to Production:**
```bash
# On server
git pull origin main

# Create database config (if first time)
cp config/database.example.php config/database.php
nano config/database.php  # Add production credentials

# Never commit this file!
```

---

**Your credentials are now protected!** 🔒

