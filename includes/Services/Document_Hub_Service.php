<?php
/**
 * Document Hub Service
 *
 * Service layer for the Document Hub controller.
 * Handles data retrieval and business logic for the hub dashboard.
 *
 * @package    ShahiLegalFlowSuite
 * @subpackage Services
 * @since      3.0.1
 */

namespace ShahiLegalFlowSuite\Services;

use ShahiLegalFlowSuite\Database\Repositories\Legal_Doc_Repository;
use ShahiLegalFlowSuite\Database\Repositories\Company_Profile_Repository;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Document Hub Service Class
 *
 * @since 3.0.1
 */
class Document_Hub_Service
{

    /**
     * Document Repository
     *
     * @var Legal_Doc_Repository
     */
    private $doc_repository;

    /**
     * Profile Repository
     *
     * @var Company_Profile_Repository
     */
    private $profile_repository;

    /**
     * Constructor
     *
     * @since 3.0.1
     */
    public function __construct()
    {
        $this->doc_repository = new Legal_Doc_Repository();
        $this->profile_repository = Company_Profile_Repository::get_instance();
    }

    /**
     * Get document cards data
     *
     * Returns configuration and status for all available document types.
     *
     * @since 3.0.1
     * @return array Array of document card data
     */
    public function get_document_cards()
    {
        $types = $this->get_document_types();
        $cards = array();

        foreach ($types as $type_key => $config) {
            $existing = $this->doc_repository->find_by_type($type_key);

            $cards[] = array(
                'id' => $type_key,
                'title' => $config['title'],
                'description' => $config['description'],
                'icon' => $config['icon'],
                'status' => $existing ? $existing->status : 'not_generated',
                'updated_at' => $existing ? $existing->updated_at : null,
                'version' => $existing ? $existing->version : null,
                'doc_id' => $existing ? $existing->id : 0,
                'category' => $config['category'],
            );
        }

        return $cards;
    }

    /**
     * Get all available document types configuration
     *
     * @since 3.0.1
     * @return array Array of document type configurations
     */
    public function get_document_types()
    {
        return array(
            'privacy-policy' => array(
                'title' => __('Privacy Policy', 'shahi-legalflowsuite'),
                'description' => __('Mandatory for websites collecting user data. Covers GDPR, CCPA, and other privacy laws.', 'shahi-legalflowsuite'),
                'icon' => 'dashicons-shield',
                'category' => 'compliance',
            ),
            'terms-of-service' => array(
                'title' => __('Terms & Conditions', 'shahi-legalflowsuite'),
                'description' => __('Establishes the rules and regulations for using your website.', 'shahi-legalflowsuite'),
                'icon' => 'dashicons-book',
                'category' => 'legal',
            ),
            'cookie-policy' => array(
                'title' => __('Cookie Policy', 'shahi-legalflowsuite'),
                'description' => __('Explains how your website uses cookies and similar technologies.', 'shahi-legalflowsuite'),
                'icon' => 'dashicons-visibility',
                'category' => 'compliance',
            ),
            'disclaimer' => array(
                'title' => __('Disclaimer', 'shahi-legalflowsuite'),
                'description' => __('Limits your liability for the content published on your site.', 'shahi-legalflowsuite'),
                'icon' => 'dashicons-warning',
                'category' => 'legal',
            ),
            'refund-policy' => array(
                'title' => __('Refund & Return Policy', 'shahi-legalflowsuite'),
                'description' => __('Policy regarding refunds and returns for e-commerce stores.', 'shahi-legalflowsuite'),
                'icon' => 'dashicons-cart',
                'category' => 'ecommerce',
            ),
        );
    }

    /**
     * Get single document type configuration
     *
     * @since 3.0.1
     * @param string $type Document type key.
     * @return array|null Configuration array or null if not found
     */
    public function get_document_type($type)
    {
        $types = $this->get_document_types();
        return isset($types[$type]) ? $types[$type] : null;
    }

    /**
     * Get existing document by type
     *
     * @since 3.0.1
     * @param string $type Document type key.
     * @return object|null Document object
     */
    public function get_document_by_type($type)
    {
        return $this->doc_repository->find_by_type($type);
    }

    /**
     * Get categories for filter
     *
     * @since 3.0.1
     * @return array Array of categories
     */
    public function get_categories()
    {
        return array(
            'all' => __('All Documents', 'shahi-legalflowsuite'),
            'compliance' => __('Compliance', 'shahi-legalflowsuite'),
            'legal' => __('Legal Agreements', 'shahi-legalflowsuite'),
            'ecommerce' => __('E-commerce', 'shahi-legalflowsuite'),
        );
    }

    /**
     * Get profile summary for the banner
     *
     * @since 3.0.1
     * @return array Profile summary data
     */
    public function get_profile_summary()
    {
        $profile = $this->profile_repository->get_profile();

        if (!$profile || empty($profile['company']['legal_name'])) {
            return array(
                'exists' => false,
                'completeness' => 0,
                'name' => '',
                'updated_at' => '',
            );
        }

        // Use Profile_Validator for accurate completion calculation
        $validator = new Profile_Validator();
        $completeness = $validator->calculate_completion($profile);

        // Get updated timestamp from profile meta
        $meta = $this->profile_repository->get_profile_meta();

        return array(
            'exists' => true,
            'completeness' => $completeness,
            'name' => $profile['company']['legal_name'] ?? $profile['company']['trading_name'] ?? '',
            'updated_at' => $meta['updated_at'] ?? '',
        );
    }

    /**
     * Get hub statistics
     *
     * @since 3.0.1
     * @return array Statistics data
     */
    public function get_statistics()
    {
        global $wpdb;
        $docs_table = $wpdb->prefix . 'slos_documents';
        
        // Count total generated documents (excluding not_generated status)
        $total_generated = $wpdb->get_var(
            "SELECT COUNT(*) FROM {$docs_table} WHERE status != 'not_generated'"
        );
        
        // Count documents that need attention (outdated)
        $outdated_docs = $this->get_outdated_documents();
        $needs_attention = count($outdated_docs);
        
        // Count up-to-date published documents
        $up_to_date = $wpdb->get_var(
            "SELECT COUNT(*) FROM {$docs_table} WHERE status = 'published'"
        ) - $needs_attention;
        
        if ($up_to_date < 0) {
            $up_to_date = 0;
        }
        
        return array(
            'total_generated' => (int) $total_generated,
            'needs_attention' => (int) $needs_attention,
            'up_to_date' => (int) $up_to_date,
        );
    }

    /**
     * Get outdated documents
     *
     * @since 3.0.1
     * @return array Array of outdated documents
     */
    public function get_outdated_documents()
    {
        global $wpdb;
        $docs_table = $wpdb->prefix . 'slos_documents';
        
        // Get profile last updated timestamp
        $profile = $this->profile_repository->get_profile();
        if (!$profile || empty($profile['updated_at'])) {
            return array();
        }
        
        $profile_updated = $profile['updated_at'];
        
        // Find documents generated before profile was last updated
        $outdated = $wpdb->get_results($wpdb->prepare(
            "SELECT id, doc_type, title, updated_at 
             FROM {$docs_table} 
             WHERE status != 'not_generated' 
             AND (updated_at < %s OR updated_at IS NULL)
             ORDER BY updated_at ASC",
            $profile_updated
        ));
        
        return $outdated ? $outdated : array();
    }
}
