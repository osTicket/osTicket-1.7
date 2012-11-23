<?php
/*********************************************************************
    class.dynamic_forms.php

    Classes to support dynamic forms for osTicket
    
    Jared Hancock <jared@osticket.com>
    Copyright (c)  2006-2012 osTicket
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

    function all($class, $table, $sort=false) {
        return self::find($class, $table, false, $sort);
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
        if (!$this->validate())
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
        if ($this->__new__ && count($pk) == 1)
            $this->ht[$pk[0]] = db_insert_id();
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

    function validate() {
        return $this->isValid();
    }

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

/**
 * Form template, used for designing the custom form and for entering custom
 * data for a ticket
 */
class DynamicForm extends VerySimpleModel {

    function __construct($row) {
        parent::__construct($row);
        $this->id = $row['id'];
        $this->_errors = false;
    }
    
    function getFields() {
        if (!$this->_fields) {
            $this->_fields = array();
            foreach (DynamicFormField::find(array('form_id'=>$this->id)) as $f)
                $this->_fields[] = $f->getImpl();
        }
        return $this->_fields;
    }
    function getTitle() { return $this->get('title'); }

    function isValid() {
        if ($this->_errors === null)
            $this->validate();
        return !$this->_errors;
    }

    function validate() {
        # Validates a field added to a dynamic form
        return true;
    }

    function validateEntry() {
        # Validates a user-input into an instance of this field on a dynamic
        # form
        if (!is_array($this->_errors)) {
            $this->_errors = array();
            foreach ($this->getFields() as $field)
                if (!$field->isValid())
                    $this->_errors =
                        array_merge($this->_errors, $field->errors());
        }
        return $this->_errors;
    }

    function errors() {
        return $this->_errors;
    }

    function getClean() {
        if (!$this->_clean) {
            $this->_clean = array();
            foreach ($this->getFields() as $field)
                # Need to hash be able to get field in order to ask the
                # field to convert the value to the database format when
                # saving to database
                $this->_clean[$field] = $field->getClean();
        }
        return $this->_clean;
    }

    function instanciate() {
        return DynamicFormEntry::create(array('form_id'=>$this->get('id')));
    }

    function all($sort='title') {
        return parent::all(get_class(), DYNAMIC_FORM_TABLE, $sort);
    }

    function count($where=false) {
        return parent::count(get_class(), DYNAMIC_FORM_TABLE, $where);
    }

    function find($where, $sort='title') {
        return parent::find(get_class(), DYNAMIC_FORM_TABLE, $where,
            $sort);
    }

    function lookup($id) {
        return ($id && is_numeric($id)
            && ($r=parent::lookup(get_class(), DYNAMIC_FORM_TABLE, array('id'=>$id)))
            && $r->get('id')==$id)?$r:null;
    }

    function save() {
        if (count($this->dirty))
            $this->set('updated', new SqlFunction('NOW'));
        return parent::save(DYNAMIC_FORM_TABLE, 'id', true);
    }

    function create($ht=false) {
        $inst = parent::create(get_class(), $ht);
        $inst->set('created', new SqlFunction('NOW'));
        return $inst;
    }
}

class DynamicFormField extends VerySimpleModel {

    /**
     * Validates and cleans inputs from POST request
     */
    function getClean() {
        $value = $this->getWidget()->getValue();
        $value = $this->parse($value);
        $this->validateEntry($value);
        return $value;
    }

    function errors() {
        if (!$this->_errors) return array();
        else return $this->_errors;
    }

    function isValid() {
        return true;
    }

    function validate() {
        return true;
    }

    function isValidEntry() {
        $this->validateEntry();
        return count($this->_errors) == 0;
    }

    function validateEntry($value) {
        # Validates a user-input into an instance of this field on a dynamic
        # form
        if (!is_array($this->_errors)) $this->_errors = array();
        $value = $this->to_php($value);
        # Returns array of errors
        if ($this->get('required'))
            if (!$value)
                $this->_errors[] = $this->getLabel() . ' is a required field';
    }

    function parse($value) {
        # From (validated) user input to PHP
        return $value;
    }

    function to_php($value) {
        # From database value to PHP
        return $value;
    }
    
