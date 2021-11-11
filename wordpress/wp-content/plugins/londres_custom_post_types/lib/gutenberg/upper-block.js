/**
 *
 * BLOCK: UPPER WPBAKERY ELEMENT
 *
 
 comments:
 
 	well we had to change the getReusableBlock[s] functions as they will exist no more.
 	and the __experimentalGetReuableBlock[s] will also be gonners. 
 	it will do for now.
 
**/
window.upper_blocks_props = [];
var upper_blocks_parser = [];

window.upper_intervals = {
	shortcode_first : false,
	shortcode : false,
	dragging_block : false,
	block : false,
	block_clone : false,
	block_reusable : false
};

var upper_add_new_block, upper_add_other_block, upper_add_clone, upper_add_clone_of, upper_hovering_blocks, upper_skip_reusables, upper_convert_normal_to_reusable = false;

( function() {
	var __ = wp.i18n.__; // The __() for internationalization.
	var el = wp.element.createElement,
		registerBlockType = wp.blocks.registerBlockType,
		block_name = '[UPPER] WPBakery Element]';
	var upper_rbt = registerBlockType( 'upper/upper-wpb-element', {
		title: block_name,
		icon: 'welcome-widgets-menus',
		category: 'common',
		attributes : {
			content : {
				type: 'array',
				source: 'children',
				selector: 'p',
			}
		},

		edit: function( props ) {
			
			window.current_block_id = props.clientId;
			if (typeof window.upper_blocks_props[ window.current_block_id ] == "undefined") window.upper_blocks_props[ window.current_block_id ] = [];
			window.upper_blocks_props[ window.current_block_id ]['props'] = props;
						
			if ( typeof window.upper_blocks_props[ window.current_block_id ]['init'] == "undefined" ){

				if (jQuery('body').hasClass('upper-blocks-ready')){
										
					if (upper_add_new_block){

						jQuery('#vc_no-content-add-element').trigger('click'); 
						jQuery('#vc_ui-panel-add-element').addClass('upper_temp_allow');
						
						upper_add_new_block = false;
						try{ clearInterval( window.upper_intervals['block'] ); } catch(e){ console.log(e); }
						
						window.upper_intervals['block'] = setInterval(function(){
							if ( wp.data.select('core/editor').getSelectedBlock() ){
								
								clearInterval( window.upper_intervals['block'] );
	
								window.upper_blocks_props[ window.current_block_id ]['props'].attributes.init = true;
								window.upper_blocks_props[ window.current_block_id ]['props'].attributes.upper_shortcode_output = "";
								window.upper_blocks_props[ window.current_block_id ]['init'] = true;
								
								jQuery(window).trigger('resize');
								
								window.upper_current_block_id = jQuery('.editor-block-list__block.is-selected').attr('id');
								
							}
						}, 100);
						
					} else if (upper_add_other_block){
						upper_add_other_block = false;
						
						try{ clearInterval( window.upper_intervals['block_reusable'] ); } catch(e){ console.log(e); }
						window.upper_intervals['block_reusable'] = setInterval(function(){
					
							if ( wp.data.select('core/editor').getSelectedBlock() ){
								
								clearInterval( window.upper_intervals['block_reusable'] );
								
								jQuery(window).trigger('resize');
								
								window.upper_current_block_id = jQuery('.editor-block-list__block.is-selected').attr('id');
								
								var element = jQuery('#' + window.upper_current_block_id);
									element.data('upper-from-start', true);
								
								var paramsRegex = /\s+(?:(?!\=")(?:.|\n))+\="(?:(?!")(?:.|\n))+"/g,
									shortcode_output = element.find('p.wp-block-upper-upper-wpb-element').text().slice(1, -1),
									shortcode_parts = shortcode_output.match(paramsRegex),
									shortcode = shortcode_output.split(" ")[0].trim(),
									params = [];
									
								if (typeof shortcode_parts != "null" && shortcode_parts.length){
									for (var i=0; i<shortcode_parts.length; i++){
										let aux_param = shortcode_parts[i].split('="');
										params[ aux_param[0].trim() ] = aux_param[1].replace('"','');
									}
								}
								
								var newVCShortcode = window.vc.shortcodes.create({ shortcode:shortcode, params:params });
								element.attr( 'data-upper-related-vc-shortcode-id', newVCShortcode.attributes.id );
								window.upper_blocks_props[ window.current_block_id ]['vc_shortcode_id'] = newVCShortcode.attributes.id;
								
								if (typeof window.upper_blocks_props[ window.upper_current_block_id.replace( 'block-', '' ) ] == "undefined")
									window.upper_blocks_props[ window.upper_current_block_id.replace( 'block-', '' ) ] = [];
									
								if (typeof window.upper_blocks_props[ window.current_block_id ]['props'] != "undefined")
									window.upper_blocks_props[ window.upper_current_block_id.replace( 'block-', '' ) ]['props'] = window.upper_blocks_props[ window.current_block_id ]['props'];
								
								if (typeof window.upper_blocks_props[ window.current_block_id ]['vc_shortcode_id'] != "undefined")
									window.upper_blocks_props[ window.upper_current_block_id.replace( 'block-', '' ) ]['vc_shortcode_id'] = window.upper_blocks_props[ window.current_block_id ]['vc_shortcode_id'];
									
								window.upper_blocks_props[ window.upper_current_block_id.replace( 'block-', '' ) ]['init'] = true;
								
								delete window.upper_blocks_props[ window.current_block_id ];
								
								window.upper_blocks_props[ window.upper_current_block_id.replace( 'block-', '' ) ]['props'].attributes.upper_shortcode_output = shortcode_output;
								window.upper_blocks_props[ window.upper_current_block_id.replace( 'block-', '' ) ]['props'].attributes.content = shortcode_output;
								
								window.upper_blocks_props[ window.upper_current_block_id.replace( 'block-', '' ) ]['vc_shortcode_id'] = newVCShortcode.attributes.id;
								window.upper_blocks_props[ window.upper_current_block_id.replace( 'block-', '' ) ]['init'] = true;
								
								setTimeout(function(){
					
									var vc_wrapper = jQuery('#visual_composer_content div[data-model-id="'+newVCShortcode.attributes.id+'"] .wpb_element_wrapper');
									
									if (vc_wrapper){
										var vc_shortcode_header = vc_wrapper.clone(true,true);
											vc_shortcode_header.find('a, .vc_admin_label:not(.admin_label_type)').remove();
										
										element.find('p.wp-block-upper-upper-wpb-element').addClass('hidden_bc_of_vc_description').siblings().remove();
										element.find('p.wp-block-upper-upper-wpb-element').before( vc_shortcode_header ).parent().addClass('upper-shortcode-ready');
									}
									
									element.find( '.editor-block-list__block-edit' ).addClass( 'upper-editable-shortcode-block' );
									
								}, 1000);
								
							}
							
						}, 100);
						
					} else if (upper_add_clone){
						upper_add_clone = false;

						try{ clearInterval( window.upper_intervals['block_clone'] ); } catch(e){ console.log(e); }
						window.upper_intervals['block_clone'] = setInterval(function(){

							if ( wp.data.select('core/editor').getSelectedBlock() && !jQuery('form.reusable-block-edit-panel').length ){
								
								clearInterval( window.upper_intervals['block_clone'] );
								
								var element = jQuery( '#block-' + wp.data.select('core/editor').getSelectedBlock().clientId );
				
								if (typeof upper_blocks_props[ wp.data.select('core/editor').getSelectedBlock().clientId ] == "undefined")
									upper_blocks_props[ wp.data.select('core/editor').getSelectedBlock().clientId ] = [];
									
								if (typeof upper_blocks_props[window.current_block_id]['props'] != undefined)
									upper_blocks_props[ wp.data.select('core/editor').getSelectedBlock().clientId ]['props'] = upper_blocks_props[window.current_block_id]['props'];
								
								if (typeof upper_blocks_props[window.current_block_id]['vc_shortcode_id'] != undefined)
									upper_blocks_props[ wp.data.select('core/editor').getSelectedBlock().clientId ]['vc_shortcode_id'] = upper_blocks_props[window.current_block_id]['vc_shortcode_id'];
									
								upper_blocks_props[ wp.data.select('core/editor').getSelectedBlock().clientId ]['init'] = true;
								
								delete upper_blocks_props[window.current_block_id];
				
								element.data('upper-from-start', true);
								
								var paramsRegex = /\s+(?:(?!\=")(?:.|\n))+\="(?:(?!")(?:.|\n))+"/g,
									shortcode_output = element.find('p.wp-block-upper-upper-wpb-element') ? element.find('p.wp-block-upper-upper-wpb-element').text().slice(1, -1) : '',
									shortcode_parts = shortcode_output.match(paramsRegex),
									shortcode = shortcode_output.split(" ")[0].trim(),
									params = [];
									
								if (shortcode_parts.length){
									for (var i=0; i<shortcode_parts.length; i++){
										let aux_param = shortcode_parts[i].split('="');
										params[ aux_param[0].trim() ] = aux_param[1].replace('"','');
									}
								}
								
								var newVCShortcode = window.vc.shortcodes.create({ shortcode:shortcode, params:params });
								element.attr( 'data-upper-related-vc-shortcode-id', newVCShortcode.attributes.id ).find( '.editor-block-list__block-edit' ).addClass( 'upper-editable-shortcode-block' );
								
								window.upper_blocks_props[ wp.data.select('core/editor').getSelectedBlock().clientId ] = [];
								window.upper_blocks_props[ wp.data.select('core/editor').getSelectedBlock().clientId ]['props'] = [];
								window.upper_blocks_props[ wp.data.select('core/editor').getSelectedBlock().clientId ]['vc_shortcode_id'] = newVCShortcode.attributes.id;
								
								setTimeout(function(){
					
									var vc_wrapper = jQuery('#visual_composer_content div[data-model-id="'+newVCShortcode.attributes.id+'"] .wpb_element_wrapper');
									
									if (vc_wrapper){
										var vc_shortcode_header = vc_wrapper.clone(true,true);
											vc_shortcode_header.find('a, .vc_admin_label:not(.admin_label_type)').remove();
										
										element.find('p.wp-block-upper-upper-wpb-element').addClass('hidden_bc_of_vc_description').siblings().remove();
										element.find('p.wp-block-upper-upper-wpb-element').before( vc_shortcode_header ).parent().addClass('upper-shortcode-ready');
									}
									
								}, 1000);
								
								upper_add_clone_of = false;
								
							} 
						}, 100);
						
					} else {
						setTimeout( function(){ upper_init_reusables(); },0 );
					}
				} 
			} else {
				setTimeout(function(){ upper_init_reusables(); }, 10);
			}

			var content = typeof props.attributes.content == "object" && props.attributes.content[0] != "" ? props.attributes.content[0] : false;
			if (!content) content = props.attributes.content != "" ? props.attributes.content : false;
			if (!content) content = props.attributes.upper_shortcode_output && props.attributes.upper_shortcode_output != "" ? props.attributes.upper_shortcode_output : false;
			if (!content) content = '[ UPPER WPBAKERY ELEMENT ]';

			return el(
				'p', // Tag type.
				{ className: props.className },
				content
			);
		},
	
		save: function( props, fromedit = false ) {
			
			var content = typeof props.attributes.content == "object" && props.attributes.content[0] != "" ? props.attributes.content[0] : false;
			if (!content) content = props.attributes.content != "" ? props.attributes.content : false;
			if (!content) content = props.attributes.upper_shortcode_output && props.attributes.upper_shortcode_output != "" ? props.attributes.upper_shortcode_output : false;
			if (!content) content = '[ UPPER WPBAKERY ELEMENT ]';
			
			return el(
				'p', // Tag type.
				{ className: props.className }, 
				content
			);
		}
	} );
	
	
	/* create vc shortcodes for support */
	jQuery(document).on( 'upper-blocks-ready', 'body', function(){ 
		var wrappers = [];
		
		jQuery('.editor-block-list__block[data-type="upper/upper-wpb-element"]').each(function(){
			
			var current_block = wp.data.select('core/editor').getBlock( jQuery(this).attr('id').replace('block-','') ),
				element = jQuery(this),
				shortcode_output = current_block.attributes.content;
			
			element.data('upper-from-start', true);
			
			if (typeof shortcode_output != "string"){
				if (typeof shortcode_output == "object"){
					shortcode_output = shortcode_output[0];
				} else {
					if (current_block.originalContent != "") shortcode_output = current_block.originalContent;
					else {
						if (jQuery(this).find('p.wp-block-upper-upper-wpb-element').length){
							shortcode_output = jQuery(this).find('p.wp-block-upper-upper-wpb-element').text();
						}
					}
				}
			}
			
			if (typeof shortcode_output == "string" && shortcode_output != ""){
				window.vc.shortcodes.createFromString( shortcode_output, 0 );
				element.attr( 'data-upper-related-vc-shortcode-id', window.vc.shortcodes.models[window.vc.shortcodes.models.length-1].id ).find( '.editor-block-list__block-edit' ).addClass( 'upper-editable-shortcode-block' );
				
				if (typeof window.upper_blocks_props[ element.attr('id').replace('block-','') ] == "undefined") window.upper_blocks_props[ element.attr('id').replace('block-','') ] = [];
				window.upper_blocks_props[ element.attr('id').replace('block-','') ]['vc_shortcode_id'] = window.vc.shortcodes.models[window.vc.shortcodes.models.length-1].id;
				wrappers.push([window.vc.shortcodes.models[window.vc.shortcodes.models.length-1].id, element]);
			}
		});
		
		if (wrappers.length){
			setTimeout(function(){
				for (var i=0; i<wrappers.length; i++){
					var vc_wrapper = jQuery('#visual_composer_content div[data-model-id="'+wrappers[i][0]+'"] .wpb_element_wrapper');
					if (vc_wrapper){
						var vc_shortcode_header = vc_wrapper.clone(true,true);
							vc_shortcode_header.find('a, .vc_admin_label:not(.admin_label_type)').remove();
						wrappers[i][1].find('p.wp-block-upper-upper-wpb-element').addClass('hidden_bc_of_vc_description').siblings().remove();
						wrappers[i][1].find('p.wp-block-upper-upper-wpb-element').before( vc_shortcode_header ).parent().addClass('upper-shortcode-ready');
					}
				}
			}, 1000);
		}
		
		// normies are done. take care of reusables.
		upper_init_reusables();
	});
	
	/* binds */
	/* EDIT */
	jQuery(document).on( 'click', '.upper-editable-shortcode-block .wpb_element_wrapper, .upper-editable-shortcode-block p.wp-block-upper-upper-wpb-element, .editor-block-list__block-edit .editor-block-list__block[data-type="upper/upper-wpb-element"] .wpb_element_wrapper, .editor-block-list__block-edit .editor-block-list__block[data-type="upper/upper-wpb-element"] p.wp-block-upper-upper-wpb-element', function(e){
		
		if (!jQuery(this).closest('.upper-for-edit').length && !jQuery(this).siblings('.upper-for-edit').length && !jQuery(this).closest('.is-reusable').length){
			jQuery(this).addClass('upper-for-edit');
			return;
		}
		
		// well this makes easier the ux as prevents options to show every single time user clicks the element.
		jQuery(this).closest('.upper-for-edit').add(jQuery(this).siblings('.upper-for-edit')).removeClass('upper-for-edit');
		
		try{ clearInterval( window.upper_intervals['block_clone'] ); clearInterval( window.upper_intervals['shortcode'] ); } catch(e){ console.log(e); }
		
		jQuery('div[data-model-id="'+  window.upper_blocks_props[ wp.data.select('core/editor').getSelectedBlock().clientId ]['vc_shortcode_id'] +'"] .vc_controls .vc_control-btn-edit').trigger('click');

		window.upper_block = jQuery(e.currentTarget).closest('.editor-block-list__block');
		window.upper_block_id = window.upper_block.attr('id').replace('block-','');
		window.upper_shortcode_id = window.upper_blocks_props[ window.upper_block_id ][ 'vc_shortcode_id' ];
		window.this_shortcode = window.vc.shortcodes.where({ id : window.upper_shortcode_id })[0];
		
		try{ clearInterval( window.upper_intervals['shortcode'] ); } catch(e){ console.log(e); }
		window.upper_intervals['shortcode'] = setInterval(function(){
			if (jQuery('#vc_edit-form-tabs > .vc_active').children().length > 0){
				
				clearInterval(window.upper_intervals['shortcode']);
				
				window.upper_current_vc_shortcode_id = window.upper_block.data( 'upper-related-vc-shortcode-id' );
				
				jQuery('.wpb_edit_form_elements.vc_edit_form_elements input.vc_textarea_html_content').each(function(){
					if (jQuery(this).val() && !jQuery(this).siblings('textarea').val()) jQuery(this).siblings('textarea').val( jQuery(this).val() );
				});
				
				jQuery('#vc_ui-panel-edit-element span[data-vc-ui-element="button-save"]').data('shortcode-id', window.upper_current_vc_shortcode_id).unbind('click').bind('click', function(e){
					
					setTimeout(function(){ 
						
						var shortcode_output = "["+window.this_shortcode.attributes.shortcode;
						if (window.this_shortcode.attributes.params){
							for (var i=0; i<Object.keys(window.this_shortcode.attributes.params).length; i++){
								if ( window.this_shortcode.attributes.params[Object.keys(window.this_shortcode.attributes.params)[i]] )
									shortcode_output += ' '+Object.keys(window.this_shortcode.attributes.params)[i]+'="'+window.this_shortcode.attributes.params[Object.keys(window.this_shortcode.attributes.params)[i]]+'"';
							}
						}
						shortcode_output += "]";
						
						if (typeof window.upper_blocks_props[ window.upper_block_id ]['props'].attributes != "undefined"){
							window.upper_blocks_props[ window.upper_block_id ]['props'].attributes.upper_shortcode_output = shortcode_output;
							window.upper_blocks_props[ window.upper_block_id ]['props'].attributes.content = shortcode_output;
						}
						
						wp.data.select('core/editor').getBlock(window.upper_block_id).attributes.upper_shortcode_output = shortcode_output;
						wp.data.select('core/editor').getBlock(window.upper_block_id).attributes.content = shortcode_output;
						
						jQuery('#block-'+window.upper_block_id+' p.wp-block-upper-upper-wpb-element').text( shortcode_output );
						
						if (jQuery('.reusable-block-edit-panel').length){	
							if (typeof window.upper_blocks_props[ window.upper_block_id ]['props'].block != "undefined"){
								window.upper_blocks_props[ window.upper_block_id ]['props'].block.attributes.content = shortcode_output;
							}
						} else {
							if (jQuery('.reusable-block-edit-panel').length){
								wp.data.select.dispatch( 'core/editor' ).getBlock( window.upper_block_id ).setAttributes('content', shortcode_output);
							} else {
								upper_rbt.save( window.upper_blocks_props[ window.upper_block_id ]['props'], true );
								wp.data.dispatch( 'core/editor' ).updateBlock( window.upper_block_id );
								wp.data.dispatch( 'core/editor' ).clearSelectedBlock();
							}
							
							setTimeout(function(){
								var vc_wrapper = jQuery('#visual_composer_content div[data-model-id="'+window.this_shortcode.attributes.id+'"] .wpb_element_wrapper');
								if (vc_wrapper){
									var vc_shortcode_header = vc_wrapper.clone(true,true);
										vc_shortcode_header.find('a, .vc_admin_label:not(.admin_label_type)').remove();
									window.upper_block.find('p.wp-block-upper-upper-wpb-element').addClass('hidden_bc_of_vc_description').siblings().remove();
									window.upper_block.find('p.wp-block-upper-upper-wpb-element').before( vc_shortcode_header ).parent().addClass('upper-shortcode-ready');
								}
							}, 1000);
						}
						
					},100);
					
				});
				
			}
		}, 200);
		
	});
	
	jQuery(document).on( 'click', '#vc_ui-panel-add-element button[data-vc-ui-element="button-close"]', function(e){
		jQuery('#vc_ui-panel-add-element').removeClass('vc_active upper_temp_allow');
		jQuery('div.editor-block-list__block[data-type="upper/upper-wpb-element"]').each(function(){
			if (!jQuery(this).data('upper-related-vc-shortcode-id')) {
				wp.data.dispatch( 'core/editor' ).removeBlock( jQuery(this).attr('id').replace('block-','') );
				wp.data.dispatch( 'core/editor' ).clearSelectedBlock();
			}
		});
	});
	
	/* hook into our shortcodes being dragged around and stuff */
	jQuery(document).on('dragstart', '.editor-block-list__block[data-type="upper/upper-wpb-element"] .editor-block-mover__control-drag-handle', function(){ 
		
		window.upper_block_dragging_id = jQuery('#block-'+ wp.data.select('core/editor').getSelectedBlock().clientId ).data('upper-related-vc-shortcode-id');
		
		jQuery(this).unbind( 'dragend' ).bind('dragend', function(e){
			var element_anchor = jQuery('.editor-block-list__block[data-type="upper/upper-wpb-element"] .editor-block-mover__control-drag-handle');
			if (typeof element_anchor != "undefined" && window.upper_block_dragging_id != false){
				var block = jQuery(element_anchor[0]).closest('.editor-block-list__block[data-type="upper/upper-wpb-element"]');
				if (typeof block.data('upper-related-vc-shortcode-id') == "undefined") {
					
					var vc_wrapper = jQuery('#visual_composer_content div[data-model-id="'+window.upper_block_dragging_id+'"] .wpb_element_wrapper');
					if (vc_wrapper){
						
						var vc_shortcode_header = vc_wrapper.clone(true,true);
							vc_shortcode_header.find('a, .vc_admin_label:not(.admin_label_type)').remove();
						block.find('p.wp-block-upper-upper-wpb-element').addClass('hidden_bc_of_vc_description').siblings().remove();
						block.find('p.wp-block-upper-upper-wpb-element').before( vc_shortcode_header ).parent().addClass('upper-shortcode-ready');
						
					} 
					
					block.data('upper-related-vc-shortcode-id', window.upper_block_dragging_id).find('.editor-block-list__block-edit').addClass('upper-editable-shortcode-block upper-shortcode-ready');
					
					window.upper_blocks_props[ wp.data.select('core/editor').getSelectedBlock().clientId ]['vc_shortcode_id'] = window.upper_block_dragging_id;
					upper_init_reusables();
				}
			}
			
		});
		
	});
	
	/* hook into reusable blocks */
	jQuery(document).on( 'submit', '.reusable-block-edit-panel', function( e ){
			
		try{ clearInterval( window.upper_intervals['block_clone'] ); } catch(e){ console.log(e); }
		var block_id = wp.data.select('core/editor').getSelectedBlock().clientId;
		
		var aux_vc_shortcode_id = typeof window.upper_blocks_props[ block_id ] != "undefined" ? window.upper_blocks_props[ block_id ]['vc_shortcode_id'] : false;
		if (!aux_vc_shortcode_id){

			aux_vc_shortcode_id = (typeof wp.data.select('core/editor').getSelectedBlock().attributes.ref != "undefined" && typeof window.upper_blocks_props[  wp.data.select('core/editor').__experimentalGetReusableBlock( wp.data.select('core/editor').getSelectedBlock().attributes.ref ).clientId ] != "undefined") ? window.upper_blocks_props[  wp.data.select('core/editor').__experimentalGetReusableBlock( wp.data.select('core/editor').getSelectedBlock().attributes.ref ).clientId ]['vc_shortcode_id'] : false;
		}
		
		if (aux_vc_shortcode_id){
			var vc_wrapper = jQuery('#visual_composer_content div[data-model-id="'+ aux_vc_shortcode_id +'"] .wpb_element_wrapper');
			var args = [vc_wrapper, block_id];
			if (vc_wrapper){
				setTimeout((function(){
					
					var vc_shortcode_header = args[0].clone(true,true);
						vc_shortcode_header.find('a, .vc_admin_label:not(.admin_label_type)').remove();
					jQuery('#block-'+args[1]+' p.wp-block-upper-upper-wpb-element').addClass('hidden_bc_of_vc_description').siblings().remove();
					jQuery('#block-'+args[1]+' p.wp-block-upper-upper-wpb-element').before( vc_shortcode_header ).parent().addClass('upper-shortcode-ready');
					
					upper_convert_normal_to_reusable = false;
					
				}).bind(null, args), 100);
			}
		}
		
		upper_convert_normal_to_reusable = false;
		
	} ).on( 'click', '.reusable-block-edit-panel__button[type="button"]', function( e ){
		if (jQuery(e.currentTarget).siblings('button').length){
			// cancel edition. revert p content 
			setTimeout(function(){ upper_init_reusables(); }, 0);
		} else {
			upper_skip_reusables = true;
		}
	}).on( 'click', '.editor-block-types-list__list-item button', function( e ){
		if (jQuery(e.currentTarget).is('.editor-block-list-item-upper-upper-wpb-element')){
			upper_add_new_block = true;
			upper_add_other_block = false;
		} else {
			upper_add_new_block = false;
			upper_add_other_block = true;
		}
	}).on( 'mouseenter', '.editor-block-types-list__list-item button', function( e ){
		e.stopPropagation(); e.preventDefault(); 
		try{ clearInterval( window.upper_intervals['block_clone'] ); } catch(e){ console.log(e); }
		upper_hovering_blocks = true;
	}).on( 'click', '.editor-inserter-with-shortcuts button', function(){
		if (jQuery(this).attr('aria-label').indexOf(block_name) > -1){
			upper_add_new_block = true;
			upper_add_other_block = false;
		} else {
			upper_add_new_block = false;
			upper_add_other_block = true;
		}
	}).on('click','.editor-block-settings-menu__content button', function(e){ 
		
		// DUPLICATES
		if (jQuery(this).find('svg.dashicons-admin-page').length){ 
			upper_add_clone = true;
			upper_add_clone_of = wp.data.select('core/editor').getSelectedBlock().clientId;
			setTimeout(function(){ upper_init_reusables(); upper_add_clone = false; }, 0);
		} 
		
		// CONVERTIONS
		if (jQuery(this).find('svg.dashicons-controls-repeat').length){ 
			
			if (jQuery('#block-'+wp.data.select('core/editor').getSelectedBlock().clientId).is('.is-reusable')){
				// REUSABLE -> NORMAL
				// unnecessary
			} else {
				// NORMAL -> REUSABLE
				upper_convert_normal_to_reusable = true;
			}
			
		}
		
	});
	
	// bind into the add new vc element
	// also hide shortcodes window on shortcode select
	jQuery(document).on( 'click', 'li[data-vc-ui-element="add-element-button"]', function(){
		
		jQuery('#vc_ui-panel-add-element').removeClass('upper_temp_allow');
		
		try{ clearInterval( window.upper_intervals['shortcode_first'] ); } catch(e){ console.log(e); }
		window.upper_intervals['shortcode_first'] = setInterval(function(){
			if (jQuery('.wpb_edit_form_elements.vc_edit_form_elements > .vc_active')){
				
				clearInterval(window.upper_intervals['shortcode_first']);
				
				jQuery('.wpb_edit_form_elements.vc_edit_form_elements input.vc_textarea_html_content').each(function(){
					if (jQuery(this).val() && !jQuery(this).siblings('textarea').val()) jQuery(this).siblings('textarea').val( jQuery(this).val() );
				});
				
				window.upper_current_vc_shortcode_id = window.vc.shortcodes.models[window.vc.shortcodes.models.length-1].attributes.id;
				
				//bind edit
				jQuery('#'+ window.upper_current_block_id).attr( 'data-upper-related-vc-shortcode-id', window.upper_current_vc_shortcode_id ).find('.editor-block-list__block-edit').addClass( 'upper-editable-shortcode-block' );
				
				window.upper_blocks_props[ wp.data.select('core/editor').getSelectedBlock().clientId ]['vc_shortcode_id'] = window.upper_current_vc_shortcode_id;
				
				jQuery('#vc_ui-panel-edit-element span[data-vc-ui-element="button-save"]').add(jQuery('#vc_ui-panel-edit-element span[data-vc-ui-element="button-close"]')).data('shortcode-id', window.upper_current_vc_shortcode_id).unbind('click').bind('click', function(e){

					var shortcode_id = jQuery(e.currentTarget).data('shortcode-id'),
						shortcode = window.vc.shortcodes.where({ id : shortcode_id })[0];
					
					setTimeout(function(){ 

						var shortcode_output = "["+shortcode.attributes.shortcode;
						if (shortcode.attributes.params){
							for (var i=0; i<Object.keys(shortcode.attributes.params).length; i++){
								if ( shortcode.attributes.params[Object.keys(shortcode.attributes.params)[i]] )
									shortcode_output += ' '+Object.keys(shortcode.attributes.params)[i]+'="'+shortcode.attributes.params[Object.keys(shortcode.attributes.params)[i]]+'"';
							}
						}
						shortcode_output += "]";
						
						window.upper_blocks_props[ window.current_block_id ]['props'].attributes.upper_shortcode_output = shortcode_output;
						window.upper_blocks_props[ window.current_block_id ]['props'].attributes.content = shortcode_output;
						
						upper_rbt.save( window.upper_blocks_props[ window.current_block_id ]['props'], true );
						
						wp.data.dispatch( 'core/editor' ).updateBlock( window.current_block_id );
						wp.data.dispatch( 'core/editor' ).clearSelectedBlock();
						
						setTimeout(function(){
							
							var vc_wrapper = jQuery('#visual_composer_content div[data-model-id="'+jQuery('#block-'+window.current_block_id).data('upper-related-vc-shortcode-id')+'"] .wpb_element_wrapper');
							if (vc_wrapper){
								
								var vc_shortcode_header = vc_wrapper.clone(true,true);
									vc_shortcode_header.find('a, .vc_admin_label:not(.admin_label_type)').remove();
								jQuery('#block-'+window.current_block_id+' p.wp-block-upper-upper-wpb-element').addClass('hidden_bc_of_vc_description').siblings().remove();
								jQuery('#block-'+window.current_block_id+' p.wp-block-upper-upper-wpb-element').before( vc_shortcode_header ).parent().addClass('upper-shortcode-ready');
								
							}
						}, 1000);
						
					},100);
				});
			}
		}, 50);
		
	});	
	
})();


jQuery(window).on("load", function(){
	
	if (!wp.data.select('core/editor').isCleanNewPost())
		setTimeout(function(){ 
			jQuery('body').trigger('upper-blocks-ready'); 
		}, 1000);
	else jQuery('body').addClass('upper-blocks-ready'); 
	/* override VCs textareas + TinyMCE mambo jambo */
	window.init_textarea_html = function($element) {
		if (!$element) return;
		var $wp_link, textfield_id, $form_line, $content_holder;
		($wp_link = jQuery("#wp-link")).parent().hasClass("wp-dialog") && $wp_link.wpdialog("destroy"), textfield_id = $element.attr("id"), $content_holder = ($form_line = $element.closest(".edit_form_line")).find(".vc_textarea_html_content");
		$element.data("vcTinyMceDisabled", !0).appendTo($form_line), jQuery("#wp-" + textfield_id + "-wrap").remove();
		// thats all we need from vc, thank it.
		// - thank you, vc.
	}
});

function upper_init_reusables(){
	
	if (upper_skip_reusables){
		upper_skip_reusables = false;
		return;
	}
	if ( upper_convert_normal_to_reusable || jQuery('form.reusable-block-edit-panel').length ) return;
	
	var reusable_blocks = wp.data.select('core/editor').__experimentalGetReusableBlocks(),
		wrappers = [];
	
	if( reusable_blocks ){
		for (var i=0; i<reusable_blocks.length; i++){
			
			var ref_block = wp.data.select('core/editor').__experimentalGetReusableBlock( reusable_blocks[i].id ),
				aux_block = wp.data.select('core/editor').getBlock( ref_block.clientId );
			
			upper_blocks_parser = [];
			upper_parse_our_blocks( aux_block, upper_blocks_parser );
			
			if ( upper_blocks_parser.length ){
				
				for (var j=0; j<upper_blocks_parser.length; j++){
					
					var found = [];
					jQuery('p.wp-block-upper-upper-wpb-element:not(.upper-reusable-inited)').each(function(){
						if (jQuery(this).text() == strip_tags( upper_blocks_parser[j].originalContent )){
							jQuery(this).addClass('upper-reusable-inited'); 
							found.push( jQuery(this).closest('.editor-block-list__block-edit').find('div[data-block]').data('block') );
						}
					});
					
					if (found.length){
						for (var k=0; k<found.length; k++){
							if (typeof window.upper_blocks_props[ found[k] ] == "undefined"){
								window.upper_blocks_props[ found[k] ] = window.upper_blocks_props[ upper_blocks_parser[j].clientId ];
								delete window.upper_blocks_props[ upper_blocks_parser[j].clientId ];
							}
							
							shortcode_output = typeof upper_blocks_parser[j].attributes.content == "object" ? upper_blocks_parser[j].attributes.content[0] : upper_blocks_parser[j].attributes.content;
						
							window.vc.shortcodes.createFromString( shortcode_output, 0 );
							jQuery('#block-'+ found[k] ).attr( 'data-upper-related-vc-shortcode-id', window.vc.shortcodes.models[window.vc.shortcodes.models.length-1].id ).attr('data-type', 'upper/upper-wpb-element').find( '.editor-block-list__block-edit' ).addClass( 'upper-editable-shortcode-block upper-shortcode-ready' ).find('div[data-block]').addClass('upper-shortcode-ready');
												
							if (typeof window.upper_blocks_props[ found[k] ] != "undefined"){
								window.upper_blocks_props[ found[k] ]['vc_shortcode_id'] = window.vc.shortcodes.models[window.vc.shortcodes.models.length-1].id;
								window.upper_blocks_props[ found[k] ]['init'] = true;
								
								var vc_wrapper = jQuery('#visual_composer_content div[data-model-id="'+window.upper_blocks_props[ found[k] ]['vc_shortcode_id']+'"] .wpb_element_wrapper');
				
								if (vc_wrapper){
									wrappers.push( [ vc_wrapper, found[k] ] );
								}
							}
						}
						
					} 
				}
			} 
		}
		
		if (wrappers.length){
			setTimeout((function(){
				for (var i=0; i<wrappers.length; i++){
					var vc_shortcode_header = wrappers[i][0].clone(true,true);
						vc_shortcode_header.find('a, .vc_admin_label:not(.admin_label_type)').remove();
					jQuery('#block-'+ wrappers[i][1] +' p.wp-block-upper-upper-wpb-element').addClass('hidden_bc_of_vc_description').siblings().remove();
					jQuery('#block-'+ wrappers[i][1] +' p.wp-block-upper-upper-wpb-element').before( vc_shortcode_header ).parent().addClass('upper-shortcode-ready');
				}
				jQuery('body').addClass('upper-blocks-ready');
			}).bind(null,wrappers), 100);
		} else {
			jQuery('body').addClass('upper-blocks-ready');
		}
		
		jQuery('div[data-type="upper/upper-wpb-element"]').not('[data-upper-related-vc-shortcode-id]').each(function(){
			if ( typeof window.upper_blocks_props[ jQuery(this).attr('id').replace('block-','') ] != "undefined" && typeof window.upper_blocks_props[ jQuery(this).attr('id').replace('block-','') ]['vc_shortcode_id'] != "undefined"){
				jQuery(this).attr('data-upper-related-vc-shortcode-id', window.upper_blocks_props[ jQuery(this).attr('id').replace('block-','') ]['vc_shortcode_id'] );
				
				var vc_wrapper = jQuery('#visual_composer_content div[data-model-id="'+window.upper_blocks_props[ jQuery(this).attr('id').replace('block-','') ]['vc_shortcode_id']+'"] .wpb_element_wrapper');

				if (vc_wrapper){
					var vc_shortcode_header = vc_wrapper.clone(true,true);
						vc_shortcode_header.find('a, .vc_admin_label:not(.admin_label_type)').remove();
					
					jQuery(this).find('p.wp-block-upper-upper-wpb-element').addClass('hidden_bc_of_vc_description').siblings().remove();
					jQuery(this).find('p.wp-block-upper-upper-wpb-element').before( vc_shortcode_header ).parent().addClass('upper-shortcode-ready');
				}
				
				jQuery(this).find( '.editor-block-list__block-edit' ).addClass( 'upper-editable-shortcode-block' );
			}
		});
		
	} else {
		// ALL IS DONE. add class to body 
		jQuery('body').addClass('upper-blocks-ready');
	}
	
}

function upper_parse_our_blocks( Obj, upper_blocks_parser ){
	var foundObjects = [];
	for (prop in Obj){
		if (prop === "name" && Obj[prop] === "upper/upper-wpb-element") {
			upper_blocks_parser.push(Obj); 
		}
		else if (prop === "innerBlocks") {
			for (var i=0; i<Obj[prop].length; i++) upper_parse_our_blocks(Obj[prop][i], upper_blocks_parser);
		}
	}
}