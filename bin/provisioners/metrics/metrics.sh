#!/bin/bash

# Metrics for PHP
#
# author: Jean-François Lépine

echo "PHPMetrics, by Alter Way"
echo


folder=$1
if [ ! -d "$folder" ]; then
  echo "Given folder doesn't exist"
  exit 0
fi

echo -n 'path folder: '
echo -n "$folder"


echo -n 'LOC: '
echo '----------'
echo -n "Number of PHP files: "
find $folder -iname "*.php" | wc -l

echo "Number of lines by file: "
find $folder -iname "*.php"  | xargs wc -l | sort -rn | head

echo


echo 'SQL: '
echo '----------'
echo -n "Number of mysql_queries: "
grep  -r --include \*.php "mysql_query"  $folder | wc -l

echo -n "Number of mysql_escape_string: "
grep -r  --include \*.php "mysql_escape_string"  $folder | wc -l

echo -n "Number of mysql_real_escape_string: "
grep -r  --include \*.php  "mysql_escape_string"  $folder | wc -l

echo -n "Number of mysql_error: "
grep -r  --include \*.php " mysql_error"  $folder | wc -l

echo -n "Number of PDO queries: "
grep -r --include \*.php '>query('  $folder | wc -l

echo -n "Number of PDO quote: "
grep -r --include \*.php '>quote(' $folder | wc -l

echo -n "Number of 'SELECT ': "
grep -r  -i --include \*.php "SELECT " $folder | wc -l

echo -n "Number of 'SELECT *': "
grep -r   -i --include \*.php "SELECT \*" $folder | wc -l

echo -n "Number of 'WHERE ': "
grep -r  -i --include \*.php "WHERE " $folder | wc -l

echo -n "Number of 'JOIN ': "
grep -r  -i --include \*.php "JOIN " $folder | wc -l

echo -n "Number of 'save(': "
grep -r  -i --include \*.php "save(" $folder | wc -l

echo -n "Number of 'delete(': "
grep -r  -i --include \*.php "delete(" $folder | wc -l

echo -n "Number of 'find(': "
grep -r  -i --include \*.php "find(" $folder | wc -l

echo -n "Number of 'findAll(': "
grep -r  -i --include \*.php "findAll(" $folder | wc -l

echo -n "Number of 'loadModel(': "
grep -r  -i --include \*.php "loadModel(" $folder | wc -l

echo -n "Number of 'findByPK(': "
grep -r  -i --include \*.php "findByPK(" $folder | wc -l


#echo -n "Number of SELECT by file: "
#echo
#for file in `find $folder  -iname "*.php"`
#do
#   cnt=`ack-grep --nogroup --php "SELECT " "$file" | wc -l`
#   echo "    $cnt $file"
#done |sort -rn |head


echo

echo 'Escaping: '
echo '----------'
echo -n "Number of \$_GET: "
grep -r --include \*.php "\$_GET" $folder  |wc -l

echo -n "Number of \$_POST: "
grep -r --include \*.php "\$_POST" $folder  |wc -l

echo -n "Number of \$_REQUEST: "
grep -r --include \*.php "\$_REQUEST" $folder  |wc -l

echo -n "Number of filter_: "
grep -r --include \*.php "filter_" $folder  | wc -l

echo -n "Number of addslashes: "
grep -r --include \*.php "addslashes" $folder  |wc -l

echo -n "Number of html_entities: "
grep -r --include \*.php "html_entities" $folder  |wc -l

echo -n "Number of stripslashes: "
grep -r --include \*.php "stripslashes(" $folder  |wc -l

echo -n "Number of stripslashes with array_merge: "
grep -r --include \*.php "array_map('|\"stripslashes" $folder  |wc -l

echo -n "Number of htmlentities: "
grep -r --include \*.php "htmlentities(" $folder  |wc -l

#echo -n "Number of \$_GET or \$_POST by file: "
#echo
#for file in `find $folder  -iname "*.php"`
#do
#   cnt=`ack-grep  "_(POST|GET|REQUEST)" "$file" | wc -l`
#   echo "    $cnt $file"
#done |sort -rn |head


echo

echo 'Prints: '
echo '----------'
echo -n "Number of echo: "
grep -r --include \*.php "echo" $folder  |wc -l

echo -n "Number of var_dump: "
grep -r --include \*.php "var_dump" $folder  |wc -l

