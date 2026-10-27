---
name: Kristohanon
description: A contemporary bilingual Catholic prayer book for Scripture, reflection, and daily devotion.
colors:
  ink: "#272321"
  ink-soft: "#6c625d"
  burgundy: "#a84643"
  burgundy-deep: "#823431"
  rose-wash: "#f3e5e1"
  parchment: "#eee9de"
  paper: "#fffdfa"
  surface-muted: "#f7f1ea"
  success: "#435837"
  danger: "#8b3d35"
typography:
  display:
    fontFamily: "Gelasio, Georgia, serif"
    fontSize: "clamp(2.35rem, 6vw, 4.2rem)"
    fontWeight: 400
    lineHeight: 1.06
    letterSpacing: "-0.035em"
  headline:
    fontFamily: "Gelasio, Georgia, serif"
    fontSize: "clamp(1.65rem, 4vw, 2.35rem)"
    fontWeight: 400
    lineHeight: 1.35
  body:
    fontFamily: "Source Sans 3, system-ui, sans-serif"
    fontSize: "1rem"
    fontWeight: 400
    lineHeight: 1.5
  reading:
    fontFamily: "Gelasio, Georgia, serif"
    fontSize: "clamp(1.05rem, 2.1vw, 1.25rem)"
    fontWeight: 400
    lineHeight: 1.85
  label:
    fontFamily: "Source Sans 3, system-ui, sans-serif"
    fontSize: "0.74rem"
    fontWeight: 600
    lineHeight: 1.5
    letterSpacing: "0.14em"
rounded:
  compact: "4px"
  control: "9px"
  card: "12px"
  feature: "18px"
  pill: "999px"
spacing:
  xs: "8px"
  sm: "16px"
  md: "24px"
  lg: "32px"
  xl: "40px"
  2xl: "48px"
  3xl: "64px"
components:
  button-primary:
    backgroundColor: "{colors.burgundy}"
    textColor: "{colors.paper}"
    rounded: "{rounded.pill}"
    padding: "11px 16px"
    height: "44px"
  button-primary-hover:
    backgroundColor: "{colors.burgundy-deep}"
    textColor: "{colors.paper}"
    rounded: "{rounded.pill}"
  button-secondary:
    backgroundColor: "transparent"
    textColor: "{colors.ink-soft}"
    rounded: "{rounded.pill}"
    padding: "11px 16px"
    height: "44px"
  card:
    backgroundColor: "{colors.paper}"
    textColor: "{colors.ink}"
    rounded: "{rounded.control}"
    padding: "24px"
  input:
    backgroundColor: "{colors.surface-muted}"
    textColor: "{colors.ink}"
    rounded: "{rounded.control}"
    padding: "12px 14px"
---

# Design System: Kristohanon

## Overview

**Creative North Star: "The Contemporary Prayer Book"**

Kristohanon should feel like opening a well-made prayer book in a quiet room: composed, humane, readable, and reverent without appearing antique. The system borrows the editorial cadence of Scripture and Daily Office pages—serif reading text, measured margins, numbered journeys, thin rules, and restrained rubric color—then adapts it to touch screens and short devotional sessions.

This is an **Operate + Read** product. Visitors must be able to write, choose, pray, and continue without learning a novel interface, while Scripture and prayer remain comfortable for sustained reading. Brand expression comes from typography, devotional imagery, burgundy rubric accents, and spatial rhythm—not from decorating every section or wrapping every idea in a card.

The atmosphere is warm and contemplative, never sleepy or vague. It should feel intentionally Catholic and Filipino in content while remaining welcoming to other Christians. The interface must avoid both app-dashboard sterility and ornate church-themed pastiche.

The official Kristohanon mark is a cross rising from two abstract open hands that also suggest an open book, framed by seven restrained rays. Use the burgundy master mark on light surfaces and the warm-ivory inverse inside burgundy controls. Keep its background transparent and never place it inside an additional badge except when the containing control itself requires one, such as the chat launcher.

**Key Characteristics:**

- Reading-first editorial hierarchy.
- Warm paper surfaces with one disciplined burgundy accent.
- Large, quiet feature rails balanced by compact controls.
- Contemporary devotional imagery with ample negative space.
- Gentle, purposeful state changes rather than ambient spectacle.
- Full-width composition on desktop and calm single-column reading on mobile.

## Colors

The palette behaves like a modern prayer book: dark ink on warm paper, with burgundy used as a rubric and interaction signal. Muted rose identifies selection; green and red are reserved for semantic states.

### Primary

- **Rubric Burgundy:** the sole branded interaction accent. Use for primary actions, active references, short kickers, selected progress, and important links.
- **Deep Burgundy:** hover and strong active treatment; do not introduce a second competing action color.

### Neutral

