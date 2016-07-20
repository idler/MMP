<?php

class initController extends AbstractController
{
    public function runStrategy()
    {
        $fname = Helper::get('savedir').'/schema.php';
        if (!file_exists($fname)) {
            echo "File: {$fname} does not exist!\n";
            die;
        }
        $this->askForRewriteInformation();
        require_once $fname;
        $sc = new Schema();
        $sc->load(Helper::getDbObject());
    }

    public function askForRewriteInformation()
    {
        if (intval(Helper::get('forceyes'))) {
            return;
        }
        if (intval(Helper::get('noninteractive'))) {
            die;
        }
        $c = '';
        do {
            if ($c != '
'
            ) {
                echo 'Can I rewrite tables in database (all data will be lost) [y/n]? ';
                ob_flush();
            }
            $c = fread(STDIN, 1);
            if ($c === 'Y' or $c === 'y') {
                return;
            }
            if ($c === 'N' or $c === 'n') {
                echo '
Exit without changing shema
';
                die;
            }
        } while (true);
    }
}