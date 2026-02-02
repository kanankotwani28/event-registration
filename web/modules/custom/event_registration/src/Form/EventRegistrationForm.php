<?php

namespace Drupal\event_registration\Form;

use Drupal\Core\Database\Connection;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\event_registration\Service\RegistrationService;
use Drupal\event_registration\Service\ValidationService;
use Drupal\event_registration\Service\EventService;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Event Registration Form.
 */
class EventRegistrationForm extends FormBase {

  /**
   * The database connection service.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * Registration service.
   *
   * @var \Drupal\event_registration\Service\RegistrationService
   */
  protected $registrationService;

  /**
   * Validation service.
   *
   * @var \Drupal\event_registration\Service\ValidationService
   */
  protected $validationService;

  /**
   * Event service.
   *
   * @var \Drupal\event_registration\Service\EventService
   */
  protected $eventService;

  /**
   * Constructor.
   */
  public function __construct(
    Connection $database,
    RegistrationService $registration_service,
    ValidationService $validation_service,
    EventService $event_service
  ) {
    $this->database = $database;
    $this->registrationService = $registration_service;
    $this->validationService = $validation_service;
    $this->eventService = $event_service;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('database'),
      $container->get('event_registration.registration_service'),
      $container->get('event_registration.validation_service'),
      $container->get('event_registration.event_service')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'event_registration_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // Check if registration is currently allowed
    $activeEvents = $this->eventService->getActiveEvents();
    if (empty($activeEvents)) {
      $form['message'] = [
        '#markup' => '<p>' . $this->t('Event registration is currently closed.') . '</p>',
      ];
      return $form;
    }

    $form['name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Full Name'),
      '#required' => TRUE,
      '#maxlength' => 255,
    ];

    $form['email'] = [
      '#type' => 'email',
      '#title' => $this->t('Email Address'),
      '#required' => TRUE,
      '#maxlength' => 255,
    ];

    $form['college'] = [
      '#type' => 'textfield',
      '#title' => $this->t('College Name'),
      '#required' => TRUE,
      '#maxlength' => 255,
    ];

    $form['department'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Department'),
      '#required' => TRUE,
      '#maxlength' => 255,
    ];

    // Get unique categories from active events
    $categories = $this->eventService->getActiveCategories();

    $form['category'] = [
      '#type' => 'select',
      '#title' => $this->t('Category of the Event'),
      '#options' => ['' => $this->t('-- Select Category --')] + $categories,
      '#required' => TRUE,
      '#ajax' => [
        'callback' => '::ajaxUpdateEventDates',
        'wrapper' => 'event-dates-wrapper',
        'event' => 'change',
      ],
    ];

    $category = $form_state->getValue('category');

    $form['event_date'] = [
      '#type' => 'select',
      '#title' => $this->t('Event Date'),
      '#options' => $category ? $this->eventService->getEventDatesByCategory($category) : [],
      '#required' => TRUE,
      '#prefix' => '<div id="event-dates-wrapper">',
      '#suffix' => '</div>',
      '#ajax' => [
        'callback' => '::ajaxUpdateEventNames',
        'wrapper' => 'event-names-wrapper',
        'event' => 'change',
      ],
    ];

    $event_date = $form_state->getValue('event_date');

    $form['event_name'] = [
      '#type' => 'select',
      '#title' => $this->t('Event Name'),
      '#options' => ($category && $event_date) ? $this->eventService->getEventNamesByDateAndCategory($event_date, $category) : [],
      '#required' => TRUE,
      '#prefix' => '<div id="event-names-wrapper">',
      '#suffix' => '</div>',
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Register'),
    ];

    return $form;
  }

  /**
   * AJAX callback to update event dates.
   */
  public function ajaxUpdateEventDates(array &$form, FormStateInterface $form_state) {
    $form['event_date']['#options'] = $this->eventService->getEventDatesByCategory($form_state->getValue('category'));
    $form['event_name']['#options'] = [];
    return $form['event_date'];
  }

  /**
   * AJAX callback to update event names.
   */
  public function ajaxUpdateEventNames(array &$form, FormStateInterface $form_state) {
    $category = $form_state->getValue('category');
    $event_date = $form_state->getValue('event_date');

    if ($category && $event_date) {
      $form['event_name']['#options'] = $this->eventService->getEventNamesByDateAndCategory($event_date, $category);
    } else {
      $form['event_name']['#options'] = [];
    }

    return $form['event_name'];
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    // Validate name
    $name = $form_state->getValue('name');
    if (!$this->validationService->isValidName($name)) {
      $form_state->setErrorByName('name', $this->t('Full name contains invalid characters. Only letters, spaces, and hyphens are allowed.'));
    }

    // Validate email
    $email = $form_state->getValue('email');
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      $form_state->setErrorByName('email', $this->t('Please enter a valid email address.'));
    }

    // Check for duplicate registration
    $event_name_id = $form_state->getValue('event_name');
    if ($event_name_id && $this->validationService->isDuplicateRegistration($email, $event_name_id)) {
      $form_state->setErrorByName('email', $this->t('You have already registered for this event.'));
    }

    // Validate college and department
    $college = $form_state->getValue('college');
    $department = $form_state->getValue('department');

    if (!$this->validationService->isValidText($college)) {
      $form_state->setErrorByName('college', $this->t('College name contains invalid characters.'));
    }

    if (!$this->validationService->isValidText($department)) {
      $form_state->setErrorByName('department', $this->t('Department contains invalid characters.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $event_name_id = $form_state->getValue('event_name');

    // Get event details
    $event = $this->database->select('event_config', 'ec')
      ->fields('ec', ['id', 'event_name', 'category'])
      ->condition('id', $event_name_id)
      ->execute()
      ->fetchAssoc();

    if (!$event) {
      $this->messenger()->addError($this->t('Invalid event selected.'));
      return;
    }

    // Insert registration
    $registration_id = $this->database->insert('event_registration')
      ->fields([
        'event_id' => $event['id'],
        'name' => $form_state->getValue('name'),
        'email' => $form_state->getValue('email'),
        'college' => $form_state->getValue('college'),
        'department' => $form_state->getValue('department'),
        'created' => time(),
      ])
      ->execute();

    // Send confirmation email
    $this->registrationService->sendConfirmationEmail(
      $form_state->getValue('email'),
      $form_state->getValue('name'),
      $event['event_name'],
      $form_state->getValue('event_date'),
      $event['category']
    );

    $this->messenger()->addStatus($this->t('Registration successful! A confirmation email has been sent.'));
    $form_state->setRedirect('<front>');
  }

}

