<?php

namespace Drupal\commerce_installments\Controller;

use Drupal\commerce_installments\UrlParameterBuilderTrait;
use Drupal\Component\Utility\Xss;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\commerce_installments\Entity\InstallmentPlanInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class InstallmentPlanController.
 *
 *  Returns responses for Installment Plan routes.
 */
class InstallmentPlanController extends ControllerBase {

  /**
   * The date formatter.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormatter;

  public function __construct(DateFormatterInterface $dateFormatter) {
    $this->dateFormatter = $dateFormatter;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('date.formatter')
    );
  }

  use UrlParameterBuilderTrait;

  /**
   * Displays a Installment Plan  revision.
   *
   * @param int $installment_plan_revision
   *   The Installment Plan  revision ID.
   *
   * @return array
   *   An array suitable for drupal_render().
   */
  public function revisionShow($installment_plan_revision) {
    $installment_plan = $this->entityTypeManager()->getStorage('installment_plan')->loadRevision($installment_plan_revision);
    $view_builder = $this->entityTypeManager()->getViewBuilder('installment_plan');

    return $view_builder->view($installment_plan);
  }

  /**
   * Page title callback for a Installment Plan  revision.
   *
   * @param int $installment_plan_revision
   *   The Installment Plan  revision ID.
   *
   * @return string
   *   The page title.
   */
  public function revisionPageTitle($installment_plan_revision) {
    $installment_plan = $this->entityTypeManager()->getStorage('installment_plan')->loadRevision($installment_plan_revision);
    return $this->t('Revision of %title from %date', ['%title' => $installment_plan->label(), '%date' => $this->dateFormatter->format($installment_plan->getRevisionCreationTime())]);
  }

  /**
   * Generates an overview table of older revisions of a Installment Plan .
   *
   * @param \Drupal\commerce_installments\Entity\InstallmentPlanInterface $installment_plan
   *   A Installment Plan  object.
   *
   * @return array
   *   An array as expected by drupal_render().
   */
  public function revisionOverview(InstallmentPlanInterface $installment_plan) {
    $account = $this->currentUser();
    $langcode = $installment_plan->language()->getId();
    $langname = $installment_plan->language()->getName();
    $languages = $installment_plan->getTranslationLanguages();
    $has_translations = (count($languages) > 1);
    $installment_plan_storage = $this->entityTypeManager()->getStorage('installment_plan');

    $build['#title'] = $has_translations ? $this->t('@langname revisions for %title', ['@langname' => $langname, '%title' => $installment_plan->label()]) : $this->t('Revisions for %title', ['%title' => $installment_plan->label()]);
    $header = [$this->t('Revision'), $this->t('Operations')];

    $revert_permission = (($account->hasPermission("revert all installment plan revisions") || $account->hasPermission('administer installment plan entities')));
    $delete_permission = (($account->hasPermission("delete all installment plan revisions") || $account->hasPermission('administer installment plan entities')));

    $rows = [];

    $vids = $installment_plan_storage->revisionIds($installment_plan);

    $latest_revision = TRUE;

    foreach (array_reverse($vids) as $vid) {
      /** @var \Drupal\commerce_installments\Entity\InstallmentPlanInterface $revision */
      $revision = $installment_plan_storage->loadRevision($vid);
      // Only show revisions that are affected by the language that is being
      // displayed.
      if ($revision->hasTranslation($langcode) && $revision->getTranslation($langcode)->isRevisionTranslationAffected()) {
        $username = [
          '#theme' => 'username',
          '#account' => $revision->getRevisionUser(),
        ];

        // Use revision link to link to revisions that are not active.
        $date = $this->dateFormatter->format($revision->getRevisionCreationTime(), 'short');
        if ($vid != $installment_plan->getRevisionId()) {
          $link = new Link($date, new Url('entity.installment_plan.revision', ['installment_plan' => $installment_plan->id(), 'installment_plan_revision' => $vid] + $this->getUrlParameters()));
        }
        else {
          $link = $installment_plan->toLink($date);
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
              Url::fromRoute('entity.installment_plan.translation_revert', ['installment_plan' => $installment_plan->id(), 'installment_plan_revision' => $vid, 'langcode' => $langcode] + $this->getUrlParameters()) :
              Url::fromRoute('entity.installment_plan.revision_revert', ['installment_plan' => $installment_plan->id(), 'installment_plan_revision' => $vid] + $this->getUrlParameters()),
            ];
          }

          if ($delete_permission) {
            $links['delete'] = [
              'title' => $this->t('Delete'),
              'url' => Url::fromRoute('entity.installment_plan.revision_delete', ['installment_plan' => $installment_plan->id(), 'installment_plan_revision' => $vid] + $this->getUrlParameters()),
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

    $build['installment_plan_revisions_table'] = [
      '#theme' => 'table',
      '#rows' => $rows,
      '#header' => $header,
    ];

    return $build;
  }

}
