<?php

namespace App\ZL\Moodle;

use App\Models\ExternalToken;
use App\Models\User;

class TokenHelper
{
    static public $tokenduration =  2592000;

    static function getTokenForUser(User $user) {

        $tokens = ExternalToken::where([
            ['userid', $user->id],
            ['externalserviceid', 2],
            ['tokentype', EXTERNAL_TOKEN_PERMANENT],
        ])->get();
//external_services_users

        // A bit of sanity checks.
        foreach ($tokens as $key => $token) {

            // Checks related to a specific token. (script execution continue).
            $unsettoken = false;

            // Remove token is not valid anymore.
            if (!empty($token->validuntil) and $token->validuntil < time()) {
                self::delToken($token);
                $unsettoken = true;
            }

            // Remove token if its ip not in whitelist.
            if (isset($token->iprestriction) and !address_in_subnet(getremoteaddr(), $token->iprestriction)) {
                $unsettoken = true;
            }

            if ($unsettoken) {
                unset($tokens[$key]);
            }
        }

        // If some valid tokens exist then use the most recent.
        if (count($tokens) > 0) {
            $token = array_pop($tokens);
        } else {
            $token = new \stdClass;
            $token->token = md5(uniqid(rand(), 1));
            $token->userid = $user->id;
            $token->tokentype = EXTERNAL_TOKEN_PERMANENT;
            $token->contextid = 1;
//            $token->contextid = 0;
            $token->creatorid = $user->id;
            $token->timecreated = time();
            $token->externalserviceid = 2;
            // By default tokens are valid for 12 weeks.
            $token->validuntil = $token->timecreated + self::$tokenduration;
            $token->iprestriction = null;
            $token->sid = null;
            $token->lastaccess = null;
            // Generate the private token, it must be transmitted only via https.
            $token->privatetoken = random_string(64);
            $token->id = ExternalToken::create(json_decode(json_encode($token), true))->id;

            $eventtoken = clone $token;
            $eventtoken->privatetoken = null;
            $params = array(
                'objectid' => $eventtoken->id,
                'relateduserid' => $user->id,
                'other' => array(
                    'auto' => true
                )
            );
            $event = \core\event\webservice_token_created::create($params);
            $event->add_record_snapshot('external_tokens', $eventtoken);
            $event->trigger();
        }
        return $token;
    }

    static function generateNewTokenForUser(User $user) {

        ExternalToken::where([
            ['userid', $user->id],
            ['externalserviceid', 2],
            ['tokentype', EXTERNAL_TOKEN_PERMANENT],
        ])->delete();

        $token = new \stdClass;
        $token->token = md5(uniqid(rand(), 1));
        $token->userid = $user->id;
        $token->tokentype = EXTERNAL_TOKEN_PERMANENT;
        $token->contextid = 1;
//            $token->contextid = 0;
        $token->creatorid = $user->id;
        $token->timecreated = time();
        $token->externalserviceid = 2;
        // By default tokens are valid for 12 weeks.
        $token->validuntil = $token->timecreated + self::$tokenduration;
        $token->iprestriction = null;
        $token->sid = null;
        $token->lastaccess = null;
        // Generate the private token, it must be transmitted only via https.
        $token->privatetoken = random_string(64);
        $token->id = ExternalToken::create(json_decode(json_encode($token), true))->id;

        $eventtoken = clone $token;
        $eventtoken->privatetoken = null;
        $params = array(
            'objectid' => $eventtoken->id,
            'relateduserid' => $user->id,
            'other' => array(
                'auto' => true
            )
        );
        $event = \core\event\webservice_token_created::create($params);
        $event->add_record_snapshot('external_tokens', $eventtoken);
        $event->trigger();
        return $token;
    }

    protected static function delToken(ExternalToken $token)
    {
        ExternalToken::where('id', $token->id)->delete();
    }

}
