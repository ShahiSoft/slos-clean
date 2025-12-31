# Custom Workflows

## Build Advanced Automation and Business Processes

### Workflow Engine Architecture

#### Step 1: Workflow Design Framework
```
SLOS → Advanced → Workflows → Design Framework
```

**Workflow Architecture:**
```json
{
  "workflow_engine": {
    "type": "event_driven",
    "execution_model": "asynchronous",
    "persistence_layer": "database",
    "scalability": "horizontal",
    "monitoring": "real_time"
  },
  "workflow_components": [
    {
      "component": "triggers",
      "types": ["event_based", "schedule_based", "api_based"],
      "configuration": "json_schema"
    },
    {
      "component": "conditions",
      "types": ["data_conditions", "time_conditions", "user_conditions"],
      "evaluation": "real_time"
    },
    {
      "component": "actions",
      "types": ["email_actions", "api_actions", "database_actions", "notification_actions"],
      "execution": "queued"
    },
    {
      "component": "flows",
      "structure": "directed_graph",
      "branching": "conditional",
      "loops": "supported"
    }
  ],
  "workflow_capabilities": {
    "max_concurrent_workflows": 1000,
    "max_steps_per_workflow": 50,
    "execution_timeout": 3600,
    "retry_mechanism": "exponential_backoff",
    "error_handling": "comprehensive"
  }
}
```

#### Step 2: Workflow Definition Schema
```json
{
  "$schema": "https://schemas.slos.com/workflow/v1.0",
  "workflow": {
    "id": "consent_compliance_monitoring",
    "name": "Consent Compliance Monitoring",
    "description": "Automated monitoring and enforcement of consent compliance",
    "version": "1.0",
    "active": true,
    "triggers": [
      {
        "type": "event",
        "event": "consent_given",
        "conditions": {
          "consent_categories": ["marketing"],
          "user_type": "eu_citizen"
        }
      },
      {
        "type": "schedule",
        "cron": "0 9 * * 1", // Every Monday at 9 AM
        "description": "Weekly compliance check"
      }
    ],
    "steps": [
      {
        "id": "validate_consent",
        "name": "Validate Consent Data",
        "type": "validation",
        "config": {
          "rules": [
            "check_required_categories",
            "verify_timestamp",
            "validate_user_consent"
          ]
        },
        "on_success": "send_notification",
        "on_failure": "log_violation"
      },
      {
        "id": "send_notification",
        "name": "Send Compliance Notification",
        "type": "email",
        "config": {
          "template": "compliance_notification",
          "recipients": ["compliance@company.com"],
          "priority": "high"
        }
      },
      {
        "id": "log_violation",
        "name": "Log Compliance Violation",
        "type": "database",
        "config": {
          "table": "compliance_violations",
          "fields": ["user_id", "violation_type", "timestamp"]
        },
        "on_success": "alert_compliance_team"
      },
      {
        "id": "alert_compliance_team",
        "name": "Alert Compliance Team",
        "type": "notification",
        "config": {
          "channels": ["email", "slack"],
          "urgency": "high",
          "escalation": "immediate"
        }
      }
    ],
    "error_handling": {
      "max_retries": 3,
      "retry_delay": "exponential",
      "fallback_action": "log_error",
      "notification_on_failure": true
    },
    "monitoring": {
      "metrics": ["execution_time", "success_rate", "error_rate"],
      "alerts": {
        "error_threshold": 5,
        "performance_threshold": 30
      }
    }
  }
}
```

