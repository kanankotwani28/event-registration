<?php

namespace Drupal\event_registration\Mail;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Mail\MailManagerInterface;

/**
 * Mail Handler for sending emails.
 */
class MailHandler {

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The mail manager.
   *
   * @var \Drupal\Core\Mail\MailManagerInterface
   */
  protected $mailManager;

  /**
   * Constructor.
   */
  public function __construct(ConfigFactoryInterface $config_factory, MailManagerInterface $mail_manager) {
    $this->configFactory = $config_factory;
    $this->mailManager = $mail_manager;
  }

  /**
   * Send confirmation email to user.
   */
  public function sendUserConfirmation($email, $name, $event_name, $event_date, $category) {
    $config = $this->configFactory->get('event_registration.settings');
    $site_name = $this->configFactory->get('system.site')->get('name');

    $params = [
      'email' => $email,
      'name' => $name,
      'event_name' => $event_name,
      'event_date' => $event_date,
      'category' => $category,
      'site_name' => $site_name,
    ];

    return $this->mailManager->mail(
      'event_registration',
      'registration_confirmation',
      $email,
      'en',
      $params
    );
  }

  /**
   * Send notification email to admin.
   */
  public function sendAdminNotification($name, $email, $event_name, $event_date, $category) {
    $config = $this->configFactory->get('event_registration.settings');
    $admin_email = $config->get('admin_email');

    if (!$admin_email || !$config->get('enable_admin_notifications')) {
      return FALSE;
    }

    $site_name = $this->configFactory->get('system.site')->get('name');
    $subject = $config->get('notification_email_subject') ?? 'New Event Registration';

    $params = [
      'user_name' => $name,
      'user_email' => $email,
      'event_name' => $event_name,
      'event_date' => $event_date,
      'category' => $category,
      'site_name' => $site_name,
      'subject' => $subject,
    ];

    return $this->mailManager->mail(
      'event_registration',
      'admin_notification',
      $admin_email,
      'en',
      $params
    );
  }

}
