<?php

namespace Drupal\service_club_event\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Entity\RevisionableContentEntityBase;
use Drupal\Core\Entity\RevisionableInterface;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\service_club_tmp\Entity\TrafficManagementPlan;
use Drupal\user\UserInterface;
use Drupal\service_club_event\Entity\ManageShifts;
use Drupal\service_club_tmp\Entity\EventClass;
use Drupal\service_club_tmp\Entity\Questionnaire;

/**
 * Defines the Event information entity.
 *
 * @ingroup service_club_event
 *
 * @ContentEntityType(
 *   id = "event_information",
 *   label = @Translation("Event information"),
 *   handlers = {
 *     "storage" = "Drupal\service_club_event\EventInformationStorage",
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" =
 *   "Drupal\service_club_event\EventInformationListBuilder",
 *     "views_data" =
 *   "Drupal\service_club_event\Entity\EventInformationViewsData",
 *     "translation" =
 *   "Drupal\service_club_event\EventInformationTranslationHandler",
 *
 *     "form" = {
 *       "default" = "Drupal\service_club_event\Form\EventInformationForm",
 *       "add" = "Drupal\service_club_event\Form\EventInformationForm",
 *       "edit" = "Drupal\service_club_event\Form\EventInformationForm",
 *       "delete" =
 *   "Drupal\service_club_event\Form\EventInformationDeleteForm",
 *       "shift-list" = "Drupal\service_club_event\form\OverviewShifts",
 *     },
 *     "access" =
 *   "Drupal\service_club_event\EventInformationAccessControlHandler",
 *     "route_provider" = {
 *       "html" =
 *   "Drupal\service_club_event\EventInformationHtmlRouteProvider",
 *     },
 *   },
 *   base_table = "event_information",
 *   data_table = "event_information_field_data",
 *   revision_table = "event_information_revision",
 *   revision_data_table = "event_information_field_revision",
 *   translatable = TRUE,
 *   admin_permission = "administer event information entities",
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
 *     "canonical" = "/admin/structure/event_information/{event_information}",
 *     "add-form" = "/admin/structure/event_information/add",
 *     "edit-form" =
 *   "/admin/structure/event_information/{event_information}/edit",
 *     "delete-form" =
 *   "/admin/structure/event_information/{event_information}/delete",
 *     "version-history" =
 *   "/admin/structure/event_information/{event_information}/revisions",
 *     "revision" =
 *   "/admin/structure/event_information/{event_information}/revisions/{event_information_revision}/view",
 *     "revision_revert" =
 *   "/admin/structure/event_information/{event_information}/revisions/{event_information_revision}/revert",
 *     "revision_delete" =
 *   "/admin/structure/event_information/{event_information}/revisions/{event_information_revision}/delete",
 *     "translation_revert" =
 *   "/admin/structure/event_information/{event_information}/revisions/{event_information_revision}/revert/{langcode}",
 *     "collection" = "/admin/structure/event_information",
 *     "asset-list" =
 *   "/admin/structure/event_information/{event_information}/asset_list",
 *     "shift-list" = "/admin/structure/event_information/{event_information}/shift-list",
 *   },
 *   field_ui_base_route = "event_information.settings"
 * )
 */
class EventInformation extends RevisionableContentEntityBase implements EventInformationInterface {

  use EntityChangedTrait;

