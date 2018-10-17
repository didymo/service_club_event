<?php

namespace Drupal\service_club_event\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Datetime;

/**
 * Form controller for Event information edit forms.
 *
 * @ingroup service_club_event
 */
class EventInformationForm extends ContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    /* @var $entity \Drupal\service_club_event\Entity\EventInformation */
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
    parent::validateForm($form, $form_state);

    // Get both dates from the form.
    $shift_start = $form_state->getValue('event_date_start');
    $shift_end = $form_state->getValue('event_date_finish');

    // Ensure the event start and end dates are valid with each other.
    if ($shift_end <= $shift_start) {
      $form_state->setErrorByName('Invalid Event Start/End Dates', $this->t('The start and end dates are invalid please re-enter valid information. An event must end after it\'s start.'));
    }
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
        drupal_set_message($this->t('Created the %label Event information.', [
          '%label' => $entity->label(),
        ]));
        break;

      default:
        drupal_set_message($this->t('Saved the %label Event information.', [
          '%label' => $entity->label(),
        ]));
    }
    $form_state->setRedirect('entity.event_information.canonical', ['event_information' => $entity->id()]);
  }

}
