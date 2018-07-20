<?php

namespace Drupal\service_club_event\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\RevisionLogInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Additional guests entities.
 *
 * @ingroup service_club_event
 */
interface AdditionalGuestsInterface extends ContentEntityInterface, RevisionLogInterface, EntityChangedInterface, EntityOwnerInterface {

  // Add get/set methods for your configuration properties here.

  /**
   * Gets the Additional guests name.
   *
   * @return string
   *   Name of the Additional guests.
   */
  public function getName();

  /**
   * Sets the Additional guests name.
   *
   * @param string $name
   *   The Additional guests name.
   *
   * @return \Drupal\service_club_event\Entity\AdditionalGuestsInterface
   *   The called Additional guests entity.
   */
  public function setName($name);

  /**
   * Gets the Additional guests creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Additional guests.
   */
  public function getCreatedTime();

  /**
   * Sets the Additional guests creation timestamp.
   *
   * @param int $timestamp
   *   The Additional guests creation timestamp.
   *
   * @return \Drupal\service_club_event\Entity\AdditionalGuestsInterface
   *   The called Additional guests entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Returns the Additional guests published status indicator.
   *
   * Unpublished Additional guests are only visible to restricted users.
   *
   * @return bool
   *   TRUE if the Additional guests is published.
   */
  public function isPublished();

  /**
   * Sets the published status of a Additional guests.
   *
   * @param bool $published
   *   TRUE to set this Additional guests to published, FALSE to set it to unpublished.
   *
   * @return \Drupal\service_club_event\Entity\AdditionalGuestsInterface
   *   The called Additional guests entity.
   */
  public function setPublished($published);

  /**
   * Gets the Additional guests revision creation timestamp.
   *
   * @return int
   *   The UNIX timestamp of when this revision was created.
   */
  public function getRevisionCreationTime();

  /**
   * Sets the Additional guests revision creation timestamp.
   *
   * @param int $timestamp
   *   The UNIX timestamp of when this revision was created.
   *
   * @return \Drupal\service_club_event\Entity\AdditionalGuestsInterface
   *   The called Additional guests entity.
   */
  public function setRevisionCreationTime($timestamp);

  /**
   * Gets the Additional guests revision author.
   *
   * @return \Drupal\user\UserInterface
   *   The user entity for the revision author.
   */
  public function getRevisionUser();

  /**
   * Sets the Additional guests revision author.
   *
   * @param int $uid
   *   The user ID of the revision author.
   *
   * @return \Drupal\service_club_event\Entity\AdditionalGuestsInterface
   *   The called Additional guests entity.
   */
  public function setRevisionUserId($uid);

}