    function to_database($value) {
        # From PHP to database value
        return $value;
    }

    function getLabel() { return $this->get('label'); }

    function getImpl() {
        $types = get_dynamic_field_types();
        $clazz = $types[$this->get('type')][1];
        return new $clazz($this->ht);
    }

    function getAnswer() { return $this->answer; }

    function getFormName() {
        return '_form-field-id-'.$this->get('id');
    }

    function render() {
        $this->getWidget()->render();
    }

    function all($sort='sort') {
        return parent::all(get_class(), DYNAMIC_FORM_FIELD_TABLE, $sort);
    }

    function find($where, $sort='sort') {
        return parent::find(get_class(), DYNAMIC_FORM_FIELD_TABLE, $where,
            $sort);
    }

    function lookup($id) {
        return parent::lookup(get_class(), DYNAMIC_FORM_FIELD_TABLE,
            array('id'=>$id));
    }

    function delete() {
        return parent::delete(DYNAMIC_FORM_FIELD_TABLE, 'id');
    }

    function save() {
        if (count($this->dirty))
            $this->set('updated', new SqlFunction('NOW'));
        return parent::save(DYNAMIC_FORM_FIELD_TABLE, 'id');
    }

    function create($ht=false) {
        $inst = parent::create(get_class(), $ht);
        $inst->set('created', new SqlFunction('NOW'));
        return $inst;
    }
}

/**
 * Represents an entry to a dynamic form. Used to render the completed form
 * in reference to the attached ticket, etc.
 */
class DynamicFormEntry extends VerySimpleModel {

    function getAnswers() {
        if (!$this->_values) {
            $this->_values = DynamicFormEntryAnswer::find(
                array('entry_id'=>$this->get('id')));
            foreach ($this->_values as $v)
                $v->entry = $this;
        }
        return $this->_values;
    }

    function errors() {
        return $this->_errors;
    }

    function getTitle() { return $this->getForm()->getTitle(); }

    function getForm() {
        if (!$this->_form)
            $this->_form = DynamicForm::lookup($this->get('form_id'));
        return $this->_form;
    }

    function getFields() {
        if (!$this->_fields) {
            $this->_fields = array();
            foreach ($this->getAnswers() as $a) {
                $this->_fields[] = $a->getField();
            }
        }
        return $this->_fields;
    }

    function isValid() {
        return $this->validate();
    }
    
    function validate() {
        if (!is_array($this->_errors)) {
            $this->_errors = array();
            $this->getClean();
            foreach ($this->getFields() as $field)
                if ($field->errors())
                    $this->_errors[$field->get('id')] = $field->errors();
        }
        return !$this->_errors;
    }

    function getClean() {
        if (!$this->_clean) {
            $this->_clean = array();
            foreach ($this->getFields() as $field)
                $this->_clean[$field->get('id')] = $field->getClean();
        }
        return $this->_clean;
    }

    function forTicket($ticket_id) {
        return self::find(array('ticket_id'=>$ticket_id));
    }

    function find($where, $sort='sort') {
        return parent::find(get_class(), DYNAMIC_FORM_ENTRY_TABLE, $where,
            $sort);
    }

    function save() {
        $this->set('updated', new SqlFunction('NOW'));
        parent::save(DYNAMIC_FORM_ENTRY_TABLE, 'id');
        # XXX: Handle field additions to form (?)
        foreach ($this->getAnswers() as $a) {
            $a->set('value', $a->getField()->getClean());
            $a->set('entry_id', $this->get('id'));
            $a->save();
        }
        $this->_values = array();
    }

    function create($ht=false) {
        $inst = parent::create(get_class(), $ht);
        $inst->set('created', new SqlFunction('NOW'));
        foreach ($inst->getForm()->getFields() as $f) {
            $a = DynamicFormEntryAnswer::create(
                array('field_id'=>$f->get('id')));
            $a->field = $f;
            $inst->_values[] = $a;
        }
        return $inst;
    }
}

class DynamicFormEntryAnswer extends VerySimpleModel {

    function getEntry() {
        return $this->entry;
    }

    function getForm() {
        if (!$this->form)
            $this->form = $this->getEntry()->getForm();
        return $this->form;
    }

