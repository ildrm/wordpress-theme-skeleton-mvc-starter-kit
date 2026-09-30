# WPMVC Theme

A classic WordPress theme with a small MVC application layer. WordPress owns request parsing, authentication, the main query, template selection, REST, AJAX, and the database. The theme adds dependency injection, semantic template routes, controllers, repository contracts, PHP views, configuration, and Vite assets. It neither loads Laravel nor creates another HTTP lifecycle.

## Install and develop

Requirements: WordPress 6.6+, PHP 8.2+, Composer 2, and Node.js 20.19+ or 22.12+.

Install the theme in `wp-content/themes/wpmvc-theme`. For production, run `composer install --no-dev --optimize-autoloader` and `npm ci && npm run build`, then deploy `vendor/` and `public/build/` with the theme. Activate it in WordPress. The theme does not write to its own directory at runtime and has no database migrations. For local development use `composer install`, `npm ci`, and `npm run dev`; WordPress remains the PHP server and Vite builds the assets. `style.css` contains WordPress metadata and fallback styles.

Supply a real project screenshot as `screenshot.png` before distributing the finished theme. The skeleton does not include a generic screenshot.

## Lifecycle and boundaries

```text
WordPress → template hierarchy → root PHP adapter → TemplateDispatcher
→ TemplateRouter → controller → repository → WordPress API
→ ViewData → view → layout → HTML
```

`functions.php` loads Composer and `bootstrap/app.php`. Bootstrap creates `Application`, loads `config/*.php`, registers all classes in `bootstrap/providers.php`, then boots every provider. `AppServiceProvider` binds application contracts; `RoutingServiceProvider` loads route files; `ViewServiceProvider` configures rendering; `ThemeServiceProvider` registers theme features and assets; `WordPressServiceProvider` registers hooks. This two-phase lifecycle lets a provider use another provider's bindings in `boot()`.

| Path | Purpose |
| --- | --- |
| `app/Foundation`, `app/Providers` | Container, configuration, provider lifecycle |
| `app/Routing`, `routes` | WordPress template, REST, AJAX, and admin declarations |
| `app/Http/Controllers` | Coordinate requests and return views or WordPress responses |
| `app/Contracts/Repositories`, `app/Repositories/WordPress` | Data contracts and WordPress-backed retrieval |
| `app/Data`, `app/Actions`, `app/Services` | Typed data and focused use cases when needed |
| `app/WordPress` | Supports, menus, hooks, sidebars, image sizes, assets |
| `resources/views` | PHP layouts, pages, partials, components, errors |
| `resources/css`, `resources/js`, `public/build` | Source and built assets |

Controllers should stay thin. Repositories own data retrieval; services and actions own reusable operations. Views receive an explicit `ViewData` object, do not construct queries or resolve services, and escape at output. `WP_Post` remains a WordPress entity; this theme does not invent an ORM. Persistent schema and post type registration normally belong in a plugin so content survives a theme switch.

## Container, configuration, and providers

`Application` extends a lightweight container with `bind`, `singleton`, `instance`, aliases, constructor injection, method calls, parameter overrides, and circular dependency errors. Bind an interface in a provider and type-hint it in a controller:

```php
$this->app->bind(PostRepository::class, WordPressPostRepository::class);

public function __construct(
    private readonly PostRepository $posts,
    private readonly ViewFactory $views,
) {}
```

Add a provider to `bootstrap/providers.php` when it owns a coherent group of bindings or hooks. Add a hook class with `register(): void` under `app/WordPress/Hooks` and invoke it from `HookRegistrar` or a focused provider. The namespaced `WPMVC\Support\app()`, `config()`, and `view()` helpers are for framework boundaries; controllers and services should use constructor injection.

Configuration files return arrays. `config('theme.text_domain')`, `config('assets.manifest')`, and `config('view.paths', [])` use dot notation. Environment values come from WordPress (`wp_get_environment_type()` and `WP_DEBUG`); there is no `.env` loader or writable configuration cache.

## Web routes, controllers, and views

`routes/web.php` maps keys such as `front-page`, `single`, `search`, and `404` to controller actions. WordPress still chooses the root PHP template. To add a custom type, create a tiny `single-product.php` adapter that dispatches the key and register it:

```php
$router->template('single-product', [ProductController::class, 'show']);
```

A controller may call an injected repository and return a view:

```php
final class ProductController
{
    public function __construct(
        private readonly ProductRepository $products,
        private readonly ViewFactory $views,
    ) {}

    public function show(): View
    {
        return $this->views->make('pages.product', [
            'product' => $this->products->current(),
        ])->layout('layouts.app');
    }
}
```

Put `ProductRepository` in `app/Contracts/Repositories`, a WordPress implementation in `app/Repositories/WordPress`, and its binding in `AppServiceProvider`. Put a reusable multi-step operation in `app/Actions` or `app/Services`. Keep SQL and complex `WP_Query` setup out of controllers.

The finder resolves `pages.product` to `resources/views/pages/product.php`. The view reads `$data->get('product')` and escapes with `esc_html()`, `esc_attr()`, `esc_url()`, or appropriate allowed-HTML handling. Render a component with `$view->render('components.post-card', ['post' => $post])`. A view composer can supply layout data:

```php
$views->composer('layouts.app', static function (ViewData $data): ViewData {
    return $data->with('notice', 'Example');
});
```

Use composers sparingly. Child themes can override WordPress root templates and parent views by adding the same path under the child theme's `resources/views` directory.

## REST, AJAX, and admin routing

