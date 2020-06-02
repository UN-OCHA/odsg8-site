# odsg8-site

OCHA Donor Support Group (ODSG) site - Drupal 8 version

## Notes

**Composer memory issue**

Run `COMPOSER_MEMORY_LIMIT=-1 composer install`

**Fist installation with drush or drupal console**

Run `drush site:install minimal --site-name="OCHA Donor Support Group" --site-mail="admin@odsg.test"`

This will add the database settings and hash_salt to the `html/sites/default/settings.php` and should probably be removed and added to `/srv/www/shared/settings/settings.local.php` instead.

**Import configuration after installation**

1. Change the site uid: `drush -y config-set "system.site" uuid "UUID_FROM_CONFIG_SYNC_SYSTEM_SITE_YML"`
2. Run `drush -y config:import`

**Reload migration configurations**

Run `drush odsg-mr`
