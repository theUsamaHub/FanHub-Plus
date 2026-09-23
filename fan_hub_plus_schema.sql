-- =====================================================================
-- FAN HUB PLUS — DATABASE SCHEMA
-- Engine: MySQL / MariaDB (InnoDB, utf8mb4)
-- =====================================================================
-- NOTE: The following tables are assumed to already exist from the
-- Laravel starter kit (Breeze) and are NOT redefined here:
--   users, roles, role_user, password_reset_tokens, sessions,
--   cache, jobs, personal_access_tokens
--
-- This script only adds the tables Fan Hub Plus needs on top of that.
-- Every table below stores PATHS/URLS only where files are involved —
-- never the actual file bytes. Files live on disk/S3/CDN; the `media`
-- table is the single place that tracks where each one lives.
-- =====================================================================

SET FOREIGN_KEY_CHECKS = 0;

-- ---------------------------------------------------------------------
-- 1. media
-- WHY THIS TABLE EXISTS:
-- Central registry for every uploaded file across the whole app —
-- avatars, article covers, character images, merch photos, trailers,
-- podcast/soundtrack clips. Instead of every other table having its
-- own "image_path" column (duplication, inconsistent validation), they
-- all point here via a foreign key. This is also what makes "Admin-
-- controlled tagging and categorization of media" (SRS 1.6) possible
-- in one place, and it's the only table that ever touches a file path.
-- ---------------------------------------------------------------------
CREATE TABLE media (
    id                  BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uploaded_by         BIGINT UNSIGNED NULL COMMENT 'User who uploaded it; NULL for system/seeded assets',
    disk                VARCHAR(50) NOT NULL DEFAULT 'public' COMMENT 'Laravel filesystem disk name (public, s3, etc.) — tells the app WHERE to resolve the path from',
    path                VARCHAR(500) NOT NULL COMMENT 'Relative storage path only. The actual file is never stored in the DB.',
    original_filename   VARCHAR(255) NULL COMMENT 'Original name at upload time, for display/download purposes',
    mime_type           VARCHAR(100) NULL,
    media_type          ENUM('image','video','audio','document') NOT NULL DEFAULT 'image' COMMENT 'High-level type, used to pick the right player/renderer in the UI',
    size_bytes          BIGINT UNSIGNED NULL,
    width_px            INT UNSIGNED NULL COMMENT 'Populated for images/video, used to avoid layout shift',
    height_px           INT UNSIGNED NULL,
    duration_seconds    INT UNSIGNED NULL COMMENT 'Populated for video/audio (trailers, podcasts, soundtracks) i am should be able to select the time in hour and seconds , but it should automatically convert it to seconds ',
    alt_text            VARCHAR(255) NULL COMMENT 'Accessibility text for images — required by NFR "Accessibility"',
    created_at          TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_media_uploaded_by FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_media_type (media_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
COMMENT='Single source of truth for uploaded files: stores paths/metadata only, never file bytes.';


-- ---------------------------------------------------------------------
-- 2. user_profiles
-- WHY THIS TABLE EXISTS:
-- Breeze's `users` table only gives you name/email/password. The SRS
-- (1.6) asks for "editable favorite fandoms, categories of interest,
-- and display preferences" plus an optional avatar — none of which
-- belongs on the auth table. Keeping it 1:1-separate means the auth
-- table stays lean and this table can grow without touching login logic.
-- ---------------------------------------------------------------------
CREATE TABLE user_profiles (
    id                      BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id                 BIGINT UNSIGNED NOT NULL UNIQUE COMMENT 'One profile per user (1:1 with users table)',
    avatar_media_id         BIGINT UNSIGNED NULL COMMENT 'Optional profile picture, points to media.id (SRS: optional avatar upload)',
    display_name            VARCHAR(100) NULL COMMENT 'Public-facing name shown on submitted content/reviews, separate from real name',
    bio                     TEXT NULL,
    theme_preference        ENUM('light','dark','system') NOT NULL DEFAULT 'system' COMMENT 'Dark mode toggle, required by NFR "Accessibility and UI Enhancements"',
    font_size_preference    ENUM('small','medium','large') NOT NULL DEFAULT 'medium' COMMENT 'Font-size adjustment, required by NFR "Accessibility"',
    created_at              TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at              TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_profile_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_profile_avatar FOREIGN KEY (avatar_media_id) REFERENCES media(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
COMMENT='Extends Breeze users with fandom-site-specific profile data and display preferences.';


ALTER TABLE user_profiles
    ADD COLUMN onboarding_completed_at TIMESTAMP NULL
    COMMENT 'Set when user finishes or skips the fandom-picker modal — prevents it from reappearing';

-- ---------------------------------------------------------------------
-- 3. categories
-- WHY THIS TABLE EXISTS:
-- The 8 fandom domains (Anime, Gaming, Movies, TV Shows, K-Pop, Comics,
-- Manga, Cosplay) are the backbone of the whole site — content,
-- character profiles, merchandise, and events are all filtered by this.
-- A real table (not a hardcoded enum) lets admins add/rename categories
-- later without a migration, per "Admin Control Panel > Category content".
-- ---------------------------------------------------------------------
CREATE TABLE categories (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name            VARCHAR(100) NOT NULL,
    slug            VARCHAR(120) NOT NULL UNIQUE COMMENT 'URL-friendly identifier, e.g. "k-pop"',
    description     VARCHAR(500) NULL,
    icon_media_id   BIGINT UNSIGNED NULL COMMENT 'Small icon/thumbnail representing the category in nav/filters',
    created_at      TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_category_icon FOREIGN KEY (icon_media_id) REFERENCES media(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
COMMENT='The 8 fandom domains that every piece of content, character, and event is filtered by.';


-- ---------------------------------------------------------------------
-- 4. user_favorite_categories
-- WHY THIS TABLE EXISTS:
-- SRS 1.6: profile has "editable favorite fandoms" and the dashboard
-- shows "favorite fandoms". A user can like more than one fandom and a
-- fandom has many fans, so this is the many-to-many pivot connecting
-- user_profiles to categories.
-- ---------------------------------------------------------------------
CREATE TABLE user_favorite_categories (
    user_id      BIGINT UNSIGNED NOT NULL,
    category_id  BIGINT UNSIGNED NOT NULL,
    created_at   TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id, category_id),
    CONSTRAINT fk_ufc_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_ufc_category FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
COMMENT='Many-to-many: which fandoms each user has marked as a favorite, powers the personalized dashboard.';


-- ---------------------------------------------------------------------
-- 5. contents
-- WHY THIS TABLE EXISTS:
-- Covers the "Fandom Content Explorer" (articles, profiles-adjacent
-- write-ups, media items) in ONE table instead of separate tables per
-- type, since the SRS content types (article/video/audio/image) all
-- share the same lifecycle: category, search/filter, sorting, ratings,
-- bookmarking, and — critically — the same admin approval workflow for
-- "users submit fan content or articles (admin approval required)".
-- One table = one moderation queue instead of four.
-- ---------------------------------------------------------------------
CREATE TABLE contents (
    id                  BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category_id         BIGINT UNSIGNED NOT NULL,
    title               VARCHAR(255) NOT NULL,
    slug                VARCHAR(280) NOT NULL UNIQUE,
    type                ENUM('article','video','audio','image') NOT NULL DEFAULT 'article' COMMENT 'Drives which player/renderer the frontend uses',
    excerpt             VARCHAR(500) NULL COMMENT 'Short summary shown in listing/grid views',
    body                LONGTEXT NULL COMMENT 'Rich text body for articles / featured articles (SRS: embedded images, timeline-style highlights)',
    release_date        DATE NULL COMMENT 'For "upcoming release listings" sorting/filtering',
    popularity_score     INT NOT NULL DEFAULT 0 COMMENT 'Precomputed ranking score for "most popular" sort option',
    view_count          BIGINT UNSIGNED NOT NULL DEFAULT 0,
    status              ENUM('draft','pending_review','published','rejected') NOT NULL DEFAULT 'draft' COMMENT 'Drives the admin moderation queue',
    is_featured         BOOLEAN DEFAULT FALSE
    is_user_submitted   TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'TRUE if a registered user submitted this as fan content, per SRS approval requirement',
    submitted_by        BIGINT UNSIGNED NULL COMMENT 'The user who submitted it (NULL for admin/staff-authored content)',
    reviewed_by         BIGINT UNSIGNED NULL COMMENT 'Admin who approved/rejected it',
    published_at        TIMESTAMP NULL,
    created_at          TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_content_category FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE RESTRICT,
    CONSTRAINT fk_content_submitted_by FOREIGN KEY (submitted_by) REFERENCES users(id) ON DELETE SET NULL,
    CONSTRAINT fk_content_reviewed_by FOREIGN KEY (reviewed_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_content_category_status (category_id, status),
    INDEX idx_content_type (type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
COMMENT='Unified table for articles, videos, audio, and image content — one moderation workflow for all types.';


-- ---------------------------------------------------------------------
-- 6. content_media
-- WHY THIS TABLE EXISTS:
-- One article can have a cover image AND a gallery AND an embedded
-- trailer. This pivot lets a single `contents` row attach to MULTIPLE
-- `media` rows, each tagged with a role, which is exactly what the
-- "Interactive Multimedia Center" and image galleries need.
-- ---------------------------------------------------------------------
CREATE TABLE content_media (
    id           BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    content_id   BIGINT UNSIGNED NOT NULL,
    media_id     BIGINT UNSIGNED NOT NULL,
    role         ENUM('cover','gallery','trailer','audio_clip','attachment') NOT NULL DEFAULT 'gallery' COMMENT 'What this file represents within the content item',
    sort_order   INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Display order within a gallery',
    CONSTRAINT fk_cm_content FOREIGN KEY (content_id) REFERENCES contents(id) ON DELETE CASCADE,
    CONSTRAINT fk_cm_media FOREIGN KEY (media_id) REFERENCES media(id) ON DELETE CASCADE,
    UNIQUE KEY uq_content_media_role (content_id, media_id, role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
COMMENT='Pivot: attaches multiple media files (cover, gallery images, trailer) to one content item.';


-- ---------------------------------------------------------------------
-- 7. character_profiles
-- WHY THIS TABLE EXISTS:
-- SRS explicitly calls out "Character Profiles and Featured Articles
-- Hub" as its own feature with card-based, fandom-filterable profiles —
-- distinct enough from generic `contents` (bio format, one hero image,
-- no rich article body) that it earns its own table.
-- ---------------------------------------------------------------------
CREATE TABLE character_profiles (
    id                BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category_id       BIGINT UNSIGNED NOT NULL,
    name              VARCHAR(150) NOT NULL,
    slug              VARCHAR(180) NOT NULL UNIQUE,
    bio               TEXT NULL,
    image_media_id    BIGINT UNSIGNED NULL COMMENT 'Main portrait/card image, points to media.id',
    created_at        TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at        TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_char_category FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE RESTRICT,
    CONSTRAINT fk_char_image FOREIGN KEY (image_media_id) REFERENCES media(id) ON DELETE SET NULL,
    INDEX idx_char_category (category_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
COMMENT='Card-based character profiles, filterable by fandom category.';


-- ---------------------------------------------------------------------
-- 8. merchandise_items
-- WHY THIS TABLE EXISTS:
-- SRS: "Merchandise Showcase and Resource Library" — display/discovery
-- only, NOT a purchasable product (no price, no cart, no payment per
-- Constraints 1.5). Kept separate from `contents` because it has its
-- own tag vocabulary (Limited Edition / Pre-Order / Collectible) and
-- an "is_upcoming" flag that content items don't need.
-- ---------------------------------------------------------------------
CREATE TABLE merchandise_items (
    id                BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category_id       BIGINT UNSIGNED NOT NULL,
    name              VARCHAR(200) NOT NULL,
    slug              VARCHAR(220) NOT NULL UNIQUE,
    description       TEXT NULL,
    image_media_id    BIGINT UNSIGNED NULL,
    tag               ENUM('limited_edition','pre_order','collectible','standard') NOT NULL DEFAULT 'standard' COMMENT 'Backend-driven tagging per SRS example',
    is_upcoming       TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'TRUE = shown in "Upcoming releases" section',
    view_count        BIGINT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Optional: admin can track view count/popularity per SRS',
    created_at        TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at        TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_merch_category FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE RESTRICT,
    CONSTRAINT fk_merch_image FOREIGN KEY (image_media_id) REFERENCES media(id) ON DELETE SET NULL,
    INDEX idx_merch_category (category_id),
    INDEX idx_merch_upcoming (is_upcoming)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
COMMENT='Display-only merchandise showcase — no purchasing/payment functionality (explicitly out of scope).';


-- ---------------------------------------------------------------------
-- 9. events
-- WHY THIS TABLE EXISTS:
-- SRS "Location-Aware Event Discovery and Calendar" needs its own
-- table because it carries geo-coordinates (for map/GPS integration)
-- and date ranges that nothing else in the schema needs.
-- ---------------------------------------------------------------------
CREATE TABLE events (
    id               BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category_id      BIGINT UNSIGNED NULL COMMENT 'Optional link to a fandom, e.g. an anime convention',
    title            VARCHAR(200) NOT NULL,
    description      TEXT NULL,
    city             VARCHAR(100) NOT NULL COMMENT 'Used for "filterable by city" requirement',
    venue            VARCHAR(255) NULL,
    address          VARCHAR(255) NULL,
    latitude         DECIMAL(10,8) NULL COMMENT 'For map/GPS integration',
    longitude        DECIMAL(11,8) NULL,
    start_at         DATETIME NOT NULL,
    end_at           DATETIME NULL,
    ticket_url       VARCHAR(500) NULL COMMENT 'External ticket link, per SRS',
    cover_media_id   BIGINT UNSIGNED NULL,
    created_at       TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at       TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_event_category FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
    CONSTRAINT fk_event_cover FOREIGN KEY (cover_media_id) REFERENCES media(id) ON DELETE SET NULL,
    INDEX idx_event_city_date (city, start_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
COMMENT='Fan conventions, meetups, and screenings shown on the map/calendar, filterable by city.';


-- ---------------------------------------------------------------------
-- 10. bookmarks
-- WHY THIS TABLE EXISTS:
-- SRS: "Bookmark any article, character profile, video, or merchandise
-- item." Rather than 4 near-identical tables (bookmark_content,
-- bookmark_character, bookmark_merch...), this is ONE polymorphic
-- table — bookmarkable_type tells you which table bookmarkable_id
-- points to. Standard Laravel "morph" pattern.
-- ---------------------------------------------------------------------
CREATE TABLE bookmarks (
    id                  BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id             BIGINT UNSIGNED NOT NULL,
    bookmarkable_type   VARCHAR(100) NOT NULL COMMENT 'Which model is bookmarked, e.g. "Content", "CharacterProfile", "MerchandiseItem"',
    bookmarkable_id     BIGINT UNSIGNED NOT NULL COMMENT 'The row ID within that model''s table',
    note                VARCHAR(500) NULL COMMENT 'Optional personal note the user attaches to the bookmark',
    created_at          TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_bookmark_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY uq_bookmark_unique (user_id, bookmarkable_type, bookmarkable_id),
    INDEX idx_bookmark_target (bookmarkable_type, bookmarkable_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
COMMENT='Polymorphic bookmarks — one table covers bookmarking content, character profiles, and merch items.';


-- ---------------------------------------------------------------------
-- 11. ratings
-- WHY THIS TABLE EXISTS:
-- SRS: "Supports User feedback/rating on media (5-star or thumbs-
-- up/down system)." Same polymorphic pattern as bookmarks — keeps one
-- rating table instead of duplicating per content type.
-- ---------------------------------------------------------------------
CREATE TABLE ratings (
    id             BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id        BIGINT UNSIGNED NOT NULL,
    rateable_type  VARCHAR(100) NOT NULL COMMENT 'Which model is being rated, e.g. "Content"',
    rateable_id    BIGINT UNSIGNED NOT NULL,
    rating_type    ENUM('star','thumbs') NOT NULL DEFAULT 'star' COMMENT 'Which of the two supported rating systems was used',
    stars          TINYINT UNSIGNED NULL COMMENT '1-5, populated only when rating_type = star',
    is_thumbs_up   TINYINT(1) NULL COMMENT '1 = thumbs up, 0 = thumbs down, populated only when rating_type = thumbs',
    created_at     TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at     TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_rating_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY uq_rating_unique (user_id, rateable_type, rateable_id),
    INDEX idx_rating_target (rateable_type, rateable_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
COMMENT='Polymorphic 5-star or thumbs up/down ratings on media/content.';


-- ---------------------------------------------------------------------
-- 12. chatbot_faqs
-- WHY THIS TABLE EXISTS:
-- SRS Admin Control Panel: "Optional Chatbot FAQ entries and knowledge
-- base." This is what the optional AI chatbot draws its canned answers
-- from, and what admins manage through the panel.
-- ---------------------------------------------------------------------
CREATE TABLE chatbot_faqs (
    id            BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category_id   BIGINT UNSIGNED NULL COMMENT 'Optional: scope an FAQ to one fandom',
    question      VARCHAR(500) NOT NULL,
    answer        TEXT NOT NULL,
    created_by    BIGINT UNSIGNED NULL COMMENT 'Admin who authored this FAQ entry',
    created_at    TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at    TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_faq_category FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
    CONSTRAINT fk_faq_created_by FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
COMMENT='Admin-managed knowledge base the optional chatbot answers frequently asked questions from.';


-- ---------------------------------------------------------------------
-- 13. chatbot_queries
-- WHY THIS TABLE EXISTS:
-- SRS: chatbot "Includes chat history stored for context continuity
-- and progress tracking." Logs every question asked and the answer
-- given, separate from chatbot_faqs (which is the source material,
-- not the log).
-- ---------------------------------------------------------------------
CREATE TABLE chatbot_queries (
    id            BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id       BIGINT UNSIGNED NULL COMMENT 'NULL allowed — visitors (not just registered users) can use the chatbot',
    session_id    VARCHAR(100) NULL COMMENT 'Groups messages into one conversation for guest users without an account',
    message       TEXT NOT NULL,
    response      TEXT NULL,
    created_at    TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_chatbot_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_chatbot_session (session_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
COMMENT='Log of every chatbot interaction, used for context continuity within a session.';


-- ---------------------------------------------------------------------
-- 14. feedback
-- WHY THIS TABLE EXISTS:
-- SRS: "Dynamic feedback form with type categorization (bug,
-- suggestion, or query)" plus admin's "User feedback and fan-submitted
-- content" moderation view. This is the general site-feedback channel,
-- distinct from ratings (which are attached to a specific item).
-- ---------------------------------------------------------------------
CREATE TABLE feedback (
    id            BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id       BIGINT UNSIGNED NULL COMMENT 'NULL allowed if guests can submit feedback',
    type          ENUM('bug','suggestion','query') NOT NULL DEFAULT 'query',
    message       TEXT NOT NULL,
    status        ENUM('open','in_review','resolved','closed') NOT NULL DEFAULT 'open',
    created_at    TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at    TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_feedback_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_feedback_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
COMMENT='General site feedback (bugs/suggestions/queries), tracked through an admin resolution workflow.';

SET FOREIGN_KEY_CHECKS = 1;

-- =====================================================================
-- END OF SCHEMA
-- Tables intentionally NOT created (already provided by Breeze):
--   users, roles, role_user, password_reset_tokens, sessions,
--   cache, jobs, personal_access_tokens
-- =====================================================================
