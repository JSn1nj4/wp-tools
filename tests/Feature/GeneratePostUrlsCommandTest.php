<?php

beforeEach(function (): void {
	$this->urlBase = 'https://example.com';
	$this->urlBaseWithSlash = 'https://example.com/';
});

it("throws an exception if 'url-base' argument is missing")
	->tap(fn () => $this->artisan('gen:post_urls', [
		'data' => [],
	]))
	->throws('Not enough arguments');

it("throws an exception if 'data' argument is missing")
	->tap(fn () => $this->artisan('gen:post_urls', [
		'url-base' => $this->urlBase,
	]))
	->throws('Not enough arguments');

it("supports post data in JSON format", function (): void {

});

it("can output URLs as a single line of JSON", function (): void {

});

it("can output URLs as pretty-formatted JSON", function (): void {

});

it("can output URLs as multi-line plain-text output", function (): void {

});
