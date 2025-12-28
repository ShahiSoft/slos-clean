<?php
/**
 * Legal Documents Module
 *
 * Handles the registration and initialization of the Legal Documents
 * generation and management features.
 *
 * @package    ShahiLegalopsSuite
 * @subpackage Modules\LegalDocs
 * @since      3.0.1
 */

namespace ShahiLegalopsSuite\Modules\LegalDocs;

use ShahiLegalopsSuite\Modules\Module;
use ShahiLegalopsSuite\Admin\Document_Hub_Controller;
use ShahiLegalopsSuite\Admin\Profile_Wizard;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * LegalDocs Module Class
 *
 * @since 3.0.1
 */
class LegalDocuments extends Module
{

    /**
     * Document Hub Controller instance
     *
     * @var Document_Hub_Controller
     */
    private $controller;

    /**
     * Profile Wizard instance
     *
     * @var Profile_Wizard
     */
    private $profile_wizard;

    /**
     * Get module unique key
     *
     * @since 3.0.1
     * @return string Module key
     */
    public function get_key()
    {
        return 'legal-docs';
    }

    /**
     * Get module name
     *
     * @since 3.0.1
     * @return string Module name
     */
    public function get_name()
    {
        return __('Legal Documents', 'shahi-legalops-suite');
    }

    /**
     * Get module description
     *
     * @since 3.0.1
     * @return string Module description
     */
    public function get_description()
    {
        return __('Generate and manage legal documents for your website.', 'shahi-legalops-suite');
    }

    /**
     * Get module icon
     *
     * @since 3.0.1
     * @return string Icon class
     */
    public function get_icon()
    {
        return 'dashicons-media-document';
    }

    /**
     * Get module category
     *
     * @since 3.0.1
     * @return string Category
     */
    public function get_category()
    {
        return 'compliance';
    }

    /**
     * Initialize module
     *
     * @since 3.0.1
     * @return void
     */
    public function init()
    {
        // Initialize the controller
        $this->controller = new Document_Hub_Controller();
        $this->controller->init();

        // Initialize Profile Wizard (Company Profile Setup)
        if ( slos_is_feature_enabled( 'company_wizard' ) ) {
            $this->profile_wizard = new Profile_Wizard();
            $this->profile_wizard->init();
        }

        // Register admin menu
        add_action('admin_menu', array($this, 'register_admin_menu'), 20);

        // Enqueue assets
        add_action('admin_enqueue_scripts', array($this, 'enqueue_assets'));
    }

    /**
     * Register admin menu
     *
     * @since 3.0.1
     * @return void
     */
    public function register_admin_menu()
    {
        add_submenu_page(
            'shahi-legalops-suite',
            __('Legal Documents', 'shahi-legalops-suite'),
            '📄 ' . __('Documents', 'shahi-legalops-suite'),
            'manage_shahi_modules',
            'slos-documents',
            array($this->controller, 'render')
        );

        // Register hidden editor page
        add_submenu_page(
            null, // Hidden from menu
            __('Edit Document', 'shahi-legalops-suite'),
            __('Edit Document', 'shahi-legalops-suite'),
            'manage_shahi_modules',
            'slos-edit-document',
            array(new \ShahiLegalopsSuite\Admin\Document_Editor_Controller(), 'render')
        );
    }

    /**
     * Enqueue assets
     *
     * @since 3.0.1
     * @param string $hook Current admin page hook.
     * @return void
     */
    public function enqueue_assets($hook)
    {
        // Only load on our page
        if (false === strpos($hook, 'slos-documents')) {
            return;
        }

        $this->controller->enqueue_assets();
    }

    /**
     * Get module settings URL
     *
     * Returns the admin URL for Document Hub main page.
     *
     * @since 3.1.1
     * @return string Settings URL
     */
    public function get_settings_url()
    {
        return admin_url('admin.php?page=slos-documents');
    }
}
