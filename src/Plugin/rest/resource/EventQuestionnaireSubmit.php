<?php

namespace Drupal\service_club_event\Plugin\rest\resource;

use Drupal\Core\Session\AccountProxyInterface;
use Drupal\rest\ModifiedResourceResponse;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\service_club_event\Entity\EventInformation;
use Drupal\service_club_tmp\Entity\EventClass;
use Drupal\service_club_tmp\Entity\Question;
use Drupal\service_club_tmp\Entity\QuestionResponse;
use Drupal\service_club_tmp\Entity\Questionnaire;
use Drupal\rest\ResourceResponse;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Provides a resource to get view modes by entity and bundle.
 *
 * @RestResource(
 *   id = "event_questionnaire_submit",
 *   label = @Translation("Event Questionnaire submit"),
 *   uri_paths = {
 *     "canonical" = "/event/{event_information}/questionnaire/submit",
 *     "https://www.drupal.org/link-relations/create" = "/event/{event_information}/questionnaire/submit"
 *   }
 * )
 */
class EventQuestionnaireSubmit extends ResourceBase {

  /**
   * A current user instance.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * Constructs a new EventQuestionnaireSubmit object.
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

    // Load the list of questions from the system.
    $question_configs = Question::loadMultiple();

    // Check that all the expected questions are in the json object.
    $errors = array();
    foreach ($question_configs as $question_config) {
      $q_id = $question_config->getId();
      $q_label = $question_config->getLabel();
      // Check the question is in the POST.
      if (!array_key_exists($q_id, $json)) {
        $errors[] = array("Invalid questionnaire submission" => "Missing $q_id.");
      }
      // Check the question matches the one stored in the system.
      if (!array_key_exists($q_label, $json[$q_id])) {
        $errors[] = array("Invalid questionnaire submission" => "Expected $q_id to have an object keyed with '$q_label'.)");
      }
      // Check the question has been answered with a boolean.
      if (!is_bool($json[$q_id][$q_label])) {
        $errors[] = array("Invalid questionnaire submission" => "Expected $q_id : $q_label to map to a boolean.");
      }
    }

    if (!empty($errors)) {
      return new ModifiedResourceResponse($errors, 400);
    }

    // Load the list of Event Classes.
    $ec_configs = EventClass::loadMultiple();
    $ec_ids = [];

    // Pull the Ids and the weights on the Event Class configs.
    foreach ($ec_configs as $ec_config) {
      $ec_ids = $ec_ids + [$ec_config->getId() => $ec_config->getWeight()];
    }

    // Sort based on the weights.
    asort($ec_ids);

    $ec_flags = [];

    // Use the sorted Ids to pull the questions and fill the response.
    foreach ($ec_ids as $ec_id => $weight) {
      $ec_flags = $ec_flags + [$ec_id => FALSE];
    }

    $qr_ids = [];
    // Loop to create the Question Response entities.
    // Note that while we perform the same loop as above we can't begin
    // creating the entities until we have completed validation.
    // We also set the Event Class flags as we loop through.
    foreach ($question_configs as $question_config) {
      $q_id = $question_config->getId();
      $q_label = $question_config->getLabel();

      // If the user answered true, set the flag for that event class to TRUE.
      if ($json[$q_id][$q_label]) {
        $ec_flags[$question_config->getEventClass()] = TRUE;
      }

      $question_response = QuestionResponse::create([
        'type' => 'question_response',
        'name' => 'qn_$event_information__$q_id',
        'question' => $q_label,
        'response' => $json[$q_id][$q_label],
      ]);
      $question_response->save();
      $qr_ids = array_merge($qr_ids, [$question_response->id()]);
    }

    // Set the Event Class to be the lowest in the hierarchy by default.
    end($ec_flags);
    $event_class = key($ec_flags);
    // Grab the highest Event Class in the hierarchy that has been flagged TRUE.
    foreach ($ec_flags as $ec_key => $ec_flag) {
      if ($ec_flag) {
        $event_class = $ec_key;
        break;
      }
    }

    // Create the Questionnaire entity.
    $questionnaire = Questionnaire::create([
      'type' => 'questionnaire',
      'name' => 'qn_$event_information',
      'question_response' => $qr_ids,
      'event_class' => $event_class,
    ]);
    $questionnaire->save();

    $event->setEventClass($event_class);

    return new ModifiedResourceResponse(["Questionnaire successfully submitted." => 1], 200);
  }

}
