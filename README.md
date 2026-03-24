# Street Care - Empowering Communities

Street Care (CivicConnect) is a powerful civic engagement platform designed to bridge the gap between citizens, volunteers, and local authorities. It empowers community members to report urban issues, volunteer for local improvement tasks, and enables authorities to manage and resolve civic problems efficiently.

## 🌟 Key Features

### 👤 Citizens
- **Easy Reporting**: Snap photos, tag locations, and report civic issues (garbage, road damage, lighting, etc.) in seconds.
- **Track Progress**: Real-time updates on reported issues from "Pending" to "Resolved".
- **Impact Tracking**: Earn badges and recognition for participating in community care.

### 🤝 Volunteers
- **Community Tasks**: Browse and join local volunteer tasks to fix reported issues.
- **Task Management**: Manage availability and track contributions to neighborhood improvements.
- **Recognition**: Build a profile showcasing community impact.

### 🏛️ Authorities & Admins
- **Smart Governance**: Bird's-eye view of all reported issues with heatmaps and analytics.
- **Efficient Dispatch**: Assign tasks to teams or volunteers for faster resolution.
- **System Oversight**: Manage user roles, monitor system health, and track overall civic improvement stats.

## 🛠️ Tech Stack

- **Frontend**: 
  - Responsive HTML5 & Vanilla CSS.
  - Interactive JavaScript (Vanilla).
  - Modern Animations using AOS (Animate On Scroll).
  - Elegant Typography (Google Fonts - Outfit).
  - Scalable Icons (Font Awesome).
- **Backend**:
  - PHP (RESTful API architecture).
- **Database**:
  - MySQL with a robust relational schema.

## 📂 Project Structure

- `/civicconnect`: Root application folder.
  - `/frontend`: User interfaces for Citizens, Volunteers, Authorities, and Admins.
  - `/backend`: Core logic including API endpoints, configuration, and utility functions.
  - `/assets`: Images and shared media resources.
  - `database.sql`: MySQL schema for easy database setup.
  - `setup_db.php`: Automated database initialization script.

## 🚀 Setup Instructions

### Prerequisites
- PHP 7.4 or higher.
- MySQL/MariaDB.
- Web Server (e.g., Apache via XAMPP/WAMP).

### Database Configuration
1. Create a MySQL database named `civicconnect`.
2. Import the `database.sql` file located in the project root.
3. Update `backend/config/database.php` (if applicable) with your credentials.

### Running the Application
1. Place the project folder in your web server's root directory (e.g., `htdocs` for XAMPP).
2. Start the Apache and MySQL services.
3. Navigate to `http://localhost/street care/civicconnect` in your browser.

---
*Making cities smarter, safer, and cleaner through community collaboration.*
