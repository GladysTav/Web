<?php
/**
 * @package	AcyMailing for Joomla
 * @version	6.0.2
 * @author	acyba.com
 * @copyright	(C) 2009-2018 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die('Restricted access');
?><?php

class acympaginationHelper
{
    var $total;
    var $page;
    var $nbPerPage;

    function setStatus($total, $page, $nbPerPage)
    {
        $this->total = $total;
        $this->page = $page;
        $this->nbPerPage = $nbPerPage;
    }

    function display($page, $suffix = '')
    {
        $name = empty($page) ? 'pagination_page_ajax' : $page.'_pagination_page';
        $pagination = '<input type="hidden" id="acym_pagination'.$suffix.'" name="'.$name.'" value="'.$this->page.'"/>';

        $nbPages = ceil($this->total / $this->nbPerPage);

        $pagination .= '<div class="pagination text-center cell grid-x" role="navigation" aria-label="Pagination">
                            <div class="shrink pagination_container cell margin-auto">';

        if ($this->page > 1) {
            $pagination .= '<div class="pagination-previous pagination_one_pagination acym__pagination__page'.$suffix.'" page="'.($this->page - 1).'"><i class="material-icons revert180deg pagination__i">play_arrow</i></div>';
        } else {
            $pagination .= '<div class="pagination-previous pagination_one_pagination pagination_disabled"><i class="material-icons revert180deg pagination__i">play_arrow</i></div>';
        }

        $ellipsis = false;
        for ($i = 1; $i <= $nbPages; $i++) {
            if ($i > 2 && $i < $nbPages - 1 && ($i < $this->page - 1 || $i > $this->page + 1)) {
                if (!$ellipsis) {
                    $ellipsis = true;
                    $pagination .= '<div class="pagination_border_left"></div><div class="ellipsis pagination_one_pagination"></div><div class="pagination_border_right"></div>';
                }
                continue;
            }

            $ellipsis = false;

            if ($i == $this->page) {
                $pagination .= '<div class="pagination_border_left"></div><div class="pagination_current pagination_one_pagination">'.$this->page.'</div><div class="pagination_border_right"></div>';
            } else {
                $pagination .= '<div class="pagination_border_left"></div><div class="pagination_one_pagination acym__pagination__page'.$suffix.'" page="'.$i.'">'.$i.'</div><div class="pagination_border_right"></div>';
            }
        }

        if ($this->page < $nbPages) {
            $pagination .= '<div class="pagination-next pagination_one_pagination acym__pagination__page'.$suffix.'" page="'.($this->page + 1).'"><i class="material-icons pagination__i">play_arrow</i></div>';
        } else {
            $pagination .= '<div class="pagination-next pagination_one_pagination pagination_disabled"><i class="material-icons pagination__i">play_arrow</i></div>';
        }

        $pagination .= '</div></div>';

        return $pagination;
    }

    function displayAjax()
    {
        return $this->display('', '__ajax');
    }
}