  /**
   * {@inheritdoc}
   */
  public static function preCreate(EntityStorageInterface $storage_controller, array &$values) {
    parent::preCreate($storage_controller, $values);
    //dd('Dominic is great');
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

    // If no revision author has been set explicitly, make the event_information owner the
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
  public function getEventAssets() {
    return $this->get('event_assets')->getValue();
  }

  /**
   * {@inheritdoc}
   */
  public function setEventAssets($assets) {
    $this->set('event_assets', $assets);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getVolunteerList() {
    return $this->get('volunteer_registration')->getValue();
  }

  /**
   * {@inheritdoc}
   */
  public function addVolunteer($volunteer) {
    $volunteer_list = $this->get('volunteer_registration')->getValue();
    $volunteer_list += [count($volunteer_list) => ['target_id' => $volunteer]];
    $this->set('volunteer_registration', $volunteer_list);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getAnonymousList() {
    return $this->get('anonymous_registration')->getValue();
  }

  /**
   * {@inheritdoc}
   */
  public function addAnonymousRegistration($anonymous_id) {
    $anonymous_list = $this->get('anonymous_registration')->getValue();
    $anonymous_list += [count($anonymous_list) => ['target_id' => $anonymous_id]];
    $this->set('anonymous_registration', $anonymous_list);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getShifts() {
    $references = $this->get('shifts')->getValue();
    $shifts = array();

    foreach ($references as $shift_id) {
      $shift = ManageShifts::load($shift_id['target_id']);

      // Extra level of security to ensure shift sorting doesn't break.
      if ($shift !== NULL) {
          $shifts[] = $shift;
      }
      else {
          $event_id = $this->getOwnerId();
          $shift_id_target_id = $shift_id['target_id'];
          \Drupal::logger('EventInformation getShifts')->error("The event with id: $event_id contains a reference to a shift: $shift_id_target_id that no longer exists. This should not have happened.");
      }
    }

    // Sort the shirts by a comparsion we defined.
    usort($shifts, array("Drupal\service_club_event\Entity\ManageShifts", "compare_start_time"));
    return $shifts;
  }

  /**
   * {@inheritdoc}
   */
  public function addShift($shift_id) {
    $shifts = $this->get('shifts')->getValue();
    $shifts += [count($shifts) => ['target_id' => $shift_id]];
    $this->set('shifts', $shifts);
    $this->save();
  }

  /**
   * {@inheritdoc}
   */
  public function removeShift($shift_id) {
    $current_shifts = $this->get('shifts')->getValue();

    $new_shifts = [];

    // Add each shift to a new array.
    foreach ($current_shifts as $shift) {
      // Skip the shfit if it's id matches the given id.
      if ($shift['target_id'] !== $shift_id) {
        // Add the shift to the new array.
        $new_shifts += [count($new_shifts) => ['target_id' => $shift_id]];
      }
    }

    // Save the new shift list.
    $this->set('shifts', $new_shifts);
    $this->save();
  }

  /**
   * {@inheritdoc}
   */
  public function getEventStartDate() {
    return $this->get('event_date_start')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setEventStartDate($start_date) {
    $this->set('event_date_start', $start_date);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getEventEndDate() {
    return $this->get('event_date_finish')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setEventEndDate($end_date) {
    $this->set('event_date_finish', $end_date);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getVolParking() {
    return $this->get('volParking')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setVolParking($parking) {
    $this->set('volParking', $parking);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getPubParking() {
    return $this->get('pubParking')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setPubParking($parking) {
    $this->set('pubParking', $parking);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function isRegistered($uid) {
    $registration_ids = $this->get('volunteer_registration')->getValue();
    $registrations = array();

    foreach ($registration_ids as $rid) {
      $registrations[] = VolunteerRegistration::load($rid['target_id']);
    }

    foreach ($registrations as $registration) {
      if ($registration->getOwner()->id() === $uid) {
        return $registration;
      }
    }

    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getEventClass() {
    $event_class_reference = $this->get('event_class')->getValue();

    if (isset($event_class_reference[0]['target_id'])) {
      $event_class = EventClass::load($event_class_reference[0]['target_id']);
      return $event_class;
    }

    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function setEventClass($event_class) {
    $event_class_reference = $this->get('event_class')->getValue();
    $event_class_reference[0]['target_id'] = $event_class;
    $this->set('event_class', $event_class_reference);
    $this->save();
  }

  /**
   * {@inheritdoc}
   */
  public function getTrafficManagementPlan() {
    $traffic_management_plan_reference = $this->get('traffic_management_plan')->getValue();

    if (isset($traffic_management_plan_reference[0]['target_id'])) {
      $traffic_management_plan = TrafficManagementPlan::load($traffic_management_plan_reference[0]['target_id']);
      return $traffic_management_plan;
    }

    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function setTrafficManagementPlan($tmp) {
    $traffic_management_plan_reference = $this->get('traffic_management_plan')->getValue();
    $traffic_management_plan_reference[0]['target_id'] = $tmp->id();
    $this->set('traffic_management_plan', $traffic_management_plan_reference);
    $this->save();
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['user_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Authored by'))
      ->setDescription(t('The user ID of author of the Event information entity.'))
      ->setRevisionable(TRUE)
      ->setSetting('target_type', 'user')
      ->setSetting('handler', 'default')
      ->setTranslatable(TRUE)
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'author',
        'weight' => 10,
      ])
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'weight' => 10,
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
        'weight' => 0,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => 0,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(TRUE);

    $fields['status'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Publishing status'))
      ->setDescription(t('A boolean indicating whether the Event information is published.'))
      ->setRevisionable(TRUE)
      ->setDefaultValue(TRUE)
      ->setDisplayOptions('form', [
        'type' => 'boolean_checkbox',
        'weight' => 11,
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
        'weight' => 1,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => 1,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(TRUE);

    // Event Date (Array(Date-Time))
    $fields['event_date_start'] = BaseFieldDefinition::create('datetime')
      ->setLabel(t('Event Start'))
      ->setDescription(t('The Date and Time the Event will Start'))
      ->setRevisionable(TRUE)
      ->setSettings([
        'datetime_type' => 'date_time',
      ])
      ->setDefaultValue('')
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'datetime_default',
        'settings' => [
          'format_type' => 'medium',
        ],
        'weight' => 2,
      ])
      ->setDisplayOptions('form', [
        'type' => 'datetime_default',
        'weight' => 2,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(TRUE);

    // Event Date End(Array(Date-Time))
    $fields['event_date_finish'] = BaseFieldDefinition::create('datetime')
      ->setLabel(t('Event Finish'))
      ->setDescription(t('The Date and Time the Event will finish'))
      ->setRevisionable(TRUE)
      ->setSettings([
        'datetime_type' => 'date_time',
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

    // Images (Array(Images))
    $fields['image'] = BaseFieldDefinition::create('image')
      ->setLabel(t('Event Image'))
      ->setDescription(t('Add image/s for the event'))
      ->setCardinality(4)
      ->setSettings([
        'file_directory' => 'image_folder',
        'alt_field_reindentquired' => FALSE,
        'file_extensions' => 'png jpg jpeg',
      ])
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'default',
        'weight' => 5,
      ])
      ->setDisplayOptions('form', [
        'label' => 'hidden',
        'type' => 'image_image',
        'weight' => 5,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    // Event Information (text(formatted, long))
    $fields['description'] = BaseFieldDefinition::create('text_long')
      ->setLabel(t('Event Description'))
      ->setDescription(t('A brief description of the event'))
      ->setRevisionable(TRUE)
      ->setSettings([
        'max_length' => 50,
        'text_processing' => 0,
      ])
      ->setDefaultValue('')
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => 4,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => 4,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(TRUE);

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
        'weight' => 7,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => 7,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(TRUE);

    // Available Shifts (Array(shift entities))
    $fields['shifts'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Shifts'))
      ->setDescription(t('Shifts for the event'))
      ->setCardinality(FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED)
      ->setRevisionable(TRUE)
      ->setSetting('target_type', 'manage_shifts')
      ->setSetting('handler', 'default')
      ->setTranslatable(TRUE)
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => 8,
      ])
      ->setDisplayConfigurable('form', FALSE)
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(FALSE);

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
        'weight' => 6,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => 6,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(TRUE);

    // Asset's assigned to an event.
    $fields['event_assets'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Assets assigned to an event.'))
      ->setDescription(t('Assets assigned to an event.'))
      ->setRevisionable(TRUE)
      ->setSetting('target_type', 'asset_entity')
      ->setSetting('handler', 'default')
      ->setTranslatable(TRUE)
      ->setDisplayOptions('view', [
        'type' => 'string',
        'weight' => 9,
      ])
      ->setDisplayConfigurable('view', TRUE)
      ->setCardinality(FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED);

    // List of volunteers for the current event.
    $fields['volunteer_registration'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Registered Volunteers'))
      ->setDescription(t('List of volunteer registrations for this event.'))
      ->setCardinality(FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED)
      ->setRevisionable(TRUE)
      ->setSetting('target_type', 'volunteer_registration')
      ->setSetting('handler', 'default')
      ->setTranslatable(TRUE)
      ->setRequired(FALSE);

    // List of anonymous registrations.
    $fields['anonymous_registration'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Anonymous Registrations'))
      ->setDescription(t('List of anonymous registrations for this event.'))
      ->setCardinality(FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED)
      ->setRevisionable(TRUE)
      ->setSetting('target_type', 'event_registration')
      ->setSetting('handler', 'default')
      ->setTranslatable(TRUE)
      ->setRequired(FALSE);

    // The Questionnaire entity that was filled out for this event.
    $fields['questionnaire'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Questionnaire'))
      ->setDescription(t('The TMP Questionnaire for this event.'))
      ->setSetting('target_type', 'questionnaire')
      ->setSetting('handler', 'default')
      ->setTranslatable(TRUE)
      ->setRequired(FALSE);

    // The Event Class entity that the questionnaire evaluated to.
    $fields['event_class'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Event Class'))
      ->setDescription(t('The Event Class that the questionnaire evaluated to.'))
      ->setSetting('target_type', 'event_class')
      ->setSetting('handler', 'default')
      ->setTranslatable(TRUE)
      ->setRequired(FALSE);

    // The Event Class entity that the questionnaire evaluated to.
    $fields['traffic_management_plan'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Traffic Management Plan'))
      ->setDescription(t('The Traffic Management Plan that is attached to this event.'))
      ->setSetting('target_type', 'traffic_management_plan')
      ->setSetting('handler', 'default')
      ->setTranslatable(TRUE)
      ->setRequired(FALSE);

    return $fields;
  }

}
