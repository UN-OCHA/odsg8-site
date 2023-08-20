<?php

namespace Drupal\odsg_ocha\Plugin\views\query;

use Drupal\Component\Serialization\Json;
use Drupal\Component\Utility\Html;
use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Plugin\views\query\QueryPluginBase;
use Drupal\views\ResultRow;
use Drupal\views\ViewExecutable;
use GuzzleHttp\Client;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Views query plugin wrapping calls to UN OCHA site feeds.
 *
 * This plugin provides "ensureTable" and "addField" methods as described
 * in the SQL views query plugin for "compatibility" with views handlers that
 * generally assume they are working with a SQL query. This would probably cause
 * errors in some cases but is not an issue in the context of the ODSG's usage
 * of this plugin. See https://www.drupal.org/node/2484565 for more details.
 *
 * @ViewsQuery(
 *   id = "odsg_ocha_feed",
 *   title = @Translation("OCHA feed"),
 *   help = @Translation("Query OCHA document feed for ODSG.")
 * )
 *
 * @see https://www.drupal.org/node/2484565
 */
class OdsgOchaFeed extends QueryPluginBase {

  /**
   * GuzzleHttp\Client definition.
   *
   * @var GuzzleHttp\Client
   */
  protected $httpClient;

  /**
   * Constructs a PluginBase object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \GuzzleHttp\Client $http_client
   *   The http_client.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, Client $http_client) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->httpClient = $http_client;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('http_client')
    );
  }

  /**
   * Ensure a table exists for the query.
   *
   * This is for "compatibility" with views handlers that generally assume
   * they are working with a SQL query. This would probably cause errors in
   * some cases but is not an issue in the context of the ODSG's usage of
   * this plugin.
   *
   * @return string
   *   An empty string.
   *
   * @see https://www.drupal.org/node/2484565
   */
  public function ensureTable($table, $relationship = NULL) {
    return '';
  }

  /**
   * Add a field to the query.
   *
   * This is for "compatibility" with views handlers that generally assume
   * they are working with a SQL query. This would probably cause errors in
   * some cases but is not an issue in the context of the ODSG's usage of
   * this plugin.
   *
   * @return string
   *   The name that this field can be referred to as.
   *
   * @see https://www.drupal.org/node/2484565
   */
  public function addField($table, $field, $alias = '', $params = []) {
    return $field;
  }

