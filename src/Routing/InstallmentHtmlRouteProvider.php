<?php

namespace Drupal\commerce_installments\Routing;

use Drupal\commerce_installments\Controller\InstallmentEntityController;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\Routing\DefaultHtmlRouteProvider;

/**
 * Provides routes for Installment Plan entities.
 *
 * @see \Drupal\Core\Entity\Routing\AdminHtmlRouteProvider
 * @see \Drupal\Core\Entity\Routing\DefaultHtmlRouteProvider
 */
class InstallmentHtmlRouteProvider extends DefaultHtmlRouteProvider {

  /**
   * {@inheritdoc}
   */
  protected function getAddPageRoute(EntityTypeInterface $entity_type) {
    if ($route = parent::getAddPageRoute($entity_type)) {
      return $route->setDefault('_controller', InstallmentEntityController::class . '::addPage');
    }
  }

}
