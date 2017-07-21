<?php

namespace Drupal\commerce_installments;

use Drupal\Core\Entity\ContentEntityStorageInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\commerce_installments\Entity\InstallmentInterface;

/**
 * Defines the storage handler class for Installment entities.
 *
 * This extends the base storage class, adding required special handling for
 * Installment entities.
 *
 * @ingroup commerce_installments
 */
interface InstallmentStorageInterface extends ContentEntityStorageInterface {

  /**
   * Gets a list of Installment revision IDs for a specific Installment.
   *
   * @param \Drupal\commerce_installments\Entity\InstallmentInterface $entity
   *   The Installment entity.
   *
   * @return int[]
   *   Installment revision IDs (in ascending order).
   */
  public function revisionIds(InstallmentInterface $entity);

  /**
   * Gets a list of revision IDs having a given user as Installment author.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user entity.
   *
   * @return int[]
   *   Installment revision IDs (in ascending order).
   */
  public function userRevisionIds(AccountInterface $account);

  /**
   * Counts the number of revisions in the default language.
   *
   * @param \Drupal\commerce_installments\Entity\InstallmentInterface $entity
   *   The Installment entity.
   *
   * @return int
   *   The number of revisions in the default language.
   */
  public function countDefaultLanguageRevisions(InstallmentInterface $entity);

  /**
   * Unsets the language for all Installment with the given language.
   *
   * @param \Drupal\Core\Language\LanguageInterface $language
   *   The language object.
   */
  public function clearRevisionsLanguage(LanguageInterface $language);

}
