<?php

class Paginator
{
	public $_paginatorWrap = '<div id="paginator">###PAGINATOR###</div>';
	public $_separator = '<span class="separator"></span>';
	public $_gap = '<span class="gap"> ... </span>';
	public $_currentPageWrap = '<span class="currentPage">###CURRENT_PAGE###</span>';
	public $_baseUrl = '&page=';
	public $_addArrows = true;
	public $_reverseArrows = false;

	private $_totalItemCount;
	private $_itemPerPage;
	private $_currentPage;

	public function __construct($totalItemCount, $itemPerPage = 5, $_currentPage = 0)
	{
		$this->_totalItemCount = $totalItemCount;
		$this->_itemPerPage = $itemPerPage;
		$this->_currentPage = $_currentPage;
	}

	public function getURL($n)
	{
		return $this->_baseUrl . $n;
	}

	public function getLink($n, $label = null, $class = null)
	{
		$rc = '<a href="' . $this->getURL($n) . '"';

		if($class)
		{
			$rc .= ' class="' . $class . '"';
		}

		$rc .= '>';

		$rc .= $label ? $label : ($n + 1);

		$rc .= '</a>';

		return $rc;
	}

	public function getPosition($n)
	{
		return str_replace("###CURRENT_PAGE###", $n, $this->_currentPageWrap);
	}

	public function __toString()
	{
		$pages = ceil($this->_totalItemCount / $this->_itemPerPage);

		if($pages == 1)
		{
			return;
		}

		$currentPage = $this->_currentPage + 1;
		$rc = null;

		if($pages > 10)
		{
			$init_page_max = min($pages, 3);

			for($i = 1 ; $i < $init_page_max + 1 ; $i++)
			{
				if($i == $currentPage)
				{
					$rc .= $this->getPosition($i);
				}
				else
				{
					$rc .= $this->getLink($i - 1);
				}

				if($i < $init_page_max)
				{
					$rc .= $this->_separator;
				}
			}

			if($pages > 3)
			{
				if($currentPage > 1 && $currentPage < $pages)
				{
					$rc .= ($currentPage > 5) ? $this->_gap : $this->_separator;
					$init_page_min = ($currentPage > 4) ? $currentPage : 5;
					$init_page_max = ($currentPage < $pages - 4) ? $currentPage : $pages - 4;

					for($i = $init_page_min - 1 ; $i < $init_page_max + 2 ; $i++)
					{
						$rc .= ($i == $currentPage) ? $this->getPosition($i) : $this->getLink($i - 1);

						if($i < $init_page_max + 1)
						{
							$rc .= $this->_separator;
						}
					}

					$rc .= ($currentPage < $pages - 4) ? $this->_gap : $this->_separator;
				}
				else
				{
					$rc .= $this->_gap;
				}

				for($i = $pages - 2 ; $i < $pages + 1 ; $i++)
				{
					$rc .= ($i == $currentPage) ? $this->getPosition($i) : $this->getLink($i - 1);

					if($i < $pages)
					{
						$rc .= $this->_separator;
					}
				}
			}
		}
		else
		{
			for($i = 1 ; $i < $pages + 1 ; $i++)
			{
				$rc .= ($i == $currentPage) ? $this->getPosition($i) : $this->getLink($i - 1);

				if($i < $pages)
				{
					$rc .= $this->_separator;
				}
			}
		}

		if($this->_addArrows)
		{
			#
			# add next (>) link
			#
			if($this->_reverseArrows ? ($currentPage > 1) : ($currentPage < $pages))
			{
				$rc .= $this->_separator . $this->getLink($this->_reverseArrows ? $currentPage - 2 : $currentPage, '&gt;', 'next');
			}

			#
			# add prev (<) link
			#
			if($this->_reverseArrows ? ($currentPage < $pages) : ($currentPage > 1))
			{
				$rc = $this->getLink($this->_reverseArrows ? $currentPage : $currentPage - 2, '&lt;', 'previous') . $this->_separator . $rc;
			}
		}
		
		$rc = str_replace("###PAGINATOR###", $rc, $this->_paginatorWrap);

		return $rc;
	}
}

?>