#### Step 3: Workflow Execution Engine
```php
// Advanced workflow execution engine
class WorkflowEngine {
    private $workflows = [];
    private $active_executions = [];
    private $execution_queue = [];
    private $event_listeners = [];

    public function __construct() {
        $this->loadWorkflows();
        $this->setupEventListeners();
        $this->initializeExecutionQueue();
    }

    private function loadWorkflows() {
        global $wpdb;

        $workflows = $wpdb->get_results("
            SELECT * FROM wp_slos_workflows
            WHERE active = 1
        ");

        foreach ($workflows as $workflow) {
            $this->workflows[$workflow->id] = json_decode($workflow->definition, true);
        }
    }

    private function setupEventListeners() {
        // Register event listeners for workflow triggers
        $events = ['consent_given', 'user_registered', 'compliance_violation', 'data_export_requested'];

        foreach ($events as $event) {
            add_action("slos_{$event}", [$this, 'handleEventTrigger'], 10, 2);
        }
    }

    private function initializeExecutionQueue() {
        // Set up background processing for workflow execution
        add_action('slos_process_workflow_queue', [$this, 'processExecutionQueue']);

        if (!wp_next_scheduled('slos_process_workflow_queue')) {
            wp_schedule_event(time(), '30', 'slos_process_workflow_queue');
        }
    }

    public function handleEventTrigger($event_data, $event_type) {
        // Find workflows triggered by this event
        $triggered_workflows = $this->findTriggeredWorkflows($event_type, $event_data);

        foreach ($triggered_workflows as $workflow_id) {
            $this->startWorkflowExecution($workflow_id, $event_data);
        }
    }

    private function findTriggeredWorkflows($event_type, $event_data) {
        $triggered = [];

        foreach ($this->workflows as $workflow_id => $workflow) {
            if (isset($workflow['triggers'])) {
                foreach ($workflow['triggers'] as $trigger) {
                    if ($this->matchesTrigger($trigger, $event_type, $event_data)) {
                        $triggered[] = $workflow_id;
                        break;
                    }
                }
            }
        }

        return $triggered;
    }

    private function matchesTrigger($trigger, $event_type, $event_data) {
        // Check trigger type
        if ($trigger['type'] !== 'event' || $trigger['event'] !== $event_type) {
            return false;
        }

        // Check conditions if specified
        if (isset($trigger['conditions'])) {
            return $this->evaluateConditions($trigger['conditions'], $event_data);
        }

        return true;
    }

    private function evaluateConditions($conditions, $event_data) {
        foreach ($conditions as $field => $expected_value) {
            if (!isset($event_data[$field])) {
                return false;
            }

            $actual_value = $event_data[$field];

            if (is_array($expected_value)) {
                if (!in_array($actual_value, $expected_value)) {
                    return false;
                }
            } elseif ($actual_value !== $expected_value) {
                return false;
            }
        }

        return true;
    }

    public function startWorkflowExecution($workflow_id, $initial_data = []) {
        if (!isset($this->workflows[$workflow_id])) {
            return false;
        }

        $workflow = $this->workflows[$workflow_id];
        $execution_id = $this->generateExecutionId();

        $execution = [
            'id' => $execution_id,
            'workflow_id' => $workflow_id,
            'status' => 'running',
            'current_step' => $workflow['steps'][0]['id'] ?? null,
            'data' => $initial_data,
            'step_history' => [],
            'started_at' => time(),
            'last_updated' => time()
        ];

        $this->active_executions[$execution_id] = $execution;
        $this->execution_queue[] = $execution_id;

        // Persist execution state
        $this->persistExecutionState($execution);

        return $execution_id;
    }

    public function processExecutionQueue() {
        $max_executions_per_batch = 10;
        $processed = 0;

        while ($processed < $max_executions_per_batch && !empty($this->execution_queue)) {
            $execution_id = array_shift($this->execution_queue);

            if (isset($this->active_executions[$execution_id])) {
                $this->executeWorkflowStep($execution_id);
                $processed++;
            }
        }
    }

    private function executeWorkflowStep($execution_id) {
        $execution = $this->active_executions[$execution_id];
        $workflow = $this->workflows[$execution['workflow_id']];

        if (!$execution['current_step']) {
            $this->completeWorkflowExecution($execution_id);
            return;
        }

        $current_step = $this->findStepById($workflow, $execution['current_step']);

        if (!$current_step) {
            $this->failWorkflowExecution($execution_id, 'step_not_found');
            return;
        }

        try {
            $result = $this->executeStep($current_step, $execution['data']);

            // Record step execution
            $execution['step_history'][] = [
                'step_id' => $current_step['id'],
                'executed_at' => time(),
                'result' => $result
            ];

            // Determine next step
            $next_step = $this->determineNextStep($current_step, $result);

            if ($next_step) {
                $execution['current_step'] = $next_step;
                $execution['last_updated'] = time();
                $this->active_executions[$execution_id] = $execution;
                $this->execution_queue[] = $execution_id; // Re-queue for next step
            } else {
                $this->completeWorkflowExecution($execution_id);
            }

            $this->persistExecutionState($execution);

        } catch (Exception $e) {
            $this->handleStepExecutionError($execution_id, $current_step['id'], $e);
        }
    }

    private function executeStep($step, &$data) {
        switch ($step['type']) {
            case 'validation':
                return $this->executeValidationStep($step, $data);
            case 'email':
                return $this->executeEmailStep($step, $data);
            case 'database':
                return $this->executeDatabaseStep($step, $data);
            case 'api':
                return $this->executeAPIStep($step, $data);
            case 'notification':
                return $this->executeNotificationStep($step, $data);
            default:
                throw new Exception("Unknown step type: {$step['type']}");
        }
    }

    private function executeValidationStep($step, &$data) {
        $rules = $step['config']['rules'] ?? [];
        $results = [];

        foreach ($rules as $rule) {
            $results[$rule] = $this->validateRule($rule, $data);
        }

        $all_passed = !in_array(false, $results, true);

        return [
            'success' => $all_passed,
            'results' => $results,
            'data' => $data
        ];
    }

    private function executeEmailStep($step, &$data) {
        $config = $step['config'];

        $email_data = [
            'to' => $config['recipients'],
            'subject' => $this->processTemplate($config['subject'] ?? '', $data),
            'message' => $this->processTemplate($config['message'] ?? '', $data),
            'template' => $config['template'] ?? null
        ];

        return $this->sendWorkflowEmail($email_data);
    }

    private function executeDatabaseStep($step, &$data) {
        global $wpdb;

        $config = $step['config'];
        $table = $config['table'];
        $operation = $config['operation'] ?? 'insert';

        switch ($operation) {
            case 'insert':
                $result = $wpdb->insert($table, $config['data'] ?? $data);
                return ['success' => $result !== false, 'inserted_id' => $wpdb->insert_id];

            case 'update':
                $where = $config['where'] ?? [];
                $result = $wpdb->update($table, $config['data'] ?? $data, $where);
                return ['success' => $result !== false, 'affected_rows' => $result];

            case 'select':
                $query = $config['query'] ?? '';
                $results = $wpdb->get_results($this->processTemplate($query, $data));
                return ['success' => true, 'results' => $results];

            default:
                return ['success' => false, 'error' => 'Unknown operation'];
        }
    }

    private function executeAPIStep($step, &$data) {
        $config = $step['config'];

        $url = $this->processTemplate($config['url'], $data);
        $method = $config['method'] ?? 'GET';
        $headers = $config['headers'] ?? [];
        $body = $config['body'] ?? null;

        if ($body && is_array($body)) {
            $body = json_encode($body);
            $headers['Content-Type'] = 'application/json';
        }

        $response = wp_remote_request($url, [
            'method' => $method,
            'headers' => $headers,
            'body' => $body,
            'timeout' => $config['timeout'] ?? 30
        ]);

        if (is_wp_error($response)) {
            return ['success' => false, 'error' => $response->get_error_message()];
        }

        $response_code = wp_remote_retrieve_response_code($response);
        $response_body = wp_remote_retrieve_body($response);

        return [
            'success' => $response_code >= 200 && $response_code < 300,
            'response_code' => $response_code,
            'response_body' => $response_body
        ];
    }

    private function executeNotificationStep($step, &$data) {
        $config = $step['config'];
        $channels = $config['channels'] ?? ['email'];

        $notification_data = [
            'title' => $this->processTemplate($config['title'] ?? '', $data),
            'message' => $this->processTemplate($config['message'] ?? '', $data),
            'urgency' => $config['urgency'] ?? 'normal',
            'channels' => $channels
        ];

        return $this->sendWorkflowNotification($notification_data);
    }

    private function determineNextStep($current_step, $result) {
        if ($result['success']) {
            return $current_step['on_success'] ?? null;
        } else {
            return $current_step['on_failure'] ?? null;
        }
    }

    private function completeWorkflowExecution($execution_id) {
        $execution = $this->active_executions[$execution_id];
        $execution['status'] = 'completed';
        $execution['completed_at'] = time();

        $this->persistExecutionState($execution);
        unset($this->active_executions[$execution_id]);

        // Trigger completion event
        do_action('slos_workflow_completed', $execution);
    }

    private function failWorkflowExecution($execution_id, $reason) {
        $execution = $this->active_executions[$execution_id];
        $execution['status'] = 'failed';
        $execution['failure_reason'] = $reason;
        $execution['failed_at'] = time();

        $this->persistExecutionState($execution);
        unset($this->active_executions[$execution_id]);

        // Trigger failure event
        do_action('slos_workflow_failed', $execution);
    }

    private function handleStepExecutionError($execution_id, $step_id, $error) {
        $execution = $this->active_executions[$execution_id];

        // Check retry configuration
        $max_retries = $execution['workflow']['error_handling']['max_retries'] ?? 3;
        $retry_count = $execution['retry_count'] ?? 0;

        if ($retry_count < $max_retries) {
            // Schedule retry
            $execution['retry_count'] = $retry_count + 1;
            $delay = $this->calculateRetryDelay($retry_count);

            wp_schedule_single_event(time() + $delay, 'slos_retry_workflow_step', [
                'execution_id' => $execution_id,
                'step_id' => $step_id
            ]);
        } else {
            // Max retries exceeded, fail workflow
            $this->failWorkflowExecution($execution_id, 'max_retries_exceeded');
        }

        $this->persistExecutionState($execution);
    }

    private function calculateRetryDelay($retry_count) {
        // Exponential backoff: 30s, 2m, 8m, 32m, etc.
        return 30 * pow(4, $retry_count);
    }

    private function persistExecutionState($execution) {
        global $wpdb;

        $wpdb->replace('wp_slos_workflow_executions', [
            'execution_id' => $execution['id'],
            'workflow_id' => $execution['workflow_id'],
            'status' => $execution['status'],
            'current_step' => $execution['current_step'],
            'data' => json_encode($execution['data']),
            'step_history' => json_encode($execution['step_history']),
            'started_at' => date('Y-m-d H:i:s', $execution['started_at']),
            'last_updated' => date('Y-m-d H:i:s', $execution['last_updated'])
        ]);
    }

    // Helper methods
    private function generateExecutionId() {
        return 'wf_exec_' . time() . '_' . wp_generate_password(8, false);
    }

    private function findStepById($workflow, $step_id) {
        foreach ($workflow['steps'] as $step) {
            if ($step['id'] === $step_id) {
                return $step;
            }
        }
        return null;
    }

    private function validateRule($rule, $data) {
        // Implementation of validation rules
        switch ($rule) {
            case 'check_required_categories':
                return isset($data['consent_categories']) && is_array($data['consent_categories']);
            case 'verify_timestamp':
                return isset($data['timestamp']) && strtotime($data['timestamp']);
            case 'validate_user_consent':
                return isset($data['user_id']) && !empty($data['user_id']);
            default:
                return true;
        }
    }

    private function processTemplate($template, $data) {
        // Simple template processing with {{variable}} syntax
        foreach ($data as $key => $value) {
            $template = str_replace("{{{$key}}}", $value, $template);
        }
        return $template;
    }

    private function sendWorkflowEmail($email_data) {
        // Implementation of email sending
        return wp_mail($email_data['to'], $email_data['subject'], $email_data['message']);
    }

    private function sendWorkflowNotification($notification_data) {
        // Implementation of notifications (email, Slack, etc.)
        return true;
    }
}
```

