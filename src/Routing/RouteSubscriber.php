<?php

namespace Drupal\commerce_installments\Routing;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
 * Class RouteSubscriber.
 *
 * Listens to the dynamic route events.
 */
class RouteSubscriber extends RouteSubscriberBase {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a RouteSubscriber object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity manager.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager) {
    $this->entityManager = $entityTypeManager;
  }

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {
    $definition = $this->entityManager->getDefinition('installment_plan');
    $entity_type_id = $definition->id();
    foreach (array_keys($definition->getLinkTemplates()) as $key) {
      $key = str_replace('-', '_', $key);
      if ($route = $collection->get("entity.{$entity_type_id}.$key")) {
        $parameters = $route->getOption('parameters');
        $parameters['commerce_order'] = [
          'type' => 'entity:commerce_order',
        ];
        $route->setOption('parameters', $parameters);
      }
    }
  }

}
