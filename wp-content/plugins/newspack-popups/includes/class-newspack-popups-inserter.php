<?php
/**
 * Newspack Popups Inserters
 *
 * @package Newspack
 */

defined( 'ABSPATH' ) || exit;

require_once dirname( __FILE__ ) . '/../api/segmentation/class-segmentation.php';

/**
 * API endpoints
 */
final class Newspack_Popups_Inserter {
	/**
	 * The popup objects to display.
	 *
	 * @var array
	 */
	protected static $popups = [];

	/**
	 * Whether we've already inserted prompts into the content.
	 * If we've already inserted popups into the content, don't try to do it again.
	 *
	 * @var boolean
	 */
	public static $the_content_has_rendered = false;

	/**
	 * Retrieve the appropriate popups for the current post.
	 *
	 * @return array Popup objects.
	 */
	public static function popups_for_post() {
		// Inject prompts only in posts, pages, and CPTs that explicitly opt in.
		if ( ! in_array(
			get_post_type(),
			apply_filters(
				'newspack_campaigns_post_types_for_campaigns',
				[ 'post', 'page' ]
			)
		) ) {
			return [];
		}

		if ( ! empty( self::$popups ) ) {
			return self::$popups;
		}

		// Get the previewed popup and return early if there's one.
		if ( Newspack_Popups::previewed_popup_id() ) {
			return [ Newspack_Popups_Model::retrieve_preview_popup( Newspack_Popups::previewed_popup_id() ) ];
		}

		// Popups disabled for this page.
		if ( self::assess_has_disabled_popups() ) {
			return [];
		}

		$view_as_spec             = Segmentation::parse_view_as( Newspack_Popups_View_As::viewing_as_spec() );
		$view_as_spec_campaign    = isset( $view_as_spec['campaign'] ) ? $view_as_spec['campaign'] : false;
		$view_as_spec_unpublished = isset( $view_as_spec['show_unpublished'] ) && 'true' === $view_as_spec['show_unpublished'] ? true : false;

		// Retrieve all prompts eligible for display.

		// 1. Get all inline popups.
		$popups_to_maybe_display = Newspack_Popups_Model::retrieve_inline_popups( $view_as_spec_unpublished, $view_as_spec_campaign );

		// 2. Check if there are any overlay popups with matching category.
		$category_overlay_popups = Newspack_Popups_Model::retrieve_category_overlay_popups( $view_as_spec_unpublished, $view_as_spec_campaign );

		// 3. If there are matching category overlays, use those. Otherwise, get all valid overlay popups.
		$overlay_popups = ! empty( $category_overlay_popups ) ?
			$category_overlay_popups :
			Newspack_Popups_Model::retrieve_overlay_popups( $view_as_spec_unpublished, $view_as_spec_campaign );

		// 4. Add overlay popups to array.
		if ( ! empty( $overlay_popups ) ) {
			$popups_to_maybe_display = array_merge(
				$popups_to_maybe_display,
				$overlay_popups
			);
		}

		// 5. Remove manual placement prompts.
		$popups_to_maybe_display = array_filter(
			$popups_to_maybe_display,
			function( $popup ) {
				return 'manual' !== $popup['options']['frequency'];
			}
		);

		return array_filter(
			$popups_to_maybe_display,
			[ __CLASS__, 'should_display' ]
		);
	}

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_filter( 'the_content', [ $this, 'insert_popups_in_content' ], 1 );
		add_shortcode( 'newspack-popup', [ $this, 'popup_shortcode' ] );
		add_action( 'after_header', [ $this, 'insert_popups_after_header' ] ); // This is a Newspack theme hook. When used with other themes, popups won't be inserted on archive pages.
		add_action( 'wp_head', [ $this, 'insert_popups_amp_access' ] );
		add_action( 'wp_head', [ $this, 'register_amp_scripts' ] );
		add_action( 'before_header', [ $this, 'insert_before_header' ] );