### Advanced Workflow Patterns

#### Step 1: Complex Workflow Patterns
```
SLOS → Advanced → Workflows → Complex Patterns
```

**Advanced Workflow Patterns:**
```json
{
  "patterns": [
    {
      "pattern": "consent_lifecycle_management",
      "description": "Complete lifecycle management from consent to withdrawal",
      "complexity": "high",
      "components": ["conditional_branching", "scheduled_reviews", "escalation_paths"]
    },
    {
      "pattern": "compliance_monitoring_dashboard",
      "description": "Real-time compliance monitoring with automated alerts",
      "complexity": "medium",
      "components": ["real_time_data_processing", "threshold_alerts", "dashboard_integration"]
    },
    {
      "pattern": "data_subject_rights_automation",
      "description": "Automated processing of DSAR requests",
      "complexity": "high",
      "components": ["document_generation", "multi_party_approval", "audit_trailing"]
    },
    {
      "pattern": "international_compliance_coordination",
      "description": "Coordinated compliance across multiple jurisdictions",
      "complexity": "very_high",
      "components": ["geo_fencing", "jurisdiction_mapping", "cross_border_data_flows"]
    }
  ],
  "pattern_templates": {
    "consent_lifecycle": {
      "triggers": ["consent_given", "consent_updated", "consent_withdrawn"],
      "states": ["active", "expired", "withdrawn", "breached"],
      "transitions": {
        "active->expired": "schedule_review",
        "active->breached": "immediate_action",
        "expired->active": "renewal_process"
      }
    }
  }
}
```

