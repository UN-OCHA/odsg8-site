<?php

namespace Drupal\odsg_migrate\Plugin\migrate\source;

use Drupal\migrate_drupal\Plugin\migrate\source\DrupalSqlBase;

/**
 * Drupal 7 menu source from database.
 *
 * @MigrateSource(
 *   id = "odsg_menu",
 *   source_module = "menu",
 *   source_provider = "menu"
 * )
 */
class OdsgMenu extends DrupalSqlBase {

  /**
   * {@inheritdoc}
   */
  public function query() {
    return $this
      ->select('menu_custom', 'm')
      ->fields('m')
      ->condition('m.menu_name', [
        'main-menu',
        'menu-about-us---menu',
        'menu-funding---menu',
        'menu-meeting-events---menu',
        'menu-resources-menu',
      ], 'IN');
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    return [
      'menu_name' => $this->t('The menu name. Primary key.'),
      'title' => $this->t('The human-readable name of the menu.'),
      'description' => $this->t('A description of the menu'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getIds() {
    return [
      'menu_name' => [
        'type' => 'string',
        'alias' => 'm',
      ],
    ];
  }

}
