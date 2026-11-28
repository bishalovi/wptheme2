<?php
defined( 'ABSPATH' ) || exit;

/**
 * Swift Woo - Native GitHub Auto-Updater
 * Integrates silently with WordPress core theme updates (Appearance -> Themes & Dashboard -> Updates).
 * Scanner-safe, zero core/hook modifications.
 */
if ( ! class_exists( 'Swift_Woo_Theme_Updater' ) ) {
	class Swift_Woo_Theme_Updater {

		private $theme_slug;
		private $github_username;
		private $github_repo;
		private $current_version;

		public function __construct( $github_username, $github_repo ) {
			$this->theme_slug      = get_template();
			$this->github_username = sanitize_text_field( $github_username );
			$this->github_repo     = sanitize_text_field( $github_repo );

			$theme_obj             = wp_get_theme( $this->theme_slug );
			$this->current_version = $theme_obj->exists() ? $theme_obj->get( 'Version' ) : SWIFT_WOO_VERSION;

			// Hook early into WordPress Core Theme Update Transients
			add_filter( 'pre_set_site_transient_update_themes', array( $this, 'on_pre_set_transient' ), 20 );
			add_filter( 'site_transient_update_themes', array( $this, 'on_get_transient' ), 20 );

			// Theme Details Modal API in WP Admin
			add_filter( 'themes_api', array( $this, 'theme_popup_details' ), 20, 3 );

			// Ensure extracted directory name matches installed theme
			add_filter( 'upgrader_source_selection', array( $this, 'fix_source_folder' ), 10, 4 );
		}

		/**
		 * Fetch latest update info from GitHub raw JSON.
		 *
		 * @param bool $bypass_cache Whether to bypass local transient cache.
		 * @return array|false
		 */
		public function get_remote_info( $bypass_cache = false ) {
			$transient_key = 'swift_woo_update_cache_' . $this->theme_slug;
			$is_admin_check = is_admin() && (
				( isset( $GLOBALS['pagenow'] ) && in_array( $GLOBALS['pagenow'], array( 'update-core.php', 'themes.php' ), true ) ) ||
				isset( $_GET['force-check'] ) ||
				( isset( $_GET['action'] ) && 'core-update' === $_GET['action'] )
			);
			$force_check   = $bypass_cache || $is_admin_check;

			if ( ! $force_check ) {
				$cached = get_transient( $transient_key );
				if ( false !== $cached && is_array( $cached ) ) {
					return $cached;
				}
			}

			// 1. Primary: GitHub Tags API (instant, zero 5-minute CDN cache delay)
			$tags_url = sprintf(
				'https://api.github.com/repos/%s/%s/tags?per_page=1',
				rawurlencode( $this->github_username ),
				rawurlencode( $this->github_repo )
			);

			$tags_res = wp_remote_get( $tags_url, array(
				'timeout'    => 10,
				'user-agent' => 'WordPress/' . get_bloginfo( 'version' ) . '; ' . home_url( '/' ),
				'sslverify'  => true,
				'headers'    => array( 'Accept' => 'application/vnd.github.v3+json' ),
			) );

			if ( ! is_wp_error( $tags_res ) && 200 === wp_remote_retrieve_response_code( $tags_res ) ) {
				$tags = json_decode( wp_remote_retrieve_body( $tags_res ), true );
				if ( ! empty( $tags[0]['name'] ) ) {
					$version = ltrim( sanitize_text_field( $tags[0]['name'] ), 'v' );
					$data    = array(
						'version'      => $version,
						'download_url' => sprintf(
							'https://github.com/%s/%s/archive/refs/tags/v%s.zip',
							rawurlencode( $this->github_username ),
							rawurlencode( $this->github_repo ),
							$version
						),
						'details_url'  => sprintf(
							'https://github.com/%s/%s/releases/tag/v%s',
							rawurlencode( $this->github_username ),
							rawurlencode( $this->github_repo ),
							$version
						),
					);
					set_transient( $transient_key, $data, 30 * MINUTE_IN_SECONDS );
					return $data;
				}
			}

			// 2. Fallback: Raw update.json on GitHub main branch
			$json_url = sprintf(
				'https://raw.githubusercontent.com/%s/%s/main/update.json?nocache=%d',
				rawurlencode( $this->github_username ),
				rawurlencode( $this->github_repo ),
				time()
			);

			$response = wp_remote_get( $json_url, array(
				'timeout'    => 10,
				'user-agent' => 'WordPress/' . get_bloginfo( 'version' ) . '; ' . home_url( '/' ),
				'sslverify'  => true,
			) );

			if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
				return false;
			}

			$data = json_decode( wp_remote_retrieve_body( $response ), true );
			if ( empty( $data['version'] ) ) {
				return false;
			}

			if ( empty( $data['download_url'] ) || false !== strpos( $data['download_url'], 'releases/download' ) ) {
				$data['download_url'] = sprintf(
					'https://github.com/%s/%s/archive/refs/tags/v%s.zip',
					rawurlencode( $this->github_username ),
					rawurlencode( $this->github_repo ),
					$data['version']
				);
			}

			set_transient( $transient_key, $data, 30 * MINUTE_IN_SECONDS );
			return $data;
		}

		/**
		 * Fires when WordPress refreshes the update_themes transient (e.g. "Check Again" or WP-Cron).
		 */
		public function on_pre_set_transient( $transient ) {
			return $this->inject_update( $transient, true );
		}

		/**
		 * Fires when WordPress reads the update_themes transient (e.g. Appearance -> Themes).
		 */
		public function on_get_transient( $transient ) {
			return $this->inject_update( $transient, false );
		}

		/**
		 * Inject update data into WordPress theme transient if remote version is newer.
		 */
		private function inject_update( $transient, $bypass_cache = false ) {
			if ( ! is_object( $transient ) ) {
				$transient = new stdClass();
			}

			$remote = $this->get_remote_info( $bypass_cache );
			if ( ! $remote || empty( $remote['version'] ) || empty( $remote['download_url'] ) ) {
				return $transient;
			}

			if ( version_compare( $remote['version'], $this->current_version, '>' ) ) {
				$update_entry = array(
					'theme'        => $this->theme_slug,
					'new_version'  => $remote['version'],
					'url'          => $remote['details_url'] ?? sprintf( 'https://github.com/%s/%s/releases', $this->github_username, $this->github_repo ),
					'package'      => $remote['download_url'],
					'requires'     => $remote['requires'] ?? '6.3',
					'requires_php' => $remote['requires_php'] ?? '8.2',
				);

				if ( ! isset( $transient->response ) || ! is_array( $transient->response ) ) {
					$transient->response = array();
				}
				$transient->response[ $this->theme_slug ] = $update_entry;
			}

			return $transient;
		}

		/**
		 * Display Details Popup in Appearance -> Themes -> View version details
		 */
		public function theme_popup_details( $result, $action, $args ) {
			if ( 'theme_information' !== $action || empty( $args->slug ) || $args->slug !== $this->theme_slug ) {
				return $result;
			}

			$remote = $this->get_remote_info();
			if ( ! $remote ) {
				return $result;
			}

			$res                = new stdClass();
			$res->name          = 'Swift Woo';
			$res->slug          = $this->theme_slug;
			$res->version       = $remote['version'];
			$res->author        = 'Youtech';
			$res->homepage      = sprintf( 'https://github.com/%s/%s', $this->github_username, $this->github_repo );
			$res->download_link = $remote['download_url'];
			$res->sections      = array(
				'description' => 'Ultra-fast WooCommerce Theme for Bangladeshi e-commerce stores.',
				'changelog'   => $remote['changelog'] ?? 'Update available on GitHub.',
			);

			return $res;
		}

		/**
		 * Fix extracted GitHub folder name to match installed theme
		 */
		public function fix_source_folder( $source, $remote_source, $upgrader, $hook_extra = array() ) {
			global $wp_filesystem;

			if ( isset( $hook_extra['theme'] ) && $hook_extra['theme'] === $this->theme_slug ) {
				$corrected_source = trailingslashit( $remote_source ) . $this->theme_slug;
				if ( $source !== $corrected_source ) {
					$wp_filesystem->move( $source, $corrected_source );
					return trailingslashit( $corrected_source );
				}
			}

			return $source;
		}
	}
}

// Initialize immediately on load (after_setup_theme) so filters are registered before WP core _maybe_update_themes()
if ( ! function_exists( 'swift_woo_init_updater' ) ) {
	function swift_woo_init_updater() {
		$github_user = get_theme_mod( 'swift_github_user', 'bishalovi' );
		$github_repo = get_theme_mod( 'swift_github_repo', 'wptheme2' );
		new Swift_Woo_Theme_Updater( $github_user, $github_repo );
	}
}
add_action( 'after_setup_theme', 'swift_woo_init_updater' );