#### Step 2: Workflow State Management
```php
// Advanced workflow state management
class WorkflowStateManager {
    private $state_machine = [];
    private $state_transitions = [];
    private $state_validators = [];

    public function defineWorkflowStates($workflow_id, $states, $transitions) {
        $this->state_machine[$workflow_id] = [
            'states' => $states,
            'transitions' => $transitions,
            'current_state' => $states[0] ?? 'initial'
        ];

        $this->state_transitions[$workflow_id] = $transitions;
    }

    public function transitionState($workflow_id, $new_state, $context = []) {
        $current_state = $this->getCurrentState($workflow_id);

        if (!$this->isValidTransition($workflow_id, $current_state, $new_state)) {
            throw new Exception("Invalid state transition from {$current_state} to {$new_state}");
        }

        if (!$this->validateStateTransition($workflow_id, $new_state, $context)) {
            throw new Exception("State transition validation failed");
        }

        // Execute transition actions
        $this->executeTransitionActions($workflow_id, $current_state, $new_state, $context);

        // Update state
        $this->state_machine[$workflow_id]['current_state'] = $new_state;

        // Persist state change
        $this->persistStateChange($workflow_id, $new_state, $context);

        // Trigger state change event
        do_action('slos_workflow_state_changed', $workflow_id, $current_state, $new_state, $context);
    }

    private function isValidTransition($workflow_id, $from_state, $to_state) {
        $transitions = $this->state_transitions[$workflow_id] ?? [];

        return isset($transitions[$from_state]) && in_array($to_state, $transitions[$from_state]);
    }

    private function validateStateTransition($workflow_id, $new_state, $context) {
        if (isset($this->state_validators[$workflow_id][$new_state])) {
            $validator = $this->state_validators[$workflow_id][$new_state];
            return call_user_func($validator, $context);
        }

        return true; // No validator means transition is always valid
    }

    private function executeTransitionActions($workflow_id, $from_state, $to_state, $context) {
        $transition_key = $from_state . '->' . $to_state;
        $actions = $this->getTransitionActions($workflow_id, $transition_key);

        foreach ($actions as $action) {
            $this->executeAction($action, $context);
        }
    }

    private function getTransitionActions($workflow_id, $transition_key) {
        // This would be loaded from workflow definition
        $transition_actions = [
            'active->expired' => ['send_expiry_notification', 'schedule_review'],
            'active->breached' => ['send_breach_alert', 'initiate_investigation'],
            'pending->approved' => ['send_approval_notification', 'update_records'],
            'pending->rejected' => ['send_rejection_notification', 'log_reason']
        ];

        return $transition_actions[$transition_key] ?? [];
    }

    private function executeAction($action, $context) {
        switch ($action) {
            case 'send_expiry_notification':
                $this->sendExpiryNotification($context);
                break;
            case 'send_breach_alert':
                $this->sendBreachAlert($context);
                break;
            case 'send_approval_notification':
                $this->sendApprovalNotification($context);
                break;
            // Add more actions as needed
        }
    }

    public function addStateValidator($workflow_id, $state, $validator_callback) {
        $this->state_validators[$workflow_id][$state] = $validator_callback;
    }

    public function getCurrentState($workflow_id) {
        return $this->state_machine[$workflow_id]['current_state'] ?? null;
    }

    public function getAvailableTransitions($workflow_id) {
        $current_state = $this->getCurrentState($workflow_id);
        return $this->state_transitions[$workflow_id][$current_state] ?? [];
    }

    public function getWorkflowHistory($workflow_id) {
        global $wpdb;

        return $wpdb->get_results($wpdb->prepare("
            SELECT * FROM wp_slos_workflow_state_history
            WHERE workflow_id = %s
            ORDER BY changed_at DESC
        ", $workflow_id));
    }

    private function persistStateChange($workflow_id, $new_state, $context) {
        global $wpdb;

        $wpdb->insert('wp_slos_workflow_state_history', [
            'workflow_id' => $workflow_id,
            'new_state' => $new_state,
            'context' => json_encode($context),
            'changed_at' => current_time('mysql'),
            'changed_by' => get_current_user_id()
        ]);
    }

    // Action implementations
    private function sendExpiryNotification($context) {
        $subject = 'Consent Expiry Notification';
        $message = "Your consent is expiring soon. Please review and renew if needed.";
        wp_mail($context['user_email'], $subject, $message);
    }

    private function sendBreachAlert($context) {
        $subject = 'URGENT: Consent Policy Breach Detected';
        $message = "A consent policy breach has been detected and requires immediate attention.";
        wp_mail('compliance@company.com', $subject, $message);
    }

    private function sendApprovalNotification($context) {
        $subject = 'Request Approved';
        $message = "Your request has been approved and processed.";
        wp_mail($context['user_email'], $subject, $message);
    }
}
```

