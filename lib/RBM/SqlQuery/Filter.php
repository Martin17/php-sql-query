<?php

namespace RBM\SqlQuery;

class Filter
{

    const OPERATOR_GREATER_THAN_OR_EQUAL = '>=';
    const OPERATOR_GREATER_THAN = '>';
    const OPERATOR_LOWER_THAN_OR_EQUAL = '<=';
    const OPERATOR_LOWER_THAN = '<';
    const OPERATOR_LIKE = 'LIKE';
    const OPERATOR_NOT_LIKE = 'NOT LIKE';
    const OPERATOR_EQUAL = '=';
    const OPERATOR_NOT_EQUAL = '<>';

    /** @var Table */
    protected $_table;

    /** @var array */
    protected $_comparisons = array();

    /** @var array */
    protected $_betweens = array();

    /** @var array */
    protected $_isNull = array();

    /** @var array */
    protected $_isNotNull = array();

    /** @var array */
    protected $_booleans = array();

    /** @var array */
    protected $_ins = array();

    /** @var array */
    protected $_notIns = array();

    /** @var Filter[] */
    protected $_subFilters = array();

    /** @var string */
    protected $_operator = "AND";

    /**
     * Deep copy for nested references
     * @return mixed
     */
    public function __clone()
    {
        return unserialize(serialize($this));
    }

    public function isEmpty()
    {
        return
            count($this->_comparisons) +
            count($this->_booleans) +
            count($this->_betweens) +
            count($this->_isNotNull) +
            count($this->_isNull) +
            count($this->_ins) +
            count($this->_notIns) +
            count($this->_subFilters) == 0;
    }

    /**
     * @param $table string|Table
     */
    public function setTable($table)
    {
        $this->_table = $table;
    }

    /**
     * @return Table
     */
    public function getTable()
    {
        return Helper::prepareTable($this->_table);
    }
    /**
     * @return string
     */
    public function getOperator()
    {
        return $this->_operator;
    }

    /**
     * @return Filter[]
     */
    public function getSubFilters()
    {
        return $this->_subFilters;
    }

    public function subFilter()
    {
        /** @var $filter Filter */
        $filter = new static();
        $filter->setTable($this->getTable());
        $this->_subFilters[] = $filter;
        return $filter;
    }

    /**
     * @param $col
     * @param $value
     * @param $operation
     * @return Filter
     */
    public function compare($col, $value, $operator)
    {
        $col = $this->_prepareCol($col);
        $this->_comparisons[] = array(
            "subject" => $col,
            "operator" => $operator,
            "target" => $value
        );
        return $this;
    }

    /**
     * @param $col
     * @param $value
     * @return Filter
     */
    public function equals($col, $value)
    {
        return $this->compare($col, $value, self::OPERATOR_EQUAL);
    }

    /**
     * @param $col
     * @param $value
     * @return Filter
     */
    public function notEquals($col, $value)
    {
        return $this->compare($col, $value, self::OPERATOR_NOT_EQUAL);
    }

    /**
     * @param $col
     * @param $value
     * @return Filter
     */
    public function greaterThan($col, $value)
    {
        return $this->compare($col, $value, self::OPERATOR_GREATER_THAN);
    }

    /**
     * @param $col
     * @param $value
     * @return Filter
     */
    public function greaterThanEquals($col, $value)
    {
        return $this->compare($col, $value, self::OPERATOR_GREATER_THAN_OR_EQUAL);
    }

    /**
     * @param $col
     * @param $value
     * @return Filter
     */
    public function lowerThan($col, $value)
    {
        return $this->compare($col, $value, self::OPERATOR_LOWER_THAN);
    }

    /**
     * @param $col
     * @param $value
     * @return Filter
     */
    public function lowerThanEquals($col, $value)
    {
        return $this->compare($col, $value, self::OPERATOR_LOWER_THAN_OR_EQUAL);
    }

    /**
     * @param $col
     * @param $value
     * @return Filter
     */
    public function like($col, $value)
    {
        return $this->compare($col, $value, self::OPERATOR_LIKE);
    }

    /**
     * @param $col
     * @param $value
     * @return Filter
     */
    public function notLike($col, $value)
    {
        return $this->compare($col, $value, self::OPERATOR_NOT_LIKE);
    }

    /**
     * @param $col
     * @param $values
     * @return Filter
     */
    public function in($col, $values)
    {
        $this->_ins[$col] = $values;
        return $this;
    }

    /**
     * @param $col
     * @param $values
     * @return Filter
     */
    public function notIn($col, $values)
    {
        $this->_notIns[$col] = $values;
        return $this;
    }

    /**
     * @param $col
     * @param $a
     * @param $b
     * @return Filter
     */
    public function between($col, $a, $b)
    {
        $col = $this->_prepareCol($col);
        $this->_betweens[] = array(
            "column" => $col,
            "a" => $a,
            "b" => $b,
        );
        return $this;
    }

    /**
     * @param $col
     * @return Filter
     */
    public function isNull($col)
    {
        $col = $this->_prepareCol($col);
        $this->_isNull[] = array(
            "column" => $col,
        );
        return $this;
    }

    /**
     * @param $col
     * @return Filter
     */
    public function isNotNull($col)
    {
        $col = $this->_prepareCol($col);
        $this->_isNotNull[] = array(
            "column" => $col,
        );
        return $this;
    }

    /**
     * @param $column
     * @param $value
     * @return Filter
     */
    public function addBitClause($column, $value)
    {
        $col = $this->_prepareCol($column);
        $this->_booleans[] = array(
            "column" => $col,
            "value" => ($value),
        );
        return $this;
    }

    /**
     * @param $col
     * @return Column
     */
    protected function _prepareCol($col)
    {
        return Helper::prepareColumn($col, $this->getTable());
    }



    /**
     * @return array
     */
    public function getIns()
    {
        return $this->_ins;
    }

    /**
     * @return array
     */
    public function getNotIns()
    {
        return $this->_notIns;
    }


    /**
     * @param $operator
     * @return $this
     */
    public function operator($operator)
    {
        $this->_operator = $operator;
        return $this;
    }

    /**
     * @return array
     */
    public function getBetweens()
    {
        return $this->_betweens;
    }

    /**
     * @return array
     */
    public function getBooleans()
    {
        return $this->_booleans;
    }

    /**
     * @return array
     */
    public function getComparisons()
    {
        return $this->_comparisons;
    }

    /**
     * @return array
     */
    public function getIsNotNull()
    {
        return $this->_isNotNull;
    }

    /**
     * @return array
     */
    public function getIsNull()
    {
        return $this->_isNull;
    }


}
