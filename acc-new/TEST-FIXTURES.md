# Test Fixtures for Accessibility Checkers

HTML samples for testing each checker.

---

## Test File Structure

```
acc-new/tests/
├── fixtures/
│   ├── passing/          # HTML that should PASS all checks
│   ├── failing/          # HTML that should FAIL specific checks  
│   └── edge-cases/       # Edge cases and complex scenarios
└── run-tests.php         # Test runner script
```

---

## Passing Test Cases

### passing/complete-accessible-page.html

```html
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accessible Test Page</title>
</head>
<body>
    <a href="#main-content" class="skip-link">Skip to main content</a>
    
    <header>
        <nav aria-label="Main navigation">
            <ul>
                <li><a href="/">Home</a></li>
                <li><a href="/about">About Us</a></li>
                <li><a href="/contact">Contact Us</a></li>
            </ul>
        </nav>
    </header>
    
    <main id="main-content">
        <h1>Welcome to Our Website</h1>
        
        <article>
            <h2>Article Title</h2>
            <p style="color: #000; background-color: #fff;">
                This paragraph has sufficient color contrast (21:1).
            </p>
            
            <img src="product.jpg" alt="Red sneakers with white laces on a wooden floor">
            
            <figure>
                <img src="chart.png" alt="Bar chart showing sales growth">
                <figcaption>Sales growth from 2020 to 2024</figcaption>
            </figure>
        </article>
        
        <section aria-labelledby="form-heading">
            <h2 id="form-heading">Contact Form</h2>
            
            <form action="/submit" method="post">
                <div>
                    <label for="name">Full Name</label>
                    <input type="text" id="name" name="name" required 
                           autocomplete="name"
                           aria-describedby="name-error">
                    <span id="name-error" class="error" aria-live="polite"></span>
                </div>
                
                <div>
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" required 
                           autocomplete="email"
                           aria-describedby="email-error">
                    <span id="email-error" class="error" aria-live="polite"></span>
                </div>
                
                <fieldset>
                    <legend>Preferred Contact Method</legend>
                    <div>
                        <input type="radio" id="contact-email" name="contact" value="email">
                        <label for="contact-email">Email</label>
                    </div>
                    <div>
                        <input type="radio" id="contact-phone" name="contact" value="phone">
                        <label for="contact-phone">Phone</label>
                    </div>
                </fieldset>
                
                <button type="submit">Send Message</button>
            </form>
        </section>
        
        <section aria-labelledby="video-heading">
            <h2 id="video-heading">Our Video</h2>
            <video controls>
                <source src="intro.mp4" type="video/mp4">
                <track kind="captions" src="captions.vtt" srclang="en" label="English">
                <track kind="descriptions" src="descriptions.vtt" srclang="en" label="Audio Descriptions">
            </video>
        </section>
        
        <section aria-labelledby="table-heading">
            <h2 id="table-heading">Price Comparison</h2>
            <table>
                <caption>Product prices by region</caption>
                <thead>
                    <tr>
                        <th scope="col">Product</th>
                        <th scope="col">US Price</th>
                        <th scope="col">EU Price</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <th scope="row">Widget A</th>
                        <td>$10</td>
                        <td>€9</td>
                    </tr>
                    <tr>
                        <th scope="row">Widget B</th>
                        <td>$20</td>
                        <td>€18</td>
                    </tr>
                </tbody>
            </table>
        </section>
    </main>
    
    <aside aria-label="Related links">
        <h2>Related Articles</h2>
        <ul>
            <li><a href="/article-1">Understanding Accessibility Standards</a></li>
            <li><a href="/article-2">Best Practices for Web Forms</a></li>
        </ul>
    </aside>
    
    <footer>
        <nav aria-label="Footer navigation">
            <ul>
                <li><a href="/privacy">Privacy Policy</a></li>
                <li><a href="/terms">Terms of Service</a></li>
            </ul>
        </nav>
        <p>&copy; 2024 Our Company</p>
    </footer>
    
    <div role="status" aria-live="polite" id="notifications"></div>
</body>
</html>
```

---

## Failing Test Cases

### failing/missing-alt.html

```html
<!-- EXPECTED: missing-alt-text check should FAIL -->
<div>
    <img src="product1.jpg">
    <img src="product2.jpg" alt="">
    <img src="product3.jpg" alt="   ">
</div>
```

### failing/low-contrast.html

```html
<!-- EXPECTED: text-color-contrast check should FAIL -->
<div>
    <p style="color: #777; background-color: #fff;">Gray on white - ratio ~4.48:1</p>
    <p style="color: #888; background-color: #fff;">Lighter gray - ratio ~3.54:1</p>
    <p style="color: #aaa; background-color: #ccc;">Gray on gray - very low contrast</p>
</div>
```

### failing/missing-skip-link.html

