<?php

namespace Drupal\event_registration\Form;

use Drupal\Component\Utility\Html;
use Drupal\Core\Database\Connection;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\event_registration\Service\EventService;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Admin Listings Form.
 */
class AdminListingsForm extends FormBase {

  /**
   * The database connection service.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * Event service.
   *
   * @var \Drupal\event_registration\Service\EventService
   */
  protected $eventService;

  /**
   * Constructor.
   */
  public function __construct(Connection $database, EventService $event_service) {
    $this->database = $database;
    $this->eventService = $event_service;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('database'),
      $container->get('event_registration.event_service')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'event_registration_admin_listings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // Get all unique event dates
    $dates = $this->database->select('event_config', 'ec')
      ->fields('ec', ['event_date'])
      ->distinct()
      ->orderBy('event_date', 'DESC')
      ->execute()
      ->fetchCol();

    $date_options = ['' => $this->t('-- Select Date --')];
    foreach ($dates as $timestamp) {
      $date_options[$timestamp] = date('Y-m-d', $timestamp);
    }

    $form['event_date'] = [
      '#type' => 'select',
      '#title' => $this->t('Event Date'),
      '#options' => $date_options,
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
      '#options' => $event_date ? $this->getEventNamesByDate($event_date) : [],
      '#prefix' => '<div id="event-names-wrapper">',
      '#suffix' => '</div>',
      '#ajax' => [
        'callback' => '::ajaxLoadRegistrations',
        'wrapper' => 'registrations-table-wrapper',
        'event' => 'change',
      ],
    ];

    $form['export'] = [
      '#type' => 'link',
      '#title' => $this->t('Export as CSV'),
      '#url' => \Drupal\Core\Url::fromRoute('event_registration.admin_export_csv'),
      '#attributes' => [
        'class' => ['button'],
      ],
    ];

    // Display registrations table
    $event_name_id = $form_state->getValue('event_name');
    if ($event_date && $event_name_id) {
      $form['registrations'] = [
        '#type' => 'markup',
        '#markup' => $this->getRegistrationsTable($event_name_id, $event_date),
        '#prefix' => '<div id="registrations-table-wrapper">',
        '#suffix' => '</div>',
      ];
    } else {
      $form['registrations'] = [
        '#type' => 'markup',
        '#markup' => '<div id="registrations-table-wrapper"></div>',
      ];
    }

    return $form;
  }

  /**
   * AJAX callback to update event names.
   */
  public function ajaxUpdateEventNames(array &$form, FormStateInterface $form_state) {
    $event_date = $form_state->getValue('event_date');
    if ($event_date) {
      $form['event_name']['#options'] = $this->getEventNamesByDate($event_date);
    } else {
      $form['event_name']['#options'] = [];
    }
    return $form['event_name'];
  }

  /**
   * AJAX callback to load registrations.
   */
  public function ajaxLoadRegistrations(array &$form, FormStateInterface $form_state) {
    $event_date = $form_state->getValue('event_date');
    $event_name_id = $form_state->getValue('event_name');

    if ($event_date && $event_name_id) {
      $markup = $this->getRegistrationsTable($event_name_id, $event_date);
    } else {
      $markup = '';
    }

    $form['registrations']['#markup'] = $markup;
    return $form['registrations'];
  }

  /**
   * Get event names by date.
   */
  private function getEventNamesByDate($event_date) {
    $events = $this->database->select('event_config', 'ec')
      ->fields('ec', ['id', 'event_name'])
      ->condition('event_date', $event_date)
      ->execute()
      ->fetchAll();

    $options = ['' => $this->t('-- Select Event --')];
    foreach ($events as $event) {
      $options[$event->id] = $event->event_name;
    }

    return $options;
  }

  /**
   * Get registrations table HTML.
   */
  private function getRegistrationsTable($event_name_id, $event_date) {
    $registrations = $this->database->select('event_registration', 'er')
      ->fields('er', ['id', 'name', 'email', 'college', 'department', 'created'])
      ->condition('event_id', $event_name_id)
      ->orderBy('created', 'DESC')
      ->execute()
      ->fetchAll();

    if (empty($registrations)) {
      return '<p>' . $this->t('No registrations found for this event.') . '</p>';
    }

    $rows = [];
    foreach ($registrations as $registration) {
      $rows[] = [
        Html::escape($registration->name),
        Html::escape($registration->email),
        Html::escape(date('Y-m-d', $event_date)),
        Html::escape($registration->college),
        Html::escape($registration->department),
        Html::escape(date('Y-m-d H:i:s', $registration->created)),
      ];
    }

    $header = [
      $this->t('Name'),
      $this->t('Email'),
      $this->t('Event Date'),
      $this->t('College Name'),
      $this->t('Department'),
      $this->t('Submission Date'),
    ];

    $html = '<h3>' . $this->t('Total Participants: @count', ['@count' => count($registrations)]) . '</h3>';
    $html .= \Drupal\Core\Render\Markup::create('<table class="registrations-table"><thead><tr><th>' . implode('</th><th>', $header) . '</th></tr></thead><tbody>');

    foreach ($rows as $row) {
      $html .= '<tr><td>' . implode('</td><td>', $row) . '</td></tr>';
    }

    $html .= '</tbody></table>';

    return $html;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // This form doesn't submit, it only displays data
  }

}
