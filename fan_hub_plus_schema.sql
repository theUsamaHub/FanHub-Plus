-- =====================================================================
-- FAN HUB PLUS — FINAL DATABASE SCHEMA
-- Engine: MySQL / MariaDB (InnoDB, utf8mb4)
-- =====================================================================
-- Aligned with the "FAN HUB PLUS — FINAL DATABASE SCHEMA" spec:
--   * Only two actual roles: Registered User and Admin.
--     Guest/Visitor is unauthenticated and is NOT a database role.
--   * Merchandise is display-only — no cart/order/payment tables.
--   * chatbot_faqs / chatbot_queries are optional (chatbot is optional scope).
--   * Polymorphic tables: bookmarks, ratings, reviews (Laravel morph — no
--     single normal FK to every possible target table).
--   * Media stores paths/URLs + metadata only — never file bytes.
--
-- Tables provided by the Laravel starter kit (Breeze) and NOT redefined
-- as their own CREATE statements here (except `users`, shown as
-- IF NOT EXISTS for standalone use):
--   password_reset_tokens, sessions, cache, jobs, personal_access_tokens
--
-- Columns marked "app extension" are extra columns used by the existing
-- admin panel / onboarding features and are not part of the core spec.
-- =====================================================================

SET FOREIGN_KEY_CHECKS = 0;

