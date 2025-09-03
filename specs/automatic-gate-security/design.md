# Design Document

## Overview

The Automatic Gate Security System is a web-based PHP application that provides RFID-based access control for Holy Family High School. The system follows a Model-View-Controller (MVC) architecture pattern and uses MySQL for data persistence. The application consists of an admin authentication system, RFID card management, real-time access logging, and comprehensive reporting capabilities.

## Architecture

### System Architecture
The system follows a three-tier architecture:

1. **Presentation Layer**: Web-based user interface built with HTML, CSS, JavaScript, and Bootstrap
2. **Application Layer**: PHP-based business logic handling authentication, RFID processing, and data management
3. **Data Layer**: MySQL database storing user credentials, RFID cards, access logs, and system configuration

### Technology Stack
- **Backend**: PHP 7.4+
- **Database**: MySQL 5.7+
- **Frontend**: HTML5, CSS3, JavaScript, Bootstrap 4
- **Web Server**: Apache/Nginx
- **Session Management**: PHP Sessions
- **Security**: Password hashing (PHP password_hash), SQL injection prevention

## Components and Interfaces

### 1. Authentication Module
**Purpose**: Handles admin login/logout and session management

**Components**:
- `AuthController`: Manages login/logout logic
- `SessionManager`: Handles session creation, validation, and cleanup
- `LoginView`: Login form interface

**Key Methods**:
- `authenticate($username, $password)`: Validates admin credentials
- `createSession($adminId)`: Creates secure session
- `validateSession()`: Checks session validity
- `logout()`: Destroys session and redirects

### 2. RFID Card Management Module
**Purpose**: Manages RFID card registration, updates, and deactivation

**Components**:
- `RFIDController`: Handles CRUD operations for RFID cards
- `RFIDModel`: Database operations for card data
- `CardManagementView`: Interface for managing cards

**Key Methods**:
- `addCard($rfidId, $name, $role, $plateNumber)`: Registers new RFID card
- `updateCard($cardId, $data)`: Updates existing card details
- `deactivateCard($cardId)`: Deactivates card access
- `searchCards($query)`: Searches cards by name or RFID ID

### 3. Access Control Module
**Purpose**: Processes RFID scans and controls gate access

**Components**:
- `AccessController`: Handles RFID scan requests
- `GateController`: Controls gate mechanism
- `AccessLogger`: Logs all access attempts

**Key Methods**:
- `processRFIDScan($rfidId)`: Validates RFID and grants/denies access
- `openGate()`: Activates gate opening mechanism
- `logAccess($rfidId, $result, $timestamp)`: Records access attempt

### 4. Logging and Reporting Module
**Purpose**: Manages access logs and generates reports

**Components**:
- `LogController`: Manages log viewing and filtering
- `ReportGenerator`: Creates PDF/Excel reports
- `LogModel`: Database operations for access logs

**Key Methods**:
- `getAccessLogs($filters)`: Retrieves filtered access logs
- `generateReport($dateRange, $format)`: Creates downloadable reports
- `getStatistics()`: Calculates access statistics

### 5. Dashboard Module
**Purpose**: Provides real-time monitoring interface

**Components**:
- `DashboardController`: Manages dashboard data
- `AlertManager`: Handles security alerts
- `DashboardView`: Real-time monitoring interface

**Key Methods**:
- `getDashboardData()`: Retrieves current system status
- `getRecentActivity()`: Gets latest access attempts
- `checkAlerts()`: Monitors for security incidents

## Data Models

### Database Schema

#### 1. admins Table
```sql
CREATE TABLE admins (
    id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
```

#### 2. rfid_cards Table
```sql
CREATE TABLE rfid_cards (
    id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    rfid_id VARCHAR(50) NOT NULL UNIQUE,
    full_name VARCHAR(100) NOT NULL,
    role ENUM('student', 'teacher', 'staff', 'visitor') NOT NULL,
    plate_number VARCHAR(20) NULL,
    status ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
    created_by INT(11) UNSIGNED,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES admins(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
```

#### 3. access_logs Table
```sql
CREATE TABLE access_logs (
    id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    rfid_id VARCHAR(50) NOT NULL,
    card_id INT(11) UNSIGNED NULL,
    full_name VARCHAR(100) NULL,
    access_result ENUM('granted', 'denied') NOT NULL,
    denial_reason VARCHAR(100) NULL,
    gate_location VARCHAR(50) DEFAULT 'main_gate',
    access_timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ip_address VARCHAR(45) NULL,
    INDEX idx_rfid_timestamp (rfid_id, access_timestamp),
    INDEX idx_timestamp (access_timestamp),
    FOREIGN KEY (card_id) REFERENCES rfid_cards(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
```

