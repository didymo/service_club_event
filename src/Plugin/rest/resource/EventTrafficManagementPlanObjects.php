<?php

namespace Drupal\service_club_event\Plugin\rest\resource;

use Drupal\Core\Session\AccountProxyInterface;
use Drupal\rest\ModifiedResourceResponse;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Psr\Log\LoggerInterface;
use Drupal\service_club_event\Entity\EventInformation;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Provides a resource to get view modes by entity and bundle.
 *
 * @RestResource(
 *   id = "event_traffic_management_plan_objects",
 *   label = @Translation("Event traffic management plan objects"),
 *   uri_paths = {
 *     "canonical" = "/event/{event_information}/tmp/objects",
 *     "https://www.drupal.org/link-relations/create" = "/event/{event_information}/tmp/objects"
 *   }
 * )
 */
class EventTrafficManagementPlanObjects extends ResourceBase {

  /**
   * A current user instance.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * Constructs a new EventTrafficManagementPlanObjects object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param array $serializer_formats
   *   The available serialization formats.
   * @param \Psr\Log\LoggerInterface $logger
   *   A logger instance.
   * @param \Drupal\Core\Session\AccountProxyInterface $current_user
   *   A current user instance.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    array $serializer_formats,
    LoggerInterface $logger,
    AccountProxyInterface $current_user) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);

    $this->currentUser = $current_user;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->getParameter('serializer.formats'),
      $container->get('logger.factory')->get('service_club_event'),
      $container->get('current_user')
    );
  }

  /**
   * Turns the JSON monster array into a string to be saved.
   *
   * @param string $json
   *   The JSON array.
   *
   * @return string
   *   The JSON array encoded as a string.
   */
  private function encode_string($json) {
    $first_run = TRUE;
    $blackmagic = "";
    foreach ($json as $key => $value) {
      if(!$first_run) {
        if (is_numeric($value)) {
          $blackmagic = $blackmagic . "|";
        }
        else {
          $blackmagic = $blackmagic . ",";
        }
      }
      else {
        $first_run = FALSE;
      }
      if(!is_integer($key)){
        $blackmagic = $blackmagic . "$key:";
      }
      if (is_array($value)) {
        $blackmagic = $blackmagic . "{" . $this->encode_string($value) . "}";
      }
      else {
        $blackmagic = $blackmagic . "$value";
      }
    }
    return $blackmagic;
  }

  /**
   * Turns the saved string into the JSON monster array.
   *
   * @param string $blackmagic
   *   The string representing the JSON array.
   *
   * @return array
   *   The JSON array restored from the string.
   */
  private function decode_string($blackmagic) {
    // If entering an object, strip the surrounding brackets.
    if(!empty($blackmagic) && $blackmagic[0] === "{") {
      $blackmagic = substr($blackmagic, 1, strlen($blackmagic) - 2);
    }

    // USE CASE: NESTED ARRAY, we just stripped the objects brackets and there is another set.
    if (!empty($blackmagic) && $blackmagic[0] === "{") {
      // USE CASE : Need to recurse each object in the array.
      $start = 0;
      $current = $start;
      $temp = array();
      while($current < strlen($blackmagic)) {
        if ($blackmagic[$current] === "{") {
          // Find the where the object closes.
          $open = 1;
          $closed = 0;
          while ($open > $closed) {
            $current++;
            if ($blackmagic[$current] === "{") {
              $open++;
            } else if ($blackmagic[$current] === "}") {
              $closed++;
            }
          }
          // Current equals the closing bracket at this point.
          $temp[] = $this->decode_string(substr($blackmagic, $start, (($current + 1) - $start)));

          // Make start equal to the next opening bracket
          $start = $current + 2;
          $current = $start;
        }
        else {
          // We should never get here, if we get here the JSON is bad.
          \Drupal::logger('JSON_BUILDER')->error("Bad JSON: $blackmagic");
          break;
        }
      }

      return $temp;
    }

    // USE CASE: EMPTY OBJECT.
    if (empty($blackmagic)) {
      return array();
    }

    // Find where the next colon and comma are.
    $colon = strpos($blackmagic, ":");
    $comma = strpos($blackmagic, ",");

    // USE CASE: COORDINATE PAIR.
    if ($colon === FALSE && $comma === FALSE) {
      $coord = strpos($blackmagic, "|");
      if ($coord !== FALSE) {
        $array = explode("|", $blackmagic);
        $temp = array();
        foreach ($array as $value) {
          array_push($temp, (double) $value);
        }
        return $temp;
      }
    }
    // USE CASE: PAIR OF COORDINATE PAIRS, this else if is now redundant because the NEST ARRAY case above handles it.
    else if ($colon === FALSE && $comma !== FALSE) {
      $array = explode(",", $blackmagic);
      $temp = array();
      foreach ($array as $value) {
        $temp[] = $this->decode_string($value);
      }
      // Seriously this is never called.
      \Drupal::logger('NEVER CALLED')->error("USE CASE: PAIR OF COORDINATE PAIRS");
      return $temp;
    }
    // USE CASE: KEY:VALUE PAIR
    else if ($colon !== FALSE && $comma === FALSE) {
      $key = substr($blackmagic, 0, $colon);
      $value = substr($blackmagic, ($colon + 1));
      return array($key => $value);
    }

    // If we didn't find a colon or comma we need to guard against it.
    if ($colon === FALSE) {
      $colon = strlen($blackmagic);
    }
    if ($comma === FALSE) {
      $comma = strlen($blackmagic);
    }

    // USE CASE : Need to recurse over the unknown number of nested JSON objects.
    $start = 0;
    $current = $colon + 1;
    $temp = array();
    while($current < strlen($blackmagic)) {
      // If the value is a nested object, recursively handle that object.
      if ($blackmagic[$current] === "{") {
        // Find the where the object closes.
        $open = 1;
        $closed = 0;
        while ($open > $closed) {
          $current++;
          if ($blackmagic[$current] === "{") {
            $open++;
          } else if ($blackmagic[$current] === "}") {
            $closed++;
          }
        }
        // Get the key substring.
        $key = substr($blackmagic, $start, ($colon - $start));
        // Current equals the closing bracket at this point.
        $temp[] = array($key => $this->decode_string(substr($blackmagic, ($colon + 1), ($current - $colon))));

        // Make start equal to the pos after the closing bracket and the comma.
        $start = $current + 2;

        $colon = strpos($blackmagic, ":", $start);
        $comma = strpos($blackmagic, ",", $start);
        // If there are no more colons we are done parsing the JSON in this branch.
        if ($colon === FALSE) {
          break;
        }
        // If there are no more commas there could still be one more 'key:value' pair.
        if ($comma === FALSE) {
          $comma = strlen($blackmagic);
        }
        $current = $colon + 1;
      }
      // Else the value wasn't a nested object and we have to handle that differently.
      else {
        $temp[] = $this->decode_string(substr($blackmagic, $start, ($comma - $start)));
        $start = $comma + 1;
        $colon = strpos($blackmagic, ":", $start);
        $comma = strpos($blackmagic, ",", $start);
        // If there are no more colons we are done parsing the JSON in this branch.
        if ($colon === FALSE) {
          break;
        }
        // If there are no more commas there could still be one more 'key:value' pair.
        if ($comma === FALSE) {
          $comma = strlen($blackmagic);
        }
        $current = $colon + 1;
      }
    }

    // We need to condense the arrays down because the recursion adds too many array layers.
    $response = array();
    foreach ($temp as $key => $value) {
      if (is_array($value)) {
        foreach ($value as $little_key => $little_value) {
          $response[$little_key] = $little_value;
        }
      }
      else {
        $response[$key] = $value;
      }
    }

    return $response;
  }