-- ---------------------------------------------------------------------
-- 1. users
-- Laravel Breeze default users table.
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS users (
    id                  BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name                VARCHAR(255) NOT NULL,
    email               VARCHAR(255) NOT NULL UNIQUE,
    email_verified_at   TIMESTAMP NULL,
    password            VARCHAR(255) NOT NULL,
    remember_token      VARCHAR(100) NULL,
    created_at          TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ---------------------------------------------------------------------
-- 2. roles
-- Only two actual application roles.
-- Initial data:
--   1 | Registered User | registered-user
--   2 | Admin           | admin
-- Guest/Visitor is NOT a role — it means unauthenticated.
-- ---------------------------------------------------------------------
CREATE TABLE roles (
    id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(50) NOT NULL UNIQUE,
    slug        VARCHAR(50) NOT NULL UNIQUE,
    description TEXT NULL COMMENT 'app extension — description shown in admin role management',
    permissions JSON NULL COMMENT 'app extension — optional permission map for admin role management',
    created_at  TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
COMMENT='Only two roles exist: Registered User and Admin.';


-- ---------------------------------------------------------------------
-- 3. role_user
-- Many-to-many between users and roles.
-- ---------------------------------------------------------------------
CREATE TABLE role_user (
    id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id     BIGINT UNSIGNED NOT NULL,
    role_id     BIGINT UNSIGNED NOT NULL,
    created_at  TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_role_user (user_id, role_id),
    CONSTRAINT fk_ru_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_ru_role FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
COMMENT='Many-to-many: which roles each user holds.';


-- ---------------------------------------------------------------------
-- 4. user_profiles
-- User profile + accessibility/display preferences (1:1 with users).
-- ---------------------------------------------------------------------
CREATE TABLE user_profiles (
    id                      BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id                 BIGINT UNSIGNED NOT NULL UNIQUE,
    avatar_media_id         BIGINT UNSIGNED NULL,
    display_name            VARCHAR(100) NULL,
    bio                     TEXT NULL,
    theme_preference        ENUM('light','dark','system') NOT NULL DEFAULT 'system',
    font_size_preference    ENUM('small','medium','large') NOT NULL DEFAULT 'medium',
    onboarding_completed_at TIMESTAMP NULL COMMENT 'app extension — set when user finishes/skips the fandom-picker modal',
    created_at              TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at              TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_profile_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_profile_avatar FOREIGN KEY (avatar_media_id) REFERENCES media(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
COMMENT='Extends users with profile data and light/dark + font-size preferences.';


-- ---------------------------------------------------------------------
-- 5. media
-- Central media registry — file paths/URLs only, never file bytes.
-- duration is stored in seconds (DECIMAL allows fractional seconds);
-- the UI accepts hours+minutes input and converts automatically.
-- ---------------------------------------------------------------------
CREATE TABLE media (
    id                  BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uploaded_by         BIGINT UNSIGNED NULL,
    disk                VARCHAR(50) NOT NULL DEFAULT 'public' COMMENT 'Laravel filesystem disk (public, s3, ...)',
    path                VARCHAR(500) NOT NULL COMMENT 'Relative storage path — the file itself never lives in the DB',
    original_filename   VARCHAR(255) NULL,
    mime_type           VARCHAR(100) NULL,
    media_type          ENUM('image','video','audio','document') NOT NULL DEFAULT 'image',
    size_bytes          BIGINT NULL,
    width               INT NULL COMMENT 'Images/video — avoids layout shift',
    height              INT NULL,
    duration            DECIMAL(10,2) NULL COMMENT 'Video/audio duration in seconds (auto-converted from h+m input)',
    alt_text            VARCHAR(255) NULL COMMENT 'Accessibility text for images',
    created_at          TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_media_uploaded_by FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_media_type (media_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
COMMENT='Single source of truth for uploaded files: paths/metadata only.';


-- ---------------------------------------------------------------------
-- 6. categories
-- The 8 fandom domains: Anime, Gaming, Movies, TV Shows, K-Pop,
-- Comics, Manga, Cosplay.
-- ---------------------------------------------------------------------
CREATE TABLE categories (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name            VARCHAR(100) NOT NULL,
    slug            VARCHAR(120) NOT NULL UNIQUE,
    description     TEXT NULL,
    icon_media_id   BIGINT UNSIGNED NULL,
    created_at      TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_category_icon FOREIGN KEY (icon_media_id) REFERENCES media(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
COMMENT='Fandom domains every content/character/merch/event belongs to.';


-- ---------------------------------------------------------------------
-- 7. user_favorite_categories
-- A user''s favorite fandom categories (many-to-many).
-- ---------------------------------------------------------------------
CREATE TABLE user_favorite_categories (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id         BIGINT UNSIGNED NOT NULL,
    category_id     BIGINT UNSIGNED NOT NULL,
    created_at      TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_user_favorite (user_id, category_id),
    CONSTRAINT fk_ufc_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_ufc_category FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
COMMENT='Which fandoms each user marked as favorite — powers the personalized dashboard.';


-- ---------------------------------------------------------------------
-- 8. contents
-- Articles, videos, audio and images in ONE table — shared lifecycle
-- (category, search/filter, ratings, bookmarks, admin approval workflow).
-- ---------------------------------------------------------------------
CREATE TABLE contents (
    id                  BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category_id         BIGINT UNSIGNED NOT NULL,
    title               VARCHAR(255) NOT NULL,
    slug                VARCHAR(300) NOT NULL UNIQUE,
    type                ENUM('article','video','audio','image') NOT NULL DEFAULT 'article',
    excerpt             TEXT NULL,
    body                LONGTEXT NULL,
    release_date        DATE NULL,
    popularity_score    DECIMAL(10,2) NOT NULL DEFAULT 0,
    view_count          BIGINT NOT NULL DEFAULT 0,
    status              ENUM('draft','pending_review','published','rejected') NOT NULL DEFAULT 'draft',
    is_featured         BOOLEAN NOT NULL DEFAULT FALSE,
    is_user_submitted   BOOLEAN NOT NULL DEFAULT FALSE,
    submitted_by        BIGINT UNSIGNED NULL,
    reviewed_by         BIGINT UNSIGNED NULL,
    published_at        TIMESTAMP NULL,
    created_at          TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_content_category FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE RESTRICT,
    CONSTRAINT fk_content_submitted_by FOREIGN KEY (submitted_by) REFERENCES users(id) ON DELETE SET NULL,
    CONSTRAINT fk_content_reviewed_by FOREIGN KEY (reviewed_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_content_category_status (category_id, status),
    INDEX idx_content_type (type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
COMMENT='Unified content table — one moderation queue for all content types.';


-- ---------------------------------------------------------------------
-- 9. content_media
-- Connects one content item to multiple media files (cover, gallery,
-- trailer, audio clip, attachments).
-- ---------------------------------------------------------------------
CREATE TABLE content_media (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    content_id      BIGINT UNSIGNED NOT NULL,
    media_id        BIGINT UNSIGNED NOT NULL,
    role            ENUM('cover','gallery','trailer','audio_clip','attachment') NOT NULL DEFAULT 'gallery',
    sort_order      INT NOT NULL DEFAULT 0,
    created_at      TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_content_media_role (content_id, media_id, role),
    CONSTRAINT fk_cm_content FOREIGN KEY (content_id) REFERENCES contents(id) ON DELETE CASCADE,
    CONSTRAINT fk_cm_media FOREIGN KEY (media_id) REFERENCES media(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
COMMENT='Pivot: attaches multiple media files to one content item.';


-- ---------------------------------------------------------------------
-- 10. tags
-- Tags for advanced search, filtering and content discovery.
-- ---------------------------------------------------------------------
CREATE TABLE tags (
    id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(100) NOT NULL,
    slug        VARCHAR(120) NOT NULL UNIQUE,
    color       VARCHAR(20) NOT NULL DEFAULT '#4f46e5' COMMENT 'app extension — admin tag color picker',
    created_at  TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
COMMENT='Tag vocabulary for content discovery.';

-- app extension: polymorphic tagging used by categories (admin tag manager)
CREATE TABLE taggables (
    tag_id          BIGINT UNSIGNED NOT NULL,
    taggable_type   VARCHAR(255) NOT NULL,
    taggable_id     BIGINT UNSIGNED NOT NULL,
    PRIMARY KEY (tag_id, taggable_type, taggable_id),
    CONSTRAINT fk_taggables_tag FOREIGN KEY (tag_id) REFERENCES tags(id) ON DELETE CASCADE,
    INDEX idx_taggable (taggable_type, taggable_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
COMMENT='Pivot: polymorphic tagging (used for category tagging).';


-- ---------------------------------------------------------------------
-- 11. content_tags
-- Many-to-many between contents and tags.
-- ---------------------------------------------------------------------
CREATE TABLE content_tags (
    id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    content_id  BIGINT UNSIGNED NOT NULL,
    tag_id      BIGINT UNSIGNED NOT NULL,
    created_at  TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_content_tag (content_id, tag_id),
    CONSTRAINT fk_ct_content FOREIGN KEY (content_id) REFERENCES contents(id) ON DELETE CASCADE,
    CONSTRAINT fk_ct_tag FOREIGN KEY (tag_id) REFERENCES tags(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
COMMENT='Pivot: tag-based search/filtering for content.';


-- ---------------------------------------------------------------------
-- 12. character_profiles
-- Fandom character profiles (card-based, category-filterable).
-- ---------------------------------------------------------------------
CREATE TABLE character_profiles (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category_id     BIGINT UNSIGNED NOT NULL,
    name            VARCHAR(255) NOT NULL,
    slug            VARCHAR(300) NOT NULL UNIQUE,
    bio             TEXT NULL,
    image_media_id  BIGINT UNSIGNED NULL,
    created_at      TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_char_category FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE RESTRICT,
    CONSTRAINT fk_char_image FOREIGN KEY (image_media_id) REFERENCES media(id) ON DELETE SET NULL,
    INDEX idx_char_category (category_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
COMMENT='Character profiles with one hero image, filterable by fandom.';


-- ---------------------------------------------------------------------
-- 13. character_contents
-- Connects characters to related articles/videos/audio/images so a
-- character page can show related content.
-- ---------------------------------------------------------------------
CREATE TABLE character_contents (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    character_id    BIGINT UNSIGNED NOT NULL,
    content_id      BIGINT UNSIGNED NOT NULL,
    created_at      TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_character_content (character_id, content_id),
    CONSTRAINT fk_cc_character FOREIGN KEY (character_id) REFERENCES character_profiles(id) ON DELETE CASCADE,
    CONSTRAINT fk_cc_content FOREIGN KEY (content_id) REFERENCES contents(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
COMMENT='Pivot: related content per character profile.';


-- ---------------------------------------------------------------------
-- 14. merchandise_items
-- Display-only merchandise showcase.
-- No purchase, cart, order or payment functionality (out of scope).
-- ---------------------------------------------------------------------
CREATE TABLE merchandise_items (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category_id     BIGINT UNSIGNED NOT NULL,
    name            VARCHAR(255) NOT NULL,
    slug            VARCHAR(300) NOT NULL UNIQUE,
    description     TEXT NULL,
    image_media_id  BIGINT UNSIGNED NULL,
    tag             ENUM('limited_edition','pre_order','collectible','standard') NOT NULL DEFAULT 'standard',
    is_upcoming     BOOLEAN NOT NULL DEFAULT FALSE,
    view_count      BIGINT NOT NULL DEFAULT 0,
    created_at      TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_merch_category FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE RESTRICT,
    CONSTRAINT fk_merch_image FOREIGN KEY (image_media_id) REFERENCES media(id) ON DELETE SET NULL,
    INDEX idx_merch_category (category_id),
    INDEX idx_merch_upcoming (is_upcoming)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
COMMENT='Display-only merchandise showcase — no purchasing/payment tables.';


-- ---------------------------------------------------------------------
-- 15. events
-- Fan events with optional location/map information.
-- ---------------------------------------------------------------------
CREATE TABLE events (
    id               BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category_id      BIGINT UNSIGNED NULL,
    title            VARCHAR(255) NOT NULL,
    description      TEXT NULL,
    city             VARCHAR(100) NULL COMMENT 'Used for "filterable by city"',
    venue            VARCHAR(255) NULL,
    address          VARCHAR(500) NULL,
    latitude         DECIMAL(10,8) NULL COMMENT 'For map/GPS integration',
    longitude        DECIMAL(11,8) NULL,
    start_at         DATETIME NOT NULL,
    end_at           DATETIME NULL,
    ticket_url       VARCHAR(500) NULL COMMENT 'External ticket link',
    cover_media_id   BIGINT UNSIGNED NULL,
    status           ENUM('draft','published','cancelled') NOT NULL DEFAULT 'draft',
    created_at       TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at       TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_event_category FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
    CONSTRAINT fk_event_cover FOREIGN KEY (cover_media_id) REFERENCES media(id) ON DELETE SET NULL,
    INDEX idx_event_city_date (city, start_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
COMMENT='Conventions, meetups and screenings on the map/calendar.';


-- ---------------------------------------------------------------------
-- 16. bookmarks
-- Polymorphic bookmarks with optional note.
-- bookmarkable_id cannot have one normal FK to multiple tables —
-- integrity is enforced through Laravel models/validation.
-- ---------------------------------------------------------------------
CREATE TABLE bookmarks (
    id                  BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id             BIGINT UNSIGNED NOT NULL,
    bookmarkable_type   VARCHAR(255) NOT NULL COMMENT 'Model being bookmarked, e.g. App\\Models\\Content',
    bookmarkable_id     BIGINT UNSIGNED NOT NULL,
    note                TEXT NULL,
    created_at          TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_bookmark_unique (user_id, bookmarkable_type, bookmarkable_id),
    INDEX idx_bookmark_target (bookmarkable_type, bookmarkable_id),
    CONSTRAINT fk_bookmark_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
COMMENT='Polymorphic bookmarks — content, characters, merch, etc.';


-- ---------------------------------------------------------------------
-- 17. ratings
-- Star ratings or thumbs-up/down (polymorphic).
-- Validation: rating_type = star  -> stars 1..5
--             rating_type = thumbs -> is_thumbs_up true/false
-- ---------------------------------------------------------------------
CREATE TABLE ratings (
    id             BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id        BIGINT UNSIGNED NOT NULL,
    rateable_type  VARCHAR(255) NOT NULL,
    rateable_id    BIGINT UNSIGNED NOT NULL,
    rating_type    ENUM('star','thumbs') NOT NULL DEFAULT 'star',
    stars          TINYINT NULL COMMENT '1-5 when rating_type = star',
    is_thumbs_up   BOOLEAN NULL COMMENT 'TRUE/FALSE when rating_type = thumbs',
    created_at     TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at     TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_rating_unique (user_id, rateable_type, rateable_id),
    INDEX idx_rating_target (rateable_type, rateable_id),
    CONSTRAINT fk_rating_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
COMMENT='Polymorphic numeric ratings (5-star or thumbs up/down).';


-- ---------------------------------------------------------------------
-- 18. reviews
-- Textual user reviews, separate from numeric ratings (polymorphic).
-- ---------------------------------------------------------------------
CREATE TABLE reviews (
    id                BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id           BIGINT UNSIGNED NOT NULL,
    reviewable_type   VARCHAR(255) NOT NULL,
    reviewable_id     BIGINT UNSIGNED NOT NULL,
    title              VARCHAR(255) NULL,
    body              TEXT NOT NULL,
    status            ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
    created_at        TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at        TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_review_target (reviewable_type, reviewable_id),
    INDEX idx_review_status (status),
    CONSTRAINT fk_review_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
COMMENT='Polymorphic written reviews with admin moderation.';


-- ---------------------------------------------------------------------
-- 19. chatbot_faqs
-- OPTIONAL — admin-managed knowledge base for the optional chatbot.
-- ---------------------------------------------------------------------
CREATE TABLE chatbot_faqs (
    id            BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category_id   BIGINT UNSIGNED NULL,
    question      TEXT NOT NULL,
    answer        LONGTEXT NOT NULL,
    created_by    BIGINT UNSIGNED NULL,
    created_at    TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at    TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_faq_category FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
    CONSTRAINT fk_faq_created_by FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
COMMENT='OPTIONAL chatbot knowledge base managed by Admin.';


-- ---------------------------------------------------------------------
-- 20. chatbot_queries
-- OPTIONAL — chatbot conversation/history log.
-- user_id NULL allows guests to use the chatbot; session_id groups
-- guest messages into one conversation.
-- ---------------------------------------------------------------------
CREATE TABLE chatbot_queries (
    id            BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id       BIGINT UNSIGNED NULL,
    session_id    VARCHAR(255) NULL,
    message       TEXT NOT NULL,
    response      LONGTEXT NULL,
    created_at    TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_chatbot_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_chatbot_session (session_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
COMMENT='OPTIONAL chatbot conversation log for context continuity.';


-- ---------------------------------------------------------------------
-- 21. feedback
-- User feedback: bug reports, suggestions and queries.
-- ---------------------------------------------------------------------
CREATE TABLE feedback (
    id            BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id       BIGINT UNSIGNED NULL COMMENT 'NULL allowed — guests can submit feedback',
    type          ENUM('bug','suggestion','query') NOT NULL DEFAULT 'query',
    message       TEXT NOT NULL,
    status        ENUM('open','in_review','resolved','closed') NOT NULL DEFAULT 'open',
    created_at    TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at    TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_feedback_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_feedback_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
COMMENT='Site feedback tracked through an admin resolution workflow.';

-- ---------------------------------------------------------------------
-- 22. LARAVEL BREEZE / STARTER KIT TABLES
-- Provided by the starter kit — keep their normal migrations, do not
-- create custom versions:
--   password_reset_tokens, sessions, cache, jobs, personal_access_tokens
-- ---------------------------------------------------------------------

SET FOREIGN_KEY_CHECKS = 1;

-- =====================================================================
-- END OF SCHEMA
--
-- Note: operational admin-panel tables that live outside this spec
-- (contacts, settings, subscribers, activity_logs, notifications) are
-- created by their own migrations in database/migrations/.
-- =====================================================================
