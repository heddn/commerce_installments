<?php

namespace Drupal\commerce_installments\Routing;

use Drupal\commerce_installments\Plugin\InstallmentPlanManager;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\Route;
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
   * @var \Drupal\commerce_installments\Plugin\InstallmentPlanManager
   */
  protected $manager;

  /**
   * Constructs a RouteSubscriber object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity manager.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager, InstallmentPlanManager $manager) {
    $this->entityTypeManager = $entityTypeManager;
    $this->manager = $manager;
  }

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {
    $definition = $this->entityTypeManager->getDefinition('installment_plan');
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

    foreach ($this->manager->getDefinitions() as $definition) {
      if ($setting_route = $this->getSettingRoute($definition)) {
        $collection->add('commerce_installment_plan_settings_' . $definition['id'], $setting_route);
      }
    }
  }


  /**
   * Gets the setting route.
   *
   * @param string
   *   The plugin definition.
   *
   * @return \Symfony\Component\Routing\Route|null
   *   The generated route, if available.
   */
  protected function getSettingRoute(array $definition) {
    $route = new Route('/admin/commerce/config/installment_plan_type/settings/' . $definition['id']);
    $route
      ->setDefaults([
        '_title' => "{$definition['id']} settings",
        '_controller' => InstallmentPlanController::class . '::revisionOverviewController',
      ])
      ->setRequirement('_permission', 'administer installment plan entities');
    $parameters['installment_plan_plugin'] = $definition['id'];
    $route->setOption('parameters', $parameters);
    return $route;
  }

}
