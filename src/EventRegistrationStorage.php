<?php

namespace Drupal\service_club_event;

use Drupal\Core\Entity\Sql\SqlContentEntityStorage;
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
class EventRegistrationStorage extends SqlContentEntityStorage implements EventRegistrationStorageInterface {

  /**
   * {@inheritdoc}
   */
  public function revisionIds(EventRegistrationInterface $entity) {
    return $this->database->query(
      'SELECT vid FROM {event_registration_revision} WHERE id=:id ORDER BY vid',
      [':id' => $entity->id()]
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function userRevisionIds(AccountInterface $account) {
    return $this->database->query(
      'SELECT vid FROM {event_registration_field_revision} WHERE uid = :uid ORDER BY vid',
      [':uid' => $account->id()]
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function countDefaultLanguageRevisions(EventRegistrationInterface $entity) {
    return $this->database->query('SELECT COUNT(*) FROM {event_registration_field_revision} WHERE id = :id AND default_langcode = 1', [':id' => $entity->id()])
      ->fetchField();
  }

  /**
   * {@inheritdoc}
   */
  public function clearRevisionsLanguage(LanguageInterface $language) {
    return $this->database->update('event_registration_revision')
      ->fields(['langcode' => LanguageInterface::LANGCODE_NOT_SPECIFIED])
      ->condition('langcode', $language->getId())
      ->execute();
  }

}
