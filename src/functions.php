<?php
/**
 * @author    pixiv Inc.
 * @copyright 2015 pixiv Inc.
 * @license   WTFPL
 */

/**
 * @return \PDO
 */
function db()
{
    static $db;

    if (!$db) {
        $db = new \PDO(getenv('DB_DSN'), null, null, [PDO::ATTR_PERSISTENT => true]);
    }

    return $db;
}

function csrf_token()
{
	return base64_encode(openssl_random_pseudo_bytes(64));
}

function password($passwd, $hash)
{
	if (isset($passwd, $hash)) {
		return password_verify($passwd, $hash);
	} else {
		return password_hash($passwd, PASSWORD_DEFAULT);
	}
}
