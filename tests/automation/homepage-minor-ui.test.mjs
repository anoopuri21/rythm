import test from 'node:test';
import assert from 'node:assert/strict';
import { readFile } from 'node:fs/promises';

const read = (path) => readFile(new URL(`../../${path}`, import.meta.url), 'utf8');

test('homepage top bar is settings-driven and hides missing contact data', async () => {
  const [component, layout, settings] = await Promise.all([
    read('resources/views/components/top-bar.blade.php'),
    read('resources/views/layouts/app.blade.php'),
    read('app/Services/SiteSettingsService.php'),
  ]);

  assert.match(component, /SiteSettingsService/);
  assert.match(component, /contact_phone/);
  assert.match(component, /contact_email/);
  assert.match(component, /social_instagram/);
  assert.match(component, /Instagram/);
  assert.match(component, /Facebook/);
  assert.match(component, /YouTube/);
  assert.match(component, /FILTER_VALIDATE_URL/);
  assert.match(layout, /components\.top-bar/);
  // Defaults empty so storefront never invents phone/email (C5 / W5).
  assert.match(settings, /'contact_email'\s*=>\s*''/);
  assert.match(settings, /'contact_phone'\s*=>\s*''/);
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

test('recent purchase synthetic demo is purged from storefront (C5 / W4)', async () => {
  const [component, layout, js] = await Promise.all([
    read('resources/views/components/recent-purchase-card.blade.php'),
    read('resources/views/layouts/app.blade.php'),
    read('resources/js/modules/ui.js'),
  ]);

  assert.doesNotMatch(component, /\$demoPurchases/);
  assert.doesNotMatch(component, /data-recent-purchase-demo/);
  assert.doesNotMatch(component, /Fender Player Stratocaster/);
  assert.doesNotMatch(layout, /components\.recent-purchase-card/);
  assert.doesNotMatch(js, /initRecentPurchasePreview/);
  assert.doesNotMatch(js, /rythme-recent-purchase-preview-dismissed-v1/);
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
  assert.match(popup, /loading="lazy"/);
  assert.match(popup, /fetchpriority="low"/);
  assert.match(popup, /route\('product\.show'/);
  assert.match(js, /initOfferPopup/);
  assert.match(js, /rythme-offer-popup-closed-at-v1/);
  assert.match(js, /24 \* 60 \* 60 \* 1000/);
  assert.match(js, /Date\.now\(\)/);
  assert.match(js, /data-offer-popup-close/);
  assert.match(css, /offer-popup__dialog/);
  assert.match(css, /offer-popup\.is-pending/);
});

test('homepage UI plan notes C5 purge of synthetic recent-purchase demo', async () => {
  const plan = await read('tasks/HOMEPAGE_UI_UX_MINOR_CHANGES_PLAN.md');
  assert.match(plan, /C5|purged|removed|production/i);
});
