<?php

namespace Drupal\odsg_ocha\Plugin\views\filter;

use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Plugin\views\filter\FilterPluginBase;

/**
 * Simple filter to select a year for the OCHA funding.
 *
 * @ingroup views_filter_handlers
 *
 * @ViewsFilter("odsg_funding_year_filter")
 */
class OdsgFundingYearFilter extends FilterPluginBase {

  /**
   * Disable the possibility to force a single value.
   *
   * @var bool
   */
  protected $alwaysMultiple = TRUE;

  /**
   * Provide simple equality operator.
   *
   * @return array
   *   Array of available operators for the filter.
   */
  public function operatorOptions() {
    return [
      '=' => $this->t('Is equal to'),
    ];
  }

  /**
   * Provide a simple select input for equality.
   */
  protected function valueForm(&$form, FormStateInterface $form_state) {
    $end = date('Y');
    $start = \Drupal::config('odsg_ocha.settings')->get('funding.start') ?? $end - 10;
    $range = range($end, $start, -1);

    $form['value'] = [
      '#type' => 'select',
      '#title' => $this->t('Value'),
      '#options' => array_combine($range, $range),
      '#default_value' => $this->value ?? $end,
    ];

    if ($form_state->get('exposed')) {
      $identifier = $this->options['expose']['identifier'];
      $user_input = $form_state->getUserInput();
      if (!isset($user_input[$identifier])) {
        $user_input[$identifier] = $this->value;
        $form_state->setUserInput($user_input);
      }
    }
  }

}
