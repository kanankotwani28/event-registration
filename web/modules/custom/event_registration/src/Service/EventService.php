<?php

namespace Drupal\event_registration\Service;

use Drupal\Core\Database\Connection;

/**
 * Event Service to handle event-related operations.
 */
class EventService {

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
   * Get all active events (within registration period).
   */
  public function getActiveEvents() {
    $current_time = time();

    return $this->database->select('event_config', 'ec')
      ->fields('ec')
      ->condition('reg_start', $current_time, '<=')
      ->condition('reg_end', $current_time, '>=')
      ->orderBy('event_date', 'ASC')
      ->execute()
      ->fetchAll();
  }

  /**
   * Get active categories.
   */
  public function getActiveCategories() {
    $current_time = time();

    $results = $this->database->select('event_config', 'ec')
      ->fields('ec', ['category'])
      ->condition('reg_start', $current_time, '<=')
      ->condition('reg_end', $current_time, '>=')
      ->distinct()
      ->execute()
      ->fetchCol();

    $categories = [
      'online_workshop' => 'Online Workshop',
      'hackathon' => 'Hackathon',
      'conference' => 'Conference',
      'one_day_workshop' => 'One-day Workshop',
    ];

    $active_categories = [];
    foreach ($results as $category) {
      if (isset($categories[$category])) {
        $active_categories[$category] = $categories[$category];
      }
    }

    return $active_categories;
  }

  /**
   * Get event dates by category.
   */
  public function getEventDatesByCategory($category) {
    $current_time = time();

    $results = $this->database->select('event_config', 'ec')
      ->fields('ec', ['event_date'])
      ->condition('category', $category)
      ->condition('reg_start', $current_time, '<=')
      ->condition('reg_end', $current_time, '>=')
      ->distinct()
      ->orderBy('event_date', 'ASC')
      ->execute()
      ->fetchCol();

    $options = [];
    foreach ($results as $timestamp) {
      $options[$timestamp] = date('Y-m-d', $timestamp);
    }

    return $options;
  }

  /**
   * Get event names by date and category.
   */
  public function getEventNamesByDateAndCategory($event_date, $category) {
    $current_time = time();

    $results = $this->database->select('event_config', 'ec')
      ->fields('ec', ['id', 'event_name'])
      ->condition('event_date', $event_date)
      ->condition('category', $category)
      ->condition('reg_start', $current_time, '<=')
      ->condition('reg_end', $current_time, '>=')
      ->orderBy('event_name', 'ASC')
      ->execute()
      ->fetchAll();

    $options = [];
    foreach ($results as $event) {
      $options[$event->id] = $event->event_name;
    }

    return $options;
  }

  /**
   * Get event by ID.
   */
  public function getEventById($event_id) {
    return $this->database->select('event_config', 'ec')
      ->fields('ec')
      ->condition('id', $event_id)
      ->execute()
      ->fetchAssoc();
  }

  /**
   * Format timestamp to readable date string.
   *
   * @param int $timestamp
   *   Unix timestamp.
   * @param string $format
   *   PHP date format (default: Y-m-d H:i).
   *
   * @return string
   *   Formatted date string.
   */
  public function formatTimestamp($timestamp, $format = 'Y-m-d H:i') {
    return date($format, (int) $timestamp);
  }

}
