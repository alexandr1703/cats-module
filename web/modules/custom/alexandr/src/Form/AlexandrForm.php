<?php

namespace Drupal\alexandr\Form;

use Drupal\Core\Ajax\InsertCommand;
use Drupal\Core\Ajax\RedirectCommand;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\AlertCommand;
use Drupal\Core\Ajax\HtmlCommand;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\file\Entity\File;
use Drupal\Core\Url;




class AlexandrForm extends FormBase {
  /**
   * The current time.
   *
   */
  protected $currentTime;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->currentTime = $container->get('datetime.time');

    return $instance;
  }


  public function getFormId() {
    return 'alexandr_form_cats';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['system_messages'] = [
      '#markup' => '<div id="form-system-messages"></div>',
      '#weight' => -100,
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
    ];

    $form['cat'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Your cat’s name:'),
      '#required' => TRUE,
      '#maxlength' => 32,
      '#minlength' => 2,
      '#description' => $this->t('Please fill in the field from 2 to 32 characters'),
    ];


    $form['email-messages'] = [
      '#markup' => '<div id="email-messages"></div>',
      '#weight' => -100,
    ];

    $form['image'] = [
      '#type' => 'managed_file',
      '#title' => t('Image'),
      '#description' => t('Only png, jpg and jpeg.Max size 2Mb.'),
      '#upload_validators' => [
        'file_validate_extensions' => ['png jpg jpeg'],
        'file_validate_size' => [2097152],
      ],
      '#theme' => 'image_widget',
      '#preview_image_style' => 'medium',
      '#upload_location' => 'public://module_image',
      '#required' => TRUE
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
    $image = $form_state->getValue('image');
    $errorArray = [0, 0, 0];
    if (strlen($cat) == 1) {
      \Drupal::messenger()->addError('The name is too short. Please enter a longer name.');
    }
    elseif(strlen($cat) >1) {
      $errorArray[0]=1;
    }
    elseif (strlen($cat) == '') {
      \Drupal::messenger()->addError('Enter your cats name');
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || !preg_match('/^[A-Za-z-_]+[@]+[a-z]{2,12}+[.]+[a-z]{2,7}+$/', $email)) {
      \Drupal::messenger()->addError($this->t('Enter valid Email'));
      $errorArray[1]=0;
    }
    else{
      $errorArray[1]=1;
    }
    if ($image){
      $errorArray[2]=1;
    }
    else{
      \Drupal::messenger()->addError($this->t('Download image'));
    }
    if ($errorArray[0]==1 && $errorArray[1]==1 && $errorArray[2]==1){
      \Drupal::messenger()->addMessage($this->t('Your cat name: %cat . Form submited)))', ['%cat' => $cat] ));
      return TRUE;
    }
  }

  public function valideEmail(array &$form, FormStateInterface $form_state){
    $ajax_response = new AjaxResponse();
    $email = $form_state->getValue('email');
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || !preg_match('/^[A-Za-z-_]+[@]+[a-z]{2,12}+[.]+[a-z]{2,7}+$/', $email)) {
      $ajax_response->addCommand(new HtmlCommand('#form-system-messages', 'This email is invalid'));
    }
    else {
      $ajax_response->addCommand(new HtmlCommand('#form-system-messages', $email));
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
    if($this->validateForm($form, $form_state)==TRUE){
      $ajax_response->addCommand(new RedirectCommand('/alexandr/cats'));
    }
    return $ajax_response;
  }


  /**
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function submitForm(array &$form, FormStateInterface $form_state){
    $times = time() + 10800;
    if($this->validateForm($form, $form_state)==TRUE){
      $connection = \Drupal::service('database');
      $file = File::load($form_state->getValue('image')[0]);
      $file->setPermanent();
      $file->save();
      $connection->insert('alexandr')
        ->fields([
          'name' => $form_state->getValue('cat'),
          'email' => $form_state->getValue('email'),
          'uid' => $this->currentUser()->id(),
          'created' => date('d-M-Y  H:i:s', $times),
          'image' => $form_state->getValue('image')[0],
        ])
        ->execute();
    }


  }


}



