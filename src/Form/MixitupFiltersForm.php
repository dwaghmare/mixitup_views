<?php
/**
 * @file
 * Contains \Drupal\mixitup_views\Form\MixitupFiltersForm.
 */

namespace Drupal\mixitup_views\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\taxonomy\Entity\Vocabulary;
use Symfony\Component\DependencyInjection\ContainerInterface;

class MixitupFiltersForm extends FormBase {

  /**
   * @var \Drupal\mixitup_views\MixitupFunc.
   */
  protected $mixitupFuncService;

  public function __construct($mixitupFuncService) {
    $this->mixitupFuncService = $mixitupFuncService;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('mixitup_views.func_service')
    );
  }

  /**
   * {@inheritdoc}.
   */
  public function getFormId() {
    return 'mixitup_views_filters_form';
  }

  /**
   * {@inheritdoc}.
   */
  public function buildForm(array $form, FormStateInterface $form_state, $options = array()) {
    $filters = $this->mixitupFuncService->getPopulatedFilters();
    $form = array();

    if (isset($filters)) {
      foreach ($filters as $vid => $terms) {
        // Show only selected vocabularies.
        if ($options['restrict_vocab'] == 1 && (!isset($options['restrict_vocab_ids'][$vid]))) {
          unset($filters[$vid]);
          continue;
        }
        // If all nodes have just one term tagged, it doesn't make sense
        // to show a term and clear filters link.
        if (count($terms) < 2) {
          unset($filters[$vid]);
          continue;
        }
        $vocab = Vocabulary::load($vid);
        $name = $vocab->get('name');
        $form['filter_' . $vid] = array(
          '#type' => 'checkboxes',
          '#title' => $name,
          '#options' => $terms,
          '#attributes' => array('class' => array('mixitup_views_filter'), 'vid' => $vid),
          '#multiple' => TRUE,
        );
      }
      if ($filters) {
        $form['reset'] = array(
          '#markup' => '<a href="#reset" id="reset">' . $this->t('Reset filters') . '</a>',
        );
      }
    }

    if (isset($options['use_sort']) && $options['use_sort'] == 1 && isset($options['sorts'])) {
      $form['sort'] = array(
        '#theme' => 'mixitup_views_sorting',
        '#sorts' => $options['sorts'],
      );
    }

    return $form;
  }

  /**
   * {@inheritdoc}.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);
  }
}
