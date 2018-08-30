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
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $new_assigned_assets = [];

    // Counter is used as array position and needs to increment over both checkbox responses.
    $counter = 0;

    // Display result.
    foreach ($form_state->getValues() as $key => $value) {
      drupal_set_message($key . ': ' . $value);

      // Get the available_assets checkbox response.
      if ($key === 'available_assets') {
        foreach ($value as $asset_id) {
          // If the asset was ticked then save it's id.
          if ($asset_id != 0) {
            $new_assigned_assets += [$counter++ => ['target_id' => $asset_id]];
          }
        }
      }

      // Get the assigned_assets checkbox response.
      if ($key === 'assigned_assets') {
        foreach ($value as $asset_id) {
          // If the asset was ticked then save it's id.
          if ($asset_id != 0) {
            $new_assigned_assets += [$counter++ => ['target_id' => $asset_id]];
          }
        }
      }

    }

    // Load the event to save the new assigned asset list.
    $current_event_id = $this->getRouteMatch()->getParameter('event_information');
    $current_event = EventInformation::load($current_event_id);

    $current_event->setEventAssets($new_assigned_assets);
    $current_event->save();
  }

}
