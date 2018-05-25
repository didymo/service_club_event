<?php

namespace Drupal\service_club_event;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the Event info entity entity.
 *
 * @see \Drupal\service_club_event\Entity\EventInfoEntity.
 */
class EventInfoEntityAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\service_club_event\Entity\EventInfoEntityInterface $entity */
    switch ($operation) {
      case 'view':
        if (!$entity->isPublished()) {
          return AccessResult::allowedIfHasPermission($account, 'view unpublished event info entity entities');
        }
        return AccessResult::allowedIfHasPermission($account, 'view published event info entity entities');

      case 'update':
        return AccessResult::allowedIfHasPermission($account, 'edit event info entity entities');

      case 'delete':
        return AccessResult::allowedIfHasPermission($account, 'delete event info entity entities');
    }

    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add event info entity entities');
  }

}
