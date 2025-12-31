# Custom Reporting

## Build Custom Reports and Dashboards

### Report Builder Framework

#### Step 1: Report Builder Architecture
```
SLOS → Advanced → Reporting → Report Builder
```

**Report Builder Architecture:**
```json
{
  "report_builder": {
    "components": {
      "data_source_manager": {
        "description": "Manages connections to various data sources",
        "supported_sources": ["database", "api", "files", "analytics"],
        "connection_types": ["direct", "cached", "streamed"]
      },
      "query_builder": {
        "description": "Visual query construction interface",
        "query_types": ["SQL", "no_code", "template_based"],
        "validation": "real_time",
        "optimization": "automatic"
      },
      "visualization_engine": {
        "description": "Chart and graph generation",
        "chart_types": ["bar", "line", "pie", "scatter", "heatmap", "gauge"],
        "interactivity": "full",
        "responsiveness": "adaptive"
      },
      "layout_engine": {
        "description": "Report layout and formatting",
        "templates": "customizable",
        "responsive_design": true,
        "branding_support": true
      },
      "export_engine": {
        "description": "Multi-format export capabilities",
        "formats": ["PDF", "Excel", "CSV", "JSON", "HTML"],
        "scheduling": "automated",
        "distribution": "multi_channel"
      }
    },
    "capabilities": {
      "drag_drop_interface": true,
      "real_time_preview": true,
      "collaborative_editing": true,
      "version_control": true,
      "access_control": "granular"
    }
  },
  "report_types": {
    "operational_reports": "Daily operational metrics and KPIs",
    "compliance_reports": "Regulatory compliance and audit reports",
    "analytical_reports": "Trend analysis and predictive insights",
    "executive_dashboards": "High-level business intelligence",
    "custom_reports": "User-defined reports and visualizations"
  }
}
```

