# Kristohanon deployment

Kristohanon uses `main` as the production branch and `staging` as the integration branch. Feature work should be opened as a pull request into `staging`. After the Quality workflow passes and the change has been reviewed, merge `staging` into `main` to deploy production.

## One-time Hostinger setup

1. Point `kristohanon.com` and `www.kristohanon.com` to the Hostinger site and enable HTTPS.
2. Connect this repository to Hostinger and deploy only the `main` branch to `public_html`.
3. Copy `.env.example` to `.env` outside Git and set production values.
4. Set a strong `OPENAI_API_KEY` and a supported `OPENAI_MODEL` only if AI replies are enabled.
5. Set the Hostinger mailbox password in `SMTP_PASSWORD`.
6. Run `composer install --no-dev --classmap-authoritative` in the deployed directory once and after `composer.lock` changes.
7. Confirm that `storage/` is writable by PHP and blocked from direct web access.
8. Test contact email, newsletter signup, chat fallback, clean URLs, and the PWA icon.

Never put API keys or mailbox passwords in GitHub, source files, issue reports, or screenshots.

## GitHub quality gate

In GitHub, open **Settings → Rules → Rulesets** and create a branch ruleset for `main`:

- Require a pull request before merging.
- Require the `PHP quality` and `Browser, accessibility and SEO` status checks.
- Block force pushes and branch deletion.
- Require the branch to be up to date before merging.

Apply the same rules to `staging`, but allow maintainers to merge feature pull requests after checks pass.

## Release flow

```bash
git switch staging
git pull --ff-only
git switch -c codex/short-feature-name
# make and verify changes
git push -u origin codex/short-feature-name
```

Open a pull request to `staging`. For a production release, open a second pull request from `staging` to `main`. Hostinger should deploy only after the production pull request is merged.

## Personalized Scripture feed and reminder jobs

The feed uses the reviewed bilingual passage library in `data/passages.php`. It chooses a daily featured passage using the account language and selected topic groups, then ranks the remaining library using saved or engaged topics. The current base library contains 49 selected readings in each language. Reviewed devotional cards in `data/monthly-readings.php` add a reflection, a small practice, and a short prayer to an existing verified passage. The publisher validates the passage ID and language before importing a card; it never generates, rewrites, or fetches Bible text.

### Monthly feed content publishing

1. Prepare a new monthly batch in `data/monthly-readings.php` with a distinct English and Cebuano entry for each scheduled post. Use passage IDs from `data/passages.php`, retain the translation label and source attribution, and review the Cebuano text with a fluent speaker before publishing.
2. Give each pair a stable ID, batch name, topic, and `publish_on` date. New reflections, practices, and prayers are editorial content; do not present them as Scripture or official Church teaching.
3. Merge the reviewed batch to `main`. Hostinger deploys it with the site.
4. Configure a daily Hostinger PHP CLI cron job so scheduled entries appear on their publication dates. The script is idempotent and safe to run more than once:

```text
/usr/bin/php /home/ACCOUNT/domains/kristohanon.com/public_html/scripts/publish-scheduled-readings.php
```

Run it daily (for example, 8:10 AM Asia/Manila). It creates the `feed_readings` table if needed, syncs approved entries from the deployed monthly calendar, and publishes items due in Manila time. Each user sees the entries matching their selected language, with the existing topic and interaction ranking applied. New monthly batches still need to be prepared and reviewed; the schedule automates their release, not authorship.

The account Preferences page defaults email reminders to **Off**. A user must opt in to daily or weekly email. To deliver reminders on Hostinger, add these PHP CLI cron jobs after deploying:

1. Run `scripts/send-reading-reminders.php --notifications-only` once each morning (for example, 8:00 AM Asia/Manila). It creates that day's in-app notification for each verified user without queueing email.
2. If you want email reminders active, run `scripts/send-reading-reminders.php` once each morning. It queues daily users every day and weekly users on Monday, and honors each account's Off/Daily/Weekly preference.
3. When email reminders are enabled, run `scripts/process-email-queue.php` every 15 minutes. It sends queued messages over the configured SMTP connection and retries delivery failures up to three times.

Use Hostinger's **PHP CLI** cron mode and the absolute path to the deployed site, for example:

```text
/usr/bin/php /home/ACCOUNT/domains/kristohanon.com/public_html/scripts/send-reading-reminders.php
/usr/bin/php /home/ACCOUNT/domains/kristohanon.com/public_html/scripts/process-email-queue.php
```

Replace `ACCOUNT` with the actual Hostinger account directory. The jobs only run from CLI, use the app's existing MySQL schema (`notifications` and `email_queue`), and do not call OpenAI. Do not enable the reminder jobs until Hostinger SMTP and database access are working.

## Verification

```bash
composer validate --strict
find . -path ./vendor -prune -o -name '*.php' -print0 | xargs -0 -n1 php -l
vendor/bin/phpstan analyse
npm ci
npx playwright install chromium webkit
npm run test:e2e
```
