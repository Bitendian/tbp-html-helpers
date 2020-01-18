<?php

namespace Bitendian\TBP\HTML\Helpers\Paginator;

use Bitendian\TBP\UI\AbstractWidget;
use Bitendian\TBP\UI\Templater;
use Bitendian\TBP\Utils\Pagination;

class PaginatorWidget extends AbstractWidget
{
	const MAX_PAGES = 10;

	var $main_template;
	var $center_template;
	var $next_template;
	var $next_disabled_template;
	var $previous_template;
	var $previous_disabled_template;
	var $first_template;
	var $first_disabled_template;
	var $last_template;
	var $last_disabled_template;
	var $baseurl = ".";
	var $arg_name = 'page';

	var $prefix;

    /**
     * PaginatorWidget constructor.
     * @param Pagination $pagination
     * @param string $arg_name
     */
	function __construct($pagination, $arg_name = 'page')
    {
		$this->baseurl = $_SERVER['REQUEST_URI'];
		$this->arg_name = $arg_name;
		$this->init_paths();

		if ($pagination != null) {
			$has_previous = $pagination->getCurrentPage() > 1;
			$has_next = $pagination->getCurrentPage() < $pagination->getTotalPages();
			$showed_total_pages = $pagination->getTotalPages();
			$showed_current_page = $pagination->getCurrentPage();
			$showed_items_per_page = $pagination->getItemsPerPage();
			$showed_total_items = $pagination->getTotalItems();
		} else {
			$has_previous = false;
			$has_next = false;
			$showed_total_pages = 0;
			$showed_current_page = 0;
			$showed_items_per_page = 0;
			$showed_total_items = 0;
		}

		$ctx = new \stdClass();
		$ctx->numbers = '';

		$url_struct = parse_url($this->baseurl);
		$args = array();
		if (isset($url_struct['query'])) {
			parse_str(html_entity_decode($url_struct['query']), $args);
		}

		if ($has_next) {
			$args[$this->arg_name] = ($pagination->getCurrentPage() + 1);
			$ctx->next_page_url = $url_struct['path'] . '?' . htmlentities(http_build_query($args));
			$ctx->next = '' . new Templater($this->next_template, $ctx);
			$args[$this->arg_name] = $showed_total_pages;
			$ctx->last_page_url = $url_struct['path'] . '?' . htmlentities(http_build_query($args));
			$ctx->last = '' . new Templater($this->last_template, $ctx);
		} else {
			$ctx->next = '' . new Templater($this->next_disabled_template, $ctx);
			$ctx->last = '' . new Templater($this->last_disabled_template, $ctx);
		}

		if ($has_previous) {
			$args[$this->arg_name] = 1;
			$ctx->first_page_url = $url_struct['path'] . '?' . htmlentities(http_build_query($args));
			$ctx->first = '' . new Templater($this->first_template, $ctx);
			$args[$this->arg_name] = ($pagination->getCurrentPage() - 1);
			$ctx->previous_page_url = $url_struct['path'] . '?' . htmlentities(http_build_query($args));
			$ctx->previous = '' . new Templater($this->previous_template, $ctx);
		} else {
			$ctx->first = '' . new Templater($this->first_disabled_template, $ctx);
			$ctx->previous = '' . new Templater($this->previous_disabled_template, $ctx);
		}

		$goToPages = Array();

		// Paginator numbers
		$ctx->numbers = $this->render_go_to_page_numbers($showed_total_pages, $showed_current_page);
		$ctx->info = $this->render_info($showed_total_pages, $showed_current_page, $showed_items_per_page, $showed_total_items);

		$ctx->center = '' . (new Templater($this->center_template, $ctx));

		$this->html = '' . (new Templater($this->main_template, $ctx));
	}

