# Advanced Analytics

## Build Custom Analytics and Reporting Systems

### Analytics Architecture Design

#### Step 1: Analytics Framework Setup
```
SLOS → Advanced → Analytics → Framework Design
```

**Analytics Architecture:**
```json
{
  "analytics_framework": {
    "data_collection": {
      "methods": ["event_tracking", "database_queries", "api_calls", "user_interactions"],
      "frequency": "real_time",
      "retention": "7_years",
      "anonymization": "automatic"
    },
    "data_processing": {
      "engine": "stream_processing",
      "aggregation": "multi_level",
      "transformation": "real_time",
      "validation": "schema_based"
    },
    "data_storage": {
      "primary": "time_series_database",
      "secondary": "data_warehouse",
      "caching": "redis_cluster",
      "backup": "automated"
    },
    "analytics_engine": {
      "query_language": "SQL_enhanced",
      "visualization": "custom_dashboards",
      "export": "multiple_formats",
      "api_access": "rest_graphql"
    }
  },
  "analytics_capabilities": {
    "real_time_analytics": true,
    "predictive_analytics": true,
    "anomaly_detection": true,
    "segmentation": "advanced",
    "attribution_modeling": true,
    "privacy_compliance": "gdpr_ccpa_compliant"
  }
}
```

#### Step 2: Event Tracking System
```php
// Advanced event tracking and analytics system
class AnalyticsEngine {
    private $event_collectors = [];
    private $data_processors = [];
    private $storage_backends = [];
    private $privacy_filters = [];

    public function __construct() {
        $this->initializeEventCollectors();
        $this->setupDataProcessors();
        $this->configureStorageBackends();
        $this->setupPrivacyFilters();
    }

    private function initializeEventCollectors() {
        // Register event collectors for different data sources
        $this->event_collectors = [
            'consent_events' => new ConsentEventCollector(),
            'user_interactions' => new UserInteractionCollector(),
            'system_events' => new SystemEventCollector(),
            'compliance_events' => new ComplianceEventCollector(),
            'performance_events' => new PerformanceEventCollector()
        ];

        // Hook into WordPress actions
        add_action('slos_consent_given', [$this->event_collectors['consent_events'], 'trackConsentGiven']);
        add_action('slos_consent_withdrawn', [$this->event_collectors['consent_events'], 'trackConsentWithdrawn']);
        add_action('wp_login', [$this->event_collectors['user_interactions'], 'trackUserLogin']);
        add_action('wp_logout', [$this->event_collectors['user_interactions'], 'trackUserLogout']);
    }

    private function setupDataProcessors() {
        $this->data_processors = [
            'real_time_processor' => new RealTimeDataProcessor(),
            'batch_processor' => new BatchDataProcessor(),
            'aggregation_processor' => new AggregationDataProcessor(),
            'anomaly_detector' => new AnomalyDetectionProcessor()
        ];
    }

    private function configureStorageBackends() {
        $this->storage_backends = [
            'time_series' => new TimeSeriesStorage(),
            'relational' => new RelationalStorage(),
            'cache' => new CacheStorage(),
            'archive' => new ArchiveStorage()
        ];
    }

    private function setupPrivacyFilters() {
        $this->privacy_filters = [
            'anonymization' => new DataAnonymizationFilter(),
            'pseudonymization' => new DataPseudonymizationFilter(),
            'aggregation_filter' => new AggregationPrivacyFilter(),
            'retention_filter' => new DataRetentionFilter()
        ];
    }

    public function trackEvent($event_type, $event_data, $context = []) {
        // Apply privacy filters first
        $filtered_data = $this->applyPrivacyFilters($event_data, $context);

        // Enrich event data
        $enriched_data = $this->enrichEventData($filtered_data, $context);

        // Store event
        $this->storeEvent($event_type, $enriched_data);

        // Process event in real-time
        $this->processEventRealTime($event_type, $enriched_data);

        // Trigger analytics hooks
        do_action('slos_analytics_event_tracked', $event_type, $enriched_data);
    }

    private function applyPrivacyFilters($event_data, $context) {
        $filtered_data = $event_data;

        foreach ($this->privacy_filters as $filter) {
            $filtered_data = $filter->apply($filtered_data, $context);
        }

        return $filtered_data;
    }

    private function enrichEventData($event_data, $context) {
        // Add metadata
        $event_data['_metadata'] = [
            'timestamp' => time(),
            'session_id' => $this->getSessionId(),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'ip_address' => $this->anonymizeIpAddress($_SERVER['REMOTE_ADDR'] ?? ''),
            'geo_location' => $this->getGeoLocation(),
            'device_info' => $this->getDeviceInfo()
        ];

        // Add context information
        $event_data['_context'] = $context;

        // Add calculated fields
        $event_data['_calculated'] = [
            'hour_of_day' => date('H', time()),
            'day_of_week' => date('N', time()),
            'week_of_year' => date('W', time()),
            'month' => date('m', time()),
            'year' => date('Y', time())
        ];

        return $event_data;
    }

    private function storeEvent($event_type, $event_data) {
        // Store in time-series database for real-time analytics
        $this->storage_backends['time_series']->store($event_type, $event_data);

        // Store in relational database for complex queries
        $this->storage_backends['relational']->store($event_type, $event_data);

        // Cache recent data
        $this->storage_backends['cache']->store($event_type, $event_data, 3600); // 1 hour TTL
    }

    private function processEventRealTime($event_type, $event_data) {
        // Update real-time metrics
        $this->data_processors['real_time_processor']->process($event_type, $event_data);

        // Check for anomalies
        $anomaly_score = $this->data_processors['anomaly_detector']->analyze($event_type, $event_data);
        if ($anomaly_score > 0.8) {
            $this->handleAnomaly($event_type, $event_data, $anomaly_score);
        }
    }

    private function handleAnomaly($event_type, $event_data, $anomaly_score) {
        // Log anomaly
        error_log("Analytics anomaly detected: {$event_type} with score {$anomaly_score}");

        // Trigger alert
        do_action('slos_analytics_anomaly_detected', $event_type, $event_data, $anomaly_score);

        // Store anomaly for analysis
        $this->storage_backends['relational']->storeAnomaly($event_type, $event_data, $anomaly_score);
    }

    public function getAnalyticsData($query, $time_range = null, $filters = []) {
        // Parse query
        $parsed_query = $this->parseAnalyticsQuery($query);

        // Apply time range filter
        if ($time_range) {
            $parsed_query['filters']['timestamp'] = $time_range;
        }

        // Apply additional filters
        $parsed_query['filters'] = array_merge($parsed_query['filters'], $filters);

        // Execute query
        return $this->executeAnalyticsQuery($parsed_query);
    }

    private function parseAnalyticsQuery($query) {
        // Simple query parser - in production, use a proper query language
        $parts = explode(' ', trim($query));

        return [
            'metric' => $parts[0] ?? 'count',
            'event_type' => $parts[1] ?? '*',
            'group_by' => $parts[2] ?? null,
            'filters' => []
        ];
    }

    private function executeAnalyticsQuery($parsed_query) {
        // Try cache first
        $cache_key = 'analytics_' . md5(json_encode($parsed_query));
        $cached_result = $this->storage_backends['cache']->get($cache_key);

        if ($cached_result) {
            return $cached_result;
        }

        // Execute query against appropriate storage backend
        if ($this->isTimeSeriesQuery($parsed_query)) {
            $result = $this->storage_backends['time_series']->query($parsed_query);
        } else {
            $result = $this->storage_backends['relational']->query($parsed_query);
        }

        // Cache result
        $this->storage_backends['cache']->store($cache_key, $result, 300); // 5 minute TTL

        return $result;
    }

    private function isTimeSeriesQuery($query) {
        // Determine if query should use time-series storage
        return isset($query['filters']['timestamp']) ||
               in_array($query['metric'], ['count_over_time', 'rate', 'increase']);
    }

    public function getRealTimeMetrics() {
        return $this->data_processors['real_time_processor']->getCurrentMetrics();
    }

    public function generateReport($report_type, $parameters = []) {
        $report_generator = new ReportGenerator($report_type, $parameters);
        return $report_generator->generate();
    }

    public function exportAnalyticsData($format, $query, $time_range = null) {
        $data = $this->getAnalyticsData($query, $time_range);

        switch ($format) {
            case 'csv':
                return $this->exportToCSV($data);
            case 'json':
                return json_encode($data);
            case 'xml':
                return $this->exportToXML($data);
            default:
                throw new Exception("Unsupported export format: {$format}");
        }
    }

    private function exportToCSV($data) {
        if (empty($data)) return '';

        $output = fopen('php://temp', 'r+');

        // Write headers
        if (is_array($data) && isset($data[0])) {
            fputcsv($output, array_keys($data[0]));
        }

        // Write data
        foreach ($data as $row) {
            fputcsv($output, $row);
        }

        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return $csv;
    }

    private function exportToXML($data) {
        $xml = new SimpleXMLElement('<analytics-data/>');

        foreach ($data as $row) {
            $item = $xml->addChild('item');
            foreach ($row as $key => $value) {
                $item->addChild($key, htmlspecialchars($value));
            }
        }

        return $xml->asXML();
    }

    // Utility methods
    private function getSessionId() {
        if (!session_id()) {
            session_start();
        }
        return session_id();
    }

    private function anonymizeIpAddress($ip) {
        // Anonymize IP address for privacy compliance
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $parts = explode('.', $ip);
            return $parts[0] . '.' . $parts[1] . '.0.0';
        } elseif (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            $parts = explode(':', $ip);
            return $parts[0] . ':' . $parts[1] . '::';
        }
        return 'unknown';
    }

    private function getGeoLocation() {
        // Get geo location from IP (simplified)
        return [
            'country' => 'Unknown',
            'region' => 'Unknown',
            'city' => 'Unknown'
        ];
    }

    private function getDeviceInfo() {
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        return [
            'browser' => $this->detectBrowser($user_agent),
            'os' => $this->detectOS($user_agent),
            'device_type' => $this->detectDeviceType($user_agent)
        ];
    }

    private function detectBrowser($user_agent) {
        if (strpos($user_agent, 'Chrome') !== false) return 'Chrome';
        if (strpos($user_agent, 'Firefox') !== false) return 'Firefox';
        if (strpos($user_agent, 'Safari') !== false) return 'Safari';
        if (strpos($user_agent, 'Edge') !== false) return 'Edge';
        return 'Unknown';
    }

    private function detectOS($user_agent) {
        if (strpos($user_agent, 'Windows') !== false) return 'Windows';
        if (strpos($user_agent, 'Mac') !== false) return 'macOS';
        if (strpos($user_agent, 'Linux') !== false) return 'Linux';
        if (strpos($user_agent, 'Android') !== false) return 'Android';
        if (strpos($user_agent, 'iOS') !== false) return 'iOS';
        return 'Unknown';
    }

    private function detectDeviceType($user_agent) {
        if (strpos($user_agent, 'Mobile') !== false) return 'Mobile';
        if (strpos($user_agent, 'Tablet') !== false) return 'Tablet';
        return 'Desktop';
    }
}
```

