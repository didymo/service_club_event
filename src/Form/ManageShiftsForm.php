<?php

namespace Drupal\service_club_event\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\service_club_event\Entity\EventInformation;
use Drupal\service_club_event\Entity\EventInformationInterface;

/**
 * Form controller for Manage shifts edit forms.
 *
 * @ingroup service_club_event
 */
class ManageShiftsForm extends ContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    /* @var $entity \Drupal\service_club_event\Entity\ManageShifts */
    $form = parent::buildForm($form, $form_state);

    if (!$this->entity->isNew()) {
      $form['new_revision'] = [
        '#type' => 'checkbox',
        '#title' => $this->t('Create new revision'),
        '#default_value' => FALSE,
        '#weight' => 10,
      ];
    }

    $entity = $this->entity;

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $entity = parent::validateForm($form, $form_state);

    // Get both dates from the form.
    $shift_start = $form_state->getValue('shift_start');
    $shift_end = $form_state->getValue('shift_finish');

    // Ensure the event start and end dates are valid with each other.
    if ($shift_end <= $shift_start) {
      $form_state->setErrorByName('Invalid Shift Start/End Dates', $this->t('The start and end dates are invalid please re-enter valid information. An event must end after it\'s start.'));
    }

    // Get the shift numbers.
    $shift_numbers = $entity->getShiftNumbers();

    // Ensure the given number of people is a number.
    if (!is_numeric($shift_numbers)) {
      $form_state->setErrorByName('Invalid Recommended People value', $this->t('The given value for recommended number of people is not a number.'));
    }
    if ((int)$shift_numbers <= 0) {
      $form_state->setErrorByName('Invalid Recommended People value', $this->t('Must select a number greater than 0.'));
    }

  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $entity = $this->entity;
    $event = $this->getRouteMatch()->getParameter('event_information');

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

    $status = parent::save($form, $form_state);

    switch ($status) {
      case SAVED_NEW:
        drupal_set_message($this->t('Created the %label Manage shifts.', [
          '%label' => $entity->label(),
        ]));
        $event->addShift($entity->id());

        // Add reference to the corresponding event.
        $entity->setEventId($event->id());

        break;

      default:
        drupal_set_message($this->t('Saved the %label Manage shifts.', [
          '%label' => $entity->label(),
        ]));
    }
    $form_state->setRedirect('entity.event_information.shift_list', ['event_information' => $entity->getEventId()]);
  }

}
