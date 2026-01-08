<?php
/**
 * Pages Requiring Attention Component
 * 
 * Displays list of pages with accessibility issues requiring attention.
 * This component is integrated into the Tools & Scanner tab.
 *
 * @package ShahiLegalFlowSuite
 * @subpackage Modules\AccessibilityScanner\Admin
 * @since 3.1.2
 * @version 1.0.0
 * 
 * STATUS: IMPLEMENTED - Phase 1.1 Complete
 * Created: January 6, 2026
 * Updated: January 6, 2026
 * Phase: Phase 1 - Critical Path
 * Priority: P0 - Blocking
 */

namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\Admin;

/**
 * Class PagesRequiringAttention
 * 
 * Renders the "Pages Requiring Attention" section in the Tools tab.
 * Displays pages with accessibility issues, priority indicators, and action buttons.
 */
class PagesRequiringAttention {
    
    /**
     * Render the Pages Requiring Attention section
     * 
     * Main entry point for displaying the component.
     * Fetches pages with issues and includes the template.
     * 
     * @since 3.1.2
     * @return void
     */
    public function render() {
        // Get scan results from WordPress options
        $scan_results = get_option( 'slos_last_scan_results', [] );
        
        // Filter pages that have accessibility issues
        $pages_with_issues = $this->get_pages_with_issues( $scan_results );
        
        // Sort by priority (critical issues first)
        usort( $pages_with_issues, function( $a, $b ) {
            // Primary sort: critical count (descending)
            $critical_diff = ( $b['critical_count'] ?? 0 ) - ( $a['critical_count'] ?? 0 );
            if ( $critical_diff !== 0 ) {
                return $critical_diff;
            }
            
            // Secondary sort: total issues (descending)
            return ( $b['issues_count'] ?? 0 ) - ( $a['issues_count'] ?? 0 );
        });
        
        // Include the template file
        include SHAHI_LEGALFLOWSUITE_PATH . 'templates/admin/accessibility-pages-attention.php';
    }
    
    /**
     * Get pages with accessibility issues
     * 
     * Retrieves and filters scan results to find pages needing attention.
     * 
     * @since 3.1.2
     * @param array $scan_results Raw scan results from database
     * @return array Array of pages with issues
     */
    private function get_pages_with_issues( $scan_results ) {
        if ( empty( $scan_results ) || ! is_array( $scan_results ) ) {
            return [];
        }
        
        $pages_with_issues = [];
        
        foreach ( $scan_results as $result ) {
            // Skip if not an array or doesn't have required data
            if ( ! is_array( $result ) ) {
                continue;
            }
            
            $issues_count = isset( $result['issues_count'] ) ? intval( $result['issues_count'] ) : 0;
            
            // Only include pages that have issues
            if ( $issues_count > 0 ) {
                // Ensure post_id exists
                if ( ! isset( $result['post_id'] ) && isset( $result['page_id'] ) ) {
                    $result['post_id'] = $result['page_id'];
                }
                
                // Set defaults for missing fields
                $result['title'] = isset( $result['title'] ) ? $result['title'] : 
                                 ( isset( $result['page'] ) ? $result['page'] : 'Untitled' );
                $result['post_type'] = isset( $result['post_type'] ) ? $result['post_type'] : 'post';
                $result['critical_count'] = isset( $result['critical_count'] ) ? intval( $result['critical_count'] ) : 0;
                $result['score'] = isset( $result['score'] ) ? intval( $result['score'] ) : 100;
                
                // Calculate priority
                $result['priority'] = $this->calculate_priority( $result );
                
                $pages_with_issues[] = $result;
            }
        }
        
        return $pages_with_issues;
    }
    
    /**
     * Calculate priority level for a page
     * 
     * Determines priority (high/medium/low) based on issue counts.
     * 
     * @since 3.1.2
     * @param array $page Page data with issue counts
     * @return string Priority level: 'high' | 'medium' | 'low'
     */
    private function calculate_priority( $page ) {
        $critical_count = isset( $page['critical_count'] ) ? intval( $page['critical_count'] ) : 0;
        $issues_count = isset( $page['issues_count'] ) ? intval( $page['issues_count'] ) : 0;
        
        // Priority rules based on strategic plan:
        // - High: critical_count > 5
        // - Medium: issues_count > 10
        // - Low: all others
        if ( $critical_count > 5 ) {
            return 'high';
        } elseif ( $issues_count > 10 ) {
            return 'medium';
        } else {
            return 'low';
        }
    }
}