### Advanced Segmentation and Targeting

#### Step 1: User Segmentation Engine
```
SLOS → Advanced → Analytics → User Segmentation
```

**Segmentation Architecture:**
```json
{
  "segmentation_engine": {
    "segmentation_types": [
      {
        "type": "behavioral_segmentation",
        "description": "Segment based on user behavior patterns",
        "attributes": ["page_views", "time_spent", "conversion_actions", "engagement_score"]
      },
      {
        "type": "demographic_segmentation",
        "description": "Segment based on user demographics",
        "attributes": ["age", "location", "language", "device_type"]
      },
      {
        "type": "consent_based_segmentation",
        "description": "Segment based on consent preferences",
        "attributes": ["consent_categories", "consent_level", "withdrawal_history"]
      },
      {
        "type": "lifecycle_segmentation",
        "description": "Segment based on user lifecycle stage",
        "attributes": ["registration_date", "last_activity", "loyalty_score", "churn_risk"]
      }
    ],
    "segmentation_rules": {
      "rule_engine": "declarative",
      "operators": ["equals", "contains", "greater_than", "less_than", "between", "in"],
      "logic": ["AND", "OR", "NOT"],
      "nesting": "unlimited"
    },
    "segment_calculation": {
      "frequency": "real_time",
      "method": "streaming_aggregation",
      "caching": "intelligent",
      "persistence": "database"
    }
  },
  "segmentation_capabilities": {
    "dynamic_segments": true,
    "nested_segments": true,
    "segment_overlap_analysis": true,
    "segment_performance_tracking": true,
    "predictive_segmentation": true
  }
}
```