echo -n "Number of print_r: "
grep -r --include \*.php "print_r" $folder  |wc -l



## http://php.net/manual/en/security.filesystem.php
echo

echo 'Files: '
echo '----------'

echo -n "Number of fopen: "
grep -r --include \*.php "fopen" $folder  |wc -l

echo -n "Number of fget: "
grep -r --include \*.php "fget" $folder  |wc -l

echo -n "Number of file_put_contents: "
grep -r --include \*.php "file_put_contents" $folder  |wc -l

echo -n "Number of file_get_content: "
grep -r --include \*.php "file_get_content" $folder  |wc -l

echo -n "Number of fclose: "
grep -r --include \*.php "fclose" $folder  |wc -l

echo -n "Number of flock: "
grep -r --include \*.php "flock" $folder  |wc -l

echo -n "Number of unlink: "
grep -r --include \*.php "unlink" $folder  |wc -l

echo -n "Number of rmdir: "
grep -r --include \*.php "rmdir" $folder  |wc -l

echo -n "Number of mkdir: "
grep -r --include \*.php "mkdir" $folder  |wc -l



echo

echo 'Encoding: '
echo '----------'

echo -n "Number of utf8_encode: "
grep -r --include \*.php "utf8_encode" $folder  |wc -l

echo -n "Number of utf8_decode: "
grep -r --include \*.php "utf8_decode" $folder  |wc -l

echo -n "Number of mb_convert: "
grep -r --include \*.php "mb_convert" $folder  |wc -l

echo -n "Number of mb_detect_encoding: "
grep -r --include \*.php "mb_detect_encoding" $folder  |wc -l

echo -n "Number of iconv: "
grep -r --include \*.php "iconv(" $folder  |wc -l



echo

echo 'Flow: '
echo '----------'

echo -n "Number of die: "
grep -r --include \*.php "die(" $folder  |wc -l

echo -n "Number of exit: "
grep -r --include \*.php "exit" $folder  |wc -l

echo -n "Number of register_shutdown_function: "
grep -r --include \*.php "register_shutdown_function" $folder  |wc -l




## http://security.stackexchange.com/questions/1382/disable-insecure-dangerous-php-functions
## http://php.net/manual/en/security.php
echo

echo 'Execution insecure/dangerous php function: '
echo '----------'

echo -n "Number of assert: "
grep -r --include \*.php "assert(" $folder  |wc -l

echo -n "Number of exec: "
grep -r --include \*.php "exec(" $folder  |wc -l

echo -n "Number of passthru: "
grep -r --include \*.php "passthru(" $folder  |wc -l

echo -n "Number of popen: "
grep -r --include \*.php "popen(" $folder  |wc -l

echo -n "Number of proc_close: "
grep -r --include \*.php "proc_close(" $folder  |wc -l

echo -n "Number of proc_open: "
grep -r --include \*.php "proc_open(" $folder  |wc -l

echo -n "Number of shell_exec: "
grep -r --include \*.php "shell_exec(" $folder  |wc -l

echo -n "Number of system: "
grep -r --include \*.php "system(" $folder  |wc -l

echo -n "Number of pcntl_exec: "
grep -r --include \*.php "pcntl_exec(" $folder  |wc -l

echo

echo 'Env: '
echo '----------'

echo -n "Number of getenv: "
grep -r --include \*.php "getenv" $folder  |wc -l

echo -n "Number of apache_getenv: "
grep -r --include \*.php "apache_getenv" $folder  |wc -l

echo -n "Number of putenv: "
grep -r --include \*.php "putenv" $folder  |wc -l

echo -n "Number of apache_setenv: "
grep -r --include \*.php "apache_setenv" $folder  |wc -l


echo
echo 'XSS: '
echo '----------'
echo -n "Number of echo \$_: "
grep -r --include \*.php "echo[[:space:]]+.*\$(_ENV|_GET|_POST|_COOKIE|_REQUEST|_SERVER|HTTP|http).*" $folder  |wc -l

echo -n "Number of print \$_: "
grep -r --include \*.php "print[[:space:]]+.*\$(_ENV|_GET|_POST|_COOKIE|_REQUEST|_SERVER|HTTP|http).*" $folder  |wc -l

echo -n "Number of <?= \$_: "
grep -r --include \*.php "\<\?\=\$(_ENV|_GET|_POST|_COOKIE|_REQUEST|_SERVER|HTTP|http)" $folder  |wc -l


