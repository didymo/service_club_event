<?php

namespace Drupalservice_club_event;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\ContentEntityStorageInterface;

/**
 * Defines an interface for manage_shifts entity storage classes.
 */
interface ShiftStorageInterface extends ContentEntityStorageInterface {


    /**
     * Finds all shifts of a given event.
     *
     * @param string $eid
     *  Event ID to retrieve shifts for.
     *
     * @return \Drupal\service_club_event\Entity\ManageShiftsInterface[]
     *   An array of shift object which are atached to the event $eid.
     */
    public function getShifts($eid);
  /**
   * Count the number of nodes in a given vocabulary ID.
   *
   * @param string $vid
   *   Vocabulary ID to retrieve terms for.
   *
   * @return int
   *   A count of the nodes in a given vocabulary ID.
   */
  public function nodeCount($eid);

  /**
   * Reset the weights for a given vocabulary ID.
   *
   * @param string $vid
   *   Vocabulary ID to retrieve terms for.
   */
  public function resetWeights($eid);

  /**
   * Returns all terms used to tag some given nodes.
   *
   * @param array $nids
   *   Node IDs to retrieve terms for.
   * @param array $vocabs
   *   (optional) A vocabularies array to restrict the term search. Defaults to
   *   empty array.
   * @param string $langcode
   *   (optional) A language code to restrict the term search. Defaults to NULL.
   *
   * @return array
   *   An array of nids and the term entities they were tagged with.
   */
  public function getNodeTerms(array $nids, array $vocabs = [], $langcode = NULL);

}
