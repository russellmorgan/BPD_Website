# BPD Healthcare — Static Site

Static site conversion of the BPD Healthcare WordPress site, built with [Astro](https://astro.build) and [Tailwind CSS](https://tailwindcss.com). Content is parsed from a WordPress XML export and served as fully static HTML — no CMS or server required.

---

## Repository structure

```
BPD_Website/
├── Site_Export/                  # WordPress XML export (source of truth for content)
│   └── bpdhealthcare.WordPress.2026-05-28.xml
├── scripts/
│   └── parse-export.mjs          # Converts XML export → JSON data files
├── site/                         # Astro project (the actual website)
│   ├── src/
│   │   ├── data/                 # Generated JSON files (do not hand-edit)
│   │   ├── components/           # Shared Astro components
│   │   ├── layouts/              # Base page layout
│   │   ├── pages/                # File-based routes
│   │   └── styles/global.css     # Tailwind v4 + custom prose styles
│   ├── public/                   # Static assets (favicon, etc.)
│   └── dist/                     # Build output (generated, not committed)
├── package.json                  # Root package — hosts the parser script
└── node_modules/                 # Root dependencies (fast-xml-parser)
```

---

## Prerequisites

- **Node.js** 18 or later

---

## First-time setup

Install dependencies for both the parser (root) and the Astro site:

```bash
npm install
cd site && npm install && cd ..
```

---

## Workflow

### 1. Parse the WordPress export

Run this any time the XML export is updated. It reads `Site_Export/*.xml` and writes JSON data files into `site/src/data/`.

```bash
node scripts/parse-export.mjs
```

Output files written to `site/src/data/`:

| File | Content | Published count |
|---|---|---|
| `posts.json` | Blog posts (Insights) | 131 |
| `icu-blog.json` | ICU issues blog | 183 |
| `icu-updates.json` | ICU policy updates | 154 |
| `podcasts.json` | Podcast episodes | 220 |
| `staff.json` | Team member profiles | 127 |
| `work.json` | Case studies | 23 |
| `events.json` | Events | 35 |
| `news.json` | BPD news | 27 |
| `guides.json` | Guides / white papers | 10 |
| `pages.json` | WordPress pages | 140 |
| `taxonomy.json` | All categories and tags | — |

Only `publish`-status posts are included. ACF custom fields (staff bios, event metadata, etc.) are extracted from WordPress postmeta.

### 2. Run the dev server

```bash
cd site
npm run dev
```

Opens at `http://localhost:4321`.

### 3. Build for production

```bash
cd site
npm run build
```

Static output is written to `site/dist/`. Deploy this directory to any static host.

```bash
npm run preview   # preview the production build locally
```

---

## Site sections and routes

| Section | URL | Detail pages |
|---|---|---|
| Home | `/` | — |
| Insights | `/insights/` | `/insights/[slug]/` |
| Insights by category | `/insights/category/[slug]/` | — |
| ICU (Issues & Crisis) | `/icu/` | `/icu/[slug]/` |
| ICU by topic | `/icu/category/[slug]/` | — |
| Work | `/work/` | `/work/[slug]/` |
| Work by type | `/work/category/[slug]/` | — |
| Podcast | `/podcast/` | `/podcast/[slug]/` |
| Team | `/team/` | `/team/[slug]/` |
| Events | `/events/` | `/events/[slug]/` |
| News | `/news/` | `/news/[slug]/` |
| Guides | `/guides/` | `/guides/[slug]/` |

---

## Design

- **Framework:** Astro v5 (static output)
- **Styling:** Tailwind CSS v4 (configured via CSS, no `tailwind.config.js`)
- **Colors:** Dark navy `#0f2240` (brand), sky blue `#0072ce` (accent)
- **Fonts:** System sans-serif stack
- **Article rendering:** WordPress HTML rendered via Astro's `set:html` with custom `.prose` styles
- **Images:** Not included — the WordPress media library was not exported. Image references in post content point to the original `bpdhealthcare.com` domain.

---

## Deployment

The `site/dist/` directory is a self-contained static site. Deploy to any platform:

**Netlify / Vercel**
- Build command: `cd site && npm install && npm run build`
- Publish directory: `site/dist`

**GitHub Pages**
- Use the [Astro GitHub Pages guide](https://docs.astro.build/en/guides/deploy/github/) and set the base to `/` if serving from a custom domain.

---

## Updating content

When a new WordPress export is available:

1. Replace the XML file in `Site_Export/`.
2. Run `node scripts/parse-export.mjs` from the repo root.
3. Run `cd site && npm run build` to rebuild.
4. Deploy `site/dist/`.