#### 4. system_settings Table
```sql
CREATE TABLE system_settings (
    id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(50) NOT NULL UNIQUE,
    setting_value TEXT NOT NULL,
    description VARCHAR(255) NULL,
    updated_by INT(11) UNSIGNED,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (updated_by) REFERENCES admins(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
```

### Data Relationships
- **admins** → **rfid_cards**: One admin can create many RFID cards
- **rfid_cards** → **access_logs**: One RFID card can have many access attempts
- **admins** → **system_settings**: One admin can update many settings

## Error Handling

### Error Categories
1. **Authentication Errors**: Invalid credentials, session timeout
2. **Database Errors**: Connection failures, query errors
3. **RFID Errors**: Invalid card format, scanner communication issues
4. **Validation Errors**: Invalid input data, duplicate entries
5. **System Errors**: File permissions, configuration issues

### Error Handling Strategy
- **Logging**: All errors logged to `logs/error.log` with timestamp and context
- **User Feedback**: User-friendly error messages without exposing system details
- **Graceful Degradation**: System continues operating with reduced functionality when possible
- **Recovery**: Automatic retry mechanisms for transient failures

### Error Response Format
```php
{
    "success": false,
    "error": {
        "code": "RFID_NOT_FOUND",
        "message": "RFID card not found in system",
        "details": "Card ID: ABC123 is not registered"
    }
}
```

## Testing Strategy

### Unit Testing
- **Models**: Test database operations, data validation
- **Controllers**: Test business logic, request handling
- **Utilities**: Test helper functions, security functions

### Integration Testing
- **Database Integration**: Test complete CRUD operations
- **RFID Scanner Integration**: Test hardware communication
- **Session Management**: Test login/logout flows

### Security Testing
- **SQL Injection**: Test all database queries with malicious input
- **XSS Prevention**: Test output sanitization
- **Session Security**: Test session hijacking prevention
- **Access Control**: Test unauthorized access attempts

### Performance Testing
- **Database Performance**: Test query performance with large datasets
- **Concurrent Access**: Test multiple simultaneous RFID scans
- **Report Generation**: Test large report creation performance

### User Acceptance Testing
- **Admin Workflows**: Test complete admin task flows
- **RFID Scanning**: Test real-world RFID scanning scenarios
- **Report Generation**: Test report accuracy and formatting
- **Dashboard Monitoring**: Test real-time updates and alerts

## Security Considerations

### Authentication Security
- Password hashing using PHP `password_hash()` with BCRYPT
- Session tokens with secure random generation
- Session timeout after 30 minutes of inactivity
- Account lockout after 3 failed login attempts

### Data Security
- SQL injection prevention using prepared statements
- XSS prevention through output sanitization
- CSRF protection using tokens
- Input validation and sanitization

### Access Control
- Role-based access control for admin functions
- Session validation on every request
- Secure session configuration (httpOnly, secure flags)

### Database Security
- Database user with minimal required privileges
- Regular database backups
- Encrypted sensitive data storage
- Connection encryption (SSL/TLS)

## Deployment Architecture

### File Structure
```
/automatic-gate-security/
├── index.php                 # Main entry point
├── config/
│   ├── database.php         # Database configuration
│   └── settings.php         # Application settings
├── controllers/
│   ├── AuthController.php
│   ├── DashboardController.php
│   ├── RFIDController.php
│   └── ReportController.php
├── models/
│   ├── Admin.php
│   ├── RFIDCard.php
│   └── AccessLog.php
├── views/
│   ├── auth/
│   │   └── login.php
│   ├── dashboard/
│   │   └── index.php
│   ├── rfid/
│   │   ├── manage.php
│   │   └── add.php
│   └── reports/
│       └── logs.php
├── assets/
│   ├── css/
│   ├── js/
│   └── images/
├── logs/
└── uploads/
```

### System Requirements
- **PHP**: 7.4 or higher
- **MySQL**: 5.7 or higher
- **Web Server**: Apache 2.4+ or Nginx 1.18+
- **Memory**: Minimum 512MB RAM
- **Storage**: Minimum 1GB available space
- **Network**: Stable internet connection for remote monitoring