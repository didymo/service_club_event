<?php

/**
 * @file
 * Contains volunteer_registration.page.inc.
 *
 * Page callback for Volunteer registration entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for Volunteer registration templates.
 *
 * Default template: volunteer_registration.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_volunteer_registration(array &$variables) {
  // Fetch VolunteerRegistration Entity Object.
  $volunteer_registration = $variables['elements']['#volunteer_registration'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
