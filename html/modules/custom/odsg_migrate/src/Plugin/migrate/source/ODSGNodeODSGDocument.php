<?php

namespace Drupal\odsg_migrate\Plugin\migrate\source;

use Drupal\migrate\Plugin\migrate\source\SqlBase;
use Drupal\migrate\Row;

/**
 * Drupal 7 ODSG Document nodes source from database.
 *
 * @MigrateSource(
 *   id = "odsg_node_odsg_document",
 *   source_provider = "node"
 * )
 */
class ODSGNodeODSGDocument extends SqlBase {

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

    // Only ODSG documents.
    $query->condition('n.type', 'odsg_document');

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

    // Attached file.
    $query = $this->select('field_revision_field_document', 'f')
      ->fields('f', ['field_document_fid'])
      ->condition('f.entity_id', $nid)
      ->condition('f.entity_type', 'node')
      ->condition('f.revision_id', $vid);
    $row->setSourceProperty('document', $query->execute()->fetchField());

    // Description.
    // NOTE: "desciption" is not a typo in this script but from when the field
    // was created in D7.
    $query = $this->select('field_revision_field_document_desciption', 'f')
      ->fields('f', ['field_document_desciption_value'])
      ->condition('f.entity_id', $nid)
      ->condition('f.entity_type', 'node')
      ->condition('f.revision_id', $vid);
    $row->setSourceProperty('description', $query->execute()->fetchField());

    // Publication date.
    $query = $this->select('field_revision_field_published_date', 'f')
      ->fields('f', ['field_published_date_value'])
      ->condition('f.entity_id', $nid)
      ->condition('f.entity_type', 'node')
      ->condition('f.revision_id', $vid);
    $date = $query->execute()->fetchField();
    $row->setSourceProperty('publication_date', !empty($date) ? substr($date, 0, 10) : FALSE);

    // Year.
    $query = $this->select('field_revision_field_year', 'f')
      ->fields('f', ['field_year_tid'])
      ->condition('f.entity_id', $nid)
      ->condition('f.entity_type', 'node')
      ->condition('f.revision_id', $vid);
    $row->setSourceProperty('year', $query->execute()->fetchField());

    // Document type.
    $query = $this->select('field_revision_field_document_type_project_repo', 'f')
      ->fields('f', ['field_document_type_project_repo_tid'])
      ->condition('f.entity_id', $nid)
      ->condition('f.entity_type', 'node')
      ->condition('f.revision_id', $vid);
    $row->setSourceProperty('document_type', $query->execute()->fetchField());

    // Document subtype.
    $query = $this->select('field_revision_field_subtype', 'f')
      ->fields('f', ['field_subtype_tid'])
      ->condition('f.entity_id', $nid)
      ->condition('f.entity_type', 'node')
      ->condition('f.revision_id', $vid);
    $row->setSourceProperty('document_subtype', $query->execute()->fetchField());

    // Meeting title.
    $query = $this->select('field_revision_field_meeting_title_country', 'f')
      ->fields('f', ['field_meeting_title_country_tid'])
      ->condition('f.entity_id', $nid)
      ->condition('f.entity_type', 'node')
      ->condition('f.revision_id', $vid);
    $row->setSourceProperty('meeting_title', $query->execute()->fetchField());

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
