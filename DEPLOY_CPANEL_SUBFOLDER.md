# Deploy Moodle on cPanel (Subfolder)

This guide walks you through deploying your Moodle app on cPanel when the site runs in a **subfolder** (e.g. `https://yourdomain.com/moodle`).

---

## 1. Prepare on your local machine

- **Zip the complete Moodle folder** — every file and folder must be included. Only **exclude**:
  - `moodledata` (or your local data folder) — you’ll use a new data folder on the server
  - `config.php` — you’ll create a new one on the server with cPanel settings
- **Critical:** If any core folder is missing on the server (e.g. `repository/`, `theme/boost/`, `version.php`, `vendor/`), you will get “file does not exist” or “theme not available” errors. Always upload the **full** Moodle package, including the **entire `vendor/`** folder (and `vendor/composer/`).
- Optional: keep a copy of your current `config.php` as reference for DB prefix and other options.

---

## 2. cPanel: Create database and user

1. Log in to **cPanel**.
2. Open **MySQL® Databases** (or **MySQL Database Wizard**).
3. **Create a database**, e.g. `youruser_moodle`.
4. **Create a user**, e.g. `youruser_moodleuser`, with a strong password.
5. **Add the user to the database** with **ALL PRIVILEGES**.
6. Note:
   - Full DB name (often `youruser_moodle`)
   - DB user (often `youruser_moodleuser`)
   - DB password
   - DB host is usually `localhost` (cPanel will show it).

---

## 3. cPanel: Upload Moodle into a subfolder

1. Open **File Manager**.
2. Go to `public_html`.
3. Create a folder for Moodle, e.g. `moodle` (URL will be `https://yourdomain.com/moodle`).
4. Upload your Moodle zip into `public_html/moodle`.
5. **Extract** the zip **inside** `moodle` so that:
   - `public_html/moodle/index.php` exists
   - `public_html/moodle/config-dist.php` exists
   - `public_html/moodle/lib/` exists  
   If the zip had an extra “moodle” folder inside, move its contents up so the structure is as above.

---

## 4. cPanel: Create data directory (outside web root)

Moodle’s data must **not** be inside `public_html` for security.

1. In File Manager, go to your **home** directory (one level above `public_html`).
2. Create a folder, e.g. `moodledata`.
3. Full path will be something like: `/home/cpanelusername/moodledata`
4. Set permissions to **755** (or 0755). If the installer asks for 0777 temporarily, you can set it for install then change back to 755 after.

---

## 5. Create config.php for cPanel (subfolder)

1. In File Manager, go to `public_html/moodle`.
2. Copy `config-dist.php` to `config.php` (or create a new `config.php`).
3. Edit `config.php` and set at least:

```php
<?php
unset($CFG);
global $CFG;
$CFG = new stdClass();

// --- DATABASE (use your cPanel MySQL details) ---
$CFG->dbtype    = 'mysqli';
$CFG->dblibrary = 'native';
$CFG->dbhost    = 'localhost';           // usually localhost on cPanel
$CFG->dbname    = 'youruser_moodle';      // full database name from cPanel
$CFG->dbuser    = 'youruser_moodleuser';  // full database user from cPanel
$CFG->dbpass    = 'your_db_password';     // password you set
$CFG->prefix    = 'mdl_';
$CFG->dboptions = array(
  'dbpersist' => 0,
  'dbport' => '',
  'dbsocket' => '',
  'dbcollation' => 'utf8mb4_unicode_ci',
);

// --- SUBFOLDER: your domain + folder name (no trailing slash) ---
$CFG->wwwroot   = 'https://yourdomain.com/moodle';
$CFG->dataroot  = '/home/cpanelusername/moodledata';  // path from step 4
$CFG->admin     = 'admin';

$CFG->directorypermissions = 0777;

require_once(__DIR__ . '/lib/setup.php');
```

Replace:

- `youruser_moodle` / `youruser_moodleuser` / `your_db_password` with your cPanel MySQL details.
- `https://yourdomain.com/moodle` with your real URL (same protocol and domain as the site).
- `cpanelusername` with your cPanel username so `dataroot` is the path you created in step 4.

Use **forward slashes** for `dataroot` on Linux (e.g. `/home/username/moodledata`).

---

## 6. Run the Moodle installer

1. Visit `https://yourdomain.com/moodle` in your browser.
2. If you see the **installer**:
   - Choose language.
   - Confirm paths (especially `dataroot`). If it can’t write to `dataroot`, fix permissions (e.g. 755 or 0777 during install).
   - Complete the admin account and site details.
3. If you already have a database from a previous install (e.g. migrated), the site may load directly; then go to **Site administration** and run any upgrade steps it suggests.

---

## 7. After installation

