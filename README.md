# Personal Contact Manager 
A secure, web-based contact management system built on the LAMP stack. This application allows users to maintain private contact lists with real-time, server-side search capabilities.

#🚀 Live Demo
URL: [ ] 
API Documentation: [ ]

📌 Key Features
Secure Authentication: User registration and login system to ensure private data access.

Contact CRUD: Create, read, update, and delete contacts within a personalized, responsive dashboard.

Server-Side Search: Search API supporting partial name matches (no client-side caching).

Asynchronous UI: Fully AJAX-enabled web client for a "no-refresh" user experience.

JSON Communication: All data exchange between the client and the Linux/Apache server is handled via JSON.

🛠️ Technology Stack
Frontend: HTML5, Tailwind CSS, JavaScript (AJAX/Fetch API)

Backend: PHP (REST-style API)

Database: MySQL (Hosted on a remote server)

Server/Hosting: Linux/Apache hosted on [ ]

Documentation: SwaggerHub (API)

📊 System Architecture
Entity Relationship Diagram (ERD)
The database is structured to ensure that contacts are strictly tied to a unique user ID, preventing any cross-user data leakage.

![Entity Relationship Diagram](erd.png)

Database Schema
Users Table: Stores unique user credentials and hashed passwords.

Contacts Table: Stores names, phone numbers, emails, and the UserID foreign key.

👥 Team Members & Roles
[Aiman Hakim]: [Designed and implemented Tailwind CSS interfaces for registration and login forms to ensure a responsive UI and layout. Developed the PHP backend and MySQL schema using prepared statements for secure user authentication. Authored SwaggerHub documentation to standardize API endpoints and integrated AJAX workflows to enable asynchronous JSON communication between the client and server.]

[ ]: []

[ ]: []

[ ]: [ ]

[ ]: [ ]
