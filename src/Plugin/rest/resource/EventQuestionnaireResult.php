<?php

namespace Drupal\service_club_event\Plugin\rest\resource;

use Drupal\Core\Session\AccountProxyInterface;
use Drupal\rest\ModifiedResourceResponse;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Drupal\service_club_event\Entity\EventInformation;

/**
 * Provides a resource to get view modes by entity and bundle.
 *
 * @RestResource(
 *   id = "event_questionnaire_result",
 *   label = @Translation("Event questionnaire result"),
 *   uri_paths = {
 *     "canonical" = "/event/{event_information}/questionnaire/result"
 *   }
 * )
 */
class EventQuestionnaireResult extends ResourceBase {

  /**
   * A current user instance.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * Constructs a new EventQuestionnaireResult object.
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
      \Drupal::logger("REST:EventQuestionnaireResult")->error("Event $event_information does not exist!");
      return (new ResourceResponse(["Event $event_information does not exist!" => -1], 404))->addCacheableDependency($build);
    }

    $event_class = $event->getEventClass();
    if (!isset($event_class)) {
      \Drupal::logger("REST:EventQuestionnaireResult")->error("A questionnaire has not yet been completed for Event $event_information!");
      return (new ResourceResponse(["Event $event_information has not completed the questionnaire!" => 0], 404))->addCacheableDependency($build);
    }

    \Drupal::logger("REST:EventQuestionnaireResult")->info("Event $event_information Questionnaire Result has been queried successfully.");
    return (new ResourceResponse($event_class->getInformation(), 200))->addCacheableDependency($build);
  }

}
