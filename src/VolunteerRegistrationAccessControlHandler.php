<?php

namespace Drupal\service_club_event;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the Volunteer registration entity.
 *
 * @see \Drupal\service_club_event\Entity\VolunteerRegistration.
 */
class VolunteerRegistrationAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\service_club_event\Entity\VolunteerRegistrationInterface $entity */
    switch ($operation) {
      case 'view':
        if (!$entity->isPublished()) {
          return AccessResult::allowedIfHasPermission($account, 'view unpublished volunteer registration entities');
        }
        return AccessResult::allowedIfHasPermission($account, 'view published volunteer registration entities');

      case 'update':
        return AccessResult::allowedIfHasPermission($account, 'edit volunteer registration entities');

      case 'delete':
        return AccessResult::allowedIfHasPermission($account, 'delete volunteer registration entities');
    }

    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add volunteer registration entities');
  }

}
