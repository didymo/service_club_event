<?php

namespace Drupal\service_club_event;

use Drupal\Core\Entity\Sql\SqlContentEntityStorage;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\service_club_event\Entity\EventInformation;
use Drupal\service_club_event\Entity\ManageShiftsInterface;

/**
 * Defines the storage handler class for Manage shifts entities.
 *
 * This extends the base storage class, adding required special handling for
 * Manage shifts entities.
 *
 * @ingroup service_club_event
 */
class ManageShiftsStorage extends SqlContentEntityStorage implements ManageShiftsStorageInterface {

  /**
   * {@inheritdoc}
   */
  public function revisionIds(ManageShiftsInterface $entity) {
    return $this->database->query(
      'SELECT vid FROM {manage_shifts_revision} WHERE id=:id ORDER BY vid',
      [':id' => $entity->id()]
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function userRevisionIds(AccountInterface $account) {
    return $this->database->query(
      'SELECT vid FROM {manage_shifts_field_revision} WHERE uid = :uid ORDER BY vid',
      [':uid' => $account->id()]
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function countDefaultLanguageRevisions(ManageShiftsInterface $entity) {
    return $this->database->query('SELECT COUNT(*) FROM {manage_shifts_field_revision} WHERE id = :id AND default_langcode = 1', [':id' => $entity->id()])
      ->fetchField();
  }

  /**
   * {@inheritdoc}
   */
  public function clearRevisionsLanguage(LanguageInterface $language) {
    return $this->database->update('manage_shifts_revision')
      ->fields(['langcode' => LanguageInterface::LANGCODE_NOT_SPECIFIED])
      ->condition('langcode', $language->getId())
      ->execute();
  }

    /**
     * {@inheritdoc}
     */
    public function getShifts($eid){
        $event = EventInformation::load($eid);
        return $event->getShifts();

    }
}
