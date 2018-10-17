<?php

namespace Drupal\service_club_event\Plugin\rest\resource;

use Drupal\Core\Session\AccountProxyInterface;
use Drupal\rest\ModifiedResourceResponse;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Drupal\service_club_event\Entity\EventInformation;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Provides a resource to get view modes by entity and bundle.
 *
 * @RestResource(
 *   id = "event_state",
 *   label = @Translation("Event state"),
 *   uri_paths = {
 *     "canonical" = "/event/{event_information}/state"
 *   }
 * )
 */
class EventState extends ResourceBase {

  /**
   * A current user instance.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * Constructs a new EventState object.
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
      \Drupal::logger("REST:EventState")->error("Event $event_information does not exist!");
      return (new ResourceResponse(["Event $event_information does not exist!" => -1], 404))->addCacheableDependency($build);
    }

    // If TMP exists return state 2.
    $traffic_management_plan = $event->getTrafficManagementPlan();
    if (isset($traffic_management_plan)) {
      \Drupal::logger("REST:EventState")->info("Event $event_information returned a state of 2!");
      return (new ResourceResponse(["state" => 2], 200))->addCacheableDependency($build);
    }

    // If Questionnaire exists return state 1.
    $event_class = $event->getEventClass();
    if (isset($event_class)) {
      \Drupal::logger("REST:EventState")->info("Event $event_information returned a state of 1!");
      return (new ResourceResponse(["state" => 1], 200))->addCacheableDependency($build);
    }

    // If neither of the above if's triggered return state 0.
    \Drupal::logger("REST:EventState")->info("Event $event_information returned a state of 0!");
    return (new ResourceResponse(["state" => 0], 200))->addCacheableDependency($build);
  }

}
