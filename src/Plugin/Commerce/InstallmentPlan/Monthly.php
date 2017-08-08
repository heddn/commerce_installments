<?php


namespace Drupal\commerce_installments\Plugin\Commerce\InstallmentPlan;

use Drupal\commerce_installments\Annotation\InstallmentPlan;
use Drupal\commerce_order\Entity\OrderInterface;
use Drupal\Component\Datetime\DateTimePlus;

/**
 * Provides a monthly installment plugin.
 *
 * @InstallmentPlan(
 *   id = "monthly",
 *   label = @Translation("Monthly Installments"),
 * )
 */
class Monthly extends InstallmentPlanBase {

  /**
   * @inheritDoc
   */
  public function getDay() {
    return (new DateTimePlus('now', $this->getTimezone()))->format('d');
  }

  /**
   * @inheritDoc
   */
  public function buildInstallments(OrderInterface $order, $numberPayments) {
    $planEntity = $this->installmentPlanStorage->create([
      'type' => $this->getInstallmentPlanBundle(),
      'order_id' => $order->id(),
      'payment_gateway' => $order->payment_gateway->entity,
      'payment_method' => $order->payment_method->entity,
    ]);

    $installmentPayments = $this->getInstallmentAmounts($numberPayments, $order->getTotalPrice());
    $installmentDates = $this->getInstallmentDates($numberPayments);

    foreach ($installmentPayments as $delta => $payment) {
      $installmentEntity = $this->installmentStorage->create([
        'type' => $this->getInstallmentBundle(),
        'payment_date' => $installmentDates[$delta]->format('U'),
        'amount' => $payment,
      ]);
      $installmentEntity->save();
      $planEntity->addInstallment($installmentEntity);
    }
    $planEntity->save();

    return $planEntity;
  }

  /**
   * @inheritDoc
   */
  public function getInstallmentDates($numberPayments) {
    $monthYear = (new DateTimePlus('now', $this->getTimezone()))->format('m-Y');
    $monthDay = $this->getDay();
    $time = $this->getTime();
    $date = DateTimePlus::createFromFormat('d-m-Y H:i:s', "$monthDay-$monthYear $time");

    $oldDate = clone $date;

    $dates = [];
    // Add today to the list of payments.
    $dates[] = clone $date;
    // Now add the rest of the installments.
    for ($i = 1; $i < $numberPayments; $i++) {
      $date->modify('+ 1 month');
      $dateInterval = $date->diff($oldDate);
      if ($dateInterval->d <> 0) {
        $date->modify('last day of last month');
      }
      $dates[] = clone $date;
    }

    return $dates;
  }

}