#### Step 2: Segmentation Engine Implementation
```php
// Advanced user segmentation and targeting engine
class SegmentationEngine {
    private $segment_definitions = [];
    private $user_segments = [];
    private $segment_cache = [];
    private $rule_engine = null;

    public function __construct() {
        $this->rule_engine = new SegmentationRuleEngine();
        $this->loadSegmentDefinitions();
        $this->initializeSegmentCache();
    }

    private function loadSegmentDefinitions() {
        global $wpdb;

        $segments = $wpdb->get_results("
            SELECT * FROM wp_slos_segments
            WHERE active = 1
        ");

        foreach ($segments as $segment) {
            $this->segment_definitions[$segment->id] = [
                'id' => $segment->id,
                'name' => $segment->name,
                'description' => $segment->description,
                'rules' => json_decode($segment->rules, true),
                'created_at' => $segment->created_at,
                'updated_at' => $segment->updated_at
            ];
        }
    }

    private function initializeSegmentCache() {
        // Initialize cache for frequently accessed segments
        $this->segment_cache = new SegmentCache();
    }

    public function createSegment($name, $description, $rules) {
        global $wpdb;

        // Validate rules
        if (!$this->validateSegmentRules($rules)) {
            throw new Exception('Invalid segment rules');
        }

        $segment_id = wp_generate_password(12, false);

        $wpdb->insert('wp_slos_segments', [
            'id' => $segment_id,
            'name' => $name,
            'description' => $description,
            'rules' => json_encode($rules),
            'active' => 1,
            'created_at' => current_time('mysql'),
            'updated_at' => current_time('mysql')
        ]);

        $this->segment_definitions[$segment_id] = [
            'id' => $segment_id,
            'name' => $name,
            'description' => $description,
            'rules' => $rules,
            'created_at' => current_time('mysql'),
            'updated_at' => current_time('mysql')
        ];

        return $segment_id;
    }

    public function updateSegment($segment_id, $name, $description, $rules) {
        global $wpdb;

        if (!isset($this->segment_definitions[$segment_id])) {
            throw new Exception('Segment not found');
        }

        // Validate rules
        if (!$this->validateSegmentRules($rules)) {
            throw new Exception('Invalid segment rules');
        }

        $wpdb->update('wp_slos_segments', [
            'name' => $name,
            'description' => $description,
            'rules' => json_encode($rules),
            'updated_at' => current_time('mysql')
        ], ['id' => $segment_id]);

        $this->segment_definitions[$segment_id]['name'] = $name;
        $this->segment_definitions[$segment_id]['description'] = $description;
        $this->segment_definitions[$segment_id]['rules'] = $rules;
        $this->segment_definitions[$segment_id]['updated_at'] = current_time('mysql');

        // Clear cache for this segment
        $this->segment_cache->clearSegment($segment_id);

        return true;
    }

    public function deleteSegment($segment_id) {
        global $wpdb;

        if (!isset($this->segment_definitions[$segment_id])) {
            throw new Exception('Segment not found');
        }

        $wpdb->update('wp_slos_segments', [
            'active' => 0,
            'updated_at' => current_time('mysql')
        ], ['id' => $segment_id]);

        unset($this->segment_definitions[$segment_id]);
        $this->segment_cache->clearSegment($segment_id);

        return true;
    }

    public function evaluateUserSegment($user_id, $segment_id) {
        // Check cache first
        $cached_result = $this->segment_cache->getUserSegment($user_id, $segment_id);
        if ($cached_result !== null) {
            return $cached_result;
        }

        if (!isset($this->segment_definitions[$segment_id])) {
            return false;
        }

        $segment = $this->segment_definitions[$segment_id];
        $user_data = $this->getUserData($user_id);

        $result = $this->rule_engine->evaluateRules($segment['rules'], $user_data);

        // Cache result
        $this->segment_cache->setUserSegment($user_id, $segment_id, $result);

        return $result;
    }

    public function getUserSegments($user_id) {
        $user_segments = [];

        foreach ($this->segment_definitions as $segment_id => $segment) {
            if ($this->evaluateUserSegment($user_id, $segment_id)) {
                $user_segments[] = $segment_id;
            }
        }

        return $user_segments;
    }

    public function getSegmentUsers($segment_id, $limit = null, $offset = 0) {
        if (!isset($this->segment_definitions[$segment_id])) {
            return [];
        }

        // For large user bases, this needs optimization
        // In production, pre-calculate segment memberships
        $users = $this->getAllUsers();

        $segment_users = [];
        foreach ($users as $user_id) {
            if ($this->evaluateUserSegment($user_id, $segment_id)) {
                $segment_users[] = $user_id;
                if ($limit && count($segment_users) >= $limit + $offset) {
                    break;
                }
            }
        }

        return array_slice($segment_users, $offset, $limit);
    }

    public function getSegmentSize($segment_id) {
        // Use cached count if available
        $cached_count = $this->segment_cache->getSegmentSize($segment_id);
        if ($cached_count !== null) {
            return $cached_count;
        }

        $users = $this->getSegmentUsers($segment_id);
        $count = count($users);

        // Cache the count
        $this->segment_cache->setSegmentSize($segment_id, $count);

        return $count;
    }

    public function analyzeSegmentOverlap($segment_ids) {
        $overlap_analysis = [];

        // Calculate pairwise overlaps
        for ($i = 0; $i < count($segment_ids); $i++) {
            for ($j = $i + 1; $j < count($segment_ids); $j++) {
                $segment_a = $segment_ids[$i];
                $segment_b = $segment_ids[$j];

                $overlap = $this->calculateSegmentOverlap($segment_a, $segment_b);
                $overlap_analysis["{$segment_a}_{$segment_b}"] = $overlap;
            }
        }

        return $overlap_analysis;
    }

    private function calculateSegmentOverlap($segment_a, $segment_b) {
        $users_a = $this->getSegmentUsers($segment_a);
        $users_b = $this->getSegmentUsers($segment_b);

        $intersection = array_intersect($users_a, $users_b);
        $union = array_unique(array_merge($users_a, $users_b));

        return [
            'overlap_count' => count($intersection),
            'overlap_percentage' => count($union) > 0 ? (count($intersection) / count($union)) * 100 : 0,
            'jaccard_similarity' => count($union) > 0 ? count($intersection) / count($union) : 0
        ];
    }

    public function getSegmentPerformance($segment_id, $time_range = 30) {
        // Analyze how well this segment performs for various metrics
        $segment_users = $this->getSegmentUsers($segment_id);

        if (empty($segment_users)) {
            return null;
        }

        $analytics = new AnalyticsEngine();

        // Calculate key performance indicators
        $consent_rate = $analytics->getAnalyticsData(
            "consent_rate segment:{$segment_id}",
            "-{$time_range}d"
        );

        $engagement_score = $analytics->getAnalyticsData(
            "avg_engagement segment:{$segment_id}",
            "-{$time_range}d"
        );

        $conversion_rate = $analytics->getAnalyticsData(
            "conversion_rate segment:{$segment_id}",
            "-{$time_range}d"
        );

        return [
            'segment_id' => $segment_id,
            'segment_size' => count($segment_users),
            'time_range_days' => $time_range,
            'metrics' => [
                'consent_rate' => $consent_rate,
                'engagement_score' => $engagement_score,
                'conversion_rate' => $conversion_rate
            ]
        ];
    }

    public function createDynamicSegment($name, $description, $base_segment, $modifiers) {
        // Create a dynamic segment based on another segment with modifications
        if (!isset($this->segment_definitions[$base_segment])) {
            throw new Exception('Base segment not found');
        }

        $base_rules = $this->segment_definitions[$base_segment]['rules'];
        $modified_rules = $this->applyModifiers($base_rules, $modifiers);

        return $this->createSegment($name, $description, $modified_rules);
    }

    private function applyModifiers($rules, $modifiers) {
        // Apply modifications to segment rules
        $modified_rules = $rules;

        foreach ($modifiers as $modifier) {
            switch ($modifier['type']) {
                case 'add_condition':
                    $modified_rules = $this->addConditionToRules($modified_rules, $modifier['condition']);
                    break;
                case 'remove_condition':
                    $modified_rules = $this->removeConditionFromRules($modified_rules, $modifier['condition_id']);
                    break;
                case 'modify_condition':
                    $modified_rules = $this->modifyConditionInRules($modified_rules, $modifier['condition_id'], $modifier['new_condition']);
                    break;
            }
        }

        return $modified_rules;
    }

    private function validateSegmentRules($rules) {
        // Basic validation of segment rules structure
        return is_array($rules) && $this->rule_engine->validateRules($rules);
    }

    private function getUserData($user_id) {
        // Get comprehensive user data for segmentation
        $user = get_userdata($user_id);

        if (!$user) {
            return null;
        }

        // Get additional user data from SLOS
        $consent_data = $this->getUserConsentData($user_id);
        $behavior_data = $this->getUserBehaviorData($user_id);
        $engagement_data = $this->getUserEngagementData($user_id);

        return [
            'user_id' => $user_id,
            'email' => $user->user_email,
            'registration_date' => $user->user_registered,
            'last_login' => get_user_meta($user_id, 'last_login', true),
            'role' => $user->roles[0] ?? 'subscriber',
            'consent_data' => $consent_data,
            'behavior_data' => $behavior_data,
            'engagement_data' => $engagement_data
        ];
    }

    private function getUserConsentData($user_id) {
        global $wpdb;

        return $wpdb->get_row($wpdb->prepare("
            SELECT * FROM wp_slos_user_consent
            WHERE user_id = %d
            ORDER BY created_at DESC
            LIMIT 1
        ", $user_id), ARRAY_A);
    }

    private function getUserBehaviorData($user_id) {
        $analytics = new AnalyticsEngine();

        return [
            'page_views' => $analytics->getAnalyticsData("page_views user:{$user_id}", "-30d"),
            'time_spent' => $analytics->getAnalyticsData("time_spent user:{$user_id}", "-30d"),
            'actions_taken' => $analytics->getAnalyticsData("actions user:{$user_id}", "-30d")
        ];
    }

    private function getUserEngagementData($user_id) {
        // Calculate engagement score based on various factors
        $behavior_data = $this->getUserBehaviorData($user_id);

        $engagement_score = 0;

        if ($behavior_data['page_views'] > 10) $engagement_score += 20;
        if ($behavior_data['time_spent'] > 300) $engagement_score += 30; // 5+ minutes
        if ($behavior_data['actions_taken'] > 5) $engagement_score += 25;

        // Cap at 100
        $engagement_score = min($engagement_score, 100);

        return [
            'engagement_score' => $engagement_score,
            'engagement_level' => $this->getEngagementLevel($engagement_score)
        ];
    }

    private function getEngagementLevel($score) {
        if ($score >= 80) return 'high';
        if ($score >= 50) return 'medium';
        return 'low';
    }

    private function getAllUsers() {
        // Get all user IDs (simplified - in production, use pagination)
        $users = get_users(['fields' => 'ID']);
        return $users;
    }

    // Helper methods for rule manipulation
    private function addConditionToRules($rules, $condition) {
        // Add condition to rules (simplified implementation)
        if (!isset($rules['conditions'])) {
            $rules['conditions'] = [];
        }
        $rules['conditions'][] = $condition;
        return $rules;
    }

    private function removeConditionFromRules($rules, $condition_id) {
        // Remove condition from rules (simplified implementation)
        if (isset($rules['conditions'])) {
            unset($rules['conditions'][$condition_id]);
        }
        return $rules;
    }

    private function modifyConditionInRules($rules, $condition_id, $new_condition) {
        // Modify condition in rules (simplified implementation)
        if (isset($rules['conditions'][$condition_id])) {
            $rules['conditions'][$condition_id] = $new_condition;
        }
        return $rules;
    }
}
```

