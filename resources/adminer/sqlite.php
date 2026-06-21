<?php

/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL v2 or later (https://www.gnu.org//licenses/gpl.html)
 * @author     vrana, FrancoisCapon, mikespub
 */

/**
 * Summary of adminer_object
 * @see https://github.com/vrana/adminer/wiki/Enable-sqlite3
 * @see https://github.com/FrancoisCapon/LoginToASqlite3DatabaseWithoutCredentialsWithAdminer
 * @return \Adminer\Plugins
 */
function adminer_object()
{
    class AdminerCustomization extends \Adminer\Plugins
    {
        /** @var string */
        protected $password_hash;

        /** Set allowed password
         * @param string $password_hash result of password_hash()
         */
        public function __construct($password_hash)
        {
            $this->password_hash = $password_hash;
        }

        /**
         * Overwrite the Adminer::loginForm() method : Print login form
         *
         * @uses adminer() - Get Adminer object
         * @uses Adminer::loginFormField(...) - Get login form field
         * @return true always true
         */
        public function loginForm()
        {
            echo '<div class="error">' . \Adminer\lang('Warning: don\'t use this via public Internet connection!') . '</div>';
            echo "<table cellspacing='0' class='layout'>\n";
            echo '<input type="hidden" name="auth[driver]" value="sqlite">';
            $dbContext = new \SebLucas\Cops\Database\DatabaseContext();
            if ($dbContext->isMultipleDatabaseEnabled()) {
                $input = '<select name="auth[db]">';
                $db = 0;
                foreach ($dbContext->getDbNameList() as $name) {
                    $dbContext->setDatabase($db);
                    $dbFile = $dbContext->getDbFileName();
                    $name = $name ?: basename(dirname($dbFile));
                    $input .= '<option value="' . htmlspecialchars($dbFile) . '">' . htmlspecialchars($name) . '</option>';
                    $db++;
                }
                $input .= '</select>';
            } else {
                $db = 0;
                $dbFile = $dbContext->getDbFileName();
                $input = '<input type="hidden" name="auth[db]" value="' . htmlspecialchars($dbFile) . '">';
                $input .= basename(dirname($dbFile));
            }
            echo \Adminer\adminer()->loginFormField(
                'db',
                '<tr><th>' . \Adminer\lang('Database') . '<td>',
                $input . "\n"
            );
            echo "</table>\n";
            echo "<p><input type='submit' value='" . \Adminer\lang('Select') . "'>\n";
            return true;
        }

        //function loginFormField($name, $heading, $value) {
        //    return parent::loginFormField($name, $heading, str_replace("value='server'", "value='sqlite'", $value));
        //}

        /**
         * Summary of database
         * @return string
         */
        public function database()
        {
            $dbFile = $_GET["db"] ?? "";
            if (empty($dbFile)) {
                return $dbFile;
            }
            $found = false;
            $db = 0;
            $dbContext = new \SebLucas\Cops\Database\DatabaseContext();
            if ($dbContext->isMultipleDatabaseEnabled()) {
                foreach ($dbContext->getDbNameList() as $name) {
                    $dbContext->setDatabase($db);
                    if ($dbFile == $dbContext->getDbFileName()) {
                        $found = true;
                        break;
                    }
                    $db++;
                }
            } elseif ($dbFile == $dbContext->getDbFileName()) {
                $found = true;
            }
            if (!$found) {
                echo "Invalid database " . htmlspecialchars($dbFile);
                exit;
            }
            return $dbFile;
        }

        /**
         * Summary of credentials
         * @return string[]
         */
        public function credentials()
        {
            //$password = \Adminer\get_password();
            //return array(\Adminer\SERVER, $_GET["username"], (password_verify($password, $this->password_hash) ? "" : $password));
            // server, username and password for connecting to database
            return ['localhost', '', ''];
        }

        /**
         * Summary of login
         * @param string $login
         * @param string $password
         * @return bool
         */
        public function login($login, $password)
        {
            return true;
        }
    }
    //  allow sqlite with login-password-less plugin and selecting sqlite3 server type and put full path to sqlite database file in database field.
    //   new AdminerLoginPasswordLess("sladmin", password_hash("mypassword", PASSWORD_DEFAULT)),

    $plugins = [new AdminerCustomization(password_hash("mypassword", PASSWORD_DEFAULT))];
    return new \Adminer\Plugins($plugins);
}
