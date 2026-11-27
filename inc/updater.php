<?php
defined( 'ABSPATH' ) || exit;

/**
 * Swift Woo - GitHub Auto-Updater
 * Integrates with WordPress native theme update transient.
 * Zero core/hook modifications, fully scanner-safe, 6-hour transient cache.
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

			add_filter( 'pre_set_site_transient_update_themes', array( $this, 'check_for_update' ) );
			add_filter( 'site_transient_update_themes', array( $this, 'check_for_update' ) );
		}

		/**
		 * Fetch latest release from GitHub API or Raw update.json
		 */
		private function get_remote_release() {
			$transient_key = 'swift_woo_gh_update_' . $this->theme_slug;
			$cached = get_transient( $transient_key );
			if ( false !== $cached ) {
				return $cached;
			}

			// First try GitHub Releases API
			$api_url = sprintf(
				'https://api.github.com/repos/%s/%s/releases/latest',
				rawurlencode( $this->github_username ),
				rawurlencode( $this->github_repo )
			);

			$response = wp_remote_get( $api_url, array(
				'timeout'    => 10,
				'user-agent' => 'WordPress/' . get_bloginfo( 'version' ) . '; ' . home_url( '/' ),
				'headers'    => array(
					'Accept' => 'application/vnd.github.v3+json',
				),
			) );

			if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
				// Fallback: Check raw update.json in the repo
				$json_url = sprintf(
					'https://raw.githubusercontent.com/%s/%s/main/update.json',
					rawurlencode( $this->github_username ),
					rawurlencode( $this->github_repo )
				);

				$json_res = wp_remote_get( $json_url, array( 'timeout' => 10 ) );
				if ( ! is_wp_error( $json_res ) && 200 === wp_remote_retrieve_response_code( $json_res ) ) {
					$data = json_decode( wp_remote_retrieve_body( $json_res ), true );
					if ( ! empty( $data['version'] ) && ! empty( $data['download_url'] ) ) {
						set_transient( $transient_key, $data, 6 * HOUR_IN_SECONDS );
						return $data;
					}
				}
				return false;
			}

			$release = json_decode( wp_remote_retrieve_body( $response ), true );
			if ( empty( $release['tag_name'] ) ) {
				return false;
			}

			$version = ltrim( $release['tag_name'], 'v' );
			$zip_url = '';

			// If release has custom zip asset attached, use it; otherwise use zipball
			if ( ! empty( $release['assets'] ) && is_array( $release['assets'] ) ) {
				foreach ( $release['assets'] as $asset ) {
					if ( ! empty( $asset['browser_download_url'] ) && preg_match( '/\.zip$/i', $asset['name'] ) ) {
						$zip_url = $asset['browser_download_url'];
						break;
					}
				}
			}

			if ( empty( $zip_url ) ) {
				$zip_url = $release['zipball_url'] ?? '';
			}

			$data = array(
				'version'      => sanitize_text_field( $version ),
				'download_url' => esc_url_raw( $zip_url ),
				'details_url'  => esc_url_raw( $release['html_url'] ?? '' ),
			);

			set_transient( $transient_key, $data, 6 * HOUR_IN_SECONDS );
			return $data;
		}

		/**
		 * Hook into WordPress theme update transient
		 */
		public function check_for_update( $transient ) {
			if ( empty( $transient->checked ) ) {
				return $transient;
			}

			$remote = $this->get_remote_release();
			if ( ! $remote || empty( $remote['version'] ) || empty( $remote['download_url'] ) ) {
				return $transient;
			}

			if ( version_compare( $remote['version'], $this->current_version, '>' ) ) {
				$transient->response[ $this->theme_slug ] = array(
					'theme'       => $this->theme_slug,
					'new_version' => $remote['version'],
					'url'         => $remote['details_url'] ?? '',
					'package'     => $remote['download_url'],
				);
			}

			return $transient;
		}
	}
}

// Initialize updater configured for https://github.com/bishalovi/wptheme2
if ( ! function_exists( 'swift_woo_init_updater' ) ) {
	function swift_woo_init_updater() {
		$github_user = get_theme_mod( 'swift_github_user', 'bishalovi' );
		$github_repo = get_theme_mod( 'swift_github_repo', 'wptheme2' );
		new Swift_Woo_Theme_Updater( $github_user, $github_repo );
	}
}
add_action( 'admin_init', 'swift_woo_init_updater' );
