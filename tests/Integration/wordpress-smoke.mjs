import assert from 'node:assert/strict';

const base = process.env.WP_BASE_URL;

if (!base) {
  throw new Error('Set WP_BASE_URL to a running WordPress site.');
}

async function get(path) {
  const response = await fetch(new URL(path, base));
  return { response, body: await response.text() };
}

const home = await get('/');
assert.equal(home.response.status, 200, 'front page should render');
assert.match(home.body, /<body[^>]*\bwpmvc-theme\b/, 'theme should be active');
assert.match(home.body, /<main id="main-content"/, 'layout should contain main landmark');
assert.match(home.body, /class="skip-link/, 'layout should contain a skip link');
assert.match(home.body, /wpmvc-theme-app-js-module/, 'built frontend asset should enqueue');
const assetUrl = home.body.match(/<script[^>]+src="([^"]+\/public\/build\/assets\/app-[^"]+\.js(?:\?[^\"]*)?)"/);
assert.ok(assetUrl, 'frontend asset should use a hashed build filename');
assert.equal((await get(assetUrl[1])).response.status, 200, 'built asset should be available');

for (const [kind, label] of [['posts', 'post'], ['pages', 'page'], ['categories', 'archive']]) {
  const resource = await get(`/wp-json/wp/v2/${kind}?per_page=1`);
  assert.equal(resource.response.status, 200, `${kind} REST resource should respond`);
  const items = JSON.parse(resource.body);
  assert.ok(items.length > 0, `site should have a seeded ${label}`);
  const rendered = await get(items[0].link);
  assert.equal(rendered.response.status, 200, `${label} template should render`);
  assert.match(rendered.body, /<main id="main-content"/, `${label} should use the layout`);
}

const search = await get('/?s=wordpress');
assert.equal(search.response.status, 200, 'search template should render');
assert.match(search.body, /type="search"/, 'search template should contain a search field');

const missing = await get('/wpmvc-test-missing-page-239481/');
assert.equal(missing.response.status, 404, '404 template should return a 404 status');
assert.match(missing.body, /<main id="main-content"/, '404 should use the layout');

const publicApi = await get('/wp-json/wpmvc/v1/theme');
assert.equal(publicApi.response.status, 200, 'public REST route should respond');
assert.doesNotThrow(() => JSON.parse(publicApi.body), 'public REST response should be JSON');

const privateApi = await get('/wp-json/wpmvc/v1/admin/theme');
assert.equal(privateApi.response.status, 401, 'private REST route should reject anonymous visitors');

const ajax = await fetch(new URL('/wp-admin/admin-ajax.php?action=wpmvc_theme_status', base), {
  method: 'POST',
});
assert.ok(ajax.status >= 400, 'private AJAX route should reject anonymous visitors');

console.log('WordPress activation, page contexts, assets, REST, and AJAX smoke checks passed.');
