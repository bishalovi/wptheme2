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
			add_filter( 'upgrader_source_selection', array( $this, 'fix_source_folder' ), 10, 4 );
		}

		/**
		 * Fetch latest release or tag info from GitHub
		 */
		private function get_remote_release() {
			$transient_key = 'swift_woo_gh_update_' . $this->theme_slug;
			$cached = get_transient( $transient_key );
			if ( false !== $cached ) {
				return $cached;
			}

			// 1. First check raw update.json on GitHub main branch
			$json_url = sprintf(
				'https://raw.githubusercontent.com/%s/%s/main/update.json',
				rawurlencode( $this->github_username ),
				rawurlencode( $this->github_repo )
			);

			$json_res = wp_remote_get( $json_url, array(
				'timeout'    => 10,
				'user-agent' => 'WordPress/' . get_bloginfo( 'version' ) . '; ' . home_url( '/' ),
			) );

			if ( ! is_wp_error( $json_res ) && 200 === wp_remote_retrieve_response_code( $json_res ) ) {
				$data = json_decode( wp_remote_retrieve_body( $json_res ), true );
				if ( ! empty( $data['version'] ) ) {
					// Ensure a reliable download URL
					if ( empty( $data['download_url'] ) || false !== strpos( $data['download_url'], 'releases/download' ) ) {
						$data['download_url'] = sprintf(
							'https://api.github.com/repos/%s/%s/zipball/refs/tags/v%s',
							rawurlencode( $this->github_username ),
							rawurlencode( $this->github_repo ),
							$data['version']
						);
					}
					set_transient( $transient_key, $data, 6 * HOUR_IN_SECONDS );
					return $data;
				}
			}

			// 2. Fallback: GitHub Tags API
			$tags_url = sprintf(
				'https://api.github.com/repos/%s/%s/tags',
				rawurlencode( $this->github_username ),
				rawurlencode( $this->github_repo )
			);

			$tags_res = wp_remote_get( $tags_url, array(
				'timeout'    => 10,
				'user-agent' => 'WordPress/' . get_bloginfo( 'version' ) . '; ' . home_url( '/' ),
			) );

			if ( ! is_wp_error( $tags_res ) && 200 === wp_remote_retrieve_response_code( $tags_res ) ) {
				$tags = json_decode( wp_remote_retrieve_body( $tags_res ), true );
				if ( ! empty( $tags ) && is_array( $tags ) ) {
					$latest_tag = $tags[0];
					$version    = ltrim( $latest_tag['name'], 'v' );
					$data = array(
						'version'      => sanitize_text_field( $version ),
						'download_url' => esc_url_raw( $latest_tag['zipball_url'] ),
						'details_url'  => sprintf( 'https://github.com/%s/%s/releases', rawurlencode( $this->github_username ), rawurlencode( $this->github_repo ) ),
					);
					set_transient( $transient_key, $data, 6 * HOUR_IN_SECONDS );
					return $data;
				}
			}

			return false;
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

		/**
		 * Fix folder name when upgrading from GitHub zipball (which names folders like 'bishalovi-wptheme2-6e21e26')
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
add_action( 'admin_init', 'swift_woo_init_updater' );
