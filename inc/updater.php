<?php
defined( 'ABSPATH' ) || exit;

/**
 * Swift Woo - GitHub Auto-Updater
 * Integrates directly with WordPress native theme update engine.
 * Scanner-safe, additive hooks only, zero core modifications.
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
			$this->current_version = SWIFT_WOO_VERSION;

			// Hook into theme update checks
			add_filter( 'pre_set_site_transient_update_themes', array( $this, 'check_for_update' ), 20 );
			add_filter( 'site_transient_update_themes', array( $this, 'check_for_update' ), 20 );
			add_filter( 'upgrader_source_selection', array( $this, 'fix_source_folder' ), 10, 4 );
		}

		/**
		 * Fetch latest release info from GitHub
		 */
		private function get_remote_release() {
			// On Dashboard -> Updates "Check Again", force fresh check (bypass transient cache)
			$force_check   = isset( $_GET['force-check'] ) || ( isset( $_GET['action'] ) && 'core-update' === $_GET['action'] );
			$transient_key = 'swift_woo_gh_update_' . $this->theme_slug;

			if ( ! $force_check ) {
				$cached = get_transient( $transient_key );
				if ( false !== $cached ) {
					return $cached;
				}
			}

			// 1. Fetch raw update.json from GitHub main branch with cache buster
			$json_url = sprintf(
				'https://raw.githubusercontent.com/%s/%s/main/update.json?t=%d',
				rawurlencode( $this->github_username ),
				rawurlencode( $this->github_repo ),
				time()
			);

			$json_res = wp_remote_get( $json_url, array(
				'timeout'    => 10,
				'user-agent' => 'WordPress/' . get_bloginfo( 'version' ) . '; ' . home_url( '/' ),
				'sslverify'  => true,
			) );

			if ( ! is_wp_error( $json_res ) && 200 === wp_remote_retrieve_response_code( $json_res ) ) {
				$data = json_decode( wp_remote_retrieve_body( $json_res ), true );
				if ( ! empty( $data['version'] ) ) {
					// Direct raw zipball url from GitHub
					$data['download_url'] = sprintf(
						'https://github.com/%s/%s/archive/refs/tags/v%s.zip',
						rawurlencode( $this->github_username ),
						rawurlencode( $this->github_repo ),
						$data['version']
					);
					set_transient( $transient_key, $data, 3 * HOUR_IN_SECONDS );
					return $data;
				}
			}

			return false;
		}

		/**
		 * Inject update into WordPress transient
		 */
		public function check_for_update( $transient ) {
			if ( ! is_object( $transient ) ) {
				$transient = new stdClass();
			}

			$remote = $this->get_remote_release();
			if ( ! $remote || empty( $remote['version'] ) || empty( $remote['download_url'] ) ) {
				return $transient;
			}

			// If GitHub has a newer version than installed theme
			if ( version_compare( $remote['version'], $this->current_version, '>' ) ) {
				$update_data = array(
					'theme'       => $this->theme_slug,
					'new_version' => $remote['version'],
					'url'         => $remote['details_url'] ?? sprintf( 'https://github.com/%s/%s/releases', $this->github_username, $this->github_repo ),
					'package'     => $remote['download_url'],
				);

				if ( ! isset( $transient->response ) || ! is_array( $transient->response ) ) {
					$transient->response = array();
				}
				$transient->response[ $this->theme_slug ] = $update_data;
			}

			return $transient;
		}

		/**
		 * Fix folder name: GitHub archives are named 'wptheme2-1.0.3'.
		 * This ensures WordPress extracts it into 'swift-woo' seamlessly.
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

// Initialize updater
if ( ! function_exists( 'swift_woo_init_updater' ) ) {
	function swift_woo_init_updater() {
		$github_user = get_theme_mod( 'swift_github_user', 'bishalovi' );
		$github_repo = get_theme_mod( 'swift_github_repo', 'wptheme2' );
		new Swift_Woo_Theme_Updater( $github_user, $github_repo );
	}
}
// Run on admin_init as well as background cron/transient refresh
add_action( 'init', 'swift_woo_init_updater' );
