<?php
/**
 * BackupService Unit Tests
 *
 * @package ShahiLegalFlowSuite
 * @subpackage Tests
 */

namespace ShahiLegalFlowSuite\Tests\Services;

use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Services\BackupService;
use WP_UnitTestCase;

/**
 * Test BackupService class
 */
class BackupServiceTest extends WP_UnitTestCase {
    
    /**
     * BackupService instance
     *
     * @var BackupService
     */
    private $backup_service;
    
    /**
     * Test post ID
     *
     * @var int
     */
    private $test_post_id;
    
    /**
     * Set up before each test
     */
    public function setUp(): void {
        parent::setUp();
        
        $this->backup_service = new BackupService();
        
        // Create test post
        $this->test_post_id = $this->factory->post->create(
            array(
                'post_title'   => 'Test Post',
                'post_content' => 'Original test content',
                'post_status'  => 'publish',
            )
        );
    }
    
    /**
     * Clean up after each test
     */
    public function tearDown(): void {
        // Delete test post
        if ( $this->test_post_id ) {
            wp_delete_post( $this->test_post_id, true );
        }
        
        // Clean up backups
        global $wpdb;
        $table = $wpdb->prefix . 'slos_accessibility_fix_history';
        $wpdb->query( "TRUNCATE TABLE {$table}" );
        
        parent::tearDown();
    }
    
    /**
     * Test save_backup creates backup
     */
    public function test_save_backup_creates_backup() {
        $content = 'Test content to backup';
        $metadata = array( 'test_key' => 'test_value' );
        
        $backup_id = $this->backup_service->save_backup( $this->test_post_id, $content, $metadata );
        
        $this->assertIsInt( $backup_id );
        $this->assertGreaterThan( 0, $backup_id );
    }
    
    /**
     * Test save_backup validates inputs
     */
    public function test_save_backup_validates_inputs() {
        // Invalid post ID
        $result = $this->backup_service->save_backup( null, 'content' );
        $this->assertFalse( $result );
        
        $result = $this->backup_service->save_backup( 'invalid', 'content' );
        $this->assertFalse( $result );
        
        // Invalid content
        $result = $this->backup_service->save_backup( $this->test_post_id, array() );
        $this->assertFalse( $result );
    }
    
    /**
     * Test get_backup retrieves saved backup
     */
    public function test_get_backup_retrieves_saved_backup() {
        $content = 'Test content';
        $backup_id = $this->backup_service->save_backup( $this->test_post_id, $content );
        
        $backup = $this->backup_service->get_backup( $backup_id );
        
        $this->assertIsArray( $backup );
        $this->assertEquals( $this->test_post_id, $backup['post_id'] );
        $this->assertEquals( $content, $backup['original_content'] );
    }
    
    /**
     * Test get_backup returns null for invalid ID
     */
    public function test_get_backup_returns_null_for_invalid_id() {
        $backup = $this->backup_service->get_backup( 99999 );
        $this->assertNull( $backup );
        
        $backup = $this->backup_service->get_backup( null );
        $this->assertNull( $backup );
    }
    
    /**
     * Test get_latest_backup returns most recent backup
     */
    public function test_get_latest_backup_returns_most_recent() {
        // Create multiple backups
        $backup_id_1 = $this->backup_service->save_backup( $this->test_post_id, 'Content 1' );
        sleep( 1 );
        $backup_id_2 = $this->backup_service->save_backup( $this->test_post_id, 'Content 2' );
        
        $latest = $this->backup_service->get_latest_backup( $this->test_post_id );
        
        $this->assertIsArray( $latest );
        $this->assertEquals( $backup_id_2, $latest['id'] );
        $this->assertEquals( 'Content 2', $latest['original_content'] );
    }
    
