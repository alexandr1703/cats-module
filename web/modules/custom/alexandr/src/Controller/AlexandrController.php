<?php

/**
 * @file
 * Contains \Drupal\alexandr\Controller\AlexandrController::printText
 */
namespace Drupal\alexandr\Controller;

use Drupal\Core\Controller\ControllerBase;

class AlexandrController extends  ControllerBase{
  public function content() {
    return[
      '#type' => 'markup',
      '#markup' => 'Hello! You can add here a photo of your cat.',
    ];
  }
}
