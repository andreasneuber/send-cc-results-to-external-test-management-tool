<?php

namespace Helper;

// is not used, just to illustrate the usage of CC hooks better
// run test and observe output in terminal

// all public methods declared in helper class will be available in $I


use Codeception\Module;
use Codeception\TestInterface;

class DemoHookHelper extends Module {

	private string $linebreak = "\r\n";


	public function _beforeSuite( $settings = null ) {
		echo $this->linebreak;
		echo __CLASS__ . ':' . __FUNCTION__ . ' hook: Many greetings!';
	}

	public function _afterSuite() {
		echo $this->linebreak;
		echo __CLASS__ . ':' . __FUNCTION__ . ' hook: Many greetings!';
	}

	public function _after( TestInterface $test ) {
		echo $this->linebreak;
		echo __CLASS__ . ':' . __FUNCTION__ . ' hook: right after test';
	}

	public function _failed( TestInterface $test, $fail ) {
		echo $this->linebreak;
		echo __CLASS__ . ':' . __FUNCTION__ . ' hook: Hi - bad new, test has failed';
	}

	// helpers
	private function helper_function( $raw_signature ): void {
		// something useful
	}
}
