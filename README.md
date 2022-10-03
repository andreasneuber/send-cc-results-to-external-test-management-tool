# send-cc-results-to-external-test-management-tool

### Purpose
Provides guidance how sending of CC test results to an external test management tool could be implemented. Hope its of some use.

I was very tempted to publish here a specific implementation for one popular tool only.
But then I decided to keep things "vendor-agnostic" and rather provide general implementation steps/considerations.

### Setup
- `git clone`
- `composer install`
- `php vendor/bin/codecept build`
- Copy `codeception.dist.yml` to `codeception.yml`

### Step 1 - Have a closer look at your external test management tool
- Does the tool allow for creating test cases, and can these put together to "test runs"?
- Does the tool have a REST API?
- If so is the REST API well documented? :-)
- Does the REST API have endpoints to receive passed/failed results for a test case (in a test run)?
- Does the tool have a straight-forward but safe way to authenticate the REST API call?
- Do you manage to get it working with Postman?
- If so - very good chances that this will become a success :-)

### Step 2 - Have a look at file codeception.yml
- Values below line `- \Helper\ConfigHelper:` allow you to set config data of any kind, like e.g. the api base url
- In your CC test you can use the values like this `$I->getConfig('test_run_id')`
- Adjust / extend values as needed

### Step 3 - Adjust test file send_results_to_external_test_management_tool_Cest.php
Most likely these things need adjustment:
- `$api_action = '/add_test_result/';` - adjust to endpoint according to API docu of tool vendor
- `$this->authenticate_by($I,'http');` - CC REST module comes with different ways to authenticate
- `private function build_rest_api_call_url()` - often parameters are sent along like `test_run_id/test_id` but it might need changing
- `$I->sendPost` - if API docu shows verb "POST" this should be the right method, otherwise you might need to adjust

### Step 4 - Run CC
`php vendor/bin/codecept run --steps`

The CC test will attempt now to contact the external management tool via API 2 times, posting 'passed' and 'failed' results


### Step 5 - Verify in your external test management tool
Go to your TestRun.
Expected results: 1 Passed, 1 Failed.

### See also
- Example of a "Top 20 list" for Test Management tools - https://www.guru99.com/top-20-test-management-tools.html
- https://codeception.com/docs/modules/REST
- https://codeception.com/docs/ModulesAndHelpers#Hooks

### FAQs
#### # I want to pass the "test run ID" as variable from my pipeline into the Codeception test. Is that possible?
There is a good chance to get it working like this:
```php
// Create a 'tests/_bootstrap.php' file for auto-loading
// Add to codeception.yml 'bootstrap: _bootstrap.php'

// When pipeline is fired off we send along variable TEST_RUN_ID through pipeline UI

// This line in _bootstrap.php gets then the provided variable
$test_run_id = getenv( 'TEST_RUN_ID' );

// do something with $test_run_id in CC test (perhaps global scope is needed)
```

#### # I like to store the test ID in a phpdoc comment above the CC test. Can PHP read such comments?

If comment looks like this...
```
/**
* @test_id 1234
*/
public function test_something( AcceptanceTester $I ){ .. }
```
...then it should be possible to get the entire comment like this:
```
$ref = new ReflectionMethod('className', 'methodName');
$comment = $ref->getDocComment();
```
See also: https://www.php.net/manual/en/reflectionclass.getdoccomment.php

#### # I like to store the test ID in a method attribute. Can PHP read such attributes?

If attribute looks like this...
```
#[TestInfo(test_id: '1234', importance: 'Critical')]
public function test_something( AcceptanceTester $I ){ .. }
```

...then it should be possible to get attribute data like this:

` $attributes = $reflection->getAttributes();`

See here: https://www.php.net/manual/en/language.attributes.reflection.php for more code
