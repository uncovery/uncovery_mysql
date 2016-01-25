<?php

/**
 * PDO abstraction
 *
 */

global $UNC_DB;
$default_config = array(
    'database' => 'default',
    'username' => 'default',
    'server' => 'localhost',
    'password' => 'default',
);

// let's set the
foreach ($default_config as $type => $value) {
    if (!isset($UNC_DB[$type])) {
        $UNC_DB[$type] = $value;
    }
}

if (!function_exists('XMPP_ERROR_trace')) {
    die('ERROR checking failed, install XMPP_ERROR!');
}

// new way
$UNC_DB['link'] = new PDO("mysql:host={$UNC_DB['server']};dbname={$UNC_DB['database']}", $UNC_DB['username'], $UNC_DB['password']);
$UNC_DB['link']->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

/**
 * Replacement for mysql_query
 * returns result. includes error notification
 *
 * can close the query if needed, otherwise returns result
 *
 * @global PDO $UMC_DB
 * @param string $sql
 * @param boolean $close
 * @return type
 */
function umc_mysql_query($sql, $close = false) {
    global $UNC_DB;
    XMPP_ERROR_trace(__FUNCTION__, func_get_args());
    $rst = $UNC_DB['link']->query($sql);
    $error = $UNC_DB['link']->errorInfo();
    if (!is_null($error[2])) {
        XMPP_ERROR_trigger("MySQL Query Error: '$sql' : " . $error[2]);
        return false;
    } else if ($close) {
        $rst->closeCursor();
        return true;
    } else {
        return $rst;
    }
}

/**
 * Replacement for mysql_query
 * specific for DELETE, UPDATE and INSERT queries. returns affected rows
 *
 * can close the query if needed, otherwise returns result
 *
 * @global PDO $UMC_DB
 * @param string $sql
 * @param boolean $close
 * @return type
 */
function umc_mysql_execute_query($sql) {
    global $UNC_DB;
    XMPP_ERROR_trace(__FUNCTION__, func_get_args());
    $obj = $UNC_DB['link']->prepare($sql);
    $obj->execute();
    $error = $obj->errorInfo();
    if (!is_null($error[2])) {
        XMPP_ERROR_trigger("MySQL Query Error: '$sql' : " . $error[2]);
        return false;
    } else {
        $count = $obj->rowCount();
        return $count;
    }
}

/**
 * Replacement for mysql_affected_rows
 * Returns count. Optionally closes the recordset
 *
 * @param recordset $rst
 * @param boolean $close
 * @return int
 */
function umc_mysql_affected_rows($rst, $close = false) {
    $rowCount = $rst->fetchColumn();
    if ($close) {
        $rst->closeCursor();
    }
    return $rowCount;
}

/**
 * Replacement for mysql_insert_id
 *
 * @global PDO $UMC_DB
 * @return type integer
 */
function umc_mysql_insert_id() {
    global $UNC_DB;
    return $UNC_DB['link']->lastInsertId();
}

/**
 * Replacement for mysql_fetch_array (MYSQL_ASSOC)
 * Returns one line of associative arrays
 *
 * @param type $rst
 * @return type
 */
function umc_mysql_fetch_array($rst) {
    XMPP_ERROR_trace(__FUNCTION__, func_get_args());
    if (!$rst) {
        XMPP_ERROR_trigger("tried fetch_array on erroneous recordset");
        return false;
    }
    $row = $rst->fetch(PDO::FETCH_ASSOC);
    return $row;
}

/**
 * Replacement of mysql_free_result
 *
 * @param type $rst
 */
function umc_mysql_free_result($rst) {
    $rst->closeCursor();
}

/**
 * Replacement of mysql_real_escape_string
 * ATTENTION: This also puts quotes around the value
 *
 * @global PDO $UMC_DB
 * @param type $value
 * @return type
 */
function umc_mysql_real_escape_string($value) {
    global $UNC_DB;
    return $UNC_DB['link']->quote($value);
}

function umc_mysql_fetch_all($sql) {
    global $UNC_DB;
    XMPP_ERROR_trace(__FUNCTION__, func_get_args());
    $stmt = $UNC_DB['link']->prepare($sql);
    if (!$stmt) {
        $error = $UNC_DB['link']->errorInfo();
        XMPP_ERROR_trigger($error);
        return false;
    } else {
        $stmt->execute();
    }
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $result;
}
