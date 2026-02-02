<?php

namespace Drupal\event_registration\Service;

use Drupal\Core\Database\Connection;
use Drupal\event_registration\Mail\MailHandler;

/**
 * Registration Service to handle registration operations.
 */
class RegistrationService {

  /**
   * The database connection service.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * Mail handler.
   *
   * @var \Drupal\event_registration\Mail\MailHandler
   */
  protected $mailHandler;

  /**
   * Constructor.
   */
  public function __construct(Connection $database, MailHandler $mail_handler) {
    $this->database = $database;
    $this->mailHandler = $mail_handler;
  }

  /**
   * Send confirmation email to user.
   */
  public function sendConfirmationEmail($email, $name, $event_name, $event_date, $category) {
    // Send user confirmation
    $this->mailHandler->sendUserConfirmation($email, $name, $event_name, $event_date, $category);

    // Send admin notification
    $this->mailHandler->sendAdminNotification($name, $email, $event_name, $event_date, $category);
  }

  /**
   * Get registration count for an event.
   */
  public function getRegistrationCount($event_id) {
    return $this->database->select('event_registration', 'er')
      ->condition('event_id', $event_id)
      ->countQuery()
      ->execute()
      ->fetchField();
  }

  /**
   * Get all registrations for an event.
   */
  public function getEventRegistrations($event_id) {
    return $this->database->select('event_registration', 'er')
      ->fields('er')
      ->condition('event_id', $event_id)
      ->orderBy('created', 'DESC')
      ->execute()
      ->fetchAll();
  }

}