### Predictive Analytics and Machine Learning

#### Step 1: Predictive Modeling Framework
```
SLOS → Advanced → Analytics → Predictive Analytics
```

**Predictive Analytics Architecture:**
```json
{
  "predictive_engine": {
    "models": [
      {
        "model": "consent_withdrawal_prediction",
        "description": "Predict users likely to withdraw consent",
        "algorithm": "gradient_boosting",
        "features": ["engagement_score", "consent_age", "interaction_frequency", "complaint_history"],
        "accuracy_target": "85%"
      },
      {
        "model": "compliance_risk_prediction",
        "description": "Predict compliance violation risk",
        "algorithm": "random_forest",
        "features": ["user_behavior", "consent_patterns", "geographic_location", "device_fingerprint"],
        "accuracy_target": "90%"
      },
      {
        "model": "churn_prediction",
        "description": "Predict user churn probability",
        "algorithm": "neural_network",
        "features": ["activity_trend", "consent_changes", "support_tickets", "feature_usage"],
        "accuracy_target": "82%"
      },
      {
        "model": "lifecycle_prediction",
        "description": "Predict user lifecycle stage transitions",
        "algorithm": "markov_chain",
        "features": ["current_stage", "time_in_stage", "engagement_metrics", "external_events"],
        "accuracy_target": "88%"
      }
    ],
    "training_pipeline": {
      "data_preparation": "automated",
      "feature_engineering": "domain_specific",
      "model_selection": "automated",
      "validation": "cross_validation",
      "deployment": "continuous"
    },
    "real_time_scoring": {
      "latency_target": "<100ms",
      "throughput_target": "1000_predictions_per_second",
      "caching_strategy": "intelligent",
      "fallback_mechanism": "rule_based"
    }
  },
  "predictive_capabilities": {
    "real_time_predictions": true,
    "batch_predictions": true,
    "model_explainability": true,
    "bias_detection": true,
    "performance_monitoring": true
  }
}
```

