<?php

namespace Drupal\service_club_event\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\service_club_asset\Entity\AssetEntity;

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
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
    ];

    $assets = AssetEntity::loadMultiple();
    $names = 'hi';

    $counter = 0;

    foreach ($assets as $asset) {
        //print_r($asset->getName());

        $form['available_assets'][$counter] = [
            //'#type' => 'textarea',
            '#markup' => $asset->getName() . "\r\n\n",
        ];
        $counter++;
    }
/*
      $form['available_assets'][0] = [
          //'#type' => 'textarea',
          '#markup' => 'hi',
      ];

      $form['available_assets'][1] = [
          //'#type' => 'textarea',
          '#markup' => 'test',
      ];*/

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
    // Display result.
    foreach ($form_state->getValues() as $key => $value) {
      drupal_set_message($key . ': ' . $value);
    }

  }

}
