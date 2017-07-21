<?php

namespace Drupal\commerce_installments\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class InstallmentPlanTypeForm.
 */
class InstallmentPlanTypeForm extends EntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $commerce_installment_plan_type = $this->entity;
    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $commerce_installment_plan_type->label(),
      '#description' => $this->t("Label for the Installment Plan type."),
      '#required' => TRUE,
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $commerce_installment_plan_type->id(),
      '#machine_name' => [
        'exists' => '\Drupal\commerce_installments\Entity\InstallmentPlanType::load',
      ],
      '#disabled' => !$commerce_installment_plan_type->isNew(),
    ];

    /* You will need additional form elements for your custom properties. */

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $commerce_installment_plan_type = $this->entity;
    $status = $commerce_installment_plan_type->save();

    switch ($status) {
      case SAVED_NEW:
        drupal_set_message($this->t('Created the %label Installment Plan type.', [
          '%label' => $commerce_installment_plan_type->label(),
        ]));
        break;

      default:
        drupal_set_message($this->t('Saved the %label Installment Plan type.', [
          '%label' => $commerce_installment_plan_type->label(),
        ]));
    }
    $form_state->setRedirectUrl($commerce_installment_plan_type->toUrl('collection'));
  }

}
