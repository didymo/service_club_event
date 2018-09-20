<?php

namespace Drupal\service_club_event\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\service_club_event\Entity\EventInformation;
use Drupal\service_club_event\Entity\EventInformationInterface;

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
        '#title' => 'Available Shifts',
        '#options' => $shift_names,
        '#description' => 'token description',
        '#default_value' => -1,
    ];

    $entity = $this->entity;

    return $form;
  }

    /**
     * {@inheritdoc}
     */
    public function validateForm(array &$form, FormStateInterface $form_state) {
        parent::validateForm($form, $form_state);
    }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $entity = $this->entity;

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
        drupal_set_message($this->t('Created the %label Volunteer registration.', [
          '%label' => $entity->label(),
        ]));
        break;

      default:
        drupal_set_message($this->t('Saved the %label Volunteer registration.', [
          '%label' => $entity->label(),
        ]));
    }
    $form_state->setRedirect('entity.volunteer_registration.canonical', ['volunteer_registration' => $entity->id()]);
  }

}
