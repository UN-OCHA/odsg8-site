<?php

namespace Drupal\odsg_ocha\Plugin\views\filter;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Plugin\views\filter\FilterPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Simple filter to select a year for the OCHA funding.
 *
 * @ingroup views_filter_handlers
 *
 * @ViewsFilter("odsg_funding_year_filter")
 */
final class OdsgFundingYearFilter extends FilterPluginBase {

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Disable the possibility to force a single value.
   *
   * @var bool
   */
  protected $alwaysMultiple = TRUE;

  /**
   * Constructs a PluginBase object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    ConfigFactoryInterface $config_factory,
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('config.factory')
    );
  }

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
    $start = $this->configFactory->get('odsg_ocha.settings')?->get('funding.start') ?? $end - 10;
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
