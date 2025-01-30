<?php
// tests/Integration/BookmarkControllerTest.php

require_once __DIR__ . '/../../CIIntegrationTestCase.php';

class BookmarkControllerTest extends CIIntegrationTestCase
{
	/** @var PHPUnit_Framework_MockObject_MockObject */
	protected $bookmarkModel;

	protected function setUp()
	{
		parent::setUp();

		// 1) build a mock for the Bookmark_model
		$this->bookmarkModel = $this
			->getMockBuilder('Bookmark_model')
			->setMethods(['loadWhere','load','insert','update','delete'])
			->getMock();

		// 2) inject it into CI
		$this->CI->load->model('dashboard/Bookmark_model', 'BookmarkModel');
		$this->CI->BookmarkModel = $this->bookmarkModel;

		// 3) force a known user ID
		$this->CI->uid = 42;
	}

	public function test_test_true_endpoint()
	{
		$out = $this->request('GET', 'bookmark/test_true');
		$this->assertEquals('expected response', trim($out));
	}

	public function test_getBookmarks_returns_ordered_array()
	{
		$dummy = [
			(object)['bookmark_id'=>1,'url'=>'a','uid'=>42],
			(object)['bookmark_id'=>2,'url'=>'b','uid'=>42],
		];
		// expect loadWhere() → our dummy
		$this->bookmarkModel
			->expects($this->once())
			->method('loadWhere')
			->with(['uid'=>42])
			->willReturn($dummy);

		$out = $this->request('GET', 'bookmark/getBookmarks');
		$data = json_decode($out);
		$this->assertCount(2, $data);
		$this->assertEquals(1, $data[0]->bookmark_id);
	}

	public function test_delete_own_bookmark_succeeds()
	{
		// load() → record owned by our uid
		$this->bookmarkModel
			->expects($this->once())
			->method('load')
			->with(5)
			->willReturn([(object)['bookmark_id'=>5,'uid'=>42]]);

		// delete() → true
		$this->bookmarkModel
			->expects($this->once())
			->method('delete')
			->with(5)
			->willReturn(true);

		$out = $this->request('DELETE', 'bookmark/delete/5');
		$this->assertJsonStringEqualsJsonString('true', $out);
	}

	public function test_delete_not_owned_forbidden()
	{
		// load() → record owned by someone else
		$this->bookmarkModel
			->method('load')
			->willReturn([(object)['bookmark_id'=>8,'uid'=>99]]);

		$out = $this->request('DELETE', 'bookmark/delete/8');
		// your controller does → $this->_outputAuthError(), typically 403
		$this->assertStringContainsString('403', $out);
	}

	public function test_insert_validation_error()
	{
		// send invalid data
		$out = $this->request('POST', 'bookmark/insert', [
			'url'   => 'not-a-url',
			'title' => ''
		]);
		// expecting JSON validation errors
		$json = json_decode($out, true);
		$this->assertArrayHasKey('url',   $json);
		$this->assertArrayHasKey('title', $json);
	}

	public function test_insert_success()
	{
		$input = [
			'url'   => 'https://example.org',
			'title' => 'Example',
			'tag'   => 'phpunit',
		];
		// insert(...) → new ID 99
		$this->bookmarkModel
			->expects($this->once())
			->method('insert')
			->with($this->callback(function($args) use($input) {
				return $args['url'] === $input['url']
					&& $args['title'] === $input['title']
					&& $args['uid'] === 42;
			}))
			->willReturn(99);

		$out = $this->request('POST', 'bookmark/insert', $input);
		$this->assertJsonStringEqualsJsonString('99', $out);
	}

	public function test_update_validation_error()
	{
		$out = $this->request('POST', 'bookmark/update/3', [
			'url'   => 'bad',
			'title' => ''
		]);
		$json = json_decode($out, true);
		$this->assertArrayHasKey('url',   $json);
		$this->assertArrayHasKey('title', $json);
	}

	public function test_update_success()
	{
		$this->bookmarkModel
			->expects($this->once())
			->method('update')
			->with(3, $this->callback(function($d){
				return filter_var($d['url'], FILTER_VALIDATE_URL)
					&& isset($d['updateamum']);
			}))
			->willReturn(true);

		$out = $this->request('POST', 'bookmark/update/3', [
			'url'   => 'https://ci.org',
			'title' => 'CI3',
		]);
		$this->assertJsonStringEqualsJsonString('true', $out);
	}
}