  /**
   * {@inheritdoc}
   */
  protected function defineOptions() {
    $options = parent::defineOptions();
    // Add a view query setting to select which OCHA feed to use.
    $options['feed'] = [
      'default' => NULL,
    ];
    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    parent::buildOptionsForm($form, $form_state);

    // Retrieve the list of feeds fromt the configuration.
    $feeds = \Drupal::config('odsg_ocha.settings')->get('feeds') ?? [];

    $options = ['' => $this->t('- Select a feed -')];
    foreach ($feeds as $key => $feed) {
      $options[$key] = $feed['label'];
    }

    $form['feed'] = [
      '#type' => 'select',
      '#options' => $options,
      '#title' => $this->t('Feed'),
      '#default_value' => $this->options['feed'] ?? '',
      '#description' => $this->t('Select which OCHA document feed to use for this view.'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function execute(ViewExecutable $view) {
    $feeds = static::getOchaFeedData();

    $view->result = [];

    // Initialize the pager. We only handle offset and limit.
    $view->initPager();
    $limit = $view->pager->getItemsPerPage() ?: 100;
    $offset = $view->pager->current_page * $limit;

    // Get the data from the RW API.
    $total = 0;
    if (!empty($feeds[$this->options['feed']]['url'])) {
      // Request settings.
      $url = $feeds[$this->options['feed']]['url'];
      $method = 'GET';
      $options = [
        'headers' => [
          'Accept' => 'application/json',
        ],
      ];

      // Add the pagination to the query and ensure we have the required fields.
      $url .= (strpos($url, '?') ? '&' : '?') . http_build_query([
        'offset' => $offset,
        'limit' => $limit,
        'fields' => [
          'include' => [
            'file',
            'url_alias',
          ],
        ],
      ]);

      try {
        $response = $this->httpClient->request($method, $url, $options);
        $code = $response->getStatusCode();

        if ($code == 200) {
          $body = $response->getBody()->getContents();
          $data = Json::decode($body);

          $total = $data['totalCount'] ?? 0;

          $index = 0;
          $unocha_url = rtrim(static::getOchaFeedBaseUrl(), '/') . '/';
          foreach ($data['data'] ?? [] as $item) {
            // Convert the RW API data to what was returned by the OCHA feeds.
            if (empty($item['fields'])) {
              continue;
            }
            $fields = $item['fields'];
            $file = !empty($fields['file']) ? reset($fields['file']) : NULL;
            $item = [
              'nid' => $item['id'],
              'title' => $fields['title'],
              'changed' => $fields['date']['created'],
              'path' => '/publications/' . ltrim(parse_url($item['fields']['url_alias'], \PHP_URL_PATH), '/'),
              'uri' => isset($file['preview']['url-small']) ? preg_replace('#^https://[^/]+/#', $unocha_url, $file['preview']['url-small']) : '',
              'field_publication_document' => isset($file['url']) ? preg_replace('#^https://[^/]+/#', $unocha_url, $file['url']) : '',
            ];

            $item = $this->validateDocument($item);
            // Only add the item if valid. The index property is required.
            if (!empty($item)) {
              $item['index'] = $index++;
              $view->result[] = new ResultRow($item);
            }
          }
        }
      }
      catch (\Exception $exception) {
        watchdog_exception('odsg_ocha', $exception);
      }
    }

    // Set the maximum number of results for the pager.
    $view->pager->total_items = $total;
    $view->pager->updatePageInfo();

    // Explicitly set the number of rows and the execution time.
    $view->total_rows = count($view->result);
    $view->execute_time = time() - REQUEST_TIME;
  }

  /**
   * Validate and sanitize the data of document present in the feed.
   *
   * We ignore the `path` field because it's not clear whether it is a valid
   * path on https://www.unocha.org or a path that is supposed to exist on the
   * ODSG site. It's also not used in the D7 version of the ODSG site.
   *
   * @param array $data
   *   Document data.
   *
   * @return array
   *   Sanitized data or empty array if invalid.
   */
  protected function validateDocument(array $data) {
    $data['link'] = static::getOchaFeedBaseUrl() . ltrim($data['path'] ?? '', '/');

    $filters = [
      // Node id on the https://www.unocha.org.
      'nid' => [
        'filter' => FILTER_VALIDATE_INT,
        'options' => ['min_range' => 1],
        'flags' => FILTER_NULL_ON_FAILURE,
      ],
      // HTML encoded title.
      'title' => [
        'filter' => FILTER_CALLBACK,
        'options' => [$this, 'validateTitle'],
      ],
      // Update date in the form 01 jan 2000.
      'changed' => [
        'filter' => FILTER_CALLBACK,
        'options' => [$this, 'validateDate'],
      ],
      // Array of attachment URLs (usually one).
      'field_publication_document' => [
        'filter' => FILTER_CALLBACK,
        'options' => [$this, 'validateUrl'],
      ],
      // URL of the cover preview of the first attachment or article image.
      'uri' => [
        'filter' => FILTER_CALLBACK,
        'options' => [$this, 'validateUrl'],
      ],
      // Link to the document on the OCHA corporate site.
      'link' => [
        'filter' => FILTER_CALLBACK,
        'options' => [$this, 'validateUrl'],
      ],
    ];

    // Filter and sanitize the data.
    $filtered = filter_var_array($data, $filters);

    // Skip if any of the validations failed or a field is missing.
    foreach ($filters as $key => $dummy) {
      // The attachment is not mandatory, all others are.
      if (!isset($filtered[$key]) && $key !== 'field_publication_document') {
        return [];
      }
    }

    // File is normally an array so we extract the first element.
    if (!isset($filtered['field_publication_document'])) {
      $file = '';
    }
    elseif (is_array($filtered['field_publication_document'])) {
      $file = reset($filtered['field_publication_document']);
    }
    else {
      $file = $filtered['field_publication_document'];
    }

    // Friendly renaming of the keys as defined in odsg_ocha.views.inc.
    return [
      'id' => $filtered['nid'],
      'link' => $filtered['link'],
      'title' => $filtered['title'],
      'updated' => $filtered['changed'],
      'file' => $file,
      'image' => $filtered['uri'],
    ];
  }

  /**
   * Validate a title and trim and decode it.
   *
   * @param string $title
   *   Title to validate.
   *
   * @return string|null
   *   Trimmed and decoded title or NULL if the validation failed.
   */
  protected function validateTitle($title) {
    if (!is_string($title)) {
      return NULL;
    }
    // The title is provided escaped already but it's also escaped when
    // displayed in the view so to avoid double encoding, we decode it now.
    $title = trim(Html::decodeEntities($title));
    return empty($title) ? NULL : $title;
  }

  /**
   * Validate a date and convert to timestamp.
   *
   * @param string $date
   *   Date to validate.
   *
   * @return int|null
   *   Timestamp or NULL if the validation failed.
   */
  protected function validateDate($date) {
    if (!is_string($date)) {
      return NULL;
    }
    try {
      $date = new \DateTime($date, new \DateTimeZone('UTC'));
      // 'Views' expects a timestamp for 'date' type fields.
      return $date->getTimestamp();
    }
    catch (\Exception $exception) {
      return NULL;
    }
  }

  /**
   * Validate a URL against the base URL defined in the configuration.
   *
   * @param string $url
   *   URL to validate.
   *
   * @return string|null
   *   URL or NULL if the validation failed.
   */
  protected function validateUrl($url) {
    $base_url = static::getOchaFeedBaseUrl();

    if (!is_string($url) || empty($base_url)) {
      return NULL;
    }

    if (mb_strpos($url, $base_url) !== 0) {
      return NULL;
    }

    return filter_var($url, FILTER_VALIDATE_URL, [
      'flags' => FILTER_NULL_ON_FAILURE,
    ]);
  }

  /**
   * Get the feeds data.
   *
   * @return array
   *   Feeds data.
   */
  public static function getOchaFeedData() {
    static $feeds;
    if (!isset($feeds)) {
      $feeds = \Drupal::config('odsg_ocha.settings')->get('feeds') ?? [];

      // Use the overriden feeds in state if any.
      foreach ($feeds as $feed => $info) {
        $feeds[$feed]['url'] = \Drupal::state()->get('odsg_ocha.feeds.' . $feed, '');
      }
    }
    return $feeds;
  }

  /**
   * Get the OCHA feed base URL.
   *
   * @return string
   *   Base URL.
   */
  public static function getOchaFeedBaseUrl() {
    static $base_url;
    if (!isset($base_url)) {
      $base_url = trim(\Drupal::config('odsg_ocha.settings')->get('base-url') ?? '');
    }
    return $base_url;
  }

}
