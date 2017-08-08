<?php

namespace Drupal\commerce_installments\Plugin\Commerce\CheckoutPane;

use Drupal\commerce\Response\NeedsRedirectException;
use Drupal\commerce_checkout\Plugin\Commerce\CheckoutFlow\CheckoutFlowInterface;
use Drupal\commerce_installments\Plugin\InstallmentPlanManager;
use Drupal\commerce_payment\Plugin\Commerce\CheckoutPane\PaymentProcess;
use Drupal\commerce_payment\Plugin\Commerce\PaymentGateway\ManualPaymentGatewayInterface;
use Drupal\commerce_payment\Plugin\Commerce\PaymentGateway\OffsitePaymentGatewayInterface;
use Drupal\commerce_payment\Plugin\Commerce\PaymentGateway\OnsitePaymentGatewayInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides the payment process pane.
 *
 * @CommerceCheckoutPane(
 *   id = "commerce_installments_payment_process",
 *   label = @Translation("Installment payment process"),
 *   default_step = "payment",
 *   wrapper_element = "container",
 * )
 */
class InstallmentPaymentProcess extends PaymentProcess {

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
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, CheckoutFlowInterface $checkout_flow, EntityTypeManagerInterface $entity_type_manager, InstallmentPlanManager $installment_plan_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $checkout_flow, $entity_type_manager);
    $this->installmentPlanManager = $installment_plan_manager;
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
      $container->get('plugin.manager.installment_plan')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function isVisible() {
    // This pane can't be used without the InstallmentSelection pane.
    $installment_pane = $this->checkoutFlow->getPane('installment_selection');
    // This pane can't be used without the PaymentInformation pane.
    $payment_info_pane = $this->checkoutFlow->getPane('payment_information');
    return $installment_pane->isVisible() && $installment_pane->getStepId() != '_disabled' && $payment_info_pane->isVisible() && $payment_info_pane->getStepId() != '_disabled';
  }

  /**
   * {@inheritdoc}
   */
  public function buildPaneForm(array $pane_form, FormStateInterface $form_state, array &$complete_form) {
    // The payment gateway is currently always required to be set.
    if ($this->order->get('payment_gateway')->isEmpty()) {
      drupal_set_message($this->t('No payment gateway selected.'), 'error');
      $this->redirectToPreviousStep();
    }

    /** @var \Drupal\commerce_payment\Entity\PaymentGatewayInterface $payment_gateway */
    $payment_gateway = $this->order->payment_gateway->entity;
    $payment_gateway_plugin = $payment_gateway->getPlugin();

    if ($payment_gateway_plugin instanceof OnsitePaymentGatewayInterface) {
      // Retrieve the installment selection pane. Use its configuration.
      $installment_pane = $this->checkoutFlow->getPane('installment_selection');

      /** @var \Drupal\commerce_installments\Plugin\Commerce\InstallmentPlan\InstallmentPlanInterface $planPlugin */
      $planPlugin = $this->installmentPlanManager->createInstance($installment_pane->getConfiguration()['installment_plan'], []);


      $numberPayments = $this->order->getData('commerce_installments_number_payments', 2);
      $planPlugin->buildInstallments($this->order, $numberPayments);

      $this->checkoutFlow->redirectToStep($this->checkoutFlow->getNextStepId($this->getStepId()));
    }
    elseif ($payment_gateway_plugin instanceof OffsitePaymentGatewayInterface) {
      drupal_set_message($this->t('Offsite payment gateway selected.'), 'error');
      $this->redirectToPreviousStep();
    }
    elseif ($payment_gateway_plugin instanceof ManualPaymentGatewayInterface) {
      drupal_set_message($this->t('Manual payment gateway selected.'), 'error');
      $this->redirectToPreviousStep();
    }
    else {
      drupal_set_message($this->t('Something went wrong in setting up your installment plan.'), 'error');
      $this->redirectToPreviousStep();
    }
  }

  /**
   * Builds the URL to the payment information checkout step.
   *
   * @return string
   *   The URL to the payment information checkout step.
   */
  protected function buildPaymentInformationStepUrl() {
    return Url::fromRoute('commerce_checkout.form', [
      'commerce_order' => $this->order->id(),
      'step' => $this->checkoutFlow->getPane('payment_information')->getStepId(),
    ], ['absolute' => TRUE])->toString();
  }

  /**
   * Redirects to a previous checkout step on error.
   *
   * @throws \Drupal\commerce\Response\NeedsRedirectException
   */
  protected function redirectToPreviousStep() {
    throw new NeedsRedirectException($this->buildPaymentInformationStepUrl());
  }

}
