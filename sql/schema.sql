-- /sql/schema.sql

PRAGMA foreign_keys = ON;

CREATE TABLE IF NOT EXISTS users (
                                     id INTEGER PRIMARY KEY AUTOINCREMENT,
                                     username TEXT UNIQUE NOT NULL,
                                     password_hash TEXT NOT NULL,
                                     role TEXT NOT NULL CHECK(role IN ('admin','moderator','viewer')),
    active INTEGER NOT NULL DEFAULT 1,
    created_at TEXT NOT NULL DEFAULT (datetime('now'))
    );

CREATE TABLE IF NOT EXISTS quip_moderation (
                                               quip_id TEXT PRIMARY KEY,
                                               status TEXT NOT NULL CHECK(status IN ('pending','approved','rejected')) DEFAULT 'pending',
    reason TEXT,
    moderated_by INTEGER,
    moderated_at TEXT,
    FOREIGN KEY (moderated_by) REFERENCES users(id)
    );

CREATE TABLE IF NOT EXISTS audit_log (
                                         id INTEGER PRIMARY KEY AUTOINCREMENT,
                                         user_id INTEGER,
                                         action TEXT NOT NULL,
                                         target_type TEXT,
                                         target_id TEXT,
                                         meta_json TEXT,
                                         created_at TEXT NOT NULL DEFAULT (datetime('now')),
    FOREIGN KEY (user_id) REFERENCES users(id)
    );
