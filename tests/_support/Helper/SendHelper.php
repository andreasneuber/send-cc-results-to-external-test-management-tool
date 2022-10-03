<?php

namespace Helper;

// all public methods declared in helper class will be available in $I

use GuzzleHttp\Client;
use Codeception\Exception\ModuleException;
use Codeception\Module;
use Codeception\TestInterface;
use GuzzleHttp\Exception\GuzzleException;

class SendHelper extends Module {
	private string $linebreak = "\r\n";
	private array $testnames = array();
	private array $failednames = array();
	private array $testresults = array();

	private array $test_id_mappings = array(
		'send_results_to_external_test_management_tool_Cest:passing_test' => '1',
		'send_results_to_external_test_management_tool_Cest:failing_test' => '2'
	);


	public function _beforeSuite( $settings = null ) {
		echo $this->linebreak;
	}

	/**
	 * @throws GuzzleException
	 * @throws ModuleException
	 */
	public function _afterSuite() {
		echo $this->linebreak;

		print_r( $this->testnames );
		print_r( $this->failednames );

		foreach ( $this->testnames as $test ) {
			if ( in_array( $test, $this->failednames ) ) {
				$this->testresults[ $this->get_test_id( $test ) ] = "fail";
			} else {
				$this->testresults[ $this->get_test_id( $test ) ] = "pass";
			}
		}

		print_r( $this->testresults );

		// here is where the actual sending to the test management system happens
		foreach ( $this->testresults as $test_id => $test_result ) {
			$this->contact_external_test_management_tool( $test_id, $test_result );
		}
	}

	public function _after( TestInterface $test ) {
		$raw_signature       = $test->getMetadata()->getFilename() . ':' . $test->getMetadata()->getName();
		$cest_with_test_name = $this->convert_raw_signature( $raw_signature );
		$this->testnames[]   = $cest_with_test_name;
	}

	public function _failed( TestInterface $test, $fail ) {
		$raw_signature       = $test->getMetadata()->getFilename() . ':' . $test->getMetadata()->getName();
		$cest_with_test_name = $this->convert_raw_signature( $raw_signature );
		$this->failednames[] = $cest_with_test_name;
	}

	// Helpers for our Helper :-)
	private function convert_raw_signature( $raw_signature ): string {
		$remove1 = codecept_root_dir() . 'tests' . DIRECTORY_SEPARATOR . 'acceptance';
		$remove2 = array( DIRECTORY_SEPARATOR, '.', 'php' );

		$filtered1 = str_replace( $remove1, '', $raw_signature );

		return str_replace( $remove2, '', $filtered1 );
	}

	private function get_test_id( $signature ) {
		return $this->test_id_mappings[ $signature ];
	}

	/**
	 * @throws ModuleException
	 * @throws GuzzleException
	 */
	private function contact_external_test_management_tool( $test_id, $test_result ) {
		$base_url = $this->getModule( '\Helper\ConfigHelper' )->_getConfig( 'external_test_management_base_url' );
		$url      = $base_url . $this->build_rest_api_call_url( $test_id );

		$username         = $this->getModule( '\Helper\ConfigHelper' )->_getConfig( 'external_test_management_username' );
		$pw               = $this->getModule( '\Helper\ConfigHelper' )->_getConfig( 'external_test_management_password' );
		$status_code_pass = $this->getModule( '\Helper\ConfigHelper' )->_getConfig( 'status_code_pass' );
		$status_code_fail = $this->getModule( '\Helper\ConfigHelper' )->_getConfig( 'status_code_fail' );
		$status_code      = 0;

		if ( $test_result == 'pass' ) {
			$status_code = $status_code_pass;
		}
		if ( $test_result == 'fail' ) {
			$status_code = $status_code_fail;
		}

		$client   = new Client();
		$response = $client->post( $url, [
			'headers' => [ 'Content-Type' => 'application/json' ],
			'json'    => [ 'status_id' => $status_code ],
			'verify'  => false,
			'auth'    => [ $username, $pw ]
		] );

		$this->output_response( $response );
	}

	/**
	 * @throws ModuleException
	 */
	private function build_rest_api_call_url( $test_id ): string {
		$test_run_id = $this->getModule( '\Helper\ConfigHelper' )->_getConfig( 'test_run_id' );
		$api_action  = $this->getModule( '\Helper\ConfigHelper' )->_getConfig( 'api_action' );

		return $api_action . $test_run_id . '/' . $test_id;
	}

	private function output_response( $response ): void {
		echo $response->getReasonPhrase();
		echo " - ";
		echo $response->getStatusCode();
		echo "\n";
	}

	/**
	 * For sending request via CC Module REST
	 *
	 *  After being authenticated call be made like this:
	 *  $json = $I->sendPost($url, ['status_id' => 1]);
	 *  $this->output_result($json);
	 *
	 * Challenge - $I is type AcceptanceTester, find a way to include it here in the helper
	 *
	 * @throws ModuleException
	 */
	private function authenticate_by( $method ): void {
		// see also https://codeception.com/docs/modules/REST

		switch ( $method ) {
			case 'aws':
				$I->amAWSAuthenticated();
				break;
			case 'bearer':
				$access_token = $this->getModule( '\Helper\ConfigHelper' )->_getConfig( 'external_test_management_access_token' );
				$I->amBearerAuthenticated( $access_token );
				break;
			case 'digest':
				$username = $this->getModule( '\Helper\ConfigHelper' )->_getConfig( 'external_test_management_username' );
				$pw       = $this->getModule( '\Helper\ConfigHelper' )->_getConfig( 'external_test_management_password' );
				$I->amDigestAuthenticated( $username, $pw );
				break;
			case 'http':
				echo $username = $this->getModule( '\Helper\ConfigHelper' )->_getConfig( 'external_test_management_username' );
				echo $pw = $this->getModule( '\Helper\ConfigHelper' )->_getConfig( 'external_test_management_password' );
				$I->amHttpAuthenticated( $username, $pw );
				break;
			case 'ntlm':
				$username = $this->getModule( '\Helper\ConfigHelper' )->_getConfig( 'external_test_management_username' );
				$pw       = $this->getModule( '\Helper\ConfigHelper' )->_getConfig( 'external_test_management_password' );
				$I->amNTLMAuthenticated( $username, $pw );
				break;
		}
	}

}
