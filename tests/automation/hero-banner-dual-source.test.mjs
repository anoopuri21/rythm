import test from 'node:test';
import assert from 'node:assert/strict';
import { readFile } from 'node:fs/promises';
import { glob } from 'node:fs/promises';

const read = (path) => readFile(new URL(`../../${path}`, import.meta.url), 'utf8');

/**
 * HERO + BANNERS: "built-in default AND admin-manageable".
 *
 * Unlike the rest of the no-hardcode rule (docs/NO_HARDCODE_PLAN.md), the
 * homepage hero and banner sections deliberately keep their shipped copy and
 * imagery in the code — and additionally expose every field as an admin
 * override row. This gate pins BOTH halves of that contract:
 *
 *   1. the built-in defaults still exist in HeroBannerService::DEFAULTS and
 *      still describe the shipped banners;
 *   2. the views read them through the service, so an admin row can replace
 *      any single field without the banner rendering half-empty;
 *   3. the admin surface exists for every field and every slot;
 *   4. the three dead hero knobs stay deleted.
 *
 * @see docs/HERO_BANNERS_DUAL_SOURCE_PLAN.md
 */

const SERVICE = 'app/Services/HeroBannerService.php';
const MODEL = 'app/Models/HeroBanner.php';
const RESOURCE = 'app/Filament/Resources/HeroBannerResource.php';
const HERO_VIEW = 'resources/views/home/_hero.blade.php';
const LAUNCH_VIEW = 'resources/views/home/_recently-launched.blade.php';
const MIGRATION = 'database/migrations/2026_10_04_000002_create_hero_banners_table.php';

/** Slots, in render order. */
const SLOTS = ['hero-tall', 'hero-small-1', 'hero-small-2', 'launch-banner'];

/** Every field an admin can override. `image` maps to the Media Library upload. */
const FIELDS = ['href', 'title', 'subtitle', 'cta_label', 'image', 'alt'];

/** Fields that must ship with a non-empty built-in value. */
const REQUIRED_FIELDS = ['href', 'title', 'subtitle', 'cta_label', 'image'];

/** Shipped copy/imagery. If these disappear from DEFAULTS the site changed look. */
const SHIPPED = {
  'hero-tall': ['Stage Pianos', 'As expressive as it is portable', 'images/hero/grid-banner-piano.jpg', '/shop?category=keyboards-pianos'],
  'hero-small-1': ['Tabla Sets', 'Explore percussion instruments', 'images/hero/grid-banner-tabla.jpg', '/shop?category=drums-percussion'],
  'hero-small-2': ['Studio Gear', 'Explore current studio offers', 'images/hero/grid-banner-headphones.jpg', '/shop?category=pro-audio'],
  'launch-banner': ['Fresh gear,', 'first play', 'Just landed', 'Explore all', 'images/brand-feature.jpg'],
};

/** `SLOT_HERO_TALL => 'hero-tall'` — the only place the slot id is defined. */
function slotConstants(source) {
  return Object.fromEntries(
    [...source.matchAll(/public const (SLOT_[A-Z0-9_]+) = '([a-z0-9-]+)';/g)].map((m) => [m[1], m[2]]),
  );
}

/** The `DEFAULTS` array, split into one raw block per slot. */
function defaultBlocks(source) {
  const body = source.slice(source.indexOf('public const DEFAULTS = ['), source.indexOf('private ?array $resolved'));
  const blocks = {};
  for (const m of body.matchAll(/self::(SLOT_[A-Z0-9_]+) => \[([\s\S]*?)\n {8}\],/g)) {
    blocks[m[1]] = m[2];
  }
  return blocks;
}

/** `'title' => 'Stage Pianos'` (or a double-quoted value) inside one DEFAULTS slot block. */
function defaultField(block, field) {
  const m = block.match(new RegExp(`'${field}' => (?:'((?:[^'\\\\]|\\\\.)*)'|"((?:[^"\\\\]|\\\\.)*)")`));
  return m === null ? undefined : (m[1] ?? m[2]);
}