#### Step 2: Predictive Analytics Engine
```php
// Advanced predictive analytics and machine learning engine
class PredictiveAnalyticsEngine {
    private $models = [];
    private $feature_engine = null;
    private $model_trainer = null;
    private $prediction_cache = null;

    public function __construct() {
        $this->feature_engine = new FeatureEngineeringEngine();
        $this->model_trainer = new ModelTrainingEngine();
        $this->prediction_cache = new PredictionCache();
        $this->loadModels();
    }

    private function loadModels() {
        // Load trained models from storage
        $this->models = [
            'consent_withdrawal' => $this->loadModel('consent_withdrawal_prediction'),
            'compliance_risk' => $this->loadModel('compliance_risk_prediction'),
            'churn' => $this->loadModel('churn_prediction'),
            'lifecycle' => $this->loadModel('lifecycle_prediction')
        ];
    }

    private function loadModel($model_name) {
        // Load model from file or database
        $model_path = WP_CONTENT_DIR . "/slos-models/{$model_name}.model";

        if (file_exists($model_path)) {
            return unserialize(file_get_contents($model_path));
        }

        return null;
    }

    public function predictConsentWithdrawal($user_id, $context = []) {
        if (!$this->models['consent_withdrawal']) {
            return $this->fallbackConsentWithdrawalPrediction($user_id);
        }

        // Check cache first
        $cache_key = "consent_withdrawal_{$user_id}";
        $cached_prediction = $this->prediction_cache->get($cache_key);

        if ($cached_prediction) {
            return $cached_prediction;
        }

        // Extract features
        $features = $this->feature_engine->extractConsentWithdrawalFeatures($user_id, $context);

        // Make prediction
        $prediction = $this->models['consent_withdrawal']->predict([$features]);

        $result = [
            'user_id' => $user_id,
            'prediction' => $prediction[0],
            'probability' => $prediction[1] ?? null,
            'confidence' => $this->calculateConfidence($prediction),
            'timestamp' => time(),
            'features_used' => array_keys($features)
        ];

        // Cache prediction (short TTL for real-time accuracy)
        $this->prediction_cache->set($cache_key, $result, 1800); // 30 minutes

        return $result;
    }

    public function predictComplianceRisk($user_id, $context = []) {
        if (!$this->models['compliance_risk']) {
            return $this->fallbackComplianceRiskPrediction($user_id);
        }

        $cache_key = "compliance_risk_{$user_id}";
        $cached_prediction = $this->prediction_cache->get($cache_key);

        if ($cached_prediction) {
            return $cached_prediction;
        }

        $features = $this->feature_engine->extractComplianceRiskFeatures($user_id, $context);
        $prediction = $this->models['compliance_risk']->predict([$features]);

        $result = [
            'user_id' => $user_id,
            'risk_level' => $this->mapRiskScoreToLevel($prediction[0]),
            'risk_score' => $prediction[0],
            'probability' => $prediction[1] ?? null,
            'confidence' => $this->calculateConfidence($prediction),
            'timestamp' => time(),
            'features_used' => array_keys($features),
            'recommendations' => $this->generateRiskRecommendations($prediction[0])
        ];

        $this->prediction_cache->set($cache_key, $result, 3600); // 1 hour

        return $result;
    }

    public function predictChurn($user_id, $context = []) {
        if (!$this->models['churn']) {
            return $this->fallbackChurnPrediction($user_id);
        }

        $cache_key = "churn_{$user_id}";
        $cached_prediction = $this->prediction_cache->get($cache_key);

        if ($cached_prediction) {
            return $cached_prediction;
        }

        $features = $this->feature_engine->extractChurnFeatures($user_id, $context);
        $prediction = $this->models['churn']->predict([$features]);

        $result = [
            'user_id' => $user_id,
            'churn_probability' => $prediction[0],
            'churn_risk_level' => $this->mapChurnProbabilityToLevel($prediction[0]),
            'confidence' => $this->calculateConfidence($prediction),
            'timestamp' => time(),
            'features_used' => array_keys($features),
            'retention_recommendations' => $this->generateRetentionRecommendations($prediction[0])
        ];

        $this->prediction_cache->set($cache_key, $result, 7200); // 2 hours

        return $result;
    }

    public function predictLifecycleStage($user_id, $context = []) {
        if (!$this->models['lifecycle']) {
            return $this->fallbackLifecyclePrediction($user_id);
        }

        $cache_key = "lifecycle_{$user_id}";
        $cached_prediction = $this->prediction_cache->get($cache_key);

        if ($cached_prediction) {
            return $cached_prediction;
        }

        $features = $this->feature_engine->extractLifecycleFeatures($user_id, $context);
        $prediction = $this->models['lifecycle']->predict([$features]);

        $result = [
            'user_id' => $user_id,
            'current_stage' => $this->getCurrentLifecycleStage($user_id),
            'predicted_next_stage' => $prediction[0],
            'transition_probability' => $prediction[1] ?? null,
            'time_to_transition' => $prediction[2] ?? null,
            'confidence' => $this->calculateConfidence($prediction),
            'timestamp' => time(),
            'features_used' => array_keys($features)
        ];

        $this->prediction_cache->set($cache_key, $result, 86400); // 24 hours

        return $result;
    }

    public function trainModel($model_name, $training_data = null) {
        if (!$training_data) {
            $training_data = $this->prepareTrainingData($model_name);
        }

        $model = $this->model_trainer->train($model_name, $training_data);

        // Save trained model
        $this->saveModel($model_name, $model);

        // Update loaded models
        $this->models[$model_name] = $model;

        // Clear related caches
        $this->prediction_cache->clearPattern("{$model_name}_*");

        return [
            'model_name' => $model_name,
            'training_completed' => true,
            'accuracy_score' => $model->getAccuracy(),
            'training_time' => time()
        ];
    }

    private function prepareTrainingData($model_name) {
        switch ($model_name) {
            case 'consent_withdrawal':
                return $this->prepareConsentWithdrawalTrainingData();
            case 'compliance_risk':
                return $this->prepareComplianceRiskTrainingData();
            case 'churn':
                return $this->prepareChurnTrainingData();
            case 'lifecycle':
                return $this->prepareLifecycleTrainingData();
            default:
                throw new Exception("Unknown model: {$model_name}");
        }
    }

    private function prepareConsentWithdrawalTrainingData() {
        global $wpdb;

        // Get historical consent withdrawal data
        $data = $wpdb->get_results("
            SELECT
                u.ID as user_id,
                u.user_registered,
                COALESCE(uc.consent_given, 0) as consent_given,
                COALESCE(uc.consent_withdrawn, 0) as consent_withdrawn,
                COALESCE(a.page_views, 0) as page_views,
                COALESCE(a.time_spent, 0) as time_spent
            FROM wp_users u
            LEFT JOIN wp_slos_user_consent uc ON u.ID = uc.user_id
            LEFT JOIN wp_slos_user_analytics a ON u.ID = a.user_id
            WHERE u.user_registered < DATE_SUB(NOW(), INTERVAL 30 DAY)
        ");

        $training_data = [];
        foreach ($data as $row) {
            $features = $this->feature_engine->extractConsentWithdrawalFeatures($row->user_id);
            $label = $row->consent_withdrawn > 0 ? 1 : 0;

            $training_data[] = array_merge($features, ['label' => $label]);
        }

        return $training_data;
    }

    private function prepareComplianceRiskTrainingData() {
        // Similar implementation for compliance risk training data
        return [];
    }

    private function prepareChurnTrainingData() {
        // Similar implementation for churn training data
        return [];
    }

    private function prepareLifecycleTrainingData() {
        // Similar implementation for lifecycle training data
        return [];
    }

    private function saveModel($model_name, $model) {
        $model_path = WP_CONTENT_DIR . "/slos-models/{$model_name}.model";
        $model_dir = dirname($model_path);

        if (!is_dir($model_dir)) {
            mkdir($model_dir, 0755, true);
        }

        file_put_contents($model_path, serialize($model));
    }

    // Fallback prediction methods
    private function fallbackConsentWithdrawalPrediction($user_id) {
        // Rule-based fallback
        $user_data = $this->getUserData($user_id);
        $risk_score = 0;

        if ($user_data['days_since_last_activity'] > 90) $risk_score += 0.3;
        if ($user_data['complaint_count'] > 0) $risk_score += 0.2;
        if ($user_data['consent_changes'] > 2) $risk_score += 0.1;

        return [
            'user_id' => $user_id,
            'prediction' => $risk_score > 0.3 ? 1 : 0,
            'probability' => $risk_score,
            'confidence' => 'low',
            'method' => 'rule_based_fallback'
        ];
    }

    private function fallbackComplianceRiskPrediction($user_id) {
        return [
            'user_id' => $user_id,
            'risk_level' => 'medium',
            'risk_score' => 0.5,
            'confidence' => 'low',
            'method' => 'rule_based_fallback'
        ];
    }

    private function fallbackChurnPrediction($user_id) {
        return [
            'user_id' => $user_id,
            'churn_probability' => 0.2,
            'churn_risk_level' => 'low',
            'confidence' => 'low',
            'method' => 'rule_based_fallback'
        ];
    }

    private function fallbackLifecyclePrediction($user_id) {
        return [
            'user_id' => $user_id,
            'current_stage' => $this->getCurrentLifecycleStage($user_id),
            'predicted_next_stage' => 'engaged',
            'confidence' => 'low',
            'method' => 'rule_based_fallback'
        ];
    }

    // Helper methods
    private function calculateConfidence($prediction) {
        // Simplified confidence calculation
        if (is_array($prediction) && isset($prediction[1])) {
            $probability = $prediction[1];
            if ($probability > 0.8) return 'high';
            if ($probability > 0.6) return 'medium';
            return 'low';
        }
        return 'unknown';
    }

    private function mapRiskScoreToLevel($score) {
        if ($score > 0.7) return 'high';
        if ($score > 0.4) return 'medium';
        return 'low';
    }

    private function mapChurnProbabilityToLevel($probability) {
        if ($probability > 0.7) return 'high';
        if ($probability > 0.4) return 'medium';
        return 'low';
    }

    private function generateRiskRecommendations($risk_score) {
        $recommendations = [];

        if ($risk_score > 0.7) {
            $recommendations[] = 'Immediate compliance review required';
            $recommendations[] = 'Enhanced monitoring activated';
        } elseif ($risk_score > 0.4) {
            $recommendations[] = 'Regular compliance monitoring recommended';
            $recommendations[] = 'Additional consent verification suggested';
        }

        return $recommendations;
    }

    private function generateRetentionRecommendations($churn_probability) {
        $recommendations = [];

        if ($churn_probability > 0.7) {
            $recommendations[] = 'Urgent retention campaign needed';
            $recommendations[] = 'Personalized re-engagement email';
        } elseif ($churn_probability > 0.4) {
            $recommendations[] = 'Targeted retention offers';
            $recommendations[] = 'Survey to understand concerns';
        }

        return $recommendations;
    }

    private function getCurrentLifecycleStage($user_id) {
        // Determine current lifecycle stage based on user data
        $user_data = $this->getUserData($user_id);

        if ($user_data['days_since_registration'] < 7) return 'new';
        if ($user_data['engagement_score'] > 80) return 'champion';
        if ($user_data['engagement_score'] > 50) return 'engaged';
        if ($user_data['days_since_last_activity'] > 60) return 'at_risk';

        return 'regular';
    }

    private function getUserData($user_id) {
        // Get comprehensive user data for predictions
        global $wpdb;

        $user = get_userdata($user_id);
        if (!$user) return null;

        $analytics = $wpdb->get_row($wpdb->prepare("
            SELECT * FROM wp_slos_user_analytics
            WHERE user_id = %d
        ", $user_id), ARRAY_A);

        return [
            'user_id' => $user_id,
            'days_since_registration' => floor((time() - strtotime($user->user_registered)) / 86400),
            'days_since_last_activity' => isset($analytics['last_activity']) ?
                floor((time() - strtotime($analytics['last_activity'])) / 86400) : 999,
            'engagement_score' => $analytics['engagement_score'] ?? 0,
            'complaint_count' => $analytics['complaint_count'] ?? 0,
            'consent_changes' => $analytics['consent_changes'] ?? 0
        ];
    }
}
```

