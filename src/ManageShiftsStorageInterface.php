<?php

namespace Drupal\service_club_event;

use Drupal\Core\Entity\ContentEntityStorageInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\service_club_event\Entity\ManageShiftsInterface;

/**
 * Defines the storage handler class for Manage shifts entities.
 *
 * This extends the base storage class, adding required special handling for
 * Manage shifts entities.
 *
 * @ingroup service_club_event
 */
interface ManageShiftsStorageInterface extends ContentEntityStorageInterface {

  /**
   * Gets a list of Manage shifts revision IDs for a specific Manage shifts.
   *
   * @param \Drupal\service_club_event\Entity\ManageShiftsInterface $entity
   *   The Manage shifts entity.
   *
   * @return int[]
   *   Manage shifts revision IDs (in ascending order).
   */
  public function revisionIds(ManageShiftsInterface $entity);

  /**
   * Gets a list of revision IDs having a given user as Manage shifts author.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user entity.
   *
   * @return int[]
   *   Manage shifts revision IDs (in ascending order).
   */
  public function userRevisionIds(AccountInterface $account);

  /**
   * Counts the number of revisions in the default language.
   *
   * @param \Drupal\service_club_event\Entity\ManageShiftsInterface $entity
   *   The Manage shifts entity.
   *
   * @return int
   *   The number of revisions in the default language.
   */
  public function countDefaultLanguageRevisions(ManageShiftsInterface $entity);

  /**
   * Unsets the language for all Manage shifts with the given language.
   *
   * @param \Drupal\Core\Language\LanguageInterface $language
   *   The language object.
   */
  public function clearRevisionsLanguage(LanguageInterface $language);

}
