<?php

namespace Drupal\service_club_event;

use Drupal\Core\Entity\Sql\SqlContentEntityStorage;
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
class VolunteerRegistrationStorage extends SqlContentEntityStorage implements VolunteerRegistrationStorageInterface {

  /**
   * {@inheritdoc}
   */
  public function revisionIds(VolunteerRegistrationInterface $entity) {
    return $this->database->query(
      'SELECT vid FROM {volunteer_registration_revision} WHERE id=:id ORDER BY vid',
      [':id' => $entity->id()]
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function userRevisionIds(AccountInterface $account) {
    return $this->database->query(
      'SELECT vid FROM {volunteer_registration_field_revision} WHERE uid = :uid ORDER BY vid',
      [':uid' => $account->id()]
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function countDefaultLanguageRevisions(VolunteerRegistrationInterface $entity) {
    return $this->database->query('SELECT COUNT(*) FROM {volunteer_registration_field_revision} WHERE id = :id AND default_langcode = 1', [':id' => $entity->id()])
      ->fetchField();
  }

  /**
   * {@inheritdoc}
   */
  public function clearRevisionsLanguage(LanguageInterface $language) {
    return $this->database->update('volunteer_registration_revision')
      ->fields(['langcode' => LanguageInterface::LANGCODE_NOT_SPECIFIED])
      ->condition('langcode', $language->getId())
      ->execute();
  }

}
