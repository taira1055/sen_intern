<?php
/**
 *  Project.php
 *
 *  @author     Masaki Fujimoto <fujimoto@php.net>
 */
namespace Ethnam\Generator\Generator;

/**
 *  スケルトン生成クラス
 *
 *  @author     Masaki Fujimoto <fujimoto@php.net>
 *  @access     public
 */
class Project extends Base
{
    /**
     *  アプリケーションIDをチェックする
     *
     *  @param  string  $id     アプリケーションID
     */
    public static function checkAppId($id)
    {
        if (strcasecmp($id, 'ethna') === 0
            || strcasecmp($id, 'app') === 0) {
            throw new \InvalidArgumentException("Application Id [$id] is reserved\n");
        }

        //    アプリケーションIDはクラス名のprefixともなるため、
        //    数字で始まっていてはいけない
        //    @see http://www.php.net/manual/en/language.variables.php
        if (preg_match('/^[a-zA-Z][a-zA-Z0-9]*$/', $id) === 0) {
            $msg = (preg_match('/^[0-9]$/', $id[0]))
                 ? "Application ID must NOT start with Number.\n"
                 : "Only Numeric(0-9) and Alphabetical(A-Z) is allowed for Application Id\n";
            throw new \InvalidArgumentException($msg);
        }
    }


