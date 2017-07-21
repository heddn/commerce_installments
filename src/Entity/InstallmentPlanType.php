<?php

namespace Drupal\commerce_installments\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;

/**
 * Defines the Installment Plan type entity.
 *
 * @ConfigEntityType(
 *   id = "commerce_installment_plan_type",
 *   label = @Translation("Installment Plan type"),
 *   label_collection = @Translation("Installment Plan types"),
 *   label_singular = @Translation("installment plan type"),
 *   label_plural = @Translation("installment plan types"),
 *   label_count = @PluralTranslation(
 *     singular = "@count installment plan type",
 *     plural = "@count installment plan types",
 *   ),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\commerce_installments\InstallmentPlanTypeListBuilder",
 *     "form" = {
 *       "add" = "Drupal\commerce_installments\Form\InstallmentPlanTypeForm",
 *       "edit" = "Drupal\commerce_installments\Form\InstallmentPlanTypeForm",
 *       "delete" = "Drupal\commerce_installments\Form\InstallmentPlanTypeDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\commerce_installments\InstallmentPlanTypeHtmlRouteProvider",
 *     },
 *   },
 *   config_prefix = "commerce_installment_plan_type",
 *   admin_permission = "administer site configuration",
 *   bundle_of = "commerce_installment_plan",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "/admin/commerce/config/commerce_installment_plan_type/{commerce_installment_plan_type}",
 *     "add-form" = "/admin/commerce/config/commerce_installment_plan_type/add",
 *     "edit-form" = "/admin/commerce/config/commerce_installment_plan_type/{commerce_installment_plan_type}/edit",
 *     "delete-form" = "/admin/commerce/config/commerce_installment_plan_type/{commerce_installment_plan_type}/delete",
 *     "collection" = "/admin/commerce/config/commerce_installment_plan_type"
 *   }
 * )
 */
class InstallmentPlanType extends ConfigEntityBundleBase implements InstallmentPlanTypeInterface {

  /**
   * The Installment Plan type ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The Installment Plan type label.
   *
   * @var string
   */
  protected $label;

}
