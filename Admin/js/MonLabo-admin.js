/*------------------------------------------------------------------------------------------

	WARNING :  REGENERATE WITH make.sh MonLabo-admin.min.js AFTER EVERY CHANGE IN THIS FILE

-------------------------------------------------------------------------------------------*/
/**
 * Scripts for the admin interface.
 *
 * Theses functions are used in the administration pages of the plugin.
 *
 * @link   https://monlabo.org/
 * @author Hervé Suaudeau.
 * @since  1.4.0 at least
 */
/*jshint esversion: 8 */

/**
 * Person edition page : Started when the category is changed.
 *
 * Function started when touching a selector to chose the category of a
 * person. It changes the selection of person's functions.
 *
 * @fires select[name='submit_category']:change in page "Edit persons"
 *
 * @since 2.2.0 with name touchCategoryNouvelAuteur
 * @since 2.8.0 renamed touchPersonCategory
 */
 function touchPersonCategory() {

	// Gets the category chosen by the selector
	var categoryChosen = jQuery( "select[name='submit_category']" ).val();

	// Shows optgroup of the chosen category
    jQuery( "select[name='submit_fonction'] optgroup" ).hide();
	jQuery( "select[name='submit_fonction'] optgroup[label='" + categoryChosen + "']" ).show();
}


/**
 * Person edition page : Started when the status is changed.
 *
 * Function started when touching a selector to change the status of a
 * person. It displays the form of departures date if the status is chosen
 * to alumni.
 *
 * @fires select[name='submit_status']:change in page "Edit persons"
 *
 * @since 2.5.1 with name touchStatusNouvelAuteur
 * @since 2.8.0 renamed touchPersonStatus
 */
function touchPersonStatus() {

	// Gets the item chosen by the selector
	var itemChosen = jQuery( "select[name='submit_status']" ).val();

	// Hides of shows departures date form
	if ( itemChosen === 'actif' ) {
		jQuery( '#MonLabo-date-departure-form' ).hide();
	} else {
		jQuery( '#MonLabo-date-departure-form' ).show();
	}
}


/**
 * Person edition page : Started when the function is changed.
 *
 * Function started when touching a selector to change the function of a
 * person. It updates the editing fields of the function.
 *
 * @fires select[name='submit_fonction']:change in page "Edit persons"
 *
 * @since 1.4.0 with name touchFonctionNouvelAuteur
 * @since 2.8.0 renamed touchPersonFunction
 */
function touchPersonFunction() {

	// Gets the item chosen by the selector
	// item chosen is of the form 'category | function_en | function_fr'
	var fonctions = jQuery( "select[name='submit_fonction']" ).val().split( ' | ' );

	// Updates the editing fields of the function.
	jQuery( "input[name='submit_function_en']" ).val( fonctions[1] );
	jQuery( "input[name='submit_function_fr']" ).val( fonctions[2] );
}


/**
 * Person edition page : Started when the title is changed.
 *
 * @fires select[name='submit_title']:change in page "Edit persons"
 *
 * @since 4.2.0 creation
 */
 function touchPersonTitle() {

	// Gets the item chosen by the selector
	var val = jQuery( "select[name='submit_title']" ).val();

	// Updates the editing fields of the function.
	if ( val == 'edit' ) {
		jQuery( '#edit-person-title-field' ).show();
		if ( jQuery( "input[name='submit_title_edit']" ).val() == 'none' ) {
			jQuery( "input[name='submit_title_edit']" ).val( '' );
		}
	} else {
		jQuery( '#edit-person-title-field' ).hide();
		jQuery( "input[name='submit_title_edit']" ).val( val );
	}
}


function isPositiveIntegerOrEmpty(n) {
	return ( ( n == '' ) ||
		( n == -2 ) ||
		( parseInt(n,10).toString(10) === n )
	);
}

