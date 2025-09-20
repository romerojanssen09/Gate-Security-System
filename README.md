# 🚪 RFID Access Control System

A modern, real-time web-based RFID access control system for managing entry/exit permissions using RFID cards. Built with PHP, MySQL, and real-time WebSocket integration for instant updates.

![PHP](https://img.shields.io/badge/PHP-7.4+-777BB4?style=flat-square&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-5.7+-4479A1?style=flat-square&logo=mysql&logoColor=white)
![Bootstrap](https://img.shields.io/badge/Bootstrap-4.6.2-7952B3?style=flat-square&logo=bootstrap&logoColor=white)
![Pusher](https://img.shields.io/badge/Pusher-WebSockets-300D4F?style=flat-square&logo=pusher&logoColor=white)
![License](https://img.shields.io/badge/License-MIT-green?style=flat-square)

## 🌟 Features

### 🔐 **Core Access Control**
- **Real-time RFID Scanning** - Instant card validation with visual feedback
- **Access Management** - Grant/deny access based on card status and permissions
- **Live Status Updates** - Real-time UI updates without page refreshes
- **Gate Integration Ready** - Prepared for physical gate control systems

### 📊 **Dashboard & Analytics**
- **Live Statistics** - Real-time counters for granted/denied access
- **Recent Activity** - Last 50 access attempts with live updates
- **Visual Indicators** - Color-coded status badges and animations
- **Responsive Design** - Works perfectly on desktop and mobile devices

### 📋 **RFID Card Management**
- **Complete CRUD Operations** - Add, edit, delete, and manage RFID cards
- **User Information** - Store full names, roles, plate numbers, and status
- **Bulk Operations** - Activate/deactivate multiple cards
- **Search & Filter** - Quick card lookup and filtering options

### 📈 **Advanced Reporting**
- **Paginated Logs** - 50 records per page with smooth navigation
- **Date Range Filtering** - Filter logs by specific date ranges
- **Result Filtering** - View only granted or denied access attempts
- **Excel Export** - Export filtered data with custom date ranges
- **Real-time Updates** - New entries appear instantly on the first page

### 🎨 **Enhanced User Experience**
- **Animated RFID Scanner** - Visual state transitions (Waiting → Checking → Result)
- **SweetAlert Integration** - Beautiful, responsive alert dialogs
- **Loading Animations** - Smooth transitions and visual feedback
- **Modern UI Design** - Clean, professional interface with custom styling

## 🚀 **Live Demo**

### **RFID Scanner Interface**
The scanner provides three distinct states with smooth animations:

1. **Waiting State** - "Waiting for RFID card..." with card icon
2. **Checking State** - "Checking card data..." with spinning animation
3. **Result State** - Shows access granted/denied with appropriate colors

### **Real-time Updates**
- Dashboard statistics update instantly when new scans occur
- Recent logs appear in real-time without page refresh
- Reports page shows new entries immediately (first page only)

## 🛠️ **Technology Stack**

### **Backend**
- **PHP 7.4+** - Server-side logic and API endpoints
- **MySQL/SQLite** - Database for storing cards and access logs
- **Composer** - Dependency management
- **Pusher** - Real-time WebSocket communication

### **Frontend**
- **Bootstrap 4.6.2** - Responsive UI framework
- **Font Awesome 6.0** - Icons and visual elements
- **SweetAlert2** - Enhanced alert dialogs
- **Pusher JavaScript SDK** - Real-time client updates
- **Custom CSS3** - Animations and modern styling

### **Architecture**
- **MVC Pattern** - Clean separation of concerns
- **RESTful APIs** - Well-structured API endpoints
- **Real-time Broadcasting** - WebSocket integration
- **Prepared Statements** - SQL injection prevention

## 📦 **Installation**

### **Prerequisites**
- PHP 7.4 or higher
- MySQL 5.7+ or SQLite
- Composer
- Web server (Apache/Nginx)
- Pusher account (for real-time features)

### **Step 1: Clone Repository**
```bash
git clone https://github.com/romerojanssen09/Gate-Security-System.git
cd rfid-access-control
```

### **Step 2: Install Dependencies**
```bash
composer install
```

### **Step 3: Database Setup**
```bash
# Import the database schema
mysql -u your_username -p your_database < gate.sql

# Or run the setup script
php install/database_setup.php
```

### **Step 4: Configuration**
1. **Database Configuration** - Edit `config/database.php`:
```php
<?php
$host = 'localhost';
$username = 'your_username';
$password = 'your_password';
$database = 'your_database';
?>
```

2. **Pusher Configuration** - Edit `config/pusher.php`:
```php
<?php
return [
    'app_id' => 'your_app_id',
    'key' => 'your_key',
    'secret' => 'your_secret',
    'cluster' => 'your_cluster',
    'useTLS' => true
];
?>
```

### **Step 5: Web Server Setup**
Point your web server document root to the project directory and ensure PHP is properly configured.

## 🎯 **Usage**

### **RFID Scanner**
1. Navigate to `rfid_scanner.php`
2. Enter RFID ID manually or scan with hardware
3. View real-time access results with visual feedback

### **Dashboard**
1. Access the main dashboard at `index.php`
2. View live statistics and recent access logs
3. Navigate to detailed reports for advanced filtering

### **Card Management**
1. Go to RFID Cards section
2. Add new cards with user information
3. Edit existing cards or change their status
4. Delete cards that are no longer needed

### **Reports & Export**
1. Visit the Reports page for detailed logs
2. Use date range and result filters
3. Export data to Excel with custom date ranges
4. View paginated results (50 per page)

## 📊 **Database Schema**

### **RFID Cards Table**
```sql
CREATE TABLE rfid_cards (
    id INT PRIMARY KEY AUTO_INCREMENT,
    rfid_id VARCHAR(50) UNIQUE NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    role ENUM('student', 'teacher', 'staff', 'visitor') NOT NULL,
    plate_number VARCHAR(20),
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

### **Access Logs Table**
```sql
CREATE TABLE access_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    rfid_id VARCHAR(50) NOT NULL,
    card_id INT,
    full_name VARCHAR(100),
    access_result ENUM('granted', 'denied') NOT NULL,
    denial_reason VARCHAR(255),
    gate_location VARCHAR(50) NOT NULL,
    ip_address VARCHAR(45),
    access_timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (card_id) REFERENCES rfid_cards(id) ON DELETE SET NULL
);
```

## 🔌 **API Endpoints**

### **RFID Scan API**
```http
POST /api/rfid_scan.php
Content-Type: application/json

{
    "rfid_id": "RFID001",
    "gate_location": "main_gate"
}
```

**Response:**
```json
{
    "success": true,
    "access_result": "granted",
    "full_name": "John Doe",
    "role": "student",
    "plate_number": "ABC123",
    "gate_location": "main_gate",
    "timestamp": "2025-09-20 14:30:00"
}
```

### **Dashboard Statistics API**
```http
GET /api/get_dashboard_stats.php
```

**Response:**
```json
{
    "success": true,
    "stats": {
        "total_logs": 1250,
        "granted_count": 1100,
        "denied_count": 150,
        "today_count": 45,
        "active_cards": 25
    }
}
```

## 🔄 **Real-time Features**

### **Pusher Integration**
The system uses Pusher WebSockets for real-time updates:

- **Channel**: `rfid-access-channel`
- **Event**: `rfid-scanned`
- **Data**: Complete access log information

### **Real-time Updates Include:**
- Dashboard statistics counters
- Recent access logs table
- Reports page new entries (first page only)
- Visual animations and notifications

## 🎨 **UI Components**

### **Enhanced RFID Scanner**
- **Waiting State**: Blue gradient with card icon
- **Checking State**: Pink gradient with spinning loader
- **Success State**: Green gradient with check icon
- **Error State**: Red gradient with warning icon

### **Dashboard Cards**
- **Access Granted**: Green gradient with check icon
- **Access Denied**: Red gradient with X icon
- **Today's Access**: Blue gradient with calendar icon
- **Active Cards**: Orange gradient with ID card icon

### **Status Badges**
- **Granted**: Green badge with check icon
- **Denied**: Red badge with X icon
- **Active**: Green outline badge
- **Inactive**: Gray outline badge

## 🔧 **Configuration Options**

### **Pagination Settings**
```php
// Number of records per page in reports
$recordsPerPage = 50;
```

### **Real-time Settings**
```php
// Pusher configuration
$pusherConfig = [
    'cluster' => 'ap1',
    'useTLS' => true,
    'timeout' => 30
];
```

### **Security Settings**
```php
// Input validation rules
$validationRules = [
    'rfid_id' => 'required|max:50',
    'full_name' => 'required|max:100',
    'role' => 'required|in:student,teacher,staff,visitor'
];
```

## 🧪 **Testing**

### **Sample RFID Cards**
The system comes with pre-loaded test data:

| RFID ID | Name | Role | Status | Expected Result |
|---------|------|------|--------|----------------|
| RFID001 | John Doe | Student | Active | ✅ Granted |
| RFID002 | Jane Smith | Teacher | Active | ✅ Granted |
| RFID003 | Mike Johnson | Staff | Active | ✅ Granted |
| RFID004 | Sarah Wilson | Visitor | Active | ✅ Granted |
| RFID005 | Test User | Student | Inactive | ❌ Denied |
| UNKNOWN | - | - | - | ❌ Denied |

### **Test Scenarios**
1. **Valid Active Card** - Should grant access
2. **Valid Inactive Card** - Should deny access with reason
3. **Unknown Card** - Should deny access as unregistered
4. **Empty/Invalid Input** - Should show validation error

## 📱 **Mobile Responsiveness**

The system is fully responsive and works on:
- **Desktop** - Full feature set with optimal layout
- **Tablet** - Adapted layout with touch-friendly controls
- **Mobile** - Compact design with essential features
- **Scanner Interface** - Optimized for mobile scanning devices

## 🔒 **Security Features**

### **Input Validation**
- SQL injection prevention with prepared statements
- XSS protection with proper output escaping
- Input sanitization and validation
- CSRF protection for forms

### **Access Control**
- Session-based authentication
- Role-based permissions
- Secure password handling
- IP address logging

### **Data Protection**
- Encrypted sensitive data storage
- Secure API endpoints
- Input length limitations
- SQL injection prevention

## 🚀 **Deployment**

### **Production Checklist**
- [ ] Update database credentials
- [ ] Configure Pusher production keys
- [ ] Set up SSL certificate
- [ ] Configure web server security headers
- [ ] Set up database backups
- [ ] Configure error logging
- [ ] Test all real-time features

### **Performance Optimization**
- Database indexes on frequently queried columns
- Efficient SQL queries with proper joins
- Minimal JavaScript and CSS loading
- Optimized image assets
- Gzip compression enabled

## 🤝 **Contributing**

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

### **Development Guidelines**
- Follow PSR-4 autoloading standards
- Use prepared statements for database queries
- Implement proper error handling
- Add comments for complex logic
- Test real-time features thoroughly

## 📄 **License**

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🙏 **Acknowledgments**

- **Bootstrap** - For the responsive UI framework
- **Font Awesome** - For the beautiful icons
- **Pusher** - For real-time WebSocket functionality
- **SweetAlert2** - For enhanced user dialogs
- **Composer** - For dependency management

## 📞 **Support**

If you encounter any issues or have questions:

1. Check the [Issues](https://github.com/romerojanssen09/rfid-access-control/issues) page
2. Create a new issue with detailed information
3. Include steps to reproduce the problem
4. Provide system information and error logs

## 🔮 **Future Enhancements**

- [ ] **Mobile App** - Native iOS/Android applications
- [ ] **Biometric Integration** - Fingerprint and facial recognition
- [ ] **Advanced Analytics** - Detailed reporting and insights
- [ ] **Multi-location Support** - Manage multiple gate locations
- [ ] **Email Notifications** - Automated alerts and reports
- [ ] **API Authentication** - JWT-based API security
- [ ] **Backup & Restore** - Automated data backup system
- [ ] **Audit Trail** - Complete system activity logging

---

**Made with ❤️ for secure access control systems**

*Star ⭐ this repository if you find it helpful!*