- **Prayer Ink:** primary reading and heading color; warmer and softer than pure black.
- **Quiet Ink:** supporting copy, metadata, and secondary controls. It must still meet WCAG AA against its surface.
- **Living Paper:** principal card and reading surface.
- **Parchment Ground:** page background and quiet separation between sections.
- **Muted Surface:** fields and subtle nested regions, never a substitute for hierarchy.

### Semantic

- **Steady Green:** confirmed or correct states only.
- **Pastoral Red:** errors, danger, and urgent boundaries only; never decorative.

**The Rubric Rule.** Burgundy should occupy a small minority of any screen. Its rarity gives it authority.

**The No Pure Extremes Rule.** Do not introduce pure black or pure white; the warm ink-and-paper relationship is a defining part of the product.

**The State Ownership Rule.** Green and danger red belong to meaning, never brand decoration.

## Typography

**Display Font:** Gelasio, with Georgia and serif fallbacks.  
**Body Font:** Source Sans 3, with system sans-serif fallback.

**Character:** Gelasio gives Scripture, prayer, and headings literary gravity without the theatricality of a display face. Source Sans 3 keeps navigation, labels, forms, metadata, and guidance direct and contemporary.

### Hierarchy

- **Display** (400, fluid 2.35rem–4.2rem, 1.06): one principal statement per page or feature.
- **Section headline** (400, fluid 1.65rem–2.35rem, approximately 1.35): major devotional sections and page titles.
- **Card title** (400, fluid 1.3rem–1.72rem, 1.35): titled practices and learning modules.
- **Reading text** (400, fluid 1.05rem–1.25rem, 1.7–1.85): Scripture and full prayer text, ideally 45–75 characters per line.
- **Body** (400–500, 1rem, 1.5–1.65): instructions and explanatory copy.
- **Rubric label** (600, approximately 0.7rem–0.75rem, wide tracked uppercase): category, duration, liturgical context, and stage labels. Keep labels short.

**The Two-Voice Rule.** Gelasio carries sacred reading and editorial hierarchy; Source Sans 3 carries action and explanation. Do not add another font without replacing one of these roles.

**The Reading Measure Rule.** Long Scripture, prayer, and explanatory text stays within a comfortable 45–75-character measure even when the surrounding layout is full width.

**The Rubric Is Not a Heading Rule.** Uppercase tracked labels orient the reader; they never replace a clear sentence-case heading.

## Layout

The outer canvas is fluid up to 1440px with 24px desktop gutters, reducing to 16px and then 10px on narrower screens. Primary feature rails stop around 1240px so large displays feel generous without stretching reading content beyond comprehension.

The homepage follows a deliberate sequence: welcome, journey index, private conversation, weekly Scripture, daily practice, expressed need, discovery, personal rhythm, and seasonal devotion. Sections use whitespace and fine rules before reaching for containers. Three-column practice and discovery grids collapse to one column below 900px; the seven-need grid becomes four columns and then two. The journey index becomes a two-by-two map on phones.

Use the eight-point spacing scale as the default rhythm. Section gaps are deliberately larger than card gaps: 52–72px between devotional chapters, 16px between related cards, and 24–34px inside substantive cards. Alignment should be shared across adjacent content rather than centered by habit.

Mobile is a re-composition, not a shrink. Hero copy moves toward the lower calm portion of the image, practice cards become full-width reading surfaces, weekly dates remain horizontally available, and actions retain 44px targets. No page may require horizontal page scrolling at 320px.

**The Rail Before Card Rule.** First group content with alignment, whitespace, and rules. Add a card only when a bounded surface improves interaction, persistence, or reading focus.

**The Full Width, Narrow Measure Rule.** Let sections occupy the canvas while keeping paragraphs and Scripture at readable line lengths.

**The One Chapter at a Time Rule.** Each viewport should communicate one dominant devotional purpose; avoid competing section headings or equal-weight calls to action.

## Elevation & Depth

The system is flat by default and uses tonal layering, fine borders, and imagery for structure. Shadows are ambient, warm, and low contrast. They identify an interactive or feature surface; they must not make every card float independently.

### Shadow Vocabulary

- **Quiet surface:** `0 10px 32px rgba(70, 51, 44, 0.045)` for ordinary interactive cards.
- **Companion surface:** `0 18px 60px rgba(70, 51, 44, 0.09)` for the primary conversation container.
- **Feature image:** `0 22px 70px rgba(70, 51, 44, 0.12)` for the homepage hero only.
- **Overlay:** `0 24px 70px rgba(56, 37, 33, 0.24)` for the floating chat panel and modal-level surfaces.

**The Flat-by-Default Rule.** If a border, surface tone, or spacing boundary is sufficient, do not add a shadow.

**The Warm Shadow Rule.** Shadows inherit warm brown-black color; avoid cold gray or black drop shadows.

