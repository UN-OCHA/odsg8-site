<?php

namespace Drupal\odsg_migrate\Plugin\migrate\source;

use Drupal\migrate\Plugin\migrate\source\SqlBase;

/**
 * Drupal 7 vocabularies source from database.
 *
 * @MigrateSource(
 *   id = "odsg_taxonomy_vocabulary",
 *   source_provider = "taxonomy"
 * )
 */
class OdsgTaxonomyVocabulary extends SqlBase {

  /**
   * {@inheritdoc}
   */
  public function query() {
    return $this->select('taxonomy_vocabulary', 'tv')
      ->fields('tv', [
        'vid',
        'name',
        'description',
        'hierarchy',
        'module',
        'weight',
        'machine_name',
      ])
      // Skip the unused Active and Role taxonomy vocabularies.
      ->condition('vid', [6, 7], 'NOT IN');
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    return [
      'vid' => $this->t('The vocabulary ID.'),
      'name' => $this->t('The name of the vocabulary.'),
      'description' => $this->t('The description of the vocabulary.'),
      'help' => $this->t('Help text to display for the vocabulary.'),
      'relations' => $this->t('Whether or not related terms are enabled within the vocabulary. (0 = disabled, 1 = enabled)'),
      'hierarchy' => $this->t('The type of hierarchy allowed within the vocabulary. (0 = disabled, 1 = single, 2 = multiple)'),
      'weight' => $this->t('The weight of the vocabulary in relation to other vocabularies.'),
      'parents' => $this->t("The Drupal term IDs of the term's parents."),
      'node_types' => $this->t('The names of the node types the vocabulary may be used with.'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getIds() {
    return [
      'vid' => [
        'type' => 'integer',
        'alias' => 'tv',
      ],
    ];
  }

}