    /**
     * Test get_backups_by_post returns all backups
     */
    public function test_get_backups_by_post_returns_all_backups() {
        // Create multiple backups
        $this->backup_service->save_backup( $this->test_post_id, 'Content 1' );
        $this->backup_service->save_backup( $this->test_post_id, 'Content 2' );
        $this->backup_service->save_backup( $this->test_post_id, 'Content 3' );
        
        $backups = $this->backup_service->get_backups_by_post( $this->test_post_id );
        
        $this->assertCount( 3, $backups );
        $this->assertEquals( 'Content 3', $backups[0]['original_content'] ); // Most recent first
    }
    
    /**
     * Test get_backups_by_post respects limit
     */
    public function test_get_backups_by_post_respects_limit() {
        // Create 5 backups
        for ( $i = 1; $i <= 5; $i++ ) {
            $this->backup_service->save_backup( $this->test_post_id, "Content {$i}" );
        }
        
        $backups = $this->backup_service->get_backups_by_post( $this->test_post_id, 3 );
        
        $this->assertCount( 3, $backups );
    }
    
    /**
     * Test restore_backup restores content
     */
    public function test_restore_backup_restores_content() {
        $original_content = 'Original content';
        $backup_id = $this->backup_service->save_backup( $this->test_post_id, $original_content );
        
        // Change post content
        wp_update_post(
            array(
                'ID'           => $this->test_post_id,
                'post_content' => 'Modified content',
            )
        );
        
        // Restore
        $result = $this->backup_service->restore_backup( $this->test_post_id, $backup_id );
        
        $this->assertIsArray( $result );
        $this->assertEquals( $original_content, $result['original_content'] );
        
        // Verify post content restored
        $post = get_post( $this->test_post_id );
        $this->assertEquals( $original_content, $post->post_content );
    }
    
    /**
     * Test restore_backup with latest backup
     */
    public function test_restore_backup_uses_latest_when_no_id_specified() {
        $this->backup_service->save_backup( $this->test_post_id, 'Content 1' );
        sleep( 1 );
        $this->backup_service->save_backup( $this->test_post_id, 'Content 2' );
        
        $result = $this->backup_service->restore_backup( $this->test_post_id );
        
        $this->assertIsArray( $result );
        $this->assertEquals( 'Content 2', $result['original_content'] );
    }
    
    /**
     * Test restore_backup returns error when no backup exists
     */
    public function test_restore_backup_returns_error_when_no_backup() {
        $result = $this->backup_service->restore_backup( $this->test_post_id );
        
        $this->assertWPError( $result );
        $this->assertEquals( 'no_backup', $result->get_error_code() );
    }
    
    /**
     * Test has_backup checks existence
     */
    public function test_has_backup_checks_existence() {
        $this->assertFalse( $this->backup_service->has_backup( $this->test_post_id ) );
        
        $this->backup_service->save_backup( $this->test_post_id, 'Content' );
        
        $this->assertTrue( $this->backup_service->has_backup( $this->test_post_id ) );
    }
    
    /**
     * Test cleanup_old_backups deletes old backups
     */
    public function test_cleanup_old_backups_deletes_old_backups() {
        global $wpdb;
        $table = $wpdb->prefix . 'slos_accessibility_fix_history';
        
        // Create backup and manually set old date
        $backup_id = $this->backup_service->save_backup( $this->test_post_id, 'Old content' );
        
        $old_date = gmdate( 'Y-m-d H:i:s', strtotime( '-45 days' ) );
        $wpdb->update(
            $table,
            array( 'created_at' => $old_date ),
            array( 'id' => $backup_id ),
            array( '%s' ),
            array( '%d' )
        );
        
        // Create recent backup
        $this->backup_service->save_backup( $this->test_post_id, 'Recent content' );
        
        // Cleanup backups older than 30 days
        $deleted = $this->backup_service->cleanup_old_backups( 30 );
        
        $this->assertEquals( 1, $deleted );
        
        // Verify recent backup still exists
        $backups = $this->backup_service->get_backups_by_post( $this->test_post_id );
        $this->assertCount( 1, $backups );
        $this->assertEquals( 'Recent content', $backups[0]['original_content'] );
    }
    
