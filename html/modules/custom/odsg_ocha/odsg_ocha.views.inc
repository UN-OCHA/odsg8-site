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
        'help' => t('Document id on https://unocha.org'),
        'field' => [
          'id' => 'numeric',
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
      'preview' => [
        'title' => t('Preview URL'),
        'help' => t("URL of the attached document's cover preview"),
        'field' => [
          'id' => 'standard',
        ],
      ],
    ],
  ];
}
