<?php

namespace Drupal\service_club_event\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for Event registration edit forms.
 *
 * @ingroup service_club_event
 */
class EventRegistrationForm extends ContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    /* @var $entity \Drupal\service_club_event\Entity\EventRegistration */
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
  public function save(array $form, FormStateInterface $form_state) {
    $entity = $this->entity;
    // Get the event object from the url.
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

    // Set the associated event when the registration is created.
    $entity->setEventId($event->id());
    $entity->save();

    $status = parent::save($form, $form_state);
/*
    switch ($status) {
      case SAVED_NEW:
        drupal_set_message($this->t('Created the %label Event registration.', [
          '%label' => $entity->label(),
        ]));
        break;

      default:
        drupal_set_message($this->t('Saved the %label Event registration.', [
          '%label' => $entity->label(),
        ]));
    }*/

    $form_state->setRedirect('view.event_registration_view.page_2', ['event_information' => $event->id()]);

    // Make connections between event and EventRegistration.
    $event->addAnonymousRegistration($entity->id());
    $event->save();
  }

}