function ajaxLoadPostThumbnailNow( pageNumber = '__no_change__', selectname = '' ) {
	pageNumber = parseInt( pageNumber );
	if ( jQuery( `#delayedLoadDivThumbnail_${pageNumber}` ).length ) {
		//Hide div during changes
		div_id = `#delayedLoadDivThumbnail_${pageNumber}`;
		jQuery( div_id ).hide();

		if ( selectname == '' ) {
			//In Edit persons and structures => Choose page 
			wp_post_id = jQuery( `input[name='submit_wp_post_ids[${pageNumber}]']` ).val();
			radio_status =  jQuery( `input[name='pageradio[${pageNumber}]']:checked` ).val();
			if ( jQuery( '#form_edit_person' ).length) {
				type = 'person';
			} else if ( jQuery( '#form_edit_team' ).length) {
				type = 'team';
			} else if ( jQuery( '#form_edit_thematic' ).length) {
				type = 'thematic';
			} else if ( jQuery( '#form_edit_unit' ).length) {
				type = 'unit';
			} else {
				type = 'unknown';
			}
			$item_id = jQuery( "input[name='submit_id']" ).val();
		} else {
			//In General configurations => Tab "Pages"
			wp_post_id = jQuery( `select[name='${selectname}']` ).val();
			radio_status = 'dummy';
			type = 'person';
			$item_id = 0; //dummy
		}
		if ( ( radio_status != 'none' ) && ( type != 'unknown' ) ) {
			if ( radio_status == 'new' ) {
				wp_post_id = -2;
			}

			if ( wp_post_id != '' ) {
				jQuery.ajax({
					type : 'post',
					dataType : 'json',
					url : ajax_object_update_page_infobox.ajax_url,
					data : {
						action: 'update_page_infobox',
						_ajax_nonce: ajax_object_update_page_infobox.nonce,
						'item_id': $item_id,
						'wp_post_id': wp_post_id,
						'div_id': div_id,
						'page_number': pageNumber,
						'type': type,
					},
					success: function( response ) {
						if ( 'success' == response.type ) {
							jQuery( response.div_id ).hide();
							jQuery( response.div_id ).html( response.text );
							jQuery( response.div_id ).fadeIn( 500 );
						}
					},
					error: function( ) {
						console.log("echec");
					},
				});
			}
		}
	}
}


/**
 * Displays media menu in order to chose a picture.
 *
 * Uses media menu from WordPress.
 *
 * @since 2.1.0 with name image_media_menu
 * @since 2.8.0 renamed imageMediaMenu
 */
function imageMediaMenu(
	titleText = 'Images',
	buttonText = 'OK',
	imagePreviewFieldId = 'image-preview',
	imageAttachementIdFieldId = 'image_attachment_id',
    //imageAttachementUrlFieldId = 'image_attachment_url'
 ) {

	// Creates the media frame.
	var file_frame = wp.media.frames.file_frame = wp.media( {
		title: titleText,
		button: {
			text: buttonText
		},
		multiple: false // Set to true to allow multiple files to be selected
	} );
	var url;

	// When an image is selected, runs a callback.
	file_frame.on( 'select', function() {

		// Set multiple to false so only gets one image from the uploader
		var attachment = file_frame.state().get( 'selection' ).first().toJSON();
		var urlThubmnail;
		if ( ( attachment.sizes ) && ( attachment.sizes.thumbnail ) && ( attachment.sizes.thumbnail.url ) ) {
			urlThubmnail = attachment.sizes.thumbnail.url;
		} else {
			urlThubmnail = attachment.url;
		}

		// Does something with attachment.id and/or attachment.url here
		jQuery( `#${imagePreviewFieldId}` ).attr( 'src', urlThubmnail ).css( 'width', 'auto' );
		jQuery( `#${imagePreviewFieldId}` ).removeAttr( 'srcset' ); //Id of image to display (<img id=...)
		jQuery( `#${imagePreviewFieldId}` ).show(); //Id of image to display (<img id=...)
		jQuery( `#${imageAttachementIdFieldId}` ).val( attachment.id );
	});

	// Finally, opens the modal
	file_frame.open();
}

/**
 * Action to do when changing a dropdown menu associated to a wp_pos_ids field.
 **/

function touchDropdownPage( event ) {
	hiddenDivId=event.data.hiddenDivId;
	fieldname=event.data.fieldname;
	jQuery( `#${hiddenDivId}` ).hide();
	jQuery( "input[name='" + fieldname + "']" ).val(jQuery( "select[name='dropdown_" + fieldname + "']" ).val());
	jQuery( "input[name='" + fieldname + "']" ).prop( "disabled", false );
}

function touchPersonDropdownPage( event ) {
	pageNumber=event.data.pageNumber;
	edit_wp_post_id = `input[name='submit_wp_post_ids[${pageNumber}]']`;
	jQuery( edit_wp_post_id ).val(jQuery( `select[name='dropdown_submit_wp_post_ids[${pageNumber}]']` ).val());
	ajaxLoadPostThumbnailNow( pageNumber );
}