#### Step 2: Report Builder Implementation
```php
// Advanced report builder with drag-and-drop interface
class ReportBuilder {
    private $data_sources = [];
    private $visualization_components = [];
    private $layout_templates = [];
    private $user_reports = [];

    public function __construct() {
        $this->initializeDataSources();
        $this->loadVisualizationComponents();
        $this->loadLayoutTemplates();
    }

    private function initializeDataSources() {
        $this->data_sources = [
            'consent_data' => [
                'type' => 'database',
                'table' => 'wp_slos_user_consent',
                'fields' => [
                    'user_id' => 'User ID',
                    'consent_given' => 'Consent Given',
                    'consent_withdrawn' => 'Consent Withdrawn',
                    'consent_categories' => 'Categories',
                    'timestamp' => 'Timestamp'
                ],
                'filters' => ['date_range', 'categories', 'user_type']
            ],
            'analytics_data' => [
                'type' => 'analytics_api',
                'endpoint' => '/api/v1/analytics',
                'metrics' => [
                    'page_views' => 'Page Views',
                    'session_duration' => 'Session Duration',
                    'bounce_rate' => 'Bounce Rate',
                    'conversion_rate' => 'Conversion Rate'
                ],
                'dimensions' => ['date', 'device_type', 'location', 'referrer']
            ],
            'compliance_data' => [
                'type' => 'database',
                'table' => 'wp_slos_compliance_log',
                'fields' => [
                    'violation_type' => 'Violation Type',
                    'severity' => 'Severity',
                    'user_id' => 'User ID',
                    'timestamp' => 'Timestamp',
                    'resolution_status' => 'Resolution Status'
                ],
                'filters' => ['severity', 'date_range', 'violation_type']
            ],
            'performance_data' => [
                'type' => 'metrics_api',
                'endpoint' => '/api/v1/metrics',
                'metrics' => [
                    'response_time' => 'Response Time',
                    'error_rate' => 'Error Rate',
                    'throughput' => 'Throughput',
                    'uptime' => 'Uptime'
                ],
                'dimensions' => ['endpoint', 'time_range', 'server']
            ]
        ];
    }

    private function loadVisualizationComponents() {
        $this->visualization_components = [
            'bar_chart' => [
                'name' => 'Bar Chart',
                'category' => 'comparison',
                'dimensions' => ['x_axis', 'y_axis'],
                'options' => ['orientation', 'colors', 'stacked'],
                'data_requirements' => ['categorical', 'numerical']
            ],
            'line_chart' => [
                'name' => 'Line Chart',
                'category' => 'trend',
                'dimensions' => ['x_axis', 'y_axis'],
                'options' => ['smoothing', 'markers', 'area_fill'],
                'data_requirements' => ['time_series', 'numerical']
            ],
            'pie_chart' => [
                'name' => 'Pie Chart',
                'category' => 'composition',
                'dimensions' => ['categories', 'values'],
                'options' => ['donut_style', 'legend_position', 'colors'],
                'data_requirements' => ['categorical', 'numerical']
            ],
            'scatter_plot' => [
                'name' => 'Scatter Plot',
                'category' => 'correlation',
                'dimensions' => ['x_axis', 'y_axis', 'bubble_size'],
                'options' => ['trend_line', 'quadrants', 'colors'],
                'data_requirements' => ['numerical', 'numerical']
            ],
            'heatmap' => [
                'name' => 'Heatmap',
                'category' => 'density',
                'dimensions' => ['x_axis', 'y_axis', 'intensity'],
                'options' => ['color_scale', 'clustering', 'annotations'],
                'data_requirements' => ['categorical', 'categorical', 'numerical']
            ],
            'gauge_chart' => [
                'name' => 'Gauge Chart',
                'category' => 'kpi',
                'dimensions' => ['value', 'target', 'thresholds'],
                'options' => ['ranges', 'colors', 'labels'],
                'data_requirements' => ['numerical', 'numerical']
            ],
            'table' => [
                'name' => 'Data Table',
                'category' => 'detail',
                'dimensions' => ['columns', 'rows'],
                'options' => ['sorting', 'filtering', 'pagination', 'export'],
                'data_requirements' => ['any']
            ],
            'metric_card' => [
                'name' => 'Metric Card',
                'category' => 'kpi',
                'dimensions' => ['value', 'label', 'change'],
                'options' => ['icon', 'color', 'formatting'],
                'data_requirements' => ['numerical', 'text']
            ]
        ];
    }

    private function loadLayoutTemplates() {
        $this->layout_templates = [
            'single_page' => [
                'name' => 'Single Page Report',
                'layout' => 'full_width',
                'sections' => ['header', 'content', 'footer'],
                'max_components' => 10
            ],
            'dashboard' => [
                'name' => 'Executive Dashboard',
                'layout' => 'grid',
                'sections' => ['kpi_row', 'charts_grid', 'details_table'],
                'max_components' => 20
            ],
            'detailed_report' => [
                'name' => 'Detailed Analytical Report',
                'layout' => 'multi_page',
                'sections' => ['executive_summary', 'detailed_analysis', 'recommendations', 'appendix'],
                'max_components' => 50
            ],
            'mobile_report' => [
                'name' => 'Mobile-Optimized Report',
                'layout' => 'responsive',
                'sections' => ['compact_header', 'stacked_content'],
                'max_components' => 8
            ]
        ];
    }

    public function createReport($report_config) {
        $report_id = $this->generateReportId();

        // Validate report configuration
        $this->validateReportConfig($report_config);

        // Create report structure
        $report = [
            'id' => $report_id,
            'name' => $report_config['name'],
            'description' => $report_config['description'],
            'template' => $report_config['template'],
            'data_sources' => $report_config['data_sources'],
            'components' => $report_config['components'],
            'layout' => $report_config['layout'],
            'filters' => $report_config['filters'] ?? [],
            'permissions' => $report_config['permissions'] ?? [],
            'created_by' => get_current_user_id(),
            'created_at' => time(),
            'version' => 1
        ];

        // Save report
        $this->saveReport($report);

        return $report_id;
    }

    public function updateReport($report_id, $updates) {
        if (!isset($this->user_reports[$report_id])) {
            $this->user_reports[$report_id] = $this->loadReport($report_id);
        }

        $report = $this->user_reports[$report_id];

        // Apply updates
        foreach ($updates as $key => $value) {
            if ($key === 'components') {
                $this->validateComponents($value);
            }
            $report[$key] = $value;
        }

        $report['updated_at'] = time();
        $report['version']++;

        // Save updated report
        $this->saveReport($report);

        return $report;
    }

    public function renderReport($report_id, $parameters = []) {
        $report = $this->loadReport($report_id);

        // Apply parameters and filters
        $filtered_params = $this->applyFilters($report, $parameters);

        // Fetch data for all components
        $component_data = $this->fetchComponentData($report['components'], $filtered_params);

        // Generate visualizations
        $visualizations = $this->generateVisualizations($report['components'], $component_data);

        // Apply layout template
        $layout = $this->applyLayoutTemplate($report['template'], $visualizations, $report);

        return $layout;
    }

    public function exportReport($report_id, $format, $parameters = []) {
        $rendered_report = $this->renderReport($report_id, $parameters);

        switch ($format) {
            case 'pdf':
                return $this->exportToPDF($rendered_report);
            case 'excel':
                return $this->exportToExcel($rendered_report);
            case 'csv':
                return $this->exportToCSV($rendered_report);
            case 'json':
                return json_encode($rendered_report);
            default:
                throw new Exception("Unsupported export format: {$format}");
        }
    }

    public function shareReport($report_id, $recipients, $permissions = []) {
        $report = $this->loadReport($report_id);

        // Create sharing record
        $share_id = $this->createShareRecord($report_id, $recipients, $permissions);

        // Send notification emails
        $this->sendShareNotifications($report, $recipients, $share_id);

        return $share_id;
    }

    public function duplicateReport($report_id, $new_name = null) {
        $original_report = $this->loadReport($report_id);

        $duplicated_report = $original_report;
        $duplicated_report['id'] = $this->generateReportId();
        $duplicated_report['name'] = $new_name ?: $original_report['name'] . ' (Copy)';
        $duplicated_report['created_by'] = get_current_user_id();
        $duplicated_report['created_at'] = time();
        $duplicated_report['version'] = 1;
        unset($duplicated_report['updated_at']);

        $this->saveReport($duplicated_report);

        return $duplicated_report['id'];
    }

    public function getReportMetadata($report_id) {
        $report = $this->loadReport($report_id);

        return [
            'id' => $report['id'],
            'name' => $report['name'],
            'description' => $report['description'],
            'template' => $report['template'],
            'data_sources' => array_keys($report['data_sources']),
            'component_count' => count($report['components']),
            'created_by' => $report['created_by'],
            'created_at' => $report['created_at'],
            'updated_at' => $report['updated_at'] ?? null,
            'version' => $report['version']
        ];
    }

    // Component management methods
    public function addComponent($report_id, $component_config) {
        $this->validateComponent($component_config);

        $report = $this->loadReport($report_id);
        $report['components'][] = $component_config;

        $this->saveReport($report);

        return count($report['components']) - 1; // Return component index
    }

    public function updateComponent($report_id, $component_index, $updates) {
        $report = $this->loadReport($report_id);

        if (!isset($report['components'][$component_index])) {
            throw new Exception('Component not found');
        }

        $component = $report['components'][$component_index];

        // Apply updates
        foreach ($updates as $key => $value) {
            $component[$key] = $value;
        }

        // Re-validate component
        $this->validateComponent($component);

        $report['components'][$component_index] = $component;
        $this->saveReport($report);

        return true;
    }

    public function removeComponent($report_id, $component_index) {
        $report = $this->loadReport($report_id);

        if (!isset($report['components'][$component_index])) {
            throw new Exception('Component not found');
        }

        array_splice($report['components'], $component_index, 1);
        $this->saveReport($report);

        return true;
    }

    // Data source methods
    public function addDataSource($report_id, $data_source_config) {
        $this->validateDataSource($data_source_config);

        $report = $this->loadReport($report_id);
        $data_source_id = 'ds_' . time();
        $report['data_sources'][$data_source_id] = $data_source_config;

        $this->saveReport($report);

        return $data_source_id;
    }

    public function testDataSource($data_source_config) {
        try {
            $this->validateDataSource($data_source_config);

            // Attempt to connect and fetch sample data
            $sample_data = $this->fetchDataSourceSample($data_source_config);

            return [
                'success' => true,
                'sample_data' => $sample_data,
                'field_count' => count($sample_data[0] ?? [])
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    // Validation methods
    private function validateReportConfig($config) {
        $required_fields = ['name', 'template', 'data_sources', 'components'];

        foreach ($required_fields as $field) {
            if (!isset($config[$field])) {
                throw new Exception("Missing required field: {$field}");
            }
        }

        if (!isset($this->layout_templates[$config['template']])) {
            throw new Exception("Invalid template: {$config['template']}");
        }

        $this->validateComponents($config['components']);
        $this->validateDataSources($config['data_sources']);
    }

    private function validateComponents($components) {
        foreach ($components as $component) {
            $this->validateComponent($component);
        }
    }

    private function validateComponent($component) {
        if (!isset($component['type']) || !isset($this->visualization_components[$component['type']])) {
            throw new Exception("Invalid component type: {$component['type']}");
        }

        // Check required dimensions
        $component_type = $this->visualization_components[$component['type']];
        foreach ($component_type['dimensions'] as $dimension) {
            if (!isset($component[$dimension])) {
                throw new Exception("Missing required dimension: {$dimension}");
            }
        }
    }

    private function validateDataSources($data_sources) {
        foreach ($data_sources as $data_source) {
            $this->validateDataSource($data_source);
        }
    }

    private function validateDataSource($data_source) {
        if (!isset($data_source['type']) || !isset($this->data_sources[$data_source['type']])) {
            throw new Exception("Invalid data source type: {$data_source['type']}");
        }

        // Additional validation based on data source type
        $source_type = $this->data_sources[$data_source['type']];
        // ... validation logic
    }

    // Data fetching methods
    private function fetchComponentData($components, $parameters) {
        $component_data = [];

        foreach ($components as $index => $component) {
            $component_data[$index] = $this->fetchSingleComponentData($component, $parameters);
        }

        return $component_data;
    }

    private function fetchSingleComponentData($component, $parameters) {
        $data_source = $component['data_source'];
        $query_config = $component['query'];

        // Build query based on component requirements
        $query = $this->buildComponentQuery($query_config, $parameters);

        // Execute query
        return $this->executeDataSourceQuery($data_source, $query);
    }

    private function buildComponentQuery($query_config, $parameters) {
        // Build query based on component configuration
        // This would include field selection, filtering, aggregation, etc.
        return $query_config; // Simplified
    }

    private function executeDataSourceQuery($data_source, $query) {
        // Execute query against the specified data source
        // This would connect to databases, APIs, etc.
        return []; // Placeholder
    }

    // Visualization methods
    private function generateVisualizations($components, $component_data) {
        $visualizations = [];

        foreach ($components as $index => $component) {
            $data = $component_data[$index];
            $visualizations[$index] = $this->generateVisualization($component, $data);
        }

        return $visualizations;
    }

    private function generateVisualization($component, $data) {
        $component_type = $component['type'];
        $visualization_config = $this->visualization_components[$component_type];

        // Generate visualization using Chart.js or similar
        return [
            'type' => $component_type,
            'data' => $data,
            'config' => $component['config'] ?? [],
            'html' => $this->renderVisualizationHTML($component_type, $data, $component['config'] ?? [])
        ];
    }

    private function renderVisualizationHTML($type, $data, $config) {
        // Render HTML for visualization component
        // In practice, this would use a templating system
        return "<div class='visualization' data-type='{$type}'></div>";
    }

    // Layout methods
    private function applyLayoutTemplate($template_name, $visualizations, $report) {
        $template = $this->layout_templates[$template_name];

        // Apply template to organize visualizations
        return [
            'template' => $template_name,
            'layout' => $template['layout'],
            'sections' => $this->organizeVisualizationsIntoSections($visualizations, $template),
            'metadata' => [
                'report_name' => $report['name'],
                'generated_at' => date('Y-m-d H:i:s'),
                'component_count' => count($visualizations)
            ]
        ];
    }

    private function organizeVisualizationsIntoSections($visualizations, $template) {
        $sections = [];

        foreach ($template['sections'] as $section_name) {
            $sections[$section_name] = [];
        }

        // Distribute visualizations across sections
        $section_index = 0;
        foreach ($visualizations as $index => $visualization) {
            $section_name = $template['sections'][$section_index % count($template['sections'])];
            $sections[$section_name][] = $visualization;
            $section_index++;
        }

        return $sections;
    }

    // Export methods
    private function exportToPDF($report_data) {
        // Use TCPDF or similar to generate PDF
        require_once 'vendor/tcpdf/tcpdf.php';

        $pdf = new TCPDF();
        $pdf->AddPage();
        $pdf->SetFont('helvetica', '', 12);

        // Add report content
        $pdf->writeHTML($this->renderReportHTML($report_data));

        return $pdf->Output('', 'S');
    }

    private function exportToExcel($report_data) {
        // Use PhpSpreadsheet to generate Excel
        require_once 'vendor/phpoffice/phpspreadsheet/src/Bootstrap.php';

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();

        // Add report data to spreadsheet
        $this->populateSpreadsheet($spreadsheet, $report_data);

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        ob_start();
        $writer->save('php://output');
        return ob_get_clean();
    }

    private function exportToCSV($report_data) {
        $output = fopen('php://temp', 'r+');

        // Convert report data to CSV format
        $this->writeReportToCSV($output, $report_data);

        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return $csv;
    }

    // Persistence methods
    private function saveReport($report) {
        global $wpdb;

        $table_name = 'wp_slos_reports';

        $wpdb->replace($table_name, [
            'id' => $report['id'],
            'name' => $report['name'],
            'description' => $report['description'],
            'config' => json_encode($report),
            'created_by' => $report['created_by'],
            'created_at' => date('Y-m-d H:i:s', $report['created_at']),
            'updated_at' => isset($report['updated_at']) ? date('Y-m-d H:i:s', $report['updated_at']) : null,
            'version' => $report['version']
        ]);

        $this->user_reports[$report['id']] = $report;
    }

    private function loadReport($report_id) {
        if (isset($this->user_reports[$report_id])) {
            return $this->user_reports[$report_id];
        }

        global $wpdb;

        $table_name = 'wp_slos_reports';
        $report_data = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$table_name} WHERE id = %s",
            $report_id
        ));

        if (!$report_data) {
            throw new Exception('Report not found');
        }

        $report = json_decode($report_data->config, true);
        $this->user_reports[$report_id] = $report;

        return $report;
    }

    // Utility methods
    private function generateReportId() {
        return 'report_' . time() . '_' . wp_generate_password(8, false);
    }

    private function createShareRecord($report_id, $recipients, $permissions) {
        // Create database record for report sharing
        return 'share_' . time();
    }

    private function sendShareNotifications($report, $recipients, $share_id) {
        // Send email notifications to recipients
    }

    private function renderReportHTML($report_data) {
        // Render report as HTML
        return '<html><body><h1>Report</h1></body></html>';
    }

    private function populateSpreadsheet($spreadsheet, $report_data) {
        // Populate spreadsheet with report data
    }

    private function writeReportToCSV($handle, $report_data) {
        // Write report data to CSV
    }

    private function fetchDataSourceSample($config) {
        // Fetch sample data from data source
        return [];
    }
}
```

