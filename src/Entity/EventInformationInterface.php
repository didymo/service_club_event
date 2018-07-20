<?php

namespace Drupal\service_club_event\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\RevisionLogInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Event information entities.
 *
 * @ingroup service_club_event
 */
interface EventInformationInterface extends ContentEntityInterface, RevisionLogInterface, EntityChangedInterface, EntityOwnerInterface {

  // Add get/set methods for your configuration properties here.

  /**
   * Gets the Event information name.
   *
   * @return string
   *   Name of the Event information.
   */
  public function getName();

  /**
   * Sets the Event information name.
   *
   * @param string $name
   *   The Event information name.
   *
   * @return \Drupal\service_club_event\Entity\EventInformationInterface
   *   The called Event information entity.
   */
  public function setName($name);

  /**
   * Gets the Event information creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Event information.
   */
  public function getCreatedTime();

  /**
   * Sets the Event information creation timestamp.
   *
   * @param int $timestamp
   *   The Event information creation timestamp.
   *
   * @return \Drupal\service_club_event\Entity\EventInformationInterface
   *   The called Event information entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Returns the Event information published status indicator.
   *
   * Unpublished Event information are only visible to restricted users.
   *
   * @return bool
   *   TRUE if the Event information is published.
   */
  public function isPublished();

  /**
   * Sets the published status of a Event information.
   *
   * @param bool $published
   *   TRUE to set this Event information to published, FALSE to set it to unpublished.
   *
   * @return \Drupal\service_club_event\Entity\EventInformationInterface
   *   The called Event information entity.
   */
  public function setPublished($published);

  /**
   * Gets the Event information revision creation timestamp.
   *
   * @return int
   *   The UNIX timestamp of when this revision was created.
   */
  public function getRevisionCreationTime();

  /**
   * Sets the Event information revision creation timestamp.
   *
   * @param int $timestamp
   *   The UNIX timestamp of when this revision was created.
   *
   * @return \Drupal\service_club_event\Entity\EventInformationInterface
   *   The called Event information entity.
   */
  public function setRevisionCreationTime($timestamp);

  /**
   * Gets the Event information revision author.
   *
   * @return \Drupal\user\UserInterface
   *   The user entity for the revision author.
   */
  public function getRevisionUser();

  /**
   * Sets the Event information revision author.
   *
   * @param int $uid
   *   The user ID of the revision author.
   *
   * @return \Drupal\service_club_event\Entity\EventInformationInterface
   *   The called Event information entity.
   */
  public function setRevisionUserId($uid);

}
