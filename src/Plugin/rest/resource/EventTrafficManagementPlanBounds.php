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
 *   id = "event_traffic_management_plan_bounds",
 *   label = @Translation("Event traffic management plan bounds"),
 *   uri_paths = {
 *     "canonical" = "/event/{event_information}/tmp/bounds",
 *     "https://www.drupal.org/link-relations/create" = "/event/{event_information}/tmp/bounds"
 *   }
 * )
 */
class EventTrafficManagementPlanBounds extends ResourceBase {

  /**
   * A current user instance.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * Constructs a new EventTrafficManagementPlanBounds object.
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
      return new ModifiedResourceResponse(["Event $event_information does not exist!" => -1], 404);
    }

    // Dissect json string into an array for checking.
    $north_bound = $json["leftTop"]["latitude"];
    $east_bound = $json["rightBottom"]["longitude"];
    $south_bound = $json["rightBottom"]["latitude"];
    $west_bound = $json["leftTop"]["longitude"];

    // Check values are valid.
    $errors = array();
    if (($north_bound > 90) || ($north_bound < -90)) {
      $errors[] = array("North bound needs to be a latitude between -90 and 90" => "Given: $north_bound");
    }
    if (($east_bound > 180) || ($east_bound < -180)) {
      $errors[] = array("East bound needs to be a longitude between -180 and 180" => "Given: $east_bound");
    }
    if (($south_bound > 90) || ($south_bound < -90)) {
      $errors[] = array("South bound needs to be a latitude between -90 and 90" => "Given: $south_bound");
    }
    if (($west_bound > 180) || ($west_bound < -180)) {
      $errors[] = array("West bound needs to be a longitude between -180 and 180" => "Given: $west_bound");
    }

    if (!empty($errors)) {
      \Drupal::logger("REST:EventTMPBounds")->error("Invalid TMP bounds were submitted for Event $event_information!");
      return new ModifiedResourceResponse($errors, 400);
    }

    // Get Traffic Management Plan.
    $tmp = $event->getTrafficManagementPlan();
    if (!isset($tmp)) {
      \Drupal::logger("REST:EventTMPBounds")->error("Event $event_information does not have a TMP!");
      return new ModifiedResourceResponse(["Event $event_information does not have a Traffic Management Plan!" => 1], 404);
    }

    $tmp->setBounds($json);

    \Drupal::logger("REST:EventTMPBounds")->info("Event $event_information TMP Bounds have been updated successfully.");
    return new ModifiedResourceResponse(["Traffic Management Plan Bounds successfully updated." => 2], 200);
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
      \Drupal::logger("REST:EventTMPBounds")->error("Event $event_information does not exist!");
      return (new ResourceResponse(["Event $event_information does not exist!" => -1], 404))->addCacheableDependency($build);
    }

    // Get Traffic Management Plan.
    $tmp = $event->getTrafficManagementPlan();
    if (!isset($tmp)) {
      \Drupal::logger("REST:EventTMPBounds")->error("Event $event_information does not have a TMP!");
      return (new ResourceResponse(["Event $event_information does not have a Traffic Management Plan!" => 1], 404))->addCacheableDependency($build);
    }

    // If none of the above if's triggered return the TMP Bounds.
    \Drupal::logger("REST:EventTMPBounds")->info("Event $event_information TMP Bounds have been queried successfully.");
    return (new ResourceResponse($tmp->getBounds(), 200))->addCacheableDependency($build);
  }

}
