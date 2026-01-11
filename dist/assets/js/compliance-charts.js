/**
 * Compliance Charts - Time-Series Visualization
 *
 * Chart.js integration for consent trend visualization.
 * Provides interactive charts on the compliance dashboard.
 *
 * @package    ShahiLegalFlowSuite
 * @subpackage Assets
 * @since      3.1.1
 */

(function($) {
	'use strict';

	/**
	 * Compliance Charts Module
	 */
	const ComplianceCharts = {

		/**
		 * Chart instances
		 */
		charts: {},

		/**
		 * Initialize charts
		 */
		init() {
			// Initialize consent trends chart
			if ( $('#slos-consent-trends-chart').length ) {
				this.initConsentTrendsChart();
			}

			// Initialize export buttons
			this.initExportButtons();

			// Initialize time range selector
			this.initTimeRangeSelector();
		},

		/**
		 * Initialize consent trends chart
		 */
		initConsentTrendsChart() {
			const canvas = document.getElementById('slos-consent-trends-chart');
			if ( ! canvas ) {
				return;
			}

			// Get data from data attribute or fetch via AJAX
			const dataAttr = canvas.getAttribute('data-chart-data');
			if ( dataAttr ) {
				try {
					const chartData = JSON.parse(dataAttr);
					this.renderChart(canvas, chartData);
				} catch (e) {
					console.error('Failed to parse chart data:', e);
				}
			} else {
				// Fetch data via REST API
				this.fetchTimeSeriesData();
			}
		},

		/**
		 * Fetch time-series data via AJAX
		 */
		fetchTimeSeriesData( args = {} ) {
			const defaults = {
				interval: 'daily',
				days_back: 30,
				group_by: 'none'
			};
			const params = { ...defaults, ...args };

			$.ajax({
				url: ajaxurl,
				method: 'POST',
				data: {
					action: 'slos_get_consent_time_series',
					nonce: slosExport.nonce,
					...params
				},
				success: ( response ) => {
					if ( response.success && response.data ) {
						this.renderChart(
							document.getElementById('slos-consent-trends-chart'),
							response.data
						);
					}
				},
				error: ( xhr, status, error ) => {
					console.error('Failed to fetch time-series data:', error);
				}
			});
		},

		/**
		 * Render Chart.js chart
		 */
		renderChart( canvas, data ) {
			const ctx = canvas.getContext('2d');

			// Destroy existing chart
			if ( this.charts.consentTrends ) {
				this.charts.consentTrends.destroy();
			}

			// Color palette
			const colors = [
				'rgba(59, 130, 246, 0.8)',   // Blue
				'rgba(16, 185, 129, 0.8)',   // Green
				'rgba(245, 158, 11, 0.8)',   // Orange
				'rgba(239, 68, 68, 0.8)',    // Red
				'rgba(139, 92, 246, 0.8)',   // Purple
			];

			// Prepare datasets with colors
			const datasets = data.datasets.map((dataset, index) => ({
				label: dataset.label,
				data: dataset.data,
				backgroundColor: colors[index % colors.length],
				borderColor: colors[index % colors.length].replace('0.8', '1'),
				borderWidth: 2,
				fill: false,
				tension: 0.3,
			}));

			// Chart configuration
			const config = {
				type: 'line',
				data: {
					labels: data.labels,
					datasets: datasets
				},
				options: {
					responsive: true,
					maintainAspectRatio: false,
					plugins: {
						legend: {
							position: 'top',
							labels: {
								color: '#ffffff',
								font: {
									size: 12,
									family: '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif'
								},
								padding: 15,
								usePointStyle: true,
								pointStyle: 'circle'
							}
						},
						tooltip: {
							mode: 'index',
							intersect: false,
							backgroundColor: 'rgba(0, 0, 0, 0.8)',
							titleColor: '#fff',
							bodyColor: '#fff',
							borderColor: 'rgba(59, 130, 246, 0.5)',
							borderWidth: 1,
							padding: 12,
							displayColors: true,
							callbacks: {
								title: (tooltipItems) => {
									return tooltipItems[0].label;
								},
								label: (context) => {
									let label = context.dataset.label || '';
									if (label) {
										label += ': ';
									}
									label += context.parsed.y.toLocaleString();
									return label;
								}
							}
						}
					},
					scales: {
						x: {
							ticks: {
								color: 'rgba(255, 255, 255, 0.7)',
								font: {
									size: 11
								},
								maxRotation: 45,
								minRotation: 0
							},
							grid: {
								color: 'rgba(255, 255, 255, 0.05)',
								borderColor: 'rgba(255, 255, 255, 0.1)'
							}
						},
						y: {
							beginAtZero: true,
							ticks: {
								color: 'rgba(255, 255, 255, 0.7)',
								font: {
									size: 11
								},
								precision: 0
							},
							grid: {
								color: 'rgba(255, 255, 255, 0.05)',
								borderColor: 'rgba(255, 255, 255, 0.1)'
							}
						}
					},
					interaction: {
						mode: 'nearest',
						axis: 'x',
						intersect: false
					}
				}
			};

			// Create chart
			this.charts.consentTrends = new Chart(ctx, config);

			// Update metadata display if exists
			this.updateMetadataDisplay(data.metadata);
		},

		/**
		 * Update metadata display
		 */
		updateMetadataDisplay( metadata ) {
			if ( ! metadata ) {
				return;
			}

			// Update total
			const $total = $('#slos-chart-total');
			if ( $total.length && metadata.total !== undefined ) {
				$total.text(metadata.total.toLocaleString());
			}

			// Update average
			const $average = $('#slos-chart-average');
			if ( $average.length && metadata.average !== undefined ) {
				$average.text(metadata.average.toLocaleString());
			}

			// Update interval label
			const $interval = $('#slos-chart-interval');
			if ( $interval.length && metadata.interval ) {
				$interval.text(metadata.interval.charAt(0).toUpperCase() + metadata.interval.slice(1));
			}
		},

		/**
		 * Initialize export buttons
		 */
		initExportButtons() {
			// CSV Export
			$('#slos-export-csv-btn').on('click', (e) => {
				e.preventDefault();
				const daysBack = $('#slos-export-days').val() || 30;
				const url = slosExport.exportCsvUrl + '&days_back=' + daysBack;
				window.location.href = url;
			});

			// PDF Export
			$('#slos-export-pdf-btn').on('click', (e) => {
				e.preventDefault();
				const daysBack = $('#slos-export-days').val() || 30;
				const url = slosExport.exportPdfUrl + '&days_back=' + daysBack;
				window.location.href = url;
			});

			// Audit Logs CSV Export
			$('#slos-export-audit-btn').on('click', (e) => {
				e.preventDefault();
				const daysBack = $('#slos-export-days').val() || 30;
				const url = slosExport.exportAuditUrl + '&days_back=' + daysBack;
				window.location.href = url;
			});
		},

		/**
		 * Initialize time range selector
		 */
		initTimeRangeSelector() {
			$('#slos-chart-range').on('change', (e) => {
				const daysBack = parseInt(e.target.value, 10);
				this.fetchTimeSeriesData({ days_back: daysBack });
			});

			// Group by selector
			$('#slos-chart-groupby').on('change', (e) => {
				const groupBy = e.target.value;
				const daysBack = parseInt($('#slos-chart-range').val(), 10) || 30;
				this.fetchTimeSeriesData({ days_back: daysBack, group_by: groupBy });
			});
		}
	};

	/**
	 * Initialize on document ready
	 */
	$(document).ready(function() {
		ComplianceCharts.init();
	});

})(jQuery);
