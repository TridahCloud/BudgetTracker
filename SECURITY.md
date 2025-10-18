# Security Policy

## Supported Versions

We release patches for security vulnerabilities. Currently supported versions:

| Version | Supported          |
| ------- | ------------------ |
| 1.0.x   | :white_check_mark: |

## Reporting a Vulnerability

The Tridah team and community take security bugs seriously. We appreciate your efforts to responsibly disclose your findings.

### How to Report a Security Vulnerability

**Please do NOT report security vulnerabilities through public GitHub issues.**

Instead, please report them via email to: **team@tridah.cloud**

Include the following information:
- Type of issue (e.g., SQL injection, XSS, authentication bypass)
- Full paths of source file(s) related to the issue
- Location of the affected source code (tag/branch/commit or direct URL)
- Step-by-step instructions to reproduce the issue
- Proof-of-concept or exploit code (if possible)
- Impact of the issue, including how an attacker might exploit it

### What to Expect

After submitting a report, you can expect:

1. **Acknowledgment** within 48 hours
2. **Initial assessment** within 7 days
3. **Regular updates** on our progress
4. **Public disclosure** after the issue is resolved (with your credit if desired)

### Security Best Practices for Users

#### For Self-Hosted Instances

1. **Use HTTPS**
   - Always use SSL/TLS certificates
   - Redirect HTTP to HTTPS

2. **Update Regularly**
   - Keep PHP, MySQL, and web server updated
   - Monitor for security updates

3. **Strong Passwords**
   - Enforce strong password policies
   - Use password hashing (already implemented)

4. **Database Security**
   - Use separate database user with minimal privileges
   - Never use root database account
   - Change default credentials

5. **File Permissions**
   ```bash
   # Recommended permissions
   chmod 755 /path/to/budget-tracker
   chmod 755 uploads/
   chmod 644 .env
   chown -R www-data:www-data /path/to/budget-tracker
   ```

6. **Environment Configuration**
   - Set `APP_ENV=production` in production
   - Disable PHP error display in production
   - Use strong encryption keys

7. **Backup Regularly**
   - Automated daily backups
   - Store backups securely off-site

8. **Firewall Configuration**
   - Limit database access to localhost
   - Configure firewall rules (UFW, iptables)

9. **Monitor Logs**
   - Regularly check error logs
   - Monitor for suspicious activity

10. **Rate Limiting**
    - Implement rate limiting for API endpoints
    - Use fail2ban or similar tools

### Security Features Implemented

#### Authentication
- ✅ Password hashing with bcrypt
- ✅ Session management
- ✅ CSRF protection ready
- ✅ Google OAuth support (optional)

#### Database
- ✅ Prepared statements (SQL injection protection)
- ✅ Input validation
- ✅ Parameterized queries

#### Data Protection
- ✅ XSS protection
- ✅ HTTPS ready
- ✅ Secure headers configuration
- ✅ Session security settings

#### File Security
- ✅ Upload validation
- ✅ File type restrictions
- ✅ Size limits
- ✅ .htaccess protection

### Known Security Considerations

1. **Google OAuth**
   - Requires proper configuration
   - Keep client secrets secure
   - Use environment variables

2. **File Uploads**
   - Currently limited to specific file types
   - Size restrictions in place
   - Future: Add virus scanning

3. **Session Management**
   - Sessions expire after 7 days (configurable)
   - Secure cookies in production

### Security Checklist for Production

Before deploying to production:

- [ ] HTTPS enabled and enforced
- [ ] Strong database password set
- [ ] `.env` file secured (not web-accessible)
- [ ] PHP display_errors disabled
- [ ] Strong encryption key generated
- [ ] Database user has minimal privileges
- [ ] File permissions properly set
- [ ] Backups configured
- [ ] Error logging enabled
- [ ] Security headers configured
- [ ] Google OAuth credentials (if using)
- [ ] Rate limiting implemented
- [ ] Firewall configured

### Dependency Security

We regularly monitor dependencies for security vulnerabilities:

- **PHP**: Keep updated to latest stable version
- **MySQL**: Use latest stable version
- **JavaScript Libraries**: 
  - Chart.js: Latest stable version
  - Font Awesome: CDN version

### Security Updates

Subscribe to security updates:
- Watch this repository for security announcements
- Follow [@TridahCloud](https://github.com/TridahCloud) on GitHub

### Contact

For security concerns:
- **Email**: security@tridah.cloud
- **Website**: https://tridah.cloud

### Hall of Fame

We recognize security researchers who help us:

<!-- Contributors who report security issues will be listed here -->

*No vulnerabilities reported yet.*

---

**Thank you for helping keep Tridah Budget Tracker and our users safe!** 🔐