- Set `moodledata` permissions to **755** (or 0755) if you had used 0777 during install.
- In Moodle: **Site administration → Notifications** and apply any pending upgrades.
- **Security:** Ensure `config.php` and `moodledata` are not publicly readable (they shouldn’t be if `moodledata` is outside `public_html` and permissions are correct).

---

## 8. Troubleshooting

| Issue | What to check |
|-------|----------------|
| Blank page / 500 | PHP version in cPanel (e.g. **PHP 8.0 or 8.1**), PHP error log in cPanel. |
| Class "IntlTimeZone" not found | PHP **intl** extension is disabled. In cPanel go to **Select PHP Version** (or **MultiPHP INI Editor**) and enable the **intl** extension. |
| “Invalid wwwroot” / 500 errors | `$CFG->wwwroot` must be exactly the URL you use (e.g. `https://yourdomain.com/moodle`) with **no trailing slash**. |
| “Dataroot not writable” | Path in `config.php` correct; folder exists; permissions 755 or 0777 for install. |
| Database connection failed | DB name, user, password, and host in `config.php`; user has ALL PRIVILEGES on that database. |
| CSS/JS broken in subfolder | `$CFG->wwwroot` must match the real URL (protocol + domain + subfolder). |
| “Failed to open version.php” / “No such file or directory” | The **root** `version.php` (and other root files) are missing on the server. Re-upload the full Moodle package or at least the root-level files: `version.php`, `index.php`, `config-dist.php`, etc. |
| “Attempt to require a JavaScript file that does not exist” (e.g. `/repository/filepicker.js`) | The `repository/` folder (or specific files) wasn’t uploaded. Re-upload the full Moodle package so `repository/filepicker.js` and all of `repository/` exist. |
| “Default theme boost not available or broken!” | The `theme/boost/` folder is missing or incomplete. Re-upload the full Moodle package so the entire `theme/boost/` directory is on the server. |
| “Failed to open … vendor/composer/autoload_real.php” | The `vendor/` folder is missing or incomplete. Upload the full `vendor/` folder (including `vendor/composer/`) from your local Moodle, or run `composer install --no-dev` in the Moodle directory on the server (if you have SSH and Composer). |
| “Call to undefined method core\\output\\core_renderer::firstview_fakeblocks()” | The Boost theme’s renderer isn’t loaded (often because `theme/boost/classes/output/core_renderer.php` is missing). Upload the full `theme/boost/` folder, or apply the defensive patch in `theme/boost/layout/drawers.php` (see deployment notes). |
| Class "core_question\\local\\bank\\question_bank_helper" not found (but file exists on server) | Moodle’s **component/classmap cache** is stale. Purge caches (see below) or delete `moodledata/cache/core_component.php` so Moodle rebuilds the classmap. |

### If a class is “not found” but the file exists: purge the component cache

Moodle caches the list of plugin classes in **`$CFG->cachedir`** (usually `moodledata/cache/`). If you added or fixed files after the cache was built, the class won’t be found until the cache is rebuilt.

**Option 1 – Via Moodle (if you can open the site)**  
**Site administration → Development → Purge all caches.**

**Option 2 – Via cPanel File Manager (when the site is broken)**  
1. Go to your **moodledata** folder (e.g. `/home/iesbcomm/moodledata` — **outside** `public_html`).  
2. Open the **cache** folder.  
3. Delete the file **`core_component.php`** (or delete everything inside `cache/` to purge all caches).  
4. Reload your Moodle site; the classmap will be rebuilt and the new classes will be found.

**Option 3 – PHP OPcache**  
If you use OPcache, restart PHP (e.g. from cPanel “Select PHP Version” / “MultiPHP INI Editor” or restart the web server) after purging the Moodle cache.

---

## Quick checklist

- [ ] MySQL database and user created in cPanel; user has all privileges.
- [ ] **Full** Moodle package uploaded (see “Verify on server” below).
- [ ] Moodle files in `public_html/moodle` (index.php at `public_html/moodle/index.php`).
- [ ] `moodledata` created **outside** `public_html` (e.g. `/home/username/moodledata`).
- [ ] `config.php` in `public_html/moodle` with correct `wwwroot`, `dataroot`, and DB settings.
- [ ] Visited `https://yourdomain.com/moodle` and completed installer or upgrade.

### Verify on server (must exist under `public_html/moodle/`)

If you get “file does not exist” or “theme not available”, check that these exist:

- [ ] `version.php` (root)
- [ ] `repository/filepicker.js`
- [ ] `theme/boost/` (entire folder: templates, scss, config, etc.)
- [ ] `vendor/composer/autoload_real.php` (and full `vendor/` folder — required for Composer autoload)
- [ ] `question/classes/local/bank/question_bank_helper.php` (and full `question/` plugin)
- [ ] `admin/`, `lib/`, `lang/`, `plugin/` (and other core dirs)
