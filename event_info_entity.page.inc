<?php

/**
 * @file
 * Contains event_info_entity.page.inc.
 *
 * Page callback for Event info entity entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for Event info entity templates.
 *
 * Default template: event_info_entity.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_event_info_entity(array &$variables) {
  // Fetch EventInfoEntity Entity Object.
  $event_info_entity = $variables['elements']['#event_info_entity'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
