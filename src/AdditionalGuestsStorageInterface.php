<?php

namespace Drupal\service_club_event;

use Drupal\Core\Entity\ContentEntityStorageInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\service_club_event\Entity\AdditionalGuestsInterface;

/**
 * Defines the storage handler class for Additional guests entities.
 *
 * This extends the base storage class, adding required special handling for
 * Additional guests entities.
 *
 * @ingroup service_club_event
 */
interface AdditionalGuestsStorageInterface extends ContentEntityStorageInterface {

  /**
   * Gets a list of Additional guests revision IDs for a specific Additional guests.
   *
   * @param \Drupal\service_club_event\Entity\AdditionalGuestsInterface $entity
   *   The Additional guests entity.
   *
   * @return int[]
   *   Additional guests revision IDs (in ascending order).
   */
  public function revisionIds(AdditionalGuestsInterface $entity);

  /**
   * Gets a list of revision IDs having a given user as Additional guests author.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user entity.
   *
   * @return int[]
   *   Additional guests revision IDs (in ascending order).
   */
  public function userRevisionIds(AccountInterface $account);

  /**
   * Counts the number of revisions in the default language.
   *
   * @param \Drupal\service_club_event\Entity\AdditionalGuestsInterface $entity
   *   The Additional guests entity.
   *
   * @return int
   *   The number of revisions in the default language.
   */
  public function countDefaultLanguageRevisions(AdditionalGuestsInterface $entity);

  /**
   * Unsets the language for all Additional guests with the given language.
   *
   * @param \Drupal\Core\Language\LanguageInterface $language
   *   The language object.
   */
  public function clearRevisionsLanguage(LanguageInterface $language);

}
