<?php

namespace Drupal\commerce_installments\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;

/**
 * Defines the Installment type entity.
 *
 * @ConfigEntityType(
 *   id = "commerce_installment_type",
 *   label = @Translation("Installment type"),
 *   label_collection = @Translation("Installment types"),
 *   label_singular = @Translation("installment type"),
 *   label_plural = @Translation("installment types"),
 *   label_count = @PluralTranslation(
 *     singular = "@count installment type",
 *     plural = "@count installment types",
 *   ),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\commerce_installments\InstallmentTypeListBuilder",
 *     "form" = {
 *       "add" = "Drupal\commerce_installments\Form\InstallmentTypeForm",
 *       "edit" = "Drupal\commerce_installments\Form\InstallmentTypeForm",
 *       "delete" = "Drupal\commerce_installments\Form\InstallmentTypeDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\commerce_installments\InstallmentTypeHtmlRouteProvider",
 *     },
 *   },
 *   config_prefix = "commerce_installment_type",
 *   admin_permission = "administer site configuration",
 *   bundle_of = "commerce_installment",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "/admin/commerce/config/commerce_installment_type/{commerce_installment_type}",
 *     "add-form" = "/admin/commerce/config/commerce_installment_type/add",
 *     "edit-form" = "/admin/commerce/config/commerce_installment_type/{commerce_installment_type}/edit",
 *     "delete-form" = "/admin/commerce/config/commerce_installment_type/{commerce_installment_type}/delete",
 *     "collection" = "/admin/commerce/config/commerce_installment_type"
 *   }
 * )
 */
class InstallmentType extends ConfigEntityBundleBase implements InstallmentTypeInterface {

  /**
   * The Installment type ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The Installment type label.
   *
   * @var string
   */
  protected $label;

}
