<?php

namespace Drupal\site_api_key\Controller;

use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Class SiteApiController.
 */
class SiteApiController extends ControllerBase {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Configuration Factory.
   *
   * @var \Drupal\Core\Config\ConfigFactory
   */
  protected $configFactory;

  /**
   * The serializer.
   *
   * @var \Symfony\Component\Serializer\Serializer
   */
  protected $serializer;

  /**
   * Constructs a \Drupal\site_api_key\Controller.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Core\Config\ConfigFactoryInterface $configFactory
   *   The configFactory.
   * @param \Symfony\Component\Serializer\SerializerInterface $serializer
   *   The serializer service.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, ConfigFactoryInterface $configFactory, SerializerInterface $serializer) {

    $this->entityTypeManager = $entity_type_manager;
    $this->configFactory = $configFactory;
    $this->serializer = $serializer;
  }

  /**
   * Container.
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('config.factory'),
       $container->get('serializer')
     );
  }

  /**
   * Display json representation of the node.
   *
   * @return string
   *   Return node as json string.
   */
  public function index($api_key, $node_id) {
    $flag = FALSE;
    $node = [];

    // Parse parameter to integer.
    $node_id = (int) $node_id;

    // Read default Site API Key value.
    // \Drupal::config('system.site');.
    $config = $this->config('system.site');

    // If given Site API Key is valid and not default value.
    if (!empty($api_key) && $api_key != 'No API Key yet' && $api_key == $config->get('siteapikey')) {
      $flag = TRUE;
    }

    // Load node from given node id.
    if (!empty($node_id) && $flag) {
      // $serializer = \Drupal::service('serializer');
      $storage = $this->entityTypeManager->getStorage('node');

      $node = $storage->load($node_id);

      // Check if node type is page.
      if ($node != NULL && $node->bundle() == 'page') {
        $node = $this->serializer->serialize($node, 'json', ['plugin_id' => 'entity']);
      }
      else {
        throw new AccessDeniedHttpException();
      }
    }
    else {
      throw new AccessDeniedHttpException();
    }

    return new JsonResponse($node, 200, ['Content-Type' => 'application/json']);

  }

}