```html
<!-- EXPECTED: skip-link check should FAIL -->
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Page Without Skip Link</title>
</head>
<body>
    <header>
        <nav>
            <ul>
                <li><a href="/">Home</a></li>
                <li><a href="/about">About</a></li>
            </ul>
        </nav>
    </header>
    <main>
        <h1>Content</h1>
        <p>No skip link provided.</p>
    </main>
</body>
</html>
```

### failing/keyboard-issues.html

```html
<!-- EXPECTED: interactive-element check should FAIL -->
<div>
    <!-- Non-interactive element with onclick but no keyboard support -->
    <div onclick="doSomething()" class="fake-button">Click Me</div>
    
    <!-- Missing keyboard handler -->
    <span onclick="toggle()" tabindex="0" role="button">Toggle</span>
    
    <!-- Positive tabindex -->
    <button tabindex="5">Bad Tab Order</button>
</div>
```

### failing/form-labels.html

```html
<!-- EXPECTED: missing-form-label check should FAIL -->
<form>
    <input type="text" name="username">
    <input type="email" name="email" placeholder="Email">
    <select name="country">
        <option>Select country</option>
    </select>
    <textarea name="message"></textarea>
</form>
```

### failing/heading-structure.html

```html
<!-- EXPECTED: skipped-heading-level check should FAIL -->
<div>
    <h1>Main Title</h1>
    <h3>Skipped to H3</h3>
    <h5>Skipped to H5</h5>
    <h2></h2> <!-- empty-heading -->
</div>
```

### failing/generic-links.html

```html
<!-- EXPECTED: generic-link-text check should FAIL -->
<nav>
    <a href="/page1">Click here</a>
    <a href="/page2">Read more</a>
    <a href="/page3">Learn more</a>
    <a href="/page4">Here</a>
    <a href="/page5">More</a>
    <a href="/page6">Link</a>
</nav>
```

### failing/landmarks.html

```html
<!-- EXPECTED: landmark-role check should FAIL -->
<body>
    <!-- Multiple nav without labels -->
    <nav>Main navigation</nav>
    <nav>Footer navigation</nav>
    
    <!-- Multiple main landmarks -->
    <main>Content 1</main>
    <main>Content 2</main>
</body>
```

### failing/aria-issues.html

```html
<!-- EXPECTED: aria-role and aria-attribute checks should FAIL -->
<div>
    <!-- Invalid role -->
    <div role="invalid-role">Bad role</div>
    
    <!-- Abstract role -->
    <div role="widget">Abstract role</div>
    
    <!-- Missing required attributes -->
    <div role="checkbox">Checkbox without aria-checked</div>
    <div role="slider">Slider without value attributes</div>
    
    <!-- Invalid aria-live -->
    <div aria-live="maybe">Invalid value</div>
</div>
```

### failing/modal-issues.html

```html
<!-- EXPECTED: modal-accessibility check should FAIL -->
<div role="dialog">
    <!-- Missing aria-modal and aria-label -->
    <h2>Dialog Title</h2>
    <p>Dialog content</p>
    <button>Close</button>
</div>
```

### failing/video-issues.html

```html
<!-- EXPECTED: video-accessibility check should FAIL -->
<div>
    <!-- Missing controls and captions -->
    <video autoplay>
        <source src="video.mp4" type="video/mp4">
    </video>
    
    <!-- Embedded without title -->
    <iframe src="https://youtube.com/embed/abc123"></iframe>
</div>
```

### failing/table-issues.html

```html
<!-- EXPECTED: table-header and table-caption checks should FAIL -->
<table>
    <tr>
        <td>Header 1</td>
        <td>Header 2</td>
    </tr>
    <tr>
        <td>Data 1</td>
        <td>Data 2</td>
    </tr>
</table>
```

### failing/focus-indicator.html

```html
<!-- EXPECTED: focus-indicator check should FAIL -->
<style>
    *:focus { outline: none; }
    button:focus { outline: 0; }
</style>
<button style="outline: none;">No Focus Indicator</button>
```

### failing/viewport-scaling.html

```html
<!-- EXPECTED: viewport-check should FAIL -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Non-Scalable Page</title>
</head>
<body>
    <p>Cannot zoom this page</p>
</body>
</html>
```

### failing/status-messages.html

```html
<!-- EXPECTED: status-message check should FAIL -->
<div>
    <!-- Status containers without live region -->
    <div class="alert">Alert without role</div>
    <div class="notification">Notification without aria-live</div>
    <div class="toast" data-message="">Empty toast container</div>
</div>
```

### failing/animation-issues.html

```html
<!-- EXPECTED: animation-pause check should FAIL -->
<style>
    .infinite-spin {
        animation: spin 2s linear infinite;
    }
    .long-animation {
        animation-duration: 10s;
    }
</style>
<div class="infinite-spin">Spinning forever</div>
<marquee>Deprecated scrolling text</marquee>
```

### failing/timing-issues.html

