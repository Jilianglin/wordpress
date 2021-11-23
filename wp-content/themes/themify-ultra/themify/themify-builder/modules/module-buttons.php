<?php

defined( 'ABSPATH' ) || exit;

/**
 * Module Name: Button
 * Description: Display Button content
 */
class TB_Buttons_Module extends Themify_Builder_Component_Module{

	public function __construct(){
		self::$texts['label'] = __( 'Text', 'themify' );
		parent::__construct( array(
			'name' => __( 'Button', 'themify' ),
			'slug' => 'buttons'
		) );
	}
	
	public function get_icon(){
	    return 'mouse-alt';
	}
	
	public function get_title( $module )
	{
		return isset( $module['mod_settings']['mod_title_button'] ) ? wp_trim_words( $module['mod_settings']['mod_title_button'], 100 ) : '';
	}
	public function get_assets() {
	    return array(
		'css'=>THEMIFY_BUILDER_CSS_MODULES.$this->slug.'.css'
	    );
	}
	public function get_options()
	{

		return array(
			array(
				'id' => 'buttons_size',
				'label' => __( 'Size', 'themify' ),
				'type' => 'layout',
				'mode' => 'sprite',
				'options' => array(
					array( 'img' => 'normall_button', 'value' => 'normal', 'label' => __( 'Default', 'themify' ) ),
					array( 'img' => 'small_button', 'value' => 'small', 'label' => __( 'Small', 'themify' ) ),
					array( 'img' => 'large_button', 'value' => 'large', 'label' => __( 'Large', 'themify' ) ),
					array( 'img' => 'xlarge_button', 'value' => 'xlarge', 'label' => __( 'xLarge', 'themify' ) ),
				)
			),
			array(
				'id' => 'buttons_shape',
				'type' => 'layout',
				'mode' => 'sprite',
				'label' => __( 'Shape', 'themify' ),
				'options' => array(
					array( 'img' => 'normall_button', 'value' => 'normal', 'label' => __( 'Default', 'themify' ) ),
					array( 'img' => 'squared_button', 'value' => 'squared', 'label' => __( 'Squared', 'themify' ) ),
					array( 'img' => 'circle_button', 'value' => 'circle', 'label' => __( 'Circle', 'themify' ) ),
					array( 'img' => 'rounded_button', 'value' => 'rounded', 'label' => __( 'Rounded', 'themify' ) ),
				)
			),
			array(
				'id' => 'buttons_style',
				'type' => 'layout',
				'mode' => 'sprite',
				'label' => 'bg',
				'options' => array(
					array( 'img' => 'solid_button', 'value' => 'solid', 'label' => __( 'Solid', 'themify' ) ),
					array( 'img' => 'outline_button', 'value' => 'outline', 'label' => __( 'Outline', 'themify' ) ),
					array( 'img' => 'transparent_button', 'value' => 'transparent', 'label' => __( 'Transparent', 'themify' ) ),
				)
			),
			array(
				'id' => 'display',
				'type' => 'layout',
				'mode' => 'sprite',
				'label' => __( 'Display', 'themify' ),
				'options' => array(
					array( 'img' => 'horizontal_button', 'value' => 'buttons-horizontal', 'label' => __( 'Horizontal', 'themify' ) ),
					array( 'img' => 'vertical_button', 'value' => 'buttons-vertical', 'label' => __( 'Vertical', 'themify' ) ),
				)
			),
			array(
				'id' => 'alignment',
				'label' => __( 'Alignment', 'themify' ),
				'type' => 'icon_radio',
				'aligment2' => true
			),
			array(
				'id' => 'fullwidth_button',
				'type' => 'toggle_switch',
				'label' => __( 'Fullwidth', 'themify' ),
				'options' => array(
				    'on'=>array( 'name' => 'buttons-fullwidth' )
				),
				'binding' => array(
					'checked' => array(
						'hide' => array('alignment', 'display')
					),
					'not_checked' => array(
						'show' => array('alignment', 'display')
					)
				)
			),
			array(
				'id' => 'nofollow_link',
				'type' => 'toggle_switch',
				'label' => __( 'Nofollow', 'themify' ),
				'options' => array(
				    'on'=>array( 'name' => 'yes' )
				),
				'help' => __( "If nofollow is enabled, search engines won't crawl this link.", 'themify' ),
				'control' => false
			),
			array(
				'id' => 'download_link',
				'type' => 'toggle_switch',
				'label' => __( 'Download-able', 'themify' ),
				'options' => array(
				    'on'=>array( 'name' => 'yes')
				),
				'help' => __( 'Download link as file', 'themify' ),
				'control' => false
			),
			array(
				'id' => 'content_button',
				'type' => 'builder',
				'new_row' => __( 'Add new', 'themify' ),
				'options' => array(
					array(
						'id' => 'label',
						'type' => 'text',
						'label' => self::$texts['label'],
						'class' => 'fullwidth',
						'control' => array(
							'selector' => '.builder_button span'
						)
					),
					array(
						'id' => 'link',
						'type' => 'url',
						'label' => __( 'Link', 'themify' ),
						'class' => 'fullwidth',
						'binding' => array(
							'empty' => array(
								'hide' => array( 'link_options', 'button_color_bg','title' )
							),
							'not_empty' => array(
								'show' => array( 'link_options', 'button_color_bg','title' )
							)
						)
					),
					array(
						'id' => 'link_options',
						'type' => 'radio',
						'label' => 'o_l',
						'link_type' => true,
						'option_js' => true
					),
					array(
						'type' => 'multi',
						'label' => __( 'Lightbox Dimension', 'themify' ),
						'options' => array(
							array(
								'id' => 'lightbox_width',
								'type' => 'range',
								'label' => 'w',
								'control' => false,
								'units' => array(
									'px' => array(
										'max' => 3000
									),
									'%' => ''
								)
							),
							array(
								'id' => 'lightbox_height',
								'label' => 'ht',
								'control' => false,
								'type' => 'range',
								'units' => array(
									'px' => array(
										'max' => 3000
									),
									'%' => ''
								)
							)
						),
						'wrap_class' => 'tb_group_element_lightbox lightbox_size'
					),
					array(
						'id' => 'button_color_bg',
						'type' => 'layout',
						'label' => 'c',
						'class' => 'tb_colors',
						'mode' => 'sprite',
						'color' => true,
						'transparent' => true
					),
					array(
						'id' => 'icon',
						'type' => 'icon',
						'label' => __( 'Icon', 'themify' ),
						'class' => 'fullwidth',
						'binding' => array(
							'empty' => array(
								'hide' =>  'icon_alignment' 
							),
							'not_empty' => array(
								'show' => 'icon_alignment' 
							)
						)
					),
					array(
						'id' => 'icon_alignment',
						'type' => 'select',
						'label' => __( 'Icon Alignment', 'themify' ),
						'options' => array(
							'left' => __( 'Left', 'themify' ),
							'right' => __( 'Right', 'themify' )
						)
					),
                    array(
                        'id' => 'title',
                        'type' => 'text',
                        'label' => __( 'Title Attribute', 'themify' ),
                        'class' => 'fullwidth'
                    ),
				)
			),
			array(
				'id' => 'css_button',
				'type' => 'custom_css'
			),
			array( 'type' => 'custom_css_id' )
		);
	}

