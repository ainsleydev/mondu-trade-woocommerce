const { registerBlockType } = wp.blocks;
const { __ } = wp.i18n;

registerBlockType('mondu-trade/trade-account-form', {
	title: __('Mondu Trade Account Form', 'mondu-digital-trade-account'),
	description: __('Allows users to sign up for a Mondu Trade Account', 'mondu-digital-trade-account'),
	category: 'widgets',
	icon: 'admin-users',
	supports: {
		align: false,
	},
	attributes: {},
	edit: function(props) {
		return wp.element.createElement(
			'div',
			{ className: 'mondu-trade-account-block-preview' },
			wp.element.createElement('p', null, 'Mondu Trade Account Signup Form')
		);
	},
	save: function() {
		return null; // Rendered server-side
	}
});