    function getField() {
        if (!$this->field) {
            $this->field = DynamicFormField::lookup($this->get('field_id'))->getImpl();
            $this->field->answer = $this;
        }
        return $this->field;
    }

    function getJoins() {
        return array(
            'field' => array('field_id', DynamicFormField, DYNAMIC_FORM_FIELD_TABLE, 'id')
        );
    }

    function find($where, $sort='field__sort') {
        return parent::find(get_class(), DYNAMIC_FORM_ANSWER_TABLE, $where,
            $sort);
    }

    function save() {
        return parent::save(DYNAMIC_FORM_ANSWER_TABLE, array('entry_id',
            'field_id'));
    }

    function create($ht=false) {
        return parent::create(get_class(), $ht);
    }
}

class DynamicFormGroup extends VerySimpleModel {

    function getForms() {
        if (!$this->_forms)
            $this->_forms = DynamicFormGroupForms::find(
                    array('group_id'=>$this->get('id')));
        return $this->_forms;
    }

    function hasField($name) {
        foreach ($this->getForms() as $form) 
            foreach ($form->getForm()->getFields() as $f)
                if ($f->get('name') == $name)
                    return true;
    }

    function all($sort='title') {
        return parent::all(get_class(), DYNAMIC_FORM_GROUP_TABLE, $sort);
    }

    function find($where, $sort='sort') {
        return parent::find(get_class(), DYNAMIC_FORM_GROUP_TABLE, $where,
            $sort);
    }

    function lookup($id) {
        return parent::lookup(get_class(), DYNAMIC_FORM_GROUP_TABLE,
            array('id'=>$id));
    }

    function count($where=false) {
        return parent::count(get_class(), DYNAMIC_FORM_GROUP_TABLE, $where);
    }

    function save() {
        if (count($this->dirty))
            $this->set('updated', new SqlFunction('NOW'));
        return parent::save(DYNAMIC_FORM_GROUP_TABLE, array('id'));
    }

    function create($ht=false) {
        $inst = parent::create(get_class(), $ht);
        $inst->set('created', new SqlFunction('NOW'));
        return $inst;
    }

    function delete() {
        return parent::delete(DYNAMIC_FORM_GROUP_TABLE,
                array('id'));
    }
}

class DynamicFormGroupForms extends VerySimpleModel {
    function find($where, $sort='sort') {
        return parent::find(get_class(), DYNAMIC_FORM_GROUP_FORM_TABLE, $where,
            $sort);
    }

    function getForm() {
        if (!$this->_form)
            $this->_form = DynamicForm::lookup($this->get('form_id'));
        return $this->_form;
    }

    function getTitle() {
        $title = $this->get('title');
        if ($title)
            return $title;
        else
            return $this->getForm()->get('title');
    }

    function delete() {
        return parent::delete(DYNAMIC_FORM_GROUP_FORM_TABLE,
                array('id'));
    }

    function create($ht=false) {
        return parent::create(get_class(), $ht);
    }

    function save() {
        return parent::save(DYNAMIC_FORM_GROUP_FORM_TABLE, array('id'));
    }
}

class DynamicList extends VerySimpleModel {

    function getListOrderBy() {
        switch ($this->ht['sort_mode']) {
            case 'Alpha':   return 'value';
            case '-Alpha':  return '-value';
            case 'SortCol': return 'sort';
        }
    }

    function getPluralName() {
        if ($name = $this->get('plural_name'))
            return $name;
        else
            return $this->get('name') . 's';
    }

    function getItems() {
        if (!$this->items) {
            $this->items = DynamicListItem::find(array('list_id'=>$this->id),
                $this->getListOrderBy());
        }
        return $this->items;
    }

    function all($sort='name') {
        return parent::all(get_class(), DYNAMIC_LIST_TABLE, $sort);
    }

    function find($where, $sort='name') {
        return parent::find(get_class(), DYNAMIC_LIST_TABLE, $where,
            $sort);
    }

    function lookup($id) {
        return parent::lookup(get_class(), DYNAMIC_LIST_TABLE,
            array('id'=>$id));
    }

    function save() {
        return parent::save(DYNAMIC_LIST_TABLE, 'id');
    }

