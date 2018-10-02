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

  /**
   * Adds the Event shifts to the array for the respective event.
   *
   * @param string $shift_id
   *   The id of the shift to be added to the array.
   */
  public function addShift($shift_id);

  /**
   * Checks if a user is already registered to the selected event.
   *
   * @param string $uid
   *   The id of the user being checked.
   *
   * @return \Drupal\service_club_event\Entity\VolunteerRegistrationInterface
   *   Returns the users registration object, if exists or NULL if does not.
   */
  public function isRegistered($uid);

  /**
   * Gets the Event shifts.
   *
   * @return \Drupal\service_club_event\Entity\ManageShiftsInterface[]
   *   Name of the Event information.
   */
  public function getShifts();

  /**
   * Gets the Event information name.
   *
   * @return string
   *   Name of the Event information.
   */
  public function getName();

  /**
   * Gets the Event Class that the Questionnaire evaluated to.
   *
   * @return \Drupal\service_club_tmp\Entity\EventClass
   *   The Event's Event Class, NULL if the Questionnaire hasn't been completed.
   */
  public function getEventClass();

  /**
   * Gets the Event Class that the Questionnaire evaluated to.
   *
   * @param string $event_class
   *   The Event Class id to be attached to the Event.
   *
   * @return \Drupal\service_club_tmp\Entity\EventClass
   *   The Event's Event Class, NULL if the Questionnaire hasn't been completed.
   */
  public function setEventClass($event_class);

  /**
   * Gets the Event's Traffic Management Plan.
   *
   * @return \Drupal\service_club_tmp\Entity\TrafficManagementPlan
   *   The Event's Traffic Management Plan entity, NULL if not yet created.
   */
  public function getTrafficManagementPlan();

  /**
   * Adds the Traffic Management Plan to the Event.
   *
   * @param \Drupal\service_club_tmp\Entity\TrafficManagementPlan $tmp
   *   The Traffic Management Plan to be attached to the Event.
   */
  public function setTrafficManagementPlan($tmp);

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
   *   TRUE to set this Event to published, FALSE to set it to unpublished.
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
