<?php

namespace Drupal\service_club_event\Controller;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Url;
use Drupal\service_club_event\Entity\AdditionalGuestsInterface;

/**
 * Class AdditionalGuestsController.
 *
 *  Returns responses for Additional guests routes.
 */
class AdditionalGuestsController extends ControllerBase implements ContainerInjectionInterface {

  /**
   * Displays a Additional guests  revision.
   *
   * @param int $additional_guests_revision
   *   The Additional guests  revision ID.
   *
   * @return array
   *   An array suitable for drupal_render().
   */
  public function revisionShow($additional_guests_revision) {
    $additional_guests = $this->entityManager()->getStorage('additional_guests')->loadRevision($additional_guests_revision);
    $view_builder = $this->entityManager()->getViewBuilder('additional_guests');

    return $view_builder->view($additional_guests);
  }

  /**
   * Page title callback for a Additional guests  revision.
   *
   * @param int $additional_guests_revision
   *   The Additional guests  revision ID.
   *
   * @return string
   *   The page title.
   */
  public function revisionPageTitle($additional_guests_revision) {
    $additional_guests = $this->entityManager()->getStorage('additional_guests')->loadRevision($additional_guests_revision);
    return $this->t('Revision of %title from %date', ['%title' => $additional_guests->label(), '%date' => format_date($additional_guests->getRevisionCreationTime())]);
  }

  /**
   * Generates an overview table of older revisions of a Additional guests .
   *
   * @param \Drupal\service_club_event\Entity\AdditionalGuestsInterface $additional_guests
   *   A Additional guests  object.
   *
   * @return array
   *   An array as expected by drupal_render().
   */
  public function revisionOverview(AdditionalGuestsInterface $additional_guests) {
    $account = $this->currentUser();
    $langcode = $additional_guests->language()->getId();
    $langname = $additional_guests->language()->getName();
    $languages = $additional_guests->getTranslationLanguages();
    $has_translations = (count($languages) > 1);
    $additional_guests_storage = $this->entityManager()->getStorage('additional_guests');

    $build['#title'] = $has_translations ? $this->t('@langname revisions for %title', ['@langname' => $langname, '%title' => $additional_guests->label()]) : $this->t('Revisions for %title', ['%title' => $additional_guests->label()]);
    $header = [$this->t('Revision'), $this->t('Operations')];

    $revert_permission = (($account->hasPermission("revert all additional guests revisions") || $account->hasPermission('administer additional guests entities')));
    $delete_permission = (($account->hasPermission("delete all additional guests revisions") || $account->hasPermission('administer additional guests entities')));

    $rows = [];

    $vids = $additional_guests_storage->revisionIds($additional_guests);

    $latest_revision = TRUE;

    foreach (array_reverse($vids) as $vid) {
      /** @var \Drupal\service_club_event\AdditionalGuestsInterface $revision */
      $revision = $additional_guests_storage->loadRevision($vid);
      // Only show revisions that are affected by the language that is being
      // displayed.
      if ($revision->hasTranslation($langcode) && $revision->getTranslation($langcode)->isRevisionTranslationAffected()) {
        $username = [
          '#theme' => 'username',
          '#account' => $revision->getRevisionUser(),
        ];

        // Use revision link to link to revisions that are not active.
        $date = \Drupal::service('date.formatter')->format($revision->getRevisionCreationTime(), 'short');
        if ($vid != $additional_guests->getRevisionId()) {
          $link = $this->l($date, new Url('entity.additional_guests.revision', ['additional_guests' => $additional_guests->id(), 'additional_guests_revision' => $vid]));
        }
        else {
          $link = $additional_guests->link($date);
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
              Url::fromRoute('entity.additional_guests.translation_revert', ['additional_guests' => $additional_guests->id(), 'additional_guests_revision' => $vid, 'langcode' => $langcode]) :
              Url::fromRoute('entity.additional_guests.revision_revert', ['additional_guests' => $additional_guests->id(), 'additional_guests_revision' => $vid]),
            ];
          }

          if ($delete_permission) {
            $links['delete'] = [
              'title' => $this->t('Delete'),
              'url' => Url::fromRoute('entity.additional_guests.revision_delete', ['additional_guests' => $additional_guests->id(), 'additional_guests_revision' => $vid]),
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

    $build['additional_guests_revisions_table'] = [
      '#theme' => 'table',
      '#rows' => $rows,
      '#header' => $header,
    ];

    return $build;
  }

}
