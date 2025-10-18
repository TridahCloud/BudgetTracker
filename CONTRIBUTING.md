# Contributing to Tridah Budget Tracker

First off, thank you for considering contributing to Tridah Budget Tracker! It's people like you that make this tool better for everyone. 🎉

## Table of Contents
- [Code of Conduct](#code-of-conduct)
- [How Can I Contribute?](#how-can-i-contribute)
- [Development Setup](#development-setup)
- [Coding Standards](#coding-standards)
- [Commit Guidelines](#commit-guidelines)
- [Pull Request Process](#pull-request-process)

---

## Code of Conduct

This project and everyone participating in it is governed by our commitment to creating a welcoming and inclusive environment. By participating, you are expected to uphold these values:

- **Be respectful** of differing viewpoints and experiences
- **Be collaborative** and help others learn
- **Be patient** with new contributors
- **Focus on what is best** for the community

---

## How Can I Contribute?

### Reporting Bugs 🐛

Before creating bug reports, please check the existing issues to avoid duplicates. When creating a bug report, include:

- **Clear title and description**
- **Steps to reproduce** the problem
- **Expected behavior** vs actual behavior
- **Screenshots** if applicable
- **Environment details** (OS, PHP version, browser, etc.)

**Use this template:**
```markdown
**Describe the bug**
A clear and concise description of what the bug is.

**To Reproduce**
Steps to reproduce the behavior:
1. Go to '...'
2. Click on '....'
3. Scroll down to '....'
4. See error

**Expected behavior**
A clear and concise description of what you expected to happen.

**Screenshots**
If applicable, add screenshots to help explain your problem.

**Environment:**
 - OS: [e.g. Windows 10]
 - Browser: [e.g. Chrome 91]
 - PHP Version: [e.g. 8.0]
```

### Suggesting Features 💡

We love new ideas! Before suggesting a feature:

1. **Check existing issues** to see if it's already suggested
2. **Describe the problem** this feature would solve
3. **Explain your proposed solution**
4. **Consider alternatives** you've thought about

### Code Contributions 💻

#### Good First Issues

Look for issues labeled `good first issue` - these are great for newcomers!

#### Areas We Need Help

- **UI/UX improvements**
- **Mobile responsiveness**
- **Performance optimizations**
- **Additional chart types**
- **Export functionality**
- **Testing**
- **Documentation**

---

## Development Setup

### 1. Fork and Clone

```bash
# Fork the repo on GitHub, then:
git clone https://github.com/YOUR_USERNAME/BudgetTracker.git
cd BudgetTracker
```

### 2. Create a Branch

```bash
git checkout -b feature/your-feature-name
# or
git checkout -b fix/bug-description
```

### 3. Set Up Development Environment

Follow the [INSTALLATION.md](INSTALLATION.md) guide for local setup.

### 4. Make Your Changes

- Write clean, readable code
- Follow existing code style
- Comment complex logic
- Update documentation if needed

### 5. Test Your Changes

- Test all affected functionality
- Check responsiveness on different screen sizes
- Verify in multiple browsers (Chrome, Firefox, Safari)
- Ensure no console errors

---

## Coding Standards

### PHP

- **PSR-12** coding standard
- Use **type hints** where possible
- **Comment** complex logic
- Use **meaningful variable names**
- **Sanitize** all user inputs
- Use **prepared statements** for database queries

Example:
```php
<?php
/**
 * Get user profile by ID
 * 
 * @param int $user_id User ID
 * @return array User profile data
 */
public function getProfile(int $user_id): array {
    $stmt = $this->db->prepare("SELECT * FROM users WHERE user_id = ?");
    $stmt->execute([$user_id]);
    return $stmt->fetch();
}
```

### JavaScript

- **ES6+** syntax
- Use **const/let** instead of var
- **Async/await** for asynchronous code
- **Comment** complex functions
- Use **meaningful function names**

Example:
```javascript
/**
 * Load user dashboard data
 * @returns {Promise<void>}
 */
async function loadDashboardData() {
    try {
        const response = await fetch(`${API_URL}/api/dashboard/get.php`);
        const result = await response.json();
        
        if (result.success) {
            updateDashboard(result.data);
        }
    } catch (error) {
        console.error('Dashboard load error:', error);
        showNotification('Failed to load dashboard', 'error');
    }
}
```

### CSS

- Use **CSS variables** for theming
- **Mobile-first** responsive design
- Use **meaningful class names**
- **Group** related styles
- Keep **specificity low**

Example:
```css
/* Good */
.card {
    background: var(--card-bg);
    padding: var(--spacing-md);
    border-radius: var(--radius-md);
}

/* Avoid */
div.container > div.card > div.header {
    /* Too specific */
}
```

### HTML

- **Semantic HTML5** elements
- **Accessibility** attributes (ARIA labels, alt text)
- **Proper nesting** and indentation
- **Valid HTML**

---

## Commit Guidelines

### Commit Messages

Use clear, descriptive commit messages:

```bash
# Good
git commit -m "Add expense filtering by date range"
git commit -m "Fix budget calculation rounding error"
git commit -m "Update README with Google OAuth setup"

# Avoid
git commit -m "Fixed stuff"
git commit -m "Update"
git commit -m "asdfgh"
```

### Commit Message Format

```
<type>: <subject>

<body (optional)>

<footer (optional)>
```

**Types:**
- `feat`: New feature
- `fix`: Bug fix
- `docs`: Documentation changes
- `style`: Code style changes (formatting, etc.)
- `refactor`: Code refactoring
- `test`: Adding tests
- `chore`: Maintenance tasks

**Example:**
```
feat: Add monthly expense report export

- Implement PDF generation using FPDF
- Add export button to reports page
- Include charts in exported report

Closes #123
```

---

## Pull Request Process

### Before Submitting

- [ ] Code follows the project's coding standards
- [ ] Comments added for complex logic
- [ ] Documentation updated if needed
- [ ] Tested on multiple browsers
- [ ] No console errors or warnings
- [ ] Git history is clean (squash commits if needed)

### PR Title Format

```
[Type] Brief description

Examples:
[Feature] Add budget sharing between users
[Fix] Resolve login session timeout issue
[Docs] Update installation guide for Windows
```

### PR Description Template

```markdown
## Description
Brief description of changes

## Type of Change
- [ ] Bug fix
- [ ] New feature
- [ ] Breaking change
- [ ] Documentation update

## Related Issues
Closes #(issue number)

## Testing
Describe how you tested these changes

## Screenshots (if applicable)
Add screenshots here

## Checklist
- [ ] My code follows the style guidelines
- [ ] I have commented my code
- [ ] I have updated the documentation
- [ ] My changes generate no new warnings
- [ ] I have tested on multiple browsers
```

### Review Process

1. **Automated checks** will run (when set up)
2. **Maintainers** will review your code
3. **Changes requested** may need to be addressed
4. Once approved, your PR will be **merged**!

### After Your PR is Merged

- **Delete your branch** (GitHub will prompt you)
- **Update your fork**:
  ```bash
  git checkout main
  git pull upstream main
  git push origin main
  ```

---

## Recognition

Contributors will be:
- Added to our **Contributors** list
- Mentioned in **release notes** for significant contributions
- Given credit in the project

---

## Questions?

- **GitHub Discussions**: Ask questions and share ideas
- **GitHub Issues**: Report bugs or request features
- **Email**: Contact Tridah at tridah.cloud

---

## License

By contributing, you agree that your contributions will be licensed under the MIT License.

---

**Thank you for contributing to Tridah Budget Tracker! 🙏**

Every contribution, no matter how small, makes this project better for everyone.

