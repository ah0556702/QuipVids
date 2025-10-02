CREATE TABLE IF NOT EXISTS quip_submissions (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  name TEXT NOT NULL,
  title TEXT,
  script TEXT,
  image_path TEXT,
  video_path TEXT,
  status TEXT NOT NULL DEFAULT 'pending',
  submitted_at TEXT NOT NULL DEFAULT (datetime('now')),
  moderated_by INTEGER,
  moderated_at TEXT,
  FOREIGN KEY (moderated_by) REFERENCES users(id)
);
