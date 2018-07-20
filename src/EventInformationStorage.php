<?php

namespace Drupal\service_club_event;

use Drupal\Core\Entity\Sql\SqlContentEntityStorage;
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
class EventInformationStorage extends SqlContentEntityStorage implements EventInformationStorageInterface {

  /**
   * {@inheritdoc}
   */
  public function revisionIds(EventInformationInterface $entity) {
    return $this->database->query(
      'SELECT vid FROM {event_information_revision} WHERE id=:id ORDER BY vid',
      [':id' => $entity->id()]
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function userRevisionIds(AccountInterface $account) {
    return $this->database->query(
      'SELECT vid FROM {event_information_field_revision} WHERE uid = :uid ORDER BY vid',
      [':uid' => $account->id()]
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function countDefaultLanguageRevisions(EventInformationInterface $entity) {
    return $this->database->query('SELECT COUNT(*) FROM {event_information_field_revision} WHERE id = :id AND default_langcode = 1', [':id' => $entity->id()])
      ->fetchField();
  }

  /**
   * {@inheritdoc}
   */
  public function clearRevisionsLanguage(LanguageInterface $language) {
    return $this->database->update('event_information_revision')
      ->fields(['langcode' => LanguageInterface::LANGCODE_NOT_SPECIFIED])
      ->condition('langcode', $language->getId())
      ->execute();
  }

}
