<?php

namespace Drupal\odsg_migrate\Plugin\migrate\source;

use Drupal\Core\Database\Query\Condition;
use Drupal\migrate\Row;
use Drupal\migrate_drupal\Plugin\migrate\source\d7\FieldableEntity;

/**
 * Drupal 7 file entities source from database.
 *
 * @MigrateSource(
 *   id = "odsg_file",
 *   source_provider = "file"
 * )
 */
class OdsgFile extends FieldableEntity {

  /**
   * {@inheritdoc}
   */
  public function query() {
    $query = $this->select('file_managed', 'fm')
      ->fields('fm')
      ->orderBy('fm.fid');

    if (isset($this->configuration['type'])) {
      $query->condition('fm.type', $this->configuration['type']);
    }

    $query->leftJoin('file_usage', 'fu', 'fu.fid = fm.fid');

    // Select only documents or images used by custom link nodes.
    $or = new Condition('OR');
    $or->condition('fm.type', 'document', '=');
    $or->condition('fu.type', 'node', '=');
    $query->condition($or);

    // Exclude the files attached to the duplicate ODSG documents as they will
    // not be migrated.
    $or = new Condition('OR');
    $or->isNull('fu.id');
    $or->condition('fu.id', array_keys(OdsgNodeOdsgDocument::$duplicates), 'NOT IN');
    $query->condition($or);

    // Exclude duplicate images.
    $query->condition('fm.fid', array_keys(OdsgNodeCustomLink::$imageDuplicates), 'NOT IN');

    return $query->distinct();
  }

  /**
   * {@inheritdoc}
   */
  public function prepareRow(Row $row) {
    $fid = $row->getSourceProperty('fid');
    // Get Field API field values.
    foreach (array_keys($this->getFields('file', $row->getSourceProperty('type'))) as $field) {
      $row->setSourceProperty($field, $this->getFieldValues('file', $field, $fid));
    }
    return parent::prepareRow($row);
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    return [
      'fid' => $this->t('File ID'),
      'uid' => $this->t('The {users}.uid who added the file. If set to 0, this file was added by an anonymous user.'),
      'filename' => $this->t('File name'),
      'uri' => $this->t('The URI to access the file'),
      'filemime' => $this->t('File MIME Type'),
      'status' => $this->t('The published status of a file.'),
      'timestamp' => $this->t('The time that the file was added.'),
      'type' => $this->t('The type of this file.'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getIds() {
    return [
      'fid' => [
        'type' => 'integer',
        'alias' => 'fm',
      ],
    ];
  }

}