### Dashboard Framework

#### Step 1: Interactive Dashboard Builder
```
SLOS → Advanced → Reporting → Dashboard Framework
```

**Dashboard Framework:**
```json
{
  "dashboard_framework": {
    "architecture": {
      "real_time_updates": {
        "websocket_support": true,
        "polling_fallback": true,
        "update_frequency": "configurable",
        "data_compression": "enabled"
      },
      "responsive_layout": {
        "grid_system": "12_column",
        "breakpoint_system": "mobile_first",
        "component_sizing": "flexible",
        "auto_layout": "intelligent"
      },
      "interactivity": {
        "drill_down": "multi_level",
        "cross_filtering": "automatic",
        "parameter_controls": "dynamic",
        "context_menus": "rich"
      },
      "performance": {
        "lazy_loading": "enabled",
        "caching_strategy": "multi_layer",
        "data_virtualization": "automatic",
        "render_optimization": "gpu_accelerated"
      }
    },
    "dashboard_types": {
      "executive_dashboard": {
        "focus": "high_level_kpis",
        "audience": "executives",
        "refresh_rate": "hourly",
        "components": ["kpi_cards", "trend_charts", "status_indicators"]
      },
      "operational_dashboard": {
        "focus": "real_time_operations",
        "audience": "operators",
        "refresh_rate": "real_time",
        "components": ["live_charts", "alert_panels", "control_interfaces"]
      },
      "analytical_dashboard": {
        "focus": "data_exploration",
        "audience": "analysts",
        "refresh_rate": "on_demand",
        "components": ["advanced_charts", "filter_panels", "drill_down_tables"]
      }
    }
  },
  "dashboard_capabilities": {
    "custom_widgets": true,
    "theme_customization": true,
    "user_preferences": true,
    "collaboration": true,
    "mobile_optimization": true
  }
}
```

