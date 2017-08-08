<?php

namespace Drupal\commerce_installments\Plugin\Commerce\CheckoutPane;

use Drupal\commerce_checkout\Plugin\Commerce\CheckoutFlow\CheckoutFlowInterface;
use Drupal\commerce_checkout\Plugin\Commerce\CheckoutPane\CheckoutPaneBase;
use Drupal\commerce_checkout\Plugin\Commerce\CheckoutPane\CheckoutPaneInterface;
use Drupal\commerce_installments\Plugin\InstallmentPlanManager;
use Drupal\commerce_price\NumberFormatterFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides an installment plan selection pane.
 *
 * @CommerceCheckoutPane(
 *   id = "installment_selection",
 *   label = @Translation("Installment Selection"),
 *   default_step = "installments",
 * )
 */
class InstallmentSelection extends CheckoutPaneBase implements CheckoutPaneInterface {

  /** @var \Drupal\commerce_installments\Entity\InstallmentInterface */
  protected $installmentStorage;

  /** @var \Drupal\commerce_installments\Entity\InstallmentPlanInterface */
  protected $installmentPlanStorage;

  /** @var \Drupal\Core\Entity\EntityStorageInterface $currencyStorage */
  protected $currencyStorage;

  /** @var \Drupal\commerce_installments\Plugin\InstallmentPlanManager $installmentPlanManager */
  protected $installmentPlanManager;

  /** @var \CommerceGuys\Intl\Formatter\NumberFormatterInterface $numberFormatter */
  protected $numberFormatter;

  /**
   * Constructs a new CheckoutPaneBase object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\commerce_checkout\Plugin\Commerce\CheckoutFlow\CheckoutFlowInterface $checkout_flow
   *   The parent checkout flow.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\commerce_installments\Plugin\InstallmentPlanManager $installment_plan_manager
   *   The installment plan manager.
   * @param \Drupal\commerce_price\NumberFormatterFactoryInterface $numberFormatter
   *   The number formatter.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, CheckoutFlowInterface $checkout_flow, EntityTypeManagerInterface $entity_type_manager, InstallmentPlanManager $installment_plan_manager, NumberFormatterFactoryInterface $numberFormatter) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $checkout_flow, $entity_type_manager);

    $this->installmentStorage = $this->entityTypeManager->getStorage('installment');
    $this->installmentPlanStorage = $this->entityTypeManager->getStorage('installment_plan');
    $this->currencyStorage = $this->entityTypeManager->getStorage('commerce_currency');
    $this->installmentPlanManager = $installment_plan_manager;
    $this->numberFormatter = $numberFormatter->createInstance();
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition, CheckoutFlowInterface $checkout_flow = NULL) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $checkout_flow,
      $container->get('entity_type.manager'),
      $container->get('plugin.manager.installment_plan'),
      $container->get('commerce_price.number_formatter_factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'installment_plan' => 'monthly',
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationSummary() {
    $plan = $this->installmentPlanManager->getDefinition($this->getConfiguration()['installment_plan']);

    return $this->t('Installment plan: %plan', ['%plan' => $plan['label']]);
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);

    $plans = $this->installmentPlanManager->getDefinitions();
    $keys = array_column($plans, 'id');
    $labels = array_column($plans, 'label');
    $plans = array_combine($keys, $labels);

    $form['installment_plan'] = [
      '#type' => 'select',
      '#title' => $this->t('Installment plan'),
      '#default_value' => $this->getConfiguration()['installment_plan'],
      '#options' => $plans,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function buildPaneForm(array $pane_form, FormStateInterface $form_state, array &$complete_form) {
    /** @var \Drupal\commerce_installments\Plugin\Commerce\InstallmentPlan\InstallmentPlanInterface $plan */
    $plan = $this->installmentPlanManager->createInstance($this->getConfiguration()['installment_plan'], $this->getConfiguration());

    $pane_form['number_payments'] = [
      '#type' => 'select',
      '#title' => $this->t('Number of %plan', ['%plan' => $plan->getLabel()]),
      '#options' => [0 => $this->t('None')] + $plan->getNumberPayments(),
      '#default_value' => $this->order->getData('commerce_installments_number_payments', 2),
      '#description' => $this->t('This is optional, an installment plan is not required.'),
    ];

    return $pane_form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitPaneForm(array &$pane_form, FormStateInterface $form_state, array &$complete_form) {
    $values = $form_state->getValue($pane_form['#parents']);
    $numberPayments = $values['number_payments'];

    $this->order->setData('commerce_installments_number_payments', $numberPayments);
  }


  /**
   * {@inheritdoc}
   */
  public function buildPaneSummary() {
    $summary = [];

    $numberPayments = $this->order->getData('commerce_installments_number_payments', 2);

    // If there aren't any installment payments, proceed.
    if (empty($numberPayments)) {
      $summary['no_installments'] = [
        '#markup' => $this->t('No installment plan selected.'),
      ];
    }
    else {
      $totalPrice = $this->order->getTotalPrice()->divide($numberPayments);
      $summary['installment_payments'] = [
        '#markup' => $this->t('@number payments:', ['@number' => $numberPayments]),
      ];

      /** @var \Drupal\commerce_installments\Plugin\Commerce\InstallmentPlan\InstallmentPlanInterface $plan */
      $plan = $this->installmentPlanManager->createInstance($this->getConfiguration()['installment_plan'], $this->getConfiguration());
      $dates = $plan->getInstallmentDates($numberPayments);
      $amounts = $plan->getInstallmentAmounts($numberPayments, $this->order->getTotalPrice());
      $rows = [];
        foreach ($dates as $delta => $date) {
          $row = [];
          $row[] = $date->format('m-d-Y');
          $row[] = $this->numberFormatter->formatCurrency($amounts[$delta]->getNumber(), $this->currencyStorage->load($amounts[$delta]->getCurrencyCode()));

          $rows[] = $row;
        }
      $summary['installment_table'] = [
        '#type' => 'table',
        '#rows' => $rows,
        '#header' => [
          $this->t('Date'),
          $this->t('Amount'),
        ],
      ];
    }

    return $summary;
  }

}
