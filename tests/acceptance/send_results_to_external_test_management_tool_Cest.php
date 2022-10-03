<?php

namespace acceptance;

use AcceptanceTester;

/**
 * Class send_results_to_external_test_management_tool_Cest
 *
 * @group send_results
 */
class send_results_to_external_test_management_tool_Cest {


	/**
	 * @test_id 1
	 */
	public function passing_test( AcceptanceTester $I ): void {
		$thisIsTrue = true;
		$I->assertTrue( $thisIsTrue );
	}

	/**
	 * @test_id 2
	 */
	public function failing_test( AcceptanceTester $I ): void {
		$I->fail( 'This test has failed' );
	}
}