## http://php.net/manual/en/security.database.sql-injection.php
## http://php.net/manual/en/security.database.storage.php
echo
echo 'injection SQL: '
echo '----------'

# SELECT * from user where login='".$_POST."' and password='".$_POST."'
# ' OR '1'='1 =>   SELECT * from user where login='' OR '1'='1' and password='' OR '1'='1'

# UPDATE usertable SET pwd='$pwd' WHERE uid='$uid';
# ' OR uid LIKE'%admin%   => UPDATE usertable SET pwd='hehehe', trusted=100, admin='yes' WHERE ...;

# SELECT * FROM products WHERE id LIKE '%$prod%'
# a%' exec master..xp_cmdshell 'net user test testpass /ADD' --
# =>     SELECT * FROM products WHERE id LIKE '%a%' exec master..xp_cmdshell 'net user test testpass /ADD' --%'

echo -n "Number of Magic sprintf: "
grep -r 'sprintf(".*(SELECT|UPDATE|INSERT).*' $folder  |wc -l


echo

echo 'Methodes Magics: '
echo '----------'

echo -n "Number of Magic methods: "
grep -r --include \*.php "function __" $folder  |wc -l

echo -n "Number of __construct methods: "
grep -r --include \*.php "function __construct" $folder  |wc -l

echo -n "Number of __destruct methods: "
grep -r --include \*.php "function __destruct" $folder  |wc -l

echo -n "Number of __call methods: "
grep -r --include \*.php "function __call" $folder  |wc -l

echo -n "Number of __get methods: "
grep -r --include \*.php "function __get" $folder  |wc -l

echo -n "Number of __set methods: "
grep -r --include \*.php "function __set" $folder  |wc -l

echo -n "Number of __clone methods: "
grep -r --include \*.php "function __clone" $folder  |wc -l

echo -n "Number of __invoke methods: "
grep -r --include \*.php "function __invoke" $folder  |wc -l

echo -n "Number of __sleep methods: "
grep -r --include \*.php "function __sleep" $folder  |wc -l

echo

echo 'Misc: '
echo '----------'

echo -n "Number of call_user_func: "
grep -r --include \*.php "call_user_func" $folder  |wc -l

echo -n "Number of extract: "
grep -r --include \*.php "extract" $folder  |wc -l

echo -n "Number of throw: "
grep -r --include \*.php "throw" $folder  |wc -l

echo -n "Number of eval: "
grep -r --include \*.php "eval(" $folder  |wc -l

echo -n "Number of spl_autoload: "
grep -r --include \*.php "spl_autoload" $folder  |wc -l

echo -n "Number of global: "
grep -r --include \*.php "global " $folder  |wc -l

echo -n "Number of include: "
grep -r --include \*.php "include" $folder  |wc -l

echo -n "Number of require: "
grep -r --include \*.php "require" $folder  |wc -l

echo -n "Number of switch: "
grep -r --include \*.php "switch" $folder  |wc -l

echo -n "Number of else: "
grep -r --include \*.php "else" $folder  |wc -l



echo

echo ' Docummentation reporting: '
echo '----------'

echo -n "Number of Inline comments //: "
comm_inline=$(grep -r "//" $folder  |wc -l)
echo $comm_inline

echo -n "Number of comments /*: "
comm_block_global=$(grep -r "/\*" $folder |wc -l)
echo $comm_block_global

echo -n "Number of start documentor block comments /**: "
comm_documentor_block_start=$(grep -r "/\*\*" $folder |wc -l)
echo $comm_documentor_block_start

echo -n "Number of end documentor block comments */: "
comm_documentor_block_end=$(grep -r "*/" $folder |wc -l)
echo $comm_documentor_block_end

echo -n "Number of non block comments */: "
comm_documentor_block_non=$((comm_block_global-comm_documentor_block_end))
echo $comm_documentor_block_non

echo -n "Number of bad block comments */: "
comm_documentor_block_bad=$((comm_documentor_block_end-comm_documentor_block_start))
echo $comm_documentor_block_bad


echo

echo ' Docummentation package reporting: '
echo '----------'

echo -n "Number of package comments //: "
comm_package=$(grep -r --include \*.php "\@package " $folder |wc -l)
echo $comm_package

