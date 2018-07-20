<?php

namespace Drupal\service_club_event;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Link;

/**
 * Defines a class to build a listing of Additional guests entities.
 *
 * @ingroup service_club_event
 */
class AdditionalGuestsListBuilder extends EntityListBuilder {


  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = $this->t('Additional guests ID');
    $header['name'] = $this->t('Name');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /* @var $entity \Drupal\service_club_event\Entity\AdditionalGuests */
    $row['id'] = $entity->id();
    $row['name'] = Link::createFromRoute(
      $entity->label(),
      'entity.additional_guests.edit_form',
      ['additional_guests' => $entity->id()]
    );
    return $row + parent::buildRow($entity);
  }

}
