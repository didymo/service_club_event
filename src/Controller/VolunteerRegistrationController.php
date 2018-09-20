<?php

namespace Drupal\service_club_event\Controller;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Url;
use Drupal\service_club_event\Entity\VolunteerRegistrationInterface;

/**
 * Class VolunteerRegistrationController.
 *
 *  Returns responses for Volunteer registration routes.
 */
class VolunteerRegistrationController extends ControllerBase implements ContainerInjectionInterface {

  /**
   * Displays a Volunteer registration  revision.
   *
   * @param int $volunteer_registration_revision
   *   The Volunteer registration  revision ID.
   *
   * @return array
   *   An array suitable for drupal_render().
   */
  public function revisionShow($volunteer_registration_revision) {
    $volunteer_registration = $this->entityManager()->getStorage('volunteer_registration')->loadRevision($volunteer_registration_revision);
    $view_builder = $this->entityManager()->getViewBuilder('volunteer_registration');

    return $view_builder->view($volunteer_registration);
  }

  /**
   * Page title callback for a Volunteer registration  revision.
   *
   * @param int $volunteer_registration_revision
   *   The Volunteer registration  revision ID.
   *
   * @return string
   *   The page title.
   */
  public function revisionPageTitle($volunteer_registration_revision) {
    $volunteer_registration = $this->entityManager()->getStorage('volunteer_registration')->loadRevision($volunteer_registration_revision);
    return $this->t('Revision of %title from %date', ['%title' => $volunteer_registration->label(), '%date' => format_date($volunteer_registration->getRevisionCreationTime())]);
  }

  /**
   * Generates an overview table of older revisions of a Volunteer registration .
   *
   * @param \Drupal\service_club_event\Entity\VolunteerRegistrationInterface $volunteer_registration
   *   A Volunteer registration  object.
   *
   * @return array
   *   An array as expected by drupal_render().
   */
  public function revisionOverview(VolunteerRegistrationInterface $volunteer_registration) {
    $account = $this->currentUser();
    $langcode = $volunteer_registration->language()->getId();
    $langname = $volunteer_registration->language()->getName();
    $languages = $volunteer_registration->getTranslationLanguages();
    $has_translations = (count($languages) > 1);
    $volunteer_registration_storage = $this->entityManager()->getStorage('volunteer_registration');

    $build['#title'] = $has_translations ? $this->t('@langname revisions for %title', ['@langname' => $langname, '%title' => $volunteer_registration->label()]) : $this->t('Revisions for %title', ['%title' => $volunteer_registration->label()]);
    $header = [$this->t('Revision'), $this->t('Operations')];

    $revert_permission = (($account->hasPermission("revert all volunteer registration revisions") || $account->hasPermission('administer volunteer registration entities')));
    $delete_permission = (($account->hasPermission("delete all volunteer registration revisions") || $account->hasPermission('administer volunteer registration entities')));

    $rows = [];

    $vids = $volunteer_registration_storage->revisionIds($volunteer_registration);

    $latest_revision = TRUE;

    foreach (array_reverse($vids) as $vid) {
      /** @var \Drupal\service_club_event\VolunteerRegistrationInterface $revision */
      $revision = $volunteer_registration_storage->loadRevision($vid);
      // Only show revisions that are affected by the language that is being
      // displayed.
      if ($revision->hasTranslation($langcode) && $revision->getTranslation($langcode)->isRevisionTranslationAffected()) {
        $username = [
          '#theme' => 'username',
          '#account' => $revision->getRevisionUser(),
        ];

        // Use revision link to link to revisions that are not active.
        $date = \Drupal::service('date.formatter')->format($revision->getRevisionCreationTime(), 'short');
        if ($vid != $volunteer_registration->getRevisionId()) {
          $link = $this->l($date, new Url('entity.volunteer_registration.revision', ['volunteer_registration' => $volunteer_registration->id(), 'volunteer_registration_revision' => $vid]));
        }
        else {
          $link = $volunteer_registration->link($date);
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
              Url::fromRoute('entity.volunteer_registration.translation_revert', ['volunteer_registration' => $volunteer_registration->id(), 'volunteer_registration_revision' => $vid, 'langcode' => $langcode]) :
              Url::fromRoute('entity.volunteer_registration.revision_revert', ['volunteer_registration' => $volunteer_registration->id(), 'volunteer_registration_revision' => $vid]),
            ];
          }

          if ($delete_permission) {
            $links['delete'] = [
              'title' => $this->t('Delete'),
              'url' => Url::fromRoute('entity.volunteer_registration.revision_delete', ['volunteer_registration' => $volunteer_registration->id(), 'volunteer_registration_revision' => $vid]),
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

    $build['volunteer_registration_revisions_table'] = [
      '#theme' => 'table',
      '#rows' => $rows,
      '#header' => $header,
    ];

    return $build;
  }

}
