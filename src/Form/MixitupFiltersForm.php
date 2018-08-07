<?php

namespace Drupal\mixitup_views\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\taxonomy\Entity\Vocabulary;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\mixitup_views\MixitupFunc;

/**
 * Class MixitupFiltersForm.
 *
 * @package Drupal\mixitup_views\Form
 */
class MixitupFiltersForm extends FormBase {

  /**
   * Provides MixItUpFunc Object.
   *
   * @var \Drupal\mixitup_views\MixitupFunc
   */
  protected $mixitupFuncService;

  /**
   * MixitupFiltersForm constructor.
   *
   * @param \Drupal\mixitup_views\MixitupFunc $mixitupFuncService
   *   The MixItUp Func object.
   */
  public function __construct(MixitupFunc $mixitupFuncService) {
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
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'mixitup_views_filters_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $options = []) {
    $filters = $this->mixitupFuncService->getPopulatedFilters();

    if ($filters !== NULL) {
      foreach ($filters as $vid => $terms) {
        // Show only selected vocabularies.
        if ($options['restrict_vocab'] === 1 && (!isset($options['restrict_vocab_ids'][$vid]))) {
          unset($filters[$vid]);
          continue;
        }
        // If all nodes have just one term tagged, it doesn't make sense
        // to show a term and clear filters link.
        if (\count($terms) < 2) {
          unset($filters[$vid]);
          continue;
        }
        $vocab = Vocabulary::load($vid);
        if ($vocab !== NULL) {
          $name = $vocab->get('name');
          $form['filter_' . $vid] = [
            '#type' => 'checkboxes',
            '#title' => $name,
            '#options' => $terms,
            '#attributes' => ['class' => ['mixitup_views_filter'], 'vid' => $vid],
            '#multiple' => TRUE,
          ];
        }
      }
      if ($filters) {
        $form['reset'] = [
          '#markup' => '<a href="#reset" id="reset">' . $this->t('Reset filters') . '</a>',
        ];
      }
    }

    if (isset($options['use_sort']) && $options['use_sort'] === 1 && isset($options['sorts'])) {
      $form['sort'] = [
        '#theme' => 'mixitup_views_sorting',
        '#sorts' => $options['sorts'],
      ];
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $form_state->setCompleteForm($form, $form_state);
  }

}