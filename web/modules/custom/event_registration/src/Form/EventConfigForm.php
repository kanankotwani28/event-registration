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
    // Convert datetime form values to timestamps
    $event_date_value = $form_state->getValue('event_date');
    $reg_start_value = $form_state->getValue('reg_start');
    $reg_end_value = $form_state->getValue('reg_end');

    // Convert DrupalDateTime objects to timestamps
    $event_date = $event_date_value instanceof \DateTime ? $event_date_value->getTimestamp() : strtotime($event_date_value);
    $reg_start = $reg_start_value instanceof \DateTime ? $reg_start_value->getTimestamp() : strtotime($reg_start_value);
    $reg_end = $reg_end_value instanceof \DateTime ? $reg_end_value->getTimestamp() : strtotime($reg_end_value);

    if ($reg_start >= $reg_end) {
      $form_state->setErrorByName('reg_end', $this->t('Registration end date must be after registration start date.'));
    }

    if ($reg_start >= $event_date) {
      $form_state->setErrorByName('event_date', $this->t('Event date must be after registration start date.'));
    }

    if ($reg_end >= $event_date) {
      $form_state->setErrorByName('event_date', $this->t('Event date must be after registration end date.'));
    }

    $event_name = $form_state->getValue('event_name');
    if (!preg_match('/^[a-zA-Z0-9\s\-&(),.\']+$/u', $event_name)) {
      $form_state->setErrorByName('event_name', $this->t('Event name contains invalid characters.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Get form values as DateTime objects or strings
    $event_date_value = $form_state->getValue('event_date');
    $reg_start_value = $form_state->getValue('reg_start');
    $reg_end_value = $form_state->getValue('reg_end');

    // Convert to timestamps - handle both DateTime objects and strings
    $event_date_timestamp = $this->dateToTimestamp($event_date_value);
    $reg_start_timestamp = $this->dateToTimestamp($reg_start_value);
    $reg_end_timestamp = $this->dateToTimestamp($reg_end_value);

    $this->database->insert('event_config')
      ->fields([
        'event_name' => $form_state->getValue('event_name'),
        'category' => $form_state->getValue('category'),
        'event_date' => $event_date_timestamp,
        'reg_start' => $reg_start_timestamp,
        'reg_end' => $reg_end_timestamp,
        'created' => time(),
      ])
      ->execute();

    $this->messenger()->addStatus($this->t('Event configuration saved successfully!'));
  }

  /**
   * Helper method to convert DateTime or string to timestamp.
   */
  private function dateToTimestamp($date_value) {
    if ($date_value instanceof \DateTime) {
      return $date_value->getTimestamp();
    }
    if (is_numeric($date_value)) {
      return (int) $date_value;
    }
    if (is_string($date_value)) {
      return strtotime($date_value);
    }
    return time();
  }

}
