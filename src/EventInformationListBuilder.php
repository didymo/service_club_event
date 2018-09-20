<?php

namespace Drupal\service_club_event;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Link;

/**
 * Defines a class to build a listing of Event information entities.
 *
 * @ingroup service_club_event
 */
class EventInformationListBuilder extends EntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = $this->t('Event information ID');
    $header['name'] = $this->t('Name');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /* @var $entity \Drupal\service_club_event\Entity\EventInformation */
    $row['id'] = $entity->id();
    $row['name'] = Link::createFromRoute(
      $entity->label(),
      'entity.event_information.edit_form',
      ['event_information' => $entity->id()]
    );
    return $row + parent::buildRow($entity);
  }

  /**
   * {@inheritdoc}
   */
  protected function getDefaultOperations(EntityInterface $entity) {
    $operations = [];
    if ($entity->access('asset-list') && $entity->hasLinkTemplate('asset-list')) {
      $operations['asset-list'] = [
        'title' => $this->t('Asset List'),
        'weight' => 1000,
        'url' => $this->ensureDestination($entity->toUrl('asset-list')),
      ];
    }

    if ($entity->access('shift-list') && $entity->hasLinkTemplate('shift-list')) {
      $operations['shift-list'] = [
        'title' => $this->t('Shift List'),
        'weight' => 1000,
        'url' => $this->ensureDestination($entity->toUrl('shift-list')),
      ];
    }

    return $operations + parent::getDefaultOperations($entity);
  }

}
