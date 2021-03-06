<?php

namespace Drupal\odsg_migrate\Plugin\migrate\source;

use Drupal\migrate\Plugin\migrate\source\SqlBase;
use Drupal\migrate\Row;

/**
 * Drupal 7 page nodes source from database.
 *
 * @MigrateSource(
 *   id = "odsg_node_page",
 *   source_provider = "node"
 * )
 */
class OdsgNodePage extends SqlBase {

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

    // Only pages.
    $query->condition('n.type', 'page');

    // Exclude the 404 page as a public 404 page is created instead.
    $query->condition('n.nid', 5993, '<>');

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
    $body_parts[] = $query->execute()->fetchField();

    // Extra body.
    $query = $this->select('field_revision_field_extra_box', 'f')
      ->fields('f', ['field_extra_box_value'])
      ->condition('f.entity_id', $nid)
      ->condition('f.entity_type', 'node')
      ->condition('f.revision_id', $vid);
    $body_parts[] = $query->execute()->fetchField();

    // Join the body fields together.
    $row->setSourceProperty('body', implode("\n\n", array_filter($body_parts)));

    // Sidebar content.
    $query = $this->select('field_revision_field_right_side', 'f')
      ->fields('f', ['field_right_side_value'])
      ->condition('f.entity_id', $nid)
      ->condition('f.entity_type', 'node')
      ->condition('f.revision_id', $vid);
    $row->setSourceProperty('sidebar', $query->execute()->fetchField());

    // Retrieve the latest path alias for the page and alter it if it's
    // a publication or meeting/event page to match the name fixes of the
    // menus.
    // @see migrate_plus.migration.odsg_menu.yml
    // @see OdsgMenuLink::prepareRow()
    $alias = $this->select('url_alias', 'ua')
      ->fields('ua', ['alias'])
      ->condition('ua.source', 'node/' . $nid)
      ->orderBy('ua.pid', 'DESC')
      ->range(0, 1)
      ->execute()
      ->fetchField();

    if (!empty($alias)) {
      if (mb_strpos($alias, 'meeting-events/') === 0) {
        $alias = preg_replace('#^meeting-events/#', '/meetings-events/', $alias);
      }
      elseif (mb_strpos($alias, 'publication/') === 0) {
        $alias = preg_replace('#^publication/#', '/publications/', $alias);
      }
      else {
        // Empty the alias so that no new alias is created.
        $alias = '';
      }
    }
    $row->setSourceProperty('alias', $alias);

    // Special handling of the node 1 to extract the ODSG title.
    if ($nid == 1) {
      $row->setSourceProperty('title', 'The OCHA Donor Support Group (ODSG)');
      $row->setSourceProperty('body', 'The ODSG acts as a sounding board and a source of advice on policy, management, budgetary and financial matters to OCHA. The group discusses concrete measures that members may take individually or collectively to assist OCHA in better fulfilling its mission and goals on the basis of humanitarian principles.');
    }

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
