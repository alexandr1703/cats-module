<?php

namespace Drupal\alexandr\Form;


use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;


class AlexandrForm extends FormBase {
  public function getFormId() {
    return 'alexandr_form_cats';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['cat'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Your cat’s name:'),
      '#required' => TRUE,
      '#maxlength' => 32,
      '#minlength' => 2,
      '#description' => $this->t('Please fill in the field from 2 to 32 characters'),
    ];


    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Add cat'),
    ];
    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state){
  }


}