    /**
     *  プロジェクトスケルトンを生成する
     *
     *  @access public
     *  @param  string  $id         プロジェクトID
     *  @param  string  $basedir    プロジェクトベースディレクトリ
     *  @param  string  $skeldir    スケルトンディレクトリ。これが指定されると、そこにある
     *                              ファイルが優先される。また、ETHNA_HOME/skel にないもの
     *                              も追加してコピーする
     *  @param  string  $locale     ロケール名
     *                              (ロケール名は、ll_cc の形式。ll = 言語コード cc = 国コード)
     */
    public function generate($id, $basedir, $skeldir, $locale)
    {
        $dir_list = array(
            array("app", 0755),
            array("app/action", 0755),
            array("app/action_cli", 0755),
            array("app/plugin", 0755),
            array("app/plugin/Filter", 0755),
            array("app/plugin/Validator", 0755),
            array("app/plugin/Smarty", 0755),
            array("app/view", 0755),
            array("app/test", 0755),
            array("bin", 0755),
            array("etc", 0755),
            array("lib", 0755),
            array("locale", 0755),
            array("locale/$locale", 0755),
            array("locale/$locale/LC_MESSAGES", 0755),
            array("log", 0777),
            array("schema", 0755),
            array("skel", 0755),
            array("template", 0755),
            array("template/$locale", 0755),
            array("tmp", 0777),
            array("www", 0755),
            array("www/css", 0755),
            array("www/js", 0755),
            array("www/images", 0755),
        );

        // double check.
        $id = strtolower($id);
        $r = self::checkAppId($id);

        // ディレクトリ作成
        if (is_dir($basedir) == false) {
            // confirm
            printf("creating directory ($basedir) [y/n]: ");
            flush();
            $fp = fopen("php://stdin", "r");
            $r = trim(fgets($fp, 128));
            fclose($fp);
            if (strtolower($r) != 'y') {
                throw new \Exception('aborted by user');
            }

            if (mkdir($basedir, 0775) == false) {
                throw new \Exception('directory creation failed');
            }
        }
        foreach ($dir_list as $dir) {
            $mode = $dir[1];
            $dir = $dir[0];
            $target = "$basedir/$dir";
            if (is_dir($target)) {
                printf("%s already exists -> skipping...\n", $target);
                continue;
            }
            if (mkdir($target, $mode) == false) {
                throw new \Exception('directory creation failed');
            } else {
                printf("project sub directory created [%s]\n", $target);
            }
            if (chmod($target, $mode) == false) {
                throw new \Exception('chmod failed');
            }
        }

        // スケルトンファイル作成
        $macro['ethna_version'] = ETHNA_VERSION;
        $macro['application_id'] = strtoupper($id);
        $macro['project_id'] = ucfirst($id);
        $macro['project_prefix'] = $id;
        $macro['basedir'] = realpath($basedir);
        $macro['locale'] = $locale;

        $macro['action_class'] = '{$action_class}';
        $macro['action_form'] = '{$action_form}';
        $macro['action_name'] = '{$action_name}';
        $macro['action_path'] = '{$action_path}';
        $macro['forward_name'] = '{$forward_name}';
        $macro['view_name'] = '{$view_name}';
        $macro['view_path'] = '{$view_path}';

        $user_macro = $this->_getUserMacro();
        $default_macro = $macro;
        $macro = array_merge($macro, $user_macro);

        //  select locale file.
        $locale_file = $this->_resolveSkelfile("locale/$locale/ethna_sysmsg.ini")
                     ? "locale/$locale/ethna_sysmsg.ini"
                     : 'locale/ethna_sysmsg.default.ini';

        $realfile_maps = array(
            $locale_file    => "$basedir/locale/$locale/LC_MESSAGES/ethna_sysmsg.ini",
            "www.htaccess" => "$basedir/www/.htaccess",
            "www.index.php" => "$basedir/www/index.php",
            "www.css.ethna.css" => "$basedir/www/css/ethna.css",
            "www.images.navbg.gif" => "$basedir/www/images/navbg.gif",
            "www.images.navlogo.gif" => "$basedir/www/images/navlogo.gif",
            "www.images.pagebg.gif" => "$basedir/www/images/pagebg.gif",
            "dot.ethna" => "$basedir/.ethna",
            "app.controller.php" => sprintf("$basedir/app/%s_Controller.php", $macro['project_id']),
            "app.error.php" => sprintf("$basedir/app/%s_Error.php", $macro['project_id']),
            "app.actionclass.php" => sprintf("$basedir/app/%s_ActionClass.php", $macro['project_id']),
            "app.actionform.php" => sprintf("$basedir/app/%s_ActionForm.php", $macro['project_id']),
            "app.viewclass.php" => sprintf("$basedir/app/%s_ViewClass.php", $macro['project_id']),
            "app.action.default.php" => "$basedir/app/action/Index.php",
            "app.plugin.filter.default.php" => sprintf("$basedir/app/plugin/Filter/ExecutionTime.php", $macro['project_id']),
            "app.view.default.php" => "$basedir/app/view/Index.php",
            "app.url_handler.php" => sprintf("$basedir/app/%s_UrlHandler.php", $macro['project_id']),
            "etc.config.php" => sprintf("$basedir/etc/config.php"),
            "template.index.tpl" => sprintf("$basedir/template/$locale/index.tpl"),
            "template.layout.tpl" => sprintf("$basedir/template/$locale/layout.tpl"),
            "template.403.tpl" => sprintf("$basedir/template/$locale/error403.tpl"),
            "template.404.tpl" => sprintf("$basedir/template/$locale/error404.tpl"),
            "template.500.tpl" => sprintf("$basedir/template/$locale/error500.tpl"),
        );

        $skelfile_maps = array(
            "skel.action.php" => sprintf("$basedir/skel/skel.action.php"),
            "skel.action_cli.php" => sprintf("$basedir/skel/skel.action_cli.php"),
            "skel.action_test.php" => sprintf("$basedir/skel/skel.action_test.php"),
            "skel.entry_www.php" => sprintf("$basedir/skel/skel.entry_www.php"),
            "skel.entry_cli.php" => sprintf("$basedir/skel/skel.entry_cli.php"),
            "skel.view.php" => sprintf("$basedir/skel/skel.view.php"),
            "skel.template.tpl" => sprintf("$basedir/skel/skel.template.tpl"),
            "skel.view_test.php" => sprintf("$basedir/skel/skel.view_test.php"),
        );

        //    also copy user defined skel file.
        if (!empty($skeldir)) {
            $handle = opendir($skeldir);
            while (($file = readdir($handle)) !== false) {
                if (is_dir(realpath("$skeldir/$file"))) {
                    continue;
                }
                if (array_key_exists($file, $skelfile_maps) == false) {
                    $skelfile_maps[$file] = sprintf("$basedir/skel/$file");
                }
            }
        }

        $this->_generate($realfile_maps, $macro, $skeldir);

        $this->_generate($skelfile_maps, $default_macro, $skeldir);
    }

    /**
     *  実際のプロジェクトスケルトンを生成処理を行う
     *
     *  @access private
     *  @param  string  $maps       スケルトン名と生成されるファイルの配列
     *  @param  string  $macro      適用マクロ
     *  @param  string  $skeldir    スケルトンディレクトリ。これが指定されると、そこにある
     *                              ファイルが優先される。また、ETHNA_HOME/skel にないもの
     *                              も追加してコピーする
     *  @throws \Exception
     */

    private function _generate($maps, $macro, $skeldir)
    {
        foreach ($maps as $skel => $realfile) {
            if (!empty($skeldir) && file_exists("$skeldir/$skel")) {
                $skel = "$skeldir/$skel";
            }
            if ($this->_generateFile($skel, $realfile, $macro) == false) {
                throw new \Exception("generating file failed:[$skel] => [$realfile]");
            }
        }
    }
}