function slice(source, from, to) {
  const start = source.indexOf(from);
  assert.notEqual(start, -1, `missing anchor: ${from}`);
  const end = source.indexOf(to, start);
  assert.notEqual(end, -1, `missing anchor: ${to}`);
  return source.slice(start, end);
}

test('built-in defaults still ship every hero/banner slot with full copy', async () => {
  const service = await read(SERVICE);
  const constants = slotConstants(service);
  const blocks = defaultBlocks(service);

  assert.deepEqual(Object.values(constants).sort(), [...SLOTS].sort(), 'slot constants drifted from the documented slots');
  assert.deepEqual(Object.keys(blocks).sort(), Object.keys(constants).sort(), 'DEFAULTS must cover exactly the declared slots');

  for (const [constant, slot] of Object.entries(constants)) {
    for (const field of REQUIRED_FIELDS) {
      const value = defaultField(blocks[constant], field);
      assert.ok(typeof value === 'string' && value.trim() !== '', `${slot}.${field} lost its built-in default`);
    }
    for (const expected of SHIPPED[slot]) {
      assert.ok(
        blocks[constant].includes(expected),
        `${slot} no longer ships "${expected}" — the built-in banner changed without the gate being updated`,
      );
    }
  }
});

test('every field falls back to its built-in default, one field at a time', async () => {
  const service = await read(SERVICE);
  const merge = slice(service, 'public function all(): array', 'private function overrides()');

  for (const field of FIELDS) {
    assert.match(merge, new RegExp(`\\$default\\['${field}'\\]`), `all() must fall back to the built-in default for "${field}"`);
  }

  // One pick() per field: an empty admin field must never blank the banner.
  assert.equal((merge.match(/\$this->pick\(/g) ?? []).length, FIELDS.length, 'expected one default-fallback pick() per field');
  assert.match(service, /private function pick\(\?string \$value, string \$default\): string/, 'pick() signature changed');

  // Uncached on purpose: one indexed query, admin edits live on the next request.
  assert.doesNotMatch(service, /Cache::/, 'HeroBannerService must stay uncached so admin edits are instant');
});

test('banner links are same-origin and only target live categories', async () => {
  const service = await read(SERVICE);
  const guard = slice(service, 'private function safeHref(', 'private function isLiveCategory(');

  assert.match(guard, /str_starts_with\(\$href, '\/'\)/, 'off-site hrefs must be rejected');
  assert.match(guard, /str_starts_with\(\$href, '\/\/'\)/, 'protocol-relative hrefs must be rejected');
  assert.match(guard, /category=\(\[\^&#\]\+\)/, 'category hrefs must be parsed for the liveness check');
  assert.match(guard, /\? \$href : '\/shop'/, 'a dead category link must fall back to /shop');
  assert.match(service, /->where\('is_active', true\)/, 'category liveness must ignore inactive categories');
});

test('hero and launch-banner views read banners through the service', async () => {
  const [hero, launch] = await Promise.all([read(HERO_VIEW), read(LAUNCH_VIEW)]);

  assert.match(hero, /HeroBannerService::class/, 'the hero must resolve banners through HeroBannerService');
  assert.match(launch, /HeroBannerService::class/, 'the launch banner must resolve through HeroBannerService');

  // Every rendered value comes from the resolved slot, never from a literal.
  for (const variable of ['tall', 'small1', 'small2']) {
    for (const field of ['href', 'image', 'title', 'subtitle', 'cta_label']) {
      assert.match(hero, new RegExp(`\\{\\{ \\$${variable}\\['${field}'\\] \\}\\}`), `hero banner "${variable}" must render ${field} from the service`);
    }
  }
  for (const field of ['href', 'image', 'subtitle', 'cta_label']) {
    assert.match(launch, new RegExp(`\\{\\{ \\$launch\\['${field}'\\] \\}\\}`), `launch banner must render ${field} from the service`);
  }
  // The shipped title is two lines; the break must stay escaped output, never raw.
  assert.match(launch, /\{!! nl2br\(e\(\$launch\['title'\]\)\) !!\}/, 'launch title must render through nl2br(e(...))');
  assert.doesNotMatch(launch, /\{!!\s*\$launch/, 'no banner field may be echoed unescaped');

  // The shipped literals now live only in DEFAULTS.
  const forbidden = [...Object.values(SHIPPED).flat(), 'grid-banner-piano.jpg', 'grid-banner-tabla.jpg', 'grid-banner-headphones.jpg', 'brand-feature.jpg'];
  for (const literal of forbidden) {
    assert.ok(!hero.includes(literal), `hero view still hardcodes "${literal}"`);
    assert.ok(!launch.includes(literal), `launch-banner view still hardcodes "${literal}"`);
  }
});

test('admin can override every field of every slot', async () => {
  const [resource, model, service, migration] = await Promise.all([
    read(RESOURCE),
    read(MODEL),
    read(SERVICE),
    read(MIGRATION),
  ]);

  for (const field of FIELDS.filter((f) => f !== 'image')) {
    const maker = field === 'title' ? 'Textarea' : 'TextInput';
    assert.match(resource, new RegExp(`${maker}::make\\('${field}'\\)`), `no admin field for "${field}"`);
  }
  assert.match(resource, /MediaUpload::single\('image', 'image'/, 'no admin image upload');
  assert.match(resource, /Select::make\('slot'\)/, 'slot must be a constrained select, not free text');
  assert.match(resource, /->unique\(ignoreRecord: true\)/, 'one row per slot must be enforced');
  assert.match(resource, /Toggle::make\('is_active'\)/, 'an override must be switchable off');

  // The select offers exactly the slots that have a built-in default.
  assert.match(resource, /->options\(HeroBanner::SLOTS\)/, 'slot options must come from the model');
  const slotsConst = model.slice(model.indexOf('public const SLOTS = ['), model.indexOf('protected $casts'));
  const modelSlots = [...slotsConst.matchAll(/'([a-z0-9-]+)' => '/g)].map((m) => m[1]).sort();
  assert.deepEqual(modelSlots, [...SLOTS].sort(), 'HeroBanner::SLOTS drifted from the service slots');
  assert.deepEqual(Object.values(slotConstants(service)).sort(), [...SLOTS].sort());

  for (const column of ['slot', 'title', 'subtitle', 'cta_label', 'href', 'image_url', 'alt', 'is_active']) {
    assert.match(migration, new RegExp(`'${column}'`), `hero_banners migration is missing the "${column}" column`);
  }
  assert.match(migration, /->unique\(\)/, 'slot must be unique in the schema too');

  // Content managers own it, and edits are audited like other content.
  const access = await read('app/Support/AdminAccess.php');
  assert.match(access, /HeroBanner::class => \['view' => self::CONTENT_MANAGE, 'manage' => self::CONTENT_MANAGE\]/);
  const provider = await read('app/Providers/AppServiceProvider.php');
  assert.match(provider, /HeroBanner::class => ContentPolicy::class/);
  assert.match(provider, /HeroBanner::class,\n\s+HeroSlide::class,/, 'HeroBanner must be in the admin audit list');
});

test('the dead hero knobs stay deleted', async () => {
  const dead = ['hero_mode', 'heroMode', 'hero_video_url', 'video_showcase_url', 'RYTHME_HERO_MODE'];
  const roots = ['app', 'config', 'database', 'resources', 'routes', 'tests'];

  const offenders = [];
  for (const root of roots) {
    for await (const entry of glob(`${root}/**/*.{php,blade.php,mjs,js}`, { cwd: new URL('../../', import.meta.url) })) {
      if (entry.includes('hero-banner-dual-source.test.mjs')) continue; // this gate names them on purpose
      const source = await read(entry);
      for (const needle of dead) {
        if (source.includes(needle)) offenders.push(`${entry}: ${needle}`);
      }
    }
  }

  assert.deepEqual(offenders, [], `dead hero knobs came back: ${offenders.join(', ')}`);
});
