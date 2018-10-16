<?php

namespace Drupal\service_club_event\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\service_club_event\Entity\EventInformation;
use Drupal\service_club_event\Entity\EventInformationInterface;
use Drupal\service_club_event\Entity\ManageShifts;

/**
 * Form controller for Volunteer registration edit forms.
 *
 * @ingroup service_club_event
 */
class VolunteerRegistrationForm extends ContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    /* @var $entity \Drupal\service_club_event\Entity\VolunteerRegistration */
    $form = parent::buildForm($form, $form_state);

    if (!$this->entity->isNew()) {
      $form['new_revision'] = [
        '#type' => 'checkbox',
        '#title' => $this->t('Create new revision'),
        '#default_value' => FALSE,
        '#weight' => 10,
      ];
    }

    // Get the current event.
    $event = $this->getRouteMatch()->getParameter('event_information');
    $shifts = $event->getShifts();


    $shift_names = [-1 => 'No Shift'];

    // Load array with Shift names.
    foreach ($shifts as $shift) {
      $shift_names += [$shift->id() => $shift->getName()];
    }

    // Add form element radios.
    $form['shifts'] = [
      '#type' => 'radios',
      '#title' => $this->t('Available Shifts'),
      '#options' => $shift_names,
      '#description' => $this->t('Select one of the available shifts.'),
      '#default_value' => -1,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
    /*
        // Get the event object out of the URL.
        $event = $this->getRouteMatch()->getParameter('event_information');

        // Guardian if to ensure it's an EventInformation entity.
        if ($event instanceof EventInformation) {
          // Get the saved volunteer list.
          $volunteer_list = $event->getVolunteerList();

          // Get the current user's id.
          $user_id = $form_state->getValue('user_id');

          // Check the user id hasn't been used yet.
          foreach ($volunteer_list as $volunteer_id) {
            if ($volunteer_id['target_id'] === $user_id) {
              $form_state->setErrorByName('Unique Registration', $this->t('You can only register as a volunteer for the current event once.'));
            }
          }
        }*/
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $entity = $this->entity;
    $event = $this->getRouteMatch()->getParameter('event_information');
    $entity->set('name', 'event:' . $event->id() . '_user:' . \Drupal::currentUser()->id());
    // Save as a new revision if requested to do so.
    if (!$form_state->isValueEmpty('new_revision') && $form_state->getValue('new_revision') != FALSE) {
      $entity->setNewRevision();

      // If a new revision is created, save the current user as revision author.
      $entity->setRevisionCreationTime(REQUEST_TIME);
      $entity->setRevisionUserId(\Drupal::currentUser()->id());
    }
    else {
      $entity->setNewRevision(FALSE);
    }


      // Set the associated event when the registration is created.
      $entity->setEventId($event->id());
      $entity->save();

    $status = parent::save($form, $form_state);

    // Get the event object out of the URL.
    $event = $this->getRouteMatch()->getParameter('event_information');

    switch ($status) {
      case SAVED_NEW:
        drupal_set_message($this->t('Created the %label Volunteer registration.', [
          '%label' => $entity->label(),
        ]));

        // Set the associated event when the registration is created.
        $entity->setEventId($event->id());
        $entity->save();

        break;

      default:
        drupal_set_message($this->t('Saved the %label Volunteer registration.', [
          '%label' => $entity->label(),
        ]));
    }

    // Modified routing to improve workflow of volunteer registration.
    $form_state->setRedirect('entity.event_information.canonical', ['event_information' => $event->id()]);

    // Make connections between event, shift and registration.
    $event->addVolunteer($entity->id());
    $event->save();

    // Load the shift.
    $shift_id = $form_state->getValue('shifts');
    if ($shift_id !== '-1') {
      $shift = ManageShifts::load($shift_id);
      $shift->assignVolunteer($entity->id());
      $shift->save();

      // Registration id.
      $entity->setShift($shift_id);
      $entity->save();
    }

  }

}
