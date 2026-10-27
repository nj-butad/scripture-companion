# Scripture Companion

A calm, bilingual Scripture companion built with plain PHP, HTML, CSS, and
vanilla JavaScript. It retrieves reviewed Bible passages before asking AI to
compose a short reflection, so Scripture quotations are never generated from
model memory.

## Requirements

- PHP 8.1+
- PHP cURL extension
- Apache with `mod_rewrite` (production)

## Local development

```bash
cp .env.example .env
php -S localhost:8080
```

Open <http://localhost:8080>.

The app works without an OpenAI key using reviewed fallback responses. To
enable conversational reflections, set `OPENAI_API_KEY` and `OPENAI_MODEL` in
`.env`. Never commit `.env`.

## Deployment

The repository is laid out for direct deployment to Hostinger's `public_html`.
Connect the GitHub repository, select the `main` branch, and enable automatic
deployment. The root `.htaccess` prevents public access to application code,
configuration, Git metadata, and reviewed Scripture data.

The day-to-day release workflow is:

```bash
git add -A
git commit -m "Describe the change"
git push origin main
```

Hostinger watches `main` and deploys each successful push automatically.

## Scripture licenses

- Douay-Rheims 1899: public domain. Source: <https://ebible.org/details.php?id=engDRA>
- Biblica Open Ang Pulong sa Dios 2024: CC BY-SA 4.0. Source and required
  notice: <https://ebible.org/cebocb/copyright.htm>

The Cebuano text must retain its attribution and share-alike notice. The app
displays the translation beside every Cebuano passage and links to the complete
notice in the About panel.

## Safety

This project offers Scripture-centered encouragement. It is not a replacement
for emergency services, professional mental-health care, medical care, or
pastoral counseling. Messages suggesting immediate danger are routed to a
deterministic safety response instead of the language model.

