<?php

namespace Drupal\service_club_event\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\service_club_asset\Entity\AssetEntity;
use Drupal\service_club_event\Entity\EventInformation;
use Drupal\Core\Link;

/**
 * Class AssetListForm.
 */
class AssetListForm extends FormBase {


  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'asset_list_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // Load assets associated with the current event.
    $current_event_id = $this->getRouteMatch()
      ->getParameter('event_information');
    $current_event = EventInformation::load($current_event_id);
    $assigned_assets = $current_event->getEventAssets();

    $registered_assets_content = ['assets' => []];
    $registered_assets_content += ['checked' => []];

    // Create a route for each asset.
    foreach ($assigned_assets as $asset_id) {
      $asset = AssetEntity::load($asset_id['target_id']);

      if ($asset instanceof AssetEntity) {
        // Create a fixed route to asset view.
        $route = '<a href=' . "/admin/structure/asset_entity/" . $asset->id() . ' hreflang="en" target="_blank">' . $asset->getName() . '</a>';

        // Add the information to array's for checkboxes.
        $registered_assets_content['assets'] += [$asset->id() => $route];
        $registered_assets_content['checked'] += [$asset->id() => $asset->id()];

        /**
         * @Todo change the routing from html to php following drupal standards.
         *
         */
        /*
        $route = Drupal\Core\Link::createFromRoute(
          $asset->getName(),
          'entity.asset_entity.edit_form',
          ['asset_entity' => $asset->id()]
        );*/
      }
    }

    $form['event_assets']['title'] = [
      '#type' => 'label',
      '#title' => 'Assets assigned to events.',
    ];

    $form['event_assets']['description'] = [
      '#plain_text' => 'Check the assets that should be assigned to the event. Uncheck assets that should not be assigned to the event.',
    ];

    $form['filter'] = [
      '#type' => 'checkboxes',
      '#title' => 'Filter Assets',
      '#filter' => TRUE,
    ];

    $form['assigned_assets'] = [
      '#type' => 'checkboxes',
      '#options' => $registered_assets_content['assets'],
      '#title' => 'Registered Assets',
      '#default_value' => $registered_assets_content['checked'],
    ];

    // Load assets associated with the current event.
    $all_assets = AssetEntity::loadMultiple();

    $available_assets_content = [];

    // Create a route for each asset.
    foreach ($all_assets as $asset) {
      /**
       * Logic involved is if the array $registered_assets does not contain
       * a value for the current asset id then it is not part of the assigned
       * assets.
       */
      // Skip the asset if it is in registered assets.
      if (empty($registered_assets_content['checked'][$asset->id()])) {
        if ($asset instanceof AssetEntity) {
          // Create a fixed route to asset view.
          $route = '<a href=' . "/admin/structure/asset_entity/" . $asset->id() . ' hreflang="en" target="_blank">' . $asset->getName() . '</a>';

          // Add the information to array's for checkboxes.
          $available_assets_content += [$asset->id() => $route];
        }
      }
    }

    $form['available_assets'] = [
      '#type' => 'checkboxes',
      '#options' => $available_assets_content,
      '#title' => 'Available Assets',
    ];

