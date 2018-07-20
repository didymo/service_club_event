<?php

namespace Drupal\service_club_event;

use Drupal\Core\Entity\ContentEntityStorageInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\service_club_event\Entity\EventInformationInterface;

/**
 * Defines the storage handler class for Event information entities.
 *
 * This extends the base storage class, adding required special handling for
 * Event information entities.
 *
 * @ingroup service_club_event
 */
interface EventInformationStorageInterface extends ContentEntityStorageInterface {

  /**
   * Gets a list of Event information revision IDs for a specific Event information.
   *
   * @param \Drupal\service_club_event\Entity\EventInformationInterface $entity
   *   The Event information entity.
   *
   * @return int[]
   *   Event information revision IDs (in ascending order).
   */
  public function revisionIds(EventInformationInterface $entity);

  /**
   * Gets a list of revision IDs having a given user as Event information author.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user entity.
   *
   * @return int[]
   *   Event information revision IDs (in ascending order).
   */
  public function userRevisionIds(AccountInterface $account);

  /**
   * Counts the number of revisions in the default language.
   *
   * @param \Drupal\service_club_event\Entity\EventInformationInterface $entity
   *   The Event information entity.
   *
   * @return int
   *   The number of revisions in the default language.
   */
  public function countDefaultLanguageRevisions(EventInformationInterface $entity);

  /**
   * Unsets the language for all Event information with the given language.
   *
   * @param \Drupal\Core\Language\LanguageInterface $language
   *   The language object.
   */
  public function clearRevisionsLanguage(LanguageInterface $language);

}
