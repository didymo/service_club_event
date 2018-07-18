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
 * Defines the Additional guest entity.
 *
 * @ingroup service_club_event
 *
 * @ContentEntityType(
 *   id = "additional_guest",
 *   label = @Translation("Additional guest"),
 *   handlers = {
 *     "storage" = "Drupal\service_club_event\AdditionalGuestStorage",
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\service_club_event\AdditionalGuestListBuilder",
 *     "views_data" = "Drupal\service_club_event\Entity\AdditionalGuestViewsData",
 *     "translation" = "Drupal\service_club_event\AdditionalGuestTranslationHandler",
 *
 *     "form" = {
 *       "default" = "Drupal\service_club_event\Form\AdditionalGuestForm",
 *       "add" = "Drupal\service_club_event\Form\AdditionalGuestForm",
 *       "edit" = "Drupal\service_club_event\Form\AdditionalGuestForm",
 *       "delete" = "Drupal\service_club_event\Form\AdditionalGuestDeleteForm",
 *     },
 *     "access" = "Drupal\service_club_event\AdditionalGuestAccessControlHandler",
 *     "route_provider" = {
 *       "html" = "Drupal\service_club_event\AdditionalGuestHtmlRouteProvider",
 *     },
 *   },
 *   base_table = "additional_guest",
 *   data_table = "additional_guest_field_data",
 *   revision_table = "additional_guest_revision",
 *   revision_data_table = "additional_guest_field_revision",
 *   translatable = TRUE,
 *   admin_permission = "administer additional guest entities",
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
 *     "canonical" = "/admin/structure/additional_guest/{additional_guest}",
 *     "add-form" = "/admin/structure/additional_guest/add",
 *     "edit-form" = "/admin/structure/additional_guest/{additional_guest}/edit",
 *     "delete-form" = "/admin/structure/additional_guest/{additional_guest}/delete",
 *     "version-history" = "/admin/structure/additional_guest/{additional_guest}/revisions",
 *     "revision" = "/admin/structure/additional_guest/{additional_guest}/revisions/{additional_guest_revision}/view",
 *     "revision_revert" = "/admin/structure/additional_guest/{additional_guest}/revisions/{additional_guest_revision}/revert",
 *     "revision_delete" = "/admin/structure/additional_guest/{additional_guest}/revisions/{additional_guest_revision}/delete",
 *     "translation_revert" = "/admin/structure/additional_guest/{additional_guest}/revisions/{additional_guest_revision}/revert/{langcode}",
 *     "collection" = "/admin/structure/additional_guest",
 *   },
 *   field_ui_base_route = "additional_guest.settings"
 * )
 */
class AdditionalGuest extends RevisionableContentEntityBase implements AdditionalGuestInterface {

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

    // If no revision author has been set explicitly, make the additional_guest owner the
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

    $fields['fname'] = BaseFieldDefinition::create('string')
      ->setLabel(t('First Name'))
      ->setDescription(t('Please input your first name'))
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
        'weight' => 1,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => 1,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(TRUE);

    $fields['date_of_birth'] = BaseFieldDefinition::create('datetime')
      ->setLabel(t('Date of Birth'))
      ->setDescription(t('Select your Date of Birth'))
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
        'weight' => 2,
      ])
      ->setDisplayOptions('form', [
        'type' => 'datetime_default',
        'weight' => 2,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(TRUE);

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
