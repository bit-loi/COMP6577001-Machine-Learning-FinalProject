# 📚 Bookstore E-Commerce Platform

A modern, feature-rich online bookstore built with PHP, MySQL, and Bootstrap 5. This platform provides a seamless shopping experience for customers and a powerful admin dashboard for store management.

![Bookstore Banner](https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-005C84?style=for-the-badge&logo=mysql&logoColor=white)
![Bootstrap](https://img.shields.io/badge/Bootstrap-563D7C?style=for-the-badge&logo=bootstrap&logoColor=white)

## ✨ Features

### Customer Features
- 🏠 **Homepage** - Browse all available books with elegant card layout
- 🔍 **Categories** - Filter books by categories
- 🛒 **Shopping Cart** - Add/remove items, update quantities
- 💳 **Checkout** - Complete purchase with shipping information
- 👤 **User Authentication** - Secure login and registration
- 📱 **Responsive Design** - Works perfectly on all devices

### Admin Features
- 📊 **Dashboard** - Overview of sales, orders, and analytics
- 📖 **Product Management** - Add, edit, delete books
- 🏷️ **Category Management** - Organize books by categories
- 👥 **User Management** - Manage customer accounts
- 📦 **Order Management** - Track and process orders
- 📈 **Analytics** - Sales reports and insights

## 🚀 Getting Started

### Prerequisites

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache/Nginx web server
- Composer (optional, for dependencies)

### Installation

#### Option 1: Using XAMPP (Recommended for Windows)

1. **Install XAMPP**
   - Download and install [XAMPP](https://www.apachefriends.org/)
   - Start Apache and MySQL services

2. **Clone the Repository**
   ```bash
   cd C:\xampp\htdocs
   git clone <your-repo-url> bookstore
   ```

3. **Import Database**
   - Open phpMyAdmin (http://localhost/phpmyadmin)
   - Create a new database named `bookstore`
   - Import the SQL file from `database/bookstore.sql`

4. **Configure Database Connection**
   - Edit `config/config.php`
   - Update database credentials if needed:
     ```php
     $host = 'localhost';
     $dbname = 'bookstore';
     $username = 'root';
     $password = '';
     ```

5. **Access the Application**
   - Customer Site: http://localhost/bookstore
   - Admin Dashboard: http://localhost/bookstore/admin

#### Option 2: Using Docker

1. **Build and Run with Docker**
   ```bash
   docker-compose up -d
   ```

2. **Access the Application**
   - Customer Site: http://localhost:8080
   - Admin Dashboard: http://localhost:8080/admin
   - phpMyAdmin: http://localhost:8081

3. **Import Database**
   - Access phpMyAdmin at http://localhost:8081
   - Login with:
     - Username: `root`
     - Password: `rootpassword`
   - Import `database/bookstore.sql`

## 📁 Project Structure

```
bookstore/
├── admin/                  # Admin dashboard
│   ├── index.php          # Dashboard home
│   ├── products/          # Product management
│   ├── categories/        # Category management
│   ├── orders/            # Order management
│   └── users/             # User management
├── auth/                   # Authentication
│   ├── login.php
│   ├── register.php
│   └── logout.php
├── config/                 # Configuration
│   └── config.php         # Database config
├── includes/               # Shared components
│   ├── header.php
│   ├── footer.php
│   └── contact-handler.php
├── pages/                  # Customer pages
│   ├── about.php          # About us
│   └── contact.php        # Contact form
├── shopping/               # Shopping features
│   ├── cart.php
│   ├── checkout.php
│   └── single.php
├── categories/             # Category pages
├── images/                 # Product images
├── assets/                 # CSS, JS assets
│   ├── css/
│   └── js/
├── database/               # Database files
│   └── bookstore.sql
├── index.php              # Homepage
├── Dockerfile
├── docker-compose.yml
└── README.md
```

## 🔐 Default Admin Credentials

```
Username: admin
Password: admin123
```

> ⚠️ **Important**: Change these credentials after first login!

## 🛠️ Technologies Used

- **Backend**: PHP 7.4+
- **Database**: MySQL 5.7+
- **Frontend**: HTML5, CSS3, JavaScript
- **CSS Framework**: Bootstrap 5.0.2
- **Icons**: Font Awesome 5
- **Server**: Apache (XAMPP)
- **Containerization**: Docker & Docker Compose

## 📸 Screenshots

### Customer Interface
- Modern homepage with book grid
- Smooth shopping cart experience
- Responsive checkout process

### Admin Dashboard
- Clean and intuitive interface
- Real-time analytics and charts
- Easy product and order management

## 🌟 Key Features Explained

### Shopping Cart
The shopping cart uses PHP sessions to maintain state and allows users to:
- Add products with quantity selection
- Update quantities in real-time
- Remove items
- View total price calculation

### Admin Dashboard
The admin panel provides comprehensive tools for:
- **Sales Analytics**: Track revenue and order trends
- **Inventory Management**: Monitor stock levels
- **Customer Insights**: View user activity and preferences
- **Order Processing**: Manage order lifecycle from placement to delivery

## 🔧 Configuration

### Database Configuration
Edit `config/config.php`:
```php
$host = 'localhost';      // Database host
$dbname = 'bookstore';    // Database name
$username = 'root';       // Database username
$password = '';           // Database password
```

### Application URL
Edit `includes/header.php`:
```php
define('APPURL', 'http://localhost/bookstore/');
```

## 📦 Database Schema

### Main Tables
- `users` - Customer and admin accounts
- `products` - Book inventory
- `categories` - Product categories
- `orders` - Customer orders
- `order_items` - Order line items
- `cart` - Shopping cart items

## 🐳 Docker Details

The Docker setup includes:
- **PHP 8.0 with Apache**
- **MySQL 8.0**
- **phpMyAdmin** for database management

Environment variables can be configured in `docker-compose.yml`.

## 🤝 Contributing

Contributions are welcome! Please follow these steps:

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## 📝 License

This project is licensed under the MIT License - see the LICENSE file for details.

## 🐛 Known Issues

- Session management needs enhancement for security
- Add CSRF protection for forms
- Implement input validation and sanitization
- Add password hashing (currently plain text - NOT PRODUCTION READY)

## 🔮 Future Enhancements

- [ ] Payment gateway integration (Stripe, PayPal)
- [ ] Email notifications for orders
- [ ] Product reviews and ratings
- [ ] Advanced search and filters
- [ ] Wishlist functionality
- [ ] Order tracking
- [ ] Multi-language support
- [ ] PDF invoice generation
- [ ] Export reports to Excel/PDF

## 📧 Contact & Support

For support or queries:
- Create an issue on GitHub
- Email: support@bookstore.com

## 🙏 Acknowledgments

- Bootstrap team for the amazing CSS framework
- Font Awesome for beautiful icons
- XAMPP for the development environment
- The PHP community for continuous support

---

**Made with ❤️ for book lovers everywhere**

⭐ Star this repo if you found it helpful!
