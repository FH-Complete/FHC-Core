/*! Widget: math - updated 11/22/2015 (v2.24.6) *//*
* Requires tablesorter v2.16+ and jQuery 1.7+
* by Rob Garrison
*/
/*jshint browser:true, jquery:true, unused:false */
/*global jQuery: false */
;( function( $ ) {
	'use strict';

	var ts = $.tablesorter,

	math = {

		error: {
			0       : 'Infinity result: Divide by zero',
			1       : 'Need more than one element to make this calculation',
			'undef' : 'No elements found'
		},

		// value returned when calculation is not possible, e.g. no values, dividing by zero, etc.
		invalid : function( c, name, errorIndex ) {
			// name = function returning invalid results
			// errorIndex = math.error index with an explanation of the error
			console.log( name, math.error[ errorIndex ] );
			return c && c.widgetOptions.math_none || 'none'; // text for cell
		},

		events : ( 'tablesorter-initialized update updateAll updateRows addRows updateCell ' +
			'filterReset filterEnd ' ).split(' ').join('.tsmath '),

		processText : function( c, $cell ) {
			var txt = $cell.attr( c.textAttribute );
			if ( typeof txt === 'undefined' ) {
				txt = $cell[0].textContent || $cell.text();
			}
			txt = ts.formatFloat( txt.replace( /[^\w,. \-()]/g, '' ), c.table ) || 0;
			// isNaN('') => false
			return isNaN( txt ) ? 0 : txt;
		},

		// get all of the row numerical values in an arry
		getRow : function( c, $el ) {
			var $cells,
				wo = c.widgetOptions,
				arry = [],
				$row = $el.closest( 'tr' ),
				isFiltered = $row.hasClass( wo.filter_filteredRow || 'filtered' ),
				hasFilter = wo.math_rowFilter;
			if ( hasFilter ) {
				$row = $row.filter( hasFilter );
			}
			if ( !isFiltered || hasFilter ) {
				$cells = $row.children().not( '[' + wo.math_dataAttrib + '=ignore]' );
				if ( wo.math_ignore.length ) {
					$cells = $cells.not( '[data-column=' + wo.math_ignore.join( '],[data-column=' ) + ']' );
				}
				arry = $cells.not( $el ).map( function() {
					return math.processText( c, $( this ) );
				}).get();
			}
			return arry;
		},

		// get all of the column numerical values in an arry
		getColumn : function( c, $el, type ) {
			var index, $t, $tr, len, $mathRows, mathAbove,
				arry = [],
				wo = c.widgetOptions,
				hasFilter = wo.math_rowFilter,
				mathAttr = wo.math_dataAttrib,
				filtered = wo.filter_filteredRow || 'filtered',
				cIndex = parseInt( $el.attr( 'data-column' ), 10 ),
				$rows = c.$table.children( 'tbody' ).children(),
				$row = $el.closest( 'tr' );
			// make sure tfoot rows are AFTER the tbody rows
			// $rows.add( c.$table.children( 'tfoot' ).children() );
			if ( type === 'above' ) {
				len = $rows.index( $row );
				index = len;
				while ( index >= 0 ) {
					$tr = $rows.eq( index );
					if ( hasFilter ) {
						$tr = $tr.filter( wo.math_rowFilter );
					}
					$t = $tr.children().filter( '[data-column=' + cIndex + ']' );
					mathAbove = $t.filter( '[' + mathAttr + '^=above]' ).length;
					// ignore filtered rows & rows with data-math="ignore" (and starting row)
					if ( ( ( !$tr.hasClass( filtered ) || hasFilter ) &&
							$tr.not( '[' + mathAttr + '=ignore]' ).length &&
							index !== len ) ||
							mathAbove && index !== len ) {
						// stop calculating 'above', when encountering another 'above'
						if ( mathAbove ) {
							index = 0;
						} else if ( $t.length ) {
							arry.push( math.processText( c, $t ) );
						}
					}
					index--;
				}
			} else if ( type === 'below' ) {
				len = $rows.length;
				// index + 1 to ignore starting node
				for ( index = $rows.index( $row ) + 1; index < len; index++ ) {
					$tr = $rows.eq( index );
					if ( hasFilter ) {
						$tr = $tr.filter( hasFilter );
					}
					$t = $tr.children().filter( '[data-column=' + cIndex + ']' );
					if ( $t.filter( '[' + mathAttr + '^=below]' ).length ) {
						break;
					}
					if ( ( !$tr.hasClass( filtered ) || hasFilter ) &&
							$tr.not( '[' + mathAttr + '=ignore]' ).length &&
							$t.length ) {
						arry.push( math.processText( c, $t ) );
					}
				}

			} else {
				$mathRows = $rows.not( '[' + mathAttr + '=ignore]' );
				len = $mathRows.length;
				for ( index = 0; index < len; index++ ) {
					$tr = $mathRows.eq( index );
					if ( hasFilter ) {
						$tr = $tr.filter( hasFilter );
					}
					$t = $tr.children().filter( '[data-column=' + cIndex + ']' );
					if ( ( !$tr.hasClass( filtered ) || hasFilter ) &&
						$t.not( '[' + mathAttr + '^=above],[' + mathAttr + '^=below],[' + mathAttr + '^=col]' ).length &&
						!$t.is( $el ) ) {
						arry.push( math.processText( c, $t ) );
					}
				}
			}
			return arry;
		},

		// get all of the column numerical values in an arry
		getAll : function( c ) {
			var $t, col, $row, rowIndex, rowLen, $cells, cellIndex, cellLen,
				arry = [],
				wo = c.widgetOptions,
				mathAttr = wo.math_dataAttrib,
				filtered = wo.filter_filteredRow || 'filtered',
				hasFilter = wo.filter_rowFilter,
				$rows = c.$table.children( 'tbody' ).children().not( '[' + mathAttr + '=ignore]' );
			rowLen = $rows.length;
			for ( rowIndex = 0; rowIndex < rowLen; rowIndex++ ) {
				$row = $rows.eq( rowIndex );
				if ( hasFilter ) {
					$row = $row.filter( hasFilter );
				}
				if ( !$row.hasClass( filtered ) || hasFilter ) {
					$cells = $row.children().not( '[' + mathAttr + '=ignore]' );
					cellLen = $cells.length;
					// $row.children().each(function(){
					for ( cellIndex = 0; cellIndex < cellLen; cellIndex++ ) {
						$t = $cells.eq( cellIndex );
						col = parseInt( $t.attr( 'data-column' ), 10);
						if ( !$t.filter( '[' + mathAttr + ']' ).length && $.inArray( col, wo.math_ignore ) < 0 ) {
							arry.push( math.processText( c, $t ) );
						}
					}
				}
			}
			return arry;
		},

		setColumnIndexes : function( c ) {
			c.$table.after( '<div id="_tablesorter_table_placeholder"></div>' );
			// detach table from DOM to speed up column indexing
			var $table = c.$table.detach();
			ts.computeColumnIndex( $table.children( 'tbody' ).children() );
			$( '#_tablesorter_table_placeholder' )
				.after( $table )
				.remove();
		},

		recalculate : function(c, wo, init) {
			if ( c && ( !wo.math_isUpdating || init ) ) {

				var undef, time, mathAttr, $mathCells;
				if ( c.debug ) {
					time = new Date();
				}

				// add data-column attributes to all table cells
				if ( init ) {
					math.setColumnIndexes( c ) ;
				}

				// data-attribute name (defaults to data-math)
				wo.math_dataAttrib = 'data-' + (wo.math_data || 'math');

				// all non-info tbody cells
				mathAttr = wo.math_dataAttrib;
				$mathCells = c.$tbodies.children( 'tr' ).children( '[' + mathAttr + ']' );
				math.mathType( c, $mathCells, wo.math_priority );

				// only info tbody cells
				$mathCells = c.$table
					.children( '.' + c.cssInfoBlock + ', tfoot' )
					.children( 'tr' )
					.children( '[' + mathAttr + ']' );
				math.mathType( c, $mathCells, wo.math_priority );

				// find the 'all' total
				$mathCells = c.$table.children().children( 'tr' ).children( '[' + mathAttr + '^=all]' );
				math.mathType( c, $mathCells, [ 'all' ] );

				wo.math_isUpdating = true;
				if ( c.debug ) {
					console[ console.group ? 'group' : 'log' ]( 'Math widget triggering an update after recalculation' );
				}

				// update internal cache
				ts.update( c, undef, function(){
					math.updateComplete( c );
				});

				if ( c.debug ) {
					console.log( 'Math widget update completed' + ts.benchmark( time ) );
				}
			}
		},

		updateComplete : function( c ) {
			var wo = c.widgetOptions;
			if ( wo.math_isUpdating && c.debug && console.groupEnd ) { console.groupEnd(); }
			wo.math_isUpdating = false;
		},

		mathType : function( c, $cells, priority ) {
			if ( $cells.length ) {
				var formula, result, $el, arry, getAll, $targetCells, index, len,
					wo = c.widgetOptions,
					mathAttr = wo.math_dataAttrib,
					equations = ts.equations;
				if ( priority[0] === 'all' ) {
					// no need to get all cells more than once
					getAll = math.getAll( c );
				}
				if (c.debug) {
					console[ console.group ? 'group' : 'log' ]( 'Tablesorter Math widget recalculation' );
				}
				// $.each is okay here... only 4 priorities
				$.each( priority, function( i, type ) {
					$targetCells = $cells.filter( '[' + mathAttr + '^=' + type + ']' );
					len = $targetCells.length;
					if ( len ) {
						if (c.debug) {
							console[ console.group ? 'group' : 'log' ]( type );
						}
						for ( index = 0; index < len; index++ ) {
							$el = $targetCells.eq( index );
							// Row is filtered, no need to do further checking
							if ( $el.parent().hasClass( wo.filter_filteredRow || 'filtered' ) ) {
								continue;
							}
							formula = ( $el.attr( mathAttr ) || '' ).replace( type + '-', '' );
							arry = ( type === 'row' ) ? math.getRow( c, $el ) :
								( type === 'all' ) ? getAll : math.getColumn( c, $el, type );
							if ( equations[ formula ] ) {
								if ( arry.length ) {
									result = equations[ formula ]( arry, c );
									if ( c.debug ) {
										console.log( $el.attr( mathAttr ), arry, '=', result );
									}
								} else {
									// mean will return a divide by zero error, everything else shows an undefined error
									result = math.invalid( c, formula, formula === 'mean' ? 0 : 'undef' );
								}
								math.output( $el, wo, result, arry );
							}
						}
						if ( c.debug && console.groupEnd ) { console.groupEnd(); }
					}
				});
				if ( c.debug && console.groupEnd ) { console.groupEnd(); }
			}
		},

		output : function( $cell, wo, value, arry ) {
			// get mask from cell data-attribute: data-math-mask="#,##0.00"
			var mask = $cell.attr( 'data-' + wo.math_data + '-mask' ) || wo.math_mask,
				result = ts.formatMask( mask, value, wo.math_wrapPrefix, wo.math_wrapSuffix );
			if ( typeof wo.math_complete === 'function' ) {
				result = wo.math_complete( $cell, wo, result, value, arry );
			}
			if ( result !== false ) {
				$cell.html( result );
			}
		}

	};

	// Modified from https://code.google.com/p/javascript-number-formatter/
	/**
	* @preserve IntegraXor Web SCADA - JavaScript Number Formatter
	* http:// www.integraxor.com/
	* author: KPL, KHL
	* (c)2011 ecava
	* Dual licensed under the MIT or GPL Version 2 licenses.
	*/
	ts.formatMask = function( mask, val, tmpPrefix, tmpSuffix ) {
		if ( !mask || isNaN( +val ) ) {
			return val; // return as it is.
		}

		var isNegative, result, decimal, group, posLeadZero, posTrailZero, posSeparator, part, szSep,
			integer, str, offset, index, end, inv,
			suffix = '',

			// find prefix/suffix
			len = mask.length,
			start = mask.search( /[0-9\-\+#]/ ),
			tmp = start > 0 ? mask.substring( 0, start ) : '',
			prefix = tmp;

		if ( start > 0 && tmpPrefix ) {
			if ( /\{content\}/.test( tmpPrefix || '' ) ) {
				prefix = ( tmpPrefix || '' ).replace( /\{content\}/g, tmp || '' );
			} else {
				prefix = ( tmpPrefix || '' ) + tmp;
			}
		}
		// reverse string: not an ideal method if there are surrogate pairs
		inv = mask.split( '' ).reverse().join( '' );
		end = inv.search( /[0-9\-\+#]/ );
		index = len - end;
		index += ( mask.substring( index, index + 1 ) === '.' ) ? 1 : 0;
		tmp = end > 0 ? mask.substring( index, len ) : '';
		suffix = tmp;
		if ( tmp !== '' && tmpSuffix ) {
			if ( /\{content\}/.test( tmpSuffix || '' ) ) {
				suffix = ( tmpSuffix || '' ).replace( /\{content\}/g, tmp || '' );
			} else {
				suffix = tmp + ( tmpSuffix || '' );
			}
		}
		mask = mask.substring( start, index );

		// convert any string to number according to formation sign.
		val = mask.charAt( 0 ) == '-' ? -val : +val;
		isNegative = val < 0 ? val = -val : 0; // process only abs(), and turn on flag.

		// search for separator for grp & decimal, anything not digit, not +/- sign, not #.
		result = mask.match( /[^\d\-\+#]/g );
		decimal = ( result && result[ result.length - 1 ] ) || '.'; // treat the right most symbol as decimal
		group = ( result && result[ 1 ] && result[ 0 ] ) || ',';  // treat the left most symbol as group separator

		// split the decimal for the format string if any.
		mask = mask.split( decimal );
		// Fix the decimal first, toFixed will auto fill trailing zero.
		val = val.toFixed( mask[ 1 ] && mask[ 1 ].length );
		val = +( val ) + ''; // convert number to string to trim off *all* trailing decimal zero(es)

		// fill back any trailing zero according to format
		posTrailZero = mask[ 1 ] && mask[ 1 ].lastIndexOf( '0' ); // look for last zero in format
		part = val.split( '.' );
		// integer will get !part[1]
		if ( !part[ 1 ] || ( part[ 1 ] && part[ 1 ].length <= posTrailZero ) ) {
			val = ( +val ).toFixed( posTrailZero + 1 );
		}
		szSep = mask[ 0 ].split( group ); // look for separator
		mask[ 0 ] = szSep.join( '' ); // join back without separator for counting the pos of any leading 0.

		posLeadZero = mask[ 0 ] && mask[ 0 ].indexOf( '0' );
		if ( posLeadZero > -1 ) {
			while ( part[ 0 ].length < ( mask[ 0 ].length - posLeadZero ) ) {
				part[ 0 ] = '0' + part[ 0 ];
			}
		} else if ( +part[ 0 ] === 0 ) {
			part[ 0 ] = '';
		}

		val = val.split( '.' );
		val[ 0 ] = part[ 0 ];

		// process the first group separator from decimal (.) only, the rest ignore.
		// get the length of the last slice of split result.
		posSeparator = ( szSep[ 1 ] && szSep[ szSep.length - 1 ].length );
		if ( posSeparator ) {
			integer = val[ 0 ];
			str = '';
			offset = integer.length % posSeparator;
			len = integer.length;
			for ( index = 0; index < len; index++ ) {
				str += integer.charAt( index ); // ie6 only support charAt for sz.
				// -posSeparator so that won't trail separator on full length
				/*jshint -W018 */
				if ( !( ( index - offset + 1 ) % posSeparator ) && index < len - posSeparator ) {
					str += group;
				}
			}
			val[ 0 ] = str;
		}

		val[ 1 ] = ( mask[ 1 ] && val[ 1 ] ) ? decimal + val[ 1 ] : '';
		// put back any negation, combine integer and fraction, and add back prefix & suffix
		return prefix + ( ( isNegative ? '-' : '' ) + val[ 0 ] + val[ 1 ] ) + suffix;
	};

	ts.equations = {
		count : function( arry ) {
			return arry.length;
		},
		sum : function( arry ) {
			var index,
				len = arry.length,
				total = 0;
			for ( index = 0; index < len; index++ ) {
				total += arry[ index ];
			}
			return total;
		},
		mean : function( arry ) {
			var total = ts.equations.sum( arry );
			return total / arry.length;
		},
		median : function( arry, c ) {
			var half,
				len = arry.length;
			if ( len > 1 ) {
				// https://gist.github.com/caseyjustus/1166258
				arry.sort( function( a, b ){ return a - b; } );
				half = Math.floor( len / 2 );
				return ( len % 2 ) ? arry[ half ] : ( arry[ half - 1 ] + arry[ half ] ) / 2;
			}
			return math.invalid( c, 'median', 1 );
		},
		mode : function( arry ) {
			// http://stackoverflow.com/a/3451640/145346
			var index, el, m,
				modeMap = {},
				maxCount = 1,
				modes = [ arry[ 0 ] ];
			for ( index = 0; index < arry.length; index++ ) {
				el = arry[ index ];
				modeMap[ el ] = modeMap[ el ] ? modeMap[ el ] + 1 : 1;
				m = modeMap[ el ];
				if ( m > maxCount ) {
					modes = [ el ];
					maxCount = m;
				} else if ( m === maxCount ) {
					modes.push( el );
					maxCount = m;
				}
			}
			// returns arry of modes if there is a tie
			return modes.sort( function( a, b ){ return a - b; } );
		},
		max : function(arry) {
			return Math.max.apply( Math, arry );
		},
		min : function(arry) {
			return Math.min.apply( Math, arry );
		},
		range: function(arry) {
			var v = arry.sort( function( a, b ){ return a - b; } );
			return v[ arry.length - 1 ] - v[ 0 ];
		},
		// common variance equation
		// (not accessible via data-attribute setting)
		variance: function( arry, population, c ) {
			var divisor,
				avg = ts.equations.mean( arry ),
				v = 0,
				i = arry.length;
			while ( i-- ) {
				v += Math.pow( ( arry[ i ] - avg ), 2 );
			}
			divisor = ( arry.length - ( population ? 0 : 1 ) );
			if ( divisor === 0 ) {
				return math.invalid( c, 'variance', 0 );
			}
			v /= divisor;
			return v;
		},
		// variance (population)
		varp : function( arry, c ) {
			return ts.equations.variance( arry, true, c );
		},
		// variance (sample)
		vars : function( arry, c ) {
			return ts.equations.variance( arry, false, c );
		},
		// standard deviation (sample)
		stdevs : function( arry, c ) {
			var vars = ts.equations.variance( arry, false, c );
			return Math.sqrt( vars );
		},
		// standard deviation (population)
		stdevp : function( arry, c ) {
			var varp = ts.equations.variance( arry, true, c );
			return Math.sqrt( varp );
		}
	};

	// add new widget called repeatHeaders
	// ************************************
	ts.addWidget({
		id: 'math',
		priority: 100,
		options: {
			math_data     : 'math',
			// column index to ignore
			math_ignore   : [],
			// mask info: https://code.google.com/p/javascript-number-formatter/
			math_mask     : '#,##0.00',
			// complete executed after each fucntion
			math_complete : null, // function($cell, wo, result, value, arry){ return result; },
			// order of calculation; 'all' is last
			math_priority : [ 'row', 'above', 'below', 'col' ],
			// template for or just prepend the mask prefix & suffix with this HTML
			// e.g. '<span class="red">{content}</span>'
			math_prefix   : '',
			math_suffix   : '',
			// no matching math elements found (text added to cell)
			math_none     : 'N/A',
			math_event    : 'recalculate',
			// use this filter to target specific rows (e.g. ':visible', or ':not(.empty-row)')
			math_rowFilter: ''
		},
		init : function( table, thisWidget, c, wo ) {
			// filterEnd fires after updateComplete
			var update = ts.hasWidget( table, 'filter' ) ? 'filterEnd' : 'updateComplete';
			c.$table
				.off( ( math.events + ' updateComplete.tsmath ' + wo.math_event ).replace( /\s+/g, ' ' ) )
				.on( math.events + ' ' + wo.math_event, function( e ) {
					var init = e.type === 'tablesorter-initialized';
					if ( !wo.math_isUpdating || init ) {
						if ( !/filter/.test( e.type ) ) {
							// redo data-column indexes on update
							math.setColumnIndexes( c ) ;
						}
						math.recalculate( c, wo, init );
					}
				})
				.on( update + '.tsmath', function() {
					setTimeout( function(){
						math.updateComplete( c );
					}, 40 );
				});
			wo.math_isUpdating = false;
			// math widget initialized after table - see #946
			if ( table.hasInitialized ) {
				math.recalculate( c, wo, true );
			}
		},
		// this remove function is called when using the refreshWidgets method or when destroying the tablesorter plugin
		// this function only applies to tablesorter v2.4+
		remove: function( table, c, wo, refreshing ) {
			if ( refreshing ) { return; }
			c.$table
				.off( ( math.events + ' updateComplete.tsmath ' + wo.math_event ).replace( /\s+/g, ' ' ) )
				.children().children( 'tr' ).children( '[data-' + wo.math_data + ']' ).empty();
		}
	});

})(jQuery);
