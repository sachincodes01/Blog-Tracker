# Block Usage Tracker – v2 Updates

## Plugin Overview

Block Usage Tracker helps track where Lazy Blocks and media files are used across WordPress pages.

---

## v2 Features

### 1. Block Usage Tracking

* Scans all WordPress pages
* Detects all `lazyblock/*` blocks
* Stores block usage in custom database table
* Shows block name with usage count

Example:

```text
lazyblock/second-section (2)
lazyblock/heading (1)
```

---

### 2. Media Usage Tracking

Added a dedicated **Media** tab.

Tracks media/image usage inside page content.

Displays:

* media file name
* usage count
* page where used
* post status
* last updated date

Example:

```text
istockphoto-2175543017-2048x2048-1.jpg (1)
```

---

### 3. Block Usage Count

Added usage count beside block name instead of separate column.

Example:

```text
lazyblock/first-section (1)
```

This keeps the table cleaner and easier to read.

---

### 4. Edit Block Button

Added **Edit Block** action button for Lazy Blocks.

From the Blocks tab users can:

* click **Edit Block**
* open the corresponding Lazy Blocks editor directly

This makes editing blocks much faster without searching manually in the Lazy Blocks admin panel.

---

### 5. Rescan Blocks Button

Added manual **Rescan Blocks** button.

Allows re-scanning all pages after:

* creating a new block
* editing page content
* updating media

Updates tracking table instantly.

---

### 6. Admin UI Improvements

Improved admin screen layout:

* Blocks tab
* Media tab
* Rescan action button
* cleaner grouped table output
* grouped duplicate block entries
* grouped duplicate media entries

---

### 7. Grouped Query Results

Database results are grouped by `block_name`.

This avoids duplicate rows.

Instead of:

```text
lazyblock/second-section
lazyblock/second-section
```

Now shows:

```text
lazyblock/second-section (2)
```

---

## Tech Notes

### Main files updated

* `class-but-admin.php`
* `admin-page.php`

---

### WordPress Hooks Used

* `admin_menu`
* `admin_enqueue_scripts`
* `save_post`
* `wp_ajax_but_rescan`

---

## Future Improvements (Ideas)

Possible future features:

* filter by page/post type
* search by block name
* click media item to open media library
* export CSV report
* dashboard widget summary
* block usage stats by page
* delete unused media detection

---

## Version

Current version: **v2**
