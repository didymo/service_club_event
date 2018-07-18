<?php

namespace Drupal\service_club_event;

use Drupal\Core\Entity\ContentEntityStorageInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\service_club_event\Entity\AdditionalGuestInterface;

/**
 * Defines the storage handler class for Additional guest entities.
 *
 * This extends the base storage class, adding required special handling for
 * Additional guest entities.
 *
 * @ingroup service_club_event
 */
interface AdditionalGuestStorageInterface extends ContentEntityStorageInterface {

  /**
   * Gets a list of Additional guest revision IDs for a specific Additional guest.
   *
   * @param \Drupal\service_club_event\Entity\AdditionalGuestInterface $entity
   *   The Additional guest entity.
   *
   * @return int[]
   *   Additional guest revision IDs (in ascending order).
   */
  public function revisionIds(AdditionalGuestInterface $entity);

  /**
   * Gets a list of revision IDs having a given user as Additional guest author.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user entity.
   *
   * @return int[]
   *   Additional guest revision IDs (in ascending order).
   */
  public function userRevisionIds(AccountInterface $account);

  /**
   * Counts the number of revisions in the default language.
   *
   * @param \Drupal\service_club_event\Entity\AdditionalGuestInterface $entity
   *   The Additional guest entity.
   *
   * @return int
   *   The number of revisions in the default language.
   */
  public function countDefaultLanguageRevisions(AdditionalGuestInterface $entity);

  /**
   * Unsets the language for all Additional guest with the given language.
   *
   * @param \Drupal\Core\Language\LanguageInterface $language
   *   The language object.
   */
  public function clearRevisionsLanguage(LanguageInterface $language);

}
