var __ = wp.i18n.__;
var el = wp.element.createElement;

const { Fragment } = wp.element;
const { InspectorControls } = wp.editor;
const { Panel, PanelBody, PanelRow, ToggleControl } = wp.components;

wp.blocks.registerBlockType(
	'compact-chronological/block',
	{
		title: 'Compact & Chronological',
		category: 'widgets',
//		keywords: [ __( 'calendar', 'compact-chronological' ), __( 'archives', 'compact-chronological' ) ],
		attributes: {
			showPostCount: { type: 'bool', default: 'true' } 
		},
		edit: function( props ) {
			return( [
				el(
					'p',
					{ className: props.className, style: { backgroundColor: '#eee', padding: '20px' } },
					'Compact & Chronological'
				),
				el(
					InspectorControls,
					null,
					el(
						PanelBody,
						{
							title: "Appearance"
						},
						el(
							PanelRow,
							null,
							el(
								ToggleControl,
								{
									label: 'Show post count',
									checked: props.attributes.showPostCount,
									instanceId: 'compact-chronological-show-post-count',
									onChange: function( value ) {
										props.setAttributes( { showPostCount: !props.attributes.showPostCount } );
									},
								}
							)
						)
					)
				)
			] );
		},
		save: function( props ) {
			return null;
		}
	}
);