	/**
	 * Construye un string con un indice de enlaces a las paginas en formato numerico.
	 * @param int $showed_total_pages Numero de paginas en total.
	 * @param int $showed_current_page Pagina actual.
	 * @return string con los links a las paginas.
	 */
	function render_go_to_page_numbers($showed_total_pages, $showed_current_page) {

		$url_struct = parse_url($this->baseurl);
		$args = array();
		if (isset($url_struct['query'])) {
			parse_str($url_struct['query'], $args);
		}
		$goToPages = array();

		$maxNumbers = 4;

		if ($maxNumbers >= $showed_total_pages) {
			$maxNumbers = $showed_total_pages;
			for ($i = 0; $i < $showed_total_pages; $i++) {
				$goToPages []= $this->render_page_number($i, $showed_current_page, $url_struct, $args);
			}
		} else {
			$i = min(max($showed_current_page - 2, 1), $showed_total_pages - ($maxNumbers - 1));
			$j = min($showed_total_pages - 1, $i + $maxNumbers - 1);

			// FIRST PAGE
			$goToPages []= $this->render_page_number(0, $showed_current_page, $url_struct, $args);

			// FIRST DOTS
			if ($showed_current_page > 3 && $showed_total_pages > $maxNumbers) {
				$goToPages []= '<span class="dots">...</span>';
			}

			for (; $i < $j; $i++) {
				$goToPages []= $this->render_page_number($i, $showed_current_page, $url_struct, $args);
			}

			// LAST DOTS
			if ($showed_current_page < ($showed_total_pages - 2) && $showed_total_pages > $maxNumbers) {
				$goToPages []= '<span class="dots">...</span>';
			}

			// LAST PAGE
			$goToPages []= $this->render_page_number($showed_total_pages - 1, $showed_current_page, $url_struct, $args);
		}

		return implode('', $goToPages);
	}

	function render_page_number($page, $showed_current_page, &$url_struct, &$args) {

		if ($page != ($showed_current_page - 1)) {
			if ($page != 0) $args[$this->arg_name] = ($page + 1);
			else unset($args[$this->arg_name]);
			$href = $url_struct['path'] . '?' . http_build_query($args);

			return '<a href="' . htmlentities($href) . '">' . ($page + 1) . '</a>';
		}
		return '<span>' . ($page + 1) . '</span>';
	}

	function render_info($showed_total_pages, $showed_current_page, $showed_items_per_page, $showed_total_items) {

		$showed_items_per_page = ($showed_total_items <= $showed_items_per_page) ? $showed_total_items : (($showed_current_page == $showed_total_pages) ? ($showed_total_items % $showed_items_per_page) : $showed_items_per_page);
		return '<em>' . number_format($showed_items_per_page, 0, '', '.') . '</em> ' . 'de' . ' <em>' . number_format($showed_total_items, 0, '', '.') . '</em> resultats';
	}

	/**
	 * Define las rutas para los templates que usa el widget paginador. Es un metodo gancho,
	 * con tal de extender el paginador y redefinir las rutas sobreescribiendo el metodo.
	 */
	private function init_paths()
    {

		$this->next_template =				__DIR__ . DIRECTORY_SEPARATOR . 'Next.template';
		$this->next_disabled_template =		__DIR__ . DIRECTORY_SEPARATOR . 'NextDisabled.template';
		$this->last_template =				__DIR__ . DIRECTORY_SEPARATOR . 'Last.template';
		$this->last_disabled_template =		__DIR__ . DIRECTORY_SEPARATOR . 'LastDisabled.template';
		$this->first_template =				__DIR__ . DIRECTORY_SEPARATOR . 'First.template';
		$this->first_disabled_template =	__DIR__ . DIRECTORY_SEPARATOR . 'FirstDisabled.template';
		$this->previous_template =			__DIR__ . DIRECTORY_SEPARATOR . 'Previous.template';
		$this->previous_disabled_template =	__DIR__ . DIRECTORY_SEPARATOR . 'PreviousDisabled.template';
		$this->main_template =				__DIR__ . DIRECTORY_SEPARATOR . 'Main.template';
	}

	function fetch(&$params) {}

	function render() { echo $this->html; }
}
