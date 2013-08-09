<?php
/*********************************************************************
    AttachmentMigrater

    Attachment migration from file-based attachments in pre-1.7 to
    database-backed attachments in osTicket v1.7. This class provides the
    hardware to find and retrieve old attachments and move them into the new
    database scheme with the data in the actual database.

    Copyright (c)  2006-2013 osTicket
    http://www.osticket.com

    Released under the GNU General Public License WITHOUT ANY WARRANTY.
    See LICENSE.TXT for details.

    vim: expandtab sw=4 ts=4 sts=4:
**********************************************************************/
require_once INCLUDE_DIR.'class.migrater.php';
require_once(INCLUDE_DIR.'class.file.php');

class AttachmentMigrater extends MigrationTask {
    var $description = "Attachment migration from disk to database";

    var $queue;
    var $skipList;
    var $errorList = array();

    function sleep() {
        return array('queue'=>$this->queue, 'skipList'=>$this->skipList);
    }
    function wakeup($stuff) {
        $this->queue = $stuff['queue'];
        $this->skipList = $stuff['skipList'];
    }

    function run($max_time) {
        $this->do_batch($max_time * 0.9, 100);
    }

    function isFinished() {
        return $this->getQueueLength() == 0;
    }

    /**
     * Process the migration for a unit of time. This will be used to
     * overcome the execution time restriction of PHP. This instance can be
     * stashed in a session and have this method called periodically to
     * process another batch of attachments
     *
     * Returns:
     * Number of pending attachments to migrate.
     */
    function do_batch($time=30, $max=0) {

        if(!$this->queueAttachments($max) || !$this->getQueueLength())
            return 0;

        $this->setStatus("{$this->getQueueLength()} attachments remaining");

        $count = 0;
        $start = Misc::micro_time();
        while ($this->getQueueLength() && (Misc::micro_time()-$start) < $time)
            if($this->next() && $max && ++$count>=$max)
                break;

        return $this->queueAttachments($max);

    }

    function getSkipList() {
        return $this->skipList;
    }

    function enqueue($fileinfo) {
        $this->queue[] = $fileinfo;
    }

    function getQueue() {
        return $this->queue;
    }

    function getQueueLength() { return count($this->queue); }
    /**
     * Processes the next item on the work queue. Emits a JSON messages to
     * indicate current progress.
     *
     * Returns:
     * TRUE/NULL if the migration was successful
     */
    function next() {
        # Fetch next item -- use the last item so the array indices don't
        # need to be recalculated for every shift() operation.
        $info = array_pop($this->queue);
        # Attach file to the ticket
        if (!($info['data'] = @file_get_contents($info['path']))) {
            # Continue with next file
            return $this->skip($info['attachId'],
                sprintf('%s: Cannot read file contents', $info['path']));
        }
        # Get the mime/type of each file
        # XXX: Use finfo_buffer for PHP 5.3+
        if(function_exists('mime_content_type')) {
            //XXX: function depreciated in newer versions of PHP!!!!!
            $info['type'] = mime_content_type($info['path']);
        } elseif (function_exists('finfo_file')) { // PHP 5.3.0+
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $info['type'] = finfo_file($finfo, $info['path']);
        }
        # TODO: Add extension-based mime-type lookup

        if (!($fileId = AttachmentFile::save($info))) {
            return $this->skip($info['attachId'],
                sprintf('%s: Unable to migrate attachment', $info['path']));
        }
        # Update the ATTACHMENT_TABLE record to set file_id
        db_query('update '.TICKET_ATTACHMENT_TABLE
                .' set file_id='.db_input($fileId)
                .' where attach_id='.db_input($info['attachId']));
        # Remove disk image of the file. If this fails, the migration for
        # this file would not be retried, because the file_id in the
        # TICKET_ATTACHMENT_TABLE has a nonzero value now
        if (!@unlink($info['path'])) //XXX: what should we do on failure?
            $this->error(
                sprintf('%s: Unable to remove file from disk',
                $info['path']));
        # TODO: Log an internal note to the ticket?
        return true;
    }
    /**
     * From (class Ticket::fixAttachments), used to detect the locations of
     * attachment files
     */
    /* static */ function queueAttachments($limit=0){
        global $cfg, $ost;

        # Since the queue is persistent - we want to make sure we get to empty
        # before we find more attachments.
        if(($qc=$this->getQueueLength()))
            return $qc;

        $sql='SELECT attach_id, file_name, file_key, Ti.created'
            .' FROM '.TICKET_ATTACHMENT_TABLE.' TA'
            .' INNER JOIN '.TICKET_TABLE.' Ti ON (Ti.ticket_id=TA.ticket_id)'
            .' WHERE NOT file_id ';

        if(($skipList=$this->getSkipList()))
            $sql.= ' AND attach_id NOT IN('.implode(',', db_input($skipList)).')';

        if($limit && is_numeric($limit))
            $sql.=' LIMIT '.$limit;

        //XXX: Do a hard fail or error querying the database?
        if(!($res=db_query($sql)))
            return $this->error('Unable to query DB for attached files to migrate!');

        $ost->logDebug("Attachment migration", 'Found '.db_num_rows($res).' attachments to migrate');
        if(!db_num_rows($res))
            return 0;  //Nothing else to do!!

        $dir=$cfg->getUploadDir();
        if(!$dir || !is_dir($dir)) //XXX: Abort the upgrade??? Attachments are obviously critical!
            return $this->error("Attachment directory [$dir] is invalid - aborting attachment migration");

        //Clear queue
        $this->queue = array();
        while (list($id,$name,$key,$created)=db_fetch_row($res)) {
            $month=date('my',strtotime($created));
            $info=array(
                'name'=>        $name,
                'attachId'=>    $id,
            );
            $filename15=sprintf("%s/%s_%s",rtrim($dir,'/'),$key,$name);
            $filename16=sprintf("%s/%s/%s_%s",rtrim($dir,'/'),$month,$key,$name); //new destination.
            if (file_exists($filename15)) {
                $info['path'] = $filename15;
            } elseif (file_exists($filename16)) {
                $info['path'] = $filename16;
            } else {
                # XXX Cannot find file for attachment
                $this->skip($id,
                        sprintf('%s: Unable to locate attachment file',
                            $name));
                # No need to further process this file
                continue;
            }
            # TODO: Get the size and mime/type of each file.
            #
            # NOTE: If filesize() fails and file_get_contents() doesn't,
            # then the AttachmentFile::save() method will automatically
            # estimate the filesize based on the length of the string data
            # received in $info['data'] -- ie. no need to do that here.
            #
            # NOTE: The size is done here because it should be quick to
            # lookup out of file inode already loaded. The mime/type may
            # take a while because it will require a second IO to read the
            # file data.  To ensure this will finish before the
            # max_execution_time, perform the type match in the ::next()
            # method since the entire file content will be read there
            # anyway.
            $info['size'] = @filesize($info['path']);
            # Coroutines would be nice ..
            $this->enqueue($info);
        }

        return $this->queueAttachments($limit);
    }

    function skip($attachId, $error) {

        $this->skipList[] = $attachId;

        return $this->error($error." (ID #$attachId)");
    }

    function error($what) {
        global $ost;

        $this->errors++;
        $this->errorList[] = $what;
        $ost->logDebug('Upgrader: Attachment Migrater', $what);
        # Assist in returning FALSE for inline returns with this method
        return false;
    }
    function getErrors() {
        return $this->errorList;
    }
}

return 'AttachmentMigrater';
?>
