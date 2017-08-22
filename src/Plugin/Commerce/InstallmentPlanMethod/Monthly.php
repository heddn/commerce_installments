<?php


namespace Drupal\commerce_installments\Plugin\Commerce\InstallmentPlanMethod;

use Drupal\commerce_installments\Annotation\InstallmentPlan;
use Drupal\commerce_order\Entity\OrderInterface;
use Drupal\Component\Datetime\DateTimePlus;

/**
 * Provides a monthly installment plan method plugin.
 *
 * @InstallmentPlan(
 *   id = "monthly",
 *   label = @Translation("Monthly Installments"),
 * )
 */
class Monthly extends InstallmentPlanMethodMethodBase {

  /**
   * @inheritDoc
   */
  public function getDay() {
    return (new DateTimePlus('now', $this->getTimezone()))->format('d');
  }

  /**
   * @inheritDoc
   */
  public function getInstallmentDates($numberPayments, OrderInterface $order) {
    $monthYear = (new DateTimePlus('now', $this->getTimezone()))->format('m-Y');
    $monthDay = $this->getDay();
    $time = $this->getTime();
    $date = DateTimePlus::createFromFormat('d-m-Y H:i:s', "$monthDay-$monthYear $time");

    $dates = [];
    // Add today to the list of payments.
    $dates[] = clone $date;
    // Now add the rest of the installments.
    for ($i = 1; $i < $numberPayments; $i++) {
      $oldDate = clone $date;
      $date->modify("+ 1 month");
      $dateInterval = $date->diff($oldDate);
      // Assert that the month interval is properly 1 month:
      while ($dateInterval->m < 1) {
        // We went 30x days but didn't reach the right month:
        // Ex. Dec 1 to Dec 31. appropriate behavior is to go to Jan 1.
        $date->modify("first day of next month");
        $dateInterval = $date->diff($oldDate);
      }
      if ($dateInterval->d != 0) {
        // We went too far, which can happen around February. Collapse back
        // to the end of the proper (prior) month.
        $date->modify("last day of last month");
      }
      $dates[] = clone $date;
    }

    return $dates;
  }

}
