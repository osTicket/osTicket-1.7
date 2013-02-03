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
class DynamicFormSection extends VerySimpleModel {

    function __construct($row) {
        parent::__construct($row);
        $this->id = $row['id'];
        $this->_errors = false;
    }
    
    function getFields() {
        if (!$this->_fields) {
            $this->_fields = array();
            foreach (DynamicFormField::find(array('section_id'=>$this->id)) as $f)
                $this->_fields[] = $f->getImpl();
        }
        return $this->_fields;
    }
    function getTitle() { return $this->get('title'); }
    function getInstructions() { return $this->get('instructions'); }

    function isValid() {
        return !$this->_errors;
    }

    function errors() {
        return $this->_errors;
    }

    function instanciate() {
        return DynamicFormEntry::create(array('section_id'=>$this->get('id')));
    }

    function all($sort='title', $limit=false, $offset=false) {
        return parent::all(get_class(), DYNAMIC_FORM_SEC_TABLE, $sort);
    }

    function count($where=false) {
        return parent::count(get_class(), DYNAMIC_FORM_SEC_TABLE, $where);
    }

    function find($where, $sort='title', $limit=false, $offset=false) {
        return parent::find(get_class(), DYNAMIC_FORM_SEC_TABLE, $where,
            $sort, $limit, $offset);
    }

    function lookup($id) {
        return ($id && is_numeric($id)
            && ($r=parent::lookup(get_class(), DYNAMIC_FORM_SEC_TABLE, array('id'=>$id)))
            && $r->get('id')==$id)?$r:null;
    }

    function save() {
        if (count($this->dirty))
            $this->set('updated', new SqlFunction('NOW'));
        return parent::save(DYNAMIC_FORM_SEC_TABLE, 'id', true);
    }

    function create($ht=false) {
        $inst = parent::create(get_class(), $ht);
        $inst->set('created', new SqlFunction('NOW'));
        return $inst;
    }
}

require_once(INCLUDE_DIR . "class.json.php");

class DynamicFormField extends VerySimpleModel {

    /**
     * getClean
     *
     * Validates and cleans inputs from POST request. This is performed on a
     * field instance, after a DynamicFormSet / DynamicFormSection is
     * submitted via POST, in order to kick off parsing and validation of
     * user-entered data.
     */
    function getClean() {
        $value = $this->getWidget()->value;
        $value = $this->parse($value);
        if (!$this->_validated)
            $this->validateEntry($value);
        return $value;
    }

    function errors() {
        if (!$this->_errors) return array();
        else return $this->_errors;
    }

    /**
     * validate
     *
     * Validates the contents of $this->ht before the model should be
     * committed to the database. This is the validation for the field
     * template -- edited in the admin panel for a form section.
     */
    function isValid() {
        if (!is_numeric($this->get('sort')))
            $this->_errors['sort'] = 'Enter a number';
        if (strpos($this->get('name'), ' ') !== false)
            $this->_errors['name'] = 'Name cannot contain spaces';
        return count($this->errors()) === 0;
    }

    function isValidEntry() {
        $this->validateEntry();
        return count($this->_errors) == 0;
    }

    /**
     * validateEntry
     *
     * Validates user entry on an instance of the field on a dynamic form.
     * This is called when an instance of this field (like a TextboxField)
     * receives data from the user and that value should be validated.
     *
     * Parameters:
     * $value - (string) input from the user
     */
    function validateEntry($value) {
        # Validates a user-input into an instance of this field on a dynamic
        # form
        $this->_validated = true;

        if (!is_array($this->_errors)) $this->_errors = array();

        if ($this->get('required'))
            if (!$value)
                $this->_errors[] = $this->getLabel() . ' is a required field';
    }

    /**
     * parse
     *
     * Used to transform user-submitted data to a PHP value. This value is
     * not yet considered valid. The ::validateEntry() method will be called
     * on the value to determine if the entry is valid. Therefore, if the
     * data is clearly invalid, return something like NULL that can easily
     * be deemed invalid in ::validateEntry(), however, can still produce a
     * useful error message indicating what is wrong with the input.
     */
    function parse($value) {
        return $value;
    }

    /**
     * to_php
     *
     * Transforms the data from the value stored in the database to a PHP
     * value. The ::to_database() method is used to produce the database
     * valse, so this method is the compliment to ::to_database().
     *
     * Parameters:
     * $value - (string or null) database representation of the field's
     *      content
     */
    function to_php($value) {
        return $value;
    }
    
