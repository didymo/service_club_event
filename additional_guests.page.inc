<?php

/**
 * @file
 * Contains additional_guests.page.inc.
 *
 * Page callback for Additional guests entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for Additional guests templates.
 *
 * Default template: additional_guests.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_additional_guests(array &$variables) {
  // Fetch AdditionalGuests Entity Object.
  $additional_guests = $variables['elements']['#additional_guests'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
