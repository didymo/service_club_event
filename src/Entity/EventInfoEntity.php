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
 * Defines the Event info entity entity.
 *
 * @ingroup service_club_event
 *
 * @ContentEntityType(
 *   id = "event_info_entity",
 *   label = @Translation("Event info entity"),
 *   handlers = {
 *     "storage" = "Drupal\service_club_event\EventInfoEntityStorage",
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\service_club_event\EventInfoEntityListBuilder",
 *     "views_data" = "Drupal\service_club_event\Entity\EventInfoEntityViewsData",
 *     "translation" = "Drupal\service_club_event\EventInfoEntityTranslationHandler",
 *
 *     "form" = {
 *       "default" = "Drupal\service_club_event\Form\EventInfoEntityForm",
 *       "add" = "Drupal\service_club_event\Form\EventInfoEntityForm",
 *       "edit" = "Drupal\service_club_event\Form\EventInfoEntityForm",
 *       "delete" = "Drupal\service_club_event\Form\EventInfoEntityDeleteForm",
 *     },
 *     "access" = "Drupal\service_club_event\EventInfoEntityAccessControlHandler",
 *     "route_provider" = {
 *       "html" = "Drupal\service_club_event\EventInfoEntityHtmlRouteProvider",
 *     },
 *   },
 *   base_table = "event_info_entity",
 *   data_table = "event_info_entity_field_data",
 *   revision_table = "event_info_entity_revision",
 *   revision_data_table = "event_info_entity_field_revision",
 *   translatable = TRUE,
 *   admin_permission = "administer event info entity entities",
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
 *     "canonical" = "/admin/structure/event_info_entity/{event_info_entity}",
 *     "add-form" = "/admin/structure/event_info_entity/add",
 *     "edit-form" = "/admin/structure/event_info_entity/{event_info_entity}/edit",
 *     "delete-form" = "/admin/structure/event_info_entity/{event_info_entity}/delete",
 *     "version-history" = "/admin/structure/event_info_entity/{event_info_entity}/revisions",
 *     "revision" = "/admin/structure/event_info_entity/{event_info_entity}/revisions/{event_info_entity_revision}/view",
 *     "revision_revert" = "/admin/structure/event_info_entity/{event_info_entity}/revisions/{event_info_entity_revision}/revert",
 *     "revision_delete" = "/admin/structure/event_info_entity/{event_info_entity}/revisions/{event_info_entity_revision}/delete",
 *     "translation_revert" = "/admin/structure/event_info_entity/{event_info_entity}/revisions/{event_info_entity_revision}/revert/{langcode}",
 *     "collection" = "/admin/structure/event_info_entity",
 *   },
 *   field_ui_base_route = "event_info_entity.settings"
 * )
 */
class EventInfoEntity extends RevisionableContentEntityBase implements EventInfoEntityInterface {

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

    // If no revision author has been set explicitly, make the event_info_entity owner the
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
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['user_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Authored by'))
      ->setDescription(t('The user ID of author of the Event info entity entity.'))
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
        'type' => 'entity_reference_autocomplete',
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
    // Setting the name of the event.
    $fields['name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Event Name'))
      ->setDescription(t('The name of the Event'))
      ->setRevisionable(TRUE)
      ->setSettings([
        'max_length' => 50,
        'text_processing' => 0,
      ])
      ->setDefaultValue('')
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => 1,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => 1,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(TRUE);
    // Setting the location of the event.
    $fields['location'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Event Location'))
      ->setDescription(t('The address of the event'))
      ->setRevisionable(TRUE)
      ->setSettings([
        'max_length' => 50,
        'text_processing' => 0,
      ])
      ->setDefaultValue('')
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => 2,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => 2,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(TRUE);

    // Event Date (Array(Date-Time))
    $fields['event_date_start'] = BaseFieldDefinition::create('datetime')
      ->setLabel(t('Date and Time of the Event Start'))
      ->setDescription(t('The Date and Time the Event will Start'))
      ->setRevisionable(TRUE)
      ->setSettings([
        'datetime_type' => 'date' ,
      ])
      ->setDefaultValue('')
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'datetime_default',
        'settings' => [
          'format_type' => 'medium',
        ],
        'weight' => 3,
      ])
      ->setDisplayOptions('form', [
        'type' => 'datetime_default',
        'weight' => 3,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(TRUE);

    // Event Date End(Array(Date-Time))
    $fields['event_date_finish'] = BaseFieldDefinition::create('datetime')
      ->setLabel(t('Date and Time of the Event Finish'))
      ->setDescription(t('The Date and Time the Event will finish'))
      ->setRevisionable(TRUE)
      ->setSettings([
        'datetime_type' => 'date' ,
      ])
      ->setDefaultValue('')
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'datetime_default',
        'settings' => [
          'format_type' => 'medium',
        ],
        'weight' => 4,
      ])
      ->setDisplayOptions('form', [
        'type' => 'datetime_default',
        'weight' => 4,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(TRUE);
    // Images (Array(Images))
    // Event Information (text(formatted, long))
    // Parking and other entrances for volunteers (Array(String))
    $fields['volParking'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Volunteers Parking Location and Entrance'))
      ->setDescription(t('Where all volunteers should park their car and enter the event'))
      ->setRevisionable(TRUE)
      ->setSettings([
        'max_length' => 50,
        'text_processing' => 0,
      ])
      ->setDefaultValue('')
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => 6,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => 6,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(TRUE);
    // Available Shifts (Array(Date-Time))
    // List of assets (Array(entity ref))
    // List of members attending (Array(entity ref)
    // List of anonymous users (Array(string(name)))
    // Add asset link (button)
    // Standard parking and entry (string)
    $fields['pubParking'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Public Parking Location'))
      ->setDescription(t('Where all members of the public can park their car'))
      ->setRevisionable(TRUE)
      ->setSettings([
        'max_length' => 50,
        'text_processing' => 0,
      ])
      ->setDefaultValue('')
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => 12,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => 12,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(TRUE);

    $fields['status'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Publishing status'))
      ->setDescription(t('A boolean indicating whether the Event info entity is published.'))
      ->setRevisionable(TRUE)
      ->setDefaultValue(TRUE)
      ->setDisplayOptions('form', [
        'type' => 'boolean_checkbox',
        'weight' => -3,
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

    return $fields;
  }

}
