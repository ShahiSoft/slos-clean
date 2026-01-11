/**
 * SLOS Embed Placeholder Block
 *
 * Gutenberg block for inserting consent-blocked embeds.
 *
 * @package ShahiLegalFlowSuite
 * @since 3.1.1
 */

(function (blocks, element, blockEditor, components, i18n) {
	const { registerBlockType }                                      = blocks;
	const { createElement: el }                                      = element;
	const { InspectorControls }                                      = blockEditor;
	const { PanelBody, SelectControl, TextControl, TextareaControl } = components;
	const { __ } = i18n;

	/**
	 * Register SLOS Embed Placeholder block
	 */
	registerBlockType(
		'slos/embed-placeholder',
		{
			title: __( 'SLOS Embed Placeholder', 'shahi-legalflowsuite' ),
			description: __( 'Insert a consent-blocked embed that requires user permission.', 'shahi-legalflowsuite' ),
			icon: 'video-alt3',
			category: 'embed',
			keywords: [
			__( 'consent', 'shahi-legalflowsuite' ),
			__( 'privacy', 'shahi-legalflowsuite' ),
			__( 'embed', 'shahi-legalflowsuite' ),
			__( 'placeholder', 'shahi-legalflowsuite' ),
			],
			attributes: {
				category: {
					type: 'string',
					default: 'marketing',
				},
				url: {
					type: 'string',
					default: '',
				},
				title: {
					type: 'string',
					default: '',
				},
				message: {
					type: 'string',
					default: '',
				},
				width: {
					type: 'string',
					default: '100%',
				},
				height: {
					type: 'string',
					default: '400',
				},
			},

			/**
			 * Edit function - Renders block in the editor
			 */
			edit: function (props) {
				const { attributes, setAttributes }                    = props;
				const { category, url, title, message, width, height } = attributes;

				return el(
					'div',
					{},
					// Inspector Controls (Sidebar)
					el(
						InspectorControls,
						{},
						el(
							PanelBody,
							{
								title: __( 'Embed Settings', 'shahi-legalflowsuite' ),
								initialOpen: true,
							},
							// Category selector
							el(
								SelectControl,
								{
									label: __( 'Consent Category', 'shahi-legalflowsuite' ),
									value: category,
									options: [
									{
										label: __( 'Marketing', 'shahi-legalflowsuite' ),
										value: 'marketing',
									},
									{
										label: __( 'Analytics', 'shahi-legalflowsuite' ),
										value: 'analytics',
									},
									{
										label: __( 'Functional', 'shahi-legalflowsuite' ),
										value: 'functional',
									},
									{
										label: __( 'Preferences', 'shahi-legalflowsuite' ),
										value: 'preferences',
									},
									{
										label: __( 'Necessary', 'shahi-legalflowsuite' ),
										value: 'necessary',
									},
									],
									onChange: function (newCategory) {
										setAttributes( { category: newCategory } );
									},
								}
							),
							// URL input
							el(
								TextControl,
								{
									label: __( 'Embed URL', 'shahi-legalflowsuite' ),
									value: url,
									placeholder: 'https://www.youtube.com/embed/VIDEO_ID',
									onChange: function (newUrl) {
										setAttributes( { url: newUrl } );
									},
									help: __(
										'The URL of the embed content (e.g., YouTube, Vimeo).',
										'shahi-legalflowsuite'
									),
								}
							),
							// Width input
							el(
								TextControl,
								{
									label: __( 'Width', 'shahi-legalflowsuite' ),
									value: width,
									placeholder: '100%',
									onChange: function (newWidth) {
										setAttributes( { width: newWidth } );
									},
									help: __(
										'Width in pixels or percentage (e.g., 100% or 640).',
										'shahi-legalflowsuite'
									),
								}
							),
							// Height input
							el(
								TextControl,
								{
									label: __( 'Height', 'shahi-legalflowsuite' ),
									value: height,
									placeholder: '400',
									onChange: function (newHeight) {
										setAttributes( { height: newHeight } );
									},
									help: __(
										'Height in pixels (e.g., 400).',
										'shahi-legalflowsuite'
									),
								}
							)
						),
						el(
							PanelBody,
							{
								title: __( 'Custom Messages', 'shahi-legalflowsuite' ),
								initialOpen: false,
							},
							// Title input
							el(
								TextControl,
								{
									label: __( 'Custom Title', 'shahi-legalflowsuite' ),
									value: title,
									placeholder: __( 'Content Blocked', 'shahi-legalflowsuite' ),
									onChange: function (newTitle) {
										setAttributes( { title: newTitle } );
									},
									help: __(
										'Leave empty to use default title.',
										'shahi-legalflowsuite'
									),
								}
							),
							// Message input
							el(
								TextareaControl,
								{
									label: __( 'Custom Message', 'shahi-legalflowsuite' ),
									value: message,
									placeholder: __(
										'This content requires Marketing cookies to be enabled.',
										'shahi-legalflowsuite'
									),
								onChange: function (newMessage) {
									setAttributes( { message: newMessage } );
								},
									help: __(
										'Leave empty to use default message.',
										'shahi-legalflowsuite'
									),
								}
							)
						)
					),
					// Block preview in editor
					el(
						'div',
						{
							className: 'slos-embed-placeholder-editor',
							style: {
								border: '2px dashed #3b82f6',
								borderRadius: '8px',
								padding: '40px 20px',
								textAlign: 'center',
								background: 'linear-gradient(135deg, #1e293b 0%, #0f172a 100%)',
								color: '#ffffff',
								minHeight: height ? (height + 'px') : '400px',
							},
						},
						el(
							'div',
							{
								style: {
									fontSize: '48px',
									marginBottom: '20px',
									opacity: 0.5,
								},
							},
							'🎬'
						),
						el(
							'h3',
							{
								style: {
									fontSize: '18px',
									fontWeight: '600',
									marginBottom: '12px',
								},
							},
							title || __( 'Content Blocked', 'shahi-legalflowsuite' )
						),
						el(
							'p',
							{
								style: {
									fontSize: '14px',
									color: 'rgba(255, 255, 255, 0.8)',
									marginBottom: '20px',
								},
							},
							message ||
							__(
								'This content requires consent to be enabled.',
								'shahi-legalflowsuite'
							)
						),
						el(
							'div',
							{
								style: {
									display: 'inline-block',
									padding: '12px 24px',
									background: '#3b82f6',
									color: '#ffffff',
									borderRadius: '8px',
									fontSize: '14px',
									fontWeight: '600',
									marginBottom: '16px',
								},
							},
							__( 'Enable ', 'shahi-legalflowsuite' ) +
							category.charAt( 0 ).toUpperCase() +
							category.slice( 1 ) +
							__( ' Cookies', 'shahi-legalflowsuite' )
						),
						el(
							'div',
							{
								style: {
									fontSize: '12px',
									color: 'rgba(255, 255, 255, 0.6)',
								},
							},
							__( 'Category: ', 'shahi-legalflowsuite' ) +
							category.toUpperCase() +
							(url ? ' | ' + __( 'URL: ', 'shahi-legalflowsuite' ) + url : '')
						)
					)
				);
			},

			/**
			 * Save function - Returns the shortcode for the frontend
			 */
			save: function (props) {
				const { attributes }                                   = props;
				const { category, url, title, message, width, height } = attributes;

				// Build shortcode attributes
				let shortcodeAttrs = 'category="' + category + '"';
				if (url) {
					shortcodeAttrs += ' url="' + url + '"';
				}
				if (title) {
					shortcodeAttrs += ' title="' + title + '"';
				}
				if (message) {
					shortcodeAttrs += ' message="' + message + '"';
				}
				if (width && width !== '100%') {
					shortcodeAttrs += ' width="' + width + '"';
				}
				if (height && height !== '400') {
					shortcodeAttrs += ' height="' + height + '"';
				}

				// Return the shortcode wrapped in a div
				return el(
					'div',
					{
						className: 'slos-embed-placeholder-block',
					},
					'[slos_embed_placeholder ' + shortcodeAttrs + ']'
				);
			},
		}
	);
})(
	window.wp.blocks,
	window.wp.element,
	window.wp.blockEditor,
	window.wp.components,
	window.wp.i18n
);
