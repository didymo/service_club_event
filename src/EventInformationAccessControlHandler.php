<?php

namespace Drupal\service_club_event;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the Event information entity.
 *
 * @see \Drupal\service_club_event\Entity\EventInformation.
 */
class EventInformationAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\service_club_event\Entity\EventInformationInterface $entity */
    switch ($operation) {
      case 'view':
        if (!$entity->isPublished()) {
          return AccessResult::allowedIfHasPermission($account, 'view unpublished event information entities');
        }
        return AccessResult::allowedIfHasPermission($account, 'view published event information entities');

      case 'update':
        return AccessResult::allowedIfHasPermission($account, 'edit event information entities');

      case 'asset-list':
        return AccessResult::allowedIfHasPermission($account, 'list assets information entities');

      case 'shift-list':
        return AccessResult::allowedIfHasPermission($account, 'list shift information entities');

      case 'delete':
        return AccessResult::allowedIfHasPermission($account, 'delete event information entities');

    }

    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add event information entities');
  }

}
