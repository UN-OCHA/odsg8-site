<?php

namespace Drupal\odsg_ocha\Plugin\views\query;

use Exception;
use SoapClient;
use Drupal\Component\Utility\Html;
use Drupal\Component\Utility\Unicode;
use Drupal\views\Plugin\views\query\QueryPluginBase;
use Drupal\views\ResultRow;
use Drupal\views\ViewExecutable;

/**
 * Views query plugin wrapping calls to OCHA Contribution Tracking System API.
 *
 * This plugin provides "ensureTable" and "addField" methods as described
 * in the SQL views query plugin for "compatibility" with views handlers that
 * generally assume they are working with a SQL query. This would probably cause
 * errors in some cases but is not an issue in the context of the ODSG's usage
 * of this plugin. See https://www.drupal.org/node/2484565 for more details.
 *
 * @ViewsQuery(
 *   id = "odsg_ocha_funding",
 *   title = @Translation("OCHA funding"),
 *   help = @Translation("Query OCHA funding API for ODSG.")
 * )
 *
 * @see https://www.drupal.org/node/2484565
 */
class OdsgOchaFunding extends QueryPluginBase {

  /**
   * Year condition.
   *
   * @var int
   */
  protected $yearCondition;

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
   * Add a condition to the query.
   *
   * We only accept a single equality condition on the year.
   *
   * This is for "compatibility" with views handlers that generally assume
   * they are working with a SQL query. This would probably cause errors in
   * some cases but is not an issue in the context of the ODSG's usage of
   * this plugin.
   *
   * @see \Drupal\views\Plugin\views\query\Sql::addWhere()
   * @see https://www.drupal.org/node/2484565
   */
  public function addWhere($group, $field, $value = NULL, $operator = NULL) {
    // We only accept 1 condition on the year.
    if ($field === '.year') {
      $this->yearCondition = $value;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function execute(ViewExecutable $view) {
    $view->result = [];

    $url = \Drupal::config('odsg_ocha.settings')->get('funding.url');

    if (!empty($url)) {
      $year = $this->yearCondition ?? date('Y');

      // Query parameters from the ODSG drupal 7 site. Unfortunately there is no
      // explanation about what those values mean but they are mandatory to get
      // the list of donations for OCHA.
      $parameters = [
        'year' => $year,
        'MultiDonorFundsIds' => 330,
        'UNandOtherAgenciesIds' => 330,
        'PrivateDonationsIds' => 44,
        'ExcludeDonorIds' => 44,
        'ProjectGroupID' => 192,
      ];

      // Initialize the pager. We only handle offset and limit.
      $view->initPager();
      $limit = $view->pager->getItemsPerPage();
      $offset = $view->pager->getOffset();

      try {
        $client = new SoapClient($url);

        // Get the donations data.
        $result = $client->GetDonorRankingForOCHAOnlineV2($parameters);

        // Check that the result has the proper structure.
        $valid = is_object($result) &&
                 property_exists($result, 'GetDonorRankingForOCHAOnlineV2Result') &&
                 property_exists($result->GetDonorRankingForOCHAOnlineV2Result, 'Donors') &&
                 property_exists($result->GetDonorRankingForOCHAOnlineV2Result->Donors, 'DonorRankV2') &&
                 is_array($result->GetDonorRankingForOCHAOnlineV2Result->Donors->DonorRankV2);

        if ($valid) {
          $data = $result->GetDonorRankingForOCHAOnlineV2Result->Donors->DonorRankV2;

          $index = 0;
          foreach (array_slice($data, $offset) as $item) {
            $item = $this->validateDonorData((array) $item);
            // Only add the item if valid. The index property is required.
            if (!empty($item)) {
              $item['index'] = $index++;
              $item['year'] = $year;
              $view->result[] = new ResultRow($item);
            }
            // Skip the rest of the documents if we reach the number of items
            // to return.
            if ($limit !== 0 && $index >= $limit) {
              break;
            }
          }
        }
      }
      catch (Exception $exception) {
        watchdog_exception('odsg_ocha', $exception);
      }
    }

    $view->total_rows = count($view->result);
    $view->execute_time = time() - REQUEST_TIME;
  }

  /**
   * Validate and sanitize the donor data received from the OCT API.
   *
   * @param array $data
   *   Donor data.
   *
   * @return array
   *   Sanitized data or empty array if invalid.
   */
  protected function validateDonorData(array $data) {
    $filters = [
      // Donor rank.
      'Rank' => [
        'filter' => FILTER_VALIDATE_INT,
        'options' => ['min_range' => 1],
        'flags' => FILTER_NULL_ON_FAILURE,
      ],
      // Donor name (country in our case).
      'DonorName' => [
        'filter' => FILTER_CALLBACK,
        'options' => [$this, 'validateDonorName'],
      ],
      // Country code (when elligible).
      'CountryCode' => [
        'filter' => FILTER_CALLBACK,
        'options' => [$this, 'validateCountryCode'],
      ],
      // Earmarked donations in USD.
      'Earmarked' => [
        'filter' => FILTER_VALIDATE_INT,
        'options' => ['min_range' => 0],
        'flags' => FILTER_NULL_ON_FAILURE,
      ],
      // UnEarmarked donations in USD.
      'UnEarmarked' => [
        'filter' => FILTER_VALIDATE_INT,
        'options' => ['min_range' => 0],
        'flags' => FILTER_NULL_ON_FAILURE,
      ],
      // Total donations in USD.
      'Total' => [
        'filter' => FILTER_VALIDATE_INT,
        'options' => ['min_range' => 0],
        'flags' => FILTER_NULL_ON_FAILURE,
      ],
    ];

    // Filter and sanitize the data.
    $filtered = filter_var_array($data, $filters);

    // Skip if any of the validations failed or a field is missing.
    foreach ($filters as $key => $dummy) {
      if (!isset($filtered[$key])) {
        return [];
      }
    }

    // Friendly renaming of the keys as defined in odsg_ocha.views.inc.
    return [
      'rank' => $filtered['Rank'],
      'donor' => $filtered['DonorName'],
      'code' => $filtered['CountryCode'],
      'earmarked' => $filtered['Earmarked'],
      'unearmarked' => $filtered['UnEarmarked'],
      'total' => $filtered['Total'],
    ];
  }

  /**
   * Validate and sanitize a donor name.
   *
   * @param string $name
   *   Donor name to validate.
   *
   * @return string|null
   *   Trimmed donor name or NULL if the validation failed.
   */
  protected function validateDonorName($name) {
    if (!is_string($name)) {
      return NULL;
    }
    return trim($name);
  }

  /**
   * Validate a country ISO2 code.
   *
   * Note: the country code may not be one actually but a random string for
   * donors that are not countries. We simply sanitize the string in that case.
   *
   * @param string $code
   *   Country code to validate.
   *
   * @return string|null
   *   Donor "code" or NULL if the validation failed.
   */
  protected function validateCountryCode($code) {
    if (!is_string($code)) {
      return NULL;
    }
    // Sanitize the code.
    return Html::cleanCssIdentifier(Unicode::strtolower(trim($code)));
  }

}