### Custom Reporting and Visualization

#### Step 1: Report Generation Engine
```
SLOS → Advanced → Analytics → Custom Reporting
```

**Reporting Architecture:**
```json
{
  "reporting_engine": {
    "report_types": [
      {
        "type": "compliance_reports",
        "description": "GDPR/CCPA compliance status reports",
        "frequency": "monthly",
        "audience": ["compliance_officers", "executives"],
        "format": ["PDF", "Excel", "HTML"]
      },
      {
        "type": "performance_reports",
        "description": "System performance and analytics reports",
        "frequency": "weekly",
        "audience": ["developers", "system_administrators"],
        "format": ["PDF", "JSON", "CSV"]
      },
      {
        "type": "user_engagement_reports",
        "description": "User behavior and engagement analytics",
        "frequency": "daily",
        "audience": ["marketing", "product_managers"],
        "format": ["PDF", "Excel", "Dashboard"]
      },
      {
        "type": "risk_assessment_reports",
        "description": "Compliance risk and predictive analytics",
        "frequency": "quarterly",
        "audience": ["risk_managers", "auditors"],
        "format": ["PDF", "Excel", "Interactive"]
      }
    ],
    "report_generation": {
      "engine": "template_based",
      "data_sources": ["database", "analytics_engine", "external_apis"],
      "scheduling": "automated",
      "distribution": "multi_channel"
    },
    "visualization_engine": {
      "library": "chart_js_d3",
      "interactivity": "full",
      "responsiveness": "mobile_first",
      "accessibility": "wcag_compliant"
    }
  },
  "reporting_capabilities": {
    "scheduled_reports": true,
    "ad_hoc_reports": true,
    "custom_dashboards": true,
    "data_export": true,
    "report_sharing": true
  }
}
```

