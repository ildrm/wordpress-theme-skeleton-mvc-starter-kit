#!/usr/bin/env bash
set -euo pipefail

: "${WP_ROOT:?Set WP_ROOT to a temporary WordPress directory.}"
: "${WP_BASE_URL:?Set WP_BASE_URL to the WordPress test URL.}"

theme_root="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"

wp core download --version="${WP_VERSION:-6.6}" --path="$WP_ROOT" --quiet
wp config create --path="$WP_ROOT" --dbname="${WP_DB_NAME:-wordpress}" --dbuser="${WP_DB_USER:-wordpress}" --dbpass="${WP_DB_PASSWORD:-wordpress}" --dbhost="${WP_DB_HOST:-127.0.0.1:3306}" --quiet
wp core install --path="$WP_ROOT" --url="$WP_BASE_URL" --title='WPMVC CI' --admin_user=ci-admin --admin_password=ci-password --admin_email=ci@example.test --skip-email --quiet

ln -s "$theme_root" "$WP_ROOT/wp-content/themes/wpmvc-theme"
wp theme activate wpmvc-theme --path="$WP_ROOT" --quiet
wp rewrite structure '/%postname%/' --path="$WP_ROOT" --quiet

category_id="$(wp term create category 'CI Category' --slug=ci-category --porcelain --path="${WP_ROOT}" | tail -n 1)"
post_id="$(wp post create --post_type=post --post_status=publish --post_title='CI Post' --post_name=ci-post --post_content='A seeded integration post.' --porcelain --path="${WP_ROOT}" | tail -n 1)"
wp post term set "$post_id" category "$category_id" --by=id --path="$WP_ROOT" --quiet
wp post create --post_type=page --post_status=publish --post_title='CI Page' --post_name=ci-page --post_content='A seeded integration page.' --path="$WP_ROOT" --quiet