### Workflow Analytics and Optimization

#### Step 1: Workflow Performance Analytics
```
SLOS → Advanced → Workflows → Analytics
```

**Workflow Analytics Dashboard:**
```json
{
  "analytics_metrics": [
    {
      "metric": "workflow_execution_time",
      "description": "Average time to complete workflows",
      "current_value": "45_minutes",
      "target_value": "30_minutes",
      "trend": "improving"
    },
    {
      "metric": "workflow_success_rate",
      "description": "Percentage of workflows completing successfully",
      "current_value": "94%",
      "target_value": "98%",
      "trend": "stable"
    },
    {
      "metric": "bottleneck_identification",
      "description": "Steps causing the most delays",
      "current_value": "consent_validation_step",
      "impact": "15_minute_delay",
      "recommendation": "optimize_validation_logic"
    },
    {
      "metric": "resource_utilization",
      "description": "Workflow engine resource usage",
      "current_value": "65%",
      "target_value": "<80%",
      "trend": "stable"
    }
  ],
  "optimization_opportunities": [
    {
      "opportunity": "parallel_processing",
      "description": "Execute independent steps in parallel",
      "potential_improvement": "40%",
      "complexity": "medium"
    },
    {
      "opportunity": "caching_optimization",
      "description": "Cache frequently accessed workflow data",
      "potential_improvement": "25%",
      "complexity": "low"
    },
    {
      "opportunity": "batch_processing",
      "description": "Process similar workflows in batches",
      "potential_improvement": "30%",
      "complexity": "high"
    }
  ]
}
```

