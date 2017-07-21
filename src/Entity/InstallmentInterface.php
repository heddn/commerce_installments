<?php

namespace Drupal\commerce_installments\Entity;

use Drupal\Core\Entity\RevisionLogInterface;
use Drupal\Core\Entity\RevisionableInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Installment entities.
 *
 * @ingroup commerce_installments
 */
interface InstallmentInterface extends RevisionableInterface, RevisionLogInterface, EntityChangedInterface, EntityOwnerInterface {

  // Add get/set methods for your configuration properties here.

  /**
   * Gets the Installment name.
   *
   * @return string
   *   Name of the Installment.
   */
  public function getName();

  /**
   * Sets the Installment name.
   *
   * @param string $name
   *   The Installment name.
   *
   * @return \Drupal\commerce_installments\Entity\InstallmentInterface
   *   The called Installment entity.
   */
  public function setName($name);

  /**
   * Gets the Installment creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Installment.
   */
  public function getCreatedTime();

  /**
   * Sets the Installment creation timestamp.
   *
   * @param int $timestamp
   *   The Installment creation timestamp.
   *
   * @return \Drupal\commerce_installments\Entity\InstallmentInterface
   *   The called Installment entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Gets the Installment revision creation timestamp.
   *
   * @return int
   *   The UNIX timestamp of when this revision was created.
   */
  public function getRevisionCreationTime();

  /**
   * Sets the Installment revision creation timestamp.
   *
   * @param int $timestamp
   *   The UNIX timestamp of when this revision was created.
   *
   * @return \Drupal\commerce_installments\Entity\InstallmentInterface
   *   The called Installment entity.
   */
  public function setRevisionCreationTime($timestamp);

  /**
   * Gets the Installment revision author.
   *
   * @return \Drupal\user\UserInterface
   *   The user entity for the revision author.
   */
  public function getRevisionUser();

  /**
   * Sets the Installment revision author.
   *
   * @param int $uid
   *   The user ID of the revision author.
   *
   * @return \Drupal\commerce_installments\Entity\InstallmentInterface
   *   The called Installment entity.
   */
  public function setRevisionUserId($uid);

}
