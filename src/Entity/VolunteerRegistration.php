<?php

namespace Drupal\service_club_event\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\RevisionableContentEntityBase;
use Drupal\Core\Entity\RevisionableInterface;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\user\UserInterface;

/**
 * Defines the Volunteer registration entity.
 *
 * @ingroup service_club_event
 *
 * @ContentEntityType(
 *   id = "volunteer_registration",
 *   label = @Translation("Volunteer registration"),
 *   handlers = {
 *     "storage" = "Drupal\service_club_event\VolunteerRegistrationStorage",
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" =
 *   "Drupal\service_club_event\VolunteerRegistrationListBuilder",
 *     "views_data" =
 *   "Drupal\service_club_event\Entity\VolunteerRegistrationViewsData",
 *     "translation" =
 *   "Drupal\service_club_event\VolunteerRegistrationTranslationHandler",
 *
 *     "form" = {
 *       "default" =
 *   "Drupal\service_club_event\Form\VolunteerRegistrationForm",
 *       "add" = "Drupal\service_club_event\Form\VolunteerRegistrationForm",
 *       "edit" = "Drupal\service_club_event\Form\VolunteerRegistrationForm",
 *       "delete" =
 *   "Drupal\service_club_event\Form\VolunteerRegistrationDeleteForm",
 *     },
 *     "access" =
 *   "Drupal\service_club_event\VolunteerRegistrationAccessControlHandler",
 *     "route_provider" = {
 *       "html" =
 *   "Drupal\service_club_event\VolunteerRegistrationHtmlRouteProvider",
 *     },
 *   },
 *   base_table = "volunteer_registration",
 *   data_table = "volunteer_registration_field_data",
 *   revision_table = "volunteer_registration_revision",
 *   revision_data_table = "volunteer_registration_field_revision",
 *   translatable = TRUE,
 *   admin_permission = "administer volunteer registration entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "revision" = "vid",
 *     "label" = "name",
 *     "uuid" = "uuid",
 *     "uid" = "user_id",
 *     "langcode" = "langcode",
 *     "status" = "status",
 *   },
 *   links = {
 *     "canonical" =
 *   "/admin/structure/volunteer_registration/{volunteer_registration}",
 *     "add-form" = "/admin/structure/volunteer_registration/add",
 *     "edit-form" =
 *   "/admin/structure/volunteer_registration/{volunteer_registration}/edit",
 *     "delete-form" =
 *   "/admin/structure/volunteer_registration/{volunteer_registration}/delete",
 *     "version-history" =
 *   "/admin/structure/volunteer_registration/{volunteer_registration}/revisions",
 *     "revision" =
 *   "/admin/structure/volunteer_registration/{volunteer_registration}/revisions/{volunteer_registration_revision}/view",
 *     "revision_revert" =
 *   "/admin/structure/volunteer_registration/{volunteer_registration}/revisions/{volunteer_registration_revision}/revert",
 *     "revision_delete" =
 *   "/admin/structure/volunteer_registration/{volunteer_registration}/revisions/{volunteer_registration_revision}/delete",
 *     "translation_revert" =
 *   "/admin/structure/volunteer_registration/{volunteer_registration}/revisions/{volunteer_registration_revision}/revert/{langcode}",
 *     "collection" = "/admin/structure/volunteer_registration",
 *   },
 *   field_ui_base_route = "volunteer_registration.settings"
 * )
 */
class VolunteerRegistration extends RevisionableContentEntityBase implements VolunteerRegistrationInterface {

  use EntityChangedTrait;

