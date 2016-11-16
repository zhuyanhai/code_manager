<?php
/**
 * 分页 类
 * 
 * @category F
 * @package F_Pagination
 * @author allen <allenifox@163.com>
 */
final class F_Pagination implements Countable, IteratorAggregate
{
    /**
     * 分页数据
     * 
     * @var array 
     */
    private $_datas = array();
    
    /**
     * 总页数
     * 
     * @var int 
     */
    private $_pageTotal = 0;
    
    /**
     * 当前第几页
     * 
     * @var int 
     */
    private $_pageNum = 0;
    
    /**
     * 总记录数
     * 
     * @var int 
     */
    private $_itemTotal = 0;
    
    /**
     * 每页期望记录数
     * 
     * @var int 
     */
    private $_itemCount = 0;
    
    /**
     * 当前页记录数
     * 
     * @var int 
     */
    private $_currentItemCount = 0;
    
    /**
     * 分页范围
     * 
     * @var int 
     */
    private $_pageRange = 10;
    
    /**
     * 分页对象信息
     * 
     * @var stdClass 
     */
    private $_pages = null;
    
    /**
     * 渲染视图路径
     * 
     * @var string 
     */
    private static $_pageView = '_slot/pagination_default';
    
    /**
     * 设置视图路径
     * 
     * @param string $pageView
     * @return void
     */
    public function setPageView($pageView)
    {
        self::$_pageView = $pageView;
    }
    
    /**
     * 构造函数
     * 
     * @param int $itemTotal
     * @param int $page
     * @param int $count
     * @param array $datas
     */
    public function __construct($itemTotal, $page, $count, $datas)
    {
        $this->_itemTotal = $itemTotal;
        $this->_pageNum   = $page;
        $this->_itemCount = $count;
        $this->_currentItemCount = count($datas);
        $this->_pageTotal = (integer) ceil($itemTotal / $count);
        $this->_datas     = $datas;
    }
    
    /**
     * 设置分页范围
     * 
     * @param $pageRange
     * @return F_Pagination
     */
    public function setPageRange($pageRange)
    {
        $this->_pageRange = $pageRange;
    }

    /**
     * 获取总页数
     * 
     * @return int
     */
    public function count()
    {
        return $this->_pageTotal;
    }
    
    /**
     * 获取每页期望记录数
     * 
     * @return int
     */
    public function itemCount()
    {
        return $this->_itemCount;
    }
    
    /**
     * 获取当前页记录数
     * 
     * @return int
     */
    public function currentItemCount()
    {
        return $this->_currentItemCount;
    }
    
    /**
     * 获取总记录数
     * 
     * @return int
     */
    public function itemTotal()
    {
        return $this->_itemTotal;
    }
    
    /**
     * Returns a foreach-compatible iterator.
     *
     * @return Traversable
     */
    public function getIterator()
    {
        $items = new ArrayIterator($this->_datas);
        return $items;
    }

    /**
     * 获取计算好的分页数据
     * 
     * @return \stdClass
     */
    public function getPages()
    {
        if ($this->_pages === null) {
            //总页数
            $pageTotal = $this->count();

            $pages = new stdClass();
            $pages->pageTotal        = $pageTotal;//总页数
            $pages->itemCountPerPage = $this->_itemCount;//每页数量
            $pages->first            = 1;//第1页
            $pages->current          = $this->_pageNum;//当前页码
            $pages->last             = $pageTotal;//最后一页

            //上一页页码
            if ($this->_pageNum - 1 > 0) {
                $pages->previous = $this->_pageNum - 1;
            }
            
            //下一页页码
            if ($this->_pageNum + 1 <= $pageTotal) {
                $pages->next = $this->_pageNum + 1;
            }

            // Pages in range
            $pages->pagesInRange     = $this->_getPageRange();
            $pages->firstPageInRange = min($pages->pagesInRange);
            $pages->lastPageInRange  = max($pages->pagesInRange);

            // Item numbers
            if (!empty($this->_datas)) {
                $pages->currentItemCount = $this->_currentItemCount;
                $pages->itemCountPerPage = $this->_itemCount;
                $pages->totalItemCount   = $this->_itemTotal;
                $pages->firstItemNumber  = (($this->_pageNum - 1) * $this->_itemCount) + 1;
                $pages->lastItemNumber   = $pages->firstItemNumber + $pages->currentItemCount - 1;
            }
        }
        
        return $pages;
    }
    
    /**
     * 渲染成html
     *
     * @return string
     */
    public function __toString()
    {
        try {
            $view = F_View::getInstance();
            $view->pages = $this->getPages();
            return $view->render(self::$_pageView);
        } catch (Exception $e) {
            trigger_error($e->getMessage(), E_USER_WARNING);
        }

        return '';
    }
    
    /**
     * 获取分页范围
     * 
     * @return array
     */
    private function _getPageRange()
    {
        $pageRange = $this->_pageRange;

        $pageNumber = $this->_pageNum;
        $pageCount  = $this->_pageTotal;

        if ($pageRange > $pageCount) {
            $pageRange = $pageCount;
        }

        $delta = ceil($pageRange / 2);

        if ($pageNumber - $delta > $pageCount - $pageRange) {
            $lowerBound = $pageCount - $pageRange + 1;
            $upperBound = $pageCount;
        } else {
            if ($pageNumber - $delta < 0) {
                $delta = $pageNumber;
            }

            $offset     = $pageNumber - $delta;
            $lowerBound = $offset + 1;
            $upperBound = $offset + $pageRange;
        }
        
        $lowerBound = $this->_normalizePageNumber($lowerBound);
        $upperBound = $this->_normalizePageNumber($upperBound);

        $pages = array();

        for ($pageNumber = $lowerBound; $pageNumber <= $upperBound; $pageNumber++) {
            $pages[$pageNumber] = $pageNumber;
        }

        return $pages;
    }
    
    /**
     * Brings the page number in range of the paginator.
     *
     * @param  integer $pageNumber
     * @return integer
     */
    private function _normalizePageNumber($pageNumber)
    {
        $pageNumber = (integer) $pageNumber;

        if ($pageNumber < 1) {
            $pageNumber = 1;
        }

        $pageCount = $this->_pageTotal;

        if ($pageCount > 0 && $pageNumber > $pageCount) {
            $pageNumber = $pageCount;
        }

        return $pageNumber;
    }

}