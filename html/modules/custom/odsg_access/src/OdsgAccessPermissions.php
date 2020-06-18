<?php

namespace Drupal\odsg_access;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\node\Entity\NodeType;

/**
 * Provides view permissions for nodes of different types.
 */
class OdsgAccessPermissions {
  use StringTranslationTrait;

  /**
   * Returns an array of view permissions per node types.
   *
   * @return array
   *   An associative array of permission names and descriptions.
   */
  public function buildPermissions() {
    $permissions = [];

    // Generate view permissions for all node types.
    foreach (NodeType::loadMultiple() as $type) {
      $type_id = $type->id();

      $permissions["view published {$type_id} content"] = [
        'title' => $this->t('%type_name: View published content', [
          '%type_name' => $type->label(),
        ]),
      ];
    }
    return $permissions;
  }

}