  /**
   * Responds to POST requests.
   *
   * @param string $event_information
   *   The entity object.
   * @param string $json
   *   The JSON object.
   *
   * @return \Drupal\rest\ModifiedResourceResponse
   *   The HTTP response object.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\HttpException
   *   Throws exception expected.
   */
  public function post($event_information, $json) {

    // Use current user after pass authentication to validate access.
    if (!$this->currentUser->hasPermission('access content')) {
      throw new AccessDeniedHttpException();
    }

    // Load Event.
    $event = EventInformation::load($event_information);
    if (!isset($event)) {
      return new ModifiedResourceResponse(["Event $event_information does not exist!"], 404);
    }

    // Get Traffic Management Plan.
    $tmp = $event->getTrafficManagementPlan();
    if (!isset($tmp)) {
      \Drupal::logger("REST:EventTMPObjects")->error("Event $event_information does not have a TMP!");
      return new ModifiedResourceResponse(["Event $event_information does not have a Traffic Management Plan!" => 1], 404);
    }

    $blackmagic = $this->encode_string($json);

    $tmp->setObjects($blackmagic);

    \Drupal::logger("REST:EventTMPObjects")->info("Event $event_information TMP Objects have been updated successfully.");
    return new ModifiedResourceResponse(["Traffic Management Plan successfully updated." => 2], 200);
  }

  /**
   * Responds to GET requests.
   *
   * @param string $event_information
   *   The Event Id.
   *
   * @return \Drupal\rest\ResourceResponse
   *   The HTTP response object.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\HttpException
   *   Throws exception expected.
   */
  public function get($event_information) {

    // Use current user after pass authentication to validate access.
    if (!$this->currentUser->hasPermission('access content')) {
      throw new AccessDeniedHttpException();
    }

    // Prevent the response from caching.
    // TODO: Have the cache reset when the event's state is edited,
    // which will allow this code to be removed.
    $build = array(
      '#cache' => array(
        'max-age' => 0,
      ),
    );

    // Load Event.
    $event = EventInformation::load($event_information);
    if (!isset($event)) {
      \Drupal::logger("REST:EventTMPObjects")->error("Event $event_information does not exist!");
      return (new ResourceResponse(["Event $event_information does not exist!" => -1], 404))->addCacheableDependency($build);
    }

    // Get Traffic Management Plan.
    $tmp = $event->getTrafficManagementPlan();
    if (!isset($tmp)) {
      \Drupal::logger("REST:EventTMPObjects")->error("Event $event_information does not have a TMP!");
      return (new ResourceResponse(["Event $event_information does not have a Traffic Management Plan!" => 1], 404))->addCacheableDependency($build);
    }

    // Makes a lot of warning noise that we don't care about.
    @$response = $this->decode_string($tmp->getObjects());

    // If none of the above if's triggered return the TMP Bounds.
    \Drupal::logger("REST:EventTMPObjects")->info("Event $event_information TMP Objects have been queried successfully.");
    return (new ResourceResponse($response, 200))->addCacheableDependency($build);
  }

}
