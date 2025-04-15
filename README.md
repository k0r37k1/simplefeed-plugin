# SimpleFeed – Lightweight Article/Update Plugin for WonderCMS

**SimpleFeed** is a minimalistic plugin for [WonderCMS](https://www.wondercms.com/) that enables you to create and manage lightweight blog-style posts, news updates, or articles – all without a database.

---

## 📦 Features

- Flat-file storage (JSON per post)
- Slug-based URLs (e.g., `/feed/your-title`)
- Admin panel for adding/editing/deleting posts
- Short preview text with "read more" link
- Sorting by date (descending)
- Optional tags and filtering (`?tag=release`)
- Clean layout styled for Sky theme (adjustable)
- Frontend block integration via `<?php $wCMS->block('simplefeed'); ?>`

---

## 🔧 Installation

1. Download and unzip the plugin:
   - [Download SimpleFeed ZIP](sandbox:/mnt/data/simplefeed-plugin-updated.zip)

2. Place the entire `simplefeed` folder into your WonderCMS `plugins/` directory.

3. The plugin loads automatically when placed correctly.

---

## 🚀 Usage

### Admin Panel
- Navigate to `?page=simplefeed` (or click "SimpleFeed" in the WonderCMS admin menu).
- Click **“➕ New Post”** to create a new article.
- Fill in the form:
  - **Title** – required
  - **Date** – optional (default = today)
  - **Short text** – optional (auto-generated if empty)
  - **Content** – full article (HTML allowed)
  - **Image URL** – optional
  - **Tags** – comma-separated
  - **Author** – optional

### Display in Your Theme
To show a list of posts (previews), add the following line to your theme (e.g., in `theme.php`):

```php
<?php $wCMS->block('simplefeed'); ?>
```

### Read More / Single View
Each post has its own URL:
```
yourdomain.com/feed/your-slug
```

### Filter by Tags
You can filter posts using a tag:
```
yourdomain.com/?tag=release
```

---

## 📁 File Structure

```
simplefeed/
├── simplefeed.php          # Main plugin loader
├── admin.php               # Admin UI
├── actions/                # save.php, delete.php
├── data/                   # Flat-file storage (JSON)
├── templates/              # list.php, view.php, form.php
├── css/simplefeed.css      # Default styles (Sky theme friendly)
```

---

## 🛡️ Access Control
- The admin area is only accessible to logged-in users.
- Posts are always public unless you restrict them manually (advanced use).

---

## 🔧 Customization
- You can freely style the output by modifying `css/simplefeed.css`.
- Templates (list, view, form) can be adjusted inside `templates/`.

---

## ✅ Requirements
- WonderCMS 3.0 or newer
- PHP 7.2+ recommended

---

## 📖 License
MIT – Free to use, modify, and distribute.
