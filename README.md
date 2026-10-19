# 🎬 Video Sharing & Real-Time Chat Platform

A PHP and MySQL-based web application that allows users to upload, share, and watch videos while interacting through comments, likes, private messaging, and video-room chat.

The project extends a traditional video-sharing platform with chat features and watch-together playback synchronization.
To Try The Project :- video-share-chat.free.nf


## ✨ Features

### 👤 User Authentication
- User registration and login
- Session-based authentication
- Logout functionality
- User-specific video management

### 🎥 Video Sharing
- Upload and share videos
- Video thumbnails
- Browse and watch videos
- Manage your uploaded videos
- Private video-sharing access controls
- Video view counts

### 💬 Interactive Features
- Like videos
- Post and view comments
- Direct messaging between registered users
- Video-room chat
- Typing indicators
- Periodic synchronization of video playback position and play/pause state

### 🔒 Security Features
- Prepared SQL statements for new chat endpoints
- CSRF checks for supported requests
- HTML-safe rendering of chat messages
- Access checks for restricted videos

> **Note:** Security features must be tested across the complete application before production use. Chat updates use database polling rather than a persistent WebSocket connection.

## 🛠️ Tech Stack

| Technology | Purpose |
|---|---|
| PHP | Server-side application logic |
| MySQL | User, video, comment, like, and chat data |
| HTML5 | Application structure and video playback |
| CSS3 | Responsive interface styling |
| JavaScript | Client-side interactions and chat polling |
| Bootstrap | UI components and layout |
| Apache | Local web server through XAMPP |
| Git & GitHub | Version control and source-code hosting |

## 📁 Project Structure

```text
video-sharing-chat/
├── index.php
├── login.php
├── register.php
├── video.php
├── chat.php
├── chat-api.php
├── includes/
│   └── config.php
├── assets/
├── uploads/
│   ├── videos/
│   └── thumbnails/
├── db.sql
├── upgrade_chat.sql
├── README.md
└── .gitignore
```

*The structure above is representative. Actual filenames and folders may differ slightly in your project.*

## ⚙️ Run Locally with XAMPP

### Prerequisites

- Windows, Linux, or macOS
- XAMPP with Apache, PHP, and MySQL
- A web browser
- Git (optional)

### 1. Clone the Repository

```bash
git clone https://github.com/Varshith47/video-sharing-chat.git
```

Move the extracted or cloned project into the XAMPP web directory.

For Windows:

```text
C:\xampp\htdocs\videoshare
```

### 2. Start Apache and MySQL

Open XAMPP Control Panel and start:

- Apache
- MySQL

### 3. Create the Database

Open:

http://localhost/phpmyadmin

Create a database named `vsp`.

Import the original database schema:

```text
db.sql
```

Then import the chat upgrade:

```text
upgrade_chat.sql
```

Import `upgrade_chat.sql` into the same database after the original schema has been created.

If your database already contains the original application's tables and data, do not import `db.sql` again. Apply only the required migration.

### 4. Configure the Database

Open the database configuration file, typically:

```text
includes/config.php
```

Configure the local database credentials to match your MySQL setup. A typical local XAMPP configuration uses:

```text
Host: localhost
Database: vsp
Username: root
Password: (blank by default)
```

Your settings may differ depending on your installation.

**Never commit real database credentials to a public repository.**

### 5. Launch the Application

Open:

http://localhost/videoshare/

Register an account, log in, and test the application's video and messaging features.

## 🌐 Online Deployment

The application can be deployed to a PHP-compatible web host that provides MySQL.

For InfinityFree or another compatible hosting provider:

1. Create a hosting account and website.
2. Create a MySQL database.
3. Import the database schema and migration.
4. Upload the application files to the hosting document root, commonly `htdocs`.
5. Update the database configuration with the host-provided MySQL credentials.
6. Open the assigned website URL and test the application.

**Important:** GitHub hosts the source code but does not execute PHP or provide the application's MySQL database. GitHub Pages alone cannot host this full-stack application.

## 🔄 How Chat Works

The application uses PHP endpoints and MySQL to store and retrieve chat messages. JavaScript periodically requests new messages and updates the interface.

- Direct messaging supports conversations between registered users.
- Video-room chat allows users with access to a video to communicate.
- Typing indicators provide feedback during conversations.
- Playback updates communicate the current video position and play/pause state.

This implementation uses polling. It is not a native WebSocket-based chat server, and browser autoplay restrictions can require users to start playback manually.

## 🔐 Security and Privacy

Before deploying publicly:

- Keep database credentials outside public source control.
- Use HTTPS.
- Validate and restrict uploaded file types and sizes.
- Verify file access permissions for private videos.
- Protect all state-changing requests against CSRF.
- Escape user-generated content before displaying it.
- Add appropriate rate limits and session security.
- Avoid committing real user data, private videos, or production database backups.

## 🧪 Testing Checklist

- [ ] Register a new account.
- [ ] Log in and log out.
- [ ] Upload and play a video.
- [ ] Verify thumbnails and view counts.
- [ ] Like and comment on a video.
- [ ] Test restricted video access with a second account.
- [ ] Send and receive direct messages.
- [ ] Test video-room chat and typing indicators.
- [ ] Verify playback synchronization.
- [ ] Test the application on mobile and desktop.
- [ ] Confirm database changes persist after refresh.

## 🚀 Future Improvements

- Native WebSocket communication
- Real-time online/offline presence
- Read receipts and message notifications
- Improved video transcoding and streaming
- Search, filtering, and playlists
- Profile pictures and user settings
- Better mobile experience
- Automated testing and deployment

## 👨‍💻 Author

**Varshith Reddy Nimma**

Computer Science Engineering Student

- GitHub: https://github.com/Varshith47
- Email: varshithreddy5623@gmail.com

## 📄 License

This project is intended for educational and portfolio purposes. Add an explicit open-source license, such as the MIT License, only if you want to permit others to reuse and modify the code under those terms.