		// Always enqueue scripts, since this plugin's scripts are handling pageview sending via GTAG.
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );

		add_filter(
			'widget_update_callback',
			[ $this, 'save_widgets_shortcoded_popup_ids' ],
			1,
			4
		);
		add_action( 'delete_widget', [ $this, 'remove_widgets_shortcoded_popup_ids' ] );

		add_filter(
			'newspack_newsletters_assess_has_disabled_popups',
			function () {
				return get_post_meta( get_the_ID(), 'newspack_popups_has_disabled_popups', true );
			}
		);

		// Suppress popups on product pages.
		// Until the popups non-AMP refactoring happens, they will break Add to Cart buttons.
		add_filter(
			'newspack_newsletters_assess_has_disabled_popups',
			function( $disabled ) {
				if ( function_exists( 'is_product' ) && is_product() ) {
					return true;
				}
				return $disabled;
			}
		);

		// These hooks are fired before and after rendering posts in the Homepage Posts block.
		// By removing the the_content filter before rendering, we avoid incorrectly injecting popup content into excerpts in the block.
		add_action(
			'newspack_blocks_homepage_posts_before_render',
			function() {
				remove_filter( 'the_content', [ $this, 'insert_popups_in_content' ], 1 );
			}
		);

		add_action(
			'newspack_blocks_homepage_posts_after_render',
			function() {
				add_filter( 'the_content', [ $this, 'insert_popups_in_content' ], 1 );
			}
		);
	}

	/**
	 * Process popups and insert into post and page content if needed.
	 *
	 * @param string $content The content of the post.
	 */
	public static function insert_popups_in_content( $content = '' ) {
		// Avoid duplicate execution.
		if ( true === self::$the_content_has_rendered ) {
			return $content;
		}

		// Not Frontend.
		if ( is_admin() ) {
			return $content;
		}

		// Content is empty.
		if ( empty( trim( $content ) ) ) {
			return $content;
		}

		// No popup insertion in archive pages.
		if ( ! is_singular() ) {
			return $content;
		}

		// If not in the loop, ignore.
		if ( ! in_the_loop() ) {
			return $content;
		}

		// If the current post is a popup, ignore.
		if ( Newspack_Popups::NEWSPACK_POPUPS_CPT == get_post_type() ) {
			return $content;
		}

		// Don't inject inline popups on paywalled posts.
		// It doesn't make sense with a paywall message and also causes an infinite loop.
		if ( function_exists( 'wc_memberships_is_post_content_restricted' ) && wc_memberships_is_post_content_restricted() ) {
			return $content;
		}

		// If any popups are inserted using a shortcode, skip them.
		$shortcoded_popups_ids = self::get_shortcoded_popups_ids( get_the_content() );
		$popups                = array_filter(
			self::popups_for_post(),
			function ( $popup ) use ( $shortcoded_popups_ids ) {
				return ! in_array( $popup['id'], $shortcoded_popups_ids ) && Newspack_Popups_Model::should_be_inserted_in_page_content( $popup );
			}
		);

		if ( empty( $popups ) ) {
			return $content;
		}

		if ( function_exists( 'scaip_maybe_insert_shortcode' ) ) {
			// Prevent default SCAIP insertion.
			remove_filter( 'the_content', 'scaip_maybe_insert_shortcode', 10 );

			// In order to prevent the SCAIP ad being inserted mid-popup, let's insert the ads
			// manually. SCAI begins by checking if there are any ads already inserted and bails
			// if there are, to allow for manual ads placement.
			$content = scaip_maybe_insert_shortcode( $content );
		}

		$total_length = strlen( $content );

		// 1. Separate prompts into inline and overlay.
		$inline_popups  = [];
		$overlay_popups = [];
		foreach ( $popups as $popup ) {
			if ( Newspack_Popups_Model::is_inline( $popup ) ) {
				$percentage                = intval( $popup['options']['trigger_scroll_progress'] ) / 100;
				$popup['precise_position'] = $total_length * $percentage;
				$popup['is_inserted']      = false;
				$inline_popups[]           = $popup;
			} elseif ( Newspack_Popups_Model::is_overlay( $popup ) ) {
				$overlay_popups[] = $popup;
			}
		}

		// Return early if there are no popups to insert. This can happen if e.g. the only popup is an above header one.
		if ( empty( $inline_popups ) && empty( $overlay_popups ) ) {
			return $content;
		}

		// 2. Iterate over all blocks and insert inline prompts.
		$pos    = 0;
		$output = '';
		foreach ( parse_blocks( $content ) as $block ) {
			$block_content = render_block( $block );
			$pos          += strlen( $block_content );
			foreach ( $inline_popups as &$inline_popup ) {
				if ( ! $inline_popup['is_inserted'] && $pos > $inline_popup['precise_position'] ) {
					$output .= '<!-- wp:shortcode -->[newspack-popup id="' . $inline_popup['id'] . '"]<!-- /wp:shortcode -->';

					$inline_popup['is_inserted'] = true;
				}
			}
			$output .= $block_content;
		}

		// 3. Insert any remaining inline prompts at the end.
		foreach ( $inline_popups as &$inline_popup ) {
			if ( ! $inline_popup['is_inserted'] ) {
				$output .= '<!-- wp:shortcode -->[newspack-popup id="' . $inline_popup['id'] . '"]<!-- /wp:shortcode -->';

				$inline_popup['is_inserted'] = true;
			}
		}

		// 4. Insert overlay prompts at the top of content.
		foreach ( $overlay_popups as $overlay_popup ) {
			$output = '<!-- wp:html -->' . Newspack_Popups_Model::generate_popup( $overlay_popup ) . '<!-- /wp:html -->' . $output;
		}

		self::$the_content_has_rendered = true;
		return $output;
	}

	/**
	 * Process popups and insert into archive pages if needed. Applies to Newspack Theme only.
	 */
	public static function insert_popups_after_header() {
		/* Posts and pages are covered by the_content hook */
		if ( is_singular() ) {
			return;
		}
		$popups = array_filter( self::popups_for_post(), [ 'Newspack_Popups_Model', 'should_be_inserted_in_page_content' ] );
		foreach ( $popups as $popup ) {
			echo Newspack_Popups_Model::generate_popup( $popup ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
	}

	/**
	 * Insert popups markup before header.
	 */
	public static function insert_before_header() {
		$before_header_popups = array_filter( self::popups_for_post(), [ 'Newspack_Popups_Model', 'should_be_inserted_above_page_header' ] );
		foreach ( $before_header_popups as $popup ) {
			echo Newspack_Popups_Model::generate_popup( $popup ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
	}

	/**
	 * Enqueue the assets needed to display the popups.
	 */
	public static function enqueue_scripts() {
		if ( defined( 'IS_TEST_ENV' ) && IS_TEST_ENV ) {
			return;
		}
		$is_amp = function_exists( 'is_amp_endpoint' ) && is_amp_endpoint();
		if ( ! $is_amp ) {
			wp_register_script(
				'newspack-popups-view',
				plugins_url( '../dist/view.js', __FILE__ ),
				[ 'wp-dom-ready', 'wp-url' ],
				filemtime( dirname( NEWSPACK_POPUPS_PLUGIN_FILE ) . '/dist/view.js' ),
				true
			);
			wp_enqueue_script( 'newspack-popups-view' );
		}

		\wp_register_style(
			'newspack-popups-view',
			plugins_url( '../dist/view.css', __FILE__ ),
			null,
			filemtime( dirname( NEWSPACK_POPUPS_PLUGIN_FILE ) . '/dist/view.css' )
		);
		\wp_style_add_data( 'newspack-popups-view', 'rtl', 'replace' );
		\wp_enqueue_style( 'newspack-popups-view' );
	}

	/**
	 * The popup shortcode function.
	 * Primarily, the shortcode is inserted by the plugin, but it may also be inserted manually to
	 * display a specific popup anywhere on the site.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return HTML
	 */
	public static function popup_shortcode( $atts = array() ) {
		$previewed_popup_id = Newspack_Popups::previewed_popup_id();
		if ( $previewed_popup_id ) {
			$found_popup = Newspack_Popups_Model::retrieve_preview_popup( $previewed_popup_id );
		} elseif ( isset( $atts['id'] ) ) {
			$found_popup = Newspack_Popups_Model::retrieve_popup_by_id( $atts['id'], ! empty( Newspack_Popups_View_As::viewing_as_spec() ) );
		}
		if (
			! $found_popup ||
			// Bail if it's a non-preview popup which should not be displayed.
			( ! self::should_display( $found_popup, true ) && ! Newspack_Popups::previewed_popup_id() ) ||
			// Only inline popups can be inserted via the  shortcode.
			! Newspack_Popups_Model::is_inline( $found_popup )
		) {
			return;
		}

		// Wrapping the inline popup in an aside element prevents the markup from being mangled
		// if the shortcode is the first block.
		return '<aside>' . Newspack_Popups_Model::generate_popup( $found_popup ) . '</aside>';
	}

	/**
	 * Create the popup definition for sending to the API.
	 *
	 * @param object $popup A popup.
	 */
	public static function create_single_popup_access_payload( $popup ) {
		$popup_id_string = Newspack_Popups_Model::canonize_popup_id( esc_attr( $popup['id'] ) );
		$frequency       = $popup['options']['frequency'];
		$is_overlay      = Newspack_Popups_Model::is_overlay( $popup );
		$is_above_header = Newspack_Popups_Model::is_above_header( $popup );
		$type            = 'i';

		if ( $is_overlay ) {
			$type = 'o';

			if ( 'always' === $frequency ) {
				$frequency = 'once';
			}
		}

		if ( $is_above_header ) {
			$type = 'a';
		}

		return [
			'id'  => $popup_id_string,
			'f'   => $frequency,
			'utm' => $popup['options']['utm_suppression'],
			's'   => $popup['options']['selected_segment_id'],
			'n'   => \Newspack_Popups_Model::has_newsletter_prompt( $popup ),
			'd'   => \Newspack_Popups_Model::has_donation_block( $popup ),
			't'   => $type,
		];
	}

	/**
	 * Add amp-access header code.
	 *
	 * The amp-access endpoint is also responsible for reporting visits, in order to minimise
	 * the number of requests. For this reason it is placed on every page, not only those
	 * with popups.
	 */
	public static function insert_popups_amp_access() {
		if ( ! Newspack_Popups_Segmentation::is_tracking() ) {
			return;
		}
		$shortcoded_popup_ids = array_unique(
			array_merge(
				self::get_shortcoded_popups_ids( get_the_content() ),
				self::get_all_widget_shortcoded_popups_ids()
			)
		);
		$shortcoded_popups    = array_reduce(
			$shortcoded_popup_ids,
			function ( $acc, $id ) {
				$popup_post = get_post( $id );
				if ( $popup_post ) {
					$popup_object = Newspack_Popups_Model::create_popup_object( $popup_post );
					if ( $popup_object ) {
						$acc[] = $popup_object;
					}
				}
				return $acc;
			},
			[]
		);

		$popups = array_merge(
			self::popups_for_post(),
			$shortcoded_popups
		);

		// "Escape hatch" if there's a need to block adding amp-access for pages that have no prompts.
		if ( apply_filters( 'newspack_popups_suppress_insert_amp_access', false, $popups ) ) {
			return;
		}

		$popups_access_provider = [
			'namespace'     => 'popups',
			'authorization' => esc_url( Newspack_Popups_Model::get_reader_endpoint() ) . '?cid=CLIENT_ID(' . Newspack_Popups_Segmentation::NEWSPACK_SEGMENTATION_CID_NAME . ')',
			'noPingback'    => true,
		];

		$popups_configs = [];
		foreach ( $popups as $popup ) {
			$popups_configs[] = self::create_single_popup_access_payload( $popup );
		}

		$categories   = get_the_category();
		$category_ids = '';
		if ( ! empty( $categories ) ) {
			$category_ids = implode(
				',',
				array_map(
					function( $cat ) {
						return $cat->term_id;
					},
					$categories
				)
			);
		}

		$settings                                 = array_reduce(
			\Newspack_Popups_Settings::get_settings(),
			function ( $acc, $item ) {
				$key       = $item['key'];
				$acc->$key = $item['value'];
				return $acc;
			},
			(object) []
		);
		$popups_access_provider['authorization'] .= '&ref=DOCUMENT_REFERRER';
		$popups_access_provider['authorization'] .= '&popups=' . wp_json_encode( $popups_configs );
		$popups_access_provider['authorization'] .= '&settings=' . wp_json_encode( $settings );
		$popups_access_provider['authorization'] .= '&visit=' . wp_json_encode(
			[
				'post_id'    => esc_attr( get_the_ID() ),
				'categories' => esc_attr( $category_ids ),
				'is_post'    => is_single(),
			]
		);
		$view_as_spec                             = Newspack_Popups_View_As::viewing_as_spec();
		if ( $view_as_spec ) {
			$popups_access_provider['authorization'] .= '&view_as=' . wp_json_encode( $view_as_spec );
		}
		?>
		<script id="amp-access" type="application/json">
			<?php echo wp_json_encode( $popups_access_provider ); ?>
		</script>
		<?php
	}

	/**
	 * Disable popups on posts and pages which have newspack_popups_has_disabled_popups.
	 *
	 * @return bool True if popups should be disabled for current page.
	 */
	public static function assess_has_disabled_popups() {
		return apply_filters( 'newspack_newsletters_assess_has_disabled_popups', [] );
	}

	/**
	 * Register and enqueue all required AMP scripts, if needed.
	 */
	public static function register_amp_scripts() {
		if ( self::assess_has_disabled_popups() ) {
			return;
		}
		if ( ! is_admin() && ! wp_script_is( 'amp-runtime', 'registered' ) ) {
		// phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion
			wp_register_script(
				'amp-runtime',
				'https://cdn.ampproject.org/v0.js',
				null,
				null,
				true
			);
		}
		$scripts = [ 'amp-access', 'amp-animation', 'amp-bind', 'amp-position-observer' ];
		foreach ( $scripts as $script ) {
			if ( ! wp_script_is( $script, 'registered' ) ) {
				$path = "https://cdn.ampproject.org/v0/{$script}-latest.js";
				// phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion
				wp_register_script(
					$script,
					$path,
					array( 'amp-runtime' ),
					null,
					true
				);
			}
			wp_enqueue_script( $script );
		}
	}

	/**
	 * Look for popup shortcodes in a string and return their IDs.
	 *
	 * @param string $string String to assess.
	 * @return array Found shortcoded popups IDs.
	 */
	public static function get_shortcoded_popups_ids( $string ) {
		preg_match_all( '/\[newspack-popup .*\]/', $string, $popup_shortcodes_in_content );
		if ( empty( $popup_shortcodes_in_content ) ) {
			return [];
		} else {
			return array_unique(
				array_map(
					function ( $item ) {
						preg_match( '/id=["|\'](\d*)/', $item, $matches );
						if ( empty( $matches ) ) {
							return null;
						} else {
							return $matches[1];
						}
					},
					$popup_shortcodes_in_content[0]
				)
			);
		}
	}

	/**
	 * Some popups can only appear on Posts.
	 *
	 * @param object $popup The popup to assess.
	 * @return bool Should popup be shown.
	 */
	public static function assess_is_post( $popup ) {
		if (
			// Inline Pop-ups can only appear in Posts.
			'inline' === $popup['options']['placement']
		) {
			return is_single();
		}
		return true;
	}

	/**
	 * If Pop-up has categories, it should only be shown on posts/pages with those.
	 *
	 * @param object $popup The popup to assess.
	 * @return bool Should popup be shown based on categories it has.
	 */
	public static function assess_categories_filter( $popup ) {
		$post_categories  = get_the_category();
		$popup_categories = get_the_category( $popup['id'] );

		// Filter out "Uncategorized" category which is automatically added to uncategorized posts on publish.
		$popup_categories = array_filter(
			$popup_categories,
			function( $popup_category ) {
				return 'uncategorized' !== $popup_category->slug;
			}
		);

		if ( $post_categories && count( $post_categories ) && $popup_categories && count( $popup_categories ) ) {
			return array_intersect(
				array_column( $post_categories, 'term_id' ),
				array_column( $popup_categories, 'term_id' )
			);
		}
		return true;
	}

	/**
	 * If Pop-up has tags, it should only be shown on posts/pages with those.
	 *
	 * @param object $popup The popup to assess.
	 * @return bool Should popup be shown based on tags it has.
	 */
	public static function assess_tags_filter( $popup ) {
		$post_tags  = get_the_tags();
		$popup_tags = get_the_tags( $popup['id'] );
		if ( $post_tags && count( $post_tags ) && $popup_tags && count( $popup_tags ) ) {
			return array_intersect(
				array_column( $post_tags, 'term_id' ),
				array_column( $popup_tags, 'term_id' )
			);
		}
		return true;
	}

	/**
	 * Should Popup be rendered, based on universal conditions.
	 *
	 * @param object $popup The popup to assess.
	 * @param bool   $skip_context_checks Skip checking context, like if the popup is rendered in a post, and if category/tags are matching.
	 * @return bool Should popup be shown.
	 */
	public static function should_display( $popup, $skip_context_checks = false ) {
		if ( 'manual' === $popup['options']['frequency'] ) {
			return true;
		}

		$general_conditions = self::assess_is_post( $popup ) &&
			self::assess_categories_filter( $popup ) &&
			self::assess_tags_filter( $popup );

		// When using "view as" feature, discard test mode popups.
		if ( Newspack_Popups_View_As::viewing_as_spec() ) {
			return $general_conditions;
		}
		// Hide prompts for logged-in users.
		if ( Newspack_Popups::is_user_admin() ) {
			return false;
		}
		// Hide overlay prompts in non-interactive mode, for non-logged-in users.
		if ( ! Newspack_Popups::is_user_admin() && Newspack_Popups_Settings::is_non_interactive() && ! Newspack_Popups_Model::is_inline( $popup ) ) {
			return false;
		}
		if ( $skip_context_checks ) {
			return true;
		}
		return $general_conditions;
	}

	/**
	 * When a Text widget is saved and it contains popups shortcode(s), save their IDs as an option.
	 *
	 * @param object $instance Widget instance.
	 * @param object $new_instance New widget instance.
	 * @param object $old_instance Old widget instance.
	 * @param object $widget Widget object.
	 * @return object Widget instance.
	 */
	public static function save_widgets_shortcoded_popup_ids( $instance, $new_instance, $old_instance, $widget ) {
		if ( 'widget_text' === $widget->option_name ) {
			$value                = get_option( 'newspack_popups_widget_shortcode_popups_ids', [] );
			$value[ $widget->id ] = self::get_shortcoded_popups_ids( $new_instance['text'] );
			update_option( 'newspack_popups_widget_shortcode_popups_ids', $value );
		}
		return $instance;
	}

	/**
	 * Get all widget shortcoded popups IDs.
	 *
	 * @return array IDs of popups shortcoded in widgets.
	 */
	public static function get_all_widget_shortcoded_popups_ids() {
		return array_reduce(
			array_values( get_option( 'newspack_popups_widget_shortcode_popups_ids', [] ) ),
			function ( $acc, $item ) {
				return array_merge( $acc, $item );
			},
			[]
		);
	}

	/**
	 * Remove widgets shortcoded popup IDs.
	 *
	 * @param string $widget_id IDs of a widget.
	 */
	public static function remove_widgets_shortcoded_popup_ids( $widget_id ) {
		$value = get_option( 'newspack_popups_widget_shortcode_popups_ids', [] );
		unset( $value[ $widget_id ] );
		update_option( 'newspack_popups_widget_shortcode_popups_ids', $value );
	}
}
$newspack_popups_inserter = new Newspack_Popups_Inserter();
