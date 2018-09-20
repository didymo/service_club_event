<?php

namespace Drupal\service_club_event\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\RevisionLogInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Volunteer registration entities.
 *
 * @ingroup service_club_event
 */
interface VolunteerRegistrationInterface extends ContentEntityInterface, RevisionLogInterface, EntityChangedInterface, EntityOwnerInterface {

  // Add get/set methods for your configuration properties here.

  /**
   * Gets the Volunteer registration name.
   *
   * @return string
   *   Name of the Volunteer registration.
   */
  public function getName();

  /**
   * Sets the Volunteer registration name.
   *
   * @param string $name
   *   The Volunteer registration name.
   *
   * @return \Drupal\service_club_event\Entity\VolunteerRegistrationInterface
   *   The called Volunteer registration entity.
   */
  public function setName($name);

  /**
   * Gets the Volunteer registration creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Volunteer registration.
   */
  public function getCreatedTime();

  /**
   * Sets the Volunteer registration creation timestamp.
   *
   * @param int $timestamp
   *   The Volunteer registration creation timestamp.
   *
   * @return \Drupal\service_club_event\Entity\VolunteerRegistrationInterface
   *   The called Volunteer registration entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Returns the Volunteer registration published status indicator.
   *
   * Unpublished Volunteer registration are only visible to restricted users.
   *
   * @return bool
   *   TRUE if the Volunteer registration is published.
   */
  public function isPublished();

  /**
   * Sets the published status of a Volunteer registration.
   *
   * @param bool $published
   *   TRUE to set this Volunteer registration to published, FALSE to set it to unpublished.
   *
   * @return \Drupal\service_club_event\Entity\VolunteerRegistrationInterface
   *   The called Volunteer registration entity.
   */
  public function setPublished($published);

  /**
   * Gets the Volunteer registration revision creation timestamp.
   *
   * @return int
   *   The UNIX timestamp of when this revision was created.
   */
  public function getRevisionCreationTime();

  /**
   * Sets the Volunteer registration revision creation timestamp.
   *
   * @param int $timestamp
   *   The UNIX timestamp of when this revision was created.
   *
   * @return \Drupal\service_club_event\Entity\VolunteerRegistrationInterface
   *   The called Volunteer registration entity.
   */
  public function setRevisionCreationTime($timestamp);

  /**
   * Gets the Volunteer registration revision author.
   *
   * @return \Drupal\user\UserInterface
   *   The user entity for the revision author.
   */
  public function getRevisionUser();

  /**
   * Sets the Volunteer registration revision author.
   *
   * @param int $uid
   *   The user ID of the revision author.
   *
   * @return \Drupal\service_club_event\Entity\VolunteerRegistrationInterface
   *   The called Volunteer registration entity.
   */
  public function setRevisionUserId($uid);

}
