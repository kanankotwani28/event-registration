<?php

namespace Drupal\event_registration\Form;

use Drupal\Core\Database\Connection;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Event Configuration Form.
 */
class EventConfigForm extends FormBase {

  /**
   * The database connection service.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * Constructor.
   */
  public function __construct(Connection $database) {
    $this->database = $database;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('database')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'event_config_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['event_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Event Name'),
      '#required' => TRUE,
      '#maxlength' => 255,
    ];

    $form['category'] = [
      '#type' => 'select',
      '#title' => $this->t('Category of the Event'),
      '#options' => [
        'online_workshop' => $this->t('Online Workshop'),
        'hackathon' => $this->t('Hackathon'),
        'conference' => $this->t('Conference'),
        'one_day_workshop' => $this->t('One-day Workshop'),
      ],
      '#required' => TRUE,
    ];

    $form['event_date'] = [
      '#type' => 'datetime',
      '#title' => $this->t('Event Date'),
      '#required' => TRUE,
      '#date_format' => 'Y-m-d H:i',
    ];

    $form['reg_start'] = [
      '#type' => 'datetime',
      '#title' => $this->t('Event Registration Start Date'),
      '#required' => TRUE,
      '#date_format' => 'Y-m-d H:i',
    ];

    $form['reg_end'] = [
      '#type' => 'datetime',
      '#title' => $this->t('Event Registration End Date'),
      '#required' => TRUE,
      '#date_format' => 'Y-m-d H:i',
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save Event Configuration'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    // Get form values from the datetime elements
    // Drupal datetime form element returns DrupalDateTime objects
    $event_date_value = $form_state->getValue('event_date');
    $reg_start_value = $form_state->getValue('reg_start');
    $reg_end_value = $form_state->getValue('reg_end');

    // Convert all to Unix timestamps for reliable comparison
    $event_date_timestamp = $this->dateToTimestamp($event_date_value);
    $reg_start_timestamp = $this->dateToTimestamp($reg_start_value);
    $reg_end_timestamp = $this->dateToTimestamp($reg_end_value);

    // Validate date sequence: reg_start < reg_end < event_date
    if ($reg_start_timestamp >= $reg_end_timestamp) {
      $form_state->setErrorByName('reg_end', $this->t('Registration end date must be after registration start date.'));
    }

    if ($reg_start_timestamp >= $event_date_timestamp) {
      $form_state->setErrorByName('event_date', $this->t('Event date must be after registration start date.'));
    }

    if ($reg_end_timestamp >= $event_date_timestamp) {
      $form_state->setErrorByName('event_date', $this->t('Event date must be after registration end date.'));
    }

    // Validate event name format
    $event_name = $form_state->getValue('event_name');
    if (!preg_match('/^[a-zA-Z0-9\s\-&(),.\']+$/u', $event_name)) {
      $form_state->setErrorByName('event_name', $this->t('Event name contains invalid characters.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Get form values - Drupal datetime form element returns DrupalDateTime objects
    $event_date_value = $form_state->getValue('event_date');
    $reg_start_value = $form_state->getValue('reg_start');
    $reg_end_value = $form_state->getValue('reg_end');

    // Convert all dates to Unix timestamps for database storage
    // This ensures timezone-independent, reliable date storage and comparison
    $event_date_timestamp = $this->dateToTimestamp($event_date_value);
    $reg_start_timestamp = $this->dateToTimestamp($reg_start_value);
    $reg_end_timestamp = $this->dateToTimestamp($reg_end_value);

    // Insert event into database with all dates as Unix timestamps
    $this->database->insert('event_config')
      ->fields([
        'event_name' => trim($form_state->getValue('event_name')),
        'category' => $form_state->getValue('category'),
        'event_date' => $event_date_timestamp,      // Unix timestamp
        'reg_start' => $reg_start_timestamp,        // Unix timestamp
        'reg_end' => $reg_end_timestamp,            // Unix timestamp
        'created' => time(),                        // Unix timestamp (current time)
      ])
      ->execute();

    $this->messenger()->addStatus($this->t('Event configuration saved successfully!'));
  }

  /**
   * Helper method to convert DateTime or string to Unix timestamp.
   *
   * Handles multiple input types:
   * - DrupalDateTime objects (from Drupal form element)
   * - PHP DateTime objects
   * - Numeric timestamps (passthrough)
   * - String dates (parsed with strtotime as fallback)
   *
   * @param mixed $date_value
   *   The date value to convert.
   *
   * @return int
   *   Unix timestamp (seconds since Jan 1, 1970 UTC).
   */
  private function dateToTimestamp($date_value) {
    // Handle DrupalDateTime objects (what the datetime form element returns)
    if (class_exists('\Drupal\Core\Datetime\DrupalDateTime')) {
      if ($date_value instanceof \Drupal\Core\Datetime\DrupalDateTime) {
        return (int) $date_value->getTimestamp();
      }
    }

    // Handle standard PHP DateTime objects
    if ($date_value instanceof \DateTime) {
      return (int) $date_value->getTimestamp();
    }

    // If already a numeric timestamp, validate and return it
    if (is_numeric($date_value)) {
      return (int) $date_value;
    }

    // Fallback: try to parse string dates
    if (is_string($date_value) && !empty($date_value)) {
      $timestamp = strtotime($date_value);
      if ($timestamp !== FALSE) {
        return $timestamp;
      }
    }

    // Default: return current timestamp if conversion fails
    return time();
  }

}
