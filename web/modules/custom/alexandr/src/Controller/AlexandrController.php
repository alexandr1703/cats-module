<?php

/**
 * @file
 * Contains \Drupal\alexandr\Controller\AlexandrController::content
 */

namespace Drupal\alexandr\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Database;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\file\Entity\File;
use Drupal\file\FileInterface;


class AlexandrController extends ControllerBase
{
  protected $formBuilder;

  public static function create(ContainerInterface $container)
  {
    $instance = parent::create($container);
    $instance->formBuilder = $container->get('form_builder');
    return $instance;
  }

  public function content()
  {
    $myform = \Drupal::formBuilder()->getForm('Drupal\alexandr\Form\AlexandrForm');
    return [
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

  public function load()
  {
    $query = Database::getConnection()->select('alexandr', 'a');
    $query->fields('a', ['name', 'email', 'image', 'created']);
    $result = $query->execute()->fetchAll();
    return $result;
  }


  public function report()
  {
    $content = [];
    $form = $this->content();
    $headers = [
      t('Cat name'),
      t('Email'),
      t('Photo'),
      t('Submitted'),

    ];
    $info = json_decode(json_encode($this->load()), TRUE);
    $info = array_reverse($info);
    $rows = [];
    foreach ($info as &$value) {
      $fid = $value['image'];
      $file = File::load($fid);
      if ($file instanceof FileInterface) {
        $value['image'] = [
          '#type' => 'image',
          '#theme' => 'image_style',
          '#style_name' => 'large',
          '#alt' => 'cat_image',
          '#uri' => $file->getFileUri(),
        ];
        $renderer = \Drupal::service('renderer');
        $value['image'] = $renderer->render($value['image']);
      }
      array_push($rows, $value);
    }
    $content['table'] = [
      '#type' => 'table',
      '#header' => $headers,
      '#rows' => $rows,
      '#empty' => t('No cats yet'),
    ];
    return [
      $form,
      $content,
    ];
  }
}


