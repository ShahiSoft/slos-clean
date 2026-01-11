module.exports = {
	extends: ['plugin:@wordpress/eslint-plugin/recommended'],
	rules: {
		'no-console': 'error',
		'no-alert': 'error',
	},
	env: {
		browser: true,
		es6: true,
	},
};
