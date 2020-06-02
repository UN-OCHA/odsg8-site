<?php

/**
 * @file
 * Reload migration configurations.
 */

use Drush\Log\LogLevel;

/**
 * Implements hook_drush_command().
 */
function odsg_migrate_drush_command() {
  $items['odsg-migrate-reload'] = [
    'description' => 'Reload ODSG migration configurations.',
    'options' => [],
    'arguments' => [],
    'examples' => [
      'odsg-migrate-reload' => 'Reload all ODSG migration configurations.',
    ],
    'drupal dependencies' => ['odsg_migrate'],
    'aliases' => ['odsg-mr'],
  ];

  return $items;
}

/**
 * Reload ODSG migration configurations.
 *
 * @see odsg_migrate_drush_command()
 */
function drush_odsg_migrate_reload() {
  // Uninstall and reinstall all configuration.
  \Drupal::service('config.manager')->uninstall('module', 'odsg_migrate');
  \Drupal::service('config.installer')->installDefaultConfig('module', 'odsg_migrate');
  drush_log(dt('Config reload complete.'), LogLevel::OK);

  // Rebuild caches.
  drush_invoke('cache-rebuild');
}
