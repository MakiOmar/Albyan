# Course Import (Queued) - Setup and Usage

This guide explains how to start and run the new queued course import.

## 1) One-time setup

1. Run migrations:

```bash
php artisan migrate
```

2. Set queue driver in `.env` (required for background import):

```env
QUEUE_CONNECTION=database
```

You can also use `redis` if your environment is configured for it.

3. If using `database` queue, create jobs table (only if it does not already exist):

```bash
php artisan queue:table
php artisan migrate
```

4. Start queue worker:

```bash
php artisan queue:work
```

Keep this command running while imports are processing.

## 2) How to start an import

1. Go to admin courses page:

- `admin/webinars?type=course`

2. Click **Import**.
3. (Optional) Click **Download Template** and fill it.
4. Upload `.xlsx`, `.xls`, or `.csv` file.
5. Submit import.
6. Open import details page to track:
   - total rows
   - processed rows
   - created count
   - updated count
   - failed count
   - row errors

## 3) Import matching behavior

For each row:

1. Try match by `id` (if provided).
2. If not found, try match by `slug`.
3. If still not found, create a new course.

## 4) Export/Import compatibility

Course export now uses the same columns as import template, so you can:

1. Export courses
2. Edit rows
3. Re-import file directly

## 5) Important notes

- If `QUEUE_CONNECTION=sync`, import will be blocked by guardrails.
- Large files are processed in chunks to reduce memory/time issues.
- Failed rows do not stop the entire import; check details page for errors.
- Keep required fields filled for creating new courses (e.g. `title`, `type`, `teacher_id`, `slug`, `thumbnail`, `image_cover`).

## 6) Troubleshooting

### Import remains `pending`
- Ensure queue worker is running: `php artisan queue:work`
- Check queue connection in `.env`

### Import fails immediately
- Check file type/size and required columns
- Check Laravel logs in `storage/logs/laravel.log`
- Check failed jobs:

```bash
php artisan queue:failed
```

### Retry failed jobs

```bash
php artisan queue:retry all
```

