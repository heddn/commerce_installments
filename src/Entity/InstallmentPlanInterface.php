<?php

namespace Drupal\commerce_installments\Entity;

use Drupal\Core\Entity\RevisionLogInterface;
use Drupal\Core\Entity\RevisionableInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Installment Plan entities.
 *
 * @ingroup commerce_installments
 */
interface InstallmentPlanInterface extends RevisionableInterface, RevisionLogInterface, EntityChangedInterface, EntityOwnerInterface {

  // Add get/set methods for your configuration properties here.

  /**
   * Gets the Installment Plan name.
   *
   * @return string
   *   Name of the Installment Plan.
   */
  public function getName();

  /**
   * Sets the Installment Plan name.
   *
   * @param string $name
   *   The Installment Plan name.
   *
   * @return \Drupal\commerce_installments\Entity\InstallmentPlanInterface
   *   The called Installment Plan entity.
   */
  public function setName($name);

  /**
   * Gets the Installment Plan creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Installment Plan.
   */
  public function getCreatedTime();

  /**
   * Sets the Installment Plan creation timestamp.
   *
   * @param int $timestamp
   *   The Installment Plan creation timestamp.
   *
   * @return \Drupal\commerce_installments\Entity\InstallmentPlanInterface
   *   The called Installment Plan entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Gets the Installment Plan revision creation timestamp.
   *
   * @return int
   *   The UNIX timestamp of when this revision was created.
   */
  public function getRevisionCreationTime();

  /**
   * Sets the Installment Plan revision creation timestamp.
   *
   * @param int $timestamp
   *   The UNIX timestamp of when this revision was created.
   *
   * @return \Drupal\commerce_installments\Entity\InstallmentPlanInterface
   *   The called Installment Plan entity.
   */
  public function setRevisionCreationTime($timestamp);

  /**
   * Gets the Installment Plan revision author.
   *
   * @return \Drupal\user\UserInterface
   *   The user entity for the revision author.
   */
  public function getRevisionUser();

  /**
   * Sets the Installment Plan revision author.
   *
   * @param int $uid
   *   The user ID of the revision author.
   *
   * @return \Drupal\commerce_installments\Entity\InstallmentPlanInterface
   *   The called Installment Plan entity.
   */
  public function setRevisionUserId($uid);

}
