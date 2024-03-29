<?php

namespace Metrichawk\MetrichawkLaravel\Helpers;

class IpAnonymizer {
    /**
     * @var string
     */
    public $ipv4NetMask = "255.255.255.0";

    /**
     * @var string
     */
    public $ipv6NetMask = "ffff:ffff:ffff:ffff:0000:0000:0000:0000";

    /**
     * @param $address
     * @return false|string
     */
    public static function anonymizeIp($address) {
        $anonymizer = new IpAnonymizer();

        return $anonymizer->anonymize($address);
    }

    /**
     * @param $address
     * @return string
     */
    public function anonymize($address) {
        $packedAddress = inet_pton($address);

        if (strlen($packedAddress) === 4) {
            return $this->anonymizeIPv4($address);
        } elseif (strlen($packedAddress) === 16) {
            return $this->anonymizeIPv6($address);
        } else {
            return "";
        }
    }

    /**
     * @param $address
     * @return false|string
     */
    public function anonymizeIPv4($address) {
        return inet_ntop(inet_pton($address) & inet_pton($this->ipv4NetMask));
    }

    /**
     * @param $address
     * @return false|string
     */
    public function anonymizeIPv6($address) {
        return inet_ntop(inet_pton($address) & inet_pton($this->ipv6NetMask));
    }
}
