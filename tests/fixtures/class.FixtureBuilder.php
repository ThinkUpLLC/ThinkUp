<?php
/**
 * FixtureBuilder
 *
 * Data Fixture builder for test data generation. Will auto generate values if not defined.
 *
 * Table data gets truncated when the builder object goes out of scope.
 *
 * Currently only tested with mysql.
 *
 * Example use:
 *
 *   <code>
 *   // populate a table named "table_name" with two columns: "name", "email"
 *   $builder = FixtureBuilder::build('table_name');
 *   $name_value = $builder->columns['name'];
 *   $email_value = $builder->columns['email'];
 *
 *   // you can also set values
 *   $builder = FixtureBuilder::build('table_name', array( 'name' => 'Mojo Jojo', 'email' => 'mojo@jojo.info' ));
 *   $name_value = $builder->columns['name'];
 *   $email_value = $builder->columns['email']
 *
 *   // you can set date values by string
 *   $builder = FixtureBuilder::build('table_name', array( 'date_added' => '2010-06-21 20:34:13' ));
 *
 *   // or you can set dates by + or - n days, hours, minutes or seconds
 *   // 1 hour ahead
 *   $builder = FixtureBuilder::build('table_name', array( 'date_added' => '+1h' ));
 *   // 3 days behind
 *   $builder = FixtureBuilder::build('table_name', array( 'date_added' => '-3d' ));
 *   // 600 seconds behind
 *   $builder = FixtureBuilder::build('table_name', array( 'date_added' => '-300s' ));
 *
 *   // to truncate the data in a table, just set the builder to null
 *   // and __destruct will call 'truncate table $tablename', ie:
 *   $builder = FixtureBuilder::build('table_name', array( 'date_added' => '-300s' ));
 *   $builder = null;
 *   </code>
 *
 * @author Mark Wilkie <mwilkie[at]gmail[dot]com>
 */
require_once 'class.FixtureBuilderException.php';
require_once 'webapp/model/class.Config.php';

class FixtureBuilder {

    /*
     * @var bool Debugging flag
     */
    var $DEBUG = false;

    /*
     * @var array Default lengths for data
     */
    var $DATA_DEFAULTS = array(
        'bigint'      => 1000000000,
        'int'         => 1000000,
        'smallint'    => 10000,
        'tinyint'     => 10,
        'text'        => 50, //20 cahrs
        'mediumtext'  => 40, //10 chars
        'tinytext'    => 30,  // 5 chars
        'varchar'     => 20, //20 chars
        'char'        => 10, //10 chars
        'float'       => 1000
    );

    /*
     * @var PDO our db handle
     */
    static $pdo;


    /*
     * our Constructor
     */
    public function __construct($debug = false) {
        $this->DEBUG = $debug ? $debug : $this->DEBUG;
        $this->config = Config::getInstance();
        if(is_null(self::$pdo)) {
            self::$pdo = $this->connect();
        }
    }

    /**
     * Builds our data
     * @param str table name (without prefix)
     * @param array hash args of column values (optional)
     * @param bool debug (defaults to false)
     * @return FixtureBuilder our builder object with column values
     */
    public static function build($table, $args = null, $debug = false) {
        $builder = new FixtureBuilder($debug);
        $builder->buildData($table, $args);
        $builder->table = $table;
        return $builder;
    }

    /*
     * Connect to db using PDO
     * @return PDO
     */
    private function connect() {
        $db_string = sprintf("mysql:dbname=%s;host=%s", $this->config->getValue('db_name'),
        $this->config->getValue('db_host'));
        if($this->DEBUG) { echo "DEBUG: Connecting to $db_string\n"; }
        $db_socket = $this->config->getValue('db_socket');
        if ( $db_socket) {
            $db_string.=";unix_socket=".$db_socket;
        }
        $pdo = new PDO($db_string, $this->config->getValue('db_user'), $this->config->getValue('db_password'));
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    }

