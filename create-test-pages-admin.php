<?php
/**
 * Create Test Pages - Admin Interface
 * 
 * Access this file directly: http://localhost:8080/wp-content/plugins/Shahi%20LegalOps%20Suite%20-%203.1.1/create-test-pages-admin.php
 * Or add ?create_test_pages=1 to any admin page
 */

// Load WordPress if not already loaded
if (!defined('ABSPATH')) {
    require_once(__DIR__ . '/../../../wp-load.php');
}

// Check if user is admin
if (!current_user_can('manage_options')) {
    wp_die('You do not have permission to access this page.');
}

// Check if action is triggered
if (isset($_GET['action']) && $_GET['action'] === 'create_test_pages' && check_admin_referer('create_test_pages')) {
    
    echo '<div style="max-width: 1200px; margin: 50px auto; padding: 20px; background: #0f172a; color: #f8fafc; font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, sans-serif;">';
    echo '<h1 style="color: #3b82f6; margin-bottom: 30px;">Creating Accessibility Test Pages...</h1>';
    
    // Delete existing test pages
    $existing = get_posts([
        'post_type' => 'page',
        'post_status' => 'any',
        'posts_per_page' => -1,
        's' => 'Test:'
    ]);
    
    echo '<h2 style="color: #22c55e;">Cleaning Up Old Test Pages</h2>';
    $deleted = 0;
    foreach ($existing as $page) {
        if (strpos($page->post_title, 'Test:') === 0) {
            wp_delete_post($page->ID, true);
            $deleted++;
            echo '<p style="color: #94a3b8;">✗ Deleted: ' . esc_html($page->post_title) . '</p>';
        }
    }
    echo '<p><strong>' . $deleted . ' old test pages removed.</strong></p>';
    
    echo '<h2 style="color: #22c55e; margin-top: 30px;">Creating New Test Pages</h2>';
    
    // Define test pages
    $test_pages = [];
    
    // Test Page 1: Image Issues
    $test_pages[] = [
        'title' => 'Test: Image Accessibility Issues',
        'content' => '<h1>Image Accessibility Test Page</h1>

<h2>Missing Alt Text</h2>
<img src="https://picsum.photos/300/200?random=1" width="300" height="200">
<p>This image has no alt attribute.</p>

<h2>Empty Alt Text (Non-Decorative)</h2>
<img src="https://picsum.photos/300/200?random=2" alt="" width="300" height="200">
<p>This important image has empty alt text.</p>

<h2>Redundant Alt Text</h2>
<img src="https://picsum.photos/300/200?random=3" alt="image of picture photo graphic" width="300" height="200">
<p>This alt text contains redundant words like "image", "picture".</p>

<h2>Poor Quality Alt Text</h2>
<img src="https://picsum.photos/300/200?random=4" alt="img" width="300" height="200">

<h2>Complex Image Without Description</h2>
<img src="https://picsum.photos/600/400?random=5" alt="Complex chart" width="600" height="400">

<h2>SVG Without Accessibility</h2>
<svg width="100" height="100"><circle cx="50" cy="50" r="40" fill="red"/></svg>

<h2>Image Map Without Alt</h2>
<img src="https://picsum.photos/400/300?random=7" usemap="#map1" width="400" height="300">
<map name="map1">
    <area shape="rect" coords="0,0,200,150" href="#section1">
    <area shape="rect" coords="200,150,400,300" href="#section2">
</map>'
    ];
    
    // Test Page 2: Heading Issues
    $test_pages[] = [
        'title' => 'Test: Heading Structure Issues',
        'content' => '<h2>This page is missing H1</h2>
<p>The first heading should be H1, not H2.</p>

<h1>First H1 Heading</h1>
<h1>Second H1 Heading - Multiple H1s</h1>

<h2>Correct H2</h2>
<h4>Skipped H3 - Went from H2 to H4</h4>

<h3>Empty Heading Below</h3>
<h3></h3>

<h2>Very Long Heading That Goes On And On And On And Continues To Be Extremely Verbose Without Any Real Reason To Be This Long Because It Should Be Concise</h2>

<h3>Contact Us</h3>
<p>Some content here.</p>
<h3>Contact Us</h3>
<p>Duplicate heading - not unique.</p>

<h2><span style="font-size: 14px; font-weight: normal;">Heading Without Visual Emphasis</span></h2>'
    ];
    
    // Test Page 3: Form Issues
    $test_pages[] = [
        'title' => 'Test: Form Accessibility Issues',
        'content' => '<h1>Form Accessibility Test</h1>

<h2>Missing Form Labels</h2>
<form>
    <input type="text" id="name1" placeholder="Enter your name">
    <input type="email" id="email1" placeholder="Enter email">
    <button type="submit">Submit</button>
</form>

<h2>Placeholder Used as Label</h2>
<form>
    <input type="text" placeholder="Full Name (bad practice)">
    <input type="email" placeholder="Email Address">
</form>

<h2>Radio/Checkbox Without Fieldset</h2>
<form>
    <p>Choose your preference:</p>
    <input type="radio" name="pref" value="opt1"> Option 1<br>
    <input type="radio" name="pref" value="opt2"> Option 2<br>
    <input type="checkbox" name="terms"> I agree to terms
</form>

<h2>Missing Autocomplete</h2>
<form>
    <label for="email2">Email:</label>
    <input type="email" id="email2" name="email">
    <label for="tel">Phone:</label>
    <input type="tel" id="tel" name="phone">
</form>

<h2>Orphaned Label</h2>
<form>
    <label for="nonexistent">This label has no matching input</label>
    <input type="text" id="different" name="field">
</form>'
    ];
    
    // Test Page 4: Link Issues
    $test_pages[] = [
        'title' => 'Test: Link Accessibility Issues',
        'content' => '<h1>Link Accessibility Test</h1>

<h2>Empty Links</h2>
<p><a href="#"></a></p>
<p><a href="/page"></a></p>

<h2>Generic Link Text</h2>
<p>For more information, <a href="/info">click here</a>.</p>
<p>To learn more, <a href="/learn">read more</a>.</p>
<p><a href="/details">More info</a> about our services.</p>

<h2>Links Opening in New Window</h2>
<p>Visit our <a href="https://example.com" target="_blank">partner site</a>.</p>
<p>Check out <a href="https://example.org" target="_blank">this resource</a>.</p>

<h2>Download Links Without Info</h2>
<p><a href="/files/document.pdf">Download Document</a></p>
<p><a href="/files/report.docx">Annual Report</a></p>'
    ];
    
    // Test Page 5: Color & Contrast Issues
    $test_pages[] = [
        'title' => 'Test: Color & Contrast Issues',
        'content' => '<h1>Color and Contrast Test</h1>

<h2>Low Contrast Text</h2>
<p style="color: #999999; background: #ffffff;">Light gray text on white has poor contrast.</p>
<p style="color: #ffff00; background: #ffffff;">Yellow text on white background.</p>
<p style="color: #cccccc; background: #dddddd;">Very low contrast.</p>

<h2>Color Conveys Information</h2>
<p>Required fields are marked in <span style="color: red;">red</span>.</p>
<p><span style="color: green;">✓ Available</span> | <span style="color: red;">✗ Unavailable</span></p>

<h2>Links Without Distinction</h2>
<p>This paragraph has <a href="#" style="color: inherit; text-decoration: none;">a link that looks like text</a>.</p>'
    ];
    
    // Test Page 6: Keyboard Issues
    $test_pages[] = [
        'title' => 'Test: Keyboard Accessibility Issues',
        'content' => '<h1>Keyboard Accessibility Test</h1>

<h2>Positive Tabindex</h2>
<input type="text" tabindex="3" placeholder="Third">
<input type="text" tabindex="1" placeholder="First">
<input type="text" tabindex="2" placeholder="Second">

<h2>Interactive Elements Without Keyboard</h2>
<div onclick="alert(\'Clicked\')" style="cursor: pointer; padding: 10px; background: #ddd;">
    Click me (not keyboard accessible)
</div>

<h2>Illogical Focus Order</h2>
<form>
    <div style="float: right;"><input type="text" placeholder="Last Name"></div>
    <div style="float: left;"><input type="text" placeholder="First Name"></div>
    <div style="clear: both;"><input type="email" placeholder="Email"></div>
</form>'
    ];
    
    // Test Page 7: ARIA Issues
    $test_pages[] = [
        'title' => 'Test: ARIA Accessibility Issues',
        'content' => '<h1>ARIA Accessibility Test</h1>

<h2>Invalid ARIA Roles</h2>
<div role="invalid-role">Content with invalid role</div>
<span role="button">This should use real button</span>

<h2>Invalid ARIA Attributes</h2>
<div aria-invalid-attr="true">Non-existent ARIA attribute</div>
<button aria-pressed="maybe">Invalid aria-pressed value</button>

<h2>Missing Landmarks</h2>
<div>
    <div><h2>Navigation</h2><a href="/">Home</a> | <a href="/about">About</a></div>
    <div><h2>Main Content</h2><p>Should be wrapped in landmarks.</p></div>
</div>

<h2>Redundant ARIA</h2>
<button role="button" aria-label="Submit Button">Submit</button>
<nav role="navigation">Main navigation</nav>

<h2>Hidden Content Issues</h2>
<div aria-hidden="true">
    <button>Button in aria-hidden parent</button>
</div>'
    ];
    
    // Test Page 8: Semantic HTML Issues
    $test_pages[] = [
        'title' => 'Test: Semantic HTML Issues',
        'content' => '<h1>Semantic HTML Test</h1>

<h2>Non-Semantic Interactive Elements</h2>
<div onclick="submit()">Submit Form</div>
<span onclick="navigate()">Click to Navigate</span>

<h2>Table Without Headers</h2>
<table border="1">
    <tr><td>Name</td><td>Age</td><td>City</td></tr>
    <tr><td>John</td><td>30</td><td>NYC</td></tr>
</table>

<h2>Table Without Caption</h2>
<table border="1">
    <tr><th>Product</th><th>Price</th></tr>
    <tr><td>Widget</td><td>$10</td></tr>
</table>

<h2>Empty Table Cells</h2>
<table border="1">
    <tr><th>Name</th><th>Value</th></tr>
    <tr><td>Item 1</td><td></td></tr>
    <tr><td></td><td>Data</td></tr>
</table>'
    ];
    
    // Test Page 9: Media & Mobile Issues
    $test_pages[] = [
        'title' => 'Test: Media & Mobile Issues',
        'content' => '<h1>Media and Mobile Test</h1>

<h2>Video Without Captions</h2>
<video controls width="400">
    <source src="https://www.w3schools.com/html/mov_bbb.mp4" type="video/mp4">
</video>

<h2>Audio Without Transcript</h2>
<audio controls>
    <source src="https://www.w3schools.com/html/horse.mp3" type="audio/mpeg">
</audio>

<h2>Small Touch Targets</h2>
<button style="padding: 2px 4px; font-size: 10px;">Tiny</button>
<a href="#" style="font-size: 8px; padding: 1px;">Small Link</a>

<h2>Wide Content</h2>
<p style="width: 1200px; overflow: hidden;">
    This content is too wide and requires horizontal scrolling on mobile.
</p>'
    ];
    
    // Test Page 10: Advanced Issues
    $test_pages[] = [
        'title' => 'Test: Advanced Accessibility Issues',
        'content' => '<h1>Advanced Accessibility Test</h1>

<h2>Iframe Without Title</h2>
<iframe src="https://www.example.com" width="400" height="300"></iframe>

<h2>Language Changes Not Marked</h2>
<p>English text, then: Bonjour, comment allez-vous? (French not marked)</p>
<p>More English: Hola, ¿cómo estás? (Spanish not marked)</p>

<h2>Button Without Accessible Name</h2>
<button></button>
<button><img src="icon.png"></button>

<h2>Status Messages Without ARIA</h2>
<div id="status">Loading complete</div>
<div id="error">Error occurred</div>

<h2>Required Field Not Indicated</h2>
<form>
    <label>Email (required but not marked)</label>
    <input type="email" required>
</form>'
    ];
    
    // Create pages
    $created = 0;
    foreach ($test_pages as $page_data) {
        $post_id = wp_insert_post([
            'post_title' => $page_data['title'],
            'post_content' => $page_data['content'],
            'post_status' => 'publish',
            'post_type' => 'page',
            'post_author' => get_current_user_id()
        ]);
        
        if ($post_id && !is_wp_error($post_id)) {
            $created++;
            echo '<div style="background: #1e293b; padding: 15px; margin: 10px 0; border-left: 4px solid #22c55e; border-radius: 8px;">';
            echo '<p style="margin: 0; color: #f8fafc;"><strong>✓ Created:</strong> ' . esc_html($page_data['title']) . '</p>';
            echo '<p style="margin: 5px 0 0; color: #94a3b8; font-size: 14px;">ID: ' . $post_id . ' | <a href="' . get_permalink($post_id) . '" target="_blank" style="color: #3b82f6;">View Page</a> | <a href="' . get_edit_post_link($post_id) . '" style="color: #3b82f6;">Edit</a></p>';
            echo '</div>';
        } else {
            echo '<div style="background: #1e293b; padding: 15px; margin: 10px 0; border-left: 4px solid #ef4444; border-radius: 8px;">';
            echo '<p style="margin: 0; color: #f8fafc;"><strong>✗ Failed:</strong> ' . esc_html($page_data['title']) . '</p>';
            echo '</div>';
        }
    }
    
    echo '<div style="background: #1e293b; padding: 20px; margin: 30px 0; border-radius: 12px; border: 2px solid #3b82f6;">';
    echo '<h2 style="color: #3b82f6; margin-top: 0;">✓ Summary</h2>';
    echo '<p style="font-size: 18px;"><strong>Successfully created: ' . $created . ' test pages</strong></p>';
    echo '<p style="color: #94a3b8;">These pages contain various accessibility issues for testing the scanner and autofix features.</p>';
    echo '<p style="margin-top: 20px;"><a href="' . admin_url('admin.php?page=slos-accessibility&tab=tools') . '" style="background: #3b82f6; color: white; padding: 12px 24px; text-decoration: none; border-radius: 8px; display: inline-block; font-weight: 600;">Go to Accessibility Scanner →</a></p>';
    echo '</div>';
    
    echo '</div>';
    exit;
}

