<?php

namespace Drupal\alexandr\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Database;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\file\Entity\File;
use Drupal\file\FileInterface;
use Drupal\Core\Url;
use Drupal\Core\Form\FormBase;

/**
 * Class AlexandrController.
 */
class AlexandrController extends ControllerBase {
  /**
   * Form build interface.
   *
   * @var Drupal\Core\Form\FormBase
   */
  protected $formBuilder;

  /**
   * Return instance.
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->formBuilder = $container->get('form_builder');
    return $instance;
  }

  /**
   * Return title and form.
   */
  public function content() {
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

  /**
   * Return delete form.
   *
   * @return array
   *   Array form
   */
  public function delete() {
    $deleteform = \Drupal::formBuilder()->getForm('Drupal\alexandr\Form\DeleteForm');
    return $deleteform;
  }

  /**
   * Return delete form.
   *
   * @return array
   *   Array form
   */
  public function edit() {
    $editform = \Drupal::formBuilder()->getForm('Drupal\alexandr\Form\EditForm');
    return $editform;
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
   * Render table of cats.
   */
  public function report() {
    $url = Url::fromRoute('alexandr.form', []);
    if ($url->isRouted()) {
      $out = $url->toString();
    }
    $form = $this->content();
    $delete = $this->delete();
    $edit = $this->edit();
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
          '#attributes' => [
            'class' => ['cats-image-overlay'],
          ],
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
      '#theme' => 'cats_list',
      '#form' => $form,
      '#items' => $rows,
      '#delete' => $delete,
      '#edit' => $edit,
    ];
  }

}
