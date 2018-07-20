<?php

namespace Drupal\service_club_event\Controller;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Url;
use Drupal\service_club_event\Entity\EventInformationInterface;

/**
 * Class EventInformationController.
 *
 *  Returns responses for Event information routes.
 */
class EventInformationController extends ControllerBase implements ContainerInjectionInterface {

  /**
   * Displays a Event information  revision.
   *
   * @param int $event_information_revision
   *   The Event information  revision ID.
   *
   * @return array
   *   An array suitable for drupal_render().
   */
  public function revisionShow($event_information_revision) {
    $event_information = $this->entityManager()->getStorage('event_information')->loadRevision($event_information_revision);
    $view_builder = $this->entityManager()->getViewBuilder('event_information');

    return $view_builder->view($event_information);
  }

  /**
   * Page title callback for a Event information  revision.
   *
   * @param int $event_information_revision
   *   The Event information  revision ID.
   *
   * @return string
   *   The page title.
   */
  public function revisionPageTitle($event_information_revision) {
    $event_information = $this->entityManager()->getStorage('event_information')->loadRevision($event_information_revision);
    return $this->t('Revision of %title from %date', ['%title' => $event_information->label(), '%date' => format_date($event_information->getRevisionCreationTime())]);
  }

  /**
   * Generates an overview table of older revisions of a Event information .
   *
   * @param \Drupal\service_club_event\Entity\EventInformationInterface $event_information
   *   A Event information  object.
   *
   * @return array
   *   An array as expected by drupal_render().
   */
  public function revisionOverview(EventInformationInterface $event_information) {
    $account = $this->currentUser();
    $langcode = $event_information->language()->getId();
    $langname = $event_information->language()->getName();
    $languages = $event_information->getTranslationLanguages();
    $has_translations = (count($languages) > 1);
    $event_information_storage = $this->entityManager()->getStorage('event_information');

    $build['#title'] = $has_translations ? $this->t('@langname revisions for %title', ['@langname' => $langname, '%title' => $event_information->label()]) : $this->t('Revisions for %title', ['%title' => $event_information->label()]);
    $header = [$this->t('Revision'), $this->t('Operations')];

    $revert_permission = (($account->hasPermission("revert all event information revisions") || $account->hasPermission('administer event information entities')));
    $delete_permission = (($account->hasPermission("delete all event information revisions") || $account->hasPermission('administer event information entities')));

    $rows = [];

    $vids = $event_information_storage->revisionIds($event_information);

    $latest_revision = TRUE;

    foreach (array_reverse($vids) as $vid) {
      /** @var \Drupal\service_club_event\EventInformationInterface $revision */
      $revision = $event_information_storage->loadRevision($vid);
      // Only show revisions that are affected by the language that is being
      // displayed.
      if ($revision->hasTranslation($langcode) && $revision->getTranslation($langcode)->isRevisionTranslationAffected()) {
        $username = [
          '#theme' => 'username',
          '#account' => $revision->getRevisionUser(),
        ];

        // Use revision link to link to revisions that are not active.
        $date = \Drupal::service('date.formatter')->format($revision->getRevisionCreationTime(), 'short');
        if ($vid != $event_information->getRevisionId()) {
          $link = $this->l($date, new Url('entity.event_information.revision', ['event_information' => $event_information->id(), 'event_information_revision' => $vid]));
        }
        else {
          $link = $event_information->link($date);
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
              Url::fromRoute('entity.event_information.translation_revert', ['event_information' => $event_information->id(), 'event_information_revision' => $vid, 'langcode' => $langcode]) :
              Url::fromRoute('entity.event_information.revision_revert', ['event_information' => $event_information->id(), 'event_information_revision' => $vid]),
            ];
          }

          if ($delete_permission) {
            $links['delete'] = [
              'title' => $this->t('Delete'),
              'url' => Url::fromRoute('entity.event_information.revision_delete', ['event_information' => $event_information->id(), 'event_information_revision' => $vid]),
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

    $build['event_information_revisions_table'] = [
      '#theme' => 'table',
      '#rows' => $rows,
      '#header' => $header,
    ];

    return $build;
  }

}
