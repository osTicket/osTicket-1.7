

/**
 * @version v1.7.1
 * @signature 4E5A4F09D260C778AEA695BEB43375F2
 *
 *  Change email password field to varchar 255  ASCII
 *
 *
 */

ALTER TABLE  `%TABLE_PREFIX%email`
    CHANGE  `userpass`  `userpass` VARCHAR( 255 ) CHARACTER SET ASCII COLLATE ascii_general_ci NOT NULL;

-- Finished with patch
UPDATE `%TABLE_PREFIX%config`
    SET `value` = '4E5A4F09D260C778AEA695BEB43375F2'
    WHERE `key` = 'schema_signature' AND `namespace` = 'core';
