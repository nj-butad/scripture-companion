# Product

<!-- impeccable:product-schema 1 -->

## Platform

web

## Stack

- Plain PHP 8.1+, semantic HTML, shared CSS, and vanilla JavaScript.
- Apache deployment to Hostinger, with GitHub `main` as the automatic deployment source.
- No framework, database, account system, or build pipeline is required for the current product.
- OpenAI Responses API composes reflections only from locally retrieved, reviewed Scripture candidates. The model must not invent or quote Scripture from memory.

## Users

### Primary users

Filipino Catholics—especially English- and Cebuano-speaking people—who arrive carrying stress, fear, grief, loneliness, family strain, difficulty sleeping, study pressure, or a desire to return to prayer. They may not know what passage or prayer to search for and need a gentle place to begin.

### Secondary users

- Other Christians who want Scripture-centered encouragement without needing prior knowledge of Catholic devotional practice.
- Young Catholics who have missed traditional prayers and need them presented in approachable contemporary language.
- Catholics who want a lightweight daily rhythm: Scripture, reflection, prayer, Rosary, and the Church calendar in one place.

### Core job

Help a visitor move from an honestly expressed concern to a relevant, verified passage of Scripture and one manageable act of prayer—without pretending that AI is Jesus, clergy, therapy, or divine revelation.

## Product Purpose

Kristohanon exists to help people pause, name what they carry, encounter relevant Scripture, and return to God in prayer. It combines a private conversational starting point with Catholic devotional practices that can be used immediately in English or Cebuano.

Success means that a visitor can:

1. Express a concern naturally rather than searching by theological vocabulary.
2. Receive varied, situation-specific guidance grounded in verified Scripture.
3. Understand exactly which translation and reference were used.
4. Continue into a small prayer practice, Rosary, or traditional Catholic prayer.
5. Leave feeling accompanied and oriented toward God, Scripture, trusted people, and appropriate real-world help—not dependent on the chatbot.

## Positioning

**A private Catholic Scripture companion made for Filipino life: compassionate intention-to-Scripture guidance, English and Cebuano reading, and a practical daily prayer rhythm in one lightweight web experience.**

The defensible combination is not “an AI Bible chat.” It is the integration of:

- emotional-intent matching before model generation;
- reviewed English and Cebuano Scripture text;
- Catholic prayer practices and liturgical context;
- Filipino language and cultural relevance;
- no-account, low-friction web access suitable for modest hosting and bandwidth.

## Operating Context

### Main journey

1. The visitor sees a calm invitation rather than a productivity dashboard.
2. They choose English or Cebuano and type what feels heavy.
3. Local retrieval ranks reviewed passages against the visitor's intention.
4. The model selects one or two supplied passages and writes a concise reflection, prayer, and practical next step.
5. The interface renders verified Scripture separately with its reference, translation, and license attribution.
6. The visitor may continue the conversation or move into Lectio Divina, the Rosary, the prayer library, gratitude, or another daily practice.

### Returning journey

Returning visitors can resume a gentle rhythm stored only in their browser: recent prayer, daily prayer activity, Rosary progress, gratitude text, and quiz state. This is a private aid, never a public score or competitive streak.

### Pages

- `/` — daily Scripture rhythm, conversational companion, weekly verse slider, Lectio Divina, prayer prompts, Catholic calendar, learning cards, and seasonal devotion.
- `/rosary.php` — bead-by-bead Rosary in English and Cebuano, with mysteries selected for the current day in Asia/Manila and progress saved locally.
- `/prayers.php` — searchable bilingual library of traditional Catholic prayers grouped by purpose.

## Capabilities and Constraints

### Confirmed capabilities

- English and Cebuano conversational guidance.
- Reviewed local Scripture library with pastoral-intention matching and recent-passage avoidance.
- Verified English and Cebuano Scripture rendered independently of generated reflection text.
- Deterministic urgent-safety routing before any model call.
- Seven-day verse slider defaulting to today.
- Guided Lectio Divina, gratitude, daily Catholic question, liturgical calendar context, and private prayer rhythm.
- Complete Rosary flow with day-appropriate mysteries and bilingual prayers.
- Searchable bilingual Catholic prayer library.
- Responsive desktop and mobile web layouts.
- Useful fallback responses when OpenAI is unavailable or unconfigured.

### Technical constraints

- Preserve pure PHP and vanilla JavaScript unless the user explicitly authorizes a stack change.
- Remain deployable on ordinary Hostinger PHP hosting.
- Keep secrets in `.env`; never expose or commit the OpenAI API key.
- Prefer local deterministic retrieval and validation before paid model calls to control cost and accuracy.
- Do not require an account for core prayer and Scripture features.
- Personal notes and progress remain in local browser storage unless a future privacy-reviewed sync feature is explicitly approved.
- Preserve legacy internal identifiers such as `still-waters-*` browser-storage keys and `window.STILL_WATERS`; they are compatibility contracts, not public branding, and renaming them would discard returning visitors' local progress.
- Asia/Manila is the authoritative timezone for daily and day-of-week experiences.