function PageButtonClick( action, pageNumber ) {
	edit_wp_post_id = `input[name='submit_wp_post_ids[${pageNumber}]']`;
	dropdown_menu_div = `#hidd_drop_wp_post_ids_${pageNumber}`;
	switch (action) {
		case 'new':
			jQuery( dropdown_menu_div ).hide();
			jQuery( edit_wp_post_id ).hide() ;
			jQuery( '#MonLabo_noParentPageConfigured' ).show()
			ajaxLoadPostThumbnailNow( pageNumber );
			break;
		case 'choose':
			jQuery( edit_wp_post_id ).hide() ;
			jQuery( dropdown_menu_div ).show();
			jQuery(`select[name='dropdown_submit_wp_post_ids[${pageNumber}]']`).on(
				'change', {pageNumber:pageNumber}, touchPersonDropdownPage );
				jQuery( '#MonLabo_noParentPageConfigured' ).hide()
				ajaxLoadPostThumbnailNow( pageNumber );
			break;
		case 'edit':
			jQuery( edit_wp_post_id ).show() ;
			jQuery( dropdown_menu_div ).hide();
			jQuery( '#MonLabo_noParentPageConfigured' ).hide()
			ajaxLoadPostThumbnailNow( pageNumber );
			break;
		case 'none':
			jQuery( edit_wp_post_id ).hide() ;
			jQuery( dropdown_menu_div ).hide();
			jQuery( `#delayedLoadDivThumbnail_${pageNumber}` ).hide();
			jQuery( '#MonLabo_noParentPageConfigured' ).hide()
			break;
	}
}

/**
 * Displays fields if id.
 *
 * @since 2.1.0 with name affiche_edition_fonctions_fields
 * @since 2.8.0 renamed displaysEditFunctionsFields
 * @since 3.5.0 renamed displaysIdField to be generic
 **/
function displaysIdField( id ) {
	jQuery( '#' + id ).show();
}


/**
 * Displays fields if id1 and hide fields of id2.
 **/
function toggleIdField( id1, id2 ) {
	jQuery( `#${id1}` ).show();
	jQuery( `#${id2}` ).hide();
}


/**
 * Select no picture
 *
 * Lauched when "none" button is pushed when chosing a picture.
 *
 * @since 2.1.0 with name effacer_choix_media
 * @since 2.8.0 renamed selectNoPicture
 */
function selectNoPicture( imagePreviewFieldId ,	imageAttachementIdFieldId ) {
    jQuery( `#${imageAttachementIdFieldId}` ).val( '' );
	jQuery( `#${imagePreviewFieldId}` ).attr( 'src', '' ).css( 'width', 'auto' );
	jQuery( `#${imagePreviewFieldId}` ).removeAttr( 'srcset' );
    jQuery( `#${imagePreviewFieldId}` ).hide();
}

/**
 * Select default picture
 *
 * Lauched when "none" button is pushed when chosing a picture for a person.
 */
 function selectDefaultPicture( imagePreviewFieldId ,	imageAttachementIdFieldId, defaultPictureUrl ) {
    jQuery( `#${imageAttachementIdFieldId}` ).val( '' );
	jQuery( `#${imagePreviewFieldId}` ).attr( 'src', defaultPictureUrl );
}

/**
 * Changes a selection value of an idem in the edit page.
 *
 * @since 2.4.0 with name change_item
 * @since 2.8.0 renamed changeItem
 * @since 4.1 use ID prefixed by 'choice_'
 *
 * @param {String} formId ID of the form
 * @param {String} itemId ID of the item (person, team, group or unit) to treat
 */
function changeItem( formId, itemId ) {
	// Adds in _GET the member's number to get it back in case of reloading the page
	jQuery( `#choice_${formId}` ).attr( 'action', jQuery( location ).attr( 'href' ) + `&submit_item=${itemId}` );

	// Validates the form and reloads the page
	document.forms[`choice_${formId}`].submit();
}
/**
 * Changes a selection value of a database to use in the config page
 *
 * @since 4.7
 *
 * @param {String} itemId value of the select
 */
function changeDbToUse( itemId ) {
	if( ( itemId != '___no_change___' ) && ( itemId != '___manual_edit___' ) ) {
		jQuery( "input[name='MonLabo_settings_group0[MonLabo_db_prefix_manual_edit]']" ).val( itemId );
	}
}
/**
 * Changes prefix value of a database to use in the config page
 *
 * @since 4.7
 *
 */
function editDbToUse( ) {
	jQuery( "select[name='MonLabo_settings_group0[MonLabo_multisite_db_to_use]']" ).val('___manual_edit___');
}

/**
 * Edit of suppress a person or structure
 *
 * @since 2.4.0 with name submit_form
 * @since 2.8.0 renamed submitForm
 *
 * @param {String} formId ID of the form
 * @param {String} action action to do if form is submited
 */
function submitForm( formId, action ) {
	// Schedules the action when the page reloads
	document.getElementById( 'action' ).value = action;
	if (jQuery( '#form_edit_person' ).length ) {
		// Displays the hidden fields to understand why the submit is refused if they are empty
		displaysIdField('edit-functions-fields');
	}

	// Validates the form and reloads the page
	document.forms[formId].submit();
}

/**
 * Fill trigger field and click on submit
 * (usefull for delete publication cache)
 * @since 4.8
 */
function fillTriggerAndSubmit( trigerName ) {
	console.log( `#${trigerName}` );
	jQuery( `#${trigerName}` ).val( "true" );
	// Validates the form and reloads the page
	jQuery( '#submit' ).trigger('click');
}

