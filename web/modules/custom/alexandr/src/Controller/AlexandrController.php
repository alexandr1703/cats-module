<?php

/**
 * @file
 * Contains \Drupal\alexandr\Controller\AlexandrController::content
 */
namespace Drupal\alexandr\Controller;

use Drupal\Core\Controller\ControllerBase;

class AlexandrController extends  ControllerBase{
  public function content() {

    $myform = \Drupal::formBuilder()->getForm('Drupal\alexandr\Form\AlexandrForm');

    return[
      [
        '#type' => 'html_tag',
        '#tag' => 'h2',
        '#value' => $this->t('Hello! You can add here a photo of your cat.'),
        '#attributes' => [
          'class' => ['cats-title'],
        ],
      ],
      $myform,
    ];
  }
}