	public function get_live_default(){
		return array(
			'content_button' => array(
				array(
					'label' => __( 'Button Text', 'themify' ),
					'link' => 'https://themify.me/',
					'button_color_bg' => 'tb_default_color'
				)
			)
		);
	}

	public function get_styling(){
		$general = array(
			// Background
			self::get_expand( 'bg', array(
				self::get_tab( array(
					'n' => array(
						'options' => array(
							self::get_image( '.module' )
						)
					),
					'h' => array(
						'options' => array(
							self::get_image( '.module', 'b_i', 'bg_c', 'b_r', 'b_p', 'h' )
						)
					)
				) )
			) ),
			// Font
			self::get_expand( 'f', array(
				self::get_tab( array(
					'n' => array(
						'options' => array(
							self::get_font_family(' a'),
							self::get_color_type( array( ' i', ' span' ) ),
							self::get_font_size( array( ' i', ' span' ) ),
							self::get_line_height( array( ' i', ' span' ) ),
							self::get_letter_spacing( array( ' i', ' span' ) ),
							self::get_text_align(),
							self::get_text_transform(' span'),
							self::get_font_style(' a'),
							self::get_text_decoration( array( ' i', ' span' ), 'text_decoration_regular' ),
							self::get_text_shadow(' a')
						)
					),
					'h' => array(
						'options' => array(
							self::get_font_family( ' a', 'f_f', 'h' ),
							self::get_color_type( array( ' .module-buttons-item:hover i', ' .module-buttons-item:hover span' ), 'h' ),
							self::get_font_size( array( ' i', ' span' ), 'f_s', '', 'h' ),
							self::get_line_height( array( ' i', ' span' ), 'l_h', 'h' ),
							self::get_letter_spacing( array( ' i', ' span' ), 'l_s', 'h' ),
							self::get_text_align( '', 't_a', 'h' ),
							self::get_text_transform( ' span', 't_t', 'h' ),
							self::get_font_style( ' a', 'f_st', 'f_w', 'h' ),
							self::get_text_decoration( array( ' i', ' span' ), 't_d_r', 'h' ),
							self::get_text_shadow( ' a', 't_sh', 'h' )
						)
					)
				) )
			) ),
			// Padding
			self::get_expand( 'p', array(
				self::get_tab( array(
					'n' => array(
						'options' => array(
							self::get_padding()
						)
					),
					'h' => array(
						'options' => array(
							self::get_padding( '', 'p', 'h' )
						)
					)
				) )
			) ),
			// Margin
			self::get_expand( 'm', array(
				self::get_tab( array(
					'n' => array(
						'options' => array(
							self::get_margin(),
						)
					),
					'h' => array(
						'options' => array(
							self::get_margin( '', 'm', 'h' )
						)
					)
				) )
			) ),
			// Border
			self::get_expand( 'b', array(
				self::get_tab( array(
					'n' => array(
						'options' => array(
							self::get_border()
						)
					),
					'h' => array(
						'options' => array(
							self::get_border( '', 'b', 'h' )
						)
					)
				) )
			) ),
			// Filter
			self::get_expand('f_l',
				array(
					self::get_tab(array(
						'n' => array(
							'options' => self::get_blend()

						),
						'h' => array(
							'options' => self::get_blend('', '', 'h')
						)
					))
				)
			),
			// Width
			self::get_expand('w', array(
				self::get_tab(array(
					'n' => array(
						'options' => array(
							self::get_width('', 'w')
						)
					),
					'h' => array(
						'options' => array(
							self::get_width('', 'w', 'h')
						)
					)
				))
			)),
				// Height & Min Height
				self::get_expand('ht', array(
						self::get_height(),
						self::get_min_height(),
						self::get_max_height()
					)
				),
			// Rounded Corners
			self::get_expand( 'r_c', array(
					self::get_tab( array(
						'n' => array(
							'options' => array(
								self::get_border_radius()
							)
						),
						'h' => array(
							'options' => array(
								self::get_border_radius( '', 'r_c', 'h' )
							)
						)
					) )
				)
			),
			// Shadow
			self::get_expand( 'sh', array(
					self::get_tab( array(
						'n' => array(
							'options' => array(
								self::get_box_shadow()
							)
						),
						'h' => array(
							'options' => array(
								self::get_box_shadow( '', 'sh', 'h' )
							)
						)
					) )
				)
			),
			// Position
			self::get_expand('po', array( self::get_css_position())),
			// Display
			self::get_expand('disp', self::get_display())
		);

		$button_link = array(
			// Background
			self::get_expand( 'bg', array(
				self::get_tab( array(
					'n' => array(
						'options' => array(
							self::get_image( ' .module-buttons-item a', 'b_i', 'button_background_color', 'b_r', 'b_p' )
						)
					),
					'h' => array(
						'options' => array(
							self::get_image( ' .module-buttons-item a:hover', 'b_i_h', 'button_hover_background_color', 'b_r_h', 'b_p_h' )
						)
					)
				) )
			) ),
			// Link
			self::get_expand( 'l', array(
				self::get_tab( array(
					'n' => array(
						'options' => array(
							self::get_color( array( ' .module-buttons-item a', ' .module-buttons-item a i' ), 'link_color' ),
							self::get_text_decoration( array( ' .module-buttons-item a', ' .module-buttons-item a i', ' .module-buttons-item span' ) )
						)
					),
					'h' => array(
						'options' => array(
							self::get_color( array( ' .module-buttons-item a:hover', ' .module-buttons-item a:hover i' ), 'link_color_hover', null, null, '' ),
							self::get_text_decoration( array( ' .module-buttons-item a:hover', ' .module-buttons-item a:hover i', ' .module-buttons-item:hover span' ), 't_d_h', '' )
						)
					)
				) )
			) ),
			// Padding
			self::get_expand( 'p', array(
				self::get_tab( array(
					'n' => array(
						'options' => array(
							self::get_padding( ' .module-buttons-item a', 'padding_link' )
						)
					),
					'h' => array(
						'options' => array(
							self::get_padding( ' .module-buttons-item a', 'p_l', 'h' )
						)
					)
				) )
			) ),
			// Margin
			self::get_expand( 'm', array(
				self::get_tab( array(
					'n' => array(
						'options' => array(
							self::get_margin( ' .module-buttons-item a', 'link_margin' )
						)
					),
					'h' => array(
						'options' => array(
							self::get_margin( ' .module-buttons-item a', 'l_m', 'h' )
						)
					)
				) )
			) ),
			// Border
			self::get_expand( 'b', array(
				self::get_tab( array(
					'n' => array(
						'options' => array(
							self::get_border( '.module .module-buttons-item a', 'link_border' )
						)
					),
					'h' => array(
						'options' => array(
							self::get_border( '.module .module-buttons-item a', 'l_b', 'h' )
						)
					)
				) )
			) ),
			// Rounded Corners
			self::get_expand('r_c', array(
					self::get_tab(array(
						'n' => array(
							'options' => array(
								self::get_border_radius(' .module-buttons-item a', 'l_b_r_c')
							)
						),
						'h' => array(
							'options' => array(
								self::get_border_radius(' .module-buttons-item a', 'l_b_r_c', 'h')
							)
						)
					))
				)
			),
			// Shadow
			self::get_expand('sh', array(
					self::get_tab(array(
						'n' => array(
							'options' => array(
								self::get_box_shadow(' .module-buttons-item a', 'l_b_sh')
							)
						),
						'h' => array(
							'options' => array(
								self::get_box_shadow(' .module-buttons-item a', 'l_b_sh', 'h')
							)
						)
					))
				)
			)
		);

		$button_icon = array(
			// Background
			self::get_expand( 'bg', array(
				self::get_tab( array(
					'n' => array(
						'options' => array(
							self::get_image( ' .module-buttons-item em', 'bic_b_i', 'bic_b_c', 'bic_b_r', 'bic_b_p' )
						)
					),
					'h' => array(
						'options' => array(
							self::get_image( ' .module-buttons-item:hover em', 'bic_b_i_h', 'bic_h_b_c', 'bic_b_r_h', 'bic_b_p_h' )
						)
					)
				) )
			) ),
			// Font
			self::get_expand('f', array(
				self::get_tab(array(
					'n' => array(
					'options' => array(
						self::get_color(' .module-buttons-item em', 'b_c_bic'),
						self::get_font_size(' .module-buttons-item em', 'f_s_bic')
					)
					),
					'h' => array(
					'options' => array(
						self::get_color(' .module-buttons-item:hover em', 'f_c_h_bic', null, null, ''),
						self::get_font_size(' .module-buttons-item:hover em', 'f_s_h_bic', '', '')
					)
					)
				))
			)),
			// Padding
			self::get_expand( 'p', array(
				self::get_tab( array(
					'n' => array(
						'options' => array(
							self::get_padding( ' .module-buttons-item em', 'p_i_bic' )
						)
					),
					'h' => array(
						'options' => array(
							self::get_padding( ' .module-buttons-item em', 'p_i_bic', 'h' )
						)
					)
				) )
			) ),
			// Margin
			self::get_expand( 'm', array(
				self::get_tab( array(
					'n' => array(
						'options' => array(
							self::get_margin( ' .module-buttons-item em', 'm_i_bic' )
						)
					),
					'h' => array(
						'options' => array(
							self::get_margin( ' .module-buttons-item em', 'm_i_bic', 'h' )
						)
					)
				) )
			) ),
			// Border
			self::get_expand( 'b', array(
				self::get_tab( array(
					'n' => array(
						'options' => array(
							self::get_border( ' .module-buttons-item em', 'b_i_bic' )
						)
					),
					'h' => array(
						'options' => array(
							self::get_border( ' .module-buttons-item em', 'b_i_bic', 'h' )
						)
					)
				) )
			) ),
			// Rounded Corners
			self::get_expand('r_c', array(
					self::get_tab(array(
						'n' => array(
							'options' => array(
								self::get_border_radius(' .module-buttons-item em', 'rc_i_bic')
							)
						),
						'h' => array(
							'options' => array(
								self::get_border_radius(' .module-buttons-item em', 'rc_i_bic', 'h')
							)
						)
					))
				)
			),
			// Shadow
			self::get_expand('sh', array(
					self::get_tab(array(
						'n' => array(
							'options' => array(
								self::get_box_shadow(' .module-buttons-item em', 'sh_i_bic')
							)
						),
						'h' => array(
							'options' => array(
								self::get_box_shadow(' .module-buttons-item em', 'sh_i_bic', 'h')
							)
						)
					))
				)
			)
		);

		return array(
			'type' => 'tabs',
			'options' => array(
				'g' => array(
					'options' => $general
				),
				'b' => array(
					'label' => __( 'Button Link', 'themify' ),
					'options' => $button_link
				),
				'b_ic' => array(
					'label' => __( 'Icon', 'themify' ),
					'options' => $button_icon
				)
			)
		);
	}

