# DESIGN SYSTEM — ICHA

## 1. Design Direction

ICHA uses a professional international-conference visual language:

- Academic
- Professional
- Modern
- Premium but restrained
- Trustworthy
- Clean
- Easy to navigate
- Responsive

The visual style should feel like an established academic conference website, not a startup dashboard.

### Design References

The navigation and overall information hierarchy should follow the style commonly used by established academic/international conference websites:

- clean white navigation
- strong typography
- clear hierarchy
- restrained use of gradients
- clear call-to-action
- conference information is prioritized over decorative effects

---

# 2. Design Principles

## 2.1 One Design System, Multiple Conferences

The application supports:

- ICHA 2026
- ICHA 2027
- ICHA 2028
- Future conferences

The UI structure remains consistent.

Only conference-specific content may change:

- title
- logo
- hero image
- theme
- dates
- venue
- speakers
- topics
- sponsors
- timeline
- registration information

Do NOT create a separate Blade template for each year.

---

## 2.2 Content First

The website is an academic conference website.

Prioritize:

1. Conference identity
2. Theme
3. Important dates
4. Registration
5. Speakers
6. Topics
7. Publication
8. Conference information

Avoid decorative elements that reduce readability.

---

# 3. Brand Identity

## Primary Colors

```text
Primary Purple: #4F46E5
Dark Purple:    #312E81
Navy:           #0F172A
Gold:           #FACC15
Dark Gold:      #EAB308
White:          #FFFFFF
Light Slate:    #F8FAFC
Slate:          #64748B
Border:         #E2E8F0
```

## Color Usage

### Purple

Use for:

- primary buttons
- active navigation
- links
- icons
- headings where appropriate
- important interactive states

### Gold / Yellow

Use as an accent for:

- Registration CTA
- conference highlights
- decorative accent
- countdown emphasis
- small badges
- important callouts

Gold should NOT be used for large amounts of body text.

### Navy

Use for:

- primary text
- hero overlay
- footer
- dark sections
- high-contrast areas

### White

Use for:

- navbar
- cards
- main content
- clean sections

---

# 4. Typography

Use a modern sans-serif font with strong readability.

Recommended:

- Inter
- Plus Jakarta Sans
- Poppins

Do not mix more than two font families.

## Typography Hierarchy

### Hero Title

Large and bold.

Example:

```text
ICHA 2026
International Conference
on Healthcare Administration
```

### Section Heading

Large, bold and concise.

Example:

```text
Important Dates
```

### Body

Comfortable line height and readable width.

Avoid overly dense paragraphs.

### Navigation

Medium/semi-bold.

Navigation should remain readable at all screen sizes.

---

# 5. Root Conference Portal

URL:

```text
/
```

The root page is a **Conference Portal**, not a hardcoded conference landing page.

## Structure

```text
Navbar
↓
Portal Hero
↓
Current / Featured Conference
↓
Upcoming Conferences
↓
Conference Editions
↓
Past Conferences
↓
About ICHA
↓
Footer
```

The root page may highlight the active conference.

Example:

```text
Current Conference

ICHA 2027
International Conference on Healthcare Administration

[View Conference]
```

---

# 6. Navbar

The navbar should follow a clean academic conference website style.

## Desktop Layout

```text
┌────────────────────────────────────────────────────────────────────────────┐
│ [ICHA LOGO]   Home   Conference ▾   Speakers   Timeline   Publication      │
│                                                    Sponsors  Login [Register]│
└────────────────────────────────────────────────────────────────────────────┘
```

## Navbar Appearance

- White background
- Sticky on scroll
- Full-width
- Centered content container
- Maximum width around `1280px`
- Subtle bottom border
- Very subtle shadow when sticky
- No heavy glassmorphism
- No large gradient
- No excessive animation

Example Tailwind direction:

```text
bg-white
border-b border-slate-200
```

When scrolled, a subtle shadow may be added.

---

## 6.1 Navbar Logo

Logo is displayed on the left.

Recommended:

```text
[ICHA Logo]
```

The logo should link to:

```text
/
```

For a conference-specific page, the system may use the conference logo if available.

Fallback:

```text
Global ICHA Logo
```

---

# 6.2 Navbar Menu

Recommended menu:

```text
Home
Conference ▾
Speakers
Timeline
Publication
Sponsors
Registration
```

Optional:

```text
Login
```

depending on authentication requirements.

---

# 6.3 Conference Dropdown