#### Step 2: Workflow Optimization Engine
```php
// Workflow optimization and analytics engine
class WorkflowOptimizationEngine {
    private $performance_metrics = [];
    private $bottleneck_analysis = [];

    public function analyzeWorkflowPerformance() {
        global $wpdb;

        // Analyze execution times
        $execution_times = $wpdb->get_results("
            SELECT
                workflow_id,
                AVG(TIMESTAMPDIFF(MINUTE, started_at, completed_at)) as avg_execution_time,
                COUNT(*) as execution_count,
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) / COUNT(*) * 100 as success_rate
            FROM wp_slos_workflow_executions
            WHERE started_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            GROUP BY workflow_id
        ");

        // Analyze step performance
        $step_performance = $wpdb->get_results("
            SELECT
                w.workflow_id,
                s.step_id,
                s.name as step_name,
                AVG(s.execution_time) as avg_step_time,
                COUNT(*) as execution_count,
                SUM(CASE WHEN s.result = 'success' THEN 1 ELSE 0 END) / COUNT(*) * 100 as success_rate
            FROM wp_slos_workflow_executions w
            CROSS JOIN JSON_TABLE(w.step_history, '$[*]' COLUMNS (
                step_id VARCHAR(100) PATH '$.step_id',
                execution_time INT PATH '$.execution_time',
                result VARCHAR(20) PATH '$.result'
            )) s
            WHERE w.started_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            GROUP BY w.workflow_id, s.step_id, s.step_name
            ORDER BY avg_step_time DESC
        ");

        return [
            'execution_times' => $execution_times,
            'step_performance' => $step_performance,
            'bottlenecks' => $this->identifyBottlenecks($step_performance)
        ];
    }

    private function identifyBottlenecks($step_performance) {
        $bottlenecks = [];

        foreach ($step_performance as $step) {
            if ($step->avg_step_time > 300) { // Over 5 minutes
                $bottlenecks[] = [
                    'workflow_id' => $step->workflow_id,
                    'step_id' => $step->step_id,
                    'step_name' => $step->step_name,
                    'avg_time' => $step->avg_step_time,
                    'severity' => 'high',
                    'recommendation' => $this->getOptimizationRecommendation($step)
                ];
            } elseif ($step->avg_step_time > 120) { // Over 2 minutes
                $bottlenecks[] = [
                    'workflow_id' => $step->workflow_id,
                    'step_id' => $step->step_id,
                    'step_name' => $step->step_name,
                    'avg_time' => $step->avg_step_time,
                    'severity' => 'medium',
                    'recommendation' => $this->getOptimizationRecommendation($step)
                ];
            }
        }

        return $bottlenecks;
    }

    private function getOptimizationRecommendation($step) {
        // Analyze step characteristics to provide recommendations
        if (strpos($step->step_name, 'validation') !== false) {
            return 'Implement parallel validation or caching of validation results';
        } elseif (strpos($step->step_name, 'email') !== false) {
            return 'Use bulk email sending or queue emails for background processing';
        } elseif (strpos($step->step_name, 'database') !== false) {
            return 'Optimize database queries or implement query result caching';
        } elseif (strpos($step->step_name, 'api') !== false) {
            return 'Implement API response caching or use asynchronous processing';
        } else {
            return 'Review step logic for optimization opportunities';
        }
    }

    public function optimizeWorkflow($workflow_id) {
        $analysis = $this->analyzeWorkflowPerformance();
        $workflow_bottlenecks = array_filter($analysis['bottlenecks'], function($b) use ($workflow_id) {
            return $b['workflow_id'] === $workflow_id;
        });

        $optimizations = [];

        foreach ($workflow_bottlenecks as $bottleneck) {
            $optimization = $this->generateOptimization($bottleneck);
            if ($optimization) {
                $optimizations[] = $optimization;
            }
        }

        // Apply optimizations
        foreach ($optimizations as $optimization) {
            $this->applyOptimization($workflow_id, $optimization);
        }

        return $optimizations;
    }

    private function generateOptimization($bottleneck) {
        $optimization = [
            'step_id' => $bottleneck['step_id'],
            'type' => 'unknown',
            'description' => '',
            'expected_improvement' => 0
        ];

        switch ($bottleneck['recommendation']) {
            case 'parallel_validation':
                $optimization['type'] = 'parallel_processing';
                $optimization['description'] = 'Execute validation steps in parallel';
                $optimization['expected_improvement'] = 40;
                break;

            case 'bulk_email':
                $optimization['type'] = 'bulk_processing';
                $optimization['description'] = 'Send emails in bulk to reduce processing time';
                $optimization['expected_improvement'] = 60;
                break;

            case 'query_caching':
                $optimization['type'] = 'caching';
                $optimization['description'] = 'Cache database query results';
                $optimization['expected_improvement'] = 70;
                break;

            case 'async_processing':
                $optimization['type'] = 'async_processing';
                $optimization['description'] = 'Process step asynchronously';
                $optimization['expected_improvement'] = 50;
                break;
        }

        return $optimization;
    }

    private function applyOptimization($workflow_id, $optimization) {
        global $wpdb;

        // Update workflow definition with optimization
        $workflow = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM wp_slos_workflows WHERE id = %s",
            $workflow_id
        ));

        if (!$workflow) return;

        $definition = json_decode($workflow->definition, true);

        // Apply optimization to specific step
        foreach ($definition['steps'] as &$step) {
            if ($step['id'] === $optimization['step_id']) {
                $step['optimizations'] = $step['optimizations'] ?? [];
                $step['optimizations'][] = $optimization;
                break;
            }
        }

        // Save updated definition
        $wpdb->update(
            'wp_slos_workflows',
            ['definition' => json_encode($definition)],
            ['id' => $workflow_id]
        );

        // Log optimization
        $this->logOptimization($workflow_id, $optimization);
    }

    private function logOptimization($workflow_id, $optimization) {
        global $wpdb;

        $wpdb->insert('wp_slos_workflow_optimizations', [
            'workflow_id' => $workflow_id,
            'step_id' => $optimization['step_id'],
            'optimization_type' => $optimization['type'],
            'description' => $optimization['description'],
            'expected_improvement' => $optimization['expected_improvement'],
            'applied_at' => current_time('mysql')
        ]);
    }

    public function getOptimizationHistory($workflow_id = null) {
        global $wpdb;

        $query = "SELECT * FROM wp_slos_workflow_optimizations";
        $params = [];

        if ($workflow_id) {
            $query .= " WHERE workflow_id = %s";
            $params[] = $workflow_id;
        }

        $query .= " ORDER BY applied_at DESC";

        return $wpdb->get_results($wpdb->prepare($query, $params));
    }

    public function measureOptimizationImpact($workflow_id, $optimization_id) {
        // Compare performance before and after optimization
        global $wpdb;

        $optimization = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM wp_slos_workflow_optimizations WHERE id = %s",
            $optimization_id
        ));

        if (!$optimization) return null;

        // Get performance data before optimization
        $before_date = date('Y-m-d H:i:s', strtotime($optimization->applied_at) - 86400); // 1 day before
        $after_date = date('Y-m-d H:i:s', strtotime($optimization->applied_at) + 86400);  // 1 day after

        $before_performance = $wpdb->get_row($wpdb->prepare("
            SELECT AVG(TIMESTAMPDIFF(MINUTE, started_at, completed_at)) as avg_time
            FROM wp_slos_workflow_executions
            WHERE workflow_id = %s AND started_at < %s
            ORDER BY started_at DESC LIMIT 10
        ", $workflow_id, $optimization->applied_at));

        $after_performance = $wpdb->get_row($wpdb->prepare("
            SELECT AVG(TIMESTAMPDIFF(MINUTE, started_at, completed_at)) as avg_time
            FROM wp_slos_workflow_executions
            WHERE workflow_id = %s AND started_at >= %s
            ORDER BY started_at ASC LIMIT 10
        ", $workflow_id, $optimization->applied_at));

        if ($before_performance && $after_performance) {
            $improvement = (($before_performance->avg_time - $after_performance->avg_time) / $before_performance->avg_time) * 100;

            return [
                'optimization_id' => $optimization_id,
                'before_avg_time' => $before_performance->avg_time,
                'after_avg_time' => $after_performance->avg_time,
                'actual_improvement' => round($improvement, 2),
                'expected_improvement' => $optimization->expected_improvement,
                'success' => $improvement > 0
            ];
        }

        return null;
    }
}
```

