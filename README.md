# Tridah Budget Tracker

<div align="center">
  <img src="tridah icon.png" alt="Tridah Logo" width="120">
  
  <h3>Free Online Budget Management Suite</h3>
  <p>A comprehensive, modern budgeting application for personal and business finance tracking.</p>
  
  [![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg)](LICENSE)
  [![Maintained by Tridah](https://img.shields.io/badge/Maintained%20by-Tridah-blueviolet)](https://tridah.cloud)
</div>

---

## 🌟 Features

### 💰 Comprehensive Income Tracking
- **Multiple Income Sources**: Track salary, freelance work, side hustles, business income, investments, and more
- **Recurring Income**: Set up automatic recurring income entries
- **Income Analytics**: Visualize income trends and patterns

### 💳 Smart Expense Management
- **Automatic Categorization**: Pre-configured categories with the option to create custom ones
- **Transaction History**: Complete record of all your expenses
- **Receipt Storage**: Upload and store receipt images (coming soon)
- **Payment Method Tracking**: Track cash, credit card, bank transfers, and digital wallets

### 📊 Budget Planning
- **Flexible Budgets**: Create budgets by category, time period, or overall spending
- **Budget Alerts**: Get notified when you're approaching your budget limits
- **Personal & Business**: Separate tracking for personal and business budgets
- **Progress Tracking**: Visual progress bars and analytics

### 🎯 Financial Goals
- **Savings Goals**: Set and track savings targets
- **Debt Reduction**: Monitor debt payment progress
- **Investment Planning**: Track investment goals
- **Custom Goals**: Create any type of financial goal

### 📈 Interactive Reports
- **Visual Charts**: Beautiful, interactive charts powered by Chart.js
- **Expense Breakdown**: See where your money goes
- **Income vs Expenses**: Compare your income and spending
- **Trend Analysis**: Track financial trends over time
- **Export Reports**: Download reports for your records (coming soon)

### 🔐 Security & Privacy
- **Secure Authentication**: Email/password login with bcrypt hashing
- **Google SSO**: Quick login with Google OAuth (integration ready)
- **Session Management**: Secure session handling
- **Data Privacy**: Your data is encrypted and never shared

### 📱 Modern User Experience
- **Responsive Design**: Works perfectly on desktop, tablet, and mobile
- **Tech-Startup Vibes**: Clean, modern interface
- **Interactive Dashboard**: Real-time updates and smooth animations
- **User-Friendly**: Intuitive navigation and easy to use

---

## 🚀 Quick Start

### Prerequisites
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web server (Apache/Nginx)
- Modern web browser

### Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/TridahCloud/BudgetTracker.git
   cd BudgetTracker
   ```

2. **Create database**
   ```bash
   mysql -u root -p
   CREATE DATABASE tridah_budget CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

3. **Import database schema**
   ```bash
   mysql -u root -p tridah_budget < database/schema.sql
   ```

4. **Configure environment**
   ```bash
   cp .env.example .env
   ```
   Edit `.env` and update your database credentials and other settings.

5. **Set up permissions**
   ```bash
   chmod 755 uploads/
   ```

6. **Configure your web server**
   
   Point your web server's document root to the project directory.
   
   **Apache (.htaccess example)**:
   ```apache
   RewriteEngine On
   RewriteCond %{REQUEST_FILENAME} !-f
   RewriteCond %{REQUEST_FILENAME} !-d
   RewriteRule ^(.*)$ index.html [L]
   ```

7. **Access the application**
   
   Open your browser and navigate to `http://localhost` or your configured domain.

---

## 🗄️ Database Schema

The application uses a comprehensive database schema supporting:

- **Users & Authentication**: Secure user management with email and Google OAuth support
- **Income Sources**: Track multiple income streams
- **Income Transactions**: Record all income with detailed information
- **Expense Categories**: Pre-defined and custom categories
- **Expense Transactions**: Complete expense tracking
- **Budgets**: Flexible budget planning and tracking
- **Financial Goals**: Set and monitor financial objectives
- **Accounts**: Track multiple accounts and balances
- **Recurring Transactions**: Automate regular income and expenses
- **Tags**: Additional categorization and organization
- **Notifications**: Budget alerts and reminders

See `database/schema.sql` for complete schema details.

---

## 🛠️ Technology Stack

### Backend
- **PHP**: Server-side logic and API
- **MySQL**: Database management
- **PDO**: Secure database interactions

### Frontend
- **HTML5**: Modern semantic markup
- **CSS3**: Custom styling with CSS variables
- **JavaScript (ES6+)**: Interactive functionality
- **Chart.js**: Beautiful data visualization

### Libraries & Tools
- **Font Awesome**: Icons
- **Chart.js**: Interactive charts
- **Google OAuth**: SSO authentication (optional)

---

## 📁 Project Structure

```
BudgetTracker/
├── api/
│   ├── auth/              # Authentication endpoints
│   ├── budgets/           # Budget management
│   ├── categories/        # Category management
│   ├── classes/           # PHP classes
│   ├── income-sources/    # Income source management
│   └── transactions/      # Transaction management
├── assets/
│   ├── css/              # Stylesheets
│   └── js/               # JavaScript files
├── config/
│   ├── config.php        # Main configuration
│   └── database.php      # Database configuration
├── database/
│   └── schema.sql        # Database schema
├── uploads/              # User uploaded files
├── dashboard.html        # Dashboard interface
├── index.html           # Landing page
└── README.md            # This file
```

---

## 🔧 Configuration

### Environment Variables

Create a `.env` file based on `.env.example`:

```env
# Application
APP_URL=http://localhost
APP_ENV=development

# Database
DB_HOST=localhost
DB_NAME=tridah_budget
DB_USER=root
DB_PASS=your_password

# Google OAuth (Optional)
GOOGLE_CLIENT_ID=your_client_id
GOOGLE_CLIENT_SECRET=your_client_secret

# Security
ENCRYPTION_KEY=your_random_key_here
```

### Google OAuth Setup (Optional)

**Quick Setup:**
1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Create a new project
3. Enable Google+ API
4. Create OAuth 2.0 credentials
5. Add authorized redirect URI: `http://your-domain.com/api/auth/google-callback.php`
6. Copy Client ID and Client Secret to your `.env` file

**📖 [Complete Google OAuth Setup Guide](GOOGLE_OAUTH_SETUP.md)** - Step-by-step instructions with screenshots and troubleshooting.

---

## 🤝 Contributing

We welcome contributions from the community! Here's how you can help:

### Ways to Contribute

- **Report Bugs**: Open an issue describing the bug
- **Suggest Features**: Share your ideas for new features
- **Submit Pull Requests**: Fix bugs or add features
- **Improve Documentation**: Help make the docs better
- **Share**: Spread the word about Tridah Budget Tracker

### Development Workflow

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

---

## 📝 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

---

## 🌐 About Tridah

**Tridah** is a non-profit organization dedicated to creating free, open-source software that helps people and businesses manage their digital lives better.

- **Website**: [tridah.cloud](https://tridah.cloud)
- **GitHub**: [github.com/TridahCloud](https://github.com/TridahCloud)

### Our Mission

To provide high-quality, free software that empowers individuals and organizations to take control of their finances, productivity, and digital presence.

---

## 🎯 Roadmap

### Current Features
- ✅ User authentication (email/password + Google OAuth ready)
- ✅ **Multi-tracker system** - Unlimited budget trackers per user
- ✅ Income tracking with multiple sources
- ✅ Expense tracking and categorization
- ✅ Budget planning with smart alerts and insights
- ✅ **Full CRUD** - Edit and delete all data
- ✅ Interactive dashboard with real-time charts
- ✅ **Comprehensive reports** - Analytics and trends
- ✅ Tracker switching - One-click organization
- ✅ Budget alerts and spending pace tracking
- ✅ Responsive design for all devices

### Coming Soon
- 🔄 Export reports to PDF/Excel
- 🔄 Recurring transaction automation
- 🔄 Financial goals tracking
- 🔄 Mobile app (React Native)
- 🔄 Multi-currency support
- 🔄 Share trackers with team/family

---

## 💬 Support

Need help? Have questions?

- **Documentation**: Check this README and inline code comments
- **Issues**: [GitHub Issues](https://github.com/TridahCloud/BudgetTracker/issues)
- **Discussions**: [GitHub Discussions](https://github.com/TridahCloud/BudgetTracker/discussions)

---

## 🙏 Acknowledgments

- Thanks to all contributors who help improve this project
- Font Awesome for the amazing icons
- Chart.js for beautiful data visualization
- The open-source community for inspiration and support

---

<div align="center">
  <p>Made with ❤️ by <a href="https://tridah.cloud">Tridah</a></p>
  <p>Free and Open Source Forever</p>
</div>