The Conference menu is dynamic.

Example:

```text
Conference
├── Current Conference
├── Upcoming Conference
└── Past Conferences
```

Or:

```text
Conference
├── ICHA 2027
├── ICHA 2026
└── Conference Archive
```

Do not hardcode conference years.

The dropdown must be generated from database data.

---

# 6.4 Active Navigation

Active menu:

- Purple text
- Optional small underline
- Medium/semi-bold weight

Example:

```text
Home
────
```

Avoid oversized active indicators.

---

# 6.5 Registration CTA

Registration is the primary navbar CTA.

Use:

- Gold background
- Dark text
- Rounded medium radius
- Clear hover state

Example:

```text
[ Registration ]
```

Recommended:

```text
bg-yellow-400
text-slate-950
```

Do not use excessive gradients.

---

# 6.6 Mobile Navbar

Mobile layout:

```text
┌─────────────────────────────────┐
│ [ICHA LOGO]                ☰    │
└─────────────────────────────────┘
```

Open menu:

```text
Home
Conference
  Current Conference
  Upcoming Conference
  Past Conferences
Speakers
Timeline
Publication
Sponsors
Registration
Login
```

Use a simple mobile drawer/dropdown.

Avoid complicated animation.

---

# 7. Conference Landing Page

URL:

```text
/conferences/{conference:slug}
```

The landing page is dynamic.

## Structure

```text
Navbar
↓
Hero
↓
Countdown
↓
About Conference
↓
Conference Theme
↓
Topics / Scope
↓
Important Dates
↓
Speakers
↓
Registration
↓
Publication
↓
Sponsors
↓
FAQ
↓
Contact
↓
Footer
```

---

# 8. Hero Section

The hero is the strongest visual section.

## Content

Use conference-specific:

- logo
- hero image
- title
- tagline
- theme
- event date
- venue
- city/country

Example:

```text
10th INTERNATIONAL CONFERENCE

ICHA 2026

International Conference on
Healthcare Administration

10–11 November 2026
UMSURA, Surabaya

[ Submit Abstract ] [ Learn More ]
```

All values must come from `$conference`.

---

# 8.1 Hero Background

Use the conference-specific hero image:

```text
$conference->hero_image
```

Image:

```text
object-cover
object-center
```

Add a dark overlay to guarantee text readability.

Recommended:

```text
absolute inset-0
bg-gradient-to-b
from-slate-950/50
via-slate-950/70
to-slate-950/90
```

Optional additional overlay:

```text
bg-indigo-950/20
mix-blend-multiply
```

Do not stack too many overlays.

---

# 8.2 Hero Text

Hero text should have strong contrast.

Primary title:

```text
text-white
```

Gold may be used for a small highlight:

```text
text-yellow-400
```

Do NOT use yellow for large paragraphs.

Recommended hierarchy:

```text
Small Label → Gold
Title → White
Tagline → White / Slate-100
Date → White
CTA → Gold + Purple
```

---

# 8.3 Hero CTA

Primary:

```text
Submit Abstract
```

Secondary:

```text
Learn More
```

Primary CTA:

```text
bg-indigo-600
text-white
```

Secondary CTA:

```text
bg-white/10
border border-white/30
text-white
```

Avoid overly bright gradients.

---

# 9. Countdown

Display:

```text
Days
Hours
Minutes
Seconds
```

Use compact cards.

Example:

```text
┌─────┐ ┌─────┐ ┌─────┐ ┌─────┐
│ 93  │ │ 14  │ │ 23  │ │ 29  │
│DAYS │ │HOURS│ │MINS │ │SECS │
└─────┘ └─────┘ └─────┘ └─────┘
```

The countdown must be calculated from:

```text
$conference->start_date
```

Do not hardcode the countdown.

---

# 10. About Section

Purpose:

Explain the conference clearly.

Layout:

```text
Text Content          Supporting Image
────────────────      ─────────────────
About Conference      Conference image
Description
```

Keep paragraph width controlled.

Avoid very wide text blocks.

---

# 11. Theme Section

Display the conference theme prominently.

Example:

```text
CONFERENCE THEME

"Advancing Healthcare Through
Innovation and Collaboration"
```

Use a visually distinct but restrained section.

Possible layout:

- light slate background
- purple accent
- gold decorative line

Avoid large decorative gradients.

---

# 12. Topics / Scope

Use reusable topic cards.

Example:

```text
┌──────────────┐
│ 01           │
│              │
│ Topic Title  │
│ Description  │
└──────────────┘
```

Cards should use:

- white background
- subtle border
- small shadow
- purple icon/accent

Avoid excessive card effects.

---

# 13. Important Dates

Use a timeline/list.

Example:

```text
Abstract Submission       01 Aug 2026
Notification               20 Aug 2026
Registration Deadline      15 Sep 2026
Full Paper Deadline        30 Sep 2026
Conference                  10–11 Nov 2026
```

Each date must come from conference timeline data.

---

# 14. Speakers

Speaker cards:

```text
┌────────────────────┐
│                    │
│     PHOTO          │
│                    │
├────────────────────┤
│ Speaker Name       │
│ Title              │
│ Institution        │
└────────────────────┘
```

Use consistent image ratios.

Do not make cards excessively large.

---

# 15. Registration Section

Show registration information clearly.

Example:

```text
Registration

Choose your participation type.

[ Student ]
[ Academic ]
[ Professional ]
[ General ]
```

Each registration type comes from:

```text
registration_types
```

Display:

- name
- price
- currency
- deadline
- included benefits if available
- registration CTA

---

# 16. Publication Section

Display:

- publication opportunities
- proceedings
- journals
- DOI information
- publication partners

Use conference-specific publication data.

---

# 17. Sponsors

Sponsor grid:

```text
[Logo] [Logo] [Logo] [Logo]
[Logo] [Logo] [Logo] [Logo]
```

Sponsors are conference-specific.

Use:

```text
sponsors
```

Do not hardcode logos.

---

# 18. FAQ

Accordion style.

Example:

```text
What is the abstract submission deadline?     +
What registration types are available?        +
How do I submit the full paper?               +
```

Keep interaction simple.

---

# 19. Contact

Include:

- conference secretariat
- email
- phone
- venue
- city
- website/social links if configured

Use conference-specific contact data.

---

# 20. Footer

Footer should be professional and compact.

Structure:

```text
ICHA Logo

About
Conference
Speakers
Timeline
Publication
Sponsors

Contact

© 2026 ICHA. All rights reserved.
```

The year in copyright may be generated dynamically.

---

# 21. Admin Design

Admin dashboard is separate from public website branding but should use the same core colors.

## Sidebar

```text
Dashboard

Conference
Participants
Registrations
Payments
Submissions
Papers
Presentations
Certificates
Publications
Sponsors

Settings
```

---

# 21.1 Current Conference Selector

Admin must always know which conference is currently being managed.

Example:

```text
Current Conference
[ ICHA 2027 ▼ ]
```

When changed, all conference-specific pages use the selected conference.

Never silently mix conference data.

---

# 22. Participant Dashboard

The participant dashboard should focus on progress.

Example:

```text
ICHA 2027

Registration       ✓ Paid
Abstract            ✓ Accepted
Full Paper          ● Pending
Presentation        ○ Not Submitted
Certificate         ○ Not Available
```

Also show:

- next action
- deadline
- conference
- submission number
- important notifications

---

# 23. Status Badges

Use both color and text.

Examples:

```text
[ Paid ]
[ Pending ]
[ Accepted ]
[ Revision Required ]
[ Rejected ]
```

Do not communicate status through color alone.

Suggested visual language:

- success → green
- warning/pending → amber
- rejected/error → red
- information → purple/blue
- neutral → slate

---

# 24. Cards

Default card style:

```text
bg-white
border border-slate-200
rounded-xl
shadow-sm
```

Hover should be subtle.

Avoid:

- huge shadows
- extreme rounded corners
- excessive blur
- animated scaling

---

# 25. Buttons

## Primary

Purple:

```text
bg-indigo-600
text-white
```

## Registration

Gold:

```text
bg-yellow-400
text-slate-950
```

## Secondary

White/slate:

```text
bg-white
border border-slate-300
text-slate-700
```

## Danger

Red:

```text
bg-red-600
text-white
```

Buttons should have:

- clear labels
- medium radius
- visible focus state
- comfortable padding

---

# 26. Forms

Forms should be clean and academic.

Input:

```text
border-slate-300
rounded-lg
focus:ring-indigo-500
focus:border-indigo-500
```

Always show:

- label
- validation message
- required indicator where applicable

Do not rely on placeholder text as the only label.

---

# 27. Tables

Admin tables must remain usable on desktop and mobile.

Desktop:

