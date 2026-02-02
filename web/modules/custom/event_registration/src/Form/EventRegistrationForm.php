<?php

namespace Drupal\event_registration\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

class EventRegistrationForm extends FormBase {

  public function getFormId() {
    return 'event_registration_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Full Name'),
      '#required' => TRUE,
    ];

    $form['email'] = [
      '#type' => 'email',
      '#title' => $this->t('Email'),
      '#required' => TRUE,
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Register'),
    ];

    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->messenger()->addMessage(
      $this->t('Registration successful for %name', [
        '%name' => $form_state->getValue('name')
      ])
    );
  }
}
