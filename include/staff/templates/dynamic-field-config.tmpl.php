    <h3>Field Configuration &mdash; <?php echo $field->get('label') ?></h3>
    <a class="close" href="">&times;</a>
    <hr/>
    <form method="post" action="ajax.php/form/field-config/<?php
            echo $field->get('id'); ?>" onsubmit="javascript:
            var form = $(this);
            $.post(this.action, form.serialize(), function(data, status, xhr) {
                    if (!data.length) {
                        form.closest('.dialog').hide();
                        $('#overlay').hide();
                    } else {
                        form.closest('.dialog').empty().append(data);
                    }
            });
            return false;
            ">
        <?php
        echo csrf_token();
        $config = $field->getConfiguration();
        foreach ($field->getConfigurationForm() as $name=>$f) {
            if (isset($config[$name]))
                $f->value = $config[$name];
            else if ($f->get('default'))
                $f->value = $f->get('default');
            ?>
            <label for="<?php echo $f->getWidget()->name; ?>">
                <?php echo Format::htmlchars($f->get('label')); ?>:</label>
            <span style="display:inline-block">
            <?php
            $f->render();
            if ($f->get('required')) { ?>
                <font class="error">*</font>
            <?php 
            }
            if ($f->get('hint')) { ?>
                <br /><em style="color:gray;display:inline-block"><?php
                    echo Format::htmlchars($f->get('hint')); ?></em>
            <?php
            }
            ?>
            </span>
            <?php
            foreach ($f->errors() as $e) { ?>
                <br />
                <font class="error"><?php echo $e; ?></font>
            <?php } ?>
            <br/>
            <?php
        }
        ?>
        <label for="hint">Help Text:</label>
        <span style="display:inline-block">
        <textarea name="hint" rows="2" cols="40"><?php
            echo Format::htmlchars($field->get('hint')); ?></textarea>
            <br />
            <em style="color:gray;display:inline-block">
                Help text shown with the field</em>
        </span>
        <hr style="margin-top:3em"/>
        <p class="full-width">
            <span class="buttons" style="float:left">
                <input type="reset" value="Reset">
                <input type="button" value="Cancel" class="close">
            </span>
            <span class="buttons" style="float:right">
                <input type="submit" value="Save">
            </span>
         </p>
    </form>
    <div class="clear"></div>
