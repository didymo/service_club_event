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
 * Defines the Event registration entity.
 *
 * @ingroup service_club_event
 *
 * @ContentEntityType(
 *   id = "event_registration",
 *   label = @Translation("Event registration"),
 *   handlers = {
 *     "storage" = "Drupal\service_club_event\EventRegistrationStorage",
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\service_club_event\EventRegistrationListBuilder",
 *     "views_data" = "Drupal\service_club_event\Entity\EventRegistrationViewsData",
 *     "translation" = "Drupal\service_club_event\EventRegistrationTranslationHandler",
 *
 *     "form" = {
 *       "default" = "Drupal\service_club_event\Form\EventRegistrationForm",
 *       "add" = "Drupal\service_club_event\Form\EventRegistrationForm",
 *       "edit" = "Drupal\service_club_event\Form\EventRegistrationForm",
 *       "delete" = "Drupal\service_club_event\Form\EventRegistrationDeleteForm",
 *     },
 *     "access" = "Drupal\service_club_event\EventRegistrationAccessControlHandler",
 *     "route_provider" = {
 *       "html" = "Drupal\service_club_event\EventRegistrationHtmlRouteProvider",
 *     },
 *   },
 *   base_table = "event_registration",
 *   data_table = "event_registration_field_data",
 *   revision_table = "event_registration_revision",
 *   revision_data_table = "event_registration_field_revision",
 *   translatable = TRUE,
 *   admin_permission = "administer event registration entities",
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
 *     "canonical" = "/admin/structure/event_registration/{event_registration}",
 *     "add-form" = "/admin/structure/event_registration/add",
 *     "edit-form" = "/admin/structure/event_registration/{event_registration}/edit",
 *     "delete-form" = "/admin/structure/event_registration/{event_registration}/delete",
 *     "version-history" = "/admin/structure/event_registration/{event_registration}/revisions",
 *     "revision" = "/admin/structure/event_registration/{event_registration}/revisions/{event_registration_revision}/view",
 *     "revision_revert" = "/admin/structure/event_registration/{event_registration}/revisions/{event_registration_revision}/revert",
 *     "revision_delete" = "/admin/structure/event_registration/{event_registration}/revisions/{event_registration_revision}/delete",
 *     "translation_revert" = "/admin/structure/event_registration/{event_registration}/revisions/{event_registration_revision}/revert/{langcode}",
 *     "collection" = "/admin/structure/event_registration",
 *   },
 *   field_ui_base_route = "event_registration.settings"
 * )
 */
class EventRegistration extends RevisionableContentEntityBase implements EventRegistrationInterface {

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

    // If no revision author has been set explicitly, make the event_registration owner the
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

  /**
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
      ->setDescription(t('The user ID of author of the Event registration entity.'))
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
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('First Name'))
      ->setDescription(t('Please input your first name.'))
      ->setRevisionable(TRUE)
      ->setSettings([
        'max_length' => 50,
        'text_processing' => 0,
      ])
      ->setDefaultValue('')
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => 0,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => 0,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(TRUE);

    $fields['lname'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Last Name'))
      ->setDescription(t('Please input your last name'))
      ->setRevisionable(TRUE)
      ->setSettings([
        'max_length' => 50,
        'text_processing' => 0,
      ])
      ->setDefaultValue('')
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => 10,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => 10,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(TRUE);

    $fields['status'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Publishing status'))
      ->setDescription(t('A boolean indicating whether the Event registration is published.'))
      ->setRevisionable(TRUE)
      ->setDefaultValue(TRUE)
      ->setDisplayOptions('form', [
        'type' => 'hidden',
        'weight' => -2,
      ]);

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

    $fields['pNum'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Phone Number'))
      ->setDescription(t('Please input your phone number'))
      ->setRevisionable(TRUE)
      ->setSettings([
        'max_length' => 50,
        'text_processing' => 0,
      ])
      ->setDefaultValue('')
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => 30,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => 30,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(TRUE);

    $fields['date_of_birth'] = BaseFieldDefinition::create('datetime')
      ->setLabel(t('Date of Birth'))
      ->setDescription(t('Select your Date of Birth'))
      ->setRevisionable(TRUE)
      ->setSettings([
        'datetime_type' => 'date',
      ])
      ->setDefaultValue('')
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'datetime_default',
        'settings' => [
          'format_type' => 'medium',
        ],
        'weight' => 40,
      ])
      ->setDisplayOptions('form', [
        'type' => 'datetime_default',
        'weight' => 40,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(TRUE);

    $fields['email'] = BaseFieldDefinition::create('email')
      ->setLabel(t('Email Address'))
      ->setDescription(t('Please enter your contactable email address.'))
      ->setRevisionable(TRUE)
      ->setSettings([
        'max_length' => 50,
        'text_processing' => 0,
      ])
      ->setDefaultValue('')
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => 20,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => 20,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(TRUE);

    $fields['emailPermissions'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Permission to receive emails'))
      ->setDescription(t('Do you agree to receive emails regarding information about the event?'))
      ->setRevisionable(TRUE)
      ->setDefaultValue(FALSE)
      ->setDisplayOptions('form', [
        'type' => 'boolean_checkbox',
        'weight' => 50,
      ]);

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
