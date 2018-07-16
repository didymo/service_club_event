<?php

namespace Drupal\service_club_event\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\RevisionLogInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Event registration entities.
 *
 * @ingroup service_club_event
 */
interface EventRegistrationInterface extends ContentEntityInterface, RevisionLogInterface, EntityChangedInterface, EntityOwnerInterface {

  // Add get/set methods for your configuration properties here.

  /**
   * Gets the Event registration name.
   *
   * @return string
   *   Name of the Event registration.
   */
  public function getName();

  /**
   * Sets the Event registration name.
   *
   * @param string $name
   *   The Event registration name.
   *
   * @return \Drupal\service_club_event\Entity\EventRegistrationInterface
   *   The called Event registration entity.
   */
  public function setName($name);

  /**
   * Gets the Event registration creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Event registration.
   */
  public function getCreatedTime();

  /**
   * Sets the Event registration creation timestamp.
   *
   * @param int $timestamp
   *   The Event registration creation timestamp.
   *
   * @return \Drupal\service_club_event\Entity\EventRegistrationInterface
   *   The called Event registration entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Returns the Event registration published status indicator.
   *
   * Unpublished Event registration are only visible to restricted users.
   *
   * @return bool
   *   TRUE if the Event registration is published.
   */
  public function isPublished();

  /**
   * Sets the published status of a Event registration.
   *
   * @param bool $published
   *   TRUE to set this Event registration to published, FALSE to set it to unpublished.
   *
   * @return \Drupal\service_club_event\Entity\EventRegistrationInterface
   *   The called Event registration entity.
   */
  public function setPublished($published);

  /**
   * Gets the Event registration revision creation timestamp.
   *
   * @return int
   *   The UNIX timestamp of when this revision was created.
   */
  public function getRevisionCreationTime();

  /**
   * Sets the Event registration revision creation timestamp.
   *
   * @param int $timestamp
   *   The UNIX timestamp of when this revision was created.
   *
   * @return \Drupal\service_club_event\Entity\EventRegistrationInterface
   *   The called Event registration entity.
   */
  public function setRevisionCreationTime($timestamp);

  /**
   * Gets the Event registration revision author.
   *
   * @return \Drupal\user\UserInterface
   *   The user entity for the revision author.
   */
  public function getRevisionUser();

  /**
   * Sets the Event registration revision author.
   *
   * @param int $uid
   *   The user ID of the revision author.
   *
   * @return \Drupal\service_club_event\Entity\EventRegistrationInterface
   *   The called Event registration entity.
   */
  public function setRevisionUserId($uid);

}
