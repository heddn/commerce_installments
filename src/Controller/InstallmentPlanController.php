<?php

namespace Drupal\commerce_installments\Controller;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Url;
use Drupal\commerce_installments\Entity\InstallmentPlanInterface;

/**
 * Class InstallmentPlanController.
 *
 *  Returns responses for Installment Plan routes.
 */
class InstallmentPlanController extends ControllerBase implements ContainerInjectionInterface {

  /**
   * Displays a Installment Plan  revision.
   *
   * @param int $commerce_installment_plan_revision
   *   The Installment Plan  revision ID.
   *
   * @return array
   *   An array suitable for drupal_render().
   */
  public function revisionShow($commerce_installment_plan_revision) {
    $commerce_installment_plan = $this->entityManager()->getStorage('commerce_installment_plan')->loadRevision($commerce_installment_plan_revision);
    $view_builder = $this->entityManager()->getViewBuilder('commerce_installment_plan');

    return $view_builder->view($commerce_installment_plan);
  }

  /**
   * Page title callback for a Installment Plan  revision.
   *
   * @param int $commerce_installment_plan_revision
   *   The Installment Plan  revision ID.
   *
   * @return string
   *   The page title.
   */
  public function revisionPageTitle($commerce_installment_plan_revision) {
    $commerce_installment_plan = $this->entityManager()->getStorage('commerce_installment_plan')->loadRevision($commerce_installment_plan_revision);
    return $this->t('Revision of %title from %date', ['%title' => $commerce_installment_plan->label(), '%date' => format_date($commerce_installment_plan->getRevisionCreationTime())]);
  }

  /**
   * Generates an overview table of older revisions of a Installment Plan .
   *
   * @param \Drupal\commerce_installments\Entity\InstallmentPlanInterface $commerce_installment_plan
   *   A Installment Plan  object.
   *
   * @return array
   *   An array as expected by drupal_render().
   */
  public function revisionOverview(InstallmentPlanInterface $commerce_installment_plan) {
    $account = $this->currentUser();
    $langcode = $commerce_installment_plan->language()->getId();
    $langname = $commerce_installment_plan->language()->getName();
    $languages = $commerce_installment_plan->getTranslationLanguages();
    $has_translations = (count($languages) > 1);
    $commerce_installment_plan_storage = $this->entityManager()->getStorage('commerce_installment_plan');

    $build['#title'] = $has_translations ? $this->t('@langname revisions for %title', ['@langname' => $langname, '%title' => $commerce_installment_plan->label()]) : $this->t('Revisions for %title', ['%title' => $commerce_installment_plan->label()]);
    $header = [$this->t('Revision'), $this->t('Operations')];

    $revert_permission = (($account->hasPermission("revert all installment plan revisions") || $account->hasPermission('administer installment plan entities')));
    $delete_permission = (($account->hasPermission("delete all installment plan revisions") || $account->hasPermission('administer installment plan entities')));

    $rows = [];

    $vids = $commerce_installment_plan_storage->revisionIds($commerce_installment_plan);

    $latest_revision = TRUE;

    foreach (array_reverse($vids) as $vid) {
      /** @var \Drupal\commerce_installments\InstallmentPlanInterface $revision */
      $revision = $commerce_installment_plan_storage->loadRevision($vid);
      // Only show revisions that are affected by the language that is being
      // displayed.
      if ($revision->hasTranslation($langcode) && $revision->getTranslation($langcode)->isRevisionTranslationAffected()) {
        $username = [
          '#theme' => 'username',
          '#account' => $revision->getRevisionUser(),
        ];

        // Use revision link to link to revisions that are not active.
        $date = \Drupal::service('date.formatter')->format($revision->getRevisionCreationTime(), 'short');
        if ($vid != $commerce_installment_plan->getRevisionId()) {
          $link = $this->l($date, new Url('entity.commerce_installment_plan.revision', ['commerce_installment_plan' => $commerce_installment_plan->id(), 'commerce_installment_plan_rev' => $vid]));
        }
        else {
          $link = $commerce_installment_plan->link($date);
        }

        $row = [];
        $column = [
          'data' => [
            '#type' => 'inline_template',
            '#template' => '{% trans %}{{ date }} by {{ username }}{% endtrans %}{% if message %}<p class="revision-log">{{ message }}</p>{% endif %}',
            '#context' => [
              'date' => $link,
              'username' => \Drupal::service('renderer')->renderPlain($username),
              'message' => ['#markup' => $revision->getRevisionLogMessage(), '#allowed_tags' => Xss::getHtmlTagList()],
            ],
          ],
        ];
        $row[] = $column;

        if ($latest_revision) {
          $row[] = [
            'data' => [
              '#prefix' => '<em>',
              '#markup' => $this->t('Current revision'),
              '#suffix' => '</em>',
            ],
          ];
          foreach ($row as &$current) {
            $current['class'] = ['revision-current'];
          }
          $latest_revision = FALSE;
        }
        else {
          $links = [];
          if ($revert_permission) {
            $links['revert'] = [
              'title' => $this->t('Revert'),
              'url' => $has_translations ?
              Url::fromRoute('entity.commerce_installment_plan.translation_revert', ['commerce_installment_plan' => $commerce_installment_plan->id(), 'commerce_installment_plan_rev' => $vid, 'langcode' => $langcode]) :
              Url::fromRoute('entity.commerce_installment_plan.revision_revert', ['commerce_installment_plan' => $commerce_installment_plan->id(), 'commerce_installment_plan_rev' => $vid]),
            ];
          }

          if ($delete_permission) {
            $links['delete'] = [
              'title' => $this->t('Delete'),
              'url' => Url::fromRoute('entity.commerce_installment_plan.revision_delete', ['commerce_installment_plan' => $commerce_installment_plan->id(), 'commerce_installment_plan_rev' => $vid]),
            ];
          }

          $row[] = [
            'data' => [
              '#type' => 'operations',
              '#links' => $links,
            ],
          ];
        }

        $rows[] = $row;
      }
    }

    $build['commerce_installment_plan_revisions_table'] = [
      '#theme' => 'table',
      '#rows' => $rows,
      '#header' => $header,
    ];

    return $build;
  }

}
