<?php

namespace Drupal\service_club_event\Controller;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Controller\ControllerBase;
use Drupal\service_club_event\Entity\ManageShiftsInterface;
use Drupal\service_club_event\Entity\EventInformationInterface;

/**
 * Provides route responses for taxonomy.module.
 */
class ShiftController extends ControllerBase {

  /**
   * Returns a form to add a new shift to an event.
   *
   * @param Drupal\service_club_event\Entity\EventInformationInterface $event_information
   *   The Event this shift will be added to.
   *
   * @return array
   *   The event shift add form.
   */
  public function addForm(EventInformationInterface $event_information) {
    $shift = $this->entityManager()->getStorage('manage_shifts')->create(['eid' => $event_information->id()]);
    return $this->entityFormBuilder()->getForm($shift);
  }

  /**
   * Route title callback.
   *
   * @param Drupal\service_club_event\Entity\EventInformationInterface $event_information
   *   The Event.
   *
   * @return string
   *   The Event label as a render array.
   */
  public function eventTitle(EventInformationInterface $event_information) {
    return ['#markup' => $event_information->label(), '#allowed_tags' => Xss::getHtmlTagList()];
  }

  /**
   * Route title callback.
   *
   * @param \Drupal\service_club_base\Entity\ManageShiftInterface $event_shift
   *   The event shift.
   *
   * @return array
   *   The shift label as a render array.
   */
  public function shiftTitle(ManageShiftsInterface $event_shift) {
    return ['#markup' => $event_shift->getName(), '#allowed_tags' => Xss::getHtmlTagList()];
  }

}
