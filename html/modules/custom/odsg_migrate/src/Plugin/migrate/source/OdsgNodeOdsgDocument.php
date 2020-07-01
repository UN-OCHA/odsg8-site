<?php

namespace Drupal\odsg_migrate\Plugin\migrate\source;

use Drupal\Core\Database\Query\Condition;
use Drupal\migrate\Plugin\migrate\source\SqlBase;
use Drupal\migrate\Row;
use Symfony\Component\HttpFoundation\Request;

/**
 * Drupal 7 ODSG Document nodes source from database.
 *
 * @MigrateSource(
 *   id = "odsg_node_odsg_document",
 *   source_provider = "node"
 * )
 */
class OdsgNodeOdsgDocument extends SqlBase {

  /**
   * List of duplicate nodes.
   *
   * Duplicate nodes. The key is the duplicate and the value is the node
   * to keep. We selected to keep the older node in most cases as the
   * attachment URI was often less ambiguous and sometimes newer nodes had
   * typos in their title or mutiple spaces in the file name.
   * All those duplicates have been manually verified.
   *
   * @var array
   */
  public static $duplicates = [
    3133 => 3131,
    3149 => 6248,
    3891 => 3313,
    3892 => 3308,
    4254 => 4253,
    4267 => 4262,
    4606 => 4601,
    5266 => 6168,
    5141 => 5136,
    5146 => 5136,
    5151 => 5136,
    5311 => 5291,
    5381 => 5376,
    5401 => 5396,
    6073 => 5864,
    6078 => 3878,
    6088 => 3113,
    6093 => 4721,
    6098 => 5853,
    6103 => 5858,
    6113 => 3187,
    6118 => 3280,
    6123 => 3083,
    6128 => 3111,
    6133 => 3279,
    6138 => 3281,
    6143 => 3110,
    6148 => 3271,
    6153 => 4256,
    6158 => 4257,
    6163 => 5026,
    6173 => 5759,
    6178 => 5749,
    6183 => 5754,
    6188 => 5326,
    6193 => 5216,
    6198 => 5006,
    6203 => 4496,
    6208 => 4481,
    6213 => 4249,
    6218 => 4262,
    6223 => 4224,
    6233 => 3270,
    6238 => 3299,
    6243 => 3301,
    6253 => 3282,
    6258 => 3284,
    6263 => 3283,
    6268 => 3285,
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

    // Only ODSG documents.
    $query->condition('n.type', 'odsg_document');

    // Skip duplicate nodes.
    $query->condition('n.nid', array_keys(static::$duplicates), 'NOT IN');

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
    $fid = $query->execute()->fetchField();

    // Attempt to retrieve the file id from the legacy file url field.
    if (empty($fid)) {
      $query = $this->select('field_revision_field_document_url', 'f')
        ->fields('f', ['field_document_url_value'])
        ->condition('f.entity_id', $nid)
        ->condition('f.entity_type', 'node')
        ->condition('f.revision_id', $vid);
      $this->setFileIdFromLegacyUrl($row, $query->execute()->fetchField());
    }
    else {
      $row->setSourceProperty('document', $fid);
    }

    // Description.
    // NOTE: "desciption" is not a typo in this script but from when the field
    // was created in D7.
    $query = $this->select('field_revision_field_document_desciption', 'f')
      ->fields('f', ['field_document_desciption_value'])
      ->condition('f.entity_id', $nid)
      ->condition('f.entity_type', 'node')
      ->condition('f.revision_id', $vid);
    $description = $query->execute()->fetchField();

    // Attempt to retrieve the description from the legacy document title field.
    if (empty($description)) {
      $query = $this->select('field_revision_field_document_title', 'f')
        ->fields('f', ['field_document_title_value'])
        ->condition('f.entity_id', $nid)
        ->condition('f.entity_type', 'node')
        ->condition('f.revision_id', $vid);
      $description = $query->execute()->fetchField();
    }
    $row->setSourceProperty('description', $description);

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
   * Attempt to retrieve the file id from the legacy document url field.
   *
   * @param \Drupal\migrate\Row $row
   *   Row being processed.
   * @param string $url
   *   Legacy hardcoded file URL.
   */
  public function setFileIdFromLegacyUrl(Row $row, $url) {
    $nid = $row->getSourceProperty('nid');

    // Map node id to file id for urls that cannot be matched against the
    // patterns below.
    static $map = [
      3149 => 303,
      3211 => 2713,
      3282 => 278,
      3283 => 288,
      3284 => 293,
      3285 => 313,
      4262 => 1173,
      4481 => 1548,
      4721 => 2893,
      5749 => 2288,
      5754 => 2318,
      5759 => 2313,
    ];

    if (isset($map[$nid])) {
      $row->setSourceProperty('document', $map[$nid]);
      return;
    }

    if (empty($url)) {
      return;
    }

    // For external file urls, we store the url in the corresponding field
    // and do not set a document file id.
    if (mb_strpos($url, 'https://') === 0) {
      $row->setSourceProperty('external_file_url', $url);
      return;
    }
    // Handle powperpoint viewer urls (url is the `PresentationId` parameter).
    elseif (mb_strpos($url, '/_layouts/PowerPoint.aspx') > 0) {
      return $this->setFileIdFromLegacyUrl($row, Request::create($url)->query->get('PresentationId'));
    }
    // Handle word viewer urls (url is the `id` parameter).
    elseif (mb_strpos($url, '/_layouts/WordViewer.aspx') > 0) {
      return $this->setFileIdFromLegacyUrl($row, Request::create($url)->query->get('id'));
    }

    // Private paths to replace.
    $pattern = '#^((' . implode(')|(', [
      '/system/files/',
      '/sites/dms/',
      '../sites/default/files/private/(Documents/)?',
    ]) . '))#';

    // Decode the url.
    $url = rawurldecode($url);

    // Transform the path to the private://path-to-file form.
    $uri = preg_replace($pattern, 'private://', $url);

    // Sometimes extra spaces where added by mistake.
    $uri = preg_replace('/[ ]{2,}/', ' ', $uri);

    if (empty($uri)) {
      return;
    }

    // The certified statements of accounts files are missing, we preserve the
    // url to them in case we find them somewhere so that they can be updated.
    if (mb_strpos($uri, '/Certified Statements of Accounts/')) {
      $url = file_url_transform_relative(file_create_url($uri));
      $row->setSourceProperty('external_file_url', $url);
      return;
    }

    // Get the id of the file matching the url.
    $query = $this->select('file_managed', 'fm')
      ->fields('fm', ['fid'])
      ->condition('fm.uri', $uri);

    // Search only for the files matching the URI that are not used by other
    // documents.
    $query->leftJoin('file_usage', 'fu', 'fu.fid = fm.fid');
    $or = new Condition('OR');
    $or->isNull('fu.count');
    $or->condition('fu.count', 1, '<>');
    $query->condition($or);

    $fid = $query->execute()->fetchField();

    if (empty($fid)) {
      return;
    }

    $row->setSourceProperty('document', $fid);
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
