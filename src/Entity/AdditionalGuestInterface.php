<?php

namespace Drupal\service_club_event\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\RevisionLogInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Additional guest entities.
 *
 * @ingroup service_club_event
 */
interface AdditionalGuestInterface extends ContentEntityInterface, RevisionLogInterface, EntityChangedInterface, EntityOwnerInterface {

  // Add get/set methods for your configuration properties here.

  /**
   * Gets the Additional guest name.
   *
   * @return string
   *   Name of the Additional guest.
   */
  public function getName();

  /**
   * Sets the Additional guest name.
   *
   * @param string $name
   *   The Additional guest name.
   *
   * @return \Drupal\service_club_event\Entity\AdditionalGuestInterface
   *   The called Additional guest entity.
   */
  public function setName($name);

  /**
   * Gets the Additional guest creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Additional guest.
   */
  public function getCreatedTime();

  /**
   * Sets the Additional guest creation timestamp.
   *
   * @param int $timestamp
   *   The Additional guest creation timestamp.
   *
   * @return \Drupal\service_club_event\Entity\AdditionalGuestInterface
   *   The called Additional guest entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Returns the Additional guest published status indicator.
   *
   * Unpublished Additional guest are only visible to restricted users.
   *
   * @return bool
   *   TRUE if the Additional guest is published.
   */
  public function isPublished();

  /**
   * Sets the published status of a Additional guest.
   *
   * @param bool $published
   *   TRUE to set this Additional guest to published, FALSE to set it to unpublished.
   *
   * @return \Drupal\service_club_event\Entity\AdditionalGuestInterface
   *   The called Additional guest entity.
   */
  public function setPublished($published);

  /**
   * Gets the Additional guest revision creation timestamp.
   *
   * @return int
   *   The UNIX timestamp of when this revision was created.
   */
  public function getRevisionCreationTime();

  /**
   * Sets the Additional guest revision creation timestamp.
   *
   * @param int $timestamp
   *   The UNIX timestamp of when this revision was created.
   *
   * @return \Drupal\service_club_event\Entity\AdditionalGuestInterface
   *   The called Additional guest entity.
   */
  public function setRevisionCreationTime($timestamp);

  /**
   * Gets the Additional guest revision author.
   *
   * @return \Drupal\user\UserInterface
   *   The user entity for the revision author.
   */
  public function getRevisionUser();

  /**
   * Sets the Additional guest revision author.
   *
   * @param int $uid
   *   The user ID of the revision author.
   *
   * @return \Drupal\service_club_event\Entity\AdditionalGuestInterface
   *   The called Additional guest entity.
   */
  public function setRevisionUserId($uid);

}
