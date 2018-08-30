<?php

namespace Drupal\service_club_event;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Link;

/**
 * Defines a class to build a listing of Manage shifts entities.
 *
 * @ingroup service_club_event
 */
class ManageShiftsListBuilder extends EntityListBuilder {


  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = $this->t('Manage shifts ID');
    $header['name'] = $this->t('Name');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /* @var $entity \Drupal\service_club_event\Entity\ManageShifts */
    $row['id'] = $entity->id();
    $row['name'] = Link::createFromRoute(
      $entity->label(),
      'entity.manage_shifts.edit_form',
      ['manage_shifts' => $entity->id()]
    );
    return $row + parent::buildRow($entity);
  }

}