#### Step 2: Dashboard Engine Implementation
```php
// Advanced interactive dashboard engine
class DashboardEngine {
    private $dashboards = [];
    private $widgets = [];
    private $data_connectors = [];
    private $layout_manager = null;

    public function __construct() {
        $this->layout_manager = new DashboardLayoutManager();
        $this->initializeWidgets();
        $this->setupDataConnectors();
    }

    private function initializeWidgets() {
        $this->widgets = [
            'kpi_card' => [
                'name' => 'KPI Card',
                'category' => 'metrics',
                'config' => [
                    'metric' => 'required',
                    'format' => 'number|currency|percentage',
                    'thresholds' => 'optional',
                    'trend_indicator' => 'boolean'
                ],
                'template' => 'kpi_card.twig',
                'real_time' => true
            ],
            'line_chart' => [
                'name' => 'Line Chart',
                'category' => 'trends',
                'config' => [
                    'x_axis' => 'required',
                    'y_axis' => 'required',
                    'data_source' => 'required',
                    'time_range' => 'optional'
                ],
                'template' => 'line_chart.twig',
                'real_time' => true
            ],
            'bar_chart' => [
                'name' => 'Bar Chart',
                'category' => 'comparison',
                'config' => [
                    'categories' => 'required',
                    'values' => 'required',
                    'orientation' => 'horizontal|vertical',
                    'stacked' => 'boolean'
                ],
                'template' => 'bar_chart.twig',
                'real_time' => false
            ],
            'gauge_chart' => [
                'name' => 'Gauge Chart',
                'category' => 'status',
                'config' => [
                    'value' => 'required',
                    'min' => 'number',
                    'max' => 'number',
                    'thresholds' => 'array'
                ],
                'template' => 'gauge_chart.twig',
                'real_time' => true
            ],
            'data_table' => [
                'name' => 'Data Table',
                'category' => 'detail',
                'config' => [
                    'columns' => 'required',
                    'data_source' => 'required',
                    'pagination' => 'boolean',
                    'sorting' => 'boolean',
                    'filtering' => 'boolean'
                ],
                'template' => 'data_table.twig',
                'real_time' => false
            ],
            'alert_panel' => [
                'name' => 'Alert Panel',
                'category' => 'monitoring',
                'config' => [
                    'alert_types' => 'array',
                    'severity_filter' => 'optional',
                    'auto_refresh' => 'boolean'
                ],
                'template' => 'alert_panel.twig',
                'real_time' => true
            ],
            'filter_panel' => [
                'name' => 'Filter Panel',
                'category' => 'controls',
                'config' => [
                    'filters' => 'required',
                    'layout' => 'horizontal|vertical',
                    'collapsible' => 'boolean'
                ],
                'template' => 'filter_panel.twig',
                'real_time' => false
            ]
        ];
    }

    private function setupDataConnectors() {
        $this->data_connectors = [
            'database' => new DatabaseConnector(),
            'api' => new APIConnector(),
            'realtime' => new RealTimeDataConnector(),
            'cached' => new CachedDataConnector()
        ];
    }

    public function createDashboard($config) {
        $dashboard_id = $this->generateDashboardId();

        $dashboard = [
            'id' => $dashboard_id,
            'name' => $config['name'],
            'description' => $config['description'],
            'layout' => $config['layout'],
            'widgets' => $config['widgets'],
            'data_sources' => $config['data_sources'],
            'permissions' => $config['permissions'] ?? [],
            'settings' => $config['settings'] ?? [],
            'created_by' => get_current_user_id(),
            'created_at' => time(),
            'version' => 1
        ];

        $this->validateDashboard($dashboard);
        $this->saveDashboard($dashboard);

        return $dashboard_id;
    }

    public function renderDashboard($dashboard_id, $user_id = null, $parameters = []) {
        $dashboard = $this->loadDashboard($dashboard_id);

        // Check permissions
        if (!$this->checkDashboardPermissions($dashboard, $user_id)) {
            throw new Exception('Access denied to dashboard');
        }

        // Apply user parameters
        $dashboard = $this->applyParameters($dashboard, $parameters);

        // Fetch widget data
        $widget_data = $this->fetchDashboardData($dashboard);

        // Generate widget HTML
        $widgets_html = $this->renderWidgets($dashboard['widgets'], $widget_data);

        // Apply layout
        $layout_html = $this->layout_manager->applyLayout(
            $dashboard['layout'],
            $widgets_html,
            $dashboard
        );

        // Add dashboard framework
        $dashboard_html = $this->wrapDashboardHTML($layout_html, $dashboard);

        return $dashboard_html;
    }

    public function updateDashboard($dashboard_id, $updates) {
        $dashboard = $this->loadDashboard($dashboard_id);

        foreach ($updates as $key => $value) {
            if ($key === 'widgets') {
                $this->validateWidgets($value);
            }
            $dashboard[$key] = $value;
        }

        $dashboard['updated_at'] = time();
        $dashboard['version']++;

        $this->validateDashboard($dashboard);
        $this->saveDashboard($dashboard);

        return true;
    }

    public function addWidget($dashboard_id, $widget_config) {
        $this->validateWidget($widget_config);

        $dashboard = $this->loadDashboard($dashboard_id);
        $dashboard['widgets'][] = $widget_config;

        $this->saveDashboard($dashboard);

        return count($dashboard['widgets']) - 1;
    }

    public function updateWidget($dashboard_id, $widget_index, $updates) {
        $dashboard = $this->loadDashboard($dashboard_id);

        if (!isset($dashboard['widgets'][$widget_index])) {
            throw new Exception('Widget not found');
        }

        $widget = $dashboard['widgets'][$widget_index];

        foreach ($updates as $key => $value) {
            $widget[$key] = $value;
        }

        $this->validateWidget($widget);
        $dashboard['widgets'][$widget_index] = $widget;

        $this->saveDashboard($dashboard);

        return true;
    }

    public function removeWidget($dashboard_id, $widget_index) {
        $dashboard = $this->loadDashboard($dashboard_id);

        if (!isset($dashboard['widgets'][$widget_index])) {
            throw new Exception('Widget not found');
        }

        array_splice($dashboard['widgets'], $widget_index, 1);
        $this->saveDashboard($dashboard);

        return true;
    }

    public function getDashboardData($dashboard_id, $widget_id = null) {
        $dashboard = $this->loadDashboard($dashboard_id);

        if ($widget_id) {
            // Return data for specific widget
            $widget_config = $this->findWidgetById($dashboard['widgets'], $widget_id);
            return $this->fetchWidgetData($widget_config);
        } else {
            // Return data for all widgets
            return $this->fetchDashboardData($dashboard);
        }
    }

    public function duplicateDashboard($dashboard_id, $new_name = null) {
        $original = $this->loadDashboard($dashboard_id);

        $duplicate = $original;
        $duplicate['id'] = $this->generateDashboardId();
        $duplicate['name'] = $new_name ?: $original['name'] . ' (Copy)';
        $duplicate['created_by'] = get_current_user_id();
        $duplicate['created_at'] = time();
        $duplicate['version'] = 1;
        unset($duplicate['updated_at']);

        $this->saveDashboard($duplicate);

        return $duplicate['id'];
    }

    public function exportDashboard($dashboard_id, $format = 'json') {
        $dashboard = $this->loadDashboard($dashboard_id);

        switch ($format) {
            case 'json':
                return json_encode($dashboard);
            case 'yaml':
                return yaml_emit($dashboard);
            default:
                throw new Exception("Unsupported export format: {$format}");
        }
    }

    public function importDashboard($dashboard_data, $format = 'json') {
        if ($format === 'json') {
            $dashboard = json_decode($dashboard_data, true);
        } elseif ($format === 'yaml') {
            $dashboard = yaml_parse($dashboard_data);
        } else {
            throw new Exception("Unsupported import format: {$format}");
        }

        // Generate new ID and clean up
        $dashboard['id'] = $this->generateDashboardId();
        $dashboard['created_by'] = get_current_user_id();
        $dashboard['created_at'] = time();
        $dashboard['version'] = 1;
        unset($dashboard['updated_at']);

        $this->validateDashboard($dashboard);
        $this->saveDashboard($dashboard);

        return $dashboard['id'];
    }

    // Real-time update methods
    public function enableRealTimeUpdates($dashboard_id, $update_interval = 30) {
        $dashboard = $this->loadDashboard($dashboard_id);

        $dashboard['settings']['real_time'] = [
            'enabled' => true,
            'interval' => $update_interval,
            'websocket_support' => true
        ];

        $this->saveDashboard($dashboard);

        // Register real-time update hooks
        add_action("slos_dashboard_update_{$dashboard_id}", [$this, 'processRealTimeUpdate']);

        return true;
    }

    public function processRealTimeUpdate($dashboard_id) {
        $dashboard = $this->loadDashboard($dashboard_id);

        // Check which widgets need updating
        $widgets_to_update = array_filter($dashboard['widgets'], function($widget) {
            return $this->widgets[$widget['type']]['real_time'] ?? false;
        });

        if (empty($widgets_to_update)) {
            return;
        }

        // Fetch updated data
        $updated_data = [];
        foreach ($widgets_to_update as $index => $widget) {
            $updated_data[$index] = $this->fetchWidgetData($widget);
        }

        // Send real-time update to connected clients
        $this->broadcastDashboardUpdate($dashboard_id, $updated_data);
    }

    // Private helper methods
    private function validateDashboard($dashboard) {
        if (empty($dashboard['name'])) {
            throw new Exception('Dashboard name is required');
        }

        if (empty($dashboard['widgets'])) {
            throw new Exception('Dashboard must have at least one widget');
        }

        $this->validateWidgets($dashboard['widgets']);
    }

    private function validateWidgets($widgets) {
        foreach ($widgets as $widget) {
            $this->validateWidget($widget);
        }
    }

    private function validateWidget($widget) {
        if (!isset($this->widgets[$widget['type']])) {
            throw new Exception("Unknown widget type: {$widget['type']}");
        }

        $widget_def = $this->widgets[$widget['type']];

        // Check required configuration
        foreach ($widget_def['config'] as $config_key => $requirement) {
            if ($requirement === 'required' && !isset($widget['config'][$config_key])) {
                throw new Exception("Missing required config: {$config_key}");
            }
        }
    }

    private function fetchDashboardData($dashboard) {
        $data = [];

        foreach ($dashboard['widgets'] as $index => $widget) {
            $data[$index] = $this->fetchWidgetData($widget);
        }

        return $data;
    }

    private function fetchWidgetData($widget) {
        $data_source = $widget['data_source'];
        $config = $widget['config'];

        if (!isset($this->data_connectors[$data_source['type']])) {
            throw new Exception("Unknown data source type: {$data_source['type']}");
        }

        $connector = $this->data_connectors[$data_source['type']];
        return $connector->fetchData($data_source, $config);
    }

    private function renderWidgets($widgets, $widget_data) {
        $rendered_widgets = [];

        foreach ($widgets as $index => $widget) {
            $data = $widget_data[$index];
            $rendered_widgets[] = $this->renderWidget($widget, $data);
        }

        return $rendered_widgets;
    }

    private function renderWidget($widget, $data) {
        $widget_def = $this->widgets[$widget['type']];

        // Use template engine to render widget
        return [
            'id' => $widget['id'] ?? 'widget_' . time(),
            'type' => $widget['type'],
            'html' => $this->renderWidgetTemplate($widget_def['template'], $widget, $data),
            'config' => $widget['config'],
            'data' => $data
        ];
    }

    private function renderWidgetTemplate($template, $widget, $data) {
        // Simplified template rendering
        return "<div class='dashboard-widget {$widget['type']}' data-widget-id='{$widget['id']}'></div>";
    }

    private function wrapDashboardHTML($layout_html, $dashboard) {
        $dashboard_html = "
        <div class='slos-dashboard' data-dashboard-id='{$dashboard['id']}'>
            <div class='dashboard-header'>
                <h1>{$dashboard['name']}</h1>
                <div class='dashboard-controls'>
                    <button class='refresh-btn'>Refresh</button>
                    <button class='fullscreen-btn'>Fullscreen</button>
                </div>
            </div>
            <div class='dashboard-content'>
                {$layout_html}
            </div>
        </div>";

        return $dashboard_html;
    }

    private function applyParameters($dashboard, $parameters) {
        // Apply user parameters to dashboard configuration
        return $dashboard; // Simplified
    }

    private function checkDashboardPermissions($dashboard, $user_id) {
        // Check if user has permission to view dashboard
        return true; // Simplified
    }

    private function findWidgetById($widgets, $widget_id) {
        foreach ($widgets as $widget) {
            if (($widget['id'] ?? null) === $widget_id) {
                return $widget;
            }
        }
        return null;
    }

    private function broadcastDashboardUpdate($dashboard_id, $updated_data) {
        // Send real-time updates to connected clients via WebSocket or AJAX
    }

    private function saveDashboard($dashboard) {
        global $wpdb;

        $table_name = 'wp_slos_dashboards';

        $wpdb->replace($table_name, [
            'id' => $dashboard['id'],
            'name' => $dashboard['name'],
            'config' => json_encode($dashboard),
            'created_by' => $dashboard['created_by'],
            'created_at' => date('Y-m-d H:i:s', $dashboard['created_at']),
            'updated_at' => isset($dashboard['updated_at']) ? date('Y-m-d H:i:s', $dashboard['updated_at']) : null,
            'version' => $dashboard['version']
        ]);

        $this->dashboards[$dashboard['id']] = $dashboard;
    }

    private function loadDashboard($dashboard_id) {
        if (isset($this->dashboards[$dashboard_id])) {
            return $this->dashboards[$dashboard_id];
        }

        global $wpdb;

        $table_name = 'wp_slos_dashboards';
        $dashboard_data = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$table_name} WHERE id = %s",
            $dashboard_id
        ));

        if (!$dashboard_data) {
            throw new Exception('Dashboard not found');
        }

        $dashboard = json_decode($dashboard_data->config, true);
        $this->dashboards[$dashboard_id] = $dashboard;

        return $dashboard;
    }

    private function generateDashboardId() {
        return 'dashboard_' . time() . '_' . wp_generate_password(8, false);
    }
}
```