```text
┌────────┬────────────┬───────────┬─────────┐
│ Name   │ Conference │ Status    │ Action  │
└────────┴────────────┴───────────┴─────────┘
```

Mobile:

- horizontal scrolling
- maintain readable column widths
- important actions remain accessible

---

# 28. Responsive Design

## Desktop

- max content width: approximately `1280px`
- multi-column sections
- full navigation
- large hero

## Tablet

- 2-column layouts where appropriate
- reduced spacing
- compact navigation if needed

## Mobile

- single-column
- hamburger navigation
- horizontally scrollable tables
- stacked CTA buttons
- compact cards
- readable typography
- no horizontal page overflow

---

# 29. Spacing

Use a consistent spacing scale.

Preferred:

```text
4px
8px
12px
16px
24px
32px
48px
64px
80px
96px
```

Sections should generally have generous vertical spacing.

Avoid both:
- extremely compressed sections
- excessively large empty areas

---

# 30. Border Radius

Use restrained radius:

- inputs: `rounded-lg`
- buttons: `rounded-lg`
- cards: `rounded-xl`
- large containers: `rounded-2xl` where appropriate

Avoid pill-shaped UI everywhere.

Pills are reserved for:

- badges
- small labels
- status indicators

---

# 31. Animation

Animation should be subtle.

Allowed:

- hover transitions
- dropdown transitions
- mobile menu transitions
- small reveal animations

Avoid:

- excessive parallax
- continuous floating elements
- aggressive zoom
- distracting background animation
- long transitions

Recommended transition duration:

```text
150ms–300ms
```

---

# 32. Accessibility

Must support:

- semantic HTML
- keyboard navigation
- visible focus states
- sufficient color contrast
- alt text
- labels for forms
- accessible dropdowns
- accessible mobile menu
- status text in addition to color
- meaningful button labels

Images must have useful alt text.

Decorative images may use empty alt attributes.

---

# 33. Dynamic Data Rules

Never hardcode conference-specific information in reusable UI.

Avoid:

```blade
<h1>ICHA 2026</h1>
```

Use:

```blade
<h1>{{ $conference->title }}</h1>
```

Avoid:

```blade
November 10–11, 2026
```

Use:

```blade
{{ $conference->start_date }}
{{ $conference->end_date }}
```

Avoid:

```blade
Speaker 2026
```

Use:

```blade
@foreach ($conference->speakers as $speaker)
```

---

# 34. Reusable Components

Create reusable Blade components:

```text
x-navbar
x-footer
x-conference-selector
x-hero
x-countdown
x-section-heading
x-topic-card
x-speaker-card
x-timeline
x-registration-card
x-sponsor-grid
x-status-badge
x-button
x-card
```

Components must be reusable across all conference editions.

---

# 35. Component Rules

Components should:

- receive data through props
- avoid database queries directly where possible
- remain presentation-focused
- support responsive layouts
- follow the global design tokens

Bad:

```php
// Component directly querying arbitrary conference data
```

Better:

```blade
<x-speaker-card :speaker="$speaker" />
```

---

# 36. Conference Branding Overrides

A conference may optionally define:

- logo
- hero image
- primary accent
- secondary accent

However, branding overrides must stay within the ICHA design system.

Do not allow arbitrary colors that break readability or accessibility.

Fallback to global ICHA branding when conference-specific branding is missing.

---

# 37. Image Guidelines

Hero image:

- landscape
- high resolution
- optimized for web
- `object-cover`

Speaker image:

- consistent aspect ratio
- optimized size

Sponsor logo:

- transparent PNG/SVG preferred
- consistent display height
- preserve original proportions

Always optimize large uploads.

---

# 38. Loading and Empty States

Every dynamic section should have a sensible empty state.

Example:

```text
No speakers have been announced yet.
```

Do not leave blank sections that look broken.

For admin:

```text
No submissions found for ICHA 2027.
```

---

# 39. Error States

Show clear messages.

Example:

```text
Unable to load conference information.
Please try again.
```

Form validation should explain exactly what needs to be fixed.

---

# 40. Avoid

Do NOT use:

- excessive gradients
- excessive glassmorphism
- huge shadows
- random colors
- unnecessary popups
- excessive animation
- year-specific duplicated pages
- hardcoded conference information
- giant typography that hides important content
- overly decorative cards
- low-contrast yellow text on bright backgrounds

The final interface should look like a **professional international academic conference website**, with strong information hierarchy and a clean white navbar similar to established conference sites.
