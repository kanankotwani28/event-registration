<?php

namespace Drupal\event_registration\Service;

use Drupal\Core\Database\Connection;

/**
 * Validation Service to handle validation logic.
 */
class ValidationService {

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
   * Validate name (only letters, spaces, and hyphens).
   */
  public function isValidName($name) {
    return preg_match('/^[a-zA-Z\s\-\']+$/u', $name) ? TRUE : FALSE;
  }

  /**
   * Validate text field (no special characters except basic ones).
   */
  public function isValidText($text) {
    return preg_match('/^[a-zA-Z0-9\s\-().,&\']+$/u', $text) ? TRUE : FALSE;
  }

  /**
   * Check for duplicate registration (email + event date).
   */
  public function isDuplicateRegistration($email, $event_date) {
    // Get the event_id for this event_date
    $event = $this->database->select('event_config', 'ec')
      ->fields('ec', ['id'])
      ->condition('event_date', $event_date)
      ->execute()
      ->fetchAssoc();

    if (!$event) {
      return FALSE;
    }

    $count = $this->database->select('event_registration', 'er')
      ->condition('email', $email)
      ->condition('event_id', $event['id'])
      ->countQuery()
      ->execute()
      ->fetchField();

    return $count > 0 ? TRUE : FALSE;
  }

  /**
   * Validate email format.
   */
  public function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) ? TRUE : FALSE;
  }

}
