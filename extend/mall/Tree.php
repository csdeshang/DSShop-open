<?php
namespace mall;
/**
 * 树
 *
 * 0是根结点
 */
class Tree
{
    var $data   = array();
    var $child  = array(-1 => array());
    var $layer  = array(0 => 0);
    var $parent = array();
    var $value_field = '';
    /**
     * 构造函数
     *
     * @param mix $value
     */
    function construct($value = 'root')
    {
        $this->Tree($value);
    }

    function Tree($value = 'root')
    {
        $this->setNode(0, -1, $value);
    }

    /**
     * 构造树
     *
     * @param array $nodes 结点数组
     * @param string $id_field
     * @param string $parent_field
     * @param string $value_field
     */
    function setTree($nodes, $id_field, $parent_field, $value_field)
    {
        $this->value_field = $value_field;
        foreach ($nodes as $node)
        {
            $this->setNode($node[$id_field], $node[$parent_field], $node);
        }
        $this->setLayer();
    }

    /**
     * 取得options
     *
     * @param int $layer
     * @param int $root
     * @param string $space
     * @return array (id=>value)
     */
    function getOptions($layer = 0, $root = 0, $except = NULL, $space = '&nbsp;&nbsp;')
    {
        $options = array();
        $childs = $this->getChilds($root, $except);
        foreach ($childs as $id)
        {
            if ($id > 0 && ($layer <= 0 || $this->getLayer($id) <= $layer))
            {
                $options[$id] = $this->getLayer($id, $space) . htmlspecialchars($this->getValue($id));
            }
        }
        return $options;
    }

    /**
     * 设置结点
     *
     * @param mix $id
     * @param mix $parent
     * @param mix $value
     */
    function setNode($id, $parent, $value)
    {
        $parent = $parent ? $parent : 0;

        $this->data[$id] = $value;
        if (!isset($this->child[$id]))
        {
            $this->child[$id] = array();
        }

        if (isset($this->child[$parent]))
        {
            $this->child[$parent][] = $id;
        }
        else
        {
            $this->child[$parent] = array($id);
        }

        $this->parent[$id] = $parent;
    }

    /**
     * 计算layer
     */
    function setLayer($root = 0)
    {
        foreach ($this->child[$root] as $id)
        {
            $this->layer[$id] = $this->layer[$this->parent[$id]] + 1;
            if ($this->child[$id]) $this->setLayer($id);
        }
    }

    /**
     * 先根遍历，不包括root
     *
     * @param array $tree
     * @param mix $root
     * @param mix $except 除外的结点，用于编辑结点时，上级不能选择自身及子结点
     */
    function getList(&$tree, $root = 0, $except = NULL)
    {
        foreach ($this->child[$root] as $id)
        {
            if ($id == $except)
            {
                continue;
            }

            $tree[] = $id;

            if ($this->child[$id]) $this->getList($tree, $id, $except);
        }
    }

    function getValue($id)
    {
        return $this->data[$id][$this->value_field];
    }

    function getLayer($id, $space = false)
    {
        return $space ? str_repeat($space, $this->layer[$id]) : $this->layer[$id];
    }

    function getParent($id)
    {
        return $this->parent[$id];
    }

    /**
     * 取得祖先，不包括自身
     *
     * @param mix $id
     * @return array
     */
    function getParents($id)
    {
        while ($this->parent[$id] != -1)
        {
            $id = $parent[$this->layer[$id]] = $this->parent[$id];
        }

        ksort($parent);
        reset($parent);

        return $parent;
    }

    function getChild($id)
    {
        return $this->child[$id];
    }

    /**
     * 取得子孙，包括自身，先根遍历
     *
     * @param int $id
     * @return array
     */
    function getChilds($id = 0, $except = NULL)
    {
        $child = array($id);
        $this->getList($child, $id, $except);
        unset($child[0]);

        return $child;
    }

    /**
     * 先根遍历，数组格式
     * array(
     *     array('id' => '', 'value' => '', children => array(
     *         array('id' => '', 'value' => '', children => array()),
     *     ))
     * )
     */
    function getArrayList($root = 0 , $layer = NULL)
    {
        $data = array();
        foreach ($this->child[$root] as $id)
        {
            if($layer && $this->layer[$this->parent[$id]] > $layer-1)
            {
                continue;
            }
            $data[] = array('id' => $id, 'value' => $this->getValue($id), 'children' => $this->child[$id] ? $this->getArrayList($id , $layer) : array());
        }
        return $data;
    }

    /**
     * 取得csv格式数据
     *
     * @param int $root
     * @param mix $ext_field 辅助字段
     * @return array(
     *      array('辅助字段名','主字段名'), //如无辅助字段则无此元素
     *      array('辅助字段值','一级分类'), //如无辅助字段则无辅助字段值
     *      array('辅助字段值','一级分类'),
     *      array('辅助字段值','', '二级分类'),
     *      array('辅助字段值','', '', '三级分类'),
     * )
     */
    function getCSVData($root = 0, $ext_field = array())
    {
        $data = array();
        $main = $this->value_field; //用于显示树分级结果的字段
        $extra =array(); //辅助的字段
        if (!empty($ext_field))
        {
            if (is_array($ext_field))
            {
                $extra = $ext_field;
            }
            elseif (is_string($ext_field))
            {
                $extra = array($ext_field);
            }
        }
        $childs = $this->getChilds($root);
        array_values($extra) && $data[0] = array_values($extra);
        $main && $data[0] && array_push($data[0], $main);
        foreach ($childs as $id)
        {
            $row = array();
            $value = $this->data[$id];
            foreach ($extra as $field)
            {
                $row[] = $value[$field];
            }
            for ($i = 1; $i < $this->getLayer($id); $i++)
            {
                $row[] = '';
            }
            if ($main)
            {
                $row[] = $value[$main];
            }
            else
            {
                $row[] = $value;
            }
            $data[] = $row;
        }
        return $data;

    }
}

?>