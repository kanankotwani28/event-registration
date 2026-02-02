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
    $event_date = strtotime($form_state->getValue('event_date'));
    $reg_start = strtotime($form_state->getValue('reg_start'));
    $reg_end = strtotime($form_state->getValue('reg_end'));

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
    $event_date = \DateTime::createFromFormat('Y-m-d H:i', $form_state->getValue('event_date'));
    $reg_start = \DateTime::createFromFormat('Y-m-d H:i', $form_state->getValue('reg_start'));
    $reg_end = \DateTime::createFromFormat('Y-m-d H:i', $form_state->getValue('reg_end'));

    $this->database->insert('event_config')
      ->fields([
        'event_name' => $form_state->getValue('event_name'),
        'category' => $form_state->getValue('category'),
        'event_date' => $event_date ? $event_date->getTimestamp() : time(),
        'reg_start' => $reg_start ? $reg_start->getTimestamp() : time(),
        'reg_end' => $reg_end ? $reg_end->getTimestamp() : time(),
        'created' => time(),
      ])
      ->execute();

    $this->messenger()->addStatus($this->t('Event configuration saved successfully!'));
  }

}
