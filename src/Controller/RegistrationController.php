<?php

namespace Drupal\service_club_event\Controller;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Controller\ControllerBase;
use Drupal\service_club_event\Entity\VolunteerRegistrationInterface;
use Drupal\service_club_event\Entity\EventInformationInterface;

/**
 * Provides route responses for taxonomy.module.
 */
class RegistrationController extends ControllerBase {

  /**
   * Returns a form to add a new volunteer to an event.
   *
   * @param Drupal\service_club_event\Entity\EventInformationInterface $event_information
   *   The Event this volunteer will be added to.
   *
   * @return array
   *   The event volunteer registration add form.
   */
  public function addVolunteerForm(EventInformationInterface $event_information) {
      //query to find what role they are
    if (\Drupal::currentUser()->isAnonymous()) {
      $event_registration = $this->entityManager()->getStorage('event_registration')->create(['eid' => $event_information->id()]);
      return $this->entityFormBuilder()->getForm($event_registration);
    }
    else {
        $registration = $event_information->isRegistered(\Drupal::currentUser()->id());
        if (isset($registration)) {
          return $this->entityFormBuilder()->getForm($registration);
      }
        else {
          $volunteer_registration = $this->entityManager()->getStorage('volunteer_registration')->create(['eid' => $event_information->id()]);
          return $this->entityFormBuilder()->getForm($volunteer_registration);
        }
    }
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
   * @param \Drupal\service_club_base\Entity\VolunteerRegistrationInterface $volunteer_registration
   *   The event shift.
   *
   * @return array
   *   The volunteer label as a render array.
   */
  public function volunteerRegistration(VolunteerRegistrationInterface $volunteer_registration) {
    return ['#markup' => $volunteer_registration->getName(), '#allowed_tags' => Xss::getHtmlTagList()];
  }

}
