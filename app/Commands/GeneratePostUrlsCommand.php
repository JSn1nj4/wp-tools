<?php

namespace App\Commands;

use Illuminate\Support\Collection;
use LaravelZero\Framework\Commands\Command;

class GeneratePostUrlsCommand extends Command
{
	/**
	 * The signature of the command.
	 *
	 * @todo: add support for XML input type
	 * @todo: add support for XML output type
	 *
	 * @var string
	 */
	protected $signature = 'get:post_urls
							{--t|type=post : Supported types: post, page.}
							{--i|input=json : Supported types: json.}
							{--o|output=text : Supported types: text.}
							{--pretty-print : Format output for display. This is useful if it will be stored in a file for reading.}
							{url-base : The base URL for the website. Used for constructing all URLs.}
							{data : The data to generate URLs from.}';

	/**
	 * The description of the command.
	 *
	 * @var string
	 */
	protected $description = 'Generate post URLs from exported post data.';

	private Collection $posts;

	private string $urlBase;

	private function buildFullUrl(object $post): string
	{
		return "{$this->urlBase}/{$this->buildRelativeUrl($post)}";
	}

	/**
	 * Build the segment of the URL that comes immediately after the site URL
	 */
	private function buildRelativeUrl(object $post): string
	{
		return match($post->post_parent) {
			"0" => "",
			default => $this->buildRelativeUrl($this->posts->get($post->post_parent)),
		} . "{$post->post_name}/";
	}

	/**
	 * Execute the console command.
	 *
	 * @todo: output data in desired format
	 * @todo: support JSON output
	 * @todo: support pretty printed output
	 *
	 * @return int
	 */
	public function handle()
	{
		$this->setup();

		$missingParents = $this->getMissingParentPosts();

		if($missingParents->isNotEmpty()) {
			$this->error("Parent posts ({$missingParents->implode(', ')}) missing. Please provide this post data so all URLs can be generated.");

			return Command::FAILURE;
		}

		$this->line($this->posts
				->map(fn (object $post): string => $this->buildFullUrl($post)));

		return Command::SUCCESS;
	}

	/**
	 * Collect a list of parent posts
	 *
	 * This allows checking if there are parent posts missing and using
	 * the IDs in error output to the command caller.
	 */
	private function getMissingParentPosts(): Collection
	{
		return $this->posts
			->where('post_parent', '!==', '0')
			->reduce(function (Collection $missingParents, object $post) {
				if ($this->posts->has($post->post_parent)) {
					$missingParents->push($post->post_parent);
				}

				return $missingParents;
			}, collect([]))
			->unique()
			->sort();
	}

	private function parseJson(): Collection
	{
		return collect(json_decode($this->argument('data')))
			->reduce(function (Collection $filtered, object $post): Collection {
				if(!$filtered->has($post->ID)) {
					$filtered->put($post->ID, $post);
				}

				return $filtered;
			}, collect([]));
	}

	private function setup(): void
	{
		$this->urlBase = Str::endsWith($this->argument('url-base'), '/')
			? Str::of($this->argument('url-base'))->beforeLast('/')
			: $this->argument('url-base');

		$this->posts = match($this->option('input')) {
			default => $this->parseJson(),
		};
	}
}
