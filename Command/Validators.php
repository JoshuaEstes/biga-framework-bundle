<?php

/**
 * This file is part of the Biga Framework Bundle
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code
 */

namespace BigaFrameworkBundle\Command;

/**
 * Validators that check user input from the Commands
 *
 * @author Joshua Estes
 */
class Validators
{

    public static function validateHost($host)
    {
        if (empty($host)) {
            throw new \InvalidArgumentException('Invalid host');
        }

        return $host;
    }

    public static function validateHostsFile($hosts_file)
    {
        if (empty($hosts_file)) {
            throw new \InvalidArgumentException('Invalid');
        }

        return $hosts_file;
    }

    public static function validateIPAddress($ip_address)
    {
        if (empty($ip_address)) {
            throw new \InvalidArgumentException('Invalid');
        }
        return $ip_address;
    }

    public static function validatePort($port)
    {
        if (empty($port)) {
            throw new \InvalidArgumentException('Invalid');
        }

        return $port;
    }

    public static function validateDocumentRoot($document_root)
    {
        if (empty($document_root)) {
            throw new \InvalidArgumentException('Invalid');
        }

        return $document_root;
    }

    public static function validatePriority($priority)
    {
        if (empty($priority)) {
            throw new \InvalidArgumentException('Invalid');
        }

        return $priority;
    }

    public static function validateDatabaseDriver($driver)
    {
        if (empty($driver)) {
            throw new \InvalidArgumentException('Invalid');
        }

        return $driver;
    }

    public static function validateDatabaseHost($host)
    {
        if (empty($host)) {
            throw new \InvalidArgumentException('Invalid');
        }

        return $host;
    }

    public static function validateDatabasePort($port)
    {
        if (empty($port)) {
            throw new \InvalidArgumentException('Invalid');
        }

        return $port;
    }

    public static function validateDatabaseName($name)
    {
        if (empty($name)) {
            throw new \InvalidArgumentException('Invalid');
        }

        return $name;
    }

    public static function validateDatabaseUsername($username)
    {
        if (empty($username)) {
            throw new \InvalidArgumentException('Invalid');
        }

        return $username;
    }

    public static function validateDatabasePassword($password)
    {
        if (empty($password)) {
            throw new \InvalidArgumentException('You must provide a password');
        }

        return $password;
    }

    public static function validateMailerTransport($transport)
    {
        if (empty($transport)) {
            throw new \InvalidArgumentException('Invalid');
        }

        return $transport;
    }

    public static function validateMailerHost($host)
    {
        if (empty($host)) {
            throw new \InvalidArgumentException('Invalid');
        }

        return $host;
    }

    public static function validateMailerUsername($username)
    {
        if (empty($username)) {
            throw new \InvalidArgumentException('Invalid');
        }

        return $username;
    }

    public static function validateMailerPassword($password)
    {
        if (empty($password)) {
            throw new \InvalidArgumentException('Invalid');
        }

        return $password;
    }

    public static function validateLocale($locale)
    {
        if (empty($locale)) {
            throw new \InvalidArgumentException('Invalid');
        }

        return $locale;
    }
}
