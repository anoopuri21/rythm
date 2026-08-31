import test from 'node:test';
import assert from 'node:assert/strict';
import { readFile } from 'node:fs/promises';

const read = (path) => readFile(new URL(`../../${path}`, import.meta.url), 'utf8');

test('homepage top bar is config-driven and hides missing contact data', async () => {
  const [config, component, layout] = await Promise.all([
    read('config/rythme.php'),
    read('resources/views/components/top-bar.blade.php'),
    read('resources/views/layouts/app.blade.php'),
  ]);

  assert.match(config, /contact_phone.*RYTHME_CONTACT_PHONE/s);
  assert.match(config, /contact_email.*RYTHME_CONTACT_EMAIL/s);
  assert.match(config, /social_links/);
  assert.match(component, /config\('rythme\.contact_phone'/);
  assert.match(component, /config\('rythme\.contact_email'/);
  assert.match(component, /str_starts_with\(\$social\['url'\], 'https:\/\/'\)/);
  assert.match(component, /Instagram/);
  assert.match(component, /Facebook/);
  assert.match(component, /YouTube/);
  assert.match(layout, /components\.top-bar/);
});

test('homepage offer marquee sits directly after the hero and only renders truthful 10 to 50 percent deals', async () => {
  const [home, marquee, service] = await Promise.all([
    read('resources/views/home/index.blade.php'),
    read('resources/views/home/_offer-marquee.blade.php'),
    read('app/Services/HomepageDataService.php'),
  ]);

  assert.match(home, /home\._hero[\s\S]*home\._offer-marquee/);
  assert.match(marquee, /bestDeals/);
  assert.match(marquee, /discount.*>= 10/);
  assert.match(marquee, /discount.*<= 50/);
  assert.match(marquee, /route\('product\.show'/);
  assert.match(marquee, /aria-hidden="true"/);
  assert.match(marquee, /offer-marquee__track/);
  assert.match(service, /bestDeals/);
  assert.doesNotMatch(marquee, /countdown|limited time|hurry/i);
});

test('recent purchase card is a front-end-only five-card demo with permanent browser dismissal', async () => {
  const [component, layout, js, css] = await Promise.all([
    read('resources/views/components/recent-purchase-card.blade.php'),
    read('resources/views/layouts/app.blade.php'),
    read('resources/js/modules/ui.js'),
    read('resources/css/app.css'),
  ]);

  assert.equal((component.match(/'product'\s*=>/g) ?? []).length, 5);
  assert.match(component, /@foreach\(\$demoPurchases as \$purchase\)/);
  assert.match(component, /Demo preview/);
  assert.match(component, /data-recent-purchase-close/);
  assert.match(component, /aria-label="Close recent purchase preview"/);
  assert.match(component, /unit price|₹/i);
  assert.doesNotMatch(component, /auth\(\)|Order::|query\(|database|Admin/i);
  assert.match(layout, /components\.recent-purchase-card/);
  assert.match(js, /rythme-recent-purchase-preview-dismissed-v1/);
  assert.match(js, /data-recent-purchase-demo/);
  assert.match(js, /10000/);
  assert.match(js, /localStorage/);
  assert.match(js, /prefers-reduced-motion/);
  assert.match(css, /recent-purchase__card\.is-active/);
  assert.match(css, /recent-purchase__close/);
});

test('homepage offer popup is homepage-only, offer-backed, close-persistent and 24-hour limited', async () => {
  const [home, popup, layout, js, css] = await Promise.all([
    read('resources/views/home/index.blade.php'),
    read('resources/views/home/_offer-popup.blade.php'),
    read('resources/views/layouts/app.blade.php'),
    read('resources/js/modules/ui.js'),
    read('resources/css/app.css'),
  ]);

  assert.match(home, /home\._offer-popup/);
  assert.doesNotMatch(layout, /offer-popup/);
  assert.match(popup, /bestDeals/);
  assert.match(popup, /discount >= 10/);
  assert.match(popup, /discount <= 50/);
  assert.match(popup, /data-offer-popup/);
  assert.match(popup, /data-offer-popup-close/);
  assert.match(popup, /role="dialog"/);
  assert.match(popup, /route\('product\.show'/);
  assert.match(js, /initOfferPopup/);
  assert.match(js, /rythme-offer-popup-closed-at-v1/);
  assert.match(js, /24 \* 60 \* 60 \* 1000/);
  assert.match(js, /Date\.now\(\)/);
  assert.match(js, /data-offer-popup-close/);
  assert.match(css, /offer-popup__dialog/);
  assert.match(css, /offer-popup\.is-pending/);
});

test('homepage UI plan records the clarified synthetic demo scope', async () => {
  const plan = await read('tasks/HOMEPAGE_UI_UX_MINOR_CHANGES_PLAN.md');
  assert.match(plan, /five synthetic front-end demo cards/i);
  assert.match(plan, /no Admin control/i);
  assert.match(plan, /every 10 seconds/i);
  assert.match(plan, /unit price/i);
  assert.match(plan, /Demo preview/i);
});
