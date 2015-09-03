<?php

namespace Never5\LicenseWP\License;

class Manager {

	/**
	 * Generate a new unique license key
	 *
	 * @return string
	 */
	public function generate_license_key() {
		global $wpdb;

		do {
			// generate key
			$key = apply_filters( 'license_wp_generate_license_key', strtoupper( sprintf(
				'%04x-%04x-%04x-%04x',
				mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),
				mt_rand( 0, 0x0fff ) | 0x4000,
				mt_rand( 0, 0x3fff ) | 0x8000,
				mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
			) ) );

			// check if exists
			$result = $wpdb->get_row( $wpdb->prepare( 'SELECT `license_key` FROM ' . $wpdb->lwp_licenses . ' WHERE license_key = %s', $key ) );

		} while ( null !== $result ); // keep generating until we've got a unique key

		// return key
		return $key;
	}

	/**
	 * Get licenses by order ID
	 *
	 * @param int $order_id
	 * @param bool $active Whether or not to return only active licenses. Default false.

	 * @return array
	 */
	public function get_licenses_by_order( $order_id, $active = false ) {
		global $wpdb;

		// keys
		$licenses = array();

		// generate query
		$sql = $wpdb->prepare( 'SELECT `license_key` FROM ' . $wpdb->lwp_licenses . ' WHERE order_id = %d', $order_id );

		if ( $active ) {
			$sql .= " AND (
				date_expires IS NULL
				OR date_expires = '0000-00-00 00:00:00'
				OR date_expires > NOW()
			)";
		}

		// fetch keys
		$results = $wpdb->get_results( $sql );

		// count & loop
		if ( count( $results ) > 0 ) {
			foreach ( $results as $result ) {
				// add to array
				$licenses[] = license_wp()->service( 'license_factory' )->make( $result->license_key );
			}
		}

		// return license keys
		return $licenses;
	}

	/**
	 * Get licenses by user ID
	 *
	 * @param int $user_id
	 * @param bool $active Whether or not to return only active licenses. Default false.
	 *
	 * @return array
	 */
	public function get_licenses_by_user( $user_id, $active = false ) {
		global $wpdb;

		// keys
		$licenses = array();

		// generate query
		$sql = $wpdb->prepare( 'SELECT `license_key` FROM ' . $wpdb->lwp_licenses . ' WHERE user_id = %d', $user_id );

		if ( $active ) {
			$sql .= " AND (
				date_expires IS NULL
				OR date_expires = '0000-00-00 00:00:00'
				OR date_expires > NOW()
			)";
		}

		// fetch keys
		$results = $wpdb->get_results( $sql );

		// count & loop
		if ( count( $results ) > 0 ) {
			foreach ( $results as $result ) {
				// add to array
				$licenses[] = license_wp()->service( 'license_factory' )->make( $result->license_key );
			}
		}

		// return license keys
		return $licenses;
	}

	/**
	 * Get licenses by email addresses
	 *
	 * @param string $email
	 * @param bool $active Whether or not to return only active licenses. Default false.

	 * @return array
	 */
	public function get_licenses_by_email( $email, $active = false ) {
		global $wpdb;

		// keys
		$licenses = array();

		// generate query
		$sql = $wpdb->prepare( 'SELECT `license_key` FROM ' . $wpdb->lwp_licenses . ' WHERE activation_email = %d', $email );

		if ( $active ) {
			$sql .= " AND (
				date_expires IS NULL
				OR date_expires = '0000-00-00 00:00:00'
				OR date_expires > NOW()
			)";
		}

		// fetch keys
		$results = $wpdb->get_results( $sql );

		// count & loop
		if ( count( $results ) > 0 ) {
			foreach ( $results as $result ) {
				// add to array
				$licenses[] = license_wp()->service( 'license_factory' )->make( $result->license_key );
			}
		}

		// return license keys
		return $licenses;
	}

	/**
	 * Remove license by order ID
	 *
	 * @param $order_id
	 */
	public function remove_license_data_by_order( $order_id ) {
		global $wpdb;

		// get license keys
		$licenses = $this->get_licenses_by_order( $order_id );

		// count and loop
		if ( count( $licenses ) > 0 ) {
			foreach ( $licenses as $license ) {
				// delete all data connected to license key
				$wpdb->delete( $wpdb->lwp_licenses, array( 'license_key' => $license->get_key() ) );
				$wpdb->delete( $wpdb->lwp_activations, array( 'license_key' => $license->get_key() ) );
				$wpdb->delete( $wpdb->lwp_download_log, array( 'license_key' => $license->get_key() ) );
			}
		}

	}

}
