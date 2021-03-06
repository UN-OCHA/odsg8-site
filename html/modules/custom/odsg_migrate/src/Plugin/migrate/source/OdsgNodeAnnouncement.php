<?php

namespace Drupal\odsg_migrate\Plugin\migrate\source;

use Drupal\migrate\Plugin\migrate\source\SqlBase;
use Drupal\migrate\Row;

/**
 * Drupal 7 announcement nodes source from database.
 *
 * @MigrateSource(
 *   id = "odsg_node_announcement",
 *   source_provider = "node"
 * )
 */
class OdsgNodeAnnouncement extends SqlBase {

  /**
   * {@inheritdoc}
   */
  public function query() {
    // Select node in its last revision.
    $query = $this->select('node_revision', 'nr');
    $query->innerJoin('node', 'n', 'n.vid = nr.vid');

    $query->fields('n', [
      'nid',
      'uid',
      'vid',
      'type',
      'language',
      'status',
      'created',
      'changed',
    ]);
    $query->fields('nr', [
      'title',
      'log',
      'timestamp',
    ]);
    $query->addField('nr', 'uid', 'revision_uid');

    // Only announcements.
    $query->condition('n.type', 'announcement');

    return $query;
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    return [
      'nid' => $this->t('Node ID'),
      'vid' => $this->t('Revision ID'),
      'uid' => $this->t('Node authored by (uid)'),
      'type' => $this->t('Type'),
      'title' => $this->t('Title'),
      'language' => $this->t('Language (fr, en, ...)'),
      'status' => $this->t('Published'),
      'created' => $this->t('Created timestamp'),
      'changed' => $this->t('Modified timestamp'),
      'revision_uid' => $this->t('Revision authored by (uid)'),
      'log' => $this->t('Revision log'),
      'timestamp' => $this->t('The timestamp the latest revision of this node was created.'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function prepareRow(Row $row) {
    $nid = $row->getSourceProperty('nid');
    $vid = $row->getSourceProperty('vid');

    // Body.
    $query = $this->select('field_revision_body', 'f')
      ->fields('f', ['body_value'])
      ->condition('f.entity_id', $nid)
      ->condition('f.entity_type', 'node')
      ->condition('f.revision_id', $vid);
    $row->setSourceProperty('body', $query->execute()->fetchField());

    return parent::prepareRow($row);
  }

  /**
   * {@inheritdoc}
   */
  public function getIds() {
    return [
      'nid' => [
        'type' => 'integer',
        'alias' => 'n',
      ],
    ];
  }

}
