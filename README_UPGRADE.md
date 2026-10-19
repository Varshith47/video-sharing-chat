# VideoShare — Video Sharing + Real-Time Chat Upgrade

This upgrade is built on the original PHP/MySQL project. Existing registration/login, video upload, private sharing, likes, comments, views, and library pages are retained. Added features:

- **Video room chat** attached to a video, using database-backed polling (messages typically appear within ~2.5 seconds).
- **Direct messages** between registered users.
- **Typing indicators** for video rooms and direct conversations.
- **Watch together** page with best-effort playback state synchronization (play/pause/seek state is shared; browser autoplay restrictions may require the viewer to press Play).
- Prepared statements for the new chat endpoints, HTML-safe message rendering, session cookie hardening, CSRF token checks on chat writes, and private-video access check in `video.php`.
- `upgrade_chat.sql` adds new tables without dropping the existing tables or records.

## Run with XAMPP on Windows

1. Extract the ZIP into `C:\xampp\htdocs\videoshare` (or another folder under `htdocs`).
2. Start **Apache** and **MySQL** in XAMPP Control Panel.
3. Open `http://localhost/phpmyadmin`.
4. Import `db.sql` to create the `vsp` database. If you already have your database and uploaded videos, do **not** re-import `db.sql` over the existing database.
5. Select database `vsp`, then import `upgrade_chat.sql`. This is the additive chat migration.
6. Check `includes/config.php` defaults: host `localhost`, user `root`, blank password, database `vsp`. Change them if your MySQL setup differs.
7. Open `http://localhost/videoshare/` and log in/register.
8. Open a video you can access and click **Watch & Chat**. Use **Messages** in the navbar for private conversations.

## Existing database users and data

If your old database already has users, videos, likes, comments, and sharing permissions, keep that database. Only import `upgrade_chat.sql`. Existing uploaded video and thumbnail files should remain in the matching `uploads/videos/` and `uploads/thumbnails/` folders.

## GitHub

- Keep the SQL files, PHP, CSS, JavaScript, and README in the repository.
- Do not commit database credentials, `.env` files, real private messages, or user data.
- Uploaded videos can make a Git repository very large. For a public repository, consider removing personal videos from Git and document how to add local sample files.

## Hosting notes

This app requires **PHP + MySQL/MariaDB**; it will not run on GitHub Pages or a Python-only host. Use a PHP host with MySQL. Confirm that the host supports `mysqli`, PHP 8+, file uploads, and sufficient storage. Some free hosts impose low upload limits and may disable large video files. Update PHP `upload_max_filesize` and `post_max_size` where permitted.

## Important limitations and security

- Chat is near-real-time polling, not a persistent WebSocket connection. It is chosen to remain compatible with ordinary PHP/MySQL shared hosting.
- Playback synchronization is best-effort; it does not stream media from one viewer to another. Every viewer needs access to the same uploaded video file.
- The legacy portions of the original project were not fully rewritten in this upgrade; review and harden all production paths before exposing this publicly. Use HTTPS and a strong database password. Never expose private uploads through an unprotected public URL if the videos are intended to be confidential.
- Do not use the included sample accounts/passwords from your old database for real accounts. Register new accounts or reset the old passwords.
- Video uploads should be restricted by server configuration and scanned/validated before public production use. Configure size limits to match your host.

## New files

- `chat.php` — direct messages and watch/chat page
- `chat-api.php` — authenticated polling, sending, typing, and playback endpoints
- `upgrade_chat.sql` — additive database migration
