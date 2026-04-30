/**
 * ZSkeleton Demo Content Generator JavaScript
 *
 * Handles the admin interface for generating and managing demo content.
 *
 * @package ZSkeleton
 * @since 1.0.0
 */

( function( $ ) {
	'use strict';

	/**
	 * Demo Content Manager
	 *
	 * @since 1.0.0
	 */
	const DemoContentManager = {

		/**
		 * Initialize the demo content manager
		 *
		 * @since 1.0.0
		 */
		init: function() {
			this.bindEvents();
			this.initializeInterface();
		},

		/**
		 * Bind event handlers
		 *
		 * @since 1.0.0
		 */
		bindEvents: function() {
			$( '#generate-demo-content' ).on( 'click', this.generateContent );
			$( '#regenerate-demo-content' ).on( 'click', this.regenerateContent );
			$( '#cleanup-demo-content' ).on( 'click', this.cleanupContent );
		},

		/**
		 * Initialize interface elements
		 *
		 * @since 1.0.0
		 */
		initializeInterface: function() {
			// Add loading states and animations.
			this.setupLoadingStates();
		},

		/**
		 * Setup loading states for buttons
		 *
		 * @since 1.0.0
		 */
		setupLoadingStates: function() {
			const buttons = $( '.demo-content-actions button' );
			
			buttons.each( function() {
				const $button = $( this );
				const originalText = $button.text();
				$button.data( 'original-text', originalText );
			} );
		},

		/**
		 * Generate demo content
		 *
		 * @since 1.0.0
		 * @param {Event} e Event object.
		 */
		generateContent: function( e ) {
			e.preventDefault();
			DemoContentManager.performAction( 'generate' );
		},

	/**
	 * Regenerate demo content
	 *
	 * @since 1.0.0
	 * @param {Event} e Event object.
	 */
	regenerateContent: function( e ) {
		e.preventDefault();
		
		if ( ! confirm( 'Add any missing demo content?\n\n' + 
			'This will check for missing posts and create only those that don\'t already exist. Existing content will not be affected.' ) ) {
			return;
		}
		
		DemoContentManager.performAction( 'generate' );
	},

		/**
		 * Cleanup demo content
		 *
		 * @since 1.0.0
		 * @param {Event} e Event object.
		 */
		cleanupContent: function( e ) {
			e.preventDefault();
			
			if ( ! confirm( zskeletonDemo.strings.confirm_cleanup ) ) {
				return;
			}
			
			DemoContentManager.performAction( 'cleanup' );
		},

		/**
		 * Perform AJAX action
		 *
		 * @since 1.0.0
		 * @param {string} action Action type (generate|cleanup).
		 */
		performAction: function( action ) {
			const isGenerate = action === 'generate';
			const $button = isGenerate ? 
				$( '#generate-demo-content, #regenerate-demo-content' ) : 
				$( '#cleanup-demo-content' );
			
			const loadingText = isGenerate ? 
				zskeletonDemo.strings.generating : 
				zskeletonDemo.strings.cleaning;
			
			const successText = isGenerate ? 
				zskeletonDemo.strings.generated : 
				zskeletonDemo.strings.cleaned;

			// Update button state.
			this.setButtonLoading( $button, loadingText );
			this.showLog();
			
			if ( isGenerate ) {
				this.addLogMessage( 'Starting demo content generation...' );
			} else {
				this.addLogMessage( 'Starting demo content cleanup...' );
			}

			// Perform AJAX request.
			$.ajax( {
				url: zskeletonDemo.ajax_url,
				type: 'POST',
				data: {
					action: isGenerate ? 'zskeleton_generate_demo_content' : 'zskeleton_cleanup_demo_content',
					nonce: zskeletonDemo.nonce
				},
				success: function( response ) {
					DemoContentManager.handleSuccess( response, $button, successText, isGenerate );
				},
				error: function( xhr, status, error ) {
					DemoContentManager.handleError( error, $button );
				}
			} );
		},

		/**
		 * Handle successful AJAX response
		 *
		 * @since 1.0.0
		 * @param {Object} response AJAX response.
		 * @param {jQuery} $button Button element.
		 * @param {string} successText Success message.
		 * @param {boolean} isGenerate Whether this is a generation action.
		 */
		handleSuccess: function( response, $button, successText, isGenerate ) {
			this.setButtonNormal( $button );
			
			if ( response.success ) {
				this.showSuccessMessage( successText );
				
				if ( isGenerate && response.data.log ) {
					// Add log messages.
					response.data.log.forEach( function( message ) {
						DemoContentManager.addLogMessage( message );
					} );
					
					// Show statistics.
					if ( response.data.stats ) {
						this.showStatistics( response.data.stats );
					}
				} else {
					this.addLogMessage( successText );
				}
				
				// Reload page after delay to show updated interface.
				setTimeout( function() {
					window.location.reload();
				}, 2000 );
				
			} else {
				this.showErrorMessage( response.data || zskeletonDemo.strings.error );
				this.addLogMessage( 'Error: ' + ( response.data || zskeletonDemo.strings.error ) );
			}
		},

		/**
		 * Handle AJAX error
		 *
		 * @since 1.0.0
		 * @param {string} error Error message.
		 * @param {jQuery} $button Button element.
		 */
		handleError: function( error, $button ) {
			this.setButtonNormal( $button );
			this.showErrorMessage( zskeletonDemo.strings.error + ': ' + error );
			this.addLogMessage( 'AJAX Error: ' + error );
		},

		/**
		 * Set button to loading state
		 *
		 * @since 1.0.0
		 * @param {jQuery} $button Button element.
		 * @param {string} loadingText Loading text.
		 */
		setButtonLoading: function( $button, loadingText ) {
			$button.prop( 'disabled', true )
				.addClass( 'updating-message' )
				.find( '.dashicons' )
				.removeClass()
				.addClass( 'dashicons dashicons-update-alt' );
			
			// Update text while preserving icon.
			const $icon = $button.find( '.dashicons' );
			$button.text( loadingText ).prepend( $icon );
		},

		/**
		 * Set button to normal state
		 *
		 * @since 1.0.0
		 * @param {jQuery} $button Button element.
		 */
		setButtonNormal: function( $button ) {
			const originalText = $button.data( 'original-text' );
			
			$button.prop( 'disabled', false )
				.removeClass( 'updating-message' )
				.text( originalText );
			
			// Restore original icon.
			if ( $button.is( '#generate-demo-content' ) || $button.is( '#regenerate-demo-content' ) ) {
				$button.prepend( '<span class="dashicons dashicons-admin-generic"></span>' );
			} else if ( $button.is( '#cleanup-demo-content' ) ) {
				$button.prepend( '<span class="dashicons dashicons-trash"></span>' );
			}
		},

		/**
		 * Show log container
		 *
		 * @since 1.0.0
		 */
		showLog: function() {
			const $log = $( '#demo-content-log' );
			if ( $log.is( ':hidden' ) ) {
				$log.slideDown();
				$log.find( '.log-content' ).empty();
			}
		},

		/**
		 * Add message to log
		 *
		 * @since 1.0.0
		 * @param {string} message Log message.
		 */
		addLogMessage: function( message ) {
			const timestamp = new Date().toLocaleTimeString();
			const logEntry = $( '<div class="log-entry">' )
				.html( '<span class="log-time">' + timestamp + '</span> ' + message );
			
			$( '#demo-content-log .log-content' ).append( logEntry );
			
			// Auto-scroll to bottom.
			const $logContent = $( '#demo-content-log .log-content' );
			$logContent.scrollTop( $logContent[ 0 ].scrollHeight );
		},

		/**
		 * Show statistics
		 *
		 * @since 1.0.0
		 * @param {Object} stats Statistics object.
		 */
		showStatistics: function( stats ) {
			let statsMessage = 'Generated content: ';
			const statParts = [];
			
			Object.keys( stats ).forEach( function( type ) {
				if ( stats[ type ] > 0 ) {
					statParts.push( stats[ type ] + ' ' + type.replace( '_', ' ' ) );
				}
			} );
			
			statsMessage += statParts.join( ', ' );
			this.addLogMessage( statsMessage );
		},

		/**
		 * Show success message
		 *
		 * @since 1.0.0
		 * @param {string} message Success message.
		 */
		showSuccessMessage: function( message ) {
			this.showNotice( message, 'success' );
		},

		/**
		 * Show error message
		 *
		 * @since 1.0.0
		 * @param {string} message Error message.
		 */
		showErrorMessage: function( message ) {
			this.showNotice( message, 'error' );
		},

		/**
		 * Show admin notice
		 *
		 * @since 1.0.0
		 * @param {string} message Notice message.
		 * @param {string} type Notice type (success|error|warning|info).
		 */
		showNotice: function( message, type ) {
			// Remove existing notices.
			$( '.demo-content-notice' ).remove();
			
			const $notice = $( '<div class="notice notice-' + type + ' demo-content-notice is-dismissible">' )
				.html( '<p>' + message + '</p>' );
			
			$( '.demo-content-container' ).prepend( $notice );
			
			// Add dismiss functionality.
			$notice.find( '.notice-dismiss' ).on( 'click', function() {
				$notice.fadeOut();
			} );
			
			// Auto-hide success messages after 5 seconds.
			if ( type === 'success' ) {
				setTimeout( function() {
					$notice.fadeOut();
				}, 5000 );
			}
		}
	};

	// Initialize when document is ready.
	$( document ).ready( function() {
		DemoContentManager.init();
	} );

} )( jQuery );

