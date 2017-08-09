<?php

namespace Drupal\commerce_installments;

use Drupal\commerce_installments\Entity\InstallmentPlanInterface;
use Drupal\Core\Config\Entity\ConfigEntityStorage;
use Drupal\Core\Entity\Sql\SqlContentEntityStorage;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Defines the storage handler class for Installment Plan Method entities.
 *
 * This extends the base storage class, adding required special handling for
 * Installment Plan entities.
 *
 * @ingroup commerce_installments
 */
class InstallmentPlanMethodStorage extends ConfigEntityStorage {}