```html
<!-- EXPECTED: timing-control check should FAIL -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="refresh" content="30">
    <title>Auto-refresh Page</title>
</head>
<body>
    <p>This page refreshes every 30 seconds</p>
    <div class="countdown-timer">5:00</div>
</body>
</html>
```

---

## Edge Cases

### edge-cases/decorative-images.html

```html
<!-- Images that SHOULD have empty alt (decorative) -->
<div>
    <img src="decorative-border.png" alt="" role="presentation">
    <img src="bullet-point.gif" alt="">
    
    <!-- But this is NOT decorative - has link -->
    <a href="/product"><img src="product.jpg"></a>
</div>
```

### edge-cases/nested-landmarks.html

```html
<!-- header/footer inside article should NOT be landmarks -->
<article>
    <header>Article header - NOT a banner landmark</header>
    <p>Article content</p>
    <footer>Article footer - NOT a contentinfo landmark</footer>
</article>

<!-- But these ARE landmarks -->
<header>Page banner</header>
<footer>Page contentinfo</footer>
```

### edge-cases/implicit-labels.html

```html
<!-- These should PASS - valid labeling methods -->
<form>
    <!-- Wrapped in label -->
    <label>
        Username
        <input type="text" name="username">
    </label>
    
    <!-- Using aria-label -->
    <input type="search" aria-label="Search products">
    
    <!-- Using aria-labelledby -->
    <span id="qty-label">Quantity</span>
    <input type="number" aria-labelledby="qty-label">
    
    <!-- Using title (fallback) -->
    <input type="text" title="Enter your ZIP code">
</form>
```

### edge-cases/large-text-contrast.html

```html
<!-- Large text has lower contrast requirement (3:1 vs 4.5:1) -->
<div>
    <!-- This should PASS - large text with 3.5:1 ratio -->
    <h1 style="font-size: 24px; color: #767676; background: #fff;">Large Heading</h1>
    
    <!-- This should PASS - bold 14pt text -->
    <p style="font-size: 18.67px; font-weight: bold; color: #767676; background: #fff;">
        Bold large text
    </p>
    
    <!-- This should FAIL - small text with same ratio -->
    <p style="font-size: 14px; color: #767676; background: #fff;">
        Small text needs 4.5:1
    </p>
</div>
```

### edge-cases/color-formats.html

```html
<!-- Test various color formats -->
<div>
    <p style="color: #333; background-color: #fff;">3-digit hex</p>
    <p style="color: #333333; background-color: #ffffff;">6-digit hex</p>
    <p style="color: rgb(51, 51, 51); background-color: rgb(255, 255, 255);">RGB</p>
    <p style="color: rgba(51, 51, 51, 1); background-color: rgba(255, 255, 255, 1);">RGBA</p>
    <p style="color: hsl(0, 0%, 20%); background-color: hsl(0, 0%, 100%);">HSL</p>
    <p style="color: black; background-color: white;">Named colors</p>
</div>
```

---

## Test Runner Script

### tests/run-tests.php

```php
<?php
/**
 * Accessibility Checker Test Runner
 * 
 * Run from command line:
 * php run-tests.php
 */

require_once dirname(__DIR__, 2) . '/includes/Modules/AccessibilityScanner/Scanner/ScannerEngine.php';
// ... require all checker files

$scanner = new ScannerEngine();
// Register all checks...

$test_results = [];
$fixtures_dir = __DIR__ . '/fixtures';

// Run passing tests
$passing_files = glob($fixtures_dir . '/passing/*.html');
foreach ($passing_files as $file) {
    $content = file_get_contents($file);
    $issues = $scanner->scan($content);
    
    $test_results[] = [
        'file' => basename($file),
        'type' => 'passing',
        'passed' => empty($issues),
        'issues' => $issues,
    ];
}

// Run failing tests
$failing_files = glob($fixtures_dir . '/failing/*.html');
foreach ($failing_files as $file) {
    $content = file_get_contents($file);
    $issues = $scanner->scan($content);
    
    $test_results[] = [
        'file' => basename($file),
        'type' => 'failing',
        'passed' => !empty($issues),
        'issues' => $issues,
    ];
}

// Output results
echo "=== Accessibility Checker Test Results ===\n\n";

$passed = 0;
$failed = 0;

foreach ($test_results as $result) {
    $status = $result['passed'] ? '✅ PASS' : '❌ FAIL';
    echo "$status: {$result['file']} ({$result['type']})\n";
    
    if ($result['passed']) {
        $passed++;
    } else {
        $failed++;
        if (!empty($result['issues'])) {
            foreach ($result['issues'] as $check_id => $check_result) {
                echo "   - {$check_id}: " . count($check_result['issues']) . " issues\n";
            }
        }
    }
}

echo "\n=== Summary ===\n";
echo "Passed: $passed\n";
echo "Failed: $failed\n";
echo "Total: " . ($passed + $failed) . "\n";

exit($failed > 0 ? 1 : 0);
```