    /*
     * Truncate a table by name
     * @param str Table name (without prefix)
     */
    public static function truncateTable($table) {
        $table = Config::getInstance()->getValue('table_prefix') . $table;
        try {
            self::$pdo->query('truncate table ' . $table);
        } catch(Exception $e) {
            throw new FixtureBuilderException('Unable to truncate table "' . $table . '" - ' . $e->getMessage());
        }
    }

    /*
     * Describes a table
     * @param str A table name (without prefix)
     * @return array A list of table columns
     */
    public function describeTable($table) {
        $columns = array();
        $table = $this->config->getValue('table_prefix') . $table;
        try {
            $stmt = self::$pdo->query('desc ' . $table);
            while ($row = $stmt->fetch()) {
                $columns[$row['Field']] = $row;
            }
        } catch(Exception $e) {
            throw new FixtureBuilderException('Unable to describe table "' . $table . '" - ' . $e->getMessage());
        }
        return $columns;
    }

    /*
     * Build our data
     * @param str A table name: note, without prefix
     * @param array Column values (optional)
     * @return array Our columns with data
     */
    public function buildData($table, $args = null) {
        $columns = $this->describeTable($table);
        $this->columns = array();
        $sql = "INSERT INTO " . $this->config->getValue('table_prefix') . $table;
        foreach( $columns as $column) {
            $field_value = (! is_null($args)) && isset( $args[ $column['Field'] ]) ? $args[ $column['Field'] ] : null;
            if( isset($column['Key']) && $column['Key'] == 'UNI' && ! $field_value) {
                throw new FixtureBuilderException($column['Field'] .
                ' has a unique key constraint, a value must be defined for this column');
            }
            if( isset($column['Extra']) && $column['Extra'] == 'auto_increment' && ! $field_value ) {
                continue;
            }
            if($field_value) {
                if(preg_match('/^(times|date)/', $column['Type'])) {
                    $column['value'] = $this->genDate($field_value);
                } else {
                    $column['value'] = $field_value;
                }
            } else if (isset($column['Default']) && $column['Default'] != ''
            && $column['Default'] != 'CURRENT_TIMESTAMP') {
                $column['value'] = $column['Default'];
            } else {
                if(preg_match('/^enum/', $column['Type'])) {
                    $column['value'] = $this->genEnum( $column['Type'] );
                } else if(preg_match('/^decimal/', $column['Type'])) {
                    $column['value'] = $this->genDecimal($column['Type']);
                } else if(preg_match('/^int/', $column['Type'])) {
                    $column['value'] = $this->genInt();
                } else if(preg_match('/^bigint/', $column['Type'])) {
                    $column['value'] = $this->genBigint();
                } else if(preg_match('/^(times|date)/', $column['Type'])) {
                    $column['value'] = $this->genDate();
                } else if(preg_match('/^varchar/', $column['Type'])) {
                    $column['value'] = $this->genVarchar();
                }
            }
            $this->columns[ $column['Field'] ] = $column['value'];
        }
        $sql .= sprintf(" (%s) VALUES", join(',', array_keys($this->columns) ));
        $values = array_values($this->columns);
        array_shift($values);
        $sql .= sprintf(" (?%s)", str_repeat(",?", count($values)));
        $stmt = self::$pdo->prepare($sql);
        $stmt->execute(array_values($this->columns));
        $last_insert_id = self::$pdo->lastInsertId();
        if(isset($last_insert_id)) {
            $this->columns['last_insert_id'] = $last_insert_id;
        }
    }

    /*
     * Generates a varchar value
     * @param int Length (optional)
     * @return str
     */
    public function genVarchar($length = 0) {
        $length = $length > 0 ? $length : $this->DATA_DEFAULTS['varchar'];
        return $this->genString($length);
    }