	protected function _visual_template(){
		?>
        <# var downloadLink = data.download_link == 'yes'? ' download' : '',
			alignment = (!data.alignment || 'undefined' == data.alignment )? '' : 'tf_text'+data.alignment[0],
			display=data.display;
			if(data.fullwidth_button){
				alignment=display='';
			}
        #>
        <div class="module module-<?php echo $this->slug; ?> {{ data.css_button }} {{ alignment }} {{ data.buttons_size!=='normal'?data.buttons_size:'' }} {{ data.buttons_style }} {{ data.buttons_shape!=='normal'?data.buttons_shape:'' }} {{ display }} {{ data.fullwidth_button }}">
            <# if ( data.content_button ) {
		_.each( data.content_button, function( item) { #>
                <div class="module-buttons-item tf_inline_b">
                    <# if ( item.link ) { 
					if(item.title){
						downloadLink+= ' title="'+item.title+'"';
					}
                    var color=(!item.button_color_bg || item.button_color_bg=='default') ? 'tb_default_color' : item.button_color_bg,
						title = !item.title? '' : "title='"+item.title+"'";
					#>
                    <a class="ui builder_button {{ color }}" href="{{ item.link }}" {{downloadLink}} <# print(title)#>>
                        <# }
                        if ( item.icon && (!item.icon_alignment || item.icon_alignment !== 'right') ) { #>
                        <em class="tf_inline_b tf_vmiddle"><# print(tb_app.Utils.getIcon(item.icon).outerHTML)#></em>
                        <# } #>
                        <span class="tf_inline_b tf_vmiddle" contenteditable="false" data-name="label" data-repeat="content_button">{{{ item.label }}}</span>
                        <# if ( item.icon && item.icon_alignment === 'right' ) { #>
                        <em><# print(tb_app.Utils.getIcon(item.icon).outerHTML)#></em>
                        <# }
                        if ( item.link ) { #>
                    </a>
                    <# } #>
                </div>
                <# } );
            } #>
        </div>
		<?php
	}

}

///////////////////////////////////////
// Module Options
///////////////////////////////////////
Themify_Builder_Model::register_module( 'TB_Buttons_Module' );
