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
//      '#required' => TRUE,
      '#maxlength' => 32,
      '#minlength' => 2,
      '#description' => $this->t('Please fill in the field from 2 to 32 characters'),
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
    if (strlen($cat) == 1) {
      \Drupal::messenger()->addError('The name is too short. Please enter a longer name.');
    }
    if(strlen($cat) >1) {
      \Drupal::messenger()->addMessage($this->t('Your cat name: %cat', ['%cat' => $cat]));
    }
    if (strlen($cat) == '') {
      \Drupal::messenger()->addError('This field is required!!!');
    }
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



