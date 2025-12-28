/* Consent & Compliance Admin JS */
(function ($) {
	'use strict';

	const cfg = window.slosConsentAdmin || {};
	const state = {
		consents: [],
		stats: { by_type: {}, by_status: {} },
		chart: null,
		limit: 25,
		filters: {
			period: '30d',
			type: '',
			status: '',
			search: '',
		},
	};

	const headers = {
		'X-WP-Nonce': cfg.nonce || '',
		'Content-Type': 'application/json',
	};

	function buildQuery(params) {
		const usp = new URLSearchParams(params);
		return usp.toString() ? `?${usp.toString()}` : '';
	}

	async function fetchJson(url, params = {}) {
		const query = buildQuery(params);
		const res = await fetch(url + query, { headers });
		if (!res.ok) {
			throw new Error(`Request failed: ${res.status}`);
		}
		return res.json();
	}

	function updateCounters() {
		const byStatus = state.stats.by_status || {};
		const total = Object.values(byStatus).reduce((acc, val) => acc + Number(val || 0), 0);
		$('[data-key="total"]').text(total);
		$('[data-key="accepted"]').text(byStatus.accepted || 0);
		$('[data-key="rejected"]').text(byStatus.rejected || 0);
		$('[data-key="withdrawn"]').text(byStatus.withdrawn || 0);
		$('[data-key="types"]').text(Object.keys(state.stats.by_type || {}).length);
	}

	function renderLegend(target, data, palette) {
		const container = $(target);
		container.empty();
		const entries = Object.entries(data || {});
		if (!entries.length) {
			container.append(`<div class="legend-item">${cfg.i18n?.noData || 'No data'}</div>`);
			return;
		}
		entries.forEach(([label, value], idx) => {
			const color = palette[idx % palette.length];
			container.append(
				`<div class="legend-item"><span class="swatch" style="background:${color}"></span><div><div>${label}</div><small>${value} ${cfg.i18n?.lastUpdated || ''}</small></div></div>`
			);
		});
	}

	function renderChart() {
		const canvasTarget = document.getElementById('slos-consent-chart-types');
		if (!canvasTarget) {
			return;
		}

		const labels = Object.keys(state.stats.by_type || {});
		const values = Object.values(state.stats.by_type || {});
		const colors = ['#93c5fd', '#10b981', '#f59e0b', '#38bdf8', '#ec4899', '#a3e635'];

		if (state.chart) {
			state.chart.destroy();
		}

		state.chart = new window.Chart(canvasTarget, {
			type: 'doughnut',
			data: {
				labels,
				datasets: [
					{
						data: values,
						backgroundColor: colors.slice(0, labels.length),
						borderWidth: 0,
						hoverOffset: 4,
					},
				],
			},
			options: {
				plugins: {
					legend: { display: false },
				},
				cutout: '58%',
			},
		});

		renderLegend('#slos-consent-legend-types', state.stats.by_type, colors);
	}

	function filterConsents() {
		const { type, status, search } = state.filters;
		return state.consents.filter((item) => {
			const matchesType = type ? item.type === type : true;
			const matchesStatus = status ? item.status === status : true;
			const matchesSearch = search
				? `${item.user_id} ${item.type} ${item.status}`.toLowerCase().includes(search.toLowerCase())
				: true;
			return matchesType && matchesStatus && matchesSearch;
		});
	}

	function renderTable(consents) {
		const tbody = $('#slos-consent-table tbody');
		tbody.empty();

		if (!consents.length) {
			tbody.append(
				`<tr class="empty"><td colspan="7"><div class="shahi-empty-state"><span class="dashicons dashicons-visibility"></span><p>${cfg.i18n?.noData || 'No consent data available for this view.'}</p></div></td></tr>`
			);
			return;
		}

		consents.slice(0, state.limit).forEach((consent) => {
			tbody.append(
				`<tr data-id="${consent.id}">` +
				`<td>#${consent.id}</td>` +
				`<td>${consent.user_id}</td>` +
				`<td><span class="badge neutral">${consent.type}</span></td>` +
				`<td><span class="badge state-${consent.status}">${consent.status}</span></td>` +
				`<td>${consent.created_at || ''}</td>` +
				`<td>${consent.updated_at || ''}</td>` +
				`<td><button class="link" data-action="view" data-id="${consent.id}">${cfg.i18n?.view || 'View'}</button></td>` +
				`</tr>`
			);
		});
	}

	function hydrateUI() {
		updateCounters();
		renderChart();
		renderTable(filterConsents());
	}

	async function loadStats() {
		try {
			const json = await fetchJson(cfg.routes.stats, {});
			if (json?.success) {
				state.stats = json.data;
			}
		} catch (err) {
			console.warn('Consent stats error', err);
		}
	}

	async function loadConsents() {
		try {
			const json = await fetchJson(cfg.routes.consents, { per_page: 100 });
			if (json?.success) {
				state.consents = json.data.consents || [];
			}
		} catch (err) {
			console.warn('Consent fetch error', err);
		}
	}

	function bindFilters() {
		$('#slos-consent-period').on('change', function () {
			state.filters.period = $(this).val();
		});
		$('#slos-consent-type').on('change', function () {
			state.filters.type = $(this).val();
			hydrateUI();
		});
		$('#slos-consent-status').on('change', function () {
			state.filters.status = $(this).val();
			hydrateUI();
		});
		$('#slos-consent-search').on('input', function () {
			state.filters.search = $(this).val();
			hydrateUI();
		});
		$('.mini-filters .chip').on('click', function () {
			$('.mini-filters .chip').removeClass('active');
			$(this).addClass('active');
			state.limit = Number($(this).data('limit')) || 25;
			hydrateUI();
		});
		$('#slos-consent-refresh').on('click', async function () {
			$(this).prop('disabled', true);
			await Promise.all([loadConsents(), loadStats()]);
			hydrateUI();
			$(this).prop('disabled', false);
		});
		$('#slos-consent-export').on('click', function () {
			exportCsv(filterConsents());
		});
	}

	function exportCsv(rows) {
		if (!rows.length) {
			return;
		}
		const headers = ['id', 'user_id', 'type', 'status', 'created_at', 'updated_at'];
		const csv = [headers.join(',')]
			.concat(rows.map((row) => headers.map((h) => (row[h] ?? '')).join(',')))
			.join('\n');
		const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
		const url = URL.createObjectURL(blob);
		const link = document.createElement('a');
		link.href = url;
		link.download = 'consents.csv';
		link.click();
		URL.revokeObjectURL(url);
	}

	async function init() {
		if (!cfg.routes || !cfg.routes.consents) {
			return;
		}
		bindFilters();
		await Promise.all([loadConsents(), loadStats()]);
		hydrateUI();
	}

	$(init);
})(jQuery);
