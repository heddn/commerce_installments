<?php

namespace Drupal\commerce_installments\Controller;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Url;
use Drupal\commerce_installments\Entity\InstallmentInterface;

/**
 * Class InstallmentController.
 *
 *  Returns responses for Installment routes.
 */
class InstallmentController extends ControllerBase implements ContainerInjectionInterface {

  /**
   * Displays a Installment  revision.
   *
   * @param int $commerce_installment_revision
   *   The Installment  revision ID.
   *
   * @return array
   *   An array suitable for drupal_render().
   */
  public function revisionShow($commerce_installment_revision) {
    $commerce_installment = $this->entityManager()->getStorage('commerce_installment')->loadRevision($commerce_installment_revision);
    $view_builder = $this->entityManager()->getViewBuilder('commerce_installment');

    return $view_builder->view($commerce_installment);
  }

  /**
   * Page title callback for a Installment  revision.
   *
   * @param int $commerce_installment_revision
   *   The Installment  revision ID.
   *
   * @return string
   *   The page title.
   */
  public function revisionPageTitle($commerce_installment_revision) {
    $commerce_installment = $this->entityManager()->getStorage('commerce_installment')->loadRevision($commerce_installment_revision);
    return $this->t('Revision of %title from %date', ['%title' => $commerce_installment->label(), '%date' => format_date($commerce_installment->getRevisionCreationTime())]);
  }

  /**
   * Generates an overview table of older revisions of a Installment .
   *
   * @param \Drupal\commerce_installments\Entity\InstallmentInterface $commerce_installment
   *   A Installment  object.
   *
   * @return array
   *   An array as expected by drupal_render().
   */
  public function revisionOverview(InstallmentInterface $commerce_installment) {
    $account = $this->currentUser();
    $langcode = $commerce_installment->language()->getId();
    $langname = $commerce_installment->language()->getName();
    $languages = $commerce_installment->getTranslationLanguages();
    $has_translations = (count($languages) > 1);
    $commerce_installment_storage = $this->entityManager()->getStorage('commerce_installment');

    $build['#title'] = $has_translations ? $this->t('@langname revisions for %title', ['@langname' => $langname, '%title' => $commerce_installment->label()]) : $this->t('Revisions for %title', ['%title' => $commerce_installment->label()]);
    $header = [$this->t('Revision'), $this->t('Operations')];

    $revert_permission = (($account->hasPermission("revert all installment revisions") || $account->hasPermission('administer installment entities')));
    $delete_permission = (($account->hasPermission("delete all installment revisions") || $account->hasPermission('administer installment entities')));

    $rows = [];

    $vids = $commerce_installment_storage->revisionIds($commerce_installment);

    $latest_revision = TRUE;

    foreach (array_reverse($vids) as $vid) {
      /** @var \Drupal\commerce_installments\InstallmentInterface $revision */
      $revision = $commerce_installment_storage->loadRevision($vid);
      // Only show revisions that are affected by the language that is being
      // displayed.
      if ($revision->hasTranslation($langcode) && $revision->getTranslation($langcode)->isRevisionTranslationAffected()) {
        $username = [
          '#theme' => 'username',
          '#account' => $revision->getRevisionUser(),
        ];

        // Use revision link to link to revisions that are not active.
        $date = \Drupal::service('date.formatter')->format($revision->getRevisionCreationTime(), 'short');
        if ($vid != $commerce_installment->getRevisionId()) {
          $link = $this->l($date, new Url('entity.commerce_installment.revision', ['commerce_installment' => $commerce_installment->id(), 'commerce_installment_revision' => $vid]));
        }
        else {
          $link = $commerce_installment->link($date);
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
              Url::fromRoute('entity.commerce_installment.translation_revert', ['commerce_installment' => $commerce_installment->id(), 'commerce_installment_revision' => $vid, 'langcode' => $langcode]) :
              Url::fromRoute('entity.commerce_installment.revision_revert', ['commerce_installment' => $commerce_installment->id(), 'commerce_installment_revision' => $vid]),
            ];
          }

          if ($delete_permission) {
            $links['delete'] = [
              'title' => $this->t('Delete'),
              'url' => Url::fromRoute('entity.commerce_installment.revision_delete', ['commerce_installment' => $commerce_installment->id(), 'commerce_installment_revision' => $vid]),
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

    $build['commerce_installment_revisions_table'] = [
      '#theme' => 'table',
      '#rows' => $rows,
      '#header' => $header,
    ];

    return $build;
  }

}
