<?php

namespace Drupal\alexandr\Form;

use Drupal\Core\Ajax\RedirectCommand;
use Drupal\Core\Database\Database;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Url;
use Drupal\Core\Ajax\CloseModalDialogCommand;

/**
 * Delete form class.
 */
class AdminDeleteForm extends FormBase {
  /**
   * Contain slug id to delete cat entry.
   *
   * @var ctid
   */
  protected $ctid = 0;

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'admin_delete_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $cid = NULL) {
    $form['title'] = [
      '#type' => 'html_tag',
      '#tag' => 'h4',
      '#value' => t('Really delete???'),
      '#attributes' => [
        'class' => ['delete-title'],
      ],
    ];
    $form['delete'] = [
      '#type' => 'submit',
      '#value' => t('Yes'),
      '#ajax' => [
        'callback' => '::ajaxFormSubmit',
        'event' => 'click',
        'progress' => [
          'type' => 'throbber',
        ],
      ],
    ];
    $form['cancel'] = [
      '#type' => 'button',
      '#value' => t('No'),
      '#ajax' => [
        'callback' => '::ajaxFormCancel',
        'event' => 'click',
        'progress' => [
          'type' => 'throbber',
        ],
      ],
      '#attributes' => [
        'class' => ['btn-no'],
        'onclick' => 'closeModal',
      ],
    ];
    $this->ctid = $cid;
    return $form;
  }

  /**
   * Function Submit.
   */
  public function ajaxFormSubmit(array &$form, FormStateInterface $form_state) {
    $response = new AjaxResponse();
    $url = Url::fromRoute('alexandr.admin_settings_form', []);
    if ($url->isRouted()) {
      $out = $url->toString();
      $response->addCommand(new RedirectCommand($out));

    }
    return $response;
  }

  /**
   * Function Cancel.
   */
  public function ajaxFormCancel(array &$form, FormStateInterface $form_state) {
    $command = new CloseModalDialogCommand();
    $response = new AjaxResponse();
    $response->addCommand($command);
    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $connection = \Drupal::service('database');
    $result = $connection->delete('alexandr');
    $result->condition('id', $this->ctid);

    $result->execute();
    \Drupal::messenger()->addMessage($this->t('Entry deleted successfully'), 'status', TRUE);
  }

}