### Content and licensing constraints

- English Scripture: World English Bible Updated, public domain.
- Cebuano Scripture: Biblica Open Ang Pulong sa Dios 2024, CC BY-SA 4.0; preserve its attribution, source link, and share-alike obligations.
- Catholic calendar data enhances local fallback content from `cpbjr/catholic-readings-api`; the page must never remain stuck on “Loading.”
- Do not copy proprietary liturgical, prayer, or Bible text without an applicable license or permission.
- AI output must never fabricate Scripture, Church teaching, saints, feast days, or source citations.

### Safety and trust constraints

- The companion must explicitly remain an AI tool and must not role-play Jesus, God, a saint, a prophet, clergy, a therapist, or a doctor.
- Never claim certainty about God's intentions for an individual.
- Never diagnose, shame, preach at, or manipulate a distressed visitor.
- Immediate-danger language must bypass generative guidance and show deterministic crisis-oriented next steps.
- Clearly distinguish Scripture quotation, generated reflection, prayer suggestion, and practical action.
- “Private by default” means the site does not persist the message; the interface must not imply that transmission to the configured AI provider does not occur.

## Brand Commitments

- Product name: **Kristohanon**.
- Category language: **Scripture companion**, not “AI Jesus.”
- Core invitation: people do not need perfect words before approaching Scripture and prayer.
- Voice: gentle, direct, attentive, Christ-centered, specific to the visitor's message, and free of stock consolation phrases.
- Spiritual posture: invitation rather than command; accompaniment rather than authority; prayerful hope without false certainty.
- Languages: English and Cebuano are first-class experiences, not an English product with a partial translation layer.
- Visual reference: the dignity and readability of an editorial prayer book and Daily Office, interpreted in a contemporary, mobile-friendly way.
- Independence: Kristohanon is its own product and identity, not a page within the Nielsys portfolio.

## Evidence on Hand

- A reviewed bilingual passage library with 98 English and Cebuano selections: `data/passages.php`.
- Deterministic Scripture ranking and safety services: `src/ScriptureRepository.php` and `src/SafetyService.php`.
- Model boundary and response contract: `src/OpenAIClient.php`.
- Traditional prayer and Rosary content: `data/prayers.php` and `data/rosary.php`.
- Working responsive product pages: `index.php`, `rosary.php`, and `prayers.php`.
- Original optimized devotional imagery in `assets/images/`.
- Existing editorial rules and QA checklist: `docs/design-system.md`.
- Live deployment: `https://scripture.nielsys.com/`.

No testimonials, ecclesiastical endorsement, clinical claims, usage metrics, or formal Cebuano pastoral review are currently documented. Future work must not fabricate them.

## Product Principles

1. **Verified Scripture before generated language.** Retrieve and render source text deterministically; use AI only for contextual reflection.
2. **Accompaniment without impersonation.** Sound compassionate and spiritually literate while remaining honest about what the tool is and is not.
3. **A small next step beats an endless feed.** Each visit should lead toward one passage, one prayer, or one concrete act rather than maximizing engagement time.
4. **Filipino language is product infrastructure.** Cebuano accuracy, readability, licensing, and cultural context receive the same care as English.
5. **Private, lightweight, and accessible by default.** Avoid accounts, public histories, guilt mechanics, and unnecessary bandwidth or model usage.

## Accessibility & Inclusion

- Target WCAG 2.2 AA for contrast, keyboard navigation, semantics, focus visibility, form labeling, and target size.
- Support 320px-wide screens, 200% zoom, touch interaction, and reduced-motion preferences.
- Use plain contemporary English and natural Cebuano; avoid archaic vocabulary when a licensed modern translation is available.
- Do not make spiritual literacy, disability, distress, or limited bandwidth prerequisites for using the product.
- Essential meaning may not depend only on color, imagery, animation, audio, or iconography.

## Open Decisions

- Whether new persuasive or showcase surfaces should default to comp-first or code-first design work; no project default has been recorded.
- Whether and how a qualified Catholic Cebuano reviewer will approve translations and devotional phrasing.
- Whether future daily Mass readings or Liturgy of the Hours content can be sourced under suitable permissions.
- Whether the product will remain entirely free or later accept donations; no pricing or monetization claim is approved.
- Whether a formal parish, diocese, or ministry partnership will be pursued; no endorsement currently exists.
