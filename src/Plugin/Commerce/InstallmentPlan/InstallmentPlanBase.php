<?php

namespace Drupal\commerce_installments\Plugin\Commerce\InstallmentPlan;

use Drupal\commerce_price\Price;
use Drupal\Component\Datetime\DateTimePlus;
use Drupal\Component\Datetime\TimeInterface;
use Drupal\Component\Plugin\PluginBase;
use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\options\Plugin\Field\FieldType\ListItemBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Base class for Installment Plan plugins.
 */
abstract class InstallmentPlanBase extends PluginBase implements InstallmentPlanInterface, ContainerFactoryPluginInterface {

  use StringTranslationTrait;

  /**
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The entity type bundle info.
   *
   * @var \Drupal\Core\Entity\EntityTypeBundleInfoInterface
   */
  protected $entityTypeBundleInfo;

  /**
   * @var \Drupal\commerce_installments\Entity\InstallmentInterface $installmentStorage
   */
  protected $installmentStorage;

  /**
   * @var \Drupal\commerce_installments\Entity\InstallmentPlanInterface $installmentPlanStorage
   */
  protected $installmentPlanStorage;

  /**
   * @var \Drupal\Component\Datetime\TimeInterface
   */
  protected $time;

  /**
   *    * Constructs a Drupal\Component\Plugin\PluginBase object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The config factory.
   * @param \Drupal\Core\Entity\EntityTypeBundleInfoInterface $entityTypeBundleInfo
   *   The entity type bundle info.
   * @param \Drupal\Component\Datetime\TimeInterface $time
   *   The time service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entityTypeManager, ConfigFactoryInterface $configFactory, EntityTypeBundleInfoInterface $entityTypeBundleInfo, TimeInterface $time) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entityTypeManager;
    $this->installmentStorage = $this->entityTypeManager->getStorage('installment');
    $this->installmentPlanStorage = $this->entityTypeManager->getStorage('installment_plan');
    $this->configFactory = $configFactory;
    $this->entityTypeBundleInfo = $entityTypeBundleInfo;
    $this->time = $time;

    $this->setConfiguration($configuration);
  }


  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('config.factory'),
      $container->get('entity_type.bundle.info'),
      $container->get('datetime.time')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getConfiguration() {
    return $this->configuration;
  }

  /**
   * {@inheritdoc}
   */
  public function setConfiguration(array $configuration) {
    $this->configuration = NestedArray::mergeDeep($this->defaultConfiguration(), $configuration);
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'installment_plan_bundle' => 'installment_plan',
      'installment_bundle' => 'installment',
      'number_payments' => [2 => 2],
      'time' => DateTimePlus::createFromTimestamp($this->time->getRequestTime())->format('H:i:s'),
      'timezone' => $this->configFactory->get('system.date')->get('timezone.default'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $planBundles = [];
    $bundleInfo = $this->entityTypeBundleInfo->getBundleInfo('installment_plan');
    foreach (array_keys($bundleInfo) as $bundle) {
      $planBundles[$bundle] = $bundleInfo['$bundle']['label'];
    }
    $installmentBundles = [];
    $bundleInfo = $this->entityTypeBundleInfo->getBundleInfo('installment');
    foreach (array_keys($bundleInfo) as $bundle) {
      $installmentBundles[$bundle] = $bundleInfo['$bundle']['label'];
    }

    $form['installment_plan_bundle'] = [
      '#type' => 'select',
      '#title' => $this->t('Default Installment Plan Bundle'),
      '#options' => $planBundles,
      '#default_value' => $this->configuration['installment_plan_bundle'],
      '#required' => TRUE,
    ];
    $form['installment_bundle'] = [
      '#type' => 'select',
      '#title' => $this->t('Default Installment Bundle'),
      '#options' => $installmentBundles,
      '#default_value' => $this->configuration['installment_bundle'],
      '#required' => TRUE,
    ];
    $form['number_payments'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Number of payments'),
      '#description' => $this->t('List the number of payments to spread the purchase.'),
      '#default_value' => $this->allowedValuesString($this->getConfiguration()['number_payments']),
      '#element_validate' => [[ListItemBase::class, 'validateAllowedValues']],
      '#rows' => 10,
    ];
    $form['time'] = [
      '#type' => 'datetime',
      '#title' => $this->t('Time'),
      '#date_date_element' => 'none',
      '#date_time_element' => 'time',
      '#description' => $this->t('Time of day to execute purchase.'),
      '#default_value' => $this->getConfiguration()['time'],
      '#required' => TRUE,
    ];
    $form['timezone'] = [
      '#type' => 'select',
      '#title' => $this->t('Default time zone'),
      // Use system timezone if set, but avoid throwing a warning in PHP >=5.4
      '#default_value' => $this->getConfiguration()['timezone'],
      '#options' => system_time_zones(),
      '#description' => $this->t('Timezone in which to execute purchases.'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValue($form['#parents']);
    foreach ($values['number_payments'] as $numberPayments) {
      if ($numberPayments < 2) {
        $form_state->setErrorByName('number_payments', $this->t('There must be at least 2 installment payments.'));
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    if (!$form_state->getErrors()) {
      $values = $form_state->getValue($form['#parents']);
      foreach ($values as $key => $value) {
        $this->configuration[$key] = $values;
      }
    }
  }

  /**
   * @inheritDoc
   */
  public function getNumberPayments() {
    return $this->getConfiguration()['number_payments'];
  }

  /**
   * @inheritDoc
   */
  public function getTime() {
    return $this->getConfiguration()['time'];
  }

  /**
   * @inheritDoc
   */
  public function getTimezone() {
    $this->getConfiguration()['timezone'];
  }

  /**
   * {@inheritdoc}
   */
  public function getLabel() {
    return $this->pluginDefinition['label'];
  }

  /**
   * {@inheritdoc}
   */
  public function getInstallmentPlanBundle() {
    return $this->getConfiguration()['installment_plan_bundle'];
  }

  /**
   * {@inheritdoc}
   */
  public function getInstallmentBundle() {
    return $this->getConfiguration()['installment_bundle'];
  }

  /**
   * @inheritDoc
   */
  public function getInstallmentAmounts($numberPayments, Price $totalPrice) {
    $installmentAmount = $totalPrice->divide($numberPayments);
    $payments = array_fill(0, $numberPayments, $installmentAmount);
    $multipliedAmount = $installmentAmount->multiply($numberPayments);
    $difference = $totalPrice->subtract($multipliedAmount);
    if ($difference->getNumber()){
      array_pop($payments);
      array_push($payments, $installmentAmount->add($difference));
    }

    return $payments;
  }

  /**
   * {@inheritdoc}
   */
  public function calculateDependencies() {
    return [];
  }

  /**
   * Generates a string representation of an array of 'allowed values'.
   *
   * @see \Drupal\options\Plugin\Field\FieldType\ListItemBase::allowedValuesString()
   */
  protected function allowedValuesString($values) {
    $lines = [];
    foreach ($values as $key => $value) {
      $lines[] = "$key|$value";
    }
    return implode("\n", $lines);
  }

}
