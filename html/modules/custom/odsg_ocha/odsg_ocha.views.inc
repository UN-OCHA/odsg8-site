<?php

/**
 * @file
 * Views JSON Query module.
 */

/**
 * Implements hook_views_data().
 */
function odsg_ocha_views_data() {
  return [
    'odsg_ocha_feed' => [
      'table' => [
        'group' => t('OCHA feed'),
        'base' => [
          'title' => t('OCHA Feed'),
          'help' => t('OCHA document feed for ODSG'),
          'query_id' => 'odsg_ocha_feed',
        ],
      ],
      // Fields.
      'id' => [
        'title' => t('ID'),
        'help' => t('Document id on https://www.unocha.org'),
        'field' => [
          'id' => 'numeric',
        ],
      ],
      'link' => [
        'title' => t('Document URL'),
        'help' => t('Link to the document on https://www.unocha.org'),
        'field' => [
          'id' => 'standard',
        ],
      ],
      'title' => [
        'title' => t('Title'),
        'help' => t('Document title'),
        'field' => [
          'id' => 'standard',
        ],
      ],
      'updated' => [
        'title' => t('Updated'),
        'help' => t('Document last update date'),
        'field' => [
          'id' => 'date',
        ],
      ],
      'file' => [
        'title' => t('File URL'),
        'help' => t('URL of the attached document'),
        'field' => [
          'id' => 'standard',
        ],
      ],
      'image' => [
        'title' => t('Image URL'),
        'help' => t("URL of the attached document's cover preview or article image"),
        'field' => [
          'id' => 'standard',
        ],
      ],
    ],
    'odsg_ocha_funding' => [
      'table' => [
        'group' => t('OCHA funding'),
        'base' => [
          'title' => t('OCHA Funding'),
          'help' => t('OCHA funding by donors'),
          'query_id' => 'odsg_ocha_funding',
        ],
      ],
      // Fields.
      'rank' => [
        'title' => t('Rank'),
        'help' => t('Donor rank'),
        'field' => [
          'id' => 'numeric',
        ],
        'sort' => [
          'id' => 'numeric',
        ],
      ],
      'donor' => [
        'title' => t('Donor'),
        'help' => t('Donor name'),
        'field' => [
          'id' => 'standard',
        ],
      ],
      'code' => [
        'title' => t('Code'),
        'help' => t('Identifier: iso2 code for countries or some string otherwise'),
        'field' => [
          'id' => 'standard',
        ],
      ],
      'earmarked' => [
        'title' => t('Earmarked'),
        'help' => t('Earmarked donations in USD'),
        'field' => [
          'id' => 'numeric',
        ],
      ],
      'unearmarked' => [
        'title' => t('UnEarmarked'),
        'help' => t('UnEarmarked donations in USD'),
        'field' => [
          'id' => 'numeric',
        ],
      ],
      'total' => [
        'title' => t('Total'),
        'help' => t('Total donations in USD'),
        'field' => [
          'id' => 'numeric',
        ],
      ],
      'year' => [
        'title' => t('Year'),
        'help' => t("Year"),
        'field' => [
          'id' => 'numeric',
        ],
        'filter' => [
          'id' => 'odsg_funding_year_filter',
        ],
      ],
    ],
  ];
}