    /**
     * to_database
     *
     * Determines the value to be stored in the database. The database
     * backend for all fields is a text field, so this method should return
     * a text value or NULL to represent the value of the field. The
     * ::to_php() method will convert this value back to PHP.
     *
     * Paremeters:
     * $value - PHP value of the field's content
     */
    function to_database($value) {
        return $value;
    }

    /**
     * toString
     *
     * Converts the PHP value created in ::parse() or ::to_php() to a
     * pretty-printed value to show to the user. This is especially useful
     * for something like dates which are stored considerably different in
     * the database from their respective human-friendly versions.
     * Furthermore, this method allows for internationalization and
     * localization.
     *
     * Parametes:
     * $value - PHP value of the field's content
     */
    function toString($value) {
        return $value;
    }

    function getLabel() { return $this->get('label'); }

    function getImpl() {
        $type = get_dynamic_field_types();
        $type = $type[$this->get('type')];
        $clazz = $type[1];
        return new $clazz($this->ht);
    }

    function getAnswer() { return $this->answer; }

    function getFormName() {
        return '_form-field-id-'.$this->get('id');
    }

    function render() {
        $this->getWidget()->render();
    }

    function getConfigurationOptions() {
        return array();
    }

    function getConfigurationForm() {
        if (!$this->_cform) {
            $types = get_dynamic_field_types();
            $clazz = $types[$this->get('type')][1];
            $T = new $clazz();
            $this->_cform = $T->getConfigurationOptions();
        }
        return $this->_cform;
    }

    function setConfiguration($errors) {
        $errors = $config = array();
        foreach ($this->getConfigurationForm() as $name=>$field) {
            $config[$name] = $field->getClean();
            $errors = array_merge($errors, $field->errors());
        }
        if (count($errors) === 0)
            $this->set('configuration', JsonDataEncoder::encode($config));
        $this->set('hint', $_POST['hint']);
        return count($errors) === 0;
    }
    
    /**
     * getConfiguration
     *
     * Loads configuration information from database into hashtable format.
     * Also, the defaults from ::getConfigurationOptions() are integrated
     * into the database-backed options, so that if options have not yet
     * been set or a new option has been added and not saved for this field,
     * the default value will be reflected in the returned configuration.
     */
    function getConfiguration() {
        if (!$this->_config) {
            $this->_config = $this->get('configuration');
            if (is_string($this->_config))
                $this->_config = JsonDataParser::parse($this->_config);
            elseif (!$this->_config)
                $this->_config = array();
            foreach ($this->getConfigurationOptions() as $name=>$field)
                if (!isset($this->_config[$name]))
                    $this->_config[$name] = $field->get('default');
        }
        return $this->_config;
    }

