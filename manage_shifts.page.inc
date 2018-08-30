<?php

/**
 * @file
 * Contains manage_shifts.page.inc.
 *
 * Page callback for Manage shifts entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for Manage shifts templates.
 *
 * Default template: manage_shifts.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_manage_shifts(array &$variables) {
  // Fetch ManageShifts Entity Object.
  $manage_shifts = $variables['elements']['#manage_shifts'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
