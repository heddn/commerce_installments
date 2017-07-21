<?php

namespace Drupal\commerce_installments;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Link;

/**
 * Defines a class to build a listing of Installment Plan entities.
 *
 * @ingroup commerce_installments
 */
class InstallmentPlanListBuilder extends EntityListBuilder {


  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = $this->t('Installment Plan ID');
    $header['name'] = $this->t('Name');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /* @var $entity \Drupal\commerce_installments\Entity\InstallmentPlan */
    $row['id'] = $entity->id();
    $row['name'] = Link::createFromRoute(
      $entity->label(),
      'entity.commerce_installment_plan.edit_form',
      ['commerce_installment_plan' => $entity->id()]
    );
    return $row + parent::buildRow($entity);
  }

}
