<?php

namespace Drupal\service_club_event;

use Drupal\Core\Entity\ContentEntityStorageInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\service_club_event\Entity\EventInfoEntityInterface;

/**
 * Defines the storage handler class for Event info entity entities.
 *
 * This extends the base storage class, adding required special handling for
 * Event info entity entities.
 *
 * @ingroup service_club_event
 */
interface EventInfoEntityStorageInterface extends ContentEntityStorageInterface {

  /**
   * Gets a list of Event info entity revision IDs for a specific Event info entity.
   *
   * @param \Drupal\service_club_event\Entity\EventInfoEntityInterface $entity
   *   The Event info entity entity.
   *
   * @return int[]
   *   Event info entity revision IDs (in ascending order).
   */
  public function revisionIds(EventInfoEntityInterface $entity);

  /**
   * Gets a list of revision IDs having a given user as Event info entity author.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user entity.
   *
   * @return int[]
   *   Event info entity revision IDs (in ascending order).
   */
  public function userRevisionIds(AccountInterface $account);

  /**
   * Counts the number of revisions in the default language.
   *
   * @param \Drupal\service_club_event\Entity\EventInfoEntityInterface $entity
   *   The Event info entity entity.
   *
   * @return int
   *   The number of revisions in the default language.
   */
  public function countDefaultLanguageRevisions(EventInfoEntityInterface $entity);

  /**
   * Unsets the language for all Event info entity with the given language.
   *
   * @param \Drupal\Core\Language\LanguageInterface $language
   *   The language object.
   */
  public function clearRevisionsLanguage(LanguageInterface $language);

}
