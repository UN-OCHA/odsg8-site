<?php

namespace Drupal\odsg_migrate\Plugin\migrate\source;

use Drupal\migrate\Plugin\migrate\source\SqlBase;
use Drupal\migrate\Row;

/**
 * Drupal 7 custom landing page links nodes source from database.
 *
 * @MigrateSource(
 *   id = "odsg_node_custom_link",
 *   source_provider = "node"
 * )
 */
class OdsgNodeCustomLink extends SqlBase {

  /**
   * Duplicate images.
   *
   * List of images that are duplicates keyed by the duplicate and with the
   * image to keep as value.
   *
   * @var array
   */
  public static $imageDuplicates = [
    56 => 50,
    58 => 51,
    61 => 57,
    63 => 50,
  ];

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

    // Only landing page menu links.
    $query->condition('n.type', 'page_landing_');

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

    // Category.
    // Note: "categroy" is because the field name has a typo in the D7 site...
    $query = $this->select('field_revision_field_categroy', 'f')
      ->fields('f', ['field_categroy_tid'])
      ->condition('f.entity_id', $nid)
      ->condition('f.entity_type', 'node')
      ->condition('f.revision_id', $vid);
    $row->setSourceProperty('category', $query->execute()->fetchField());

    // Link URL.
    $query = $this->select('field_revision_field_landing_link', 'f')
      ->fields('f', ['field_landing_link_value'])
      ->condition('f.entity_id', $nid)
      ->condition('f.entity_type', 'node')
      ->condition('f.revision_id', $vid);
    $link = $query->execute()->fetchField();
    // Fix outdated protocol for external links (currenty links to unocha.org).
    $link = str_replace('http://', 'https://', $link);
    // The link module expects internal links in the form 'interna:/path'.
    if (mb_strpos($link, 'https://') !== 0) {
      $link = 'internal:/' . ltrim($link, '/');
    }
    $row->setSourceProperty('link', $link);

    // Attached image.
    $query = $this->select('field_revision_field_landing_image', 'f')
      ->fields('f', ['field_landing_image_fid'])
      ->condition('f.entity_id', $nid)
      ->condition('f.entity_type', 'node')
      ->condition('f.revision_id', $vid);
    $fid = $query->execute()->fetchField();
    // Replace duplicate images.
    $fid = static::$imageDuplicates[$fid] ?? $fid;
    $row->setSourceProperty('image', $fid);

    // Order.
    $query = $this->select('field_revision_field_order', 'f')
      ->fields('f', ['field_order_tid'])
      ->condition('f.entity_id', $nid)
      ->condition('f.entity_type', 'node')
      ->condition('f.revision_id', $vid);
    $row->setSourceProperty('order', $query->execute()->fetchField());

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
