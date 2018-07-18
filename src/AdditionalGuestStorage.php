<?php

namespace Drupal\service_club_event;

use Drupal\Core\Entity\Sql\SqlContentEntityStorage;
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
class AdditionalGuestStorage extends SqlContentEntityStorage implements AdditionalGuestStorageInterface {

  /**
   * {@inheritdoc}
   */
  public function revisionIds(AdditionalGuestInterface $entity) {
    return $this->database->query(
      'SELECT vid FROM {additional_guest_revision} WHERE id=:id ORDER BY vid',
      [':id' => $entity->id()]
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function userRevisionIds(AccountInterface $account) {
    return $this->database->query(
      'SELECT vid FROM {additional_guest_field_revision} WHERE uid = :uid ORDER BY vid',
      [':uid' => $account->id()]
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function countDefaultLanguageRevisions(AdditionalGuestInterface $entity) {
    return $this->database->query('SELECT COUNT(*) FROM {additional_guest_field_revision} WHERE id = :id AND default_langcode = 1', [':id' => $entity->id()])
      ->fetchField();
  }

  /**
   * {@inheritdoc}
   */
  public function clearRevisionsLanguage(LanguageInterface $language) {
    return $this->database->update('additional_guest_revision')
      ->fields(['langcode' => LanguageInterface::LANGCODE_NOT_SPECIFIED])
      ->condition('langcode', $language->getId())
      ->execute();
  }

}
