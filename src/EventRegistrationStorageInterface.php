<?php

namespace Drupal\service_club_event;

use Drupal\Core\Entity\ContentEntityStorageInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\service_club_event\Entity\EventRegistrationInterface;

/**
 * Defines the storage handler class for Event registration entities.
 *
 * This extends the base storage class, adding required special handling for
 * Event registration entities.
 *
 * @ingroup service_club_event
 */
interface EventRegistrationStorageInterface extends ContentEntityStorageInterface {

  /**
   * Gets a list of Event registration revision IDs for a specific Event registration.
   *
   * @param \Drupal\service_club_event\Entity\EventRegistrationInterface $entity
   *   The Event registration entity.
   *
   * @return int[]
   *   Event registration revision IDs (in ascending order).
   */
  public function revisionIds(EventRegistrationInterface $entity);

  /**
   * Gets a list of revision IDs having a given user as Event registration author.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user entity.
   *
   * @return int[]
   *   Event registration revision IDs (in ascending order).
   */
  public function userRevisionIds(AccountInterface $account);

  /**
   * Counts the number of revisions in the default language.
   *
   * @param \Drupal\service_club_event\Entity\EventRegistrationInterface $entity
   *   The Event registration entity.
   *
   * @return int
   *   The number of revisions in the default language.
   */
  public function countDefaultLanguageRevisions(EventRegistrationInterface $entity);

  /**
   * Unsets the language for all Event registration with the given language.
   *
   * @param \Drupal\Core\Language\LanguageInterface $language
   *   The language object.
   */
  public function clearRevisionsLanguage(LanguageInterface $language);

}
