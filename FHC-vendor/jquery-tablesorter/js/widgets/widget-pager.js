/*! Widget: Pager - updated 11/22/2015 (v2.24.6) */
/* Requires tablesorter v2.8+ and jQuery 1.7+
 * by Rob Garrison
 */
/*jshint browser:true, jquery:true, unused:false */
;(function($){
	'use strict';
	var tsp,
	ts = $.tablesorter;

	ts.addWidget({
		id: 'pager',
		priority: 55, // load pager after filter widget
		options: {
			// output default: '{page}/{totalPages}'
			// possible variables: {size}, {page}, {totalPages}, {filteredPages}, {startRow},
			// {endRow}, {filteredRows} and {totalRows}
			pager_output: '{startRow} to {endRow} of {totalRows} rows', // '{page}/{totalPages}'

			// apply disabled classname to the pager arrows when the rows at either extreme is visible
			pager_updateArrows: true,

			// starting page of the pager (zero based index)
			pager_startPage: 0,

			// reset pager after filtering; set to desired page #
			// set to false to not change page at filter start
			pager_pageReset: 0,

			// Number of visible rows
			pager_size: 10,

			// Number of options to include in the pager number selector
			pager_maxOptionSize: 20,

			// Save pager page & size if the storage script is loaded (requires $.tablesorter.storage
			// in jquery.tablesorter.widgets.js)
			pager_savePages: true,

			// defines custom storage key
			pager_storageKey: 'tablesorter-pager',

			// if true, the table will remain the same height no matter how many records are displayed.
			// The space is made up by an empty table row set to a height to compensate; default is false
			pager_fixedHeight: false,

			// count child rows towards the set page size? (set true if it is a visible table row within the pager)
			// if true, child row(s) may not appear to be attached to its parent row, may be split across pages or
			// may distort the table if rowspan or cellspans are included.
			pager_countChildRows: false,

			// remove rows from the table to speed up the sort of large tables.
			// setting this to false, only hides the non-visible rows; needed if you plan to add/remove rows with
			// the pager enabled.
			pager_removeRows: false, // removing rows in larger tables speeds up the sort

			// use this format: 'http://mydatabase.com?page={page}&size={size}&{sortList:col}&{filterList:fcol}'
			// where {page} is replaced by the page number, {size} is replaced by the number of records to show,
			// {sortList:col} adds the sortList to the url into a 'col' array, and {filterList:fcol} adds
			// the filterList to the url into an 'fcol' array.
			// So a sortList = [[2,0],[3,0]] becomes '&col[2]=0&col[3]=0' in the url
			// and a filterList = [[2,Blue],[3,13]] becomes '&fcol[2]=Blue&fcol[3]=13' in the url
			pager_ajaxUrl: null,

			// modify the url after all processing has been applied
			pager_customAjaxUrl: function( table, url ) { return url; },

			// ajax error callback from $.tablesorter.showError function
			// pager_ajaxError: function( config, xhr, settings, exception ){ return exception; };
			// returning false will abort the error message
			pager_ajaxError: null,

			// modify the $.ajax object to allow complete control over your ajax requests
			pager_ajaxObject: {
				dataType: 'json'
			},

			// set this to false if you want to block ajax loading on init
			pager_processAjaxOnInit: true,

			// process ajax so that the following information is returned:
			// [ total_rows (number), rows (array of arrays), headers (array; optional) ]
			// example:
			// [
			//   100,  // total rows
			//   [
			//     [ "row1cell1", "row1cell2", ... "row1cellN" ],
			//     [ "row2cell1", "row2cell2", ... "row2cellN" ],
			//     ...
			//     [ "rowNcell1", "rowNcell2", ... "rowNcellN" ]
			//   ],
			//   [ "header1", "header2", ... "headerN" ] // optional
			// ]
			pager_ajaxProcessing: function( ajax ){ return [ 0, [], null ]; },

			// css class names of pager arrows
			pager_css: {
				container   : 'tablesorter-pager',
				// error information row (don't include period at beginning)
				errorRow    : 'tablesorter-errorRow',
				// class added to arrows @ extremes (i.e. prev/first arrows 'disabled' on first page)
				disabled    : 'disabled'
			},

			// jQuery selectors
			pager_selectors: {
				container   : '.pager',       // target the pager markup
				first       : '.first',       // go to first page arrow
				prev        : '.prev',        // previous page arrow
				next        : '.next',        // next page arrow
				last        : '.last',        // go to last page arrow
				gotoPage    : '.gotoPage',    // go to page selector - select dropdown that sets the current page
				pageDisplay : '.pagedisplay', // location of where the 'output' is displayed
				pageSize    : '.pagesize'     // page size selector - select dropdown that sets the 'size' option
			}
		},
		init: function( table ) {
			tsp.init( table );
		},
		// only update to complete sorter initialization
		format: function( table, c ) {
			if ( !( c.pager && c.pager.initialized ) ) {
				return tsp.initComplete( c );
			}
			tsp.moveToPage( c, c.pager, false );
		},
		remove: function( table, c, wo, refreshing ) {
			tsp.destroyPager( c, refreshing );
		}
	});

	/* pager widget functions */
	tsp = ts.pager = {

		init: function( table ) {
			// check if tablesorter has initialized
			if ( table.hasInitialized && table.config.pager && table.config.pager.initialized ) { return; }
			var t,
				c = table.config,
				wo = c.widgetOptions,
				s = wo.pager_selectors,

				// save pager variables
				p = c.pager = $.extend({
					totalPages: 0,
					filteredRows: 0,
					filteredPages: 0,
					currentFilters: [],
					page: wo.pager_startPage,
					startRow: 0,
					endRow: 0,
					ajaxCounter: 0,
					$size: null,
					last: {},
					// save original pager size
					setSize: wo.pager_size,
					setPage: wo.pager_startPage
				}, c.pager );

			// pager initializes multiple times before table has completed initialization
			if ( p.isInitializing ) { return; }

			p.isInitializing = true;
			if ( c.debug ) {
				console.log( 'Pager: Initializing' );
			}

			p.size = $.data( table, 'pagerLastSize' ) || wo.pager_size;
			// added in case the pager is reinitialized after being destroyed.
			p.$container = $( s.container ).addClass( wo.pager_css.container ).show();
			// goto selector
			p.$goto = p.$container.find( s.gotoPage ); // goto is a reserved word #657
			// page size selector
			p.$size = p.$container.find( s.pageSize );
			p.totalRows = c.$tbodies.eq( 0 )
				.children( 'tr' )
				.not( wo.pager_countChildRows ? '' : '.' + c.cssChildRow )
				.length;
			p.oldAjaxSuccess = p.oldAjaxSuccess || wo.pager_ajaxObject.success;
			c.appender = tsp.appender;
			p.initializing = true;
			if ( wo.pager_savePages && ts.storage ) {
				t = ts.storage( table, wo.pager_storageKey ) || {}; // fixes #387
				p.page = ( isNaN( t.page ) ? p.page : t.page ) || p.setPage || 0;
				p.size = ( isNaN( t.size ) ? p.size : t.size ) || p.setSize || 10;
				$.data( table, 'pagerLastSize', p.size );
			}

			// skipped rows
			p.regexRows = new RegExp( '(' + ( wo.filter_filteredRow || 'filtered' ) + '|' +
				c.selectorRemove.slice( 1 ) + '|' + c.cssChildRow + ')' );

			// clear initialized flag
			p.initialized = false;
			// before initialization event
			c.$table.trigger( 'pagerBeforeInitialized', c );

			tsp.enablePager( c, false );

			// p must have ajaxObject
			p.ajaxObject = wo.pager_ajaxObject;
			p.ajaxObject.url = wo.pager_ajaxUrl;

			if ( typeof wo.pager_ajaxUrl === 'string' ) {
				// ajax pager; interact with database
				p.ajax = true;
				// When filtering with ajax, allow only custom filtering function, disable default filtering
				// since it will be done server side.
				wo.filter_serversideFiltering = true;
				c.serverSideSorting = true;
				tsp.moveToPage( c, p );
			} else {
				p.ajax = false;
				// Regular pager; all rows stored in memory
				ts.appendCache( c, true ); // true = don't apply widgets
			}

		},

		initComplete: function( c ) {
			var p = c.pager;
			tsp.bindEvents( c );
			tsp.setPageSize( c, 0 ); // page size 0 is ignored
			if ( !p.ajax ) {
				tsp.hideRowsSetup( c );
			}

			// pager initialized
			p.initialized = true;
			p.initializing = false;
			p.isInitializing = false;
			if ( c.debug ) {
				console.log( 'Pager: Triggering pagerInitialized' );
			}
			c.$table.trigger( 'pagerInitialized', c );
			// filter widget not initialized; it will update the output display & fire off the pagerComplete event
			if ( !( c.widgetOptions.filter_initialized && ts.hasWidget( c.table, 'filter' ) ) ) {
				// if ajax, then don't fire off pagerComplete
				tsp.updatePageDisplay( c, !p.ajax );
			}
		},

		bindEvents: function( c ) {
			var ctrls, fxn,
				p = c.pager,
				wo = c.widgetOptions,
				namespace = c.namespace + 'pager',
				s = wo.pager_selectors;

			c.$table
				.off( namespace )
				.on( 'filterInit filterStart '.split( ' ' ).join( namespace + ' ' ), function( e, filters ) {
					p.currentFilters = $.isArray( filters ) ? filters : c.$table.data( 'lastSearch' );
					// don't change page if filters are the same (pager updating, etc)
					if ( e.type === 'filterStart' && wo.pager_pageReset !== false &&
						( c.lastCombinedFilter || '' ) !== ( p.currentFilters || [] ).join( '' ) ) {
						p.page = wo.pager_pageReset; // fixes #456 & #565
					}
				})
				// update pager after filter widget completes
				.on( 'filterEnd sortEnd '.split( ' ' ).join( namespace + ' ' ), function() {
					p.currentFilters = c.$table.data( 'lastSearch' );
					if ( p.initialized || p.initializing ) {
						if ( c.delayInit && c.rowsCopy && c.rowsCopy.length === 0 ) {
							// make sure we have a copy of all table rows once the cache has been built
							tsp.updateCache( c );
						}
						tsp.updatePageDisplay( c, false );
						// tsp.moveToPage( c, p, false ); <-- called when applyWidgets is triggered
						ts.applyWidget( c.table );
					}
				})
				.on( 'disablePager' + namespace, function( e ) {
					e.stopPropagation();
					tsp.showAllRows( c );
				})
				.on( 'enablePager' + namespace, function( e ) {
					e.stopPropagation();
					tsp.enablePager( c, true );
				})
				.on( 'destroyPager' + namespace, function( e, refreshing ) {
					e.stopPropagation();
					// call removeWidget to make sure internal flags are modified.
					ts.removeWidget( c.table, 'pager', false );
				})
				.on( 'updateComplete' + namespace, function( e, table, triggered ) {
					e.stopPropagation();
					// table can be unintentionally undefined in tablesorter v2.17.7 and earlier
					// don't recalculate total rows/pages if using ajax
					if ( !table || triggered || p.ajax ) { return; }
					var $rows = c.$tbodies.eq( 0 ).children( 'tr' ).not( c.selectorRemove );
					p.totalRows = $rows.length -
						( wo.pager_countChildRows ? 0 : $rows.filter( '.' + c.cssChildRow ).length );
					p.totalPages = Math.ceil( p.totalRows / p.size );
					if ( $rows.length && c.rowsCopy && c.rowsCopy.length === 0 ) {
						// make a copy of all table rows once the cache has been built
						tsp.updateCache( c );
					}
					if ( p.page >= p.totalPages ) {
						tsp.moveToLastPage( c, p );
					}
					tsp.hideRows( c );
					tsp.changeHeight( c );
					// update without triggering pagerComplete
					tsp.updatePageDisplay( c, false );
					// make sure widgets are applied - fixes #450
					ts.applyWidget( table );
					tsp.updatePageDisplay( c );
				})
				.on( 'pageSize refreshComplete '.split( ' ' ).join( namespace + ' ' ), function( e, size ) {
					e.stopPropagation();
					tsp.setPageSize( c, tsp.parsePageSize( c, size, 'get' ) );
					tsp.hideRows( c );
					tsp.updatePageDisplay( c, false );
				})
				.on( 'pageSet pagerUpdate '.split( ' ' ).join( namespace + ' ' ), function( e, num ) {
					e.stopPropagation();
					// force pager refresh
					if ( e.type === 'pagerUpdate' ) {
						num = typeof num === 'undefined' ? p.page + 1 : num;
						p.last.page = true;
					}
					p.page = ( parseInt( num, 10 ) || 1 ) - 1;
					tsp.moveToPage( c, p, true );
					tsp.updatePageDisplay( c, false );
				})
				.on( 'pageAndSize' + namespace, function( e, page, size ) {
					e.stopPropagation();
					p.page = ( parseInt(page, 10) || 1 ) - 1;
					tsp.setPageSize( c, tsp.parsePageSize( c, size, 'get' ) );
					tsp.moveToPage( c, p, true );
					tsp.hideRows( c );
					tsp.updatePageDisplay( c, false );
				});

			// clicked controls
			ctrls = [ s.first, s.prev, s.next, s.last ];
			fxn = [ 'moveToFirstPage', 'moveToPrevPage', 'moveToNextPage', 'moveToLastPage' ];
			if ( c.debug && !p.$container.length ) {
				console.warn( 'Pager: >> Container not found' );
			}
			p.$container.find( ctrls.join( ',' ) )
				.attr( 'tabindex', 0 )
				.off( 'click' + namespace )
				.on( 'click' + namespace, function( e ) {
					e.stopPropagation();
					var i,
						$c = $( this ),
						l = ctrls.length;
					if ( !$c.hasClass( wo.pager_css.disabled ) ) {
						for ( i = 0; i < l; i++ ) {
							if ( $c.is( ctrls[ i ] ) ) {
								tsp[ fxn[ i ] ]( c, p );
								break;
							}
						}
					}
				});

			if ( p.$goto.length ) {
				p.$goto
					.off( 'change' + namespace )
					.on( 'change' + namespace, function() {
						p.page = $( this ).val() - 1;
						tsp.moveToPage( c, p, true );
						tsp.updatePageDisplay( c, false );
					});
			} else if ( c.debug ) {
				console.warn( 'Pager: >> Goto selector not found' );
			}

			if ( p.$size.length ) {
				// setting an option as selected appears to cause issues with initial page size
				p.$size.find( 'option' ).removeAttr( 'selected' );
				p.$size
					.off( 'change' + namespace )
					.on( 'change' + namespace, function() {
						if ( !$( this ).hasClass( wo.pager_css.disabled ) ) {
							var size = $( this ).val();
							p.$size.val( size ); // in case there are more than one pagers
							tsp.setPageSize( c, size );
							tsp.changeHeight( c );
						}
						return false;
					});
			} else if ( c.debug ) {
				console.warn('Pager: >> Size selector not found');
			}

		},

		// hide arrows at extremes
		pagerArrows: function( c, disable ) {
			var p = c.pager,
				dis = !!disable,
				first = dis || p.page === 0,
				tp = tsp.getTotalPages( c, p ),
				last = dis || p.page === tp - 1 || tp === 0,
				wo = c.widgetOptions,
				s = wo.pager_selectors;
			if ( wo.pager_updateArrows ) {
				p.$container
					.find( s.first + ',' + s.prev )
					.toggleClass( wo.pager_css.disabled, first )
					.attr( 'aria-disabled', first );
				p.$container
					.find( s.next + ',' + s.last )
					.toggleClass( wo.pager_css.disabled, last )
					.attr( 'aria-disabled', last );
			}
		},

		calcFilters: function( c ) {
			var normalized, indx, len,
				wo = c.widgetOptions,
				p = c.pager,
				hasFilters = c.$table.hasClass( 'hasFilters' );
			if ( hasFilters && !wo.pager_ajaxUrl ) {
				if ( $.isEmptyObject( c.cache ) ) {
					// delayInit: true so nothing is in the cache
					p.filteredRows = p.totalRows = c.$tbodies.eq( 0 )
						.children( 'tr' )
						.not( wo.pager_countChildRows ? '' : '.' + c.cssChildRow )
						.length;
				} else {
					p.filteredRows = 0;
					normalized = c.cache[ 0 ].normalized;
					len = normalized.length;
					for ( indx = 0; indx < len; indx++ ) {
						p.filteredRows += p.regexRows.test( normalized[ indx ][ c.columns ].$row[ 0 ].className ) ? 0 : 1;
					}
				}
			} else if ( !hasFilters ) {
				p.filteredRows = p.totalRows;
			}
		},

		updatePageDisplay: function( c, completed ) {
			if ( c.pager.initializing ) { return; }
			var s, t, $out, options, indx, len,
				table = c.table,
				wo = c.widgetOptions,
				p = c.pager,
				namespace = c.namespace + 'pager',
				sz = tsp.parsePageSize( c, p.size, 'get' ); // don't allow dividing by zero
			if ( wo.pager_countChildRows ) { t.push( c.cssChildRow ); }
			p.$size
				.add( p.$goto )
				.removeClass( wo.pager_css.disabled )
				.removeAttr( 'disabled' )
				.attr( 'aria-disabled', 'false' );
			p.totalPages = Math.ceil( p.totalRows / sz ); // needed for 'pageSize' method
			c.totalRows = p.totalRows;
			tsp.parsePageNumber( c, p );
			tsp.calcFilters( c );
			c.filteredRows = p.filteredRows;
			p.filteredPages = Math.ceil( p.filteredRows / sz ) || 0;
			if ( tsp.getTotalPages( c, p ) >= 0 ) {
				t = ( p.size * p.page > p.filteredRows ) && completed;
				p.page = t ? wo.pager_pageReset || 0 : p.page;
				p.startRow = t ? p.size * p.page + 1 : ( p.filteredRows === 0 ? 0 : p.size * p.page + 1 );
				p.endRow = Math.min( p.filteredRows, p.totalRows, p.size * ( p.page + 1 ) );
				$out = p.$container.find( wo.pager_selectors.pageDisplay );
				// form the output string (can now get a new output string from the server)
				s = ( p.ajaxData && p.ajaxData.output ? p.ajaxData.output || wo.pager_output : wo.pager_output )
					// {page} = one-based index; {page+#} = zero based index +/- value
					.replace( /\{page([\-+]\d+)?\}/gi, function( m, n ) {
						return p.totalPages ? p.page + ( n ? parseInt( n, 10 ) : 1 ) : 0;
					})
					// {totalPages}, {extra}, {extra:0} (array) or {extra : key} (object)
					.replace( /\{\w+(\s*:\s*\w+)?\}/gi, function( m ) {
						var len, indx,
							str = m.replace( /[{}\s]/g, '' ),
							extra = str.split( ':' ),
							data = p.ajaxData,
							// return zero for default page/row numbers
							deflt = /(rows?|pages?)$/i.test( str ) ? 0 : '';
						if ( /(startRow|page)/.test( extra[ 0 ] ) && extra[ 1 ] === 'input' ) {
							len = ( '' + ( extra[ 0 ] === 'page' ? p.totalPages : p.totalRows ) ).length;
							indx = extra[ 0 ] === 'page' ? p.page + 1 : p.startRow;
							return '<input type="text" class="ts-' + extra[ 0 ] +
								'" style="max-width:' + len + 'em" value="' + indx + '"/>';
						}
						return extra.length > 1 && data && data[ extra[ 0 ] ] ?
							data[ extra[ 0 ] ][ extra[ 1 ] ] :
							p[ str ] || ( data ? data[ str ] : deflt ) || deflt;
					});
				if ( p.$goto.length ) {
					t = '';
					options = tsp.buildPageSelect( c, p );
					len = options.length;
					for ( indx = 0; indx < len; indx++ ) {
						t += '<option value="' + options[ indx ] + '">' + options[ indx ] + '</option>';
					}
					// innerHTML doesn't work in IE9 - http://support2.microsoft.com/kb/276228
					p.$goto.html( t ).val( p.page + 1 );
				}
				if ( $out.length ) {
					$out[ ($out[ 0 ].nodeName === 'INPUT' ) ? 'val' : 'html' ]( s );
					// rebind startRow/page inputs
					$out
						.find( '.ts-startRow, .ts-page' )
						.off( 'change' + namespace )
						.on( 'change' + namespace, function() {
							var v = $( this ).val(),
								pg = $( this ).hasClass( 'ts-startRow' ) ? Math.floor( v / p.size ) + 1 : v;
							c.$table.trigger( 'pageSet' + namespace, [ pg ] );
						});
				}
			}
			tsp.pagerArrows( c );
			tsp.fixHeight( c );
			if ( p.initialized && completed !== false ) {
				if ( c.debug ) {
					console.log( 'Pager: Triggering pagerComplete' );
				}
				c.$table.trigger( 'pagerComplete', c );
				// save pager info to storage
				if ( wo.pager_savePages && ts.storage ) {
					ts.storage( table, wo.pager_storageKey, {
						page : p.page,
						size : p.size
					});
				}
			}
		},

		buildPageSelect: function( c, p ) {
			// Filter the options page number link array if it's larger than 'pager_maxOptionSize'
			// as large page set links will slow the browser on large dom inserts
			var i, centralFocusSize, focusOptionPages, insertIndex, optionLength, focusLength,
				wo = c.widgetOptions,
				pg = tsp.getTotalPages( c, p ) || 1,
				// make skip set size multiples of 5
				skipSetSize = Math.ceil( ( pg / wo.pager_maxOptionSize ) / 5 ) * 5,
				largeCollection = pg > wo.pager_maxOptionSize,
				currentPage = p.page + 1,
				startPage = skipSetSize,
				endPage = pg - skipSetSize,
				optionPages = [ 1 ],
				// construct default options pages array
				optionPagesStartPage = largeCollection ? skipSetSize : 1;

			for ( i = optionPagesStartPage; i <= pg; ) {
				optionPages.push( i );
				i = i + ( largeCollection ? skipSetSize : 1 );
			}
			optionPages.push( pg );

			if ( largeCollection ) {
				focusOptionPages = [];
				// don't allow central focus size to be > 5 on either side of current page
				centralFocusSize = Math.max( Math.floor( wo.pager_maxOptionSize / skipSetSize ) - 1, 5 );

				startPage = currentPage - centralFocusSize;
				if ( startPage < 1 ) { startPage = 1; }
				endPage = currentPage + centralFocusSize;
				if ( endPage > pg ) { endPage = pg; }
				// construct an array to get a focus set around the current page
				for ( i = startPage; i <= endPage ; i++ ) {
					focusOptionPages.push( i );
				}

				// keep unique values
				optionPages = $.grep( optionPages, function( value, indx ) {
					return $.inArray( value, optionPages ) === indx;
				});

				optionLength = optionPages.length;
				focusLength = focusOptionPages.length;

				// make sure at all optionPages aren't replaced
				if ( optionLength - focusLength > skipSetSize / 2 && optionLength + focusLength > wo.pager_maxOptionSize ) {
					insertIndex = Math.floor( optionLength / 2 ) - Math.floor( focusLength / 2 );
					Array.prototype.splice.apply( optionPages, [ insertIndex, focusLength ] );
				}
				optionPages = optionPages.concat( focusOptionPages );

			}

			// keep unique values again
			optionPages = $.grep( optionPages, function( value, indx ) {
				return $.inArray( value, optionPages ) === indx;
			})
			.sort( function( a, b ) {
				return a - b;
			});

			return optionPages;
		},

		fixHeight: function( c ) {
			var d, h,
				table = c.table,
				p = c.pager,
				wo = c.widgetOptions,
				$b = c.$tbodies.eq( 0 );
			$b.find( 'tr.pagerSavedHeightSpacer' ).remove();
			if ( wo.pager_fixedHeight && !p.isDisabled ) {
				h = $.data( table, 'pagerSavedHeight' );
				if ( h ) {
					d = h - $b.height();
					if ( d > 5 && $.data( table, 'pagerLastSize' ) === p.size && $b.children( 'tr:visible' ).length < p.size ) {
						$b.append( '<tr class="pagerSavedHeightSpacer ' + c.selectorRemove.slice( 1 ) +
							'" style="height:' + d + 'px;"></tr>' );
					}
				}
			}
		},

		changeHeight: function( c ) {
			var h,
				table = c.table,
				$b = c.$tbodies.eq( 0 );
			$b.find( 'tr.pagerSavedHeightSpacer' ).remove();
			if ( !$b.children( 'tr:visible' ).length ) {
				$b.append( '<tr class="pagerSavedHeightSpacer ' + c.selectorRemove.slice( 1 ) + '"><td>&nbsp</td></tr>' );
			}
			h = $b.children( 'tr' ).eq( 0 ).height() * c.pager.size;
			$.data( table, 'pagerSavedHeight', h );
			tsp.fixHeight( c );
			$.data( table, 'pagerLastSize', c.pager.size );
		},

		hideRows: function( c ) {
			if ( !c.widgetOptions.pager_ajaxUrl ) {
				var tbodyIndex, rowIndex, $rows, len, lastIndex,
					table = c.table,
					p = c.pager,
					wo = c.widgetOptions,
					tbodyLen = c.$tbodies.length,
					start = ( p.page * p.size ),
					end =  start + p.size,
					filtr = wo && wo.filter_filteredRow || 'filtered',
					last = 0, // for cache indexing
					size = 0; // size counter
				p.cacheIndex = [];
				for ( tbodyIndex = 0; tbodyIndex < tbodyLen; tbodyIndex++ ) {
					$rows = c.$tbodies.eq( tbodyIndex ).children( 'tr' );
					len = $rows.length;
					lastIndex = 0;
					last = 0; // for cache indexing
					size = 0; // size counter
					for ( rowIndex = 0; rowIndex < len; rowIndex++ ) {
						if ( !$rows[ rowIndex ].className.match( filtr ) ) {
							if ( size === start && $rows[ rowIndex ].className.match( c.cssChildRow ) ) {
								// hide child rows @ start of pager (if already visible)
								$rows[ rowIndex ].style.display = 'none';
							} else {
								$rows[ rowIndex ].style.display = ( size >= start && size < end ) ? '' : 'none';
								if ( last !== size && size >= start && size < end ) {
									p.cacheIndex.push( rowIndex );
									last = size;
								}
								// don't count child rows
								size += $rows[ rowIndex ].className
									.match( c.cssChildRow + '|' + c.selectorRemove.slice( 1 ) ) && !wo.pager_countChildRows ? 0 : 1;
								if ( size === end && $rows[ rowIndex ].style.display !== 'none' &&
									$rows[ rowIndex ].className.match( ts.css.cssHasChild ) ) {
									lastIndex = rowIndex;
								}
							}
						}
					}
					// add any attached child rows to last row of pager. Fixes part of issue #396
					if ( lastIndex > 0 && $rows[ lastIndex ].className.match( ts.css.cssHasChild ) ) {
						while ( ++lastIndex < len && $rows[ lastIndex ].className.match( c.cssChildRow ) ) {
							$rows[ lastIndex ].style.display = '';
						}
					}
				}
			}
		},

		hideRowsSetup: function( c ) {
			var p = c.pager,
				namespace = c.namespace + 'pager',
				size = p.$size.val();
			p.size = tsp.parsePageSize( c, size, 'get' );
			p.$size.val( tsp.parsePageSize( c, p.size, 'set' ) );
			$.data( c.table, 'pagerLastSize', p.size );
			tsp.pagerArrows( c );
			if ( !c.widgetOptions.pager_removeRows ) {
				tsp.hideRows( c );
				c.$table.on( 'sortEnd filterEnd '.split( ' ' ).join( namespace + ' ' ), function() {
					tsp.hideRows( c );
				});
			}
		},

		renderAjax: function( data, c, xhr, settings, exception ) {
			var table = c.table,
				p = c.pager,
				wo = c.widgetOptions;
			// process data
			if ( $.isFunction( wo.pager_ajaxProcessing ) ) {

				// in case nothing is returned by ajax, empty out the table; see #1032
				// but do it before calling pager_ajaxProcessing because that function may add content
				// directly to the table
				c.$tbodies.eq( 0 ).empty();

				// ajaxProcessing result: [ total, rows, headers ]
				var i, j, t, hsh, $f, $sh, $headers, $h, icon, th, d, l, rr_count, len,
					$table = c.$table,
					tds = '',
					result = wo.pager_ajaxProcessing( data, table, xhr ) || [ 0, [] ],
					hl = $table.find( 'thead th' ).length;

				// Clean up any previous error.
				ts.showError( table );

				if ( exception ) {
					if ( c.debug ) {
						console.error( 'Pager: >> Ajax Error', xhr, settings, exception );
					}
					ts.showError( table, xhr, settings, exception );
					c.$tbodies.eq( 0 ).children( 'tr' ).detach();
					p.totalRows = 0;
				} else {
					// process ajax object
					if ( !$.isArray( result ) ) {
						p.ajaxData = result;
						c.totalRows = p.totalRows = result.total;
						c.filteredRows = p.filteredRows = typeof result.filteredRows !== 'undefined' ?
							result.filteredRows :
							result.total;
						th = result.headers;
						d = result.rows || [];
					} else {
						// allow [ total, rows, headers ]  or [ rows, total, headers ]
						t = isNaN( result[ 0 ] ) && !isNaN( result[ 1 ] );
						// ensure a zero returned row count doesn't fail the logical ||
						rr_count = result[ t ? 1 : 0 ];
						p.totalRows = isNaN( rr_count ) ? p.totalRows || 0 : rr_count;
						// can't set filtered rows when returning an array
						c.totalRows = c.filteredRows = p.filteredRows = p.totalRows;
						// set row data to empty array if nothing found - see http://stackoverflow.com/q/30875583/145346
						d = p.totalRows === 0 ? [] : result[ t ? 0 : 1 ] || []; // row data
						th = result[ 2 ]; // headers
					}
					l = d && d.length;
					if ( d instanceof jQuery ) {
						if ( wo.pager_processAjaxOnInit ) {
							// append jQuery object
							c.$tbodies.eq( 0 ).empty();
							c.$tbodies.eq( 0 ).append( d );
						}
					} else if ( l ) {
						// build table from array
						for ( i = 0; i < l; i++ ) {
							tds += '<tr>';
							for ( j = 0; j < d[i].length; j++ ) {
								// build tbody cells; watch for data containing HTML markup - see #434
								tds += /^\s*<td/.test( d[ i ][ j ] ) ? $.trim( d[ i ][ j ] ) : '<td>' + d[ i ][ j ] + '</td>';
							}
							tds += '</tr>';
						}
						// add rows to first tbody
						if ( wo.pager_processAjaxOnInit ) {
							c.$tbodies.eq( 0 ).html( tds );
						}
					}
					wo.pager_processAjaxOnInit = true;
					// only add new header text if the length matches
					if ( th && th.length === hl ) {
						hsh = $table.hasClass( 'hasStickyHeaders' );
						$sh = hsh ? wo.$sticky.children( 'thead:first' ).children( 'tr' ).children() : '';
						$f = $table.find( 'tfoot tr:first' ).children();
						// don't change td headers (may contain pager)
						$headers = c.$headers.filter( 'th' );
						len = $headers.length;
						for ( j = 0; j < len; j++ ) {
							$h = $headers.eq( j );
							// add new test within the first span it finds, or just in the header
							if ( $h.find( '.' + ts.css.icon ).length ) {
								icon = $h.find( '.' + ts.css.icon ).clone( true );
								$h.find( '.tablesorter-header-inner' ).html( th[ j ] ).append( icon );
								if ( hsh && $sh.length ) {
									icon = $sh.eq( j ).find( '.' + ts.css.icon ).clone( true );
									$sh.eq( j ).find( '.tablesorter-header-inner' ).html( th[ j ] ).append( icon );
								}
							} else {
								$h.find( '.tablesorter-header-inner' ).html( th[ j ] );
								if ( hsh && $sh.length ) {
									$sh.eq( j ).find( '.tablesorter-header-inner' ).html( th[ j ] );
								}
							}
							$f.eq( j ).html( th[ j ] );
						}
					}
				}
				if ( c.showProcessing ) {
					ts.isProcessing( table ); // remove loading icon
				}
				// make sure last pager settings are saved, prevents multiple server side calls with
				// the same parameters
				p.totalPages = Math.ceil( p.totalRows / tsp.parsePageSize( c, p.size, 'get' ) );
				p.last.totalRows = p.totalRows;
				p.last.currentFilters = p.currentFilters;
				p.last.sortList = ( c.sortList || [] ).join( ',' );
				p.initializing = false;
				// update display without triggering pager complete... before updating cache
				tsp.updatePageDisplay( c, false );
				// tablesorter core updateCache (not pager)
				ts.updateCache( c, function() {
					if ( p.initialized ) {
						// apply widgets after table has rendered & after a delay to prevent
						// multiple applyWidget blocking code from blocking this trigger
						setTimeout( function() {
							if ( c.debug ) {
								console.log( 'Pager: Triggering pagerChange' );
							}
							$table.trigger( 'pagerChange', p );
							ts.applyWidget( table );
							tsp.updatePageDisplay( c );
						}, 0 );
					}
				});
			}
			if ( !p.initialized ) {
				ts.applyWidget( table );
			}
		},

		getAjax: function( c ) {
			var counter,
				url = tsp.getAjaxUrl( c ),
				$doc = $( document ),
				namespace = c.namespace + 'pager',
				p = c.pager;
			if ( url !== '' ) {
				if ( c.showProcessing ) {
					ts.isProcessing( c.table, true ); // show loading icon
				}
				$doc.on( 'ajaxError' + namespace, function( e, xhr, settings, exception ) {
					tsp.renderAjax( null, c, xhr, settings, exception );
					$doc.off( 'ajaxError' + namespace );
				});
				counter = ++p.ajaxCounter;
				p.last.ajaxUrl = url; // remember processed url
				p.ajaxObject.url = url; // from the ajaxUrl option and modified by customAjaxUrl
				p.ajaxObject.success = function( data, status, jqxhr ) {
					// Refuse to process old ajax commands that were overwritten by new ones - see #443
					if ( counter < p.ajaxCounter ) {
						return;
					}
					tsp.renderAjax( data, c, jqxhr );
					$doc.off( 'ajaxError' + namespace );
					if ( typeof p.oldAjaxSuccess === 'function' ) {
						p.oldAjaxSuccess( data );
					}
				};
				if ( c.debug ) {
					console.log( 'Pager: Ajax initialized', p.ajaxObject );
				}
				$.ajax( p.ajaxObject );
			}
		},

		getAjaxUrl: function( c ) {
			var indx, len,
				p = c.pager,
				wo = c.widgetOptions,
				url = wo.pager_ajaxUrl ? wo.pager_ajaxUrl
					// allow using '{page+1}' in the url string to switch to a non-zero based index
					.replace( /\{page([\-+]\d+)?\}/, function( s, n ) { return p.page + ( n ? parseInt( n, 10 ) : 0 ); })
					.replace( /\{size\}/g, p.size ) : '',
				sortList = c.sortList,
				filterList = p.currentFilters || c.$table.data( 'lastSearch' ) || [],
				sortCol = url.match( /\{\s*sort(?:List)?\s*:\s*(\w*)\s*\}/ ),
				filterCol = url.match( /\{\s*filter(?:List)?\s*:\s*(\w*)\s*\}/ ),
				arry = [];
			if ( sortCol ) {
				sortCol = sortCol[ 1 ];
				len = sortList.length;
				for ( indx = 0; indx < len; indx++ ) {
					arry.push( sortCol + '[' + sortList[ indx ][ 0 ] + ']=' + sortList[ indx ][ 1 ] );
				}
				// if the arry is empty, just add the col parameter... '&{sortList:col}' becomes '&col'
				url = url.replace( /\{\s*sort(?:List)?\s*:\s*(\w*)\s*\}/g, arry.length ? arry.join( '&' ) : sortCol );
				arry = [];
			}
			if ( filterCol ) {
				filterCol = filterCol[ 1 ];
				len = filterList.length;
				for ( indx = 0; indx < len; indx++ ) {
					if ( filterList[ indx ] ) {
						arry.push( filterCol + '[' + indx + ']=' + encodeURIComponent( filterList[ indx ] ) );
					}
				}
				// if the arry is empty, just add the fcol parameter... '&{filterList:fcol}' becomes '&fcol'
				url = url.replace( /\{\s*filter(?:List)?\s*:\s*(\w*)\s*\}/g, arry.length ? arry.join( '&' ) : filterCol );
				p.currentFilters = filterList;
			}
			if ( $.isFunction( wo.pager_customAjaxUrl ) ) {
				url = wo.pager_customAjaxUrl( c.table, url );
			}
			if ( c.debug ) {
				console.log( 'Pager: Ajax url = ' + url );
			}
			return url;
		},

		renderTable: function( c, rows ) {
			var $tb, index, count, added,
				table = c.table,
				p = c.pager,
				wo = c.widgetOptions,
				f = c.$table.hasClass('hasFilters'),
				l = rows && rows.length || 0, // rows may be undefined
				s = ( p.page * p.size ),
				e = p.size;
			if ( l < 1 ) {
				if ( c.debug ) {
					console.warn( 'Pager: >> No rows for pager to render' );
				}
				// empty table, abort!
				return;
			}
			if ( p.page >= p.totalPages ) {
				// lets not render the table more than once
				return tsp.moveToLastPage( c, p );
			}
			p.cacheIndex = [];
			p.isDisabled = false; // needed because sorting will change the page and re-enable the pager
			if ( p.initialized ) {
				if ( c.debug ) {
					console.log( 'Pager: Triggering pagerChange' );
				}
				c.$table.trigger( 'pagerChange', c );
			}
			if ( !wo.pager_removeRows ) {
				tsp.hideRows( c );
			} else {
				ts.clearTableBody( table );
				$tb = ts.processTbody( table, c.$tbodies.eq(0), true );
				// not filtered, start from the calculated starting point (s)
				// if filtered, start from zero
				index = f ? 0 : s;
				count = f ? 0 : s;
				added = 0;
				while ( added < e && index < rows.length ) {
					if ( !f || !/filtered/.test( rows[ index ][ 0 ].className ) ) {
						count++;
						if ( count > s && added <= e ) {
							added++;
							p.cacheIndex.push( index );
							$tb.append( rows[ index ] );
						}
					}
					index++;
				}
				ts.processTbody( table, $tb, false );
			}
			tsp.updatePageDisplay( c );

			wo.pager_startPage = p.page;
			wo.pager_size = p.size;
			if ( table.isUpdating ) {
				if ( c.debug ) {
					console.log( 'Pager: Triggering updateComplete' );
				}
				c.$table.trigger( 'updateComplete', [ table, true ] );
			}

		},

		showAllRows: function( c ) {
			var index, $controls, len,
				table = c.table,
				p = c.pager,
				wo = c.widgetOptions;
			if ( p.ajax ) {
				tsp.pagerArrows( c, true );
			} else {
				$.data( table, 'pagerLastPage', p.page );
				$.data( table, 'pagerLastSize', p.size );
				p.page = 0;
				p.size = p.totalRows;
				p.totalPages = 1;
				c.$table
					.addClass( 'pagerDisabled' )
					.removeAttr( 'aria-describedby' )
					.find( 'tr.pagerSavedHeightSpacer' )
					.remove();
				tsp.renderTable( c, c.rowsCopy );
				p.isDisabled = true;
				ts.applyWidget( table );
				if ( c.debug ) {
					console.log( 'Pager: Disabled' );
				}
			}
			// disable size selector
			$controls = p.$size
				.add( p.$goto )
				.add( p.$container.find( '.ts-startRow, .ts-page ' ) );
			len = $controls.length;
			for ( index = 0; index < len; index++ ) {
				$controls.eq( index )
					.attr( 'aria-disabled', 'true' )
					.addClass( wo.pager_css.disabled )[ 0 ].disabled = true;
			}
		},

		// updateCache if delayInit: true
		// this is normally done by 'appendToTable' function in the tablesorter core AFTER a sort
		updateCache: function( c ) {
			var p = c.pager;
			// tablesorter core updateCache (not pager)
			ts.updateCache( c, function() {
				if ( !$.isEmptyObject( c.cache ) ) {
					var index,
						rows = [],
						normalized = c.cache[ 0 ].normalized;
					p.totalRows = normalized.length;
					for ( index = 0; index < p.totalRows; index++ ) {
						rows.push( normalized[ index ][ c.columns ].$row );
					}
					c.rowsCopy = rows;
					tsp.moveToPage( c, p, true );
					// clear out last search to force an update
					p.last.currentFilters = [ ' ' ];
				}
			});
		},

		moveToPage: function( c, p, pageMoved ) {
			if ( p.isDisabled ) { return; }
			if ( pageMoved !== false && p.initialized && $.isEmptyObject( c.cache ) ) {
				return tsp.updateCache( c );
			}
			var table = c.table,
				wo = c.widgetOptions,
				l = p.last;

			// abort page move if the table has filters and has not been initialized
			if ( p.ajax && !wo.filter_initialized && ts.hasWidget( table, 'filter' ) ) { return; }

			tsp.parsePageNumber( c, p );
			tsp.calcFilters( c );

			// fixes issue where one current filter is [] and the other is [ '', '', '' ],
			// making the next if comparison think the filters as different. Fixes #202.
			l.currentFilters = ( l.currentFilters || [] ).join( '' ) === '' ? [] : l.currentFilters;
			p.currentFilters = ( p.currentFilters || [] ).join( '' ) === '' ? [] : p.currentFilters;
			// don't allow rendering multiple times on the same page/size/totalRows/filters/sorts
			if ( l.page === p.page && l.size === p.size && l.totalRows === p.totalRows &&
				( l.currentFilters || [] ).join( ',' ) === ( p.currentFilters || [] ).join( ',' ) &&
				// check for ajax url changes see #730
				( l.ajaxUrl || '' ) === ( p.ajaxObject.url || '' ) &&
				// & ajax url option changes (dynamically add/remove/rename sort & filter parameters)
				( l.optAjaxUrl || '' ) === ( wo.pager_ajaxUrl || '' ) &&
				l.sortList === ( c.sortList || [] ).join( ',' ) ) {
				return;
			}
			if ( c.debug ) {
				console.log( 'Pager: Changing to page ' + p.page );
			}
			p.last = {
				page: p.page,
				size: p.size,
				// fixes #408; modify sortList otherwise it auto-updates
				sortList: ( c.sortList || [] ).join( ',' ),
				totalRows: p.totalRows,
				currentFilters: p.currentFilters || [],
				ajaxUrl: p.ajaxObject.url || '',
				optAjaxUrl: wo.pager_ajaxUrl
			};
			if ( p.ajax ) {
				tsp.getAjax( c );
			} else if ( !p.ajax ) {
				tsp.renderTable( c, c.rowsCopy );
			}
			$.data( table, 'pagerLastPage', p.page );
			if ( p.initialized && pageMoved !== false ) {
				if ( c.debug ) {
					console.log( 'Pager: Triggering pageMoved' );
				}
				c.$table.trigger( 'pageMoved', c );
				ts.applyWidget( table );
				if ( !p.ajax && table.isUpdating ) {
					if ( c.debug ) {
						console.log( 'Pager: Triggering updateComplete' );
					}
					c.$table.trigger( 'updateComplete', [ table, true ] );
				}
			}
		},

		getTotalPages: function( c, p ) {
			return ts.hasWidget( c.table, 'filter' ) ? Math.min( p.totalPages, p.filteredPages ) : p.totalPages;
		},

		// set to either set or get value
		parsePageSize: function( c, size, mode ) {
			var p = c.pager,
				s = parseInt( size, 10 ) || p.size || c.widgetOptions.pager_size || 10,
				// if select does not contain an "all" option, use size
				setAll = p.$size.find( 'option[value="all"]' ).length ? 'all' : p.totalRows;
			return /all/i.test( size ) || s === p.totalRows ?
				// "get" to set `p.size` or "set" to set `p.$size.val()`
				( mode === 'get' ? p.totalRows : setAll ) :
				( mode === 'get' ? s : p.size );
		},

		parsePageNumber: function( c, p ) {
			var min = tsp.getTotalPages( c, p ) - 1;
			p.page = parseInt( p.page, 10 );
			if ( p.page < 0 || isNaN( p.page ) ) { p.page = 0; }
			if ( p.page > min && min >= 0 ) { p.page = min; }
			return p.page;
		},

		setPageSize: function( c, size ) {
			var p = c.pager,
				table = c.table;
			p.size = tsp.parsePageSize( c, size, 'get' );
			p.$size.val( tsp.parsePageSize( c, p.size, 'set' ) );
			$.data( table, 'pagerLastPage', tsp.parsePageNumber( c, p ) );
			$.data( table, 'pagerLastSize', p.size );
			p.totalPages = Math.ceil( p.totalRows / p.size );
			p.filteredPages = Math.ceil( p.filteredRows / p.size );
			tsp.moveToPage( c, p, true );
		},

		moveToFirstPage: function( c, p ) {
			p.page = 0;
			tsp.moveToPage( c, p, true );
		},

		moveToLastPage: function( c, p ) {
			p.page = tsp.getTotalPages( c, p ) - 1;
			tsp.moveToPage( c, p, true );
		},

		moveToNextPage: function( c, p ) {
			p.page++;
			var last = tsp.getTotalPages( c, p ) - 1;
			if ( p.page >= last ) {
				p.page = last;
			}
			tsp.moveToPage( c, p, true );
		},

		moveToPrevPage: function( c, p ) {
			p.page--;
			if ( p.page <= 0 ) {
				p.page = 0;
			}
			tsp.moveToPage( c, p, true );
		},

		destroyPager: function( c, refreshing ) {
			var table = c.table,
				p = c.pager,
				s = c.widgetOptions.pager_selectors,
				ctrls = [ s.first, s.prev, s.next, s.last, s.gotoPage, s.pageSize ].join( ',' ),
				namespace = c.namespace + 'pager';
			p.initialized = false;
			c.$table.off( namespace );
			p.$container
				// hide pager
				.hide()
				// unbind pager controls
				.find( ctrls )
				.off( namespace );
			if ( refreshing ) { return; }
			tsp.showAllRows( c );
			c.appender = null; // remove pager appender function
			if ( ts.storage ) {
				ts.storage( table, c.widgetOptions.pager_storageKey, '' );
			}
			delete table.config.pager;
			delete table.config.rowsCopy;
		},

		enablePager: function( c, triggered ) {
			var info, size,
				table = c.table,
				p = c.pager;
			p.isDisabled = false;
			p.page = $.data( table, 'pagerLastPage' ) || p.page || 0;
			size = p.$size.find( 'option[selected]' ).val();
			p.size = $.data( table, 'pagerLastSize' ) || tsp.parsePageSize( c, size, 'get' );
			p.$size.val( tsp.parsePageSize( c, p.size, 'set' ) ); // set page size
			p.totalPages = Math.ceil( tsp.getTotalPages( c, p ) / p.size );
			c.$table.removeClass( 'pagerDisabled' );
			// if table id exists, include page display with aria info
			if ( table.id ) {
				info = table.id + '_pager_info';
				p.$container.find( c.widgetOptions.pager_selectors.pageDisplay ).attr( 'id', info );
				c.$table.attr( 'aria-describedby', info );
			}
			tsp.changeHeight( c );
			if ( triggered ) {
				// tablesorter core update table
				ts.update( c );
				tsp.setPageSize( c, p.size );
				tsp.hideRowsSetup( c );
				if ( c.debug ) {
					console.log( 'Pager: Enabled' );
				}
			}
		},

		appender: function( table, rows ) {
			var c = table.config,
				wo = c.widgetOptions,
				p = c.pager;
			if ( !p.ajax ) {
				c.rowsCopy = rows;
				p.totalRows = wo.pager_countChildRows ? c.$tbodies.eq( 0 ).children( 'tr' ).length : rows.length;
				p.size = $.data( table, 'pagerLastSize' ) || p.size || wo.pager_size || p.setSize || 10;
				p.totalPages = Math.ceil( p.totalRows / p.size );
				tsp.moveToPage( c, p );
				// update display here in case all rows are removed
				tsp.updatePageDisplay( c, false );
			} else {
				tsp.moveToPage( c, p, true );
			}
		}

	};

	// see #486
	ts.showError = function( table, xhr, settings, exception ) {
		var $row,
			$table = $( table ),
			c = $table[ 0 ].config,
			wo = c && c.widgetOptions,
			errorRow = c.pager && c.pager.cssErrorRow ||
				wo && wo.pager_css && wo.pager_css.errorRow ||
				'tablesorter-errorRow',
			typ = typeof xhr,
			valid = true,
			message = '',
			removeRow = function() {
				c.$table.find( 'thead' ).find( '.' + errorRow ).remove();
			};

		if ( !$table.length ) {
			console.error( 'tablesorter showError: no table parameter passed' );
			return;
		}

		// ajaxError callback for plugin or widget - see #992
		if ( typeof c.pager.ajaxError === 'function' ) {
			valid = c.pager.ajaxError( c, xhr, settings, exception );
			if ( valid === false ) {
				return removeRow();
			} else {
				message = valid;
			}
		} else if ( typeof wo.pager_ajaxError === 'function' ) {
			valid = wo.pager_ajaxError( c, xhr, settings, exception );
			if ( valid === false ) {
				return removeRow();
			} else {
				message = valid;
			}
		}

		if ( message === '' ) {
			if ( typ === 'object' ) {
				message =
					xhr.status === 0 ? 'Not connected, verify Network' :
					xhr.status === 404 ? 'Requested page not found [404]' :
					xhr.status === 500 ? 'Internal Server Error [500]' :
					exception === 'parsererror' ? 'Requested JSON parse failed' :
					exception === 'timeout' ? 'Time out error' :
					exception === 'abort' ? 'Ajax Request aborted' :
					'Uncaught error: ' + xhr.statusText + ' [' + xhr.status + ']';
			} else if ( typ === 'string'  ) {
				// keep backward compatibility (external usage just passes a message string)
				message = xhr;
			} else {
				// remove all error rows
				return removeRow();
			}
		}

		// allow message to include entire row HTML!
		$row = ( /tr\>/.test( message ) ?
			$( message ) :
			$( '<tr><td colspan="' + c.columns + '">' + message + '</td></tr>' ) )
			.click( function() {
				$( this ).remove();
			})
			// add error row to thead instead of tbody, or clicking on the header will result in a parser error
			.appendTo( c.$table.find( 'thead:first' ) )
			.addClass( errorRow + ' ' + c.selectorRemove.slice( 1 ) )
			.attr({
				role: 'alert',
				'aria-live': 'assertive'
			});

	};

})(jQuery);
