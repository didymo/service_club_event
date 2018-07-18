<?php

/**
 * @file
 * Contains additional_guest.page.inc.
 *
 * Page callback for Additional guest entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for Additional guest templates.
 *
 * Default template: additional_guest.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_additional_guest(array &$variables) {
  // Fetch AdditionalGuest Entity Object.
  $additional_guest = $variables['elements']['#additional_guest'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
