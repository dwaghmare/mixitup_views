<?php
/**
 * @file
 * Contains Drupal\mixitup_views\MixitupFunc.
 */

namespace Drupal\mixitup_views;

use Drupal\taxonomy\Entity\Term;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Performs assistance functionality.
 *
 * @package Drupal\mixitup_views
 */
class MixitupFunc {
  /**
   * Static array for store active nodes terms.
   */
  protected static $populatedFilters = array();
  /**
   * Static array for store information about which nodes have a specific tid.
   */
  protected static $nodeFilters = array();
  /**
   * Default options service.
   */
  protected $defaultOptionsService;

  /**
   * Constructor.
   */
  public function __construct(MixitupViewsDefaultOptionsService $defaultOptionsService) {
    $this->defaultOptionsService = $defaultOptionsService;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('mixitup_views.default_options_service')
    );
  }

  /**
   * Get classes string for node.
   *
   * @param int $nid
   *   Node id.
   *
   * @return string
   *   Classes string.
   */
  public function getRowClasses($nid) {
    $tids = $this->getNodeTids($nid);
    $classes = array();
    if (!empty($tids)) {
      foreach ($tids as $tid) {
        $classes[] = 'tid_' . $tid;
        $this->populateFilters($tid, $nid);
      }
    }
    $classes = implode(' ', $classes);

    return $classes;
  }

  /**
   * Get all node's taxonomy ids.
   *
   * @param int $nid
   *   Node id.
   *
   * @return array
   *   Array of tids.
   */
  public function getNodeTids($nid) {
    $tids = db_select('taxonomy_index', 'ti')
      ->fields('ti', array('tid', 'nid'))
      ->condition('ti.nid', $nid)
      ->execute()->fetchAllKeyed();

    return array_keys($tids);
  }

  /**
   * Populates structured array of used taxonomy terms.
   *
   * @param int $tid
   *   Taxonomy id.
   * @param int $nid
   *   Node id.
   */
  public function populateFilters($tid, $nid) {
    $term = Term::load($tid);
    $vid = $term->getVocabularyId();
    self::$populatedFilters[$vid]['.tid_' . $tid] = $term->getName();
    $this->populateNodeFilters($nid, $tid);
  }

  /**
   * Collects information regarding wich nodes have a specific tid.
   *
   * @param int $nid
   *   Node id.
   * @param int $tid
   *   Taxonomy id.
   */
  public function populateNodeFilters($nid, $tid) {
    self::$nodeFilters[$tid][] = $nid;
  }

  /**
   * Gets populated filters.
   *
   * @return array $populatedFilters
   *   Array with structure item[vid]['tid_{tid}'] = term_name.
   */
  public function getPopulatedFilters() {
    return self::$populatedFilters;
  }

  /**
   * Gets populated node filters.
   *
   * @return array $nodeFilters
   *   Array with structure item[tid] => array(nids).
   */
  public function getPopulatedNodeFilters() {
    return self::$nodeFilters;
  }

  /**
   * Get default mixitup options.
   *
   * @return mixed
   *   Array of default options.
   */
  public function getDefaultOptions($convert = FALSE) {
    return $this->defaultOptionsService->defaultOptions($convert);;
  }

  /**
   * Checks is mixitup js file exists.
   *
   * @return bool
   *   True or False.
   */
  public function isMixitupInstalled() {
    if (is_file(drupal_get_path('module', 'mixitup_views') . '/js/jquery.mixitup.min.js')) {
      return TRUE;
    }
    return FALSE;
  }

}
