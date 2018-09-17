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
 * Defines the Manage shifts entity.
 *
 * @ingroup service_club_event
 *
 * @ContentEntityType(
 *   id = "manage_shifts",
 *   label = @Translation("Manage shifts"),
 *   handlers = {
 *     "storage" = "Drupal\service_club_event\ManageShiftsStorage",
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\service_club_event\ManageShiftsListBuilder",
 *     "views_data" = "Drupal\service_club_event\Entity\ManageShiftsViewsData",
 *     "translation" = "Drupal\service_club_event\ManageShiftsTranslationHandler",
 *
 *     "form" = {
 *       "default" = "Drupal\service_club_event\Form\ManageShiftsForm",
 *       "add" = "Drupal\service_club_event\Form\ManageShiftsForm",
 *       "edit" = "Drupal\service_club_event\Form\ManageShiftsForm",
 *       "delete" = "Drupal\service_club_event\Form\ManageShiftsDeleteForm",
 *     },
 *     "access" = "Drupal\service_club_event\ManageShiftsAccessControlHandler",
 *     "route_provider" = {
 *       "html" = "Drupal\service_club_event\ManageShiftsHtmlRouteProvider",
 *     },
 *   },
 *   base_table = "manage_shifts",
 *   data_table = "manage_shifts_field_data",
 *   revision_table = "manage_shifts_revision",
 *   revision_data_table = "manage_shifts_field_revision",
 *   translatable = TRUE,
 *   admin_permission = "administer manage shifts entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "revision" = "vid",
 *     "label" = "name",
 *     "weight" = "weight",
 *     "uuid" = "uuid",
 *     "uid" = "user_id",
 *     "langcode" = "langcode",
 *     "status" = "status",
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/manage_shifts/{manage_shifts}",
 *     "add-form" = "/admin/structure/manage_shifts/add",
 *     "edit-form" = "/admin/structure/manage_shifts/{manage_shifts}/edit",
 *     "delete-form" = "/admin/structure/manage_shifts/{manage_shifts}/delete",
 *     "version-history" = "/admin/structure/manage_shifts/{manage_shifts}/revisions",
 *     "revision" = "/admin/structure/manage_shifts/{manage_shifts}/revisions/{manage_shifts_revision}/view",
 *     "revision_revert" = "/admin/structure/manage_shifts/{manage_shifts}/revisions/{manage_shifts_revision}/revert",
 *     "revision_delete" = "/admin/structure/manage_shifts/{manage_shifts}/revisions/{manage_shifts_revision}/delete",
 *     "translation_revert" = "/admin/structure/manage_shifts/{manage_shifts}/revisions/{manage_shifts_revision}/revert/{langcode}",
 *     "collection" = "/admin/structure/manage_shifts",
 *   },
 *   field_ui_base_route = "manage_shifts.settings"
 * )
 */
class ManageShifts extends RevisionableContentEntityBase implements ManageShiftsInterface {

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

    // If no revision author has been set explicitly, make the manage_shifts owner the
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
  public static function compare_start_time($a, $b) {
    $a_start = $a->get('shift_start')->getValue();
    $b_start = $b->get('shift_start')->getValue();

    if($a_start == $b_start) {
        return 0;
    }
    return ($a_start > $b_start) ? +1 : -1;
  }

    /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['user_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Authored by'))
      ->setDescription(t('The user ID of author of the Manage shifts entity.'))
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

    $fields['name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Name'))
      ->setDescription(t('The name of the shift'))
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

    $fields['shift_start'] = BaseFieldDefinition::create('datetime')
      ->setLabel(t('Start Time and Date'))
      ->setDescription(t('The Date and Time the shift will start'))
      ->setRevisionable(TRUE)
      ->setSettings([
        'datetime_type' => 'date_time' ,
      ])
      ->setDefaultValue('')
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'datetime_default',
        'settings' => [
          'format_type' => 'long',
        ],
        'weight' => 1,
      ])
      ->setDisplayOptions('form', [
        'type' => 'datetime_default',
        'weight' => 1,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(TRUE);

    $fields['shift_finish'] = BaseFieldDefinition::create('datetime')
      ->setLabel(t('End Time and Date'))
      ->setDescription(t('The Date and Time the shift will finish'))
      ->setRevisionable(TRUE)
      ->setSettings([
        'datetime_type' => 'date_time' ,
      ])
      ->setDefaultValue('')
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'datetime_default',
        'settings' => [
          'format_type' => 'long',
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

    $fields['recommended_number_people'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Recommended Number of People'))
      ->setDescription(t('The number of people that will be needed for he event.'))
      ->setRevisionable(TRUE)
      ->setSettings([
        'max_length' => 50,
        'text_processing' => 0,
      ])
      ->setDefaultValue('')
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => 3,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => 3,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(TRUE);

    $fields['description'] = BaseFieldDefinition::create('text_long')
      ->setLabel(t('Shift Description'))
      ->setDescription(t('A brief description of the shift and the jobs that need to be completed.'))
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

    $fields['status'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Publishing status'))
      ->setDescription(t('A boolean indicating whether the Manage shifts is published.'))
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
