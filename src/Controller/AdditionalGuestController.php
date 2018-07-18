<?php

namespace Drupal\service_club_event\Controller;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Url;
use Drupal\service_club_event\Entity\AdditionalGuestInterface;

/**
 * Class AdditionalGuestController.
 *
 *  Returns responses for Additional guest routes.
 */
class AdditionalGuestController extends ControllerBase implements ContainerInjectionInterface {

  /**
   * Displays a Additional guest  revision.
   *
   * @param int $additional_guest_revision
   *   The Additional guest  revision ID.
   *
   * @return array
   *   An array suitable for drupal_render().
   */
  public function revisionShow($additional_guest_revision) {
    $additional_guest = $this->entityManager()->getStorage('additional_guest')->loadRevision($additional_guest_revision);
    $view_builder = $this->entityManager()->getViewBuilder('additional_guest');

    return $view_builder->view($additional_guest);
  }

  /**
   * Page title callback for a Additional guest  revision.
   *
   * @param int $additional_guest_revision
   *   The Additional guest  revision ID.
   *
   * @return string
   *   The page title.
   */
  public function revisionPageTitle($additional_guest_revision) {
    $additional_guest = $this->entityManager()->getStorage('additional_guest')->loadRevision($additional_guest_revision);
    return $this->t('Revision of %title from %date', ['%title' => $additional_guest->label(), '%date' => format_date($additional_guest->getRevisionCreationTime())]);
  }

  /**
   * Generates an overview table of older revisions of a Additional guest .
   *
   * @param \Drupal\service_club_event\Entity\AdditionalGuestInterface $additional_guest
   *   A Additional guest  object.
   *
   * @return array
   *   An array as expected by drupal_render().
   */
  public function revisionOverview(AdditionalGuestInterface $additional_guest) {
    $account = $this->currentUser();
    $langcode = $additional_guest->language()->getId();
    $langname = $additional_guest->language()->getName();
    $languages = $additional_guest->getTranslationLanguages();
    $has_translations = (count($languages) > 1);
    $additional_guest_storage = $this->entityManager()->getStorage('additional_guest');

    $build['#title'] = $has_translations ? $this->t('@langname revisions for %title', ['@langname' => $langname, '%title' => $additional_guest->label()]) : $this->t('Revisions for %title', ['%title' => $additional_guest->label()]);
    $header = [$this->t('Revision'), $this->t('Operations')];

    $revert_permission = (($account->hasPermission("revert all additional guest revisions") || $account->hasPermission('administer additional guest entities')));
    $delete_permission = (($account->hasPermission("delete all additional guest revisions") || $account->hasPermission('administer additional guest entities')));

    $rows = [];

    $vids = $additional_guest_storage->revisionIds($additional_guest);

    $latest_revision = TRUE;

    foreach (array_reverse($vids) as $vid) {
      /** @var \Drupal\service_club_event\AdditionalGuestInterface $revision */
      $revision = $additional_guest_storage->loadRevision($vid);
      // Only show revisions that are affected by the language that is being
      // displayed.
      if ($revision->hasTranslation($langcode) && $revision->getTranslation($langcode)->isRevisionTranslationAffected()) {
        $username = [
          '#theme' => 'username',
          '#account' => $revision->getRevisionUser(),
        ];

        // Use revision link to link to revisions that are not active.
        $date = \Drupal::service('date.formatter')->format($revision->getRevisionCreationTime(), 'short');
        if ($vid != $additional_guest->getRevisionId()) {
          $link = $this->l($date, new Url('entity.additional_guest.revision', ['additional_guest' => $additional_guest->id(), 'additional_guest_revision' => $vid]));
        }
        else {
          $link = $additional_guest->link($date);
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
              Url::fromRoute('entity.additional_guest.translation_revert', ['additional_guest' => $additional_guest->id(), 'additional_guest_revision' => $vid, 'langcode' => $langcode]) :
              Url::fromRoute('entity.additional_guest.revision_revert', ['additional_guest' => $additional_guest->id(), 'additional_guest_revision' => $vid]),
            ];
          }

          if ($delete_permission) {
            $links['delete'] = [
              'title' => $this->t('Delete'),
              'url' => Url::fromRoute('entity.additional_guest.revision_delete', ['additional_guest' => $additional_guest->id(), 'additional_guest_revision' => $vid]),
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

    $build['additional_guest_revisions_table'] = [
      '#theme' => 'table',
      '#rows' => $rows,
      '#header' => $header,
    ];

    return $build;
  }

}
