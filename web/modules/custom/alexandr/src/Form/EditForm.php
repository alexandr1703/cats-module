<?php

namespace Drupal\alexandr\Form;

use Drupal\Core\Ajax\RedirectCommand;
use Drupal\Core\Database\Database;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Url;
use Drupal\file\Entity\File;
use Drupal\file\FileInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Main class.
 */
class EditForm extends FormBase {
  /**
   * Id cat.
   *
   * @var ctid
   * */
  protected $ctid = 0;

  /**
   * Return form.
   */
  public function getFormId() {
    return 'edit_form';
  }

  /**
   * Return current email.
   */
  public function getEmail($cid) {
    $query = Database::getConnection()->select('alexandr', 'a');
    $query->fields('a', ['name', 'email', 'image', 'created', 'id']);
    $result = $query->execute()->fetchAll();
    $info = json_decode(json_encode($result), TRUE);
    $info = array_reverse($info);
    $email = "";
    foreach ($info as &$value) {
      $id = $value['id'];
      for ($i = $id; $i <= $cid;) {
        if ($id == $cid) {
          $email = $value['email'];
          break;
        }
        else {
          $i++;
        }
      }
    }
    return $email;
  }

  /**
   * Return current cat's name.
   */
  public function getName($cid) {
    $query = Database::getConnection()->select('alexandr', 'a');
    $query->fields('a', ['name', 'email', 'image', 'created', 'id']);
    $result = $query->execute()->fetchAll();
    $info = json_decode(json_encode($result), TRUE);
    $info = array_reverse($info);
    $name = "";
    foreach ($info as &$value) {
      $id = $value['id'];
      for ($i = $id; $i <= $cid;) {
        if ($id == $cid) {
          $name = $value['name'];
          break;
        }
        else {
          $i++;
        }
      }
    }
    return $name;
  }

  /**
   * Return current cat's image.
   */
  public function getImage($cid) {
    $query = Database::getConnection()->select('alexandr', 'a');
    $query->fields('a', ['name', 'email', 'image', 'created', 'id']);
    $result = $query->execute()->fetchAll();
    $info = json_decode(json_encode($result), TRUE);
    $info = array_reverse($info);
    $image = "";
    foreach ($info as &$value) {
      $id = $value['id'];
      for ($i = $id; $i <= $cid;) {
        if ($id == $cid) {
          $image = $value['image'];
          break;
        }
        else {
          $i++;
        }
      }
    }
    return $image;
  }

  /**
   * Build form.
   */
  public function buildForm(array $form, FormStateInterface $form_state, $cid = NULL) {
    $form['system_messages'] = [
      '#type' => 'html_tag',
      '#tag' => 'div',
      '#weight' => -100,
      '#attributes' => [
        'id' => ['form-system-messages'],
      ],
    ];
    $form['email'] = [
      '#type' => 'email',
      '#title' => $this->t('Your email'),
      '#required' => TRUE,
      '#default_value' => $this->getEmail($cid),
    ];
    $form['cat'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Your cat’s name:'),
      '#required' => TRUE,
      '#maxlength' => 32,
      '#minlength' => 2,
      '#default_value' => $this->getName($cid),
    ];
    $form['email-messages'] = [
      '#type' => 'html_tag',
      '#tag' => 'div',
      '#weight' => -100,
      '#attributes' => [
        'id' => ['email-messages'],
      ],
    ];

    $form['image'] = [
      '#type' => 'managed_file',
      '#title' => t('Image'),
      '#default_value' => array($this->getImage($cid)),
      '#upload_validators' => [
        'file_validate_extensions' => ['png jpg jpeg'],
        'file_validate_size' => [2097152],
      ],
      '#theme' => 'image_widget',
      '#preview_image_style' => 'medium',
      '#upload_location' => 'public://module_image',
      '#required' => TRUE,
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Change and Save'),
      '#ajax' => [
        'callback' => '::ajaxFormEdit',
        'event' => 'click',
      ],
    ];
    $this->ctid = $cid;
    return $form;
  }

  /**
   * Ajax redirect.
   */
  public function ajaxFormEdit(array &$form, FormStateInterface $form_state) {
    $response = new AjaxResponse();
    $out = "";
    $url = Url::fromRoute('alexandr.form', []);
    if ($url->isRouted()) {
      $out = $url->toString();
    }
    $response->addCommand(new RedirectCommand($out));
    return $response;
  }

  /**
   * Validate form.
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $cat = $form_state->getValue('cat');
    $email = $form_state->getValue('email');
    $image = $form_state->getValue('image');
    $errorArray = [0, 0, 0];
    if (strlen($cat) == 1) {
      \Drupal::messenger()->addError('The name is too short. Please enter a longer name.');
    }
    elseif (strlen($cat) > 1) {
      $errorArray[0] = 1;
    }
    elseif (strlen($cat) == '') {
      \Drupal::messenger()->addError('Enter your cats name');
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || !preg_match('/^[A-Za-z-_]+[@]+[a-z]{2,12}+[.]+[a-z]{2,7}+$/', $email)) {
      \Drupal::messenger()->addError($this->t('Enter valid Email'));
      $errorArray[1] = 0;
    }
    else {
      $errorArray[1] = 1;
    }
    if ($image) {
      $errorArray[2] = 1;
    }
    else {
      \Drupal::messenger()->addError($this->t('Download image'));
    }
    if ($errorArray[0] == 1 && $errorArray[1] == 1 && $errorArray[2] == 1) {
      return TRUE;
    }
  }

  /**
   * Wrote fields into database.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    if ($this->validateForm($form, $form_state) == TRUE) {
      $connection = \Drupal::service('database');
      $file = File::load($form_state->getValue('image')[0]);
      $file->setPermanent();
      $file->save();
      $connection->update('alexandr')
        ->condition('id', $this->ctid)
        ->fields([
          'name' => $form_state->getValue('cat'),
          'email' => $form_state->getValue('email'),
          'image' => $form_state->getValue('image')[0],
        ])
        ->execute();
      \Drupal::messenger()->addMessage($this->t('Form Edit Successfully'), 'status', TRUE);
    }
  }

}

