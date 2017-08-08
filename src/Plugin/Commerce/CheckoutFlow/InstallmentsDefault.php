<?php

namespace Drupal\commerce_installments\Plugin\Commerce\CheckoutFlow;

use Drupal\commerce_checkout\Plugin\Commerce\CheckoutFlow\CheckoutFlowWithPanesBase;

/**
 * Provides a default installments checkout flow.
 *
 * @CommerceCheckoutFlow(
 *   id = "installments_default",
 *   label = "Installments - Default",
 * )
 */
class InstallmentsDefault extends CheckoutFlowWithPanesBase {

  /**
   * {@inheritdoc}
   */
  public function getSteps() {
    // Note that previous_label and next_label are not the labels
    // shown on the step itself. Instead, they are the labels shown
    // when going back to the step, or proceeding to the step.
    return [
      'login' => [
        'label' => $this->t('Login'),
        'previous_label' => $this->t('Go back'),
        'has_sidebar' => FALSE,
      ],
      'order_information' => [
        'label' => $this->t('Order information'),
        'has_sidebar' => TRUE,
        'previous_label' => $this->t('Go back'),
      ],
      'installments' => [
        'label' => $this->t('Installments'),
        'next_label' => $this->t('Continue to Installment Plan'),
        'previous_label' => $this->t('Go back'),
        'has_sidebar' => FALSE,
      ],
      'review' => [
        'label' => $this->t('Review'),
        'next_label' => $this->t('Continue to Review'),
        'previous_label' => $this->t('Go back'),
        'has_sidebar' => TRUE,
      ],
    ] + parent::getSteps();
  }

}