### Scheduled Reporting System

#### Step 1: Report Scheduling Engine
```
SLOS → Advanced → Reporting → Scheduled Reports
```

**Scheduled Reporting Architecture:**
```json
{
  "scheduling_engine": {
    "schedule_types": {
      "time_based": {
        "frequencies": ["hourly", "daily", "weekly", "monthly", "quarterly"],
        "time_zones": "user_selectable",
        "daylight_saving": "automatic_adjustment"
      },
      "event_based": {
        "triggers": ["data_threshold", "compliance_event", "user_action"],
        "conditions": "configurable",
        "cooldown_period": "prevent_spam"
      },
      "on_demand": {
        "api_trigger": true,
        "manual_trigger": true,
        "webhook_support": true
      }
    },
    "distribution_channels": {
      "email": {
        "formats": ["PDF", "Excel", "HTML"],
        "attachments": "compressed",
        "templates": "customizable"
      },
      "cloud_storage": {
        "providers": ["AWS_S3", "Google_Cloud", "Azure_Blob"],
        "access_control": "granular",
        "retention_policies": "configurable"
      },
      "api_webhooks": {
        "authentication": ["API_Key", "OAuth"],
        "retry_logic": "exponential_backoff",
        "failure_notifications": true
      },
      "internal_systems": {
        "database_storage": true,
        "dashboard_integration": true,
        "alert_systems": true
      }
    },
    "execution_engine": {
      "parallel_processing": true,
      "resource_limits": "configurable",
      "error_handling": "comprehensive",
      "performance_monitoring": true
    }
  },
  "scheduling_capabilities": {
    "complex_schedules": true,
    "conditional_execution": true,
    "dependency_chains": true,
    "failure_recovery": true,
    "performance_optimization": true
  }
}
```

