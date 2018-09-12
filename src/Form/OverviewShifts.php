<?php

namespace Drupal\service_club_event\Form;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Url;
use Drupal\service_club_event\Entity\EventInformationInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class OverviewShifts.
 */
class OverviewShifts extends FormBase {

    /**
     * The module handler service.
     *
     * @var \Drupal\Core\Extension\ModuleHandlerInterface
     */
    protected $moduleHandler;

    /**
     * The entity manager.
     *
     * @var \Drupal\Core\Entity\EntityManagerInterface
     */
    protected $entityManager;

    /**
     * The term storage handler.
     *
     * @var \Drupal\service_club_event\ShiftStorageInterface
     */
    protected $storageController;

    /**
     * The term list builder.
     *
     * @var \Drupal\Core\Entity\EntityListBuilderInterface
     */
    protected $shiftListBuilder;

    /**
     * The renderer service.
     *
     * @var \Drupal\Core\Render\RendererInterface
     */
    protected $renderer;

    /**
     * Constructs an OverviewShifts object.
     *
     * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
     *   The module handler service.
     * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
     *   The entity manager service.
     * @param \Drupal\Core\Render\RendererInterface $renderer
     *   The renderer service.
     */
    public function __construct(ModuleHandlerInterface $module_handler, EntityManagerInterface $entity_manager, RendererInterface $renderer = NULL) {
        $this->moduleHandler = $module_handler;
        $this->entityManager = $entity_manager;
        $this->storageController = $entity_manager->getStorage('manage_shifts');
        $this->shiftListBuilder = $entity_manager->getListBuilder('manage_shifts');
        $this->renderer = $renderer ?: \Drupal::service('renderer');
    }

    /**
     * {@inheritdoc}
     */
    public static function create(ContainerInterface $container) {
        return new static(
            $container->get('module_handler'),
            $container->get('entity.manager'),
            $container->get('renderer')
        );
    }

    /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'overview_shifts';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, EventInformationInterface $event_information = NULL) {
  $shifts = $this->storageController->getShifts($event_information->id());
  $shift_index = 0;
  // An array of the shifts to be displayed on this page.
  $current_page = [];
  do {
      // In case the shifts are empty.
      if (empty($shifts[$shift_index])){
          break;
      }
    $shift = $shifts[$shift_index];
    $key = 'sid:' . $shift->id();
    $current_page[$key] = $shift;
  } while (isset ($shifts[++$shift_index]));

  $errors = $form_state->getErrors();
  $row_position = 0;

  // Build the actual form.
      $access_control_handler = $this->entityManager->getAccessControlHandler('manage_shifts');
      $create_access = $access_control_handler->createAccess($event_information->id(), NULL, [], TRUE);
      if ($create_access->isAllowed()) {
          $empty = $this->t('No shifts available. <a href=":link">Add shift</a>.', [':link' => Url::fromRoute('entity.manage_shifts.add_form', ['event_information' => $event_information->id()])->toString()]);
      }
      else {
          $empty = $this->t('No shifts available.');
      }
      $form['shifts'] = [
          '#type' => 'table',
          '#empty' => $empty,
          '#header' => [
              'shift' => $this->t('Name'),
              'operations' => $this->t('Operations'),
              'weight' => $this->t('Weight'),
          ],
      ];
      $this->renderer->addCacheableDependency($form['shifts'], $create_access);

      // Only allow access to changing weights if the user has update access for
      // all terms.
      $change_weight_access = AccessResult::allowed();
      foreach ($current_page as $key => $shift) {
          $form['shifts'][$key] = [
              'shift' => [],
              'operations' => [],
              'weight' => [],
          ];
          /** @var $term \Drupal\Core\Entity\EntityInterface */
          $shift = $this->entityManager->getTranslationFromContext($shift);
          $form['shifts'][$key]['#shift'] = $shift;
          $form['shifts'][$key]['shift'] = [
              '#type' => 'link',
              '#title' => $shift->getName(),
              '#url' => $shift->urlInfo(),
          ];

          $update_access = $shift->access('update', NULL, TRUE);
          $change_weight_access = $change_weight_access->andIf($update_access);

          if ($update_access->isAllowed()) {
              $form['shifts'][$key]['weight'] = [
                  '#type' => 'weight',
                  '#title' => $this->t('Weight for added shift'),
                  '#title_display' => 'invisible',
                  '#default_value' => $shift->getWeight(),
                  '#attributes' => ['class' => ['shift-weight']],
              ];
          }

          if ($operations = $this->shiftListBuilder->getOperations($shift)) {
              $form['shifts'][$key]['operations'] = [
                  '#type' => 'operations',
                  '#links' => $operations,
              ];
          }
          // Add an error class if this row contains a form error.
          foreach ($errors as $error_key => $error) {
              if (strpos($error_key, $key) === 0) {
                  $form['shifts'][$key]['#attributes']['class'][] = 'error';
              }
          }
          $row_position++;
      }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Display result.
    foreach ($form_state->getValues() as $key => $value) {
      drupal_set_message($key . ': ' . $value);
    }

  }

}
