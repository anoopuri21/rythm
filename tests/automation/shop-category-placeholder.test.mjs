import test from 'node:test';
import assert from 'node:assert/strict';
import { readFile } from 'node:fs/promises';

const read = (path) => readFile(new URL(`../../${path}`, import.meta.url), 'utf8');

/** Full source of every `@media (max-width: 359px)` block, brace-matched. */
function mediaBlocks(css, query) {
  const blocks = [];

  for (const match of css.matchAll(new RegExp(`@media\\s*\\(${query}\\)\\s*\\{`, 'g'))) {
    let depth = 1;
    let i = match.index + match[0].length;

    while (i < css.length && depth > 0) {
      if (css[i] === '{') depth += 1;
      if (css[i] === '}') depth -= 1;
      i += 1;
    }

    blocks.push(css.slice(match.index + match[0].length, i - 1));
  }

  return blocks;
}

test('shop category shortcut keeps a fixed placeholder tile when the category has no image', async () => {
  const [view, css] = await Promise.all([
    read('resources/views/livewire/shop-index.blade.php'),
    read('resources/css/app.css'),
  ]);

  // 1. The placeholder is part of the tile, not an @else branch — so a null,
  //    empty or 404ing `image` can never leave the tile without content.
  const tile = view.slice(view.indexOf('class="shop-shortcut__image"'), view.indexOf('@endforeach'));
  assert.ok(tile.length > 0, 'shop shortcut tile not found');
  assert.ok(
    tile.indexOf('shop-shortcut__placeholder') < tile.indexOf("@if($shortcut['image'])"),
    'placeholder must render unconditionally, before the image branch',
  );
  assert.doesNotMatch(tile, /@else/);
  assert.match(view, /<span class="shop-shortcut__placeholder" aria-hidden="true">/);

  // 2. The tile no longer borrows the product-card fallback class: that class is
  //    `position: absolute; inset: 0` and only works inside a positioned parent,
  //    which `.shop-shortcut__image` was not — the placeholder escaped to the
  //    initial containing block and painted a full-viewport panel.
  assert.doesNotMatch(view, /pcard__img-fallback/);
  assert.match(css, /\.shop-shortcut__image\s*\{[^}]*position:\s*relative/);
  assert.match(css, /\.shop-shortcut__placeholder\s*\{[^}]*position:\s*absolute[^}]*inset:\s*0/);

  // 3. Tile geometry is fixed, so a placeholder is the same box as a photo.
  assert.match(css, /\.shop-shortcut__image\s*\{[^}]*flex:\s*0 0 auto/);
  assert.match(css, /\.shop-shortcut__image\s*\{[^}]*width:\s*78px[^}]*height:\s*78px/);
  assert.match(css, /\.shop-shortcut__image\s*\{[^}]*overflow:\s*hidden/);

  // 4. A URL that resolves server-side but 404s in the browser is handled too:
  //    Alpine flags the <img>, which hides over the placeholder.
  assert.match(view, /x-data="\{ broken: false \}"/);
  assert.match(view, /@error="broken = true"/);
  assert.match(view, /:class="\{ 'is-missing': broken \}"/);
  assert.match(css, /\.shop-shortcut__image img\.is-missing\s*\{[^}]*opacity:\s*0/);
});

