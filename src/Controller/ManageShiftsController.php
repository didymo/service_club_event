<?php

namespace Drupal\service_club_event\Controller;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Url;
use Drupal\service_club_event\Entity\ManageShiftsInterface;

/**
 * Class ManageShiftsController.
 *
 *  Returns responses for Manage shifts routes.
 */
class ManageShiftsController extends ControllerBase implements ContainerInjectionInterface {

  /**
   * Displays a Manage shifts  revision.
   *
   * @param int $manage_shifts_revision
   *   The Manage shifts  revision ID.
   *
   * @return array
   *   An array suitable for drupal_render().
   */
  public function revisionShow($manage_shifts_revision) {
    $manage_shifts = $this->entityManager()->getStorage('manage_shifts')->loadRevision($manage_shifts_revision);
    $view_builder = $this->entityManager()->getViewBuilder('manage_shifts');

    return $view_builder->view($manage_shifts);
  }

  /**
   * Page title callback for a Manage shifts  revision.
   *
   * @param int $manage_shifts_revision
   *   The Manage shifts  revision ID.
   *
   * @return string
   *   The page title.
   */
  public function revisionPageTitle($manage_shifts_revision) {
    $manage_shifts = $this->entityManager()->getStorage('manage_shifts')->loadRevision($manage_shifts_revision);
    return $this->t('Revision of %title from %date', ['%title' => $manage_shifts->label(), '%date' => format_date($manage_shifts->getRevisionCreationTime())]);
  }

  /**
   * Generates an overview table of older revisions of a Manage shifts .
   *
   * @param \Drupal\service_club_event\Entity\ManageShiftsInterface $manage_shifts
   *   A Manage shifts  object.
   *
   * @return array
   *   An array as expected by drupal_render().
   */
  public function revisionOverview(ManageShiftsInterface $manage_shifts) {
    $account = $this->currentUser();
    $langcode = $manage_shifts->language()->getId();
    $langname = $manage_shifts->language()->getName();
    $languages = $manage_shifts->getTranslationLanguages();
    $has_translations = (count($languages) > 1);
    $manage_shifts_storage = $this->entityManager()->getStorage('manage_shifts');

    $build['#title'] = $has_translations ? $this->t('@langname revisions for %title', ['@langname' => $langname, '%title' => $manage_shifts->label()]) : $this->t('Revisions for %title', ['%title' => $manage_shifts->label()]);
    $header = [$this->t('Revision'), $this->t('Operations')];

    $revert_permission = (($account->hasPermission("revert all manage shifts revisions") || $account->hasPermission('administer manage shifts entities')));
    $delete_permission = (($account->hasPermission("delete all manage shifts revisions") || $account->hasPermission('administer manage shifts entities')));

    $rows = [];

    $vids = $manage_shifts_storage->revisionIds($manage_shifts);

    $latest_revision = TRUE;

    foreach (array_reverse($vids) as $vid) {
      /** @var \Drupal\service_club_event\ManageShiftsInterface $revision */
      $revision = $manage_shifts_storage->loadRevision($vid);
      // Only show revisions that are affected by the language that is being
      // displayed.
      if ($revision->hasTranslation($langcode) && $revision->getTranslation($langcode)->isRevisionTranslationAffected()) {
        $username = [
          '#theme' => 'username',
          '#account' => $revision->getRevisionUser(),
        ];

        // Use revision link to link to revisions that are not active.
        $date = \Drupal::service('date.formatter')->format($revision->getRevisionCreationTime(), 'short');
        if ($vid != $manage_shifts->getRevisionId()) {
          $link = $this->l($date, new Url('entity.manage_shifts.revision', ['manage_shifts' => $manage_shifts->id(), 'manage_shifts_revision' => $vid]));
        }
        else {
          $link = $manage_shifts->link($date);
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
              Url::fromRoute('entity.manage_shifts.translation_revert', ['manage_shifts' => $manage_shifts->id(), 'manage_shifts_revision' => $vid, 'langcode' => $langcode]) :
              Url::fromRoute('entity.manage_shifts.revision_revert', ['manage_shifts' => $manage_shifts->id(), 'manage_shifts_revision' => $vid]),
            ];
          }

          if ($delete_permission) {
            $links['delete'] = [
              'title' => $this->t('Delete'),
              'url' => Url::fromRoute('entity.manage_shifts.revision_delete', ['manage_shifts' => $manage_shifts->id(), 'manage_shifts_revision' => $vid]),
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

    $build['manage_shifts_revisions_table'] = [
      '#theme' => 'table',
      '#rows' => $rows,
      '#header' => $header,
    ];

    return $build;
  }

}