/**
 * Modify <select> of persons
 *
 * @since 4.6
 */
function formatPersonsState (state) {
	if (!state.id) {
	  return state.text;
	}
	label=state.element.parentElement.label
	if ( label == 'Alumni' ) {
		return state.text + ' (alumni)';
	} else {
		return state.text;
	}
  };

/**
 * On loads function
 */

jQuery( document ).ready( function($) {
	jQuery( '.my-color-field' ).wpColorPicker();
	// Configurations for the members edit page
	if ( jQuery( '#MonLabo_admin_options' ).length) {
		name1 = 'MonLabo_settings_group10[MonLabo_perso_page_parent]';
		name2 = 'MonLabo_settings_group10[MonLabo_team_page_parent]';
		name3 = 'MonLabo_settings_group10[MonLabo_thematic_page_parent]';
		name4 = 'MonLabo_settings_group10[MonLabo_unit_page_parent]';
		//Enable search field in select tags
		jQuery('#MonLabo_settings_group10\\[MonLabo_perso_page_parent\\]').select2();
		jQuery('#MonLabo_settings_group10\\[MonLabo_team_page_parent\\]').select2();
		jQuery('#MonLabo_settings_group10\\[MonLabo_thematic_page_parent\\]').select2();
		jQuery('#MonLabo_settings_group10\\[MonLabo_unit_page_parent\\]').select2();
		// On load, update thumbnail
		ajaxLoadPostThumbnailNow(1, `${name1}` );
		ajaxLoadPostThumbnailNow(2, `${name2}` );
		ajaxLoadPostThumbnailNow(3, `${name3}` );
		ajaxLoadPostThumbnailNow(4, `${name4}` );
		// Enables update touch scripts if the wordpress page is affected
		f = function(event) { ajaxLoadPostThumbnailNow( event.data.msg, event.data.selectname ); };
		jQuery( `select[name='${name1}']` ).on( 'change', {msg: 1, selectname: `${name1}` }, f );
		jQuery( `select[name='${name2}']` ).on( 'change', {msg: 2, selectname: `${name2}` }, f );
		jQuery( `select[name='${name3}']` ).on( 'change', {msg: 3, selectname: `${name3}` }, f );
		jQuery( `select[name='${name4}']` ).on( 'change', {msg: 4, selectname: `${name4}` }, f );
	}
	if ( jQuery( '#form_edit_person' ).length) {
		// Activates touch scripts on load
		touchPersonCategory();
		touchPersonFunction();
		touchPersonTitle();
		touchPersonStatus();
		// Enables update touch scripts if the category of function selectors are affected
		jQuery( "select[name='submit_category']" ).on( 'change', touchPersonCategory );
		jQuery( "select[name='submit_status']"   ).on( 'change', touchPersonStatus   );
		//Enable search field in select tags
		jQuery('#submit_mentors').select2({ width: '100%' });
		jQuery('#submit_students').select2({ width: '100%' });
	}
	if ( jQuery( '#form_edit_unit' ).length) {
		jQuery('#submit_directors').select2({ width: '100%' });
	}
	if ( jQuery( '#form_edit_team' ).length) {
		jQuery('#submit_leaders').select2({ width: '100%' });
		jQuery('#submit_persons').select2({ width: '100%', templateSelection: formatPersonsState });
		jQuery('#submit_thematics').select2({ width: '100%' });
	}
	if ( jQuery( '#form_edit_mainstruct' ).length) {
		jQuery('#submit_directors').select2({ width: '100%' });
	}	
	if ( jQuery( '.form_edit_item' ).length) {
		jQuery('#submit_item').select2();
		// Enables update touch scripts if the wordpress page is affected
		nbPages=jQuery("input[name^='submit_wp_post_ids']").length;
		f = function(event) { ajaxLoadPostThumbnailNow( event.data.msg ); };
		for (var i = 0; i < nbPages; i++) {
			jQuery( `input[name='submit_wp_post_ids[${i}]']` ).on('change', {msg: i}, f );
			PageButtonClick( jQuery( `input[name='pageradio[${i}]']:checked` ).val(), i );
			jQuery('#dropdown_submit_wp_post_ids\\[' + i + '\\]').select2({ width: '100%' });
			//console.log('#dropdown_submit_wp_post_ids\\[' + i + '\\]');
		}
		jQuery( "#external-mentors-invisible" ).hide();
		jQuery( "#external-students-invisible" ).hide();
		jQuery( "#mentors-fields-invisible" ).hide();
		jQuery( "#students-fields-invisible" ).hide();
	}
	jQuery("#MonLaboHideDivButton").attr("type", "button").on('click', function(){
		jQuery(this).parent().parent().parent().hide(); // Hide parent div of button (the whole box)
		jQuery("#MonLaboCanceledAction").show(); //Show cancel information pannel
	});
});