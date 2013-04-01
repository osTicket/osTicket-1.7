<?php
/*********************************************************************
    class.orm.php

    Simple ORM (Object Relational Mapper) for PHPv4 based on Django's ORM,
    except that complex filter operations are not supported. The ORM simply
    supports ANDed filter operations without any GROUP BY support.

    Jared Hancock <jared@osticket.com>
    Copyright (c)  2006-2013 osTicket
    http://www.osticket.com

    Released under the GNU General Public License WITHOUT ANY WARRANTY.
    See LICENSE.TXT for details.

    vim: expandtab sw=4 ts=4 sts=4:
**********************************************************************/

class VerySimpleModel {
    function VerySimpleModel($row) {
        # Not called in PHP5
        call_user_func_array(array(&$this, '__construct'), func_get_args());
    }

    function __construct($row) {
        $this->ht = $row;
        $this->dirty = array();
    }

    function get($field) {
        return $this->ht[$field];
    }

    function set($field, $value) {
        $old = isset($this->ht[$field]) ? $this->ht[$field] : null;
        if ($old != $value) {
            $this->dirty[$field] = $old;
            $this->ht[$field] = $value;
        }
    }

    function setAll($props) {
        foreach ($props as $field=>$value)
            $this->set($field, $value);
    }

    function all($class, $table, $sort=false, $limit=false, $offset=false) {
        return self::find($class, $table, false, $sort, $limit, $offset);
    }

    function _get_joins_and_field($class, $field) {
        $joins = array();
        $parts = explode('__', $field);
        $field = array_pop($parts);
        foreach ($parts as $p) {
            $context = call_user_func(array($class, 'getJoins'));
            list($left, $class, $table, $right) = $context[$p];
            $joins[] = ' INNER JOIN '.$table
                .' ON ('.$left.' = '.$table.'.'.$right.')';
        }
        if ($table)
            $field = $table.'.'.$field;
        return array($joins, $field);
    }

    function _compile_where($class, $where) {
        $joins = array();
        $filter = array();
        foreach ($where as $field=>$value) {
            list($js, $field) = self::_get_joins_and_field($class, $field);
            $joins = array_merge($joins, $js);
            $filter[] = $field.' = '.db_input($value);
        }
        return array($joins, $filter);
    }

    function count($class, $table, $where=false) {
        if ($where) {
            list($joins, $filter) = self::_compile_where($class, $where);
            $where = ' WHERE ' . implode(' AND ', $filter);
            $joins = implode('', array_unique($joins));
        }
        $sql = 'SELECT COUNT(*) FROM '.$table.$joins.$where;
        return db_count($sql);
    }

    function find($class, $table, $where=false, $sort=false, $limit=false,
            $offset=false) {
        if ($where) {
            list($joins, $filter) = self::_compile_where($class, $where);
            $where = ' WHERE ' . implode(' AND ', $filter);
        }
        if ($sort) {
            $dir = 'ASC';
            if (substr($sort, 0, 1) == '-') {
                $dir = 'DESC';
                $sort = substr($sort, 1);
            }
            list($js, $field) = self::_get_joins_and_field($class, $sort);
            $joins = ($joins) ? array_merge($joins, $js) : $js;
            $sort = ' ORDER BY '.$field.' '.$dir;
        }
        if (is_array($joins))
            # XXX: This will change the order of the joins
            $joins = implode('', array_unique($joins));
        $sql = 'SELECT '.$table.'.* FROM '.$table.$joins.$where.$sort;
        if ($limit)
            $sql .= ' LIMIT '.$limit;
        if ($offset)
            $sql .= ' OFFSET '.$offset;

        $res = db_query($sql);
        $list = array();
        while ($row = db_fetch_array($res))
            $list[] = new $class($row);
        return $list;
    }

    function lookup($class, $table, $where) {
        $list = self::find($class, $table, $where, false, 1);
        return $list[0];
    }

    function delete($table, $pk) {
        $sql = 'DELETE FROM '.$table;
        if (!is_array($pk)) $pk=array($pk);
        foreach ($pk as $p)
            $filter[] = $p.' = '.db_input($this->get($p));
        $sql .= ' WHERE '.implode(' AND ', $filter).' LIMIT 1';
        return db_affected_rows(db_query($sql)) == 1;
    }

    function save($table, $pk, $refetch=false) {
        if (!$this->isValid())
            return false;
        if (!is_array($pk)) $pk=array($pk);
        if ($this->__new__)
            $sql = 'INSERT INTO '.$table;
        else
            $sql = 'UPDATE '.$table;
        $filter = $fields = array();
        if (count($this->dirty) === 0)
            return;
        foreach ($this->dirty as $field=>$old)
            if ($this->__new__ or !in_array($field, $pk))
                if (@get_class($this->ht[$field]) == 'SqlFunction')
                    $fields[] = $field.' = '.$this->ht[$field]->toSql();
                else
                    $fields[] = $field.' = '.db_input($this->ht[$field]);
        foreach ($pk as $p)
            $filter[] = $p.' = '.db_input($this->get($p));
        $sql .= ' SET '.implode(', ', $fields);
        if (!$this->__new__) {
            $sql .= ' WHERE '.implode(' AND ', $filter);
            $sql .= ' LIMIT 1';
        }
        if (db_affected_rows(db_query($sql)) != 1)
            return false;
        if ($this->__new__ && count($pk) == 1) {
            $this->ht[$pk[0]] = db_insert_id();
            $this->__new__ = false;
        }
        # Refetch row from database
        # XXX: Too much voodoo
        if ($refetch)
            # XXX: Support composite PK
            $this->ht = self::lookup(get_class(), $table,
                array($pk[0] => $this->get($pk[0])))->ht;
        return $this->get($pk[0]);
    }

    function create($class, $ht=false) {
        if (!$ht) $ht=array();
        $i = new $class(array());
        $i->__new__ = true;
        foreach ($ht as $field=>$value)
            $i->set($field, $value);
        return $i;
    }

    /**
     * isValid
     *
     * Validates the contents of $this->ht before the model should be
     * committed to the database. This is the validation for the field
     * template -- edited in the admin panel for a form section.
     */
    function isValid() {
        return true;
    }

    function getJoins() {
        return array();
    }
}

class SqlFunction {
    function SqlFunction($name) {
        $this->func = $name;
        $this->args = array_slice(func_get_args(), 1);
    }

    function toSql() {
        $args = (count($this->args)) ? implode(',', db_input($this->args)) : "";
        return sprintf('%s(%s)', $this->func, $args);
    }
}
?>
