<?php

namespace Drupal\event_registration\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Connection;
use Drupal\event_registration\Service\EventService;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Admin Listings Controller.
 */
class AdminListingsController extends ControllerBase {

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
   * Display registrations list.
   */
  public function listRegistrations() {
    $form = $this->formBuilder()->getForm('Drupal\event_registration\Form\AdminListingsForm');
    return $form;
  }

  /**
   * Export registrations as CSV.
   */
  public function exportCsv() {
    $registrations = $this->database->select('event_registration', 'er')
      ->fields('er', ['id', 'event_id', 'event_name', 'category', 'event_date', 'name', 'email', 'college', 'department', 'created'])
      ->execute()
      ->fetchAll();

    if (empty($registrations)) {
      $this->messenger()->addWarning($this->t('No registrations found to export.'));
      return $this->redirect('event_registration.admin_listings');
    }

    $response = new StreamedResponse(function () use ($registrations) {
      $handle = fopen('php://output', 'w');

      // CSV headers
      fputcsv($handle, ['ID', 'Event ID', 'Name', 'Email', 'College', 'Department', 'Category', 'Event Date', 'Event Name', 'Registration Date']);

      // CSV data
      foreach ($registrations as $registration) {
        fputcsv($handle, [
          $registration->id,
          $registration->event_id,
          $registration->name,
          $registration->email,
          $registration->college,
          $registration->department,
          $registration->category,
          date('Y-m-d', $registration->event_date),
          $registration->event_name,
          date('Y-m-d H:i:s', $registration->created),
        ]);
      }

      fclose($handle);
    });

    $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
    $response->headers->set('Content-Disposition', 'attachment; filename="registrations_' . time() . '.csv"');

    return $response;
  }

}
