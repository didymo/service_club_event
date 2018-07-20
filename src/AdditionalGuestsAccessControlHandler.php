<?php

namespace Drupal\service_club_event;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the Additional guests entity.
 *
 * @see \Drupal\service_club_event\Entity\AdditionalGuests.
 */
class AdditionalGuestsAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\service_club_event\Entity\AdditionalGuestsInterface $entity */
    switch ($operation) {
      case 'view':
        if (!$entity->isPublished()) {
          return AccessResult::allowedIfHasPermission($account, 'view unpublished additional guests entities');
        }
        return AccessResult::allowedIfHasPermission($account, 'view published additional guests entities');

      case 'update':
        return AccessResult::allowedIfHasPermission($account, 'edit additional guests entities');

      case 'delete':
        return AccessResult::allowedIfHasPermission($account, 'delete additional guests entities');
    }

    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add additional guests entities');
  }

}
