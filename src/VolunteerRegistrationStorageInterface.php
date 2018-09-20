<?php

namespace Drupal\service_club_event;

use Drupal\Core\Entity\ContentEntityStorageInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\service_club_event\Entity\VolunteerRegistrationInterface;

/**
 * Defines the storage handler class for Volunteer registration entities.
 *
 * This extends the base storage class, adding required special handling for
 * Volunteer registration entities.
 *
 * @ingroup service_club_event
 */
interface VolunteerRegistrationStorageInterface extends ContentEntityStorageInterface {

  /**
   * Gets a list of Volunteer registration revision IDs for a specific Volunteer registration.
   *
   * @param \Drupal\service_club_event\Entity\VolunteerRegistrationInterface $entity
   *   The Volunteer registration entity.
   *
   * @return int[]
   *   Volunteer registration revision IDs (in ascending order).
   */
  public function revisionIds(VolunteerRegistrationInterface $entity);

  /**
   * Gets a list of revision IDs having a given user as Volunteer registration author.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user entity.
   *
   * @return int[]
   *   Volunteer registration revision IDs (in ascending order).
   */
  public function userRevisionIds(AccountInterface $account);

  /**
   * Counts the number of revisions in the default language.
   *
   * @param \Drupal\service_club_event\Entity\VolunteerRegistrationInterface $entity
   *   The Volunteer registration entity.
   *
   * @return int
   *   The number of revisions in the default language.
   */
  public function countDefaultLanguageRevisions(VolunteerRegistrationInterface $entity);

  /**
   * Unsets the language for all Volunteer registration with the given language.
   *
   * @param \Drupal\Core\Language\LanguageInterface $language
   *   The language object.
   */
  public function clearRevisionsLanguage(LanguageInterface $language);

}
