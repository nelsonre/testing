<?php

namespace Drupal\hello_world\Controller;

use Drupal\Core\Controller\ControllerBase;
use \Drupal\node\Entity\Node;
use \Drupal\Core\Url;

/**
 * Class HelloController.
 *
 * @package Drupal\hello_world\Controller
 */
class HelloController extends ControllerBase {

    /**
     * Hello.
     *
     * @return string
     *   Return Hello string.
     */
    public function hello($nid) {

        $node = Node::load($nid);

        if ($node) {
            $node_type = $node->getType();
            $node_title = $node->getTitle();
            $created_date = $node->getCreatedTime();
            $author = $node->getOwner();
            $username = $author->getUsername();
            $published_date = format_date($created_date, 'long');
            $node_url = Url::fromRoute('entity.node.canonical', ['node' => $nid]);
            $internal_link = \Drupal::l($node_title,$node_url);
        }

        return [
            '#type' => 'markup',
            '#markup' => $this->t('Hello @title',['@title' => $node_title]). ' '.$internal_link,
        ];
    }

}
