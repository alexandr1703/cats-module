<?php

namespace Drupal\alexandr\Form;

use Drupal\Core\Database\Database;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\file\Entity\File;

/**
 * Contains \Drupal\alexandr\Form\CatAdminForm.
 *
 * @file
 */

/**
 * Provides an Cat form.
 */
class AlexandrAdminForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'alexandr.admin_settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'alexandr_cat_admin_form';
  }

  /**
   * Get all cats for page.
   *
   * @return array
   *   A simple array.
   */
  public function load() {
    $query = Database::getConnection()->select('alexandr', 'a');
    $query->fields('a', ['name', 'email', 'image', 'created', 'id']);
    $result = $query->execute()->fetchAll();
    return $result;
  }

  /**
   * Build form.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $info = json_decode(json_encode($this->load()), TRUE);
    $info = array_reverse($info);
    $rows = [];
    $headers = [
      t('Cat name'),
      t('Email'),
      t('Photo'),
      t('Submitted'),
      t('Delete'),
      t('Edit'),
    ];
    foreach ($info as &$value) {
      $fid = $value['image'];
      $id = $value['id'];
      $name = $value['name'];
      $email = $value['email'];
      $created = $value['created'];
      array_splice($value, 0, 5);
      $renderer = \Drupal::service('renderer');
      $file = File::load($fid);
      $image = [
        '#type' => 'image',
        '#theme' => 'image_style',
        '#style_name' => 'thumbnail',
        '#uri' => $file->getFileUri(),
        '#attributes' => [
          'style' => 'width:100px',
        ],
      ];
      $value[0] = $name;
      $value[1] = $email;
      $value[2] = $renderer->render($image);
      $value[3] = $created;
      $delete = [
        '#type' => 'link',
        '#url' => Url::fromUserInput("/alexandr/admincatsdelete/$id"),
        '#title' => $this->t('Delete'),
        '#attributes' => [
          'data-dialog-type' => ['modal'],
          'class' => ['button', 'use-ajax'],
        ],
      ];
      $value[4] = $renderer->render($delete);
      $edit = [
        '#type' => 'link',
        '#url' => Url::fromUserInput("/alexandr/admincatsedit/$id"),
        '#title' => $this->t('Edit'),
        '#attributes' => [
          'data-dialog-type' => ['modal'],
          'class' => ['button', 'use-ajax'],
        ],
      ];
      $value[5] = $renderer->render($edit);
      $newId = [
        '#type' => 'hidden',
        '#value' => $id,
      ];
      $value[6] = $newId;
      array_push($rows, $value);
    }
    $form['table'] = [
      '#type' => 'tableselect',
      '#header' => $headers,
      '#options' => $rows,
      '#empty' => t('Not cats yet('),
    ];

    $form['delete'] = [
      '#type' => 'submit',
      '#value' => t('Delete'),
      '#attributes' => [
        'onclick' => 'if(!confirm("Really Delete?")){return false;}',
      ],
    ];

    return $form;
  }

  /**
   * Submit form.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $value = $form['table']['#value'];
    $connection = \Drupal::service('database');
    foreach ($value as $key => $val) {
      $result = $connection->delete('alexandr');
      $result->condition('id', $form['table']['#options'][$key][6]["#value"]);
      $result->execute();
    }
    \Drupal::messenger()->addMessage($this->t('Form Submitted Successfully'), 'status', TRUE);
  }

}
