<?php

namespace Drupal\alexandr\Form;

use Drupal\Core\Ajax\InsertCommand;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\AlertCommand;
use Drupal\Core\Ajax\HtmlCommand;



class AlexandrForm extends FormBase {
  public function getFormId() {
    return 'alexandr_form_cats';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['system_messages'] = [
      '#markup' => '<div id="form-system-messages"></div>',
      '#weight' => -100,
    ];


    $form['cat'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Your cat’s name:'),
      '#required' => TRUE,
      '#maxlength' => 32,
      '#minlength' => 2,
      '#description' => $this->t('Please fill in the field from 2 to 32 characters'),
    ];


    $form['email'] = [
      '#type' => 'email',
      '#title' => $this->t('Your email'),
      '#required' => TRUE,
      '#description' => $this->t('Only letters, "_" and "-"'),
      '#ajax' => [
        'callback' => '::valideEmail',
        'event' => 'keyup',
        'progress' => array(
          'type' => 'throbber',
          'message' => t('Verifying email..'),
        ),
      ],
      '#prefix' => '<div id = "cats-email">',
    ];

    $form['email-messages'] = [
      '#markup' => '<div id="email-messages"></div>',
      '#weight' => -100,
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Add cat'),
      '#ajax' => [
        'callback' => '::ajaxSubmitCallback',
        'event' => 'click',
        'wrapper' => 'user-cats-name',
        'progress' => [
          'type' => 'throbber',
          'message' => t('Verifying...'),
        ],
      ],
    ];
    return $form;
  }

  public function validateForm(array &$form, FormStateInterface $form_state) {
    $cat = $form_state->getValue('cat');
    $email = $form_state->getValue('email');
    $errorArray = [0, 0];
    if (strlen($cat) == 1) {
      \Drupal::messenger()->addError('The name is too short. Please enter a longer name.');
    }
    if(strlen($cat) >1) {
//      \Drupal::messenger()->addMessage($this->t('Your cat name: %cat', ['%cat' => $cat]));
      $errorArray[0]=1;
    }
    if (strlen($cat) == '') {
      \Drupal::messenger()->addError('This field is required!!!');
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || !preg_match('/^[A-Za-z-_]+[@]+[a-z]+[.]+[a-z]+$/', $email)) {
      \Drupal::messenger()->addError($this->t('Your email is invalid'));
    }
    else{
//      \Drupal::messenger()->addMessage($this->t('Your email is valid'));
      $errorArray[1]=1;
    }
    if ($errorArray[0]==1 && $errorArray[1]==1){
      \Drupal::messenger()->addMessage($this->t('Your cat name: %cat', ['%cat' => $cat]));
    }
  }

  public function valideEmail(array &$form, FormStateInterface $form_state){
    $ajax_response = new AjaxResponse();
    $email = $form_state->getValue('email');
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || !preg_match('/^[A-Za-z-_]+[@]+[a-z]+[.]+[a-z]+$/', $email)) {
      $ajax_response->addCommand(new HtmlCommand('#email-messages', 'This email is invalid'));
    }
    else {
      $ajax_response->addCommand(new HtmlCommand('#email-messages', $email));
    }
    return $ajax_response;
  }

  public function ajaxSubmitCallback(array &$form, FormStateInterface $form_state) {
    $ajax_response = new AjaxResponse();
    $message = [
      '#theme' => 'status_messages',
      '#message_list' => $this->messenger()->all(),
      '#status_headings' => [
        'status' => t('Status message'),
        'error' => t('Error message'),
        'warning' => t('Warning message'),
      ],
    ];
    $messages = \Drupal::service('renderer')->render($message);
    $ajax_response->addCommand(new HtmlCommand('#form-system-messages', $messages));
    $this->messenger()->deleteAll();
    return $ajax_response;

  }



  public function submitForm(array &$form, FormStateInterface $form_state){
  }


}



