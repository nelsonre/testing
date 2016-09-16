<?php

namespace Drupal\downloads_widget\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Class DownloadsWidgetForm.
 *
 * @package Drupal\downloads_widget\Form
 */
class DownloadsWidgetForm extends FormBase {


  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'downloads_widget_form';
  }

  public function getDocuments() {
      $documents_query = \Drupal::database()->select('file_managed','f')
              ->condition('f.type','document')
              ->fields('f',array('filename','uri'))
              ->execute()
              ->fetchAll();
      $documents = array();
      foreach ($documents_query as $document) {
          $documents[$document->uri] = $document->filename;
      }
      return $documents;
  }
  
  
  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
      
      $options = $this->getDocuments();
      //$options = array('download1' => $this->t('download1'), 'download2' => $this->t('download2'));
      
    $form['filename'] = [
      '#type' => 'select',
      '#title' => $this->t('Filename'),
      '#description' => $this->t('Select the file that you want to download.'),
      '#options' => $options,
      '#size' => 1,
      '#required' => TRUE
    ];
    $form['pass_phrase'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Passphrase'),
      '#description' => $this->t('Type in passphrase.'),
      '#size' => 60,
      '#maxlength' => 128,
      '#required' => TRUE
    ];

    $form['submit'] = [
        '#type' => 'submit',
        '#value' => t('Download'),
    ];

    return $form;
  }

  
  
  
  /**
    * {@inheritdoc}
    */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
    
    $uri = $form_state->getValue('filename');
    
    // create the query of the taable that hold the files
    $query =\Drupal::entityQuery('file');
    $query->condition('status',1);
    $query->condition('uri',$uri);
    
    $group = $query->orConditionGroup()
            ->notExists('field_pass_phrase')
            ->condition('field_pass_phrase',$form_state->getValue('pass_phrase'));
    $query->condition($group);
    
    $fids = $query->execute();
    
    if (!count($fids)) {
        $form_state->setErrorByName('pass_phrase', t("Invalid passphrase!!"));
    }
    
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Display result.
    /*
    foreach ($form_state->getValues() as $key => $value) {
        drupal_set_message($key . ': ' . $value);
    }

     */

    #Create
    $uri = $form_state->getValue('filename');
    $response = new BinaryFileResponse($uri);
    
    #downloads the the file as an attachment as opposed to redirecting to the file URLs
    $response->setContentDisposition('attachment');
    //$form_state->clearErrors();
    #creates the response
    $form_state->setResponse($response);
    
  }

}