echo -n "Number of subpackage comments //: "
comm_subpackage=$(grep -r --include \*.php "\@subpackage " $folder |wc -l)
echo $comm_subpackage



echo

echo ' Object reporting: '
echo '----------'

#echo -n "Number of constants const string : "
obj_const_total=$(grep -r --include \*.php "const " $folder |wc -l)
#echo $obj_const_total

#echo -n "Number of constants @const: "
obj_const_comment=$(grep -r --include \*.php "@const " $folder |wc -l)
#echo $obj_const_comment

#echo -n "Number of constants 'use const': "
obj_const_use=$(grep -r --include \*.php "use const " $folder |wc -l)
#echo $obj_const_use

echo -n "Number of constants object: "
obj_const_result=$((obj_const_total-obj_const_comment-obj_const_use))
echo $obj_const_result

echo -n "Number of Abstract Classes (NOCA) //: "
obj_class_abstract=$(grep -r --include \*.php "[[:space:]]+abstract[[:space:]]+class[[:space:]]+" $folder |wc -l)
echo $obj_class_abstract

echo -n "Number of Concrete Classes (NOCA) //: "
obj_class_abstract=$(grep -r --include \*.php "([[:space:]]*)class([[:space:]]+)([A-Za-z]+)" $folder |wc -l)
echo $obj_class_abstract

echo -n "Number of Interface Classes (NOCA) //: "
obj_class_abstract=$(grep -r --include \*.php "([[:space:]]*)interface([[:space:]]+)([A-Za-z]+)" $folder |wc -l)
echo $obj_class_abstract

echo -n "Number of Implements (NOCA) //: "
obj_class_abstract=$(grep -r --include \*.php "(.*)([[:space:]]+)implements([[:space:]]+)(.*)" $folder |wc -l)
echo $obj_class_abstract



echo

echo ' Error reporting: '
echo '----------'
## Ne jamais dévoiler d’informations concernant les chemins et configuration
## En production, display_errors et error_reporting(E_ALL) doivent être désactivés  ==> error_reporting(0);
## Éviter la suppression d’erreurs avec @ qui est très lente. Il vaut mieux utiliser error_reporting(0) en début de script.
echo -n "Number of phpinfo: "
grep -r --include \*.php "phpinfo(" $folder |wc -l

echo -n "Number of display_errors: "
grep -r --include \*.php "display_errors(" $folder |wc -l

echo -n "Number of error_reporting(0): "
grep -r --include \*.php "error_reporting(0)" $folder |wc -l

echo -n "Number of @: "
grep -r --include \*.php " @" $folder |wc -l


echo
echo 'Framework SYMFONY2: '
echo '-----------------'

echo -n "Number of Form Handler: "
grep -r --include \*.php "extends AbstractFormHandler" $folder |wc -l

echo -n "Number of Form type: "
grep -r --include \*.php "extends AbstractType" $folder |wc -l

echo -n "Number of EventSubscriber: "
grep -r --include \*.php "class EventSubscriber" $folder |wc -l


echo
echo 'Framework SYMFONY 1.4: '
echo '-----------------'




echo
echo 'Framework Sourceweb: '
echo '-----------------'

echo -n "Number of DatabaseManager::get: "
grep -r --include \*.php "DatabaseManager::get" $folder  |wc -l

echo -n "Number of prepare request SQL: "
grep -r --include \*.php "prepare(" $folder  |wc -l

echo -n "Number of insert request SQL: "
grep -r --include \*.php "prepare('INSERT" $folder  |wc -l

echo -n "Number of update request SQL: "
grep -r --include \*.php "prepare('UPDATE" $folder  |wc -l

echo -n "Number of delete request SQL: "
grep -r --include \*.php "prepare('DELETE" $folder  |wc -l

echo -n "Number of bindParam: "
grep -r --include \*.php "bindParam(" $folder  |wc -l

echo -n "Number of statics function: "
grep -r --include \*.php "public static function" $folder  |wc -l

echo -n "Number of execute: "
grep -r --include \*.php "execute(" $folder  |wc -l

echo -n "Number of logger used: "
grep -r --include \*.php "getLogger()" $folder  |wc -l

echo -n "Number of SoapClient instance: "
grep -r --include \*.php "SoapClient(" $folder  |wc -l

echo -n "Number of SAPService used: "
grep -r --include \*.php "SAPService::getInstance()" $folder  |wc -l