#### Step 2: Custom Reporting Engine
```php
// Advanced custom reporting and visualization engine
class CustomReportingEngine {
    private $report_templates = [];
    private $data_sources = [];
    private $visualization_engine = null;
    private $scheduler = null;

    public function __construct() {
        $this->visualization_engine = new VisualizationEngine();
        $this->scheduler = new ReportScheduler();
        $this->loadReportTemplates();
        $this->initializeDataSources();
    }

    private function loadReportTemplates() {
        $this->report_templates = [
            'compliance_status' => [
                'name' => 'Compliance Status Report',
                'template' => 'compliance_status.twig',
                'data_queries' => [
                    'consent_stats' => 'compliance.consent_statistics',
                    'violation_log' => 'compliance.violation_log',
                    'audit_trail' => 'compliance.audit_trail'
                ],
                'charts' => ['consent_trends', 'violation_chart', 'compliance_score_gauge'],
                'schedule' => 'monthly'
            ],
            'user_engagement' => [
                'name' => 'User Engagement Report',
                'template' => 'user_engagement.twig',
                'data_queries' => [
                    'engagement_metrics' => 'analytics.user_engagement',
                    'behavior_patterns' => 'analytics.behavior_patterns',
                    'conversion_funnel' => 'analytics.conversion_funnel'
                ],
                'charts' => ['engagement_trends', 'behavior_heatmap', 'funnel_chart'],
                'schedule' => 'weekly'
            ],
            'performance_dashboard' => [
                'name' => 'Performance Dashboard',
                'template' => 'performance_dashboard.twig',
                'data_queries' => [
                    'system_metrics' => 'performance.system_metrics',
                    'response_times' => 'performance.response_times',
                    'error_rates' => 'performance.error_rates'
                ],
                'charts' => ['performance_trends', 'response_time_chart', 'error_rate_gauge'],
                'schedule' => 'daily'
            ],
            'risk_assessment' => [
                'name' => 'Risk Assessment Report',
                'template' => 'risk_assessment.twig',
                'data_queries' => [
                    'risk_scores' => 'predictive.risk_scores',
                    'compliance_predictions' => 'predictive.compliance_predictions',
                    'churn_analysis' => 'predictive.churn_analysis'
                ],
                'charts' => ['risk_heatmap', 'prediction_trends', 'churn_probability_chart'],
                'schedule' => 'quarterly'
            ]
        ];
    }

    private function initializeDataSources() {
        $this->data_sources = [
            'database' => new DatabaseDataSource(),
            'analytics' => new AnalyticsDataSource(),
            'predictive' => new PredictiveDataSource(),
            'performance' => new PerformanceDataSource(),
            'compliance' => new ComplianceDataSource()
        ];
    }

    public function generateReport($report_type, $parameters = [], $format = 'PDF') {
        if (!isset($this->report_templates[$report_type])) {
            throw new Exception("Report type '{$report_type}' not found");
        }

        $template = $this->report_templates[$report_type];

        // Gather data from multiple sources
        $report_data = $this->gatherReportData($template['data_queries'], $parameters);

        // Generate visualizations
        $charts = $this->generateReportCharts($template['charts'], $report_data);

        // Apply template
        $rendered_report = $this->renderReportTemplate($template['template'], [
            'data' => $report_data,
            'charts' => $charts,
            'parameters' => $parameters,
            'generated_at' => date('Y-m-d H:i:s')
        ]);

        // Convert to requested format
        return $this->convertReportFormat($rendered_report, $format);
    }

    private function gatherReportData($data_queries, $parameters) {
        $report_data = [];

        foreach ($data_queries as $key => $query_path) {
            list($source, $query) = explode('.', $query_path, 2);

            if (isset($this->data_sources[$source])) {
                $report_data[$key] = $this->data_sources[$source]->executeQuery($query, $parameters);
            }
        }

        return $report_data;
    }

    private function generateReportCharts($chart_definitions, $report_data) {
        $charts = [];

        foreach ($chart_definitions as $chart_key) {
            $chart_config = $this->getChartConfiguration($chart_key);
            $chart_data = $this->extractChartData($chart_config, $report_data);

            $charts[$chart_key] = $this->visualization_engine->generateChart(
                $chart_config['type'],
                $chart_data,
                $chart_config['options']
            );
        }

        return $charts;
    }

    private function getChartConfiguration($chart_key) {
        $chart_configs = [
            'consent_trends' => [
                'type' => 'line',
                'data_source' => 'consent_stats',
                'options' => [
                    'title' => 'Consent Trends Over Time',
                    'x_axis' => 'date',
                    'y_axis' => 'count',
                    'colors' => ['#4CAF50', '#F44336']
                ]
            ],
            'violation_chart' => [
                'type' => 'bar',
                'data_source' => 'violation_log',
                'options' => [
                    'title' => 'Compliance Violations by Type',
                    'x_axis' => 'violation_type',
                    'y_axis' => 'count',
                    'colors' => ['#FF9800']
                ]
            ],
            'engagement_trends' => [
                'type' => 'area',
                'data_source' => 'engagement_metrics',
                'options' => [
                    'title' => 'User Engagement Trends',
                    'x_axis' => 'date',
                    'y_axis' => 'engagement_score',
                    'colors' => ['#2196F3']
                ]
            ],
            'behavior_heatmap' => [
                'type' => 'heatmap',
                'data_source' => 'behavior_patterns',
                'options' => [
                    'title' => 'User Behavior Heatmap',
                    'x_axis' => 'hour_of_day',
                    'y_axis' => 'day_of_week',
                    'colors' => ['#E3F2FD', '#2196F3']
                ]
            ]
        ];

        return $chart_configs[$chart_key] ?? null;
    }

    private function extractChartData($chart_config, $report_data) {
        $data_source = $chart_config['data_source'];

        if (!isset($report_data[$data_source])) {
            return [];
        }

        $raw_data = $report_data[$data_source];

        // Transform data based on chart requirements
        return $this->transformDataForChart($raw_data, $chart_config);
    }

    private function transformDataForChart($raw_data, $chart_config) {
        // Transform raw data into chart-ready format
        $transformed_data = [];

        switch ($chart_config['type']) {
            case 'line':
            case 'area':
                $transformed_data = $this->transformTimeSeriesData($raw_data);
                break;
            case 'bar':
                $transformed_data = $this->transformCategoricalData($raw_data);
                break;
            case 'heatmap':
                $transformed_data = $this->transformHeatmapData($raw_data);
                break;
            case 'pie':
                $transformed_data = $this->transformPieData($raw_data);
                break;
        }

        return $transformed_data;
    }

    private function renderReportTemplate($template_name, $data) {
        // Use Twig or similar templating engine
        $template_path = plugin_dir_path(__FILE__) . "templates/reports/{$template_name}";

        if (!file_exists($template_path)) {
            throw new Exception("Report template '{$template_name}' not found");
        }

        // Simplified template rendering (in production, use Twig)
        $template_content = file_get_contents($template_path);

        // Basic variable replacement
        foreach ($data as $key => $value) {
            if (is_array($value) || is_object($value)) {
                $value = json_encode($value);
            }
            $template_content = str_replace("{{{$key}}}", $value, $template_content);
        }

        return $template_content;
    }

    private function convertReportFormat($content, $format) {
        switch ($format) {
            case 'PDF':
                return $this->convertToPDF($content);
            case 'Excel':
                return $this->convertToExcel($content);
            case 'HTML':
                return $content;
            case 'JSON':
                return json_encode(['content' => $content]);
            default:
                throw new Exception("Unsupported format: {$format}");
        }
    }

    public function scheduleReport($report_type, $schedule, $recipients, $parameters = []) {
        return $this->scheduler->scheduleReport([
            'report_type' => $report_type,
            'schedule' => $schedule,
            'recipients' => $recipients,
            'parameters' => $parameters,
            'format' => 'PDF'
        ]);
    }

    public function createCustomReport($name, $description, $data_queries, $charts, $template) {
        $report_id = 'custom_' . time();

        $this->report_templates[$report_id] = [
            'name' => $name,
            'description' => $description,
            'template' => $template,
            'data_queries' => $data_queries,
            'charts' => $charts,
            'custom' => true
        ];

        return $report_id;
    }

    public function exportReportData($report_type, $parameters = [], $format = 'CSV') {
        $template = $this->report_templates[$report_type];
        $report_data = $this->gatherReportData($template['data_queries'], $parameters);

        return $this->exportData($report_data, $format);
    }

    private function exportData($data, $format) {
        switch ($format) {
            case 'CSV':
                return $this->exportToCSV($data);
            case 'JSON':
                return json_encode($data);
            case 'XML':
                return $this->exportToXML($data);
            default:
                throw new Exception("Unsupported export format: {$format}");
        }
    }

    // Helper methods for data transformation
    private function transformTimeSeriesData($data) {
        $transformed = ['labels' => [], 'datasets' => []];

        foreach ($data as $point) {
            $transformed['labels'][] = $point['date'];
            // Add dataset values...
        }

        return $transformed;
    }

    private function transformCategoricalData($data) {
        $transformed = ['labels' => [], 'data' => []];

        foreach ($data as $point) {
            $transformed['labels'][] = $point['category'];
            $transformed['data'][] = $point['value'];
        }

        return $transformed;
    }

    private function transformHeatmapData($data) {
        // Transform for heatmap visualization
        return $data;
    }

    private function transformPieData($data) {
        $transformed = ['labels' => [], 'data' => []];

        foreach ($data as $point) {
            $transformed['labels'][] = $point['label'];
            $transformed['data'][] = $point['value'];
        }

        return $transformed;
    }

    // Format conversion methods
    private function convertToPDF($content) {
        // Use TCPDF or similar library
        require_once plugin_dir_path(__FILE__) . 'vendor/tcpdf/tcpdf.php';

        $pdf = new TCPDF();
        $pdf->AddPage();
        $pdf->writeHTML($content);
        return $pdf->Output('', 'S'); // Return as string
    }

    private function convertToExcel($content) {
        // Use PhpSpreadsheet or similar library
        require_once plugin_dir_path(__FILE__) . 'vendor/phpoffice/phpspreadsheet/src/Bootstrap.php';

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'Report Content');
        $sheet->setCellValue('A2', strip_tags($content));

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        ob_start();
        $writer->save('php://output');
        return ob_get_clean();
    }

    private function exportToCSV($data) {
        $output = fopen('php://temp', 'r+');

        // Write headers if data is array of arrays
        if (is_array($data) && isset($data[0]) && is_array($data[0])) {
            fputcsv($output, array_keys($data[0]));
        }

        // Write data
        foreach ($data as $row) {
            if (is_array($row)) {
                fputcsv($output, $row);
            }
        }

        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return $csv;
    }

    private function exportToXML($data) {
        $xml = new SimpleXMLElement('<report-data/>');

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $section = $xml->addChild($key);
                foreach ($value as $item) {
                    if (is_array($item)) {
                        $itemElement = $section->addChild('item');
                        foreach ($item as $itemKey => $itemValue) {
                            $itemElement->addChild($itemKey, htmlspecialchars($itemValue));
                        }
                    }
                }
            } else {
                $xml->addChild($key, htmlspecialchars($value));
            }
        }

        return $xml->asXML();
    }
}
```

### Support Resources

#### Documentation
- [Analytics API Reference](../Advanced/01-rest-api.md#analytics)
- [Data Privacy Guidelines](../Getting-Started/01-basic-compliance-setup.md)
- [Visualization Best Practices](../How-tos/08-understanding-consent-logs.md)

#### Help
- Data analytics specialists
- Machine learning engineers
- Business intelligence consultants
- Privacy compliance experts