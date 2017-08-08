<?php

namespace Drupal\commerce_installments\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\commerce_installments\Plugin\InstallmentPlanManager;

/**
 * Class InstallmentPlanSettings.
 */
class InstallmentPlanSettings extends FormBase {

  /**
   * Drupal\Core\Entity\EntityTypeManager definition.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  protected $entityTypeManager;
  /**
   * Drupal\commerce_installments\Plugin\InstallmentPlanManager definition.
   *
   * @var \Drupal\commerce_installments\Plugin\InstallmentPlanManager
   */
  protected $manager;

  /**
   * Drupal\commerce_installments\Plugin\Commerce\InstallmentPlan\InstallmentPlanInterface definition.
   *
   * @var \Drupal\commerce_installments\Plugin\Commerce\InstallmentPlan\InstallmentPlanInterface
   */
  protected $plugin;

  /**
   * InstallmentPlanSettings constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManager $entity_type_manager
   * @param \Drupal\commerce_installments\Plugin\InstallmentPlanManager $manager
   */
  public function __construct(
    EntityTypeManager $entity_type_manager,
    InstallmentPlanManager $manager
  ) {
    $this->entityTypeManager = $entity_type_manager;
    $this->manager = $manager;

    $pluginId = $this->getRouteMatch()->getParameter('installment_plan_plugin');
    $this->plugin = $this->manager->createInstance($pluginId);
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('plugin.manager.installment_plan')
    );
  }


  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'installment_plan_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = $this->plugin->buildConfigurationForm($form, $form_state);

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $this->plugin->buildConfigurationForm($form, $form_state);
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->plugin->submitConfigurationForm($form, $form_state);
  }

}