  /**
   * {@inheritdoc}
   */
  public static function preCreate(EntityStorageInterface $storage_controller, array &$values) {
    parent::preCreate($storage_controller, $values);
    $values += [
      'user_id' => \Drupal::currentUser()->id(),
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function urlRouteParameters($rel) {
    $uri_route_parameters = parent::urlRouteParameters($rel);

    if ($rel === 'revision_revert' && $this instanceof RevisionableInterface) {
      $uri_route_parameters[$this->getEntityTypeId() . '_revision'] = $this->getRevisionId();
    }
    elseif ($rel === 'revision_delete' && $this instanceof RevisionableInterface) {
      $uri_route_parameters[$this->getEntityTypeId() . '_revision'] = $this->getRevisionId();
    }

    return $uri_route_parameters;
  }

  /**
   * {@inheritdoc}
   */
  public function preSave(EntityStorageInterface $storage) {
    parent::preSave($storage);

    foreach (array_keys($this->getTranslationLanguages()) as $langcode) {
      $translation = $this->getTranslation($langcode);

      // If no owner has been set explicitly, make the anonymous user the owner.
      if (!$translation->getOwner()) {
        $translation->setOwnerId(0);
      }
    }

    // If no revision author has been set explicitly, make the volunteer_registration owner the
    // revision author.
    if (!$this->getRevisionUser()) {
      $this->setRevisionUserId($this->getOwnerId());
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return $this->get('name')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setName($name) {
    $this->set('name', $name);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getCreatedTime() {
    return $this->get('created')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setCreatedTime($timestamp) {
    $this->set('created', $timestamp);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwner() {
    return $this->get('user_id')->entity;
  }

  /**registered_shift
   * {@inheritdoc}
   */
  public function getOwnerId() {
    return $this->get('user_id')->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwnerId($uid) {
    $this->set('user_id', $uid);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getShift() {
    return $this->get('registered_shift')->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public function setShift($shift_id) {
    $this->set('registered_shift', ['target_id' => $shift_id]);
    $this->save();
  }

  /**
   * {@inheritdoc}
   */
  public function setOwner(UserInterface $account) {
    $this->set('user_id', $account->id());
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function isPublished() {
    return (bool) $this->getEntityKey('status');
  }

  /**
   * {@inheritdoc}
   */
  public function setPublished($published) {
    $this->set('status', $published ? TRUE : FALSE);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getEventId() {
    return $this->get('registered_event')->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public function setEventId($event_id) {
    $this->set('registered_event', ['target_id' => $event_id]);
    $this->save();
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['user_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Authored by'))
      ->setDescription(t('The user ID of author of the Volunteer registration entity.'))
      ->setRevisionable(TRUE)
      ->setSetting('target_type', 'user')
      ->setSetting('handler', 'default')
      ->setTranslatable(TRUE)
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'author',
        'weight' => 0,
      ])
      ->setDisplayOptions('form', [
        'type' => 'hidden',
        'weight' => 5,
        'settings' => [
          'match_operator' => 'CONTAINS',
          'size' => '60',
          'autocomplete_type' => 'tags',
          'placeholder' => '',
        ],
      ])
      ->setDisplayConfigurable('form', FALSE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Name'))
      ->setDescription(t('The name of the Volunteer registration entity.'))
      ->setRevisionable(TRUE)
      ->setSettings([
        'max_length' => 50,
        'text_processing' => 0,
      ])
      ->setDefaultValue('')
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(FALSE);

    $fields['status'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Publishing status'))
      ->setDescription(t('A boolean indicating whether the Volunteer registration is published.'))
      ->setRevisionable(TRUE)
      ->setDefaultValue(TRUE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the entity was created.'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the entity was last edited.'));

    $fields['revision_translation_affected'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Revision translation affected'))
      ->setDescription(t('Indicates if the last edit of a translation belongs to current revision.'))
      ->setReadOnly(TRUE)
      ->setRevisionable(TRUE)
      ->setTranslatable(TRUE);

    // Id of the shift the user has registered for.
    $fields['registered_shift'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Registered Shift'))
      ->setDescription(t('The shift the user is attending.'))
      ->setRevisionable(TRUE)
      ->setSetting('target_type', 'manage_shifts')
      ->setSetting('handler', 'default')
      ->setTranslatable(TRUE)
      ->setDisplayConfigurable('view', TRUE);

    // Id of the event the user has registered for.
    $fields['registered_event'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Registered Event'))
      ->setDescription(t('The event the user is attending.'))
      ->setRevisionable(TRUE)
      ->setSetting('target_type', 'event_information')
      ->setSetting('handler', 'default')
      ->setTranslatable(TRUE)
      ->setDisplayConfigurable('view', TRUE);

    return $fields;
  }

}