// Show form
?>
<!DOCTYPE html>
<html>
<head>
    <title>Create Test Pages</title>
    <style>
        body {
            background: #0f172a;
            color: #f8fafc;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            margin: 0;
            padding: 50px;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: #1e293b;
            padding: 40px;
            border-radius: 16px;
            border: 1px solid #334155;
        }
        h1 {
            color: #3b82f6;
            margin-top: 0;
            font-size: 32px;
        }
        .description {
            color: #94a3b8;
            line-height: 1.6;
            margin: 20px 0;
        }
        .checkers-list {
            background: #0f172a;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .checkers-list h3 {
            color: #22c55e;
            margin-top: 0;
        }
        .checkers-list ul {
            columns: 2;
            gap: 20px;
            margin: 10px 0;
            padding-left: 20px;
        }
        .checkers-list li {
            color: #cbd5e1;
            margin: 5px 0;
        }
        .btn {
            background: #3b82f6;
            color: white;
            padding: 16px 32px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: background 0.2s;
        }
        .btn:hover {
            background: #2563eb;
        }
        .warning {
            background: #451a03;
            border-left: 4px solid #f59e0b;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
            color: #fbbf24;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🧪 Create Accessibility Test Pages</h1>
        
        <p class="description">
            This will create 10 test pages containing various accessibility issues to test the Accessibility Scanner and Auto-Fix features.
        </p>
        
        <div class="checkers-list">
            <h3>Test Pages Will Include:</h3>
            <ul>
                <li>Image Issues (missing alt, empty alt, redundant, SVG, etc.)</li>
                <li>Heading Issues (missing H1, multiple H1, skipped levels)</li>
                <li>Form Issues (missing labels, fieldsets, autocomplete)</li>
                <li>Link Issues (empty links, generic text, new window)</li>
                <li>Color & Contrast Issues</li>
                <li>Keyboard Accessibility Issues</li>
                <li>ARIA Issues (invalid roles, redundant ARIA)</li>
                <li>Semantic HTML Issues (tables without headers)</li>
                <li>Media & Mobile Issues (video without captions)</li>
                <li>Advanced Issues (iframes, language, timing)</li>
            </ul>
        </div>
        
        <div class="warning">
            <strong>⚠️ Note:</strong> Any existing test pages (starting with "Test:") will be deleted before creating new ones.
        </div>
        
        <form method="get">
            <?php wp_nonce_field('create_test_pages'); ?>
            <input type="hidden" name="action" value="create_test_pages">
            <button type="submit" class="btn">Create Test Pages</button>
        </form>
    </div>
</body>
</html>
