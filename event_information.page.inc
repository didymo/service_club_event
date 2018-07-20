<?php

/**
 * @file
 * Contains event_information.page.inc.
 *
 * Page callback for Event information entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for Event information templates.
 *
 * Default template: event_information.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_event_information(array &$variables) {
  // Fetch EventInformation Entity Object.
  $event_information = $variables['elements']['#event_information'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