### Workflow Integration and APIs

#### Step 1: External System Integration
```
SLOS → Advanced → Workflows → External Integration
```

**Workflow Integration Architecture:**
```json
{
  "integration_types": [
    {
      "type": "webhook_integration",
      "description": "Receive real-time updates from external systems",
      "supported_systems": ["CRM", "ERP", "Marketing_Automation"],
      "authentication": ["API_Key", "OAuth2", "Basic_Auth"]
    },
    {
      "type": "api_integration",
      "description": "Bidirectional API communication",
      "protocols": ["REST", "GraphQL", "SOAP"],
      "data_formats": ["JSON", "XML", "Form_Data"]
    },
    {
      "type": "database_integration",
      "description": "Direct database synchronization",
      "supported_databases": ["MySQL", "PostgreSQL", "SQL_Server"],
      "sync_modes": ["real_time", "batch", "on_demand"]
    },
    {
      "type": "file_integration",
      "description": "File-based data exchange",
      "formats": ["CSV", "XML", "JSON"],
      "transfer_methods": ["SFTP", "API_Upload", "Email_Attachment"]
    }
  ],
  "integration_patterns": {
    "event_driven": "React to external events",
    "polling_based": "Regularly check for updates",
    "batch_processing": "Process data in batches",
    "real_time_sync": "Immediate synchronization"
  }
}
```