    /*
     * Generates a string value
     * @param int Length (optional)
     * @return str
     */
    public function genString($length = 0) {
        $characters = array(
        "a","b","c","d","e","f","g","h","j","k","l","m",
        "n","p","q","r","s","t","u","v","w","x","y","z",
        "A","B","C","D","E","F","G","H","J","K","L","M",
        "N","P","Q","R","S","T","U","V","W","X","Y","Z",
        "1","2","3","4","5","6","7","8","9", "0", " ");

        $length = $length > 0 ? $length : $this->DATA_DEFAULTS['varchar'];
        $length = rand(1, $length);
        $string = '';
        for($i = 0; $i < $length; $i++) {
            $string .= $characters[mt_rand(0, count($characters)-1)];
        }
        return $string;
    }

    /*
     * Generates an int value
     * @param int Length (optional)
     * @return int
     */
    public function genInt($length = 0, $unsigned = false) {
        $length = $length > 0 ? $length : $this->DATA_DEFAULTS['int'];
        $start = $unsigned ? ($length * -1) : 1;
        return rand($start, $length);
    }

    /*
     * Generates a big int value
     * @param int Length (optional)
     * @return int
     */
    public function genBigInt($length = 0, $unsigned = false) {
        $length = $length > 0 ? $length : $this->DATA_DEFAULTS['bigint'];
        return $this->genInt($length, $unsigned);
    }

    /*
     * Generates a tiny int value
     * @param int Length (optional)
     * @return int
     */
    public function genTinyInt($length = 0, $unsigned = false) {
        $length = $length > 0 ? $length : $this->DATA_DEFAULTS['tinyint'];
        return $this->genInt($length, $unsigned);
    }

    /*
     * Generates a float value
     * @param int Length (optional)
     * @return float
     */
    public function genFloat($length) {
        $int = rand(0, $length);

    }

    /*
     * Generates an enum value
     * @param str  An 'emun(...)' description
     * @return string
     */
    public function genEnum($values) {
        $values = preg_replace("/enum\\(|\\)/i", '', $values);
        $values = preg_split('/,/', $values);
        $value = $values[mt_rand(0, count($values)-1)];
        $value = preg_replace("/^'|'$/", '', $value);
        return $value;
    }

    /*
     * Generates decimal value
     * @param str A 'decimal(M,D)' description
     * @return float
     */
    public function genDecimal($values) {
        $values = preg_replace("/(decimal)\\(|\\)/i", '', $values);
        $values = preg_split('/,/', $values);
        $left = mt_rand(0, pow(10, $values[0]) - 1);
        $right = mt_rand(1, pow(10, $values[1]) - 1);
        $value = $left . '.' . $right;
        $value = $value + 0; // cast to a float;
        return $value;
    }

    /*
     * Generates a mysql date
     * @param str A date increment or decrement (+3d, -1h, +7m, -2m), or a mysql date string '2010-06-20 16:22:25'
     * @return str
     */
    public function genDate($value = null) {
        $time_inc_map = array('h' => 'HOUR', 'd' => 'DAY', 'm' => 'MINUTE', 's' => 'SECOND');
        $sql = 'select now() - interval rand()*100000000 second';
        if($value) {
            if(preg_match('/^(\+|\-)(\d+)(s|m|h|d)/', $value, $matches)) {
                $sql = "select now() $matches[1] interval $matches[2] " . $time_inc_map[$matches[3]];
            } else {
                $sql = null;
            }
        }
        if($sql) {
            $stmt = self::$pdo->query(  $sql . ' as FDATE' );
            $data = $stmt->fetch();
            return $data[0];
        } else {
            return $value;
        }
    }

    /*
     * Our destructor
     * truncates the fixture table
     */
    function __destruct() {
        if(isset($this->table)) {
            $table = Config::getInstance()->getValue('table_prefix') . $this->table;
            try {
                self::$pdo->query('truncate table ' . $table);
            } catch(Exception $e) {
                throw new FixtureBuilderException('Unable to truncate table "' . $table . '" - ' . $e->getMessage());
            }
        }

    }
}