    $form['actions'] = [
      '#type' => 'actions',
    ];

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);

    // combine assets into a single array. Contains only id's.
    $new_assigned_assets = [];
    $new_assigned_assets += $form_state->getValue('assigned_assets');
    $new_assigned_assets += $form_state->getValue('available_assets');

    // List containing children that are not assigned.
    $children_unassigned = [];

    // Loop for each asset
    foreach ($new_assigned_assets as $asset_id) {
      // If the asset is assigned, test if its children are also assigned.
      if ($asset_id !== 0) {
        $children_unassigned += $this->checkAssignedChildren($asset_id, $new_assigned_assets);
      }
    }

    // Loop through $children_unassigned to find all children that are missing and notify the user in one go.
    foreach ($children_unassigned as $asset_id => $value) {
      drupal_set_message($this->t('Asset: ' . $asset_id . ' may be required be other assets assigned to the event.'), 'warning');
    }

    // Check if the asset is available for this event.
    $event_collision_id = $this->checkEventCollisions($new_assigned_assets);
    if (!empty($event_collision_id)) {
      $form_state->setErrorByName('Asset ACID property',
        $this->t('Assets can only be assigned to one event at a single time. Current event assets collide with event id: ' . $event_collision_id));
    }
  }

  /**
   * Function checks if assets are assigned to two different events entities.
   *
   * @param array $new_assigned_assets
   *   array as a map of asset id's
   * @param \Drupal\service_club_event\Entity\EventInformation $event
   *
   * @return bool
   */
  public function checkEventAssetCollisions(array $new_assigned_assets, EventInformation $event) {
    $event_assets = $event->getEventAssets();

    // Search the "map" of asset id's to check if the asset is assigned elsewhere.
    foreach ($event_assets as $asset) {
      if (!empty($new_assigned_assets[$asset['target_id']])) {
        // Found a collision with assets.
        return TRUE;
      }
    }

    // If no collision was found.
    return FALSE;
  }

  /**
   * @param array $new_assigned_assets
   *   Map of asset id's.
   *
   * @return int
   *   Represents an event id or 0 if no collisions.
   */
  public function checkEventCollisions(array $new_assigned_assets) {
    // Load all the events.
    $all_events = EventInformation::loadMultiple();

    // Load the current event.
    $current_event_id = $this->getRouteMatch()
      ->getParameter('event_information');
    $current_event = EventInformation::load($current_event_id);

    // Save the start and end dates.
    $current_event_start = $current_event->getEventStartDate();
    $current_event_end = $current_event->getEventEndDate();

    // 'event_date_finish''event_date_start'
    foreach ($all_events as $event) {

      // Ignore the current event.
      if ($event->id() !== $current_event_id) {

        // Guardian if to ensure that the entity is an EventInformation entity.
        if ($event instanceof EventInformation) {
          // Store the start and end dates of the next event in the list.
          $start_date = $event->getEventStartDate();
          $end_date = $event->getEventEndDate();

          // If there is an overlap in event dates then check the assets for collisions.
          if (($current_event_start <= $start_date && $start_date <= $current_event_end)
            || ($current_event_start <= $end_date && $end_date <= $current_event_end)
            || ($start_date <= $current_event_start && $current_event_end <= $end_date)) {

            // Check if an asset appears twice.
            if ($this->checkEventAssetCollisions($new_assigned_assets, $event)) {
              // When there is a collision.
              return $event->id();
            }
          }
        }

      }

    }

    // No events had a collision of assets.
    return 0;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $new_assigned_assets = [];

    // Get the assigned_assets checkbox response.
    foreach ($form_state->getValue('assigned_assets') as $asset_id) {
      // If the asset was ticked then save it's id.
      if ($asset_id !== 0) {
        $new_assigned_assets += [count($new_assigned_assets) => ['target_id' => $asset_id]];
      }
    }

    // Get the available_assets checkbox response.
    foreach ($form_state->getValue('available_assets') as $asset_id) {
      // If the asset was ticked then save it's id.
      if ($asset_id !== 0) {
        $new_assigned_assets += [count($new_assigned_assets) => ['target_id' => $asset_id]];
      }
    }

    // Load the event to save the new assigned asset list.
    $current_event_id = $this->getRouteMatch()
      ->getParameter('event_information');
    $current_event = EventInformation::load($current_event_id);

    $current_event->setEventAssets($new_assigned_assets);
    $current_event->save();
  }

  /**
   * Function enforces the parental hierarchy of assets.
   *
   * @param \Drupal\service_club_event\Form\int $current_asset_id
   * @param array $new_assigned_assets
   *
   * @return bool
   *  bool represents if the asset has all it's children assigned
   *  to the event as well.
   */
  public function checkAssignedChildren(int $current_asset_id, array $new_assigned_assets) {
    // Load the given asset.
    $current_asset = AssetEntity::load($current_asset_id);

    // Get a list of its children.
    $children_list = $current_asset->getChildRelationships();

    $children_unassigned = [];

    // Check if each child is in the array of assigned assets.
    foreach ($children_list as $child_id) {
      // If the child has not been assigned when it should stop the form.
      if ($new_assigned_assets[$child_id['target_id']] === 0) {
        $children_unassigned += [$child_id['target_id'] => 1];
      }
    }

    // If every child is already assigned return true.
    return $children_unassigned;
  }

}