## Shapes

Kristohanon uses gently curved paper-like rectangles, not bubbly software containers. Ordinary cards and fields use 8–12px radii; major photographic features may reach 16–18px. Pills are reserved for compact actions, language states, filters, and small metadata—not for entire content panels.

Borders are one pixel and low contrast. Circular geometry is meaningful for Rosary beads, day rhythm, arrow controls, and compact close controls. Asymmetric message bubbles are permitted only inside conversation.

**The Radius Hierarchy Rule.** Controls are smaller than cards; cards are smaller than major features. Do not use the same oversized radius everywhere.

**The Sacred Geometry Rule.** Circles represent beads, progress, days, or compact actions. They are not generic decoration.

## Components

### Buttons

Primary buttons use filled Rubric Burgundy, Living Paper text, a 44px minimum height, compact horizontal padding, and a pill silhouette. Hover deepens the burgundy; active state compresses subtly; loading lowers opacity without changing layout. Focus uses the shared high-contrast double ring.

Secondary actions use a fine border or text treatment. Do not create a second filled action color. Icon-only buttons require accessible names and a minimum 44×44px target.

### Language controls and chips

English/Cebuano controls behave as a segmented choice. The active language receives Muted Rose and deep burgundy text; the inactive choice remains quiet but readable. Category chips share the pill silhouette but never compete with the main action.

### Cards and containers

Cards use Living Paper or a slightly translucent paper surface, a one-pixel warm border, restrained 9–12px corners, and optional quiet shadow. Internal padding follows the 24–34px range. Avoid equal-height cards when content length differs unless the row requires deliberate comparison.

Image-led cards place photography or watercolor art in a clearly cropped region with useful alt text. Text must not sit directly on detailed imagery unless a tested opaque gradient preserves contrast.

### Inputs and fields

Inputs use Muted Surface, warm borders, 8–24px corners according to context, and visible labels. Focus changes border color, adds a restrained burgundy halo, and may elevate the enclosing companion surface. Placeholder text is an example, never the only label.

The main conversation composer remains compact before use and expands only when dialogue exists. Privacy and AI-transmission language must remain accurate and visible without overwhelming the primary task.

### Navigation

Desktop navigation is quiet text with a short burgundy rubric underline for the active page. Language choices use the same editorial underline with stronger type rather than a filled pill. The brand uses Gelasio and a small devotional mark. On mobile, navigation wraps beneath the centered brand rather than collapsing into an unnecessary hamburger for four items.

The homepage journey index is a separate editorial table of contents, using numbers, thin rules, and direct anchors. It must not look like another row of primary navigation pills.

### Weekly Scripture

The seven-day strip is a tablist and compact calendar. Today is explicit in text, not color alone. The selected day uses Muted Rose; Scripture appears below on an open reading rail rather than inside a nested card. Arrow buttons support sequential browsing, and touch dragging must preserve vertical page scroll.

### Scripture companion

The companion is the signature interactive component: bilingual switch, readable conversation history, verified Scripture panel, and compact composer. The floating Rosary launcher is a secondary entry point, not a competing mascot. Motion is subtle and must stop for reduced-motion users and while the panel is open.

On a normal homepage arrival at the document top, bring the companion into the first viewport and focus its message field so the primary action is unmistakable. Never override a section hash or a browser-restored scroll position.

### Rosary guide

Progress is expressed through mystery context, a thin progress track, current bead, and explicit step count. Beads are functional state, not decoration. The current mystery for the day is visible but visitors may choose another set.

## Do's and Don'ts

### Do:

- **Do** preserve Scripture, prayer, and the visitor's concern as the strongest content on the screen.
- **Do** use editorial rails, dividers, and whitespace to reduce unnecessary containers.
- **Do** keep burgundy scarce and meaningful as the system's rubric and action color.
- **Do** verify English and Cebuano at mobile widths with long content and 200% text zoom.
- **Do** use specific devotional imagery with quiet crops and negative space.
- **Do** provide useful fallback content when remote data or AI is unavailable.
- **Do** keep interactions reversible, private by default, and honest about data handling.

### Don't:

- **Don't** turn the homepage into a grid of generic equal-weight feature cards.
- **Don't** use gradients, floating blobs, glass effects, or sacred symbols as arbitrary decoration.
- **Don't** add oversized radii, excessive pills, thick side-accent borders, or cold SaaS shadows.
- **Don't** use archaic language when a licensed contemporary translation is available.
- **Don't** imply that the chatbot is Jesus, divine revelation, clergy, therapy, or emergency care.
- **Don't** introduce streak pressure, public prayer history, competitive scores, or guilt-based retention.
- **Don't** animate layout properties, autoplay audio, or allow decorative motion to delay reading and action.
- **Don't** hide source, translation, license, privacy, safety, or AI-boundary information.
