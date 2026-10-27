# Still Waters Editorial Design System

## Context and goals

Still Waters should feel like a quiet, contemporary prayer book: reverent,
welcoming, readable, and distinctly calmer than a social feed. Scripture and
prayer remain primary; decoration supports orientation and contemplation.

## Design tokens and foundations

- Use the eight-point spacing rhythm defined by `--space-1` through
  `--space-8`.
- Use Gelasio for headings, Scripture, and prayer text; use Source Sans 3 for
  controls, labels, and supporting copy.
- Use `--ink` for primary text and `--ink-soft` only for supporting text.
- Burgundy is the single interaction accent. Green and red are reserved for
  success and error states.
- Reading text should remain between 45 and 75 characters per line where
  practical.

## Component-level rules

- Primary actions must be filled burgundy pills; secondary actions must use a
  border or text treatment. Both require hover, focus-visible, active,
  disabled, and loading states when applicable.
- All interactive controls must provide a minimum 44 by 44 CSS-pixel target.
- Content cards use a one-pixel border, nine-pixel radius, and restrained
  shadow. Do not add a card when a divider and whitespace can express the same
  grouping.
- The homepage journey navigation must link directly to Share, Scripture,
  Pray, and Discover and collapse to two columns on narrow screens.
- Loading enhancements must retain useful fallback content rather than an
  indefinite spinner or loading label.

## Accessibility requirements

- Every page must include a visible-on-focus skip link.
- Keyboard focus must be visible with the shared focus ring.
- Semantic HTML comes before ARIA; images require useful alt text unless they
  are purely decorative.
- The interface must remain usable at 200% zoom and at a 320px viewport.
- Reduced-motion preferences must suppress position-based decorative motion.
- Text and controls must meet WCAG 2.2 AA contrast requirements.

## Content and tone

Use brief, gentle, direct language. Never imply that the chatbot is Jesus,
divine revelation, professional care, or a substitute for human support.
Prefer invitations such as “Pause,” “Pray,” and “Choose” over commands or
achievement language.

## Prohibited patterns

- No autoplay audio, carousels, parallax, or decorative motion that delays use.
- No streak pressure, competitive scores, guilt-based retention, or public
  prayer histories.
- No low-contrast text over devotional artwork.
- No ambiguous icon-only controls without accessible names.
- No fixed-width content that creates horizontal scrolling on mobile.

## QA checklist

- Tab through every control and confirm focus order and visibility.
- Check the homepage at 320px, 390px, 768px, and desktop widths.
- Test English and Cebuano for long-label wrapping and content overflow.
- Verify all touch targets are at least 44px.
- Test reduced motion and 200% browser zoom.
- Confirm loading, offline fallback, success, and error states remain clear.
- Run PHP and JavaScript syntax checks and inspect the rendered accessibility
  tree before deployment.