    /**
     * Test get_statistics returns correct data
     */
    public function test_get_statistics_returns_correct_data() {
        // Create backups for multiple posts
        $post_id_2 = $this->factory->post->create();
        
        $this->backup_service->save_backup( $this->test_post_id, 'Content 1' );
        $this->backup_service->save_backup( $this->test_post_id, 'Content 2' );
        $this->backup_service->save_backup( $post_id_2, 'Content 3' );
        
        $stats = $this->backup_service->get_statistics();
        
        $this->assertIsArray( $stats );
        $this->assertEquals( 3, $stats['total_backups'] );
        $this->assertEquals( 2, $stats['unique_posts'] );
        $this->assertArrayHasKey( 'total_size_bytes', $stats );
        $this->assertArrayHasKey( 'oldest_backup', $stats );
        $this->assertArrayHasKey( 'newest_backup', $stats );
        
        wp_delete_post( $post_id_2, true );
    }
    
    /**
     * Test metadata is properly stored and retrieved
     */
    public function test_metadata_is_properly_handled() {
        $metadata = array(
            'scan_before' => array( 'issue1', 'issue2' ),
            'scan_after'  => array( 'issue3' ),
            'fixed_count' => 5,
        );
        
        $backup_id = $this->backup_service->save_backup( $this->test_post_id, 'Content', $metadata );
        $backup = $this->backup_service->get_backup( $backup_id );
        
        $this->assertIsArray( $backup['metadata'] );
        $this->assertEquals( $metadata, $backup['metadata'] );
    }
    
    /**
     * Test empty metadata defaults to empty array
     */
    public function test_empty_metadata_defaults_to_empty_array() {
        $backup_id = $this->backup_service->save_backup( $this->test_post_id, 'Content' );
        $backup = $this->backup_service->get_backup( $backup_id );
        
        $this->assertIsArray( $backup['metadata'] );
        $this->assertEmpty( $backup['metadata'] );
    }
    
    /**
     * Test save_backup with invalid metadata
     */
    public function test_save_backup_with_invalid_metadata() {
        // Non-array metadata should be converted to array
        $backup_id = $this->backup_service->save_backup( $this->test_post_id, 'Content', 'string_metadata' );
        
        $this->assertIsInt( $backup_id );
        $this->assertGreaterThan( 0, $backup_id );
        
        $backup = $this->backup_service->get_backup( $backup_id );
        $this->assertIsArray( $backup['metadata'] );
    }
    
    /**
     * Test large content backup
     */
    public function test_large_content_backup() {
        // Create large content (1MB)
        $large_content = str_repeat( 'Large content test. ', 50000 );
        
        $backup_id = $this->backup_service->save_backup( $this->test_post_id, $large_content );
        
        $this->assertIsInt( $backup_id );
        
        $backup = $this->backup_service->get_backup( $backup_id );
        $this->assertEquals( $large_content, $backup['original_content'] );
    }
    
    /**
     * Test cleanup_old_backups validates days parameter
     */
    public function test_cleanup_old_backups_validates_days_parameter() {
        // Test with invalid values - should default to 30 days
        $deleted = $this->backup_service->cleanup_old_backups( 0 );
        $this->assertIsInt( $deleted );
        
        $deleted = $this->backup_service->cleanup_old_backups( -5 );
        $this->assertIsInt( $deleted );
    }
    
    /**
     * Test get_backups_by_post with no backups
     */
    public function test_get_backups_by_post_with_no_backups() {
        $backups = $this->backup_service->get_backups_by_post( $this->test_post_id );
        
        $this->assertIsArray( $backups );
        $this->assertEmpty( $backups );
    }
    
    /**
     * Test statistics with empty database
     */
    public function test_statistics_with_empty_database() {
        $stats = $this->backup_service->get_statistics();
        
        $this->assertEquals( 0, $stats['total_backups'] );
        $this->assertEquals( 0, $stats['unique_posts'] );
        $this->assertEquals( 0, $stats['total_size_bytes'] );
    }
}
