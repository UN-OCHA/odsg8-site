<?php

namespace Drupal\odsg_ocha\Form;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\State\StateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Settings form for the OCHA feeds.
 */
class OchaFeedsSettingsForm extends FormBase {

  /**
   * The state service.
   *
   * @var \Drupal\Core\State\StateInterface
   */
  protected $state;

  /**
   * Constructor.
   *
   * @param \Drupal\Core\State\StateInterface $state
   *   The state service.
   */
  public function __construct(StateInterface $state) {
    $this->state = $state;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('state')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['ocha_core_publications'] = [
      '#type' => 'textfield',
      '#title' => $this->t('OCHA Core Publications'),
      '#description' => $this->t('Enter a ReliefWeb API URL (ex: https://api.reliefweb.int/v1/reports?appname=odsg.unocha.org&query[value]=source:OCHA).'),
      '#default_value' => $this->state->get('odsg_ocha.feeds.ocha_core_publications', ''),
      '#maxlength' => 4096,
      '#size' => 300,
    ];

    $form['ocha_annual_reports'] = [
      '#type' => 'textfield',
      '#title' => $this->t('OCHA Annual Reports'),
      '#description' => $this->t('Enter a ReliefWeb API URL (ex: https://api.reliefweb.int/v1/reports?appname=odsg.unocha.org&query[value]=source:OCHA).'),
      '#default_value' => $this->state->get('odsg_ocha.feeds.ocha_annual_reports', ''),
      '#maxlength' => 4096,
      '#size' => 300,
    ];

    $form['ocha_global_humanitarian_overview'] = [
      '#type' => 'textfield',
      '#title' => $this->t('OCHA Global Humanitarian Overview'),
      '#description' => $this->t('Enter a ReliefWeb API URL (ex: https://api.reliefweb.int/v1/reports?appname=odsg.unocha.org&query[value]=source:OCHA).'),
      '#default_value' => $this->state->get('odsg_ocha.feeds.ocha_global_humanitarian_overview', ''),
      '#maxlength' => 4096,
      '#size' => 300,
    ];

    $form['ocha_plan_and_budget'] = [
      '#type' => 'textfield',
      '#title' => $this->t('OCHA Plan and Budget'),
      '#description' => $this->t('Enter a ReliefWeb API URL (ex: https://api.reliefweb.int/v1/reports?appname=odsg.unocha.org&query[value]=source:OCHA).'),
      '#default_value' => $this->state->get('odsg_ocha.feeds.ocha_plan_and_budget', ''),
      '#maxlength' => 4096,
      '#size' => 300,
    ];

    $form['ocha_strategic_framework'] = [
      '#type' => 'textfield',
      '#title' => $this->t('OOCHA Strategic Framework'),
      '#description' => $this->t('Enter a ReliefWeb API URL (ex: https://api.reliefweb.int/v1/reports?appname=odsg.unocha.org&query[value]=source:OCHA).'),
      '#default_value' => $this->state->get('odsg_ocha.feeds.ocha_strategic_framework', ''),
      '#maxlength' => 4096,
      '#size' => 300,
    ];

    $form['ocha_news_and_updates'] = [
      '#type' => 'textfield',
      '#title' => $this->t('OOCHA News and Updates'),
      '#description' => $this->t('Enter the URL of the feed on www.unocha.org for the OCHA News and Stories. Currently disabled.'),
      '#default_value' => $this->state->get('odsg_ocha.feeds.ocha_news_and_updates', ''),
      '#disabled' => TRUE,
      '#maxlength' => 4096,
      '#size' => 300,
    ];

    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save settings'),
      '#button_type' => 'primary',
    ];
    // By default, render the form using system-config-form.html.twig.
    $form['#theme'] = 'system_config_form';

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $feeds = [
      'ocha_core_publications',
      'ocha_annual_reports',
      'ocha_global_humanitarian_overview',
      'ocha_plan_and_budget',
      'ocha_strategic_framework',
      'ocha_news_and_updates',
    ];

    foreach ($feeds as $feed) {
      $this->state->set('odsg_ocha.feeds.' . $feed, $form_state->getValue($feed) ?? '');
    }

    // Clear the cache of the view that use those feeds.
    Cache::invalidateTags(['config:views.view.ocha_feeds']);
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'odsg_ocha_ocha_feeds_settings';
  }

}
