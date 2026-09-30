import assert from 'node:assert/strict';

const base = process.env.WP_BASE_URL;

if (!base) {
  throw new Error('Set WP_BASE_URL to a running WordPress site.');
}

async function get(path) {
  const response = await fetch(new URL(path, base));
  return { response, body: await response.text() };
}

async function seededResource(kind, slug, label) {
  const resource = await get(`/wp-json/wp/v2/${kind}?slug=${slug}`);
  assert.equal(resource.response.status, 200, `${label} REST resource should respond`);
  const items = JSON.parse(resource.body);
  assert.equal(items.length, 1, `site should have the seeded ${label}`);
  const rendered = await get(items[0].link);
  assert.equal(rendered.response.status, 200, `${label} template should render`);
  assert.match(rendered.body, /<main id="main-content"/, `${label} should use the layout`);
  return rendered.body;
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

assert.match(await seededResource('posts', 'ci-post', 'post'), /A seeded integration post\./);
assert.match(await seededResource('pages', 'ci-page', 'page'), /A seeded integration page\./);
assert.match(await seededResource('categories', 'ci-category', 'category'), /CI Post/);

const search = await get('/?s=CI+Post');
assert.equal(search.response.status, 200, 'search template should render');
assert.match(search.body, /type="search"/, 'search template should contain a search field');
assert.match(search.body, /CI Post/, 'search should show the seeded post');

const missing = await get('/wpmvc-test-missing-page-239481/');
assert.equal(missing.response.status, 404, '404 template should return a 404 status');
assert.match(missing.body, /<main id="main-content"/, '404 should use the layout');

const publicApi = await get('/wp-json/wpmvc/v1/theme');
assert.equal(publicApi.response.status, 200, 'public REST route should respond');
const theme = JSON.parse(publicApi.body);
assert.equal(theme.name, 'WordPress MVC Starter');
assert.match(theme.version, /^\d/);

const privateApi = await get('/wp-json/wpmvc/v1/admin/theme');
assert.equal(privateApi.response.status, 401, 'private REST route should reject anonymous visitors');
assert.equal(JSON.parse(privateApi.body).code, 'rest_forbidden');

const ajax = await fetch(new URL('/wp-admin/admin-ajax.php?action=wpmvc_theme_status', base), {
  method: 'POST',
});
assert.ok(ajax.status >= 400, 'private AJAX route should reject anonymous visitors');

console.log('WordPress activation, page contexts, assets, REST, and AJAX smoke checks passed.');