#### Step 2: Workflow API Development
```php
// Workflow API for external integrations
class WorkflowAPI {
    private $api_version = 'v1';
    private $authentication_required = true;

    public function __construct() {
        add_action('rest_api_init', [$this, 'register_api_routes']);
    }

    public function register_api_routes() {
        register_rest_route("slos/{$this->api_version}", '/workflows', [
            'methods' => 'GET',
            'callback' => [$this, 'get_workflows'],
            'permission_callback' => [$this, 'check_permissions']
        ]);

        register_rest_route("slos/{$this->api_version}", '/workflows/(?P<id>[a-zA-Z0-9-_]+)', [
            'methods' => 'GET',
            'callback' => [$this, 'get_workflow'],
            'permission_callback' => [$this, 'check_permissions']
        ]);

        register_rest_route("slos/{$this->api_version}", '/workflows/(?P<id>[a-zA-Z0-9-_]+)/execute', [
            'methods' => 'POST',
            'callback' => [$this, 'execute_workflow'],
            'permission_callback' => [$this, 'check_permissions']
        ]);

        register_rest_route("slos/{$this->api_version}", '/workflows/executions', [
            'methods' => 'GET',
            'callback' => [$this, 'get_workflow_executions'],
            'permission_callback' => [$this, 'check_permissions']
        ]);

        register_rest_route("slos/{$this->api_version}", '/workflows/executions/(?P<id>[a-zA-Z0-9-_]+)', [
            'methods' => 'GET',
            'callback' => [$this, 'get_workflow_execution'],
            'permission_callback' => [$this, 'check_permissions']
        ]);

        register_rest_route("slos/{$this->api_version}", '/workflows/trigger', [
            'methods' => 'POST',
            'callback' => [$this, 'trigger_workflow_event'],
            'permission_callback' => [$this, 'check_permissions']
        ]);
    }

    public function check_permissions($request) {
        if (!$this->authentication_required) {
            return true;
        }

        // Check API key authentication
        $api_key = $request->get_header('X-API-Key');
        if (!$api_key) {
            return new WP_Error('missing_api_key', 'API key is required', ['status' => 401]);
        }

        return $this->validate_api_key($api_key);
    }

    private function validate_api_key($api_key) {
        global $wpdb;

        $valid_key = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM wp_slos_api_keys WHERE api_key = %s AND active = 1",
            $api_key
        ));

        return $valid_key > 0;
    }

    public function get_workflows($request) {
        global $wpdb;

        $workflows = $wpdb->get_results("
            SELECT id, name, description, version, active, created_at, updated_at
            FROM wp_slos_workflows
            ORDER BY name
        ");

        return new WP_REST_Response($workflows, 200);
    }

    public function get_workflow($request) {
        $workflow_id = $request->get_param('id');

        global $wpdb;

        $workflow = $wpdb->get_row($wpdb->prepare("
            SELECT id, name, description, version, active, created_at, updated_at
            FROM wp_slos_workflows
            WHERE id = %s
        ", $workflow_id));

        if (!$workflow) {
            return new WP_Error('workflow_not_found', 'Workflow not found', ['status' => 404]);
        }

        return new WP_REST_Response($workflow, 200);
    }

    public function execute_workflow($request) {
        $workflow_id = $request->get_param('id');
        $parameters = $request->get_json_params();

        $workflow_engine = new WorkflowEngine();
        $execution_id = $workflow_engine->startWorkflowExecution($workflow_id, $parameters);

        if (!$execution_id) {
            return new WP_Error('execution_failed', 'Failed to start workflow execution', ['status' => 500]);
        }

        return new WP_REST_Response([
            'execution_id' => $execution_id,
            'status' => 'started',
            'message' => 'Workflow execution started successfully'
        ], 201);
    }

    public function get_workflow_executions($request) {
        global $wpdb;

        $page = $request->get_param('page') ?: 1;
        $per_page = $request->get_param('per_page') ?: 20;
        $offset = ($page - 1) * $per_page;

        $executions = $wpdb->get_results($wpdb->prepare("
            SELECT execution_id, workflow_id, status, current_step, started_at, last_updated
            FROM wp_slos_workflow_executions
            ORDER BY started_at DESC
            LIMIT %d OFFSET %d
        ", $per_page, $offset));

        $total = $wpdb->get_var("SELECT COUNT(*) FROM wp_slos_workflow_executions");

        return new WP_REST_Response([
            'executions' => $executions,
            'pagination' => [
                'page' => $page,
                'per_page' => $per_page,
                'total' => $total,
                'total_pages' => ceil($total / $per_page)
            ]
        ], 200);
    }

    public function get_workflow_execution($request) {
        $execution_id = $request->get_param('id');

        global $wpdb;

        $execution = $wpdb->get_row($wpdb->prepare("
            SELECT * FROM wp_slos_workflow_executions WHERE execution_id = %s
        ", $execution_id));

        if (!$execution) {
            return new WP_Error('execution_not_found', 'Workflow execution not found', ['status' => 404]);
        }

        // Parse JSON fields
        $execution->data = json_decode($execution->data, true);
        $execution->step_history = json_decode($execution->step_history, true);

        return new WP_REST_Response($execution, 200);
    }

    public function trigger_workflow_event($request) {
        $event_data = $request->get_json_params();

        if (!isset($event_data['event_type'])) {
            return new WP_Error('missing_event_type', 'Event type is required', ['status' => 400]);
        }

        // Trigger the event
        do_action('slos_workflow_trigger_' . $event_data['event_type'], $event_data);

        return new WP_REST_Response([
            'message' => 'Workflow event triggered successfully',
            'event_type' => $event_data['event_type']
        ], 200);
    }

    // Webhook handling for external integrations
    public function handle_webhook($request) {
        $webhook_data = $request->get_json_params();
        $webhook_source = $request->get_header('X-Webhook-Source');

        // Validate webhook source
        if (!$this->validate_webhook_source($webhook_source)) {
            return new WP_Error('invalid_webhook_source', 'Invalid webhook source', ['status' => 403]);
        }

        // Process webhook based on source
        switch ($webhook_source) {
            case 'crm_system':
                $this->process_crm_webhook($webhook_data);
                break;
            case 'marketing_automation':
                $this->process_marketing_webhook($webhook_data);
                break;
            default:
                return new WP_Error('unsupported_webhook_source', 'Unsupported webhook source', ['status' => 400]);
        }

        return new WP_REST_Response(['message' => 'Webhook processed successfully'], 200);
    }

    private function validate_webhook_source($source) {
        $valid_sources = ['crm_system', 'marketing_automation', 'erp_system'];
        return in_array($source, $valid_sources);
    }

    private function process_crm_webhook($data) {
        // Process CRM-specific webhook data
        if (isset($data['customer_consent_updated'])) {
            do_action('slos_consent_updated_from_crm', $data);
        }
    }

    private function process_marketing_webhook($data) {
        // Process marketing automation webhook data
        if (isset($data['campaign_consent_changed'])) {
            do_action('slos_campaign_consent_changed', $data);
        }
    }
}
```

### Support Resources

#### Documentation
- [Workflow API Reference](../Advanced/01-rest-api.md#workflows)
- [Integration Examples](../How-tos/13-multisite-setup.md)
- [State Management Guide](../Advanced/02-hooks-filters.md)

#### Help
- Workflow automation specialists
- Integration architects
- API development consultants
- Business process analysts