    function create($ht=false) {
        return parent::create(get_class(), $ht);
    }
}

class DynamicListItem extends VerySimpleModel {
    function find($where, $sort=false) {
        return parent::find(get_class(), DYNAMIC_LIST_ITEM_TABLE, $where,
            $sort);
    }

    function save() {
        return parent::save(DYNAMIC_LIST_ITEM_TABLE, 'id');
    }

    function create($ht=false) {
        return parent::create(get_class(), $ht);
    }
}

class TextboxField extends DynamicFormField {
    function getWidget() {
        return new TextboxWidget($this);
    }
}

class TextareaField extends DynamicFormField {
    function getWidget() {
        return new TextareaWidget($this);
    }
}

class EmailField extends TextboxField {
    function validateEntry($value) {
        parent::validateEntry($value);
        # Run validator against $this->value for email type
        if (!Validator::is_email($value))
            $this->_errors[] = "Enter a valid email address";
    }
}

class PhoneField extends DynamicFormField {
    function validateEntry($value) {
        parent::validateEntry($value);
        # Run validator against $this->value for email type
        if ($value && !Validator::is_phone($value))
            $this->_errors[] = "Enter a valid phone number";
    }
    function getWidget() {
        return new PhoneNumberWidget($this);
    }
}

function get_dynamic_field_types() {
    static $types = false;
    if (!$types) {
        $types = array(
            'text'  => array('Short Answer', TextboxField),
            'memo' => array('Long Answer', TextareaField),
            'email' => array('Email Address', EmailField),
            'phone' => array('Phone Number', PhoneField)
        );
        foreach (DynamicList::all() as $list) {
            $types['list-'+$list->get('id')] = array('Selection: ' . $list->getPluralName(),
                SelectionField, $list->get('id'));
        }
    }
    return $types;
}

class SelectionField extends DynamicFormField {
    function SelectionField($list_id) {
        $this->list = DynamicList::lookup($list_id);
    }

    function getWidget() {
        return new SelectionWidget($this->list->getItems());
    }

    function parse($id) {
        return $this->to_php($id);
    }

    function to_php($id) {
        foreach ($this->list->getItems() as $i)
            if ($i->id == $id)
                return $i;
        return null;
    }

    function to_database($item) {
        if ($item && $item->id)
            return $item->id;
        return null;
    }
}

class Widget {
    function Widget($field) {
        $this->field = $field;
        $this->name = '_form-field-id-'.$field->get('id');
        if (isset($_POST[$this->name]))
            $this->value = $_POST[$this->name];
        elseif ($a = $field->getAnswer())
            $this->value = $a->get('value');
    }
    function getValue() {
        return $this->value;
    }
}   

class TextboxWidget extends Widget {
    function render() {
        ?>
        <input type="text" id="<?php echo $this->name; ?>"
            name="<?php echo $this->name; ?>" value="<?php echo $this->value; ?>"/>
        <?php
    }
}

class TextareaWidget extends Widget {
    function render() {
        ?>
        <textarea rows="4" cols="40" name="<?php echo $this->name; ?>"><?php
            echo $this->value; ?></textarea>
        <?php
    }
}

class PhoneNumberWidget extends Widget {
    function render() {
        list($phone, $ext) = explode("X", $this->value);
        ?>
        <input type="test" name="<?php echo $this->name; ?>" value="<?php
            echo $phone; ?>"/> Ext: <input type="text" name="<?php
            echo $this->name; ?>-ext" value="<?php echo $ext; ?>" size="5"/>
        <?php
    }

    function getValue() {
        $ext = $_POST["{$this->name}-ext"];
        if ($ext) $ext = 'X'.$ext;
        return $this->value . $ext;
    }
}

class SelectionWidget extends Widget {
    function SelectionWidget($field, $items) {
        $this->items = $items;
    }

    function render() {
        ?>
        <select name="field-id-<?php echo $this->field->get('id'); ?>">
            <?php foreach ($this->items as $i) { ?>
            <option value="<?php echo $i->get('id'); ?>"><?php  
                echo $i->get('value') ?></option>
            <?php } ?>
        </select>
        <?php
    }
}

?>
