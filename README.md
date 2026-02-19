# 📱 Sonic Contact Manager 
A secure, Sonic-themed contact manager built on the LAMP stack. Our app allows users to maintain personal contact lists with real-time, server-side search capabilities.

# 🚀 Live Demo
URL: https://sonic-contact-manager.up.railway.app/

API Documentation: https://app.swaggerhub.com/apis/ucf-ed8/contact-manager-api/1.0.0

# 📌 Key Features
**Login & Registration Authentication**: User and login registration forms to ensure private data access.

**Contact CRUD**: Create, read, update, and delete contacts within a personalized, responsive dashboard.

**Server-Side Search**: Search API supporting partial name matches (no client-side caching).

**Asynchronous UI**: Fully AJAX-enabled web client, a "no refresh" necessary user experience.

**JSON Communication**: All data exchange between the client and the Linux/Apache server is handled via JSON.

# 🛠️ Technology Stack
**Frontend**: HTML5, Tailwind CSS, JavaScript (AJAX/Fetch API)

**Backend**: PHP (REST-style API)

**Database**: MySQL (Hosted on a remote server)

**Server/Hosting**: Linux/Apache hosted on Railway

**Documentation**: SwaggerHub (API)

# 📊 System Architecture
**Entity Relationship Diagram (ERD)**

The database follows a relational model designed to maintain private contact lists, centering on a one-to-many relationship between the users and contacts tables. Each contact is owned by a single user and remains inaccessible to others.

**Users Table**: Stores unique user credentials such as their full names, email address, and hashed passwords.

**Contacts Table**: Stores names, phone numbers, emails, and the User ID foreign key.

**Primary Keys**: Both tables contain auto-incrementing integers as primary keys (User ID and Contacts ID), ensuring every record is uniquely identifiable and indexed for retrieval.

**Foreign Key Constraint**: The contacts table includes a User ID foreign key, which maps each contact record back to its specific user. This constraint enforces referential integrity; a contact cannot exist without a valid associated user.

**One-to-Many Relationship**: This structure allows a single user to manage an unlimited number of private contacts. By filtering queries based on the User ID foreign key, the system prevents "shared contacts" and ensures that the server-side search only returns results relevant to the logged-in session.

**Data Integrity & Security**: Prepared statements are utilized within the PHP backend to interact with these tables. This approach separates the SQL logic from user-provided data, maintaining the structural integrity of the MySQL schema during asynchronous AJAX operations.

![Entity Relationship Diagram](frontend/images/erd.png)

# 👥 Team Members & Roles
**Amy Hakim**: Designed and implemented Tailwind CSS interfaces for registration and login forms to ensure a responsive UI and layout. Developed the PHP backend and MySQL schema using prepared statements for secure user authentication. Authored SwaggerHub documentation to standardize API endpoints and integrated AJAX workflows to enable asynchronous JSON communication between the client and server.

**Samantha Amaro**: Project Manager & Frontend Developer. Developed responsive login, registration, and dashboard interfaces using HTML, CSS, and Tailwind CSS. Designed reusable contact components that dynamically render API responses into interactive UI elements. Collaborated on engineering RESTful API integration between frontend (Javascript) and backend (PHP), and its CRUD functionality for contact management.

**Harmony Hamilton**: Helped design database schema. Developed the PHP for user registration as well as add and deletion of contacts using prepared statements to the database. Developed API call functions in Javascript.

**Jeremiah Registre**: Created original database structure. Developed the PHP for updating contact information. Documented API behavior using Swagger.

**Aileen Seidl**: Developed the search contacts api. Added comments to code for clarity.