`routes/api.php` declares REST endpoints registered on `rest_api_init`. Every route must supply a permission callback or capability. WordPress parameter schemas validate and sanitize REST input. Controllers may use `WP_REST_Request` and return data, `WP_REST_Response`, or `WP_Error`.

```php
$router->get('/items', [ItemController::class, 'index'], [
    'permission' => 'edit_posts',
    'args' => [
        'page' => ['type' => 'integer', 'minimum' => 1, 'sanitize_callback' => 'absint'],
    ],
]);
```

The example `GET /wp-json/wpmvc/v1/theme` route exposes only public theme information. For private routes choose a capability; a REST nonce authenticates cookie requests but does not authorize them.

`routes/ajax.php` declares `admin-ajax.php` actions. Each route requires a nonce action and validation callbacks for declared input. Private actions also require a capability. The example `wpmvc_theme_status` action is POST-only and checks its nonce and `manage_options` capability.

```php
$router->post('save_preference', [PreferenceController::class, 'store'], [
    'nonce_action' => 'save_preference',
    'capability' => 'edit_posts',
    'args' => [
        'choice' => [
            'required' => true,
            'sanitize' => 'sanitize_key',
            'validate' => static fn ($value): bool => in_array($value, ['a', 'b'], true),
        ],
    ],
]);
```

Generate the form nonce with `wp_create_nonce('save_preference')` and submit it as `_ajax_nonce`. AJAX routes return WordPress JSON success/error responses. `routes/admin.php` registers an example Appearance submenu, checks its capability again before rendering, and uses the same view factory.

## Theme, assets, and accessibility

`theme.json` defines neutral editor tokens and layout widths. `Supports`, `MenuRegistrar`, `Sidebars`, and `ImageSizes` read `config/theme.php`. Vite builds app, admin, and editor entries. `app/Support/Vite.php` reads the manifest at most once per request and `Assets` enqueues hashed files through WordPress APIs. The base JavaScript progressively enhances navigation; essential content and search render without JavaScript.

The layout includes `wp_head()`, `wp_body_open()`, `wp_footer()`, semantic landmarks, a skip link, and visible focus styles. Search forms have labels. CSS uses logical properties for RTL-friendly layout. All user-facing text uses the `wpmvc-theme` domain. Run `npm run i18n:pot` with WP-CLI i18n installed to generate the PHP string catalog offline. Run `npm run i18n:pot:full` when WordPress's theme JSON translation schema is available to include `theme.json` strings.

Validate request values before use, sanitize where normalization is appropriate, and escape at the destination. Nonces protect state-changing AJAX; capabilities authorize access. The theme includes no credentials, direct SQL, Laravel storage directories, or theme-owned schema migrations.

## Quality commands

```sh
composer validate --no-check-publish
composer lint:php
composer test
composer phpstan
composer phpcs
npm run lint
npm test
npm run build
WP_BASE_URL=http://127.0.0.1:9400 npm run test:wp
WP_BASE_URL=http://127.0.0.1:9400 npm run test:e2e
```

PHPUnit checks the container, provider lifecycle, configuration, views, routes, and architecture boundaries. The `test:wp` script checks an active theme, seeded post/page/category views, search, 404, built assets, REST authorization, and anonymous AJAX rejection against a running WordPress site. `tests/Browser` has Playwright tests for layout, seeded content, no-JavaScript rendering, and mobile keyboard navigation. Set `WP_BASE_URL`; if the Playwright browser download is unavailable, set `PLAYWRIGHT_CHROME_PATH` to an installed Chrome executable. These live checks need a WordPress installation with a database. The WPCS ruleset includes Core, Docs, and Extra; narrow exceptions support PSR-4 namespaced class files, typed camelCase APIs, and PHP short arrays while retaining security rules.

## Continuous integration

GitHub Actions runs on pushes, pull requests, and manual dispatch. PHP 8.2 and 8.4 jobs install from `composer.lock` and run Composer validation, syntax checks, PHPUnit, PHPStan, and PHPCS. A Node 22 job installs from `package-lock.json`, runs JavaScript/CSS lint, and builds assets. WordPress 6.6 and the current release each get an isolated MySQL-backed site; CI activates the theme, seeds a post, page, and category, runs the HTTP smoke test, then runs Playwright in Chromium. All jobs must pass. Failed browser runs upload Playwright results as workflow artifacts. No deployment or repository secrets are required.

To reproduce the live suite locally, install dependencies and build assets, then provide an empty MySQL database named `wordpress` with user/password `wordpress` and run:

```sh
export WP_ROOT="$(mktemp -d)"
export WP_BASE_URL=http://127.0.0.1:9400
bash scripts/ci/setup-wordpress.sh
wp server --path="$WP_ROOT" --host=127.0.0.1 --port=9400
```

In another shell, run `npm run test:wp` and `npm run test:e2e` with the same `WP_BASE_URL`. `WP_VERSION` selects the WordPress release (default `6.6`). `WP_DB_HOST`, `WP_DB_NAME`, `WP_DB_USER`, and `WP_DB_PASSWORD` override the database defaults. The setup script is for a fresh, disposable WordPress directory and database.

## References

- [WordPress Theme Handbook](https://developer.wordpress.org/themes/)
- [Classic template hierarchy](https://developer.wordpress.org/themes/classic-themes/basics/template-hierarchy/)
- [WordPress REST endpoints](https://developer.wordpress.org/rest-api/extending-the-rest-api/adding-custom-endpoints/)
- [Laravel 13 service providers](https://laravel.com/docs/13.x/providers)

WordPress APIs take precedence when the two ecosystems differ.