    function isConfigurable() {
        return true;
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
        // Don't really delete form fields as that will screw up the data
        // model. Instead, just drop the association with the form section
        // which will give the appearance of deletion. Not deleting means
        // that the field will continue to exist on form entries it may
        // already have answers on, but since it isn't associated with the
        // form section, it won't be available for new form submittals.
        $this->set('section_id', 0);
        $this->save();
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

    function getAnswer($name) {
        foreach ($this->getAnswers() as $ans)
            if ($ans->getField()->get('name') == $name)
                return $ans->getValue();
        return null;
    }

    function errors() {
        return $this->_errors;
    }

    function getTitle() { return $this->getForm()->getTitle(); }
    function getInstructions() { return $this->getForm()->getInstructions(); }

    function getForm() {
        if (!$this->_form)
            $this->_form = DynamicFormSection::lookup($this->get('section_id'));
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

    /**
     * addMissingFields
     *
     * Adds fields that have been added to the linked form section (field
     * set) since this entry was originally created. If fields are added to
     * the form section, the method will automatically add the fields and
     * null answers to the entry.
     */
    function addMissingFields() {
        foreach ($this->getForm()->getFields() as $field) {
            $found = false;
            foreach ($this->getAnswers() as $answer) {
                if ($answer->get('field_id') == $field->get('id')) {
                    $found = true; break;
                }
            }
            if (!$found) {
                # Section ID is auto set in the ::save method
                $a = DynamicFormEntryAnswer::create(
                    array('field_id'=>$field->get('id')));
                $a->field = $field;
                // Add to list of answers
                $this->_values[] = $a;
            }
        }
    }

    function find($where, $sort='sort') {
        return parent::find(get_class(), DYNAMIC_FORM_ENTRY_TABLE, $where,
            $sort);
    }

    function save() {
        if (count($this->dirty))
            $this->set('updated', new SqlFunction('NOW'));
        parent::save(DYNAMIC_FORM_ENTRY_TABLE, 'id');
        foreach ($this->getAnswers() as $a) {
            $a->set('value', $a->getField()->to_database($a->getField()->getClean()));
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

    function getValue() {
        if (!$this->_value)
            $this->_value = $this->getField()->to_php($this->get('value'));
        return $this->_value;
    }

    function toString() {
        return $this->getField()->toString($this->getValue());
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

class DynamicFormset extends VerySimpleModel {

    function getForms() {
        if (!$this->_forms)
            $this->_forms = DynamicFormsetSections::find(
                    array('formset_id'=>$this->get('id')));
        return $this->_forms;
    }

    function hasField($name) {
        foreach ($this->getForms() as $form) 
            foreach ($form->getForm()->getFields() as $f)
                if ($f->get('name') == $name)
                    return true;
    }

    function errors() {
        return $this->_errors;
    }

    function isValid() {
        if (!$this->_errors) $this->_errors = array();
        return count($this->_errors) === 0;
    }

    function all($sort='title', $limit=false, $offset=false) {
        return parent::all(get_class(), DYNAMIC_FORMSET_TABLE, $sort,
                $limit, $offset);
    }

    function find($where, $sort='sort') {
        return parent::find(get_class(), DYNAMIC_FORMSET_TABLE, $where,
            $sort);
    }

    function lookup($id) {
        return parent::lookup(get_class(), DYNAMIC_FORMSET_TABLE,
            array('id'=>$id));
    }

    function count($where=false) {
        return parent::count(get_class(), DYNAMIC_FORMSET_TABLE, $where);
    }

    function save() {
        if (count($this->dirty))
            $this->set('updated', new SqlFunction('NOW'));
        return parent::save(DYNAMIC_FORMSET_TABLE, array('id'));
    }

    function create($ht=false) {
        $inst = parent::create(get_class(), $ht);
        $inst->set('created', new SqlFunction('NOW'));
        return $inst;
    }

    function delete() {
        return parent::delete(DYNAMIC_FORMSET_TABLE,
                array('id'));
    }
}

class DynamicFormsetSections extends VerySimpleModel {
    function find($where, $sort='sort') {
        return parent::find(get_class(), DYNAMIC_FORMSET_SEC_TABLE, $where,
            $sort);
    }

    function getForm() {
        if (!$this->_section)
            $this->_section = DynamicFormSection::lookup($this->get('section_id'));
        return $this->_section;
    }

    function getTitle() {
        $title = $this->get('title');
        if ($title)
            return $title;
        else
            return $this->getForm()->get('title');
    }

    function errors() {
        return $this->_errors;
    }

    function isValid() {
        if (!$this->_errors) $this->_errors = array();
        if (!is_numeric($this->get('sort')))
            $this->_errors['sort'] = 'Enter a number';
        return count($this->errors()) === 0;
    }

    function delete() {
        return parent::delete(DYNAMIC_FORMSET_SEC_TABLE,
                array('id'));
    }

    function create($ht=false) {
        return parent::create(get_class(), $ht);
    }

    function save() {
        return parent::save(DYNAMIC_FORMSET_SEC_TABLE, array('id'));
    }
}

class DynamicList extends VerySimpleModel {

    function getSortModes() {
        return array(
            'Alpha'     => 'Alphabetical',
            '-Alpha'    => 'Alphabetical (Reversed)',
            'SortCol'   => 'By Sort column'
        );
    }

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

    function getItems($limit=false, $offset=false) {
        if (!$this->_items) {
            $this->_items = DynamicListItem::find(array('list_id'=>$this->get('id')),
                $this->getListOrderBy(), $limit, $false);
        }
        return $this->_items;
    }

    function getItemCount() {
        return DynamicListItem::count(array('list_id'=>$this->get('id')));
    }

    function all($sort='name', $limit=false, $offset=false) {
        return parent::all(get_class(), DYNAMIC_LIST_TABLE, $sort, $limit,
                $offset);
    }

    function count($where=false) {
        return parent::count(get_class(), DYNAMIC_LIST_TABLE, $where);
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
        if (count($this->dirty))
            $this->set('updated', new SqlFunction('NOW'));
        return parent::save(DYNAMIC_LIST_TABLE, 'id');
    }

    function create($ht=false) {
        $inst = parent::create(get_class(), $ht);
        $inst->set('created', new SqlFunction('NOW'));
        return $inst;
    }
}

class DynamicListItem extends VerySimpleModel {
    function toString() {
        return $this->get('value');
    }
    
    function lookup($id) {
        return parent::lookup(get_class(), DYNAMIC_LIST_ITEM_TABLE,
            array('id'=>$id));
    }

    function find($where, $sort=false, $limit=false, $offset=false) {
        return parent::find(get_class(), DYNAMIC_LIST_ITEM_TABLE, $where,
            $sort, $limit, $offset);
    }

    function count($where=false) {
        return parent::count(get_class(), DYNAMIC_LIST_ITEM_TABLE, $where);
    }

    function save() {
        return parent::save(DYNAMIC_LIST_ITEM_TABLE, 'id');
    }

    function create($ht=false) {
        return parent::create(get_class(), $ht);
    }

    function delete() {
        # Don't really delete, just unset the list_id to un-associate it with
        # the list
        $this->set('list_id', null);
        return $this->save();
    }
}

class TextboxField extends DynamicFormField {
    function getWidget() {
        return new TextboxWidget($this);
    }

    function getConfigurationOptions() {
        return array(
            'size'  =>  new TextboxField(array(
                'id'=>1, 'label'=>'Size', 'required'=>false, 'default'=>16, 
                    'validator' => 'number')),
            'length' => new TextboxField(array(
                'id'=>2, 'label'=>'Max Length', 'required'=>false, 'default'=>30,
                    'validator' => 'number')),
            'validator' => new ChoiceField(array(
                'id'=>3, 'label'=>'Validator', 'required'=>false, 'default'=>'',
                'choices' => array('phone'=>'Phone Number','email'=>'Email Address',
                    'ip'=>'IP Address', 'number'=>'Number', ''=>'None'))),
        );
    }

    function validateEntry($value) {
        parent::validateEntry($value);
        $validators = array(
            '' =>       null,
            'email' =>  array(array('Validator', 'is_email'),
                'Enter a valid email address'),
            'phone' =>  array(array('Validator', 'is_phone'),
                'Enter a valid phone number'),
            'ip' =>     array(array('Validator', 'is_ip'),
                'Enter a valid IP address'),
            'number' => array('is_numeric', 'Enter a number')
        );
        // Support configuration forms, as well as GUI-based form fields
        $valid = $this->get('validator');
        if (!$valid) {
            $config = $this->getConfiguration();
            $valid = $config['validator'];
        }
        $func = $validators[$valid];
        if (is_array($func) && is_callable($func[0]))
            if (!call_user_func($func[0], $value))
                $this->_errors[] = $func[1];
    }
}

class TextareaField extends DynamicFormField {
    function getWidget() {
        return new TextareaWidget($this);
    }
    function getConfigurationOptions() {
        return array(
            'cols'  =>  new TextboxField(array(
                'id'=>1, 'label'=>'Width (chars)', 'required'=>true, 'default'=>40)),
            'rows'  =>  new TextboxField(array(
                'id'=>2, 'label'=>'Height (rows)', 'required'=>false, 'default'=>4)),
            'length' => new TextboxField(array(
                'id'=>3, 'label'=>'Max Length', 'required'=>false, 'default'=>30))
        );
    }
}

class PhoneField extends DynamicFormField {
    function validateEntry($value) {
        parent::validateEntry($value);
        # Run validator against $this->value for email type
        list($phone, $ext) = explode("X", $value, 2);
        if ($phone && !Validator::is_phone($phone))
            $this->_errors[] = "Enter a valid phone number";
        if ($ext) {
            if (!is_numeric($ext))
                $this->_errors[] = "Enter a valide phone extension";
            elseif (!$phone)
                $this->_errors[] = "Enter a phone number for the extension";
        }
    }
    function getWidget() {
        return new PhoneNumberWidget($this);
    }

    function toString($value) {
        list($phone, $ext) = explode("X", $value, 2);
        $phone=Format::phone($phone);
        if($ext)
            $phone.=" $ext";
    }
}

class BooleanField extends DynamicFormField {
    function getWidget() {
        return new CheckboxWidget($this);
    }

    function getConfigurationOptions() {
        return array(
            'desc' => new TextareaField(array(
                'id'=>1, 'label'=>'Description', 'required'=>false, 'default'=>'',
                'hint'=>'Text shown inline with the widget',
                'configuration'=>array('rows'=>2)))
        );
    }

    function to_database($value) {
        return ($value) ? '1' : '0';
    }

    function to_php($value) {
        return ((int)$value) ? true : false;
    }

    function toString($value) {
        return ($value) ? 'Yes' : 'No';
    }
}

class ChoiceField extends DynamicFormField {
    function getWidget() {
        return new ChoicesWidget($this);
    }

    function getConfigurationOptions() {
        return array(
            'choices'  =>  new TextareaField(array(
                'id'=>1, 'label'=>'Choices', 'required'=>false, 'default'=>'')),
        );
    }
}

class DatetimeField extends DynamicFormField {
    function getWidget() {
        return new DatetimePickerWidget($this);
    }

    function to_database($value) {
        // Store time in gmt time, unix epoch format
        return (string) $value;
    }

    function to_php($value) {
        if (!$value)
            return $value;
        else
            return (int) $value;
    }

    function parse($value) {
        if (!$value) return null;
        $config = $this->getConfiguration();
        return ($config['gmt']) ? Misc::db2gmtime($value) : strtotime($value);
    }

    function toString($value) {
        global $cfg;
        $config = $this->getConfiguration();
        $format = ($config['time'])
            ? $cfg->getDateTimeFormat() : $cfg->getDateFormat();
        if ($config['gmt'])
            // Return time local to user's timezone
            return Format::userdate($format, $value);
        else
            return Format::date($format, $value);
    }

    function getConfigurationOptions() {
        return array(
            'time' => new BooleanField(array(
                'id'=>1, 'label'=>'Time', 'required'=>false, 'default'=>false,
                'configuration'=>array(
                    'desc'=>'Show time selection with date picker'))),
            'gmt' => new BooleanField(array(
                'id'=>2, 'label'=>'Timezone Aware', 'required'=>false,
                'configuration'=>array(
                    'desc'=>"Show date/time relative to user's timezone"))),
            'min' => new DatetimeField(array(
                'id'=>3, 'label'=>'Earliest', 'required'=>false,
                'hint'=>'Earliest date selectable')),
            'max' => new DatetimeField(array(
                'id'=>4, 'label'=>'Latest', 'required'=>false,
                'default'=>null)),
            'future' => new BooleanField(array(
                'id'=>5, 'label'=>'Allow Future Dates', 'required'=>false,
                'default'=>true, 'configuration'=>array(
                    'desc'=>'Allow entries into the future'))),
        );
    }

    function validateEntry($value) {
        $config = $this->getConfiguration();
        parent::validateEntry($value);
        if (!$value) return;
        if ($config['min'] and $value < $config['min'])
            $this->_errors[] = 'Selected date is earlier than permitted';
        elseif ($config['max'] and $value > $config['max'])
            $this->_errors[] = 'Selected date is later than permitted';
        // strtotime returns -1 on error for PHP < 5.1.0 and false thereafter
        elseif ($value === -1 or $value === false)
            $this->_errors[] = 'Enter a valid date';
    }
}

function get_dynamic_field_types() {
    static $types = false;
    if (!$types) {
        $types = array(
            'text'  => array('Short Answer', TextboxField),
            'memo' => array('Long Answer', TextareaField),
            'datetime' => array('Date and Time', DatetimeField),
            'phone' => array('Phone Number', PhoneField),
            'bool' => array('Checkbox', BooleanField),
            'choices' => array('Choices', ChoiceField),
        );
        foreach (DynamicList::all() as $list) {
            $types['list-'.$list->get('id')] = array('Selection: ' . $list->getPluralName(),
                SelectionField, $list->get('id'));
        }
    }
    return $types;
}

class SelectionField extends DynamicFormField {
    function getList() {
        if (!$this->_list) {
            $list_id = explode('-', $this->get('type'));
            $list_id = $list_id[1];
            $this->_list = DynamicList::lookup($list_id);
        }
        return $this->_list;
    }

    function getWidget() {
        return new SelectionWidget($this);
    }

    function parse($id) {
        return $this->to_php($id);
    }

    function to_php($id) {
        $item = DynamicListItem::lookup($id);
        # Attempt item lookup by name too
        if (!$item) {
            $item = DynamicListItem::find(array('value'=>$id,
                        'list_id'=>$this->getList()->get('id')));
            $item = (count($item)) ? $item[0] : null;
        }
        return $item;
    }

    function to_database($item) {
        if ($item && $item->get('id'))
            return $item->get('id');
        return null;
    }

    function toString($item) {
        return ($item) ? $item->toString() : '';
    }

    function getConfigurationOptions() {
        return array(
            'typeahead' => new ChoiceField(array(
                'id'=>1, 'label'=>'Widget', 'required'=>false,
                'default'=>false,
                'choices'=>array(false=>'Drop Down', true=>'Typeahead'),
                'hint'=>'Typeahead will work better for large lists')),
        );
    }
}

class Widget {
    function Widget() {
        # Not called in PHP5
        call_user_func_array(array(&$this, '__construct'), func_get_args());
    }

    function __construct($field) {
        $this->field = $field;
        $this->name = $field->getFormName();
        if ($_SERVER['REQUEST_METHOD'] == 'POST')
            $this->value = $this->getValue();
        elseif (is_object($field->getAnswer()))
            $this->value = $field->getAnswer()->getValue();
        elseif ($field->value)
            $this->value = $field->value;
    }

    function getValue() {
        return $_POST[$this->name];
    }
}   

class TextboxWidget extends Widget {
    function render() {
        $config = $this->field->getConfiguration();
        if (isset($config['size']))
            $size = "size=\"{$config['size']}\"";
        if (isset($config['length']))
            $maxlength = "maxlength=\"{$config['length']}\"";
        ?>
        <span style="display:inline-block">
        <input type="text" id="<?php echo $this->name; ?>"
            <?php echo $size . " " . $maxlength; ?>
            name="<?php echo $this->name; ?>"
            value="<?php echo Format::htmlchars($this->value); ?>"/>
        </span>
        <?php
    }
}

class TextareaWidget extends Widget {
    function render() {
        $config = $this->field->getConfiguration();
        if (isset($config['rows']))
            $rows = "rows=\"{$config['rows']}\"";
        if (isset($config['cols']))
            $cols = "cols=\"{$config['cols']}\"";
        if (isset($config['length']))
            $maxlength = "maxlength=\"{$config['length']}\"";
        ?>
        <span style="display:inline-block">
        <textarea <?php echo $rows." ".$cols." ".$length; ?>
            name="<?php echo $this->name; ?>"><?php
                echo Format::htmlchars($this->value);
            ?></textarea>
        </span>
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
        return parent::getValue() . $ext;
    }
}

class ChoicesWidget extends Widget {
    function render() {
        $config = $this->field->getConfiguration();
        // Determine the value for the default (the one listed if nothing is
        // selected)
        $def_key = $this->field->get('default');
        $choices = $this->getChoices();
        $have_def = isset($choices[$def_key]);
        if (!$have_def)
            $def_val = 'Select '.$this->field->get('label');
        else
            $def_val = $choices[$def_key];
        ?> <span style="display:inline-block">
        <select name="<?php echo $this->name; ?>">
            <?php if (!$have_def) { ?>
            <option value="<?php echo $def_key; ?>">&mdash; <?php
                echo $def_val; ?> &mdash;</option>
            <?php }
            foreach ($choices as $key=>$name) {
                if (!$have_def && $key == $def_key)
                    continue; ?>
                <option value="<?php echo $key; ?>"
                <?php if ($this->value == $key) echo 'selected="selected"';
                ?>><?php echo $name; ?></option>
            <?php } ?>
        </select>
        </span>
        <?php
    }

    function getChoices() {
        if ($this->_choices === null) {
            // Allow choices to be set in this->ht (for configurationOptions)
            $this->_choices = $this->field->get('choices');
            if (!$this->_choices) {
                $this->_choices = array();
                $config = $this->field->getConfiguration();
                $choices = explode("\n", $config['choices']);
                foreach ($choices as $choice) { 
                    // Allow choices to be key: value
                    list($key, $val) = explode(':', $choice);
                    if ($val == null)
                        $val = $key;
                    $this->_choices[trim($key)] = trim($val);
                }
            }
        }
        return $this->_choices;
     }
}

class SelectionWidget extends ChoicesWidget {
    function render() {
        $config = $this->field->getConfiguration();
        if (!$config['typeahead'])
            return parent::render();

        $source = array(); $value = false;
        foreach ($this->field->getList()->getItems() as $i)
            $source[] = array(
                'info' => $i->get('value'),
                'value' => strtolower($i->get('value').' '.$i->get('extra')),
                'id' => $i->get('id'));
        if ($this->value && get_class($this->value) == 'DynamicListItem') {
            // Loaded from database
            $value = $this->value->get('id');
            $name = $this->value->get('value');
        } else {
            // Loaded from POST
            $value = $this->value;
            $name = DynamicListItem::lookup($this->value);
            $name = ($name) ? $name->get('value') : null;
        }
        ?>
        <span style="display:inline-block">
        <input type="hidden" name="<?php echo $this->name; ?>"
            value="<?php echo $value; ?>" />
        <input type="text" size="30" id="<?php echo $this->name; ?>"
            value="<?php echo $name; ?>" />
        <script type="text/javascript">
        $(function() {
            $('#<?php echo $this->name; ?>').typeahead({
                source: <?php echo JsonDataEncoder::encode($source); ?>,
                onselect: function(item) {
                    $('#<?php echo $this->name; ?>').val(item['info'])
                    $('input[name="<?php echo $this->name; ?>"]').val(item['id'])
                }
            });
        });
        </script>
        </span>
        <?php
    }

    function getChoices() {
        if (!$this->_choices) {
            $this->_choices = array();
            foreach ($this->field->getList()->getItems() as $i)
                $this->_choices[$i->get('id')] = $i->get('value');
        }
        return $this->_choices;
    }
}

class CheckboxWidget extends Widget {
    function __construct($field) {
        parent::__construct($field);
        $this->name = '_field-checkboxes';
    }

    function render() {
        $config = $this->field->getConfiguration();
        ?>
        <input type="checkbox" name="<?php echo $this->name; ?>[]" <?php
            if ($this->value) echo 'checked="checked"'; ?> value="<?php
            echo $this->field->get('id'); ?>"/>
        <?php
        if ($config['desc']) { ?>
            <em style="display:inline-block"><?php
                echo Format::htmlchars($config['desc']); ?></em>
        <?php }
    }

    function getValue() {
        if (count($_POST))
            return @in_array($this->field->get('id'), $_POST[$this->name]);
        return parent::getValue();
    }
}

class DatetimePickerWidget extends Widget {
    function render() {
        $config = $this->field->getConfiguration();
        if ($this->value) {
            $this->value = (is_int($this->value) ? $this->value :
                    strtotime($this->value)); 
            if ($config['gmt'])
                $this->value += 3600 *
                    $_SESSION['TZ_OFFSET']+($_SESSION['TZ_DST']?date('I',$time):0);

            list($hr, $min) = explode(':', date('H:i', $this->value));
            $this->value = date('m/d/Y', $this->value);
        }
        ?>
        <input type="text" name="<?php echo $this->name; ?>"
            value="<?php echo Format::htmlchars($this->value); ?>" size="12"
            autocomplete="off" />
        <script type="text/javascript">
            $(function() {
                $('input[name="<?php echo $this->name; ?>"]').datepicker({
                    <?php
                    if ($config['min'])
                        echo "minDate: new Date({$config['min']}000),";
                    if ($config['max'])
                        echo "maxDate: new Date({$config['max']}000),";
                    elseif (!$config['future'])
                        echo "maxDate: new Date().getTime(),";
                    ?>
                    numberOfMonths: 2,
                    showButtonPanel: true,
                    buttonImage: './images/cal.png',
                    showOn:'both'
                });
            });
        </script>
        <?php
        if ($config['time'])
            // TODO: Add time picker -- requires time picker or selection with
            //       Misc::timeDropdown
            echo '&nbsp;' . Misc::timeDropdown($hr, $min, $this->name . ':time');
    }

    /**
     * Function: getValue
     * Combines the datepicker date value and the time dropdown selected
     * time value into a single date and time string value.
     */
    function getValue() {
        $datetime = parent::getValue();
        if ($datetime && isset($_POST[$this->name . ':time']))
            $datetime .= ' ' . $_POST[$this->name . ':time'];
        return $datetime;
    }
}

?>
