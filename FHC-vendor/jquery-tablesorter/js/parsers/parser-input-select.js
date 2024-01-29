/*! Parser: input & select - updated 11/22/2015 (v2.24.6) *//*
 * for jQuery 1.7+ & tablesorter 2.7.11+
 * Demo: http://mottie.github.com/tablesorter/docs/example-widget-grouping.html
 */
/*jshint browser: true, jquery:true, unused:false */
;( function( $ ) {
	'use strict';

	var updateServer = function( event, $table, $input ) {
		// do something here to update your server, if needed
		// event = change event object
		// $table = jQuery object of the table that was just updated
		// $input = jQuery object(s) of the input or select that was modified; in v2.24.6,
		//   if the thead has a checkbox, $input may include multiple elements
	};

	// Custom parser for parsing input values
	// updated dynamically using the 'change' function below
	$.tablesorter.addParser({
		id : 'inputs',
		is : function() {
			return false;
		},
		format : function( txt, table, cell ) {
			var $input = $( cell ).find( 'input' );
			return $input.length ? $input.val() : txt;
		},
		parsed : true, // filter widget flag
		type : 'text'
	});

	$.tablesorter.addParser({
		id : 'inputs-numeric',
		is : function() {
			return false;
		},
		format : function( txt, table, cell ) {
			var $input = $( cell ).find( 'input' );
			var val = $input.length ? $input.val() : txt,
				num = $.tablesorter.formatFloat( ( val || '' ).replace( /[^\w,. \-()]/g, '' ), table );
			return txt && typeof num === 'number' ? num :
				txt ? $.trim( txt && table.config.ignoreCase ? txt.toLocaleLowerCase() : txt ) : txt;
		},
		parsed : true, // filter widget flag
		type : 'numeric'
	});

	// Custom parser for including checkbox status if using the grouping widget
	// updated dynamically using the 'change' function below
	$.tablesorter.addParser({
		id : 'checkbox',
		is : function() {
			return false;
		},
		format : function( txt, table, cell, cellIndex ) {
			var $cell = $( cell ),
				$row = $cell.closest( 'tr' ),
				wo = table.config.widgetOptions,
				checkedClass = table.config.checkboxClass || 'checked',
				// returning plain language here because this is what is shown in the
				// group headers - change it as desired
				status = wo.group_checkbox ? wo.group_checkbox : [ 'checked', 'unchecked' ],
				$input = $cell.find( 'input[type="checkbox"]' ),
				isChecked = $input.length ? $input[ 0 ].checked : '';
			// adding class to row, indicating that a checkbox is checked; includes
			// a column index in case more than one checkbox happens to be in a row
			$row.toggleClass( checkedClass + '-' + cellIndex, isChecked );
			if ( isChecked ) {
				$row.addClass( checkedClass );
			} else if ( $row.length && !( $row[0].className || '' ).match( checkedClass + '-' ) ) {
				// don't remove checked class if other columns have a check
				$row.removeClass( checkedClass );
			}
			return $input.length ? status[ isChecked ? 0 : 1 ] : txt;
		},
		parsed : true, // filter widget flag
		type : 'text'
	});

	// Custom parser which returns the currently selected options
	// updated dynamically using the 'change' function below
	$.tablesorter.addParser({
		id : 'select',
		is : function() {
			return false;
		},
		format : function( txt, table, cell ) {
			var $select = $( cell ).find( 'select' );
			return $select.length ? $select.val() : txt;
		},
		parsed : true, // filter widget flag
		type : 'text'
	});

	// Select parser to get the selected text
	$.tablesorter.addParser({
		id : 'select-text',
		is : function() {
			return false;
		},
		format : function( txt, table, cell ) {
			var $select = $( cell ).find( 'select' );
			return $select.length ? $select.find( 'option:selected' ).text() || '' : txt;
		},
		parsed : true, // filter widget flag
		type : 'text'
	});

	// Custom parser for parsing textarea values
	// updated dynamically using the 'change' function below
	$.tablesorter.addParser({
		id : 'textarea',
		is : function() {
			return false;
		},
		format : function( txt, table, cell ) {
			var $textarea = $( cell ).find( 'textarea' );
			return $textarea.length ? $textarea.val() : txt;
		},
		parsed : true, // filter widget flag
		type : 'text'
	});

	// update select and all input types in the tablesorter cache when the change event fires.
	// This method only works with jQuery 1.7+
	// you can change it to use delegate (v1.4.3+) or live (v1.3+) as desired
	// if this code interferes somehow, target the specific table $('#mytable'), instead of $('table')
	$( function() {
		if ( !$.fn.on ) { return; }
		$( 'table' ).on( 'tablesorter-initialized updateComplete', function() {
			this.tablesorterBusy = false;
			var namespace = '.parser-forms';
			// bind to .tablesorter (default class name)
			$( this ).children( 'tbody' )
			.off( namespace )
			.on( 'mouseleave' + namespace, function( event ) {
				// make sure we restore original values (trigger blur)
				// isTbody is needed to prevent the select from closing in IE
				// see https://connect.microsoft.com/IE/feedbackdetail/view/962618/
				if ( event.target.nodeName === 'TBODY' ) {
					$( ':focus' ).blur();
				}
			})
			.on( 'focus' + namespace, 'select, input, textarea', function() {
				$( this ).data( 'ts-original-value', this.value );
			})
			.on( 'blur' + namespace, 'input, textarea', function() {
				// restore input value;
				// 'change' is triggered before 'blur' so this doesn't replace the new update with the original
				this.value = $( this ).data( 'ts-original-value' );
			})
			.on( 'change keyup '.split( ' ' ).join( namespace + ' ' ), 'select, input, textarea', function( event ) {
				if ( event.which === 27 ) {
					// escape: restore original value
					this.value = $( this ).data( 'ts-original-value' );
					return;
				}
				// Update cell cache using... select: change, input: enter or textarea: alt + enter
				if ( event.type === 'change' ||
					( event.type === 'keyup' && event.which === 13 &&
					( event.target.nodeName === 'INPUT' || event.target.nodeName === 'TEXTAREA' && event.altKey ) ) ) {
					var undef,
						$target = $( event.target ),
						isCheckbox = event.target.type === 'checkbox',
						$cell = $target.closest( 'td' ),
						$table = $cell.closest( 'table' ),
						indx = $cell[ 0 ].cellIndex,
						c = $table[ 0 ].config || false,
						busy = $table.length && $table[ 0 ].tablesorterBusy,
						$hdr = c && c.$headerIndexed && c.$headerIndexed[ indx ] || [],
						val = isCheckbox ? event.target.checked : $target.val();
					// abort if not a tablesorter table, or busy, or don't use updateCell if column is set
					// to 'sorter-false' and 'filter-false', or column is set to 'parser-false'
					if ( $.isEmptyObject( c ) || busy !== false || $hdr.length && ( $hdr.hasClass( 'parser-false' ) ||
						( $hdr.hasClass( 'sorter-false' ) && $hdr.hasClass( 'filter-false' ) ) ) ||
						// table already updating; get out of here, we might be in an endless loop (in IE)! See #971
						( event.type === 'change' && c.table.isUpdating ) ) {
						return;
					}
					// ignore change event if nothing changed
					if ( c && val !== $target.data( 'ts-original-value' ) || isCheckbox ) {
						$target.data( 'ts-original-value', val );
						$table[ 0 ].tablesorterBusy = true;
						// pass undefined resort value so it falls back to config.resort setting
						$.tablesorter.updateCell( c, $cell, undef, function() {
							updateServer( event, $table, $target );
							$table[ 0 ].tablesorterBusy = false;
						});
					}
				}
			});

			// add code for a checkbox in the header to set/unset all checkboxes in a column
			if ( $( this ).children( 'thead' ).find( 'input[type="checkbox"]' ) ) {
				$( this )
				.off( namespace )
				.on( 'tablesorter-ready' + namespace, function() {
					var checkboxClass, $rows, len,
						$table = $( this ),
						c = $table.length && $table[ 0 ].config;
					if ( !$.isEmptyObject( c ) ) {
						this.tablesorterBusy = true;
						checkboxClass = c && c.checkboxClass || 'checked';
						$rows = $table.children( 'tbody' ).children( ':visible' ); // (include child rows?)
						len = $rows.length;
						// set indeterminate state on header checkbox
						$( this ).children( 'thead' ).find( 'input[type="checkbox"]' ).each( function() {
							var column = $( this ).closest( 'td, th' ).attr( 'data-column' ),
								vis = $rows.filter( '.' + checkboxClass + '-' + column ).length,
								allChecked = vis === len;
							if ( vis === 0 || allChecked ) {
								this.checked = allChecked;
								this.indeterminate = false;
							} else {
								this.indeterminate = true;
							}
						});
						this.tablesorterBusy = false;
					}
				})
				.children( 'thead' )
				.off( namespace )
				// modified from http://jsfiddle.net/abkNM/6163/
				.on( 'change' + namespace, 'input[type="checkbox"]', function( event ) {
					var undef, onlyVisible, column, $target,
						$checkbox = $( this ),
						$table = $checkbox.closest( 'table' ),
						c = $table.length && $table[ 0 ].config,
						isChecked = this.checked;
					if ( $table.length && c && !$table[ 0 ].tablesorterBusy ) {
						column = parseInt( $checkbox.closest( 'td, th' ).attr( 'data-column' ), 10 );
						onlyVisible = $table.length && c.checkboxVisible;
						$table[ 0 ].tablesorterBusy = true; // prevent "change" event from calling updateCell numerous times (see #971)
						$target = $table
							.children( 'tbody' )
							.children( 'tr' + ( typeof onlyVisible === 'undefined' || onlyVisible === true ? ':visible' : '' ) )
							.children( ':nth-child(' + ( column + 1 ) + ')' )
							.find( 'input[type="checkbox"]' )
							.prop( 'checked', isChecked );
						$.tablesorter.update( c, undef, function() {
							updateServer( event, $table, $target );
							$table[ 0 ].tablesorterBusy = false;
						});
					}
					// update already going on, don't do anything!
					return false;
				});
			}

		});
	});

})( jQuery );
