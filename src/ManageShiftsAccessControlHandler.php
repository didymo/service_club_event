<?php

namespace Drupal\service_club_event;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the Manage shifts entity.
 *
 * @see \Drupal\service_club_event\Entity\ManageShifts.
 */
class ManageShiftsAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\service_club_event\Entity\ManageShiftsInterface $entity */
    switch ($operation) {
      case 'view':
        if (!$entity->isPublished()) {
          return AccessResult::allowedIfHasPermission($account, 'view unpublished manage shifts entities');
        }
        return AccessResult::allowedIfHasPermission($account, 'view published manage shifts entities');

      case 'update':
        return AccessResult::allowedIfHasPermission($account, 'edit manage shifts entities');

      case 'delete':
        return AccessResult::allowedIfHasPermission($account, 'delete manage shifts entities');
    }

    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add manage shifts entities');
  }

}
