<?php

namespace Drupal\service_club_event\Controller;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Url;
use Drupal\service_club_event\Entity\EventInfoEntityInterface;

/**
 * Class EventInfoEntityController.
 *
 *  Returns responses for Event info entity routes.
 */
class EventInfoEntityController extends ControllerBase implements ContainerInjectionInterface {

  /**
   * Displays a Event info entity  revision.
   *
   * @param int $event_info_entity_revision
   *   The Event info entity  revision ID.
   *
   * @return array
   *   An array suitable for drupal_render().
   */
  public function revisionShow($event_info_entity_revision) {
    $event_info_entity = $this->entityManager()->getStorage('event_info_entity')->loadRevision($event_info_entity_revision);
    $view_builder = $this->entityManager()->getViewBuilder('event_info_entity');

    return $view_builder->view($event_info_entity);
  }

  /**
   * Page title callback for a Event info entity  revision.
   *
   * @param int $event_info_entity_revision
   *   The Event info entity  revision ID.
   *
   * @return string
   *   The page title.
   */
  public function revisionPageTitle($event_info_entity_revision) {
    $event_info_entity = $this->entityManager()->getStorage('event_info_entity')->loadRevision($event_info_entity_revision);
    return $this->t('Revision of %title from %date', ['%title' => $event_info_entity->label(), '%date' => format_date($event_info_entity->getRevisionCreationTime())]);
  }

  /**
   * Generates an overview table of older revisions of a Event info entity .
   *
   * @param \Drupal\service_club_event\Entity\EventInfoEntityInterface $event_info_entity
   *   A Event info entity  object.
   *
   * @return array
   *   An array as expected by drupal_render().
   */
  public function revisionOverview(EventInfoEntityInterface $event_info_entity) {
    $account = $this->currentUser();
    $langcode = $event_info_entity->language()->getId();
    $langname = $event_info_entity->language()->getName();
    $languages = $event_info_entity->getTranslationLanguages();
    $has_translations = (count($languages) > 1);
    $event_info_entity_storage = $this->entityManager()->getStorage('event_info_entity');

    $build['#title'] = $has_translations ? $this->t('@langname revisions for %title', ['@langname' => $langname, '%title' => $event_info_entity->label()]) : $this->t('Revisions for %title', ['%title' => $event_info_entity->label()]);
    $header = [$this->t('Revision'), $this->t('Operations')];

    $revert_permission = (($account->hasPermission("revert all event info entity revisions") || $account->hasPermission('administer event info entity entities')));
    $delete_permission = (($account->hasPermission("delete all event info entity revisions") || $account->hasPermission('administer event info entity entities')));

    $rows = [];

    $vids = $event_info_entity_storage->revisionIds($event_info_entity);

    $latest_revision = TRUE;

    foreach (array_reverse($vids) as $vid) {
      /** @var \Drupal\service_club_event\EventInfoEntityInterface $revision */
      $revision = $event_info_entity_storage->loadRevision($vid);
      // Only show revisions that are affected by the language that is being
      // displayed.
      if ($revision->hasTranslation($langcode) && $revision->getTranslation($langcode)->isRevisionTranslationAffected()) {
        $username = [
          '#theme' => 'username',
          '#account' => $revision->getRevisionUser(),
        ];

        // Use revision link to link to revisions that are not active.
        $date = \Drupal::service('date.formatter')->format($revision->getRevisionCreationTime(), 'short');
        if ($vid != $event_info_entity->getRevisionId()) {
          $link = $this->l($date, new Url('entity.event_info_entity.revision', ['event_info_entity' => $event_info_entity->id(), 'event_info_entity_revision' => $vid]));
        }
        else {
          $link = $event_info_entity->link($date);
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
              Url::fromRoute('entity.event_info_entity.translation_revert', ['event_info_entity' => $event_info_entity->id(), 'event_info_entity_revision' => $vid, 'langcode' => $langcode]) :
              Url::fromRoute('entity.event_info_entity.revision_revert', ['event_info_entity' => $event_info_entity->id(), 'event_info_entity_revision' => $vid]),
            ];
          }

          if ($delete_permission) {
            $links['delete'] = [
              'title' => $this->t('Delete'),
              'url' => Url::fromRoute('entity.event_info_entity.revision_delete', ['event_info_entity' => $event_info_entity->id(), 'event_info_entity_revision' => $vid]),
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

    $build['event_info_entity_revisions_table'] = [
      '#theme' => 'table',
      '#rows' => $rows,
      '#header' => $header,
    ];

    return $build;
  }

}
