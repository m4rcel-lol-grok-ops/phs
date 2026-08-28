SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(32) NOT NULL UNIQUE,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('user', 'admin') NOT NULL DEFAULT 'user',
    is_verified TINYINT(1) NOT NULL DEFAULT 0,
    is_disabled TINYINT(1) NOT NULL DEFAULT 0,
    email_verified_at DATETIME NULL,
    verification_token VARCHAR(64) NULL,
    reset_token VARCHAR(64) NULL,
    reset_token_expires DATETIME NULL,
    last_login_at DATETIME NULL,
    login_attempts INT UNSIGNED NOT NULL DEFAULT 0,
    locked_until DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_username (username),
    INDEX idx_email (email),
    INDEX idx_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS profiles (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL UNIQUE,
    display_name VARCHAR(64) NULL,
    bio TEXT NULL,
    location VARCHAR(100) NULL,
    website VARCHAR(255) NULL,
    pronouns VARCHAR(32) NULL,
    avatar VARCHAR(255) NULL,
    banner VARCHAR(255) NULL,
    theme VARCHAR(32) NOT NULL DEFAULT 'hub',
    bg_type ENUM('solid','gradient','image','url') NOT NULL DEFAULT 'solid',
    bg_color VARCHAR(7) NOT NULL DEFAULT '#0a0a0a',
    bg_gradient TEXT NULL,
    bg_image VARCHAR(255) NULL,
    bg_url VARCHAR(512) NULL,
    card_color VARCHAR(7) NOT NULL DEFAULT '#1a1a1a',
    accent_color VARCHAR(7) NOT NULL DEFAULT '#ff9900',
    text_color VARCHAR(7) NOT NULL DEFAULT '#ffffff',
    button_color VARCHAR(7) NOT NULL DEFAULT '#ff9900',
    font_family VARCHAR(64) NOT NULL DEFAULT 'system',
    effects_enabled TINYINT(1) NOT NULL DEFAULT 0,
    effect_type VARCHAR(32) NULL,
    music_url VARCHAR(512) NULL,
    music_title VARCHAR(128) NULL,
    music_artist VARCHAR(128) NULL,
    is_public TINYINT(1) NOT NULL DEFAULT 1,
    show_in_discover TINYINT(1) NOT NULL DEFAULT 1,
    profile_views INT UNSIGNED NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_public (is_public, show_in_discover),
    INDEX idx_views (profile_views)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS links (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    profile_id INT UNSIGNED NOT NULL,
    title VARCHAR(100) NOT NULL,
    url VARCHAR(512) NOT NULL,
    description VARCHAR(255) NULL,
    icon VARCHAR(64) NULL,
    emoji VARCHAR(16) NULL,
    is_enabled TINYINT(1) NOT NULL DEFAULT 1,
    sort_order INT NOT NULL DEFAULT 0,
    click_count INT UNSIGNED NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (profile_id) REFERENCES profiles(id) ON DELETE CASCADE,
    INDEX idx_profile (profile_id),
    INDEX idx_order (profile_id, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS social_links (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    profile_id INT UNSIGNED NOT NULL,
    platform VARCHAR(32) NOT NULL,
    url VARCHAR(512) NOT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    FOREIGN KEY (profile_id) REFERENCES profiles(id) ON DELETE CASCADE,
    INDEX idx_profile (profile_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS profile_views (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    profile_id INT UNSIGNED NOT NULL,
    viewer_ip VARCHAR(45) NULL,
    user_agent VARCHAR(512) NULL,
    viewed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (profile_id) REFERENCES profiles(id) ON DELETE CASCADE,
    INDEX idx_profile_date (profile_id, viewed_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS link_clicks (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    link_id INT UNSIGNED NOT NULL,
    clicker_ip VARCHAR(45) NULL,
    clicked_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (link_id) REFERENCES links(id) ON DELETE CASCADE,
    INDEX idx_link (link_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS reports (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    reporter_id INT UNSIGNED NULL,
    reported_user_id INT UNSIGNED NOT NULL,
    report_type ENUM('profile','link','avatar','banner','biography','other') NOT NULL,
    target_id INT UNSIGNED NULL,
    reason TEXT NOT NULL,
    status ENUM('pending','reviewed','dismissed','actioned') NOT NULL DEFAULT 'pending',
    admin_notes TEXT NULL,
    reviewed_by INT UNSIGNED NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (reporter_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (reported_user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_status (status),
    INDEX idx_reported (reported_user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS admin_logs (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    admin_id INT UNSIGNED NOT NULL,
    action VARCHAR(64) NOT NULL,
    target_type VARCHAR(32) NULL,
    target_id INT UNSIGNED NULL,
    details TEXT NULL,
    ip_address VARCHAR(45) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_admin (admin_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS site_settings (
    setting_key VARCHAR(64) PRIMARY KEY,
    setting_value TEXT NULL,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS sessions (
    id VARCHAR(128) PRIMARY KEY,
    user_id INT UNSIGNED NULL,
    ip_address VARCHAR(45) NULL,
    user_agent VARCHAR(512) NULL,
    payload TEXT NOT NULL,
    last_activity INT UNSIGNED NOT NULL,
    INDEX idx_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
