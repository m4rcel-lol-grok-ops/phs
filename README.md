# pornhub.singles

**A completely unnecessary bio-link website.**

pornhub.singles is an independent humor and parody project: a self-hostable personal profile / bio-link platform with a deliberately ridiculous aesthetic inspired by internet adult-site culture.

**This project is not affiliated with, sponsored by, endorsed by, or operated by Pornhub, Aylo, or any related entities.**

It is a profile service. It does **not** host pornography or sexually explicit user-generated content. Explicit sexual material is prohibited by the content policy.

## Features

- Public profile pages at `/username`
- Unlimited bio links with click tracking
- Themes (Hub, Midnight, Terminal, Corporate, Degenerate, Minimal)
- Custom colors, backgrounds, fonts, and optional visual effects
- Optional background music (HTML5 audio, no autoplay)
- Profile views and link click statistics
- Discover page (search, sort, pagination)
- User dashboard (profile, links, appearance, account)
- Admin panel (users, reports, site settings)
- Report system
- Image uploads (avatar and banner) with MIME validation
- CSRF protection, password hashing, rate limiting
- Fully responsive, accessible, reduced-motion aware
- Docker Compose deployment
- Reverse-proxy friendly (Caddy, nginx, etc.)

## Requirements

- Docker and Docker Compose (recommended)
- Or: PHP 8.3+, MariaDB/MySQL 10.5+, Apache/Nginx with rewrite

## Quick Start (Docker)

```bash
git clone <repository-url> pornhub-singles
cd pornhub-singles
cp .env.example .env
# Edit .env — especially APP_KEY, DB_PASSWORD, ADMIN_PASSWORD
docker compose up -d
```

App will be available at `http://localhost:25169`.

Default admin (created on first boot):

- Username: `admin`
- Email: value of `ADMIN_EMAIL` (default `admin@pornhub.singles`)
- Password: value of `ADMIN_PASSWORD`

Demo user:

- Username: `marcel`
- Password: `demo1234`

## Environment Variables

See `.env.example`. Important ones:

| Variable | Description |
|----------|-------------|
| `APP_URL` | Public URL of the site |
| `APP_KEY` | Random secret string |
| `DB_*` | Database credentials |
| `ADMIN_EMAIL` / `ADMIN_PASSWORD` | Initial admin account |
| `REGISTRATION_ENABLED` | `true` / `false` |
| `DISCOVERY_ENABLED` | `true` / `false` |
| `MAINTENANCE_MODE` | `true` / `false` |
| `UPLOAD_MAX_SIZE` | Max upload bytes (default 2MB) |

## Manual Installation

1. Clone the repo and copy `.env.example` to `.env`
2. Create a MariaDB database and user
3. Point your web server document root to `public/`
4. Enable rewrite (Apache: AllowOverride All; Nginx: try_files to index.php)
5. Run `php database/migrate.php` and `php database/seed.php`
6. Ensure `public/uploads` and `storage` are writable by the web user

## Caddy (system binary)

Caddy is **not** included in Docker Compose. Use your installed `caddy` binary.

1. Docker app listens on host port **25169** (set via `APP_PORT` in `.env`).
2. Point Caddy at that port. Example `Caddyfile` is in the repo root:

```caddyfile
# Development
:80 {
    reverse_proxy 127.0.0.1:25169
}

# Production
pornhub.singles {
    reverse_proxy 127.0.0.1:25169
}
```

3. Run: `caddy run --config Caddyfile` (or use your OS service).

The app respects `X-Forwarded-For` and `X-Forwarded-Proto`. Edit the Caddyfile directly — it does not load `.env`.

## Content Policy

Profiles may contain ordinary personal information, social links, jokes, memes, gaming/dev content, etc.

**Prohibited:**

- Pornographic images/videos
- Explicit sexual material
- Sexual content involving minors
- Exploitation, non-consensual material
- Doxxing, threats, harassment
- Malware, phishing, illegal content
- Impersonation, spam

See `/content-policy` on a running instance.

## Security Recommendations

- Change all default passwords immediately
- Use a strong `APP_KEY`
- Run behind HTTPS (Caddy handles this easily)
- Keep Docker images updated
- Restrict admin access
- Regular backups of the database volume and uploads volume

## Backup

```bash
# Database
docker compose exec db mariadb-dump -u app -p pornhub_singles > backup.sql

# Uploads
docker compose cp app:/var/www/html/public/uploads ./uploads-backup
```

## Updating

```bash
git pull
docker compose build --no-cache
docker compose up -d
```

Migrations run automatically on container start.

## Trademark / Parody Disclaimer

pornhub.singles is a parody and humor project. It is independently created and is **not** affiliated with, sponsored by, endorsed by, or operated by Pornhub, Aylo, or any of their subsidiaries or partners.

The name, aesthetic, and jokes are intended as humorous commentary and internet parody. This is a personal-profile / bio-link service.

Any trademarks belonging to third parties remain the property of their respective owners.

## License

MIT — see [LICENSE](LICENSE).
