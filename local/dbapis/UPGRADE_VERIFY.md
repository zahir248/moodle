# local_dbapis upgrade and verification

## 1. Run the upgrade

### Option A: Via the web (recommended)

1. Log in to your Moodle site as an administrator.
2. Go to **Site administration** → **Notifications**.
3. If an upgrade is required, Moodle will show the upgrade screen. Click **Upgrade Moodle database now**.
4. Wait for the upgrade to finish. You should see a success message.

### Option B: Via the command line

From your Moodle root directory (e.g. `c:\laragon\www\moodle`):

```bash
php admin/cli/upgrade.php --non-interactive
```

If you are prompted, confirm the upgrade.

---

## 2. Verify the new table exists

In your database client (phpMyAdmin, MySQL Workbench, etc.):

- **MySQL / MariaDB:** Check that the table `mdl_local_dbapis_history` exists (table prefix may be different if you changed it in config.php).

Example (MySQL):

```sql
SHOW TABLES LIKE '%local_dbapis_history%';
```

Or list columns:

```sql
DESCRIBE mdl_local_dbapis_history;
```

You should see columns: `id`, `messageid`, `message`, `userid`, `timecreated`.

---

## 3. Verify data was copied

Compare row counts and a sample of data.

Row count (both tables should have the same number of rows if you had data before the upgrade):

```sql
SELECT COUNT(*) FROM mdl_local_dbapis;
SELECT COUNT(*) FROM mdl_local_dbapis_history;
```

Sample data in history (each row should match a row from `local_dbapis` with `messageid` = original `id`):

```sql
SELECT * FROM mdl_local_dbapis_history ORDER BY id LIMIT 10;
```

If you had records in `local_dbapis` before the upgrade, the same number of rows should appear in `local_dbapis_history`, with `messageid` equal to the original `id` from `local_dbapis`.