test('shop category shortcut row stays responsive with or without images', async () => {
  const css = await read('resources/css/app.css');

  // Mobile: single scroll-snap row; the image tile never flexes away.
  assert.match(css, /\.shop-shortcuts\s*\{[^}]*grid-auto-flow:\s*column/);
  assert.match(css, /\.shop-shortcuts\s*\{[^}]*grid-auto-columns:\s*minmax\(112px, 1fr\)/);
  assert.match(css, /\.shop-shortcuts\s*\{[^}]*overflow-x:\s*auto/);

  // >=1024px: scroller off, one equal track per category. Must not hardcode a
  // track count — the seeder ships six parents, and `repeat(8, …)` left two
  // empty tracks on the right.
  const wide = mediaBlocks(css, 'min-width:\\s*1024px');
  assert.ok(
    wide.some((b) => /\.shop-shortcuts\s*\{[^}]*grid-auto-columns:\s*minmax\(0, 1fr\)[^}]*overflow:\s*visible/.test(b)),
    'no equal-track desktop rule inside @media (min-width: 1024px)',
  );
  assert.doesNotMatch(css, /grid-template-columns:\s*repeat\(8,/);

  // <=359px: the tile and its placeholder shrink so 320px phones still fit.
  const small = mediaBlocks(css, 'max-width:\\s*359px');
  assert.ok(
    small.some((b) => /\.shop-shortcut__image\s*\{\s*width:\s*64px;\s*height:\s*64px;/.test(b)),
    'no 320px-safe tile size inside @media (max-width: 359px)',
  );
  assert.ok(
    small.some((b) => /\.shop-shortcuts\s*\{[^}]*grid-auto-columns:\s*minmax\(96px, 1fr\)/.test(b)),
    'no 320px-safe column size inside @media (max-width: 359px)',
  );
});

test('shop category shortcuts render admin data through safe, cached paths', async () => {
  const [view, service] = await Promise.all([
    read('resources/views/livewire/shop-index.blade.php'),
    read('app/Services/CategoryService.php'),
  ]);

  // The shortcut loop must not stat the filesystem per request — resolution
  // happens once, inside the cached tree.
  const section = view.slice(view.indexOf('shop-shortcuts'), view.indexOf('@endforeach'));
  assert.doesNotMatch(section, /public_path\(|is_file\(/);
  assert.match(section, /\$shortcut\['image'\]/);

  // Slug goes into a JS call inside an HTML attribute: @js() quotes and escapes
  // it, so an admin-typed apostrophe cannot break out of the string literal.
  assert.match(view, /wire:click="setCategory\(@js\(\$shortcut\['slug'\]\)\)"/);
  assert.doesNotMatch(section, /setCategory\('\{\{/);

  // Buttons stay buttons: role="listitem" stripped their semantics and made
  // aria-pressed invalid.
  assert.doesNotMatch(section, /role="listitem"/);
  assert.doesNotMatch(section, /role="list"/);
  assert.match(view, /aria-pressed="/);

  // icon_url is free text in the admin form, so it is sanitised once at the
  // cache boundary: blanks become null, and only same-origin paths or http(s)
  // URLs survive.
  assert.match(service, /private static function storefrontImage\(\?string \$url\): \?string/);
  assert.match(service, /\[\\x00-\\x1F\\x7F\]/);
  assert.match(service, /in_array\(strtolower\(\$scheme\), \['http', 'https'\], true\)/);
  assert.match(service, /str_starts_with\(\$url, '\/\/'\)/);
  assert.match(service, /'image' => self::storefrontImage\(/);

  // Keep the media-architecture invariant intact: column-first, then the
  // committed asset, then null.
  assert.match(
    service,
    /\$category->iconUrl\(\)\s*\?\?\s*\(is_file\(public_path\(\$asset\)\)\s*\?\s*'\/'\.\$asset\s*:\s*null\)/,
  );
});

test('every remaining product-card fallback lives inside a positioned parent', async () => {
  const [css, mega, deals, categories] = await Promise.all([
    read('resources/css/app.css'),
    read('resources/views/components/mega-product-card.blade.php'),
    read('resources/views/home/_deals.blade.php'),
    read('resources/views/home/_categories.blade.php'),
  ]);

  // `.pcard__img-fallback` is absolutely positioned; assert its two parents are
  // positioned so the shop-shortcut regression cannot reappear elsewhere.
  assert.match(css, /\.pcard__img\s*\{[^}]*position:\s*relative/);
  assert.match(css, /\.cat-card__img\s*\{[^}]*position:\s*relative/);

  for (const [name, view] of [['mega-product-card', mega], ['_deals', deals], ['_categories', categories]]) {
    const index = view.indexOf('pcard__img-fallback');
    assert.ok(index > -1, `${name} no longer uses the shared fallback`);

    // The nearest preceding classed element must be one of the two positioned
    // parents — otherwise the fallback escapes to the initial containing block.
    const parents = [...view.slice(0, index).matchAll(/class="([^"]*\b(?:pcard__img|cat-card__img)\b[^"]*)"/g)];
    const nearest = parents.at(-1);
    assert.ok(nearest, `${name}: fallback has no positioned parent`);
    assert.match(nearest[1], /^(pcard__img|cat-card__img)$/);
  }
});
