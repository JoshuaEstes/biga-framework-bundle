<?php

namespace BigaFrameworkBundle\Command;

class Validators
{

    public static function validateHost($host)
    {
        if (null === $host) {
            throw new \InvalidArgumentException('Invalid host');
        }

        return $host;
    }

    public static function validateHostsFile($hosts_file)
    {
        return $hosts_file;
    }

    public static function validateIPAddress($ip_address)
    {
        return $ip_address;
    }

    public static function validatePort($port)
    {
        return $port;
    }

    public static function validateDocumentRoot($document_root)
    {
        return $document_root;
    }

    public static function validatePriority($priority)
    {
        return $priority;
    }

    public static function validateDatabaseDriver($driver)
    {
        return $driver;
    }

    public static function validateDatabaseHost($host)
    {
        return $host;
    }

    public static function validateDatabasePort($port)
    {
        return $port;
    }

    public static function validateDatabaseName($name)
    {
        return $name;
    }

    public static function validateDatabaseUsername($username)
    {
        return $username;
    }

    public static function validateDatabasePassword($password)
    {
        if (null === $password) {
            throw new \InvalidArgumentException('You must provide a password');
        }

        return $password;
    }

    public static function validateMailerTransport($transport)
    {
        return $transport;
    }

    public static function validateMailerHost($host)
    {
        return $host;
    }

    public static function validateMailerUsername($username)
    {
        return $username;
    }

    public static function validateMailerPassword($password)
    {
        return $password;
    }
}
