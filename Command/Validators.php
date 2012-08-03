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
}