#### Step 2: Report Scheduler Implementation
```php
// Advanced report scheduling and distribution system
class ReportScheduler {
    private $scheduled_reports = [];
    private $execution_queue = [];
    private $distribution_channels = [];

    public function __construct() {
        $this->initializeDistributionChannels();
        $this->loadScheduledReports();
        $this->setupExecutionHooks();
    }

    private function initializeDistributionChannels() {
        $this->distribution_channels = [
            'email' => new EmailDistributionChannel(),
            'cloud_storage' => new CloudStorageDistributionChannel(),
            'webhook' => new WebhookDistributionChannel(),
            'internal' => new InternalDistributionChannel()
        ];
    }

    private function setupExecutionHooks() {
        add_action('slos_process_report_queue', [$this, 'processExecutionQueue']);
        add_action('slos_check_scheduled_reports', [$this, 'checkScheduledReports']);

        // Schedule recurring tasks
        if (!wp_next_scheduled('slos_process_report_queue')) {
            wp_schedule_event(time(), '5minutes', 'slos_process_report_queue');
        }

        if (!wp_next_scheduled('slos_check_scheduled_reports')) {
            wp_schedule_event(time(), 'hourly', 'slos_check_scheduled_reports');
        }
    }

    public function scheduleReport($report_config) {
        $schedule_id = $this->generateScheduleId();

        $schedule = [
            'id' => $schedule_id,
            'report_type' => $report_config['report_type'],
            'report_id' => $report_config['report_id'],
            'schedule' => $report_config['schedule'],
            'parameters' => $report_config['parameters'] ?? [],
            'distribution' => $report_config['distribution'],
            'filters' => $report_config['filters'] ?? [],
            'active' => true,
            'created_by' => get_current_user_id(),
            'created_at' => time(),
            'last_run' => null,
            'next_run' => $this->calculateNextRun($report_config['schedule']),
            'run_count' => 0
        ];

        $this->validateSchedule($schedule);
        $this->saveSchedule($schedule);

        return $schedule_id;
    }

    public function updateSchedule($schedule_id, $updates) {
        $schedule = $this->loadSchedule($schedule_id);

        foreach ($updates as $key => $value) {
            if ($key === 'schedule') {
                $schedule['next_run'] = $this->calculateNextRun($value);
            }
            $schedule[$key] = $value;
        }

        $schedule['updated_at'] = time();

        $this->validateSchedule($schedule);
        $this->saveSchedule($schedule);

        return true;
    }

    public function cancelSchedule($schedule_id) {
        $schedule = $this->loadSchedule($schedule_id);
        $schedule['active'] = false;
        $schedule['cancelled_at'] = time();

        $this->saveSchedule($schedule);

        return true;
    }

    public function executeReportNow($schedule_id) {
        $schedule = $this->loadSchedule($schedule_id);

        // Add to execution queue with high priority
        $this->execution_queue[] = [
            'schedule_id' => $schedule_id,
            'priority' => 'high',
            'manual_trigger' => true,
            'queued_at' => time()
        ];

        return true;
    }

    public function checkScheduledReports() {
        $current_time = time();

        foreach ($this->scheduled_reports as $schedule_id => $schedule) {
            if (!$schedule['active']) {
                continue;
            }

            if ($schedule['next_run'] <= $current_time) {
                // Add to execution queue
                $this->execution_queue[] = [
                    'schedule_id' => $schedule_id,
                    'priority' => 'normal',
                    'queued_at' => $current_time
                ];

                // Calculate next run
                $schedule['next_run'] = $this->calculateNextRun($schedule['schedule']);
                $this->saveSchedule($schedule);
            }
        }
    }

    public function processExecutionQueue() {
        // Sort by priority (high priority first)
        usort($this->execution_queue, function($a, $b) {
            $priority_order = ['high' => 3, 'normal' => 2, 'low' => 1];
            return $priority_order[$b['priority']] <=> $priority_order[$a['priority']];
        });

        $max_executions = 5; // Limit concurrent executions
        $executed = 0;

        while ($executed < $max_executions && !empty($this->execution_queue)) {
            $execution = array_shift($this->execution_queue);

            try {
                $this->executeScheduledReport($execution['schedule_id']);
                $executed++;
            } catch (Exception $e) {
                error_log("Report execution failed: " . $e->getMessage());
                // Could implement retry logic here
            }
        }
    }

    private function executeScheduledReport($schedule_id) {
        $schedule = $this->loadSchedule($schedule_id);

        // Generate report
        $report_data = $this->generateScheduledReport($schedule);

        // Distribute report
        $this->distributeReport($schedule, $report_data);

        // Update schedule metadata
        $schedule['last_run'] = time();
        $schedule['run_count']++;
        $this->saveSchedule($schedule);

        // Log execution
        $this->logReportExecution($schedule_id, 'success', $report_data);
    }

    private function generateScheduledReport($schedule) {
        $report_type = $schedule['report_type'];
        $parameters = $schedule['parameters'];

        // Apply schedule-specific filters
        if (isset($schedule['filters'])) {
            $parameters = array_merge($parameters, $schedule['filters']);
        }

        // Generate report based on type
        switch ($report_type) {
            case 'compliance_report':
                return $this->generateComplianceReport($parameters);
            case 'performance_report':
                return $this->generatePerformanceReport($parameters);
            case 'user_engagement_report':
                return $this->generateUserEngagementReport($parameters);
            case 'custom_report':
                return $this->generateCustomReport($schedule['report_id'], $parameters);
            default:
                throw new Exception("Unknown report type: {$report_type}");
        }
    }

    private function distributeReport($schedule, $report_data) {
        $distribution_config = $schedule['distribution'];

        foreach ($distribution_config as $channel => $config) {
            if (!isset($this->distribution_channels[$channel])) {
                continue;
            }

            try {
                $this->distribution_channels[$channel]->distribute($report_data, $config);
            } catch (Exception $e) {
                error_log("Distribution failed for channel {$channel}: " . $e->getMessage());
                // Continue with other channels
            }
        }
    }

    private function calculateNextRun($schedule_config) {
        $current_time = time();

        switch ($schedule_config['type']) {
            case 'hourly':
                return strtotime('+1 hour', $current_time);

            case 'daily':
                $time = $schedule_config['time'] ?? '09:00';
                return strtotime("tomorrow {$time}");

            case 'weekly':
                $day = $schedule_config['day'] ?? 'monday';
                $time = $schedule_config['time'] ?? '09:00';
                return strtotime("next {$day} {$time}");

            case 'monthly':
                $day = $schedule_config['day'] ?? 1;
                $time = $schedule_config['time'] ?? '09:00';
                return strtotime("first day of next month {$time}") + (($day - 1) * 86400);

            case 'cron':
                // Parse cron expression and calculate next run
                return $this->calculateCronNextRun($schedule_config['expression']);

            default:
                throw new Exception("Unknown schedule type: {$schedule_config['type']}");
        }
    }

    private function calculateCronNextRun($cron_expression) {
        // Simplified cron parsing - in production, use a proper cron library
        // For now, assume hourly
        return strtotime('+1 hour');
    }

    // Report generation methods
    private function generateComplianceReport($parameters) {
        $reporting_engine = new CustomReportingEngine();
        return $reporting_engine->generateReport('compliance_status', $parameters);
    }

    private function generatePerformanceReport($parameters) {
        $reporting_engine = new CustomReportingEngine();
        return $reporting_engine->generateReport('performance_dashboard', $parameters);
    }

    private function generateUserEngagementReport($parameters) {
        $reporting_engine = new CustomReportingEngine();
        return $reporting_engine->generateReport('user_engagement', $parameters);
    }

    private function generateCustomReport($report_id, $parameters) {
        $report_builder = new ReportBuilder();
        return $report_builder->renderReport($report_id, $parameters);
    }

    // Validation and utility methods
    private function validateSchedule($schedule) {
        if (empty($schedule['report_type'])) {
            throw new Exception('Report type is required');
        }

        if (empty($schedule['schedule'])) {
            throw new Exception('Schedule configuration is required');
        }

        if (empty($schedule['distribution'])) {
            throw new Exception('Distribution configuration is required');
        }

        $this->validateScheduleConfig($schedule['schedule']);
        $this->validateDistributionConfig($schedule['distribution']);
    }

    private function validateScheduleConfig($schedule_config) {
        $valid_types = ['hourly', 'daily', 'weekly', 'monthly', 'cron'];

        if (!in_array($schedule_config['type'], $valid_types)) {
            throw new Exception("Invalid schedule type: {$schedule_config['type']}");
        }
    }

    private function validateDistributionConfig($distribution_config) {
        foreach ($distribution_config as $channel => $config) {
            if (!isset($this->distribution_channels[$channel])) {
                throw new Exception("Unknown distribution channel: {$channel}");
            }
        }
    }

    private function generateScheduleId() {
        return 'schedule_' . time() . '_' . wp_generate_password(8, false);
    }

    private function saveSchedule($schedule) {
        global $wpdb;

        $table_name = 'wp_slos_report_schedules';

        $wpdb->replace($table_name, [
            'id' => $schedule['id'],
            'config' => json_encode($schedule),
            'active' => $schedule['active'],
            'created_by' => $schedule['created_by'],
            'created_at' => date('Y-m-d H:i:s', $schedule['created_at']),
            'updated_at' => isset($schedule['updated_at']) ? date('Y-m-d H:i:s', $schedule['updated_at']) : null,
            'last_run' => $schedule['last_run'] ? date('Y-m-d H:i:s', $schedule['last_run']) : null,
            'next_run' => date('Y-m-d H:i:s', $schedule['next_run'])
        ]);

        $this->scheduled_reports[$schedule['id']] = $schedule;
    }

    private function loadSchedule($schedule_id) {
        if (isset($this->scheduled_reports[$schedule_id])) {
            return $this->scheduled_reports[$schedule_id];
        }

        global $wpdb;

        $table_name = 'wp_slos_report_schedules';
        $schedule_data = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$table_name} WHERE id = %s",
            $schedule_id
        ));

        if (!$schedule_data) {
            throw new Exception('Schedule not found');
        }

        $schedule = json_decode($schedule_data->config, true);
        $this->scheduled_reports[$schedule_id] = $schedule;

        return $schedule;
    }

    private function loadScheduledReports() {
        global $wpdb;

        $schedules = $wpdb->get_results("
            SELECT * FROM wp_slos_report_schedules
            WHERE active = 1
        ");

        foreach ($schedules as $schedule_data) {
            $schedule = json_decode($schedule_data->config, true);
            $this->scheduled_reports[$schedule->id] = $schedule;
        }
    }

    private function logReportExecution($schedule_id, $status, $report_data) {
        global $wpdb;

        $wpdb->insert('wp_slos_report_execution_log', [
            'schedule_id' => $schedule_id,
            'status' => $status,
            'executed_at' => current_time('mysql'),
            'report_size' => strlen(json_encode($report_data)),
            'execution_details' => json_encode([
                'timestamp' => time(),
                'status' => $status
            ])
        ]);
    }
}
```

### Support Resources

#### Documentation
- [Report Builder API Reference](../Advanced/01-rest-api.md#reports)
- [Dashboard Customization Guide](../How-tos/08-understanding-consent-logs.md)
- [Data Visualization Best Practices](../Advanced/08-advanced-analytics.md)

#### Help
- Report development specialists
- Data visualization experts
- Business intelligence consultants
- Dashboard architects