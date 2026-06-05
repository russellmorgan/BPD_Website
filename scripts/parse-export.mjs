import { XMLParser } from 'fast-xml-parser';
import { readFileSync, writeFileSync, mkdirSync } from 'fs';
import { join, dirname } from 'path';
import { fileURLToPath } from 'url';

const __dirname = dirname(fileURLToPath(import.meta.url));
const ROOT = join(__dirname, '..');
const XML_FILE = join(ROOT, 'Site_Export', 'bpdhealthcare.WordPress.2026-05-28.xml');
const OUT_DIR = join(ROOT, 'site', 'src', 'data');

mkdirSync(OUT_DIR, { recursive: true });

const parser = new XMLParser({
  ignoreAttributes: false,
  attributeNamePrefix: '@_',
  cdataPropName: '__cdata',
  isArray: (name) => ['item', 'wp:postmeta', 'category', 'wp:category', 'wp:term', 'wp:tag'].includes(name),
  parseTagValue: false,
  trimValues: true,
});

console.log('Parsing XML...');
const xml = readFileSync(XML_FILE, 'utf-8');
const result = parser.parse(xml);
const channel = result.rss.channel;

// --- Taxonomy extraction ---
const taxonomy = {
  categories: [],
  icuCategories: [],
  workTypes: [],
  tags: [],
};

(channel['wp:category'] || []).forEach((cat) => {
  taxonomy.categories.push({
    id: val(cat['wp:term_id']),
    slug: val(cat['wp:category_nicename']),
    name: val(cat['wp:cat_name']),
  });
});

(channel['wp:tag'] || []).forEach((tag) => {
  taxonomy.tags.push({
    id: val(tag['wp:term_id']),
    slug: val(tag['wp:tag_slug']),
    name: val(tag['wp:tag_name']),
  });
});

(channel['wp:term'] || []).forEach((term) => {
  const taxonomy_name = val(term['wp:term_taxonomy']);
  const entry = {
    id: val(term['wp:term_id']),
    slug: val(term['wp:term_slug']),
    name: val(term['wp:term_name']),
    parent: val(term['wp:term_parent']),
  };
  if (taxonomy_name === 'icu_category') taxonomy.icuCategories.push(entry);
  else if (taxonomy_name === 'work_type') taxonomy.workTypes.push(entry);
});

// --- Post type mapping ---
const TYPE_MAP = {
  post: 'posts',
  'icu-blog': 'icu-blog',
  'icu-updates': 'icu-updates',
  podcasts: 'podcasts',
  staff: 'staff',
  work: 'work',
  events: 'events',
  'bpd-news': 'news',
  guides: 'guides',
  page: 'pages',
};

const SKIP_TYPES = new Set([
  'acf-field', 'acf-field-group', 'elementor_library', 'elementor_snippet',
  'nav_menu_item', 'wp_navigation', 'attachment',
]);

const buckets = {};
Object.values(TYPE_MAP).forEach((name) => (buckets[name] = []));

// --- Item processing ---
const items = channel.item || [];
console.log(`Total items in XML: ${items.length}`);

items.forEach((item) => {
  const postType = val(item['wp:post_type']);
  const status = val(item['wp:status']);

  if (SKIP_TYPES.has(postType)) return;
  if (status !== 'publish') return;

  const bucketName = TYPE_MAP[postType];
  if (!bucketName) return; // unknown type

  const postmeta = buildMeta(item['wp:postmeta']);
  const categories = buildCategories(item.category);
  let content = val(item['content:encoded']);

  // Clean Elementor shortcodes (page is layout-only, no readable content)
  if (content.startsWith('[elementor') || content.startsWith('<!-- [elementor')) {
    content = '';
  }
  // Strip Gutenberg block comments but keep content between them
  content = content.replace(/<!-- wp:[^\n]*-->/g, '').replace(/<!-- \/wp:[^\n]*-->/g, '').trim();

  const record = {
    id: val(item['wp:post_id']),
    title: val(item.title),
    slug: val(item['wp:post_name']),
    date: val(item['wp:post_date']),
    modified: val(item['wp:post_modified']),
    menuOrder: Number(val(item['wp:menu_order'])) || 0,
    link: val(item.link),
    content,
    excerpt: val(item['excerpt:encoded']),
    categories: categories.category || [],
    tags: categories.tag || [],
    icuCategories: categories.icu_category || [],
    workTypes: categories.work_type || [],
    meta: postmeta,
  };

  // Enrich staff records with ACF fields
  if (postType === 'staff') {
    record.firstName = postmeta.first_name || '';
    record.lastName = postmeta.last_name || '';
    record.role = postmeta.role || '';
    record.linkedinUrl = postmeta.linkedin_url || '';
    record.bio = postmeta.bio || '';
    record.funFact = postmeta.fun_fact || '';
    record.petName = postmeta.pet_name || '';
    // Use display name from title if name fields are empty
    if (!record.firstName && !record.lastName) {
      const parts = record.title.split(' ');
      record.firstName = parts[0] || '';
      record.lastName = parts.slice(1).join(' ');
    }
  }

  buckets[bucketName].push(record);
});

// Sort each bucket by date descending
Object.keys(buckets).forEach((name) => {
  buckets[name].sort((a, b) => new Date(b.date) - new Date(a.date));
});

// --- Write output ---
writeJson('taxonomy.json', taxonomy);
Object.entries(buckets).forEach(([name, data]) => {
  writeJson(`${name}.json`, data);
  console.log(`  ${name}.json: ${data.length} items`);
});

console.log('\nDone. Files written to site/src/data/');

// --- Helpers ---

function val(node) {
  if (node === undefined || node === null) return '';
  if (typeof node === 'object' && '__cdata' in node) return String(node.__cdata ?? '');
  return String(node);
}

function buildMeta(postmeta) {
  const out = {};
  if (!postmeta) return out;
  const entries = Array.isArray(postmeta) ? postmeta : [postmeta];
  entries.forEach((entry) => {
    const key = val(entry['wp:meta_key']);
    if (key.startsWith('_')) return; // skip ACF field pointers
    out[key] = val(entry['wp:meta_value']);
  });
  return out;
}

function buildCategories(categoryNodes) {
  const out = {};
  if (!categoryNodes) return out;
  const entries = Array.isArray(categoryNodes) ? categoryNodes : [categoryNodes];
  entries.forEach((cat) => {
    const domain = cat['@_domain'] || 'category';
    const slug = cat['@_nicename'] || '';
    const name = val(cat);
    if (!out[domain]) out[domain] = [];
    out[domain].push({ slug, name });
  });
  return out;
}

function writeJson(filename, data) {
  writeFileSync(join(OUT_DIR, filename), JSON.stringify(data, null, 2), 'utf-8');